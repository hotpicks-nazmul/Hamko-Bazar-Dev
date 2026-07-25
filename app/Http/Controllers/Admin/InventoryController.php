<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\StockService;
use App\Exceptions\InsufficientStockException;

class InventoryController extends Controller
{
    /**
     * Display inventory management dashboard.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by stock status
        if ($request->stock_status) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->whereRaw('quantity > IFNULL(low_stock_threshold, 10)');
                    break;
                case 'low_stock':
                    $query->whereRaw('quantity > 0 AND quantity <= IFNULL(low_stock_threshold, 10)');
                    break;
                case 'out_of_stock':
                    $query->where('quantity', '<=', 0);
                    break;
            }
        }

        // Sorting — whitelisted. These two values go straight into the query, so an unknown
        // column used to answer 500 (and per_page=-5 blew up inside the paginator maths).
        $this->applySort($query, $request, ['updated_at', 'quantity', 'name', 'price', 'sku'], 'updated_at', 'desc');

        // Pagination
        $products = $query->paginate($this->perPage($request));

        // Get categories for filter
        $categories = Category::orderBy('name')->get();

        // Calculate stats using dynamic low_stock_threshold
        $stats = [
            'total_products' => Product::count(),
            'total_stock' => Product::sum('quantity'),
            'in_stock' => Product::whereRaw('quantity > IFNULL(low_stock_threshold, 10)')->count(),
            'low_stock' => Product::whereRaw('quantity > 0 AND quantity <= IFNULL(low_stock_threshold, 10)')->count(),
            'out_of_stock' => Product::where('quantity', '<=', 0)->count(),
            'total_value' => Product::sum(DB::raw('quantity * COALESCE(cost_price, price)')),
            // COALESCE falls back to the selling price, and no product has a cost_price today, so
            // the card used to present retail value as "Stock Value". Say which one it is showing.
            'uses_cost_price' => Product::whereNotNull('cost_price')->exists(),
        ];

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.inventory.partials.table-rows', compact('products'))->render(),
                'pagination' => $products->appends(request()->query())->links()->toHtml(),
            ]);
        }

        return view('admin.inventory.index', compact('products', 'categories', 'stats'));
    }

    /**
     * Display stock alerts (low stock products).
     */
    public function stockAlerts(Request $request)
    {
        $query = Product::with('category');

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by alert type
        if ($request->alert_type) {
            switch ($request->alert_type) {
                case 'critical':
                    $query->where('quantity', '<=', 0);
                    break;
                case 'warning':
                    $query->whereRaw('quantity > 0 AND quantity <= FLOOR(IFNULL(low_stock_threshold, 10) * 0.5)');
                    break;
                case 'notice':
                    $query->whereRaw('quantity > FLOOR(IFNULL(low_stock_threshold, 10) * 0.5) AND quantity <= IFNULL(low_stock_threshold, 10)');
                    break;
            }
        } else {
            // Default: show all low stock
            $query->whereRaw('quantity <= IFNULL(low_stock_threshold, 10)');
        }

        // Sorting — whitelisted (see index()).
        $this->applySort($query, $request, ['quantity', 'name', 'low_stock_threshold', 'updated_at'], 'quantity', 'asc');

        $products = $query->paginate($this->perPage($request));

        // Stats for alerts using dynamic threshold
        $stats = [
            'critical' => Product::where('quantity', '<=', 0)->count(),
            'warning' => Product::whereRaw('quantity > 0 AND quantity <= FLOOR(IFNULL(low_stock_threshold, 10) * 0.5)')->count(),
            'notice' => Product::whereRaw('quantity > FLOOR(IFNULL(low_stock_threshold, 10) * 0.5) AND quantity <= IFNULL(low_stock_threshold, 10)')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.inventory.partials.alert-table-rows', compact('products'))->render(),
                // appends() so paging past page 1 keeps the alert type / search / sort the
                // user is looking at instead of silently widening the list.
                'pagination' => $products->appends($request->query())->links('pagination::bootstrap-5')->toHtml(),
            ]);
        }

        return view('admin.inventory.stock-alerts', compact('products', 'stats'));
    }

    /**
     * Display stock history.
     */
    public function stockHistory(Request $request)
    {
        // Get inventory history from database with product info
        $history = DB::table('inventory_history as ih')
            ->select('ih.*', 'p.name as product_name', 'p.sku as product_sku')
            ->leftJoin('products as p', 'ih.product_id', '=', 'p.id')
            ->orderBy('ih.created_at', 'desc');

        // Filter by product
        if ($request->product_id) {
            $history->where('ih.product_id', $request->product_id);
        }

        // Filter by action type
        if ($request->action_type) {
            $history->where('ih.action_type', $request->action_type);
        }

        // Filter by date range
        if ($request->date_from) {
            $history->whereDate('ih.created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $history->whereDate('ih.created_at', '<=', $request->date_to);
        }

        $perPage = $request->per_page ?? 25;
        $history = $history->paginate($perPage);

        // Get products for filter
        $products = Product::orderBy('name')->get();

        // Stats
        // stock_out rows store a negative change, so the "out" total has to be absolute —
        // summing them raw rendered a Minus sign in a column that reads "stock out".
        $stats = [
            'total_in' => (int) DB::table('inventory_history')->where('action_type', 'stock_in')->sum('quantity_change'),
            'total_out' => (int) abs(DB::table('inventory_history')->where('action_type', 'stock_out')->sum('quantity_change')),
            'adjustments' => (int) DB::table('inventory_history')->where('action_type', 'adjustment')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.inventory.partials.history-table-rows', compact('history'))->render(),
                'pagination' => $history->appends($request->query())->links('pagination::bootstrap-5')->toHtml(),
            ]);
        }

        return view('admin.inventory.stock-history', compact('history', 'products', 'stats'));
    }

    /**
     * Quick stock adjustment for a single product.
     */
    public function adjustStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);
        $stock = app(StockService::class);
        $quantity = (int) $request->quantity;
        $reason = $request->reason ?? 'Manual adjustment';

        // Through StockService so quantity, stock_status, stock_update_date and the history row
        // all move together: the old code wrote stock_status = 'low_stock', a value the column
        // cannot hold, so any adjustment that left the quantity unchanged died with
        // "Data truncated for column 'stock_status'" (HTTP 500), and subtracting more than the
        // shelf held recorded the requested amount instead of the real one.
        try {
            switch ($request->adjustment_type) {
                case 'add':
                    $change = $stock->restore($product, $quantity, $reason);
                    break;
                case 'subtract':
                    $change = -$stock->deduct($product, $quantity, $reason);
                    break;
                default:
                    $change = $stock->setQuantity($product, $quantity, $reason);
                    break;
            }
        } catch (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $product->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted successfully',
            'new_quantity' => (int) $product->quantity,
            'change' => $change,
            'stock_status' => $product->stock_status,
        ]);
    }

    /**
     * Bulk stock adjustment.
     */
    public function bulkAdjust(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $reason = $request->reason ?? 'Bulk adjustment';
        $quantity = (int) $request->quantity;
        $stock = app(StockService::class);
        $updated = 0;
        $skipped = [];

        // One transaction for the whole batch: a bulk edit that half applies is worse than one
        // that reports which lines it could not do.
        DB::beginTransaction();
        try {
            foreach ($request->product_ids as $productId) {
                $product = Product::find($productId);
                if (! $product) {
                    continue;
                }

                try {
                    switch ($request->adjustment_type) {
                        case 'add':
                            $stock->restore($product, $quantity, $reason);
                            break;
                        case 'subtract':
                            $stock->deduct($product, $quantity, $reason);
                            break;
                        default:
                            $stock->setQuantity($product, $quantity, $reason);
                            break;
                    }
                    $updated++;
                } catch (InsufficientStockException $e) {
                    $skipped[] = $e->getMessage();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => "Stock adjusted for {$updated} product(s)"
                . ($skipped ? ', ' . count($skipped) . ' skipped' : ''),
            'updated_count' => $updated,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Get product details for quick edit modal.
     */
    public function getProduct($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }
        
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $product->quantity,
                'low_stock_threshold' => $product->low_stock_threshold,
                'stock_status' => $product->stock_status,
            ],
        ]);
    }

    /**
     * Update low stock threshold.
     */
    public function updateThreshold(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        $product = Product::findOrFail($request->product_id);
        $product->update(['low_stock_threshold' => $request->low_stock_threshold]);

        return response()->json([
            'success' => true,
            'message' => 'Low stock threshold updated',
        ]);
    }

    /**
     * Apply a whitelisted ORDER BY. sort/direction arrive from the query string and were passed
     * straight to orderBy(), so ?sort=nonexistent_col or ?per_page=abc answered 500.
     */
    private function applySort($query, Request $request, array $columns, string $default, string $defaultDirection): void
    {
        $sort = in_array($request->sort, $columns, true) ? $request->sort : $default;
        $direction = in_array($request->direction, ['asc', 'desc'], true) ? $request->direction : $defaultDirection;

        $query->orderBy($sort, $direction);
    }

    /**
     * Page size, limited to the values the screen offers.
     */
    private function perPage(Request $request): int
    {
        $perPage = (int) $request->per_page;

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
    }
}
