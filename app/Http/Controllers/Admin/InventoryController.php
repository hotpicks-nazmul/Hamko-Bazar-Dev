<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // Sorting
        $sort = $request->sort ?? 'updated_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $products = $query->paginate($perPage);

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

        // Sorting
        $sort = $request->sort ?? 'quantity';
        $direction = $request->direction ?? 'asc';
        $query->orderBy($sort, $direction);

        $perPage = $request->per_page ?? 25;
        $products = $query->paginate($perPage);

        // Stats for alerts using dynamic threshold
        $stats = [
            'critical' => Product::where('quantity', '<=', 0)->count(),
            'warning' => Product::whereRaw('quantity > 0 AND quantity <= FLOOR(IFNULL(low_stock_threshold, 10) * 0.5)')->count(),
            'notice' => Product::whereRaw('quantity > FLOOR(IFNULL(low_stock_threshold, 10) * 0.5) AND quantity <= IFNULL(low_stock_threshold, 10)')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.inventory.partials.alert-table-rows', compact('products'))->render(),
                'pagination' => $products->links('pagination::bootstrap-5')->toHtml(),
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
        $stats = [
            'total_in' => DB::table('inventory_history')->where('action_type', 'stock_in')->sum('quantity_change'),
            'total_out' => DB::table('inventory_history')->where('action_type', 'stock_out')->sum('quantity_change'),
            'adjustments' => DB::table('inventory_history')->where('action_type', 'adjustment')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.inventory.partials.history-table-rows', compact('history'))->render(),
                'pagination' => $history->links('pagination::bootstrap-5')->toHtml(),
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
        $oldQuantity = $product->quantity;
        $adjustment = (int) $request->quantity;
        $reason = $request->reason ?? 'Manual adjustment';

        // Calculate new quantity
        switch ($request->adjustment_type) {
            case 'add':
                $newQuantity = $oldQuantity + $adjustment;
                $actionType = 'stock_in';
                break;
            case 'subtract':
                $newQuantity = max(0, $oldQuantity - $adjustment);
                $actionType = 'stock_out';
                $adjustment = -$adjustment;
                break;
            case 'set':
                $newQuantity = $adjustment;
                $actionType = 'adjustment';
                $adjustment = $newQuantity - $oldQuantity;
                break;
        }

        // Update product quantity
        $threshold = $product->low_stock_threshold ?? 10;
        $product->update([
            'quantity' => $newQuantity,
            'stock_status' => $newQuantity > $threshold ? 'in_stock' : ($newQuantity > 0 ? 'low_stock' : 'out_of_stock'),
            'stock_update_date' => now(),
        ]);

        // Record history
        DB::table('inventory_history')->insert([
            'product_id' => $product->id,
            'action_type' => $actionType,
            'quantity_before' => $oldQuantity,
            'quantity_after' => $newQuantity,
            'quantity_change' => $adjustment,
            'reason' => $reason,
            'performed_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted successfully',
            'new_quantity' => $newQuantity,
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
        $adjustment = (int) $request->quantity;
        $updated = 0;

        foreach ($request->product_ids as $productId) {
            $product = Product::find($productId);
            if (!$product) continue;

            $oldQuantity = $product->quantity;

            switch ($request->adjustment_type) {
                case 'add':
                    $newQuantity = $oldQuantity + $adjustment;
                    $actionType = 'stock_in';
                    $changeAmount = $adjustment;
                    break;
                case 'subtract':
                    $newQuantity = max(0, $oldQuantity - $adjustment);
                    $actionType = 'stock_out';
                    $changeAmount = -$adjustment;
                    break;
                case 'set':
                    $newQuantity = $adjustment;
                    $actionType = 'adjustment';
                    $changeAmount = $newQuantity - $oldQuantity;
                    break;
            }

            $threshold = $product->low_stock_threshold ?? 10;
            $product->update([
                'quantity' => $newQuantity,
                'stock_status' => $newQuantity > $threshold ? 'in_stock' : ($newQuantity > 0 ? 'low_stock' : 'out_of_stock'),
                'stock_update_date' => now(),
            ]);

            // Record history
            DB::table('inventory_history')->insert([
                'product_id' => $product->id,
                'action_type' => $actionType,
                'quantity_before' => $oldQuantity,
                'quantity_after' => $newQuantity,
                'quantity_change' => $changeAmount,
                'reason' => $reason,
                'performed_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => "Stock adjusted for {$updated} product(s)",
            'updated_count' => $updated,
        ]);
    }

    /**
     * Get product details for quick edit modal.
     */
    public function getProduct($id)
    {
        $product = Product::findOrFail($id);
        
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
}
