<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Setting;
use App\Models\City;
use App\Models\Area;
use App\Models\Country;
use App\Models\State;
use App\Models\Warehouse;
use App\Services\PaymentService;

class CheckoutController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $cart = $this->getCart();

        if (!$cart || $cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $paymentGateways = \App\Models\PaymentGateway::where('is_active', true)->get();
        $user = auth()->user();

        $checkoutMode = Setting::get('checkout_mode', 'local');
        $defaultCountryId = Setting::get('default_country', '');
        $defaultCountry = Country::find($defaultCountryId);
        $defaultCountryName = $defaultCountry?->name ?? 'Bangladesh';

        if ($checkoutMode === 'local') {
            $cities = City::active()->where('country_id', $defaultCountryId)->ordered()->get(['id', 'name']);
        } else {
            $cities = City::active()->ordered()->get(['id', 'name', 'country']);
        }

        $countries = [];
        if ($checkoutMode === 'international') {
            $countries = Country::ordered()->get();
        }

        $addresses = collect();
        if ($user) {
            $addresses = $user->addresses()->orderBy('is_default', 'desc')->get();
        }

        return view('themes.general.checkout.index', compact(
            'cart', 'paymentGateways', 'user', 'cities', 'countries',
            'checkoutMode', 'defaultCountryName', 'addresses'
        ));
    }

    public function process(Request $request)
    {
        $request->validate([
            'billing_first_name' => 'required|string|max:255',
            'billing_last_name' => 'required|string|max:255',
            'billing_email' => 'nullable|email|max:255',
            'billing_phone' => 'required|string|max:11',
            'billing_address' => 'required|string|max:500',
            'billing_city_id' => 'required|exists:cities,id',
            'billing_area_id' => 'required|exists:areas,id',
            'billing_city' => 'required|string|max:100',
            'billing_state' => 'required|string|max:100',
            'billing_postcode' => 'nullable|string|max:20',
            'billing_country' => 'required|string|max:100',
            'payment_method' => 'required|string|in:cod,bkash,sslcommerz,nagad,rocket',
            'terms' => 'accepted',
        ]);

        $cart = $this->getCart();

        if (!$cart || $cart->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 400);
        }

        $freeShippingEnabled = Setting::get('free_shipping_enabled', '0') === '1';
        $freeShippingMinAmount = (float) Setting::get('free_shipping_min_amount', 0);
        $defaultShippingCost = (float) Setting::get('default_shipping_cost', 0);
        $subtotal = $cart->getSubtotal();
        $shipping = ($freeShippingEnabled && $freeShippingMinAmount > 0 && $subtotal >= $freeShippingMinAmount) ? 0 : $defaultShippingCost;
        $tax = $cart->getTax();
        $total = $subtotal + $shipping + $tax;

        $warehouseId = $this->detectWarehouse($request->billing_city_id, $request->billing_area_id, $request->billing_city);

        $order = DB::transaction(function () use ($request, $cart, $subtotal, $shipping, $tax, $total, $warehouseId) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => Order::generateOrderNumber(),
                'billing_first_name' => $request->billing_first_name,
                'billing_last_name' => $request->billing_last_name,
                'billing_email' => $request->billing_email,
                'billing_phone' => str_starts_with($request->billing_phone, '880') || str_starts_with($request->billing_phone, '+880') || str_starts_with($request->billing_phone, '+88') || str_starts_with($request->billing_phone, '88') ? $request->billing_phone : '880' . $request->billing_phone,
                'billing_address' => $request->billing_address,
                'billing_city' => $request->billing_city,
                'billing_city_id' => $request->billing_city_id,
                'billing_state' => $request->billing_state,
                'billing_postcode' => $request->billing_postcode,
                'billing_country' => $request->billing_country,
                'billing_area_id' => $request->billing_area_id,
                'shipping_first_name' => $request->billing_first_name,
                'shipping_last_name' => $request->billing_last_name,
                'shipping_email' => $request->billing_email,
                'shipping_phone' => $request->shipping_phone ? (str_starts_with($request->shipping_phone, '880') || str_starts_with($request->shipping_phone, '+880') || str_starts_with($request->shipping_phone, '+88') || str_starts_with($request->shipping_phone, '88') ? $request->shipping_phone : '880' . $request->shipping_phone) : (str_starts_with($request->billing_phone, '880') || str_starts_with($request->billing_phone, '+880') || str_starts_with($request->billing_phone, '+88') || str_starts_with($request->billing_phone, '88') ? $request->billing_phone : '880' . $request->billing_phone),
                'shipping_address' => $request->billing_address,
                'shipping_city' => $request->billing_city,
                'shipping_city_id' => $request->billing_city_id,
                'shipping_state' => $request->billing_state,
                'shipping_postcode' => $request->billing_postcode,
                'shipping_country' => $request->billing_country,
                'shipping_area_id' => $request->billing_area_id,
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'status' => 'pending',
                'warehouse_id' => $warehouseId,
            ]);

            $cartItems = $cart->items ?? [];
            foreach ($cartItems as $item) {
                $product = Product::find($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'] ?? ($product ? $product->name : 'Unknown'),
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }

            // Auto-save billing address to address book
            try {
                $authUser = Auth::user();
                if ($authUser) {
                    $phone = str_starts_with($request->billing_phone, '880') || str_starts_with($request->billing_phone, '+880') || str_starts_with($request->billing_phone, '+88') || str_starts_with($request->billing_phone, '88') ? $request->billing_phone : '880' . $request->billing_phone;
                    $exists = $authUser->addresses()
                        ->where('first_name', $request->billing_first_name ?? '')
                        ->where('last_name', $request->billing_last_name ?? '')
                        ->where('phone', $phone)
                        ->where('address', $request->billing_address)
                        ->where('city', $request->billing_city)
                        ->where('state', $request->billing_state)
                        ->where('postcode', $request->billing_postcode ?? '')
                        ->exists();
                    if (!$exists) {
                        $isFirst = $authUser->addresses()->count() === 0;
                        $authUser->addresses()->create([
                            'first_name' => $request->billing_first_name ?? '',
                            'last_name' => $request->billing_last_name ?? '',
                            'email' => $request->billing_email ?? $authUser->email,
                            'phone' => $phone,
                            'address' => $request->billing_address,
                            'city' => $request->billing_city,
                            'state' => $request->billing_state,
                            'postcode' => $request->billing_postcode ?? '',
                            'country' => $request->billing_country ?? 'Bangladesh',
                            'is_default' => $isFirst,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Address save failed: ' . $e->getMessage());
            }

            return $order;
        });

        if ($request->payment_method === 'bkash') {
            // bKash uses JS SDK popup — no redirect URL, return paymentID instead
            $result = $this->paymentService->createBkashPayment($order);

            if ($result && !isset($result['error']) && isset($result['paymentID'])) {
                return response()->json([
                    'success' => true,
                    'method' => 'bkash',
                    'paymentID' => $result['paymentID'],
                    'sdk_url' => $result['sdk_url'] ?? null,
                    'order_id' => $order->id,
                    'amount' => (string) $order->total,
                ]);
            }

            // Payment initiation failed — delete the order
            try {
                $order->orderItems()->delete();
                $order->delete();
            } catch (\Exception $e) {
                \Log::error('Failed to clean up order after bKash payment failure', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json(['success' => false, 'message' => 'bKash payment initialization failed. Please try again.'], 400);
        }

        if ($request->payment_method !== 'cod') {
            $paymentUrl = $this->paymentService->initiatePayment($order, $request->payment_method);

            if ($paymentUrl) {
                // Payment gateway: redirect user to pay — cart stays intact
                return response()->json(['success' => true, 'redirect' => $paymentUrl]);
            }

            // Payment failed — delete the order to prevent orphaned records
            try {
                $order->orderItems()->delete();
                $order->delete();
            } catch (\Exception $e) {
                \Log::error('Failed to clean up order after payment failure', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Payment initialization failed. Please try again.'], 400);
        }

        // COD — order placed immediately, clear cart and decrement stock
        $cartItems = $cart->items ?? [];
        $cart->clear();
        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->decrement('quantity', $item['quantity']);
            }
        }

        return response()->json(['success' => true, 'redirect' => route('checkout.success', $order->id)]);
    }

    private function detectWarehouse($cityId, $areaId = null, $cityName = null)
    {
        if ($areaId) {
            $warehouse = Warehouse::active()
                ->whereHas('areas', fn($q) => $q->where('area_id', $areaId))
                ->first();
            if ($warehouse) {
                return $warehouse->id;
            }
        }

        if ($cityId) {
            $warehouse = Warehouse::active()
                ->whereHas('cities', fn($q) => $q->where('city_id', $cityId))
                ->first();
            if ($warehouse) {
                return $warehouse->id;
            }
        }

        if ($cityName) {
            $warehouse = Warehouse::active()->where('city', $cityName)->first();
            if ($warehouse) {
                return $warehouse->id;
            }
        }

        $primary = Warehouse::active()->primary()->first();
        if ($primary) {
            return $primary->id;
        }

        return Warehouse::active()->first()->id ?? null;
    }

    private function getCart()
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        $sessionId = session()->getId();
        $cart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
        if ($cart) {
            return $cart;
        }

        $cart = Cart::create([
            'session_id' => $sessionId,
            'user_id' => null,
            'items' => [],
        ]);

        return $cart;
    }

    public function success($orderId)
    {
        $order = Order::findOrFail($orderId);
        return view('themes.general.checkout.success', compact('order'));
    }

    public function cancel()
    {
        return view('themes.general.checkout.cancel');
    }

    public function getStates(Request $request)
    {
        $query = State::active()->ordered();

        $checkoutMode = Setting::get('checkout_mode', 'local');
        if ($checkoutMode === 'local') {
            $defaultCountryId = Setting::get('default_country', '');
            if ($defaultCountryId) {
                $query->where('country_id', $defaultCountryId);
            }
        } elseif ($request->country) {
            $country = Country::where('name', $request->country)->first();
            if ($country) {
                $query->where('country_id', $country->id);
            }
        }

        $states = $query->get(['id', 'name']);

        return response()->json(['success' => true, 'states' => $states]);
    }

    public function getCities(Request $request)
    {
        $query = City::active()->ordered();

        if ($request->state_id) {
            $query->where('state_id', $request->state_id);
        } else {
            $checkoutMode = Setting::get('checkout_mode', 'local');
            if ($checkoutMode === 'local') {
                $defaultCountryId = Setting::get('default_country', '');
                if ($defaultCountryId) {
                    $query->where('country_id', $defaultCountryId);
                }
            } elseif ($request->country) {
                $country = Country::where('name', $request->country)->first();
                if ($country) {
                    $query->where('country_id', $country->id);
                }
            }
        }

        $cities = $query->get(['id', 'name']);

        return response()->json(['success' => true, 'cities' => $cities]);
    }

    public function getAreas(Request $request)
    {
        $query = Area::active()->ordered()->with('city');

        if ($request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        $areas = $query->get(['id', 'name', 'city_id']);

        return response()->json(['success' => true, 'areas' => $areas]);
    }

    public function getShippingOptions(Request $request)
    {
        $cityId = $request->city;
        $areaId = $request->area;
        $subtotal = (float) $request->subtotal;

        $freeShippingEnabled = Setting::get('free_shipping_enabled', '0') === '1';
        $freeShippingMinAmount = (float) Setting::get('free_shipping_min_amount', 0);
        $defaultShippingCost = (float) Setting::get('default_shipping_cost', 0);
        $localPickupEnabled = Setting::get('local_pickup_enabled', '0') === '1';
        $localPickupCost = (float) Setting::get('local_pickup_cost', 0);

        $options = [];

        $deliveryCost = ($freeShippingEnabled && $freeShippingMinAmount > 0 && $subtotal >= $freeShippingMinAmount) ? 0 : $defaultShippingCost;

        $options[] = [
            'id' => 'home_delivery',
            'name' => 'Home Delivery',
            'cost' => $deliveryCost,
            'estimated_days' => '3-5 business days',
        ];

        if ($localPickupEnabled) {
            $options[] = [
                'id' => 'local_pickup',
                'name' => 'Local Pickup',
                'cost' => $localPickupCost,
                'estimated_days' => 'Same day',
            ];
        }

        return response()->json(['success' => true, 'options' => $options]);
    }
}
