<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\PaymentGateway;
use App\Models\Setting;
use App\Services\ThemeService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected $theme;
    protected $paymentService;

    public function __construct(ThemeService $theme, PaymentService $paymentService)
    {
        $this->theme = $theme;
        $this->paymentService = $paymentService;
    }

    /**
     * Display checkout page.
     */
    public function index()
    {
        $this->mergeSessionCart();
        $cart = $this->getCart();

        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $paymentGateways = PaymentGateway::getActive();
        $user = Auth::user();
        $addresses = $user ? $user->addresses : collect();

        return $this->theme->view('checkout.index', compact('cart', 'paymentGateways', 'user', 'addresses'));
    }

    /**
     * Merge session cart into user cart if both exist.
     */
    protected function mergeSessionCart()
    {
        if (!Auth::check()) {
            return;
        }

        $sessionCartId = session()->get('cart_id');
        if (!$sessionCartId) {
            return;
        }

        $sessionCart = Cart::find($sessionCartId);
        if (!$sessionCart || empty($sessionCart->items)) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $userItems = $userCart->items ?? [];
        $sessionItems = $sessionCart->items ?? [];

        // Merge session items into user cart
        foreach ($sessionItems as $sessionItem) {
            $found = false;
            foreach ($userItems as &$userItem) {
                if ($userItem['product_id'] == $sessionItem['product_id']) {
                    $userItem['quantity'] += $sessionItem['quantity'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $userItems[] = $sessionItem;
            }
        }

        $userCart->items = $userItems;
        $userCart->save();

        // Clear session cart
        $sessionCart->items = [];
        $sessionCart->save();
        session()->forget('cart_id');
    }

    /**
     * Process checkout.
     */
    public function process(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'billing_first_name' => 'required|string|max:255',
            'billing_last_name' => 'required|string|max:255',
            'billing_email' => 'required|email|max:255',
            'billing_phone' => 'required|string|max:20',
            'billing_address' => 'required|string|max:500',
            'billing_city' => 'required|string|max:100',
            'billing_state' => 'required|string|max:100',
            'billing_postcode' => 'required|string|max:20',
            'billing_country' => 'required|string|max:100',
            'shipping_method' => 'nullable|string|in:home_delivery,local_pickup',
            'payment_method' => 'required|string',
            'terms' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $cart = $this->getCart();

        if ($cart->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.',
            ], 400);
        }

        // Get order configuration settings
        $minOrderAmount = (float) Setting::get('min_order_amount', 0);
        $maxOrderAmount = (float) Setting::get('max_order_amount', 0);
        $subtotal = $cart->getSubtotal();

        // Validate minimum order amount
        if ($minOrderAmount > 0 && $subtotal < $minOrderAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum order amount is ' . config('app.currency_symbol', '৳') . number_format($minOrderAmount, 2),
            ], 400);
        }

        // Validate maximum order amount
        if ($maxOrderAmount > 0 && $subtotal > $maxOrderAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum order amount is ' . config('app.currency_symbol', '৳') . number_format($maxOrderAmount, 2),
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = $cart->getSubtotal();
            $tax = $subtotal * (Setting::get('tax_rate', 0) / 100);
            $shippingMethod = $request->input('shipping_method', 'home_delivery');
            $shippingCost = $this->calculateShipping($subtotal, $shippingMethod, $request->billing_city, $request->billing_state);
            $discount = 0;

            // Apply coupon
            $coupon = null;
            if ($request->coupon_code) {
                $coupon = Coupon::findValidByCode($request->coupon_code);
                if ($coupon && $coupon->isApplicable($subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal);
                }
            }

            $total = $subtotal + $tax + $shippingCost - $discount;

            // Create order
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_type' => 'inhouse',
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'shipping_method' => $shippingMethod,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'coupon_code' => $coupon ? $coupon->code : null,
                'total' => $total,
                'billing_first_name' => $request->billing_first_name,
                'billing_last_name' => $request->billing_last_name,
                'billing_email' => $request->billing_email,
                'billing_phone' => str_starts_with($request->billing_phone, '880') || str_starts_with($request->billing_phone, '+880') || str_starts_with($request->billing_phone, '+88') || str_starts_with($request->billing_phone, '88') ? $request->billing_phone : '880' . $request->billing_phone,
                'billing_address' => $request->billing_address,
                'billing_city' => $request->billing_city,
                'billing_state' => $request->billing_state,
                'billing_postcode' => $request->billing_postcode,
                'billing_country' => $request->billing_country,
                'shipping_first_name' => $request->shipping_first_name ?? $request->billing_first_name,
                'shipping_last_name' => $request->shipping_last_name ?? $request->billing_last_name,
                'shipping_email' => $request->shipping_email ?? $request->billing_email,
                'shipping_phone' => $request->shipping_phone ? (str_starts_with($request->shipping_phone, '880') || str_starts_with($request->shipping_phone, '+880') || str_starts_with($request->shipping_phone, '+88') || str_starts_with($request->shipping_phone, '88') ? $request->shipping_phone : '880' . $request->shipping_phone) : ('880' . $request->billing_phone),
                'shipping_address' => $request->shipping_address ?? $request->billing_address,
                'shipping_city' => $request->shipping_city ?? $request->billing_city,
                'shipping_state' => $request->shipping_state ?? $request->billing_state,
                'shipping_postcode' => $request->shipping_postcode ?? $request->billing_postcode,
                'shipping_country' => $request->shipping_country ?? $request->billing_country,
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($cart->items as $item) {
                $product = \App\Models\Product::find($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'] ?? ($product ? $product->name : 'Unknown'),
                    'variation' => isset($item['variant_data']) ? json_encode($item['variant_data']) : null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);

                // Update product stock
                if ($product) {
                    $product->decrement('quantity', $item['quantity']);
                }
            }

            // Increment coupon usage
            if ($coupon) {
                $coupon->incrementUsage();
            }

            // Clear cart
            $cart->clear();

            // Process payment
            $gateway = PaymentGateway::findBySlug($request->payment_method);
            if ($gateway) {
                $this->paymentService->setGateway($gateway);
                $result = $this->paymentService->initialize($order);

                if ($result['success']) {
                    $order->update(['payment_gateway' => $gateway->slug]);
                    DB::commit();

                    if (isset($result['redirect_url'])) {
                        return response()->json([
                            'success' => true,
                            'redirect' => $result['redirect_url'],
                        ]);
                    }

                    return response()->json([
                        'success' => true,
                        'redirect' => route('checkout.success', $order->id),
                    ]);
                }

                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Payment initialization failed.',
                ], 400);
            }

            // COD or no payment gateway
            DB::commit();
            return response()->json([
                'success' => true,
                'redirect' => route('checkout.success', $order->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Display success page.
     */
    public function success($orderId)
    {
        $order = Order::with('items.product')->findOrFail($orderId);

        return $this->theme->view('checkout.success', compact('order'));
    }

    /**
     * Cancel order.
     */
    public function cancel($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->canBeCancelled()) {
            $order->update(['status' => 'cancelled']);
            return redirect()->route('home')->with('success', 'Order cancelled successfully.');
        }

        return redirect()->route('home')->with('error', 'Order cannot be cancelled.');
    }

    /**
     * Calculate shipping cost.
     */
    protected function calculateShipping($subtotal, $shippingMethod = null, $city = null, $state = null)
    {
        $freeShippingEnabled = Setting::get('free_shipping_enabled', '0') === '1';
        $freeShippingMinAmount = (float) Setting::get('free_shipping_min_amount', 0);
        $defaultShippingCost = (float) Setting::get('default_shipping_cost', 0);
        $shippingCalculationType = Setting::get('shipping_calculation_type', 'flat');
        $localPickupEnabled = Setting::get('local_pickup_enabled', '0') === '1';
        $localPickupCost = (float) Setting::get('local_pickup_cost', 0);

        if ($freeShippingEnabled && $freeShippingMinAmount > 0 && $subtotal >= $freeShippingMinAmount) {
            return 0;
        }

        if ($shippingMethod === 'local_pickup') {
            return $localPickupEnabled ? $localPickupCost : 0;
        }

        if ($shippingCalculationType === 'location' && !empty($city)) {
            $zone = \App\Models\DeliveryZone::where('name', 'like', '%' . $city . '%')->first();
            if ($zone) {
                return (float) $zone->cost;
            }
        }

        return $defaultShippingCost;
    }

    /**
     * Get shipping options for checkout.
     */
    public function getShippingOptions(Request $request)
    {
        $subtotal = (float) $request->get('subtotal', 0);
        $city = $request->get('city', '');
        $state = $request->get('state', '');

        $options = [];

        $deliveryCost = $this->calculateShipping($subtotal, 'home_delivery', $city, $state);
        $options[] = [
            'id' => 'home_delivery',
            'name' => 'Home Delivery',
            'cost' => $deliveryCost,
            'estimated_days' => '3-5 business days',
        ];

        $localPickupEnabled = Setting::get('local_pickup_enabled', '0') === '1';
        if ($localPickupEnabled) {
            $pickupCost = (float) Setting::get('local_pickup_cost', 0);
            $options[] = [
                'id' => 'local_pickup',
                'name' => 'Local Pickup',
                'cost' => $pickupCost,
                'estimated_days' => 'Ready in 1-2 hours',
            ];
        }

        return response()->json([
            'success' => true,
            'options' => $options,
        ]);
    }

    /**
     * Get current user's cart.
     */
    protected function getCart()
    {
        $cartId = session()->get('cart_id');
        $sessionCart = null;
        if ($cartId) {
            $sessionCart = Cart::find($cartId);
        }

        if (Auth::check()) {
            $userCart = Cart::firstOrCreate(['user_id' => Auth::id()]);

            // Merge session cart into user cart if session cart has items
            if ($sessionCart && !empty($sessionCart->items)) {
                $userItems = $userCart->items ?? [];
                $sessionItems = $sessionCart->items ?? [];

                foreach ($sessionItems as $sessionItem) {
                    $found = false;
                    foreach ($userItems as &$userItem) {
                        if ($userItem['product_id'] == $sessionItem['product_id']) {
                            $userItem['quantity'] += $sessionItem['quantity'];
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $userItems[] = $sessionItem;
                    }
                }

                $userCart->items = $userItems;
                $userCart->save();

                // Clear session cart
                $sessionCart->items = [];
                $sessionCart->save();
                session()->forget('cart_id');
            }

            return $userCart;
        }

        if ($sessionCart) {
            return $sessionCart;
        }

        return Cart::create(['items' => []]);
    }
}
