<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    /**
     * Display the POS terminal.
     */
    public function terminal(Request $request)
    {
        $categories = \App\Models\Category::where('status', 1)
            ->orderBy('name')
            ->get();

        $cart = $this->getCart();

        return view('admin.pos.terminal', compact('categories', 'cart'));
    }

    /**
     * Search products for POS.
     */
    public function searchProducts(Request $request)
    {
        $search = $request->search ?? '';
        $categoryId = $request->category_id ?? null;

        $query = Product::where('status', 1)
            ->where('isPublished', 1)
            ->where('stock', '>', 0);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->select('id', 'name', 'sku', 'barcode', 'featured_image', 'unit_price', 'purchase_price', 'stock')
            ->orderBy('name')
            ->limit(50)
            ->get();

        // Add image URL to each product
        $products->map(function($product) {
            $product->image_url = $this->getImageUrl($product->featured_image);
            return $product;
        });

        return response()->json([
            'products' => $products
        ]);
    }

    /**
     * Add product to cart.
     */
    public function addToCart(Request $request)
    {
        $productId = $request->product_id;
        $quantity = $request->quantity ?? 1;

        $product = Product::findOrFail($productId);

        if ($product->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock'
            ], 400);
        }

        $cart = $this->getCart();
        $cartItemKey = 'product_' . $product->id;

        if (isset($cart[$cartItemKey])) {
            $cart[$cartItemKey]['quantity'] += $quantity;
            if ($cart[$cartItemKey]['quantity'] > $product->stock) {
                $cart[$cartItemKey]['quantity'] = $product->stock;
            }
        } else {
            $cart[$cartItemKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->unit_price,
                'purchase_price' => $product->purchase_price ?? 0,
                'quantity' => $quantity,
                'image' => $this->getImageUrl($product->featured_image),
            ];
        }

        Session::put('pos_cart', $cart);

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'cart_count' => count($cart),
            'cart_total' => $this->calculateCartTotal($cart)
        ]);
    }

    /**
     * Update cart item quantity.
     */
    public function updateCartItem(Request $request)
    {
        $productId = $request->product_id;
        $quantity = $request->quantity;

        $product = Product::findOrFail($productId);

        if ($quantity > $product->stock) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Available: ' . $product->stock
            ], 400);
        }

        $cart = $this->getCart();
        $cartItemKey = 'product_' . $productId;

        if (isset($cart[$cartItemKey])) {
            if ($quantity <= 0) {
                unset($cart[$cartItemKey]);
            } else {
                $cart[$cartItemKey]['quantity'] = $quantity;
            }
        }

        Session::put('pos_cart', $cart);

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'cart_count' => count($cart),
            'cart_total' => $this->calculateCartTotal($cart)
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(Request $request)
    {
        $productId = $request->product_id;

        $cart = $this->getCart();
        $cartItemKey = 'product_' . $productId;

        if (isset($cart[$cartItemKey])) {
            unset($cart[$cartItemKey]);
        }

        Session::put('pos_cart', $cart);

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'cart_count' => count($cart),
            'cart_total' => $this->calculateCartTotal($cart)
        ]);
    }

    /**
     * Clear the cart.
     */
    public function clearCart()
    {
        Session::forget('pos_cart');

        return response()->json([
            'success' => true,
            'cart' => [],
            'cart_count' => 0,
            'cart_total' => 0
        ]);
    }

    /**
     * Apply discount to cart.
     */
    public function applyDiscount(Request $request)
    {
        $discountType = $request->discount_type; // 'percentage' or 'fixed'
        $discountValue = $request->discount_value ?? 0;

        $cart = $this->getCart();
        $subtotal = $this->calculateCartTotal($cart);

        if ($discountType === 'percentage') {
            $discount = ($subtotal * $discountValue) / 100;
        } else {
            $discount = min($discountValue, $subtotal);
        }

        Session::put('pos_discount', [
            'type' => $discountType,
            'value' => $discountValue,
            'amount' => $discount
        ]);

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'subtotal' => $subtotal,
            'total' => $subtotal - $discount
        ]);
    }

    /**
     * Process checkout and create order.
     */
    public function processCheckout(Request $request)
    {
        $cart = $this->getCart();

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash,card,digital_wallet',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $subtotal = $this->calculateCartTotal($cart);
        $discountData = Session::get('pos_discount', ['type' => 'fixed', 'value' => 0, 'amount' => 0]);
        $discount = $discountData['amount'] ?? 0;
        $total = $subtotal - $discount;

        $paidAmount = floatval($request->paid_amount);
        
        if ($paidAmount < $total) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient payment. Total: ' . number_format($total, 2) . ', Paid: ' . number_format($paidAmount, 2)
            ], 400);
        }

        $change = $paidAmount - $total;

        // Generate order number
        $orderNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        // Create order
        $order = Order::create([
            'user_id' => null, // POS orders may not have user
            'order_number' => $orderNumber,
            'order_type' => 'pos',
            'billing_first_name' => $request->customer_name,
            'billing_last_name' => '',
            'billing_email' => $request->customer_email ?? 'pos@localhost',
            'billing_phone' => $request->customer_phone ?? '',
            'billing_address' => '',
            'billing_city' => '',
            'billing_state' => '',
            'billing_postcode' => '',
            'billing_country' => 'Bangladesh',
            'shipping_first_name' => $request->customer_name,
            'shipping_last_name' => '',
            'shipping_email' => $request->customer_email ?? 'pos@localhost',
            'shipping_phone' => $request->customer_phone ?? '',
            'shipping_address' => '',
            'shipping_city' => '',
            'shipping_state' => '',
            'shipping_postcode' => '',
            'shipping_country' => 'Bangladesh',
            'subtotal' => $subtotal,
            'shipping_cost' => 0,
            'tax' => 0,
            'discount' => $discount,
            'total' => $total,
            'payment_method' => $request->payment_method,
            'payment_gateway' => $request->payment_method,
            'payment_status' => 'paid',
            'transaction_id' => 'POS-' . Str::random(10),
            'status' => 'delivered', // POS orders are completed immediately
            'notes' => $request->notes ?? 'POS Order',
        ]);

        // Create order items
        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'product_sku' => $item['sku'] ?? '',
                'price' => $item['price'],
                'purchase_price' => $item['purchase_price'] ?? 0,
                'quantity' => $item['quantity'],
                'total' => $item['price'] * $item['quantity'],
            ]);

            // Update product stock
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->stock -= $item['quantity'];
                $product->save();
            }
        }

        // Clear cart and discount
        Session::forget('pos_cart');
        Session::forget('pos_discount');

        // Store transaction for receipt
        $transaction = [
            'order_id' => $order->id,
            'order_number' => $orderNumber,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'paid_amount' => $paidAmount,
            'change' => $change,
            'payment_method' => $request->payment_method,
            'items' => $cart,
            'created_at' => now(),
        ];
        
        $transactions = Session::get('pos_transactions', []);
        array_unshift($transactions, $transaction);
        Session::put('pos_transactions', array_slice($transactions, 0, 100)); // Keep last 100

        return response()->json([
            'success' => true,
            'order' => $order,
            'receipt' => [
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'change' => $change,
                'payment_method' => $request->payment_method,
                'items' => $cart,
            ]
        ]);
    }

    /**
     * Display cash register / transaction history.
     */
    public function cashRegister(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        
        // Get database orders as primary source (persistent data)
        $dbOrders = Order::where('order_type', 'pos')
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate stats from database orders
        $totalSales = $dbOrders->sum('total');
        $totalCash = $dbOrders->where('payment_method', 'cash')->sum('total');
        $totalCard = $dbOrders->where('payment_method', 'card')->sum('total');
        $totalDigital = $dbOrders->where('payment_method', 'digital_wallet')->sum('total');
        $transactionCount = $dbOrders->count();

        // Also get session transactions for current session (fallback)
        $sessionTransactions = Session::get('pos_transactions', []);
        $sessionTransactions = array_filter($sessionTransactions, function($t) use ($date) {
            return date('Y-m-d', strtotime($t['created_at'])) === $date;
        });

        return view('admin.pos.cash-register', compact(
            'dbOrders',
            'sessionTransactions',
            'date',
            'totalSales',
            'totalCash',
            'totalCard',
            'totalDigital',
            'transactionCount'
        ));
    }

    /**
     * Display POS reports.
     */
    public function reports(Request $request)
    {
        $dateFrom = $request->date_from ?? date('Y-m-01');
        $dateTo = $request->date_to ?? date('Y-m-d');

        // Get POS orders from database
        $orders = Order::where('order_type', 'pos')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate stats
        $totalSales = $orders->sum('total');
        $totalOrders = $orders->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        
        // Payment method breakdown
        $cashSales = $orders->where('payment_method', 'cash')->sum('total');
        $cardSales = $orders->where('payment_method', 'card')->sum('total');
        $digitalSales = $orders->where('payment_method', 'digital_wallet')->sum('total');

        // Daily breakdown
        $dailySales = $orders->groupBy(function($order) {
            return date('Y-m-d', strtotime($order->created_at));
        })->map(function($dayOrders) {
            return [
                'count' => $dayOrders->count(),
                'total' => $dayOrders->sum('total'),
            ];
        });

        // Top selling products
        $orderItems = OrderItem::whereIn('order_id', $orders->pluck('id'))
            ->select('product_id', 'product_name', \DB::raw('SUM(quantity) as total_qty'), \DB::raw('SUM(total) as total_sales'))
            ->groupBy('product_id', 'product_name')
            ->orderBy('total_sales', 'desc')
            ->limit(10)
            ->get();

        return view('admin.pos.reports', compact(
            'orders',
            'dateFrom',
            'dateTo',
            'totalSales',
            'totalOrders',
            'averageOrderValue',
            'cashSales',
            'cardSales',
            'digitalSales',
            'dailySales',
            'orderItems'
        ));
    }

    /**
     * Get current cart from session.
     */
    private function getCart()
    {
        return Session::get('pos_cart', []);
    }

    /**
     * Calculate cart total.
     */
    private function calculateCartTotal($cart)
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    /**
     * Get proper image URL.
     */
    private function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return null;
        }
        
        if (str_starts_with($imagePath, '/storage/') || str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }
        
        return '/storage/' . $imagePath;
    }
}