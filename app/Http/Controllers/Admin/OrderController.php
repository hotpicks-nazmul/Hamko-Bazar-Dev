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

class OrderController extends Controller
{
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
        $query = Order::with('user', 'warehouse');

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

        // Check for CSV export
        if ($request->get('export') === 'csv') {
            return $this->exportSellerOrdersCsv($query);
        }

        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $orders = $query->paginate($perPage);

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

        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $orders = $query->paginate($perPage);

        // Get filtered stats (for AJAX) or all stats (for page load)
        if ($request->ajax()) {
            $stats = $this->getInhouseFilteredStats($query);
        } else {
            $stats = $this->getInhouseStats();
        }

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

        // Validate stock availability
        foreach ($request->products as $item) {
            $product = Product::find($item['product_id']);
            if ($product->quantity < $item['quantity']) {
                return back()->with('error', "Insufficient stock for product: {$product->name}. Available: {$product->quantity}, Requested: {$item['quantity']}")->withInput();
            }
        }

        // Generate order number
        $orderNumber = 'ORD-' . strtoupper(Str::random(8));

        // Calculate totals
        $subtotal = 0;
        $orderItems = [];

        foreach ($request->products as $item) {
            $product = Product::find($item['product_id']);
            $quantity = $item['quantity'];
            $price = $product->price;
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

            // Update stock
            $product->decrement('quantity', $quantity);
        }

        // Shipping cost (free for now, can be configured)
        $shippingCost = 0;

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
        ]);

        // Create order items
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
        }

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

        $product->decrement('quantity', $quantity);
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

            $product->decrement('quantity', $quantity);
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
                $product->decrement('quantity', $diff);
            } elseif ($diff < 0) {
                $product->increment('quantity', abs($diff));
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
            $product->increment('quantity', $item->quantity);
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
        $request->validate([
            'shipping_name' => 'sometimes|string|max:255',
            'shipping_phone' => 'sometimes|string|max:20',
            'shipping_address' => 'sometimes|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        $order->update($request->all());

        return back()->with('success', 'Order updated successfully.');
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,confirmed,shipped,delivered,cancelled,refunded',
        ]);

        $updateData = ['status' => $request->status];

        if ($request->has('payment_status')) {
            $request->validate([
                'payment_status' => 'in:pending,paid,failed,refunded',
            ]);
            $updateData['payment_status'] = $request->payment_status;
        }

        $order->update($updateData);

        return back()->with('success', 'Order status updated.');
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $order->update(['payment_status' => $request->payment_status]);

        return back()->with('success', 'Payment status updated.');
    }

    /**
     * Generate invoice.
     */
    public function invoice(Order $order)
    {
        $order->load('user', 'items.product');
        return view('admin.orders.invoice', compact('order'));
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

        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $orders = $query->paginate($perPage);

        // Get stats - filtered for AJAX, all stats for page load
        if ($request->ajax()) {
            $stats = $this->getFilteredSellerStats($query);
        } else {
            $stats = $this->getSellerStats();
        }

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

        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $orders = $query->paginate($perPage);

        // Get stats - filtered for AJAX, all stats for page load
        if ($request->ajax()) {
            $stats = $this->getFilteredPickupPointStats($query);
        } else {
            $stats = $this->getPickupPointStats();
        }

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

        $order->update([
            'picked_up_at' => now(),
            'picked_up_by' => $request->picked_up_by,
            'status' => 'delivered',
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
            'status' => 'required|string|in:pending,processing,confirmed,ready,delivered,cancelled',
        ]);

        $count = Order::whereIn('id', $request->order_ids)->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} order(s) updated to {$request->status}",
        ]);
    }
}
