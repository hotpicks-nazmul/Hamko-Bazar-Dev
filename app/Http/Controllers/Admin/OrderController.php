<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\StockService;
use App\Http\Controllers\Admin\Concerns\ListQueryParams;

class OrderController extends Controller
{
    use ListQueryParams;

    /**
     * Get statistics for inhouse orders
     */
    protected function getInhouseStats()
    {
        return [
            'total' => Order::inhouse()->count(),
            'pending' => Order::inhouse()->where('status', 'pending')->count(),
            'processing' => Order::inhouse()->where('status', 'processing')->count(),
            'confirmed' => Order::inhouse()->where('status', 'confirmed')->count(),
            'shipped' => Order::inhouse()->where('status', 'shipped')->count(),
            'delivered' => Order::inhouse()->where('status', 'delivered')->count(),
            'cancelled' => Order::inhouse()->where('status', 'cancelled')->count(),
            'refunded' => Order::inhouse()->where('status', 'refunded')->count(),
        ];
    }

    /**
     * Get statistics for all orders
     */
    protected function getStats()
    {
        return [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'refunded' => Order::where('status', 'refunded')->count(),
        ];
    }

    /**
     * Get filtered stats based on current query filters.
     */
    protected function getFilteredStats($query)
    {
        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'processing' => (clone $query)->where('status', 'processing')->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'shipped' => (clone $query)->where('status', 'shipped')->count(),
            'delivered' => (clone $query)->where('status', 'delivered')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'refunded' => (clone $query)->where('status', 'refunded')->count(),
        ];
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with('user', 'warehouse', 'items');

        // Auto-scope to warehouse if logged-in user has warehouse_id
        $authUser = auth()->user();
        if ($authUser && $authUser->warehouse_id) {
            $query->where('warehouse_id', $authUser->warehouse_id);
        }

        // Filter by warehouse (admin/super_admin can filter)
        if ($request->warehouse_id && (!$authUser || !$authUser->warehouse_id)) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Search by order number, customer name, email, phone
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('billing_first_name', 'like', "%{$search}%")
                  ->orWhere('billing_last_name', 'like', "%{$search}%")
                  ->orWhere('billing_email', 'like', "%{$search}%")
                  ->orWhere('billing_phone', 'like', "%{$search}%")
                  ->orWhere('shipping_first_name', 'like', "%{$search}%")
                  ->orWhere('shipping_last_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Check for CSV export — this used to call the seller export, so the file carried a Seller
        // column (N/A) and the wrong column order for an all-orders listing.
        if ($request->get('export') === 'csv') {
            return $this->exportCsv($query);
        }

        $this->applySort($query, $request, ['created_at', 'order_number', 'total', 'status',
            'payment_status', 'billing_first_name', 'updated_at', 'id']);

        // Pagination
        $orders = $query->paginate($this->perPage($request));

        // Get filtered stats
        $stats = $this->getFilteredStats(clone $query);

        $warehouses = Warehouse::active()->ordered()->get(['id', 'name']);

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.orders.partials.order-rows', compact('orders'))->render(),
                'pagination' => $orders->links()->toHtml(),
                'stats' => $stats
            ]);
        }

        return view('admin.orders.index', compact('orders', 'stats', 'warehouses'));
    }

    /**
     * Export orders to CSV.
     */
    protected function exportCsv($query)
    {
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'orders_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($orders) {
            $handle = fopen('php://output', 'w');
            
            // Header row
            fputcsv($handle, [
                'Order #',
                'Customer Name',
                'Email',
                'Phone',
                'Total',
                'Status',
                'Payment Status',
                'Payment Method',
                'Date'
            ]);
            
            // Data rows
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->billing_full_name,
                    $order->billing_email,
                    $order->billing_phone,
                    $order->total,
                    $order->status,
                    $order->payment_status,
                    $order->payment_method ?? 'N/A',
                    $order->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($handle);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display inhouse orders listing.
     */
    public function inHouse(Request $request)
    {
        $query = Order::inhouse()->with('user', 'warehouse', 'items');

        $authUser = auth()->user();
        if ($authUser && $authUser->warehouse_id) {
            $query->where('warehouse_id', $authUser->warehouse_id);
        }

        if ($request->warehouse_id && (!$authUser || !$authUser->warehouse_id)) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Search by order number, customer name, email, phone
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('billing_first_name', 'like', "%{$search}%")
                  ->orWhere('billing_last_name', 'like', "%{$search}%")
                  ->orWhere('billing_email', 'like', "%{$search}%")
                  ->orWhere('billing_phone', 'like', "%{$search}%")
                  ->orWhere('shipping_first_name', 'like', "%{$search}%")
                  ->orWhere('shipping_last_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Check for CSV export
        if ($request->get('export') === 'csv') {
            return $this->exportInhouseCsv($query);
        }

        $this->applySort($query, $request, ['created_at', 'order_number', 'total', 'status',
            'payment_status', 'billing_first_name', 'updated_at', 'id']);

        // Stats follow the same filters as the rows. The first load used to answer with global
        // counts while the AJAX refresh was filtered and warehouse-scoped, so the cards changed
        // meaning the first time somebody touched a filter.
        $stats = $this->getInhouseFilteredStats(clone $query);

        // Pagination
        $orders = $query->paginate($this->perPage($request));

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.orders.partials.inhouse-order-rows', compact('orders'))->render(),
                'pagination' => $orders->links()->toHtml(),
                'stats' => $stats
            ]);
        }

        return view('admin.orders.inhouse.index', compact('orders', 'stats'));
    }

    /**
     * Get filtered stats for inhouse orders based on current query.
     */
    protected function getInhouseFilteredStats($query)
    {
        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'processing' => (clone $query)->where('status', 'processing')->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'shipped' => (clone $query)->where('status', 'shipped')->count(),
            'delivered' => (clone $query)->where('status', 'delivered')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'refunded' => (clone $query)->where('status', 'refunded')->count(),
        ];
    }

    /**
     * Export inhouse orders to CSV.
     */
    protected function exportInhouseCsv($query)
    {
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'inhouse_orders_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($orders) {
            $handle = fopen('php://output', 'w');
            
            fputcsv($handle, [
                'Order #',
                'Customer Name',
                'Email',
                'Phone',
                'Total',
                'Status',
                'Payment Status',
                'Payment Method',
                'Date'
            ]);
            
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->billing_full_name,
                    $order->billing_email,
                    $order->billing_phone,
                    $order->total,
                    $order->status,
                    $order->payment_status,
                    $order->payment_method ?? 'N/A',
                    $order->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($handle);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show the form for creating a new inhouse order.
     */
    public function create()
    {
        $products = Product::where('is_active', true)
            ->select('id', 'name', 'sku', 'price', 'featured_image', 'quantity')
            ->orderBy('name')
            ->get();
        
        $customers = User::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        return view('admin.orders.inhouse.create', compact('products', 'customers'));
    }

    /**
     * Store a newly created inhouse order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'billing_first_name' => 'required|string|max:255',
            'billing_last_name' => 'required|string|max:255',
            'billing_email' => 'required|email|max:255',
            'billing_phone' => 'required|string|max:20',
            'billing_address' => 'required|string|max:500',
            'billing_city' => 'required|string|max:100',
            'billing_state' => 'required|string|max:100',
            'billing_postcode' => 'required|string|max:20',
            'billing_country' => 'required|string|max:100',
            'payment_method' => 'required|string|max:50',
            'payment_status' => 'required|in:pending,paid,failed',
        ]);

        // Validate stock availability (re-checked under lock inside the transaction below)
        foreach ($request->products as $item) {
            $product = Product::find($item['product_id']);
            if ($product->quantity < $item['quantity']) {
                return back()->with('error', "Insufficient stock for product: {$product->name}. Available: {$product->quantity}, Requested: {$item['quantity']}")->withInput();
            }
        }

        // Generate order number
        $orderNumber = 'ORD-' . strtoupper(Str::random(8));

        // Shipping cost (free for now, can be configured)
        $shippingCost = 0;

        // One transaction. Stock used to be deducted per line before the order row existed, so a
        // failure mid-way left the shelf short with nothing to point at.
        $order = DB::transaction(function () use ($request, $orderNumber, $shippingCost) {
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->products as $item) {
                $product = Product::whereKey($item['product_id'])->lockForUpdate()->first();
                $quantity = (int) $item['quantity'];

                if (! $product || $quantity > (int) $product->quantity) {
                    throw ValidationException::withMessages([
                        'products' => 'Not enough stock for ' . ($product->name ?? 'a product') . ' any more.',
                    ]);
                }

                // final_price follows sale_price, exactly like the cart and checkout, so an
                // in-house order costs the same as the one the customer would place.
                $price = (float) $product->final_price;
                $itemTotal = $price * $quantity;
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'variation' => $item['variation'] ?? null,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $itemTotal,
                ];
            }

            // Create order
            $order = Order::create([
            'order_number' => $orderNumber,
            'user_id' => $request->customer_id,
            'order_type' => 'inhouse',
            'status' => 'pending',
            'payment_status' => $request->payment_status,
            'payment_method' => $request->payment_method,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $subtotal + $shippingCost,
            'billing_first_name' => $request->billing_first_name,
            'billing_last_name' => $request->billing_last_name,
            'billing_email' => $request->billing_email,
            'billing_phone' => $request->billing_phone,
            'billing_address' => $request->billing_address,
            'billing_city' => $request->billing_city,
            'billing_state' => $request->billing_state,
            'billing_postcode' => $request->billing_postcode,
            'billing_country' => $request->billing_country,
            'shipping_first_name' => $request->shipping_first_name ?? $request->billing_first_name,
            'shipping_last_name' => $request->shipping_last_name ?? $request->billing_last_name,
            'shipping_email' => $request->shipping_email ?? $request->billing_email,
            'shipping_phone' => $request->shipping_phone ?? $request->billing_phone,
            'shipping_address' => $request->shipping_address ?? $request->billing_address,
            'shipping_city' => $request->shipping_city ?? $request->billing_city,
            'shipping_state' => $request->shipping_state ?? $request->billing_state,
            'shipping_postcode' => $request->shipping_postcode ?? $request->billing_postcode,
            'shipping_country' => $request->shipping_country ?? $request->billing_country,
            'notes' => $request->notes,
            // Warehouse staff only see orders for their own warehouse, so an order created
            // without one vanished from the list that created it.
            'warehouse_id' => auth()->user()->warehouse_id ?: null,
        ]);

        // Create order items, then take the stock — inside the same transaction, with the
        // history rows the ledger expects.
        foreach ($orderItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'variation' => $item['variation'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['total'],
            ]);

            app(StockService::class)->deduct(
                Product::find($item['product_id']),
                (int) $item['quantity'],
                'In-house order ' . $orderNumber
            );
        }

            return $order;
        });

        return redirect()->route('admin.orders.in-house.show', $order->id)
            ->with('success', 'Order created successfully.');
    }

    /**
     * Display inhouse order details.
     */
    public function inHouseShow(Order $order)
    {
        $order->load('user', 'items.product', 'warehouse', 'billingArea.city');
        $warehouses = Warehouse::active()->ordered()->get();
        return view('admin.orders.inhouse.show', compact('order', 'warehouses'));
    }

    /**
     * Search products for adding to order.
     */
    public function getProductDetail($id)
    {
        $product = Product::with(['visibleColors' => function ($q) {
                $q->withPivot(['image', 'quantity', 'price_adjustment', 'sku']);
            }, 'visibleAttributeValues' => function ($q) {
                $q->withPivot(['price', 'quantity', 'image', 'sku']);
            }, 'visibleAttributeValues.attribute'])
            ->findOrFail($id);

        $colors = $product->visibleColors->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'hex_code' => $c->hex_code,
                'image' => $c->pivot->image,
                'quantity' => $c->pivot->quantity,
                'price_adjustment' => (float) ($c->pivot->price_adjustment ?? 0),
                'sku' => $c->pivot->sku,
            ];
        });

        $attrs = [];
        foreach ($product->visibleAttributeValues->groupBy('attribute.name') as $attrName => $values) {
            $attrs[] = [
                'name' => $attrName,
                'values' => $values->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'value' => $v->value,
                        'price' => (float) ($v->pivot->price ?? 0),
                        'quantity' => $v->pivot->quantity,
                        'image' => $v->pivot->image,
                    ];
                })->values(),
            ];
        }

        $image = $product->featured_image;
        if ($image && !str_starts_with($image, '/storage/') && !str_starts_with($image, 'http')) {
            $image = '/storage/' . $image;
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) ($product->sale_price ?: $product->price),
                'base_price' => (float) $product->price,
                'stock' => (int) $product->quantity,
                'image' => $image,
                'colors' => $colors,
                'attributes' => $attrs,
            ],
        ]);
    }

    public function searchProducts(Request $request)
    {
        $request->validate(['q' => 'required|string|max:100']);
        $search = $request->q;

        $products = Product::active()
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'sku', 'price', 'sale_price', 'thumbnail', 'featured_image', 'quantity']);

        return response()->json([
            'success' => true,
            'products' => $products->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'price' => $p->sale_price ?: $p->price,
                    'stock' => $p->quantity,
                    'image' => $p->thumbnail ?: $p->featured_image,
                ];
            }),
        ]);
    }

    /**
     * Add a product to an in-house order.
     */
    public function inHouseAddItem(Request $request, Order $order)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'nullable|numeric|min:0',
        ]);

        $product = Product::findOrFail($request->product_id);
        $price = $request->price ?? ($product->sale_price ?: $product->price);
        $quantity = $request->quantity;

        if ($quantity > $product->quantity) {
            return back()->with('error', "Insufficient stock. Only {$product->quantity} available.");
        }

        // Check if product already exists in order
        $existingItem = OrderItem::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingItem) {
            $newQty = $existingItem->quantity + $quantity;
            if ($newQty > $product->quantity) {
                return back()->with('error', "Insufficient stock. Only {$product->quantity} available (currently {$existingItem->quantity} in order).");
            }
            $existingItem->update([
                'quantity' => $newQty,
                'price' => $price,
                'total' => $price * $newQty,
            ]);
        } else {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'price' => $price,
                'total' => $price * $quantity,
            ]);
        }

        app(StockService::class)->deduct($product, (int) $quantity, 'Added to order ' . $order->order_number);
        $this->recalculateOrderTotals($order);

        return back()->with('success', "{$product->name} added to order.");
    }

    /**
     * Add multiple products to an order at once.
     */
    public function inHouseAddItems(Request $request, Order $order)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
        ]);

        $added = 0;
        foreach ($request->items as $itemData) {
            $product = Product::find($itemData['product_id']);
            if (!$product) continue;

            $price = $itemData['price'] ?? ($product->sale_price ?: $product->price);
            $quantity = $itemData['quantity'];

            if ($quantity > $product->quantity) {
                continue;
            }

            $existingItem = OrderItem::where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $quantity,
                    'total' => $price * ($existingItem->quantity + $quantity),
                ]);
            } else {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $price * $quantity,
                ]);
            }

            app(StockService::class)->deduct($product, (int) $quantity, 'Added to order ' . $order->order_number);
            $added++;
        }

        $this->recalculateOrderTotals($order);

        return response()->json(['success' => true, 'message' => "{$added} product(s) added to order."]);
    }

    /**
     * Update an order item (quantity, variation).
     */
    public function inHouseUpdateItem(Request $request, Order $order, OrderItem $item)
    {
        if ($item->order_id !== $order->id) {
            abort(404);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'price' => 'nullable|numeric|min:0',
        ]);

        $product = $item->product;
        $oldQty = $item->quantity;
        $newQty = $request->quantity;
        $diff = $newQty - $oldQty;

        if ($product && $diff > 0 && $diff > $product->quantity) {
            return back()->with('error', "Insufficient stock. Only {$product->quantity} additional available.");
        }

        if ($product) {
            if ($diff > 0) {
                app(StockService::class)->deduct($product, (int) $diff, 'Order ' . $order->order_number . ' quantity raised');
            } elseif ($diff < 0) {
                app(StockService::class)->restore($product, (int) abs($diff), 'Order ' . $order->order_number . ' quantity lowered');
            }
        }

        $price = $request->price ?? $item->price;
        $item->update([
            'quantity' => $newQty,
            'price' => $price,
            'total' => $price * $newQty,
        ]);

        $this->recalculateOrderTotals($order);

        return response()->json(['success' => true, 'message' => 'Order item updated.']);
    }

    /**
     * Remove an item from an in-house order.
     */
    public function inHouseRemoveItem(Order $order, OrderItem $item)
    {
        if ($item->order_id !== $order->id) {
            abort(404);
        }

        $product = $item->product;
        if ($product) {
            app(StockService::class)->restore($product, (int) $item->quantity, 'Removed from order ' . $order->order_number);
        }

        $item->delete();
        $this->recalculateOrderTotals($order);

        return back()->with('success', 'Item removed from order.');
    }

    /**
     * Change the warehouse assigned to an order.
     */
    public function inHouseChangeWarehouse(Request $request, Order $order)
    {
        $request->validate([
            'warehouse_id' => 'required|integer|exists:warehouses,id',
        ]);

        $order->update(['warehouse_id' => $request->warehouse_id]);

        return back()->with('success', 'Warehouse changed successfully.');
    }

    /**
     * Recalculate order totals after item changes.
     */
    protected function recalculateOrderTotals(Order $order)
    {
        $items = OrderItem::where('order_id', $order->id)->get();
        $subtotal = $items->sum('total');

        $order->update([
            'subtotal' => $subtotal,
            'total' => $subtotal + $order->shipping_cost + $order->tax - $order->discount,
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load('user', 'items.product', 'warehouse', 'billingArea.city');
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, Order $order)
    {
        // Whitelist. `$order->update($request->all())` let one request rewrite total, discount,
        // user_id, status and payment_status (all fillable) while validating four address fields —
        // and writing status this way skipped the stock handling in updateStatus().
        $data = $request->validate([
            'billing_first_name' => 'sometimes|string|max:255',
            'billing_last_name' => 'sometimes|string|max:255',
            'billing_email' => 'sometimes|nullable|email|max:255',
            'billing_phone' => 'sometimes|string|max:20',
            'billing_address' => 'sometimes|string|max:500',
            'billing_city' => 'sometimes|string|max:100',
            'billing_state' => 'sometimes|string|max:100',
            'billing_postcode' => 'sometimes|nullable|string|max:20',
            'billing_country' => 'sometimes|string|max:100',
            'shipping_first_name' => 'sometimes|string|max:255',
            'shipping_last_name' => 'sometimes|string|max:255',
            'shipping_email' => 'sometimes|nullable|email|max:255',
            'shipping_phone' => 'sometimes|string|max:20',
            'shipping_address' => 'sometimes|string|max:500',
            'shipping_city' => 'sometimes|string|max:100',
            'shipping_state' => 'sometimes|string|max:100',
            'shipping_postcode' => 'sometimes|nullable|string|max:20',
            'shipping_country' => 'sometimes|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $order->update($data);

        return back()->with('success', 'Order updated successfully.');
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            // Must mirror the orders.status enum: pending, processing, confirmed, shipped,
            // delivered, cancelled, refunded. There is deliberately no 'completed' value —
            // a write of status='completed' fails at MySQL with "Data truncated for column".
            'status' => 'required|in:pending,processing,confirmed,shipped,delivered,cancelled,refunded',
            'payment_status' => 'sometimes|in:pending,paid,failed,refunded',
        ]);

        $this->applyStatus($order, $request->status, $request->input('payment_status'));

        return back()->with('success', 'Order status updated.');
    }

    /**
     * Move an order to a status (and optionally a payment status) with the stock consequences.
     *
     * One implementation for every writer: the detail page, the row dropdown and the bulk action
     * each used to do their own thing, and only the first of them moved stock.
     */
    protected function applyStatus(Order $order, string $status, ?string $paymentStatus = null): void
    {
        // The rule lives in the service so the refund flow can use the same one instead of a copy.
        app(\App\Services\OrderStatusService::class)->apply($order, $status, $paymentStatus);
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        // Not a bare $order->update(): a gateway order holds no stock until the money lands, so
        // marking it paid here is the moment the shelf has to be charged.
        $this->applyStatus($order, $order->status, $request->payment_status);

        return back()->with('success', 'Payment status updated.');
    }

    /**
     * Generate invoice.
     */
    public function invoice(Order $order)
    {
        $order->load('user', 'items.product');

        // Settings → Order Configuration → the invoice block. Prefix, terms and the logo/barcode
        // switches were saved and read by nobody, so the invoice always printed the bare order
        // number with no terms whatever the admin had configured.
        $invoiceSettings = [
            'prefix' => (string) \App\Models\Setting::get('invoice_prefix', ''),
            'terms' => (string) \App\Models\Setting::get('invoice_terms', ''),
            'show_logo' => (string) \App\Models\Setting::get('show_invoice_logo', '0') === '1',
            'show_barcode' => (string) \App\Models\Setting::get('show_invoice_barcode', '0') === '1',
            'logo' => (string) (\App\Models\Setting::get('site_logo', '') ?: \App\Models\Setting::get('site_favicon', '')),
            'store_name' => (string) (\App\Models\Setting::get('site_name', '') ?: config('app.name')),
        ];

        $invoiceNumber = trim($invoiceSettings['prefix'] . $order->order_number);

        return view('admin.orders.invoice', compact('order', 'invoiceSettings', 'invoiceNumber'));
    }

    /**
     * Ship order.
     */
    public function ship(Request $request, Order $order)
    {
        $request->validate([
            'tracking_number' => 'required|string|max:100',
            'shipping_company' => 'required|string|max:100',
        ]);

        // Shipping a cancelled order used to resurrect it and leave the stock the cancel returned
        // sitting on the shelf while the order travelled.
        if (in_array($order->status, ['cancelled', 'refunded', 'delivered'], true)) {
            return back()->with('error', "An order that is {$order->status} cannot be shipped.");
        }

        if ($order->items()->count() === 0) {
            return back()->with('error', 'This order has no items to ship.');
        }

        $order->update([
            'tracking_number' => $request->tracking_number,
            'shipping_company' => $request->shipping_company,
            'status' => 'shipped',
        ]);

        return back()->with('success', 'Order shipped successfully.');
    }

    /**
     * Get product details for AJAX.
     */
    public function getProduct(Request $request)
    {
        $product = Product::find($request->product_id);
        
        if ($product) {
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'stock' => $product->current_stock,
                    'image' => $product->featured_image ? '/storage/' . $product->featured_image : null,
                ]
            ]);
        }
        
        return response()->json(['success' => false, 'message' => 'Product not found']);
    }

    /**
     * Get customer details for AJAX.
     */
    public function getCustomer(Request $request)
    {
        $customer = User::find($request->customer_id);
        
        if ($customer) {
            return response()->json([
                'success' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'country' => $customer->country,
                    'postal_code' => $customer->postal_code,
                ]
            ]);
        }
        
        return response()->json(['success' => false, 'message' => 'Customer not found']);
    }

    /**
     * Search customers for AJAX.
     */
    public function searchCustomers(Request $request)
    {
        $search = $request->get('q', '');
        
        $customers = User::where('status', 'active')
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'email', 'phone']);
        
        return response()->json([
            'success' => true,
            'customers' => $customers
        ]);
    }

    /**
     * Get statistics for seller orders
     */
    protected function getSellerStats()
    {
        return [
            'total' => Order::seller()->count(),
            'pending' => Order::seller()->where('status', 'pending')->count(),
            'processing' => Order::seller()->where('status', 'processing')->count(),
            'confirmed' => Order::seller()->where('status', 'confirmed')->count(),
            'shipped' => Order::seller()->where('status', 'shipped')->count(),
            'delivered' => Order::seller()->where('status', 'delivered')->count(),
            'cancelled' => Order::seller()->where('status', 'cancelled')->count(),
            'refunded' => Order::seller()->where('status', 'refunded')->count(),
        ];
    }

    /**
     * Get filtered stats based on current query filters for seller orders.
     */
    protected function getFilteredSellerStats($query)
    {
        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'processing' => (clone $query)->where('status', 'processing')->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'shipped' => (clone $query)->where('status', 'shipped')->count(),
            'delivered' => (clone $query)->where('status', 'delivered')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'refunded' => (clone $query)->where('status', 'refunded')->count(),
        ];
    }

    /**
     * Display seller orders listing.
     */
    public function seller(Request $request)
    {
        $query = Order::seller()->with(['user', 'items.product']);

        // Search by order number, customer name, email, phone
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('billing_first_name', 'like', "%{$search}%")
                  ->orWhere('billing_last_name', 'like', "%{$search}%")
                  ->orWhere('billing_email', 'like', "%{$search}%")
                  ->orWhere('billing_phone', 'like', "%{$search}%")
                  ->orWhere('shipping_first_name', 'like', "%{$search}%")
                  ->orWhere('shipping_last_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by seller
        if ($request->seller_id) {
            $query->whereHas('items.product', function($q) use ($request) {
                $q->where('seller_id', $request->seller_id);
            });
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // The Export CSV button on this screen carried ?export=csv and nothing read it, so the
        // button reloaded the page instead of downloading the file.
        if ($request->get('export') === 'csv') {
            return $this->exportSellerOrdersCsv($query);
        }

        $this->applySort($query, $request, ['created_at', 'order_number', 'total', 'status',
            'payment_status', 'billing_first_name', 'updated_at', 'id']);

        // Stats follow the filters and the page load agrees with the AJAX refresh.
        $stats = $this->getFilteredSellerStats(clone $query);

        $orders = $query->paginate($this->perPage($request));

        // Get sellers for filter dropdown
        $sellers = User::sellers()->select('id', 'name', 'email')->get();

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.orders.partials.seller-order-rows', compact('orders'))->render(),
                'pagination' => $orders->links()->toHtml(),
                'stats' => $stats
            ]);
        }

        return view('admin.orders.seller.index', compact('orders', 'stats', 'sellers'));
    }

    /**
     * Export seller orders to CSV.
     */
    protected function exportSellerOrdersCsv($query)
    {
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'seller_orders_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = ['Order #', 'Customer Name', 'Customer Email', 'Phone', 'Seller', 'Total', 'Payment Status', 'Order Status', 'Date'];

        $callback = function() use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                $sellers = [];
                foreach ($order->items as $item) {
                    if ($item->product && $item->product->seller) {
                        $sellers[] = $item->product->seller->name;
                    }
                }
                $sellers = array_unique($sellers);

                fputcsv($file, [
                    $order->order_number,
                    $order->billing_full_name,
                    $order->billing_email,
                    $order->billing_phone,
                    implode(', ', $sellers) ?: 'N/A',
                    $order->total,
                    $order->payment_status,
                    $order->status,
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display seller order details.
     */
    public function sellerShow(Order $order)
    {
        // Ensure this is a seller order
        if ($order->order_type !== 'seller') {
            return redirect()->route('admin.orders.seller')
                ->with('error', 'This order is not a seller order.');
        }

        $order->load(['user', 'items.product.seller']);
        return view('admin.orders.seller.show', compact('order'));
    }

    /**
     * Get statistics for pickup point orders
     */
    protected function getPickupPointStats()
    {
        return [
            'total' => Order::pickupPoint()->count(),
            'pending' => Order::pickupPoint()->where('status', 'pending')->count(),
            'processing' => Order::pickupPoint()->where('status', 'processing')->count(),
            'confirmed' => Order::pickupPoint()->where('status', 'confirmed')->count(),
            'ready' => Order::pickupPoint()->where('status', 'confirmed')->whereNull('picked_up_at')->count(),
            'picked_up' => Order::pickupPoint()->whereNotNull('picked_up_at')->count(),
            'cancelled' => Order::pickupPoint()->where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Get filtered stats based on current query filters for pickup point orders.
     */
    protected function getFilteredPickupPointStats($query)
    {
        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'processing' => (clone $query)->where('status', 'processing')->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'ready' => (clone $query)->where('status', 'confirmed')->whereNull('picked_up_at')->count(),
            'picked_up' => (clone $query)->whereNotNull('picked_up_at')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Display pickup point orders listing.
     */
    public function pickupPoint(Request $request)
    {
        $query = Order::pickupPoint()->with(['user', 'pickupPointLocation']);

        // Search by order number, customer name, email, phone
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('billing_first_name', 'like', "%{$search}%")
                  ->orWhere('billing_last_name', 'like', "%{$search}%")
                  ->orWhere('billing_email', 'like', "%{$search}%")
                  ->orWhere('billing_phone', 'like', "%{$search}%")
                  ->orWhere('picked_up_by', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            if ($request->status === 'picked_up') {
                $query->whereNotNull('picked_up_at');
            } elseif ($request->status === 'ready') {
                $query->where('status', 'confirmed')->whereNull('picked_up_at');
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by payment status
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by pickup point
        if ($request->pickup_point_id) {
            $query->where('pickup_point_id', $request->pickup_point_id);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->get('export') === 'csv') {
            return $this->exportPickupPointCsv($query);
        }

        $this->applySort($query, $request, ['created_at', 'order_number', 'total', 'status',
            'payment_status', 'picked_up_at', 'updated_at', 'id']);

        $stats = $this->getFilteredPickupPointStats(clone $query);

        $orders = $query->paginate($this->perPage($request));

        // Get pickup points for filter dropdown
        $pickupPoints = \App\Models\PickupPoint::active()->orderBy('name')->get();

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.orders.partials.pickup-point-order-rows', compact('orders'))->render(),
                'pagination' => $orders->links()->toHtml(),
                'stats' => $stats
            ]);
        }

        return view('admin.orders.pickup-point.index', compact('orders', 'stats', 'pickupPoints'));
    }

    /**
     * Export pickup point orders to CSV — the screen's Export button pointed at a branch that
     * did not exist, so the list re-rendered (and, with any picked-up row, answered 500).
     */
    protected function exportPickupPointCsv($query)
    {
        $orders = $query->with(['pickupPointLocation', 'items'])->orderBy('created_at', 'desc')->get();

        $filename = 'pickup_point_orders_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = ['Order #', 'Customer Name', 'Email', 'Phone', 'Pickup Point', 'Items',
            'Total', 'Payment Status', 'Order Status', 'Picked Up At', 'Picked Up By', 'Date'];

        $callback = function () use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->billing_full_name,
                    $order->billing_email,
                    $order->billing_phone,
                    $order->pickupPointLocation->name ?? 'N/A',
                    $order->items->sum('quantity'),
                    $order->total,
                    $order->payment_status,
                    $order->status,
                    $order->picked_up_at ? $order->picked_up_at->format('Y-m-d H:i:s') : '',
                    $order->picked_up_by ?? '',
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display pickup point order details.
     */
    public function pickupPointShow(Order $order)
    {
        // Ensure this is a pickup point order
        if ($order->order_type !== 'pickup_point') {
            return redirect()->route('admin.orders.pickup-point')
                ->with('error', 'This order is not a pickup point order.');
        }

        $order->load(['user', 'items.product', 'pickupPointLocation']);
        return view('admin.orders.pickup-point.show', compact('order'));
    }

    /**
     * Mark order as picked up.
     */
    public function markAsPickedUp(Request $request, Order $order)
    {
        $request->validate([
            'picked_up_by' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        // A cancelled order that gets marked picked up comes back to life as delivered, with the
        // stock its cancellation returned still sitting on the shelf.
        if (in_array($order->status, ['cancelled', 'refunded'], true)) {
            return back()->with('error', "A {$order->status} order cannot be marked as picked up.");
        }

        if ($order->picked_up_at) {
            return back()->with('error', 'This order has already been picked up.');
        }

        // Status goes through the shared path so stock and the ledger stay in step.
        $this->applyStatus($order, 'delivered');

        $order->update([
            'picked_up_at' => now(),
            'picked_up_by' => $request->picked_up_by,
        ]);

        // Append notes if provided
        if ($request->notes) {
            $existingNotes = $order->notes ? $order->notes . "\n\n" : '';
            $order->update([
                'notes' => $existingNotes . 'Pickup Notes (' . now()->format('d M Y, H:i') . '): ' . $request->notes
            ]);
        }

        return back()->with('success', 'Order marked as picked up successfully.');
    }

    /**
     * Bulk update order status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            // 'ready' was allowed here but is not in the orders.status enum (the write answered
            // 500), while shipped and refunded — which are — were refused.
            'status' => 'required|string|in:pending,processing,confirmed,shipped,delivered,cancelled,refunded',
        ]);

        $count = 0;

        foreach (Order::whereIn('id', $request->order_ids)->get() as $order) {
            // Query-builder update() here skipped every side effect: a bulk cancel left the stock
            // consumed and wrote no history row while the single-order route restored it.
            $this->applyStatus($order, $request->status);
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} order(s) updated to {$request->status}",
        ]);
    }
}
