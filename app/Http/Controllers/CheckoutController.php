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
use App\Models\DeliveryZone;
use App\Models\PickupPoint;
use App\Services\PaymentService;
use App\Services\StockService;
use App\Exceptions\InsufficientStockException;

class CheckoutController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        // Settings → Order Configuration → guest checkout. The switch was saved and read by nothing,
        // so turning it off changed nothing. Absent row = allowed, which is today's behaviour.
        if (! $this->guestCheckoutAllowed()) {
            return redirect()->route('login')->with('error', 'Please log in to continue to checkout.');
        }

        $cart = $this->getCart();

        if (!$cart || $cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $paymentGateways = \App\Models\PaymentGateway::where('is_active', true)->get();
        $user = auth()->user();

        $checkoutMode = Setting::get('checkout_mode', 'local');
        $defaultCountryId = Setting::defaultCountryId();
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

        // The page used to print its own hardcoded rule here (৳60 flat, free over ৳500) that had
        // nothing to do with the shipping settings, so the customer could be quoted "Free" and
        // charged ৳60. Both prices now come from the same method the order uses.
        $defaultCity = optional($addresses->first())->city;
        $shippingOptions = $this->shippingOptionsFor($cart->getSubtotal(), $defaultCity ?: null);

        // Pick-up points are the network points a "Local Pickup" order is collected from. The
        // table, the model and orders.pickup_point_id all existed, and nothing ever offered a
        // choice, so every pick-up order had a null point and the admin's Pick-up Point screen
        // listed nothing.
        $pickupPoints = collect();
        if (Setting::get('local_pickup_enabled', '0') === '1') {
            $pickupPoints = PickupPoint::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'address', 'city', 'phone', 'opening_hours']);
        }

        return view('themes.general.checkout.index', compact(
            'cart', 'paymentGateways', 'user', 'cities', 'countries',
            'checkoutMode', 'defaultCountryName', 'addresses', 'shippingOptions', 'pickupPoints'
        ));
    }

    public function process(Request $request)
    {
        if (! $this->guestCheckoutAllowed()) {
            return response()->json([
                'success' => false,
                'message' => 'Please log in to place this order.',
                'errors' => ['auth' => ['Please log in to place this order.']],
            ], 401);
        }

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
            'shipping_method' => 'nullable|string|in:home_delivery,local_pickup',
            'pickup_point_id' => 'nullable|integer|exists:pickup_points,id',
            'terms' => 'accepted',
        ]);

        $cart = $this->getCart();

        if (!$cart || $cart->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 400);
        }

        // Refuse an order the shelf cannot fill. The cart guards when an item is added, but the
        // quantity goes stale while the cart sits (a sale elsewhere) and /cart/update does not
        // re-check stock, so this is the last place that can say no before money changes hands.
        $shortfalls = $this->stockShortfalls($cart);
        if ($shortfalls) {
            return response()->json([
                'success' => false,
                'message' => 'Some items are no longer available in the quantity you asked for. ' . implode(' ', $shortfalls),
                'stock_errors' => $shortfalls,
            ], 400);
        }

        // Settings → Order Configuration → the order limits. Every one of these was saved and read by
        // nobody, so a shop that set a minimum order value still took a ৳10 order. Absent or zero
        // limits mean "no limit", which keeps today's behaviour for a store that never set them.
        if ($limitError = $this->orderLimitError($cart)) {
            return response()->json($limitError, 422);
        }

        // Settings → Order Configuration → the status a brand-new order starts in.
        $initialStatus = (string) Setting::get('new_order_status', 'pending');
        if (! in_array($initialStatus, ['pending', 'confirmed', 'processing'], true)) {
            $initialStatus = 'pending';
        }

        // The charge follows the method the customer picked, priced by the same rules the
        // checkout quoted it with. Before this, "Local Pickup" still cost the home-delivery
        // rate and the order never recorded which method was chosen.
        $shippingMethod = $request->input('shipping_method') ?: 'home_delivery';
        $subtotal = $cart->getSubtotal();
        $shipping = $this->shippingCostFor($shippingMethod, $subtotal, $request->billing_city);
        $tax = $cart->getTax();
        $total = $subtotal + $shipping + $tax;

        // A pick-up order belongs to exactly one collection point. The page now offers the active
        // points when Local Pickup is chosen; refuse a pick-up order without one rather than
        // storing an order whose point is null (that is what the admin Pick-up Point screen had
        // nothing to show for). A shop with no points configured keeps the old behaviour: the
        // customer collects from the shop itself and the order stays an in-house order.
        $pickupPoint = null;
        if ($shippingMethod === 'local_pickup') {
            if ($request->filled('pickup_point_id')) {
                $pickupPoint = PickupPoint::active()->find($request->pickup_point_id);

                if (! $pickupPoint) {
                    return response()->json([
                        'success' => false,
                        'message' => 'That pick-up point is no longer available. Please choose another one.',
                        'errors' => ['pickup_point_id' => ['That pick-up point is no longer available.']],
                    ], 422);
                }
            } elseif (PickupPoint::active()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please choose a pick-up point for Local Pickup.',
                    'errors' => ['pickup_point_id' => ['Please choose a pick-up point for Local Pickup.']],
                ], 422);
            }
        }

        // The order type used to be left to the column default, so a storefront order matched none
        // of the order_type scopes and never appeared under In-house Orders. Every order now
        // declares what it is.
        $orderType = $pickupPoint ? 'pickup_point' : 'inhouse';

        $warehouseId = $this->detectWarehouse($request->billing_city_id, $request->billing_area_id, $request->billing_city);

        $order = DB::transaction(function () use ($request, $cart, $subtotal, $shipping, $tax, $total, $warehouseId, $shippingMethod, $pickupPoint, $orderType, $initialStatus) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => Order::generateOrderNumber(),
                'order_type' => $orderType,
                'pickup_point_id' => $pickupPoint?->id,
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
                // Collection point chosen: the shipping block is what the picker reads, so it
                // carries the point's address rather than the customer's home.
                'shipping_address' => $pickupPoint ? $pickupPoint->address : $request->billing_address,
                'shipping_city' => $pickupPoint ? $pickupPoint->city : $request->billing_city,
                'shipping_city_id' => $pickupPoint ? null : $request->billing_city_id,
                'shipping_state' => $pickupPoint ? $pickupPoint->state : $request->billing_state,
                'shipping_postcode' => $pickupPoint ? $pickupPoint->postcode : $request->billing_postcode,
                'shipping_country' => $pickupPoint ? $pickupPoint->country : $request->billing_country,
                'shipping_area_id' => $pickupPoint ? null : $request->billing_area_id,
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'tax' => $tax,
                'total' => $total,
                'shipping_method' => $shippingMethod,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'status' => $initialStatus,
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

        // COD — order placed immediately, clear cart and take the stock
        $cartItems = $cart->items ?? [];

        try {
            DB::transaction(function () use ($cartItems, $order) {
                foreach ($cartItems as $item) {
                    // Lock the row so two checkouts cannot both read the same shelf count.
                    $product = Product::whereKey($item['product_id'] ?? null)->lockForUpdate()->first();
                    if (! $product) {
                        continue;
                    }

                    app(StockService::class)->deduct(
                        $product,
                        (int) ($item['quantity'] ?? 0),
                        'Order ' . $order->order_number
                    );
                }
            });
        } catch (InsufficientStockException $e) {
            // Someone bought the last unit between the check above and here: drop the order
            // rather than leave a paid-looking row with no stock behind it.
            try {
                $order->orderItems()->delete();
                $order->delete();
            } catch (\Exception $cleanup) {
                \Log::error('Failed to clean up order after stock refusal', [
                    'order_id' => $order->id,
                    'error' => $cleanup->getMessage(),
                ]);
            }

            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        $cart->clear();

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

    /**
     * Lines in the basket whose requested quantity the shelf can no longer fill.
     *
     * @return array<int, string>
     */
    private function stockShortfalls($cart): array
    {
        $lines = [];

        foreach ($cart->items ?? [] as $item) {
            $lines[] = [
                'product' => Product::find($item['product_id'] ?? null),
                'quantity' => (int) ($item['quantity'] ?? 0),
            ];
        }

        return app(StockService::class)->shortfalls($lines);
    }

    /**
     * Settings → Order Configuration → "Guest checkout".
     *
     * A store that turns this off requires an account before the checkout page. A store that never
     * saved the setting keeps the old behaviour (guests welcome).
     */
    protected function guestCheckoutAllowed(): bool
    {
        if (Auth::check()) {
            return true;
        }

        return (string) Setting::get('guest_checkout_enabled', '1') !== '0';
    }

    /**
     * Settings → Order Configuration → the four order limits, expressed the way the admin screen
     * words them. Amounts compare against the cart subtotal; quantities are summed across lines.
     * Zero or absent means no limit in either direction.
     *
     * Returns null when the order is inside the limits, or [success,message,errors] for a 422.
     */
    protected function orderLimitError($cart): ?array
    {
        $subtotal = (float) $cart->getSubtotal();

        $quantity = 0;
        foreach (($cart->items ?? []) as $item) {
            $quantity += (int) ($item['quantity'] ?? 0);
        }

        $rules = [
            ['min_order_amount', 'Minimum order amount', (float) Setting::get('min_order_amount', 0), $subtotal, '<', 'amount'],
            ['max_order_amount', 'Maximum order amount', (float) Setting::get('max_order_amount', 0), $subtotal, '>', 'amount'],
            ['min_order_quantity', 'Minimum order quantity', (float) Setting::get('min_order_quantity', 0), (float) $quantity, '<', 'qty'],
            ['max_order_quantity', 'Maximum order quantity', (float) Setting::get('max_order_quantity', 0), (float) $quantity, '>', 'qty'],
        ];

        foreach ($rules as [$key, $label, $limit, $actual, $operator, $kind]) {
            if ($limit <= 0) {
                continue;
            }

            $breached = $operator === '<' ? $actual < $limit : $actual > $limit;
            if (! $breached) {
                continue;
            }

            $formatted = $kind === 'amount'
                ? (function_exists('format_price') ? format_price($limit) : number_format($limit, 2))
                : (string) (int) $limit;

            $message = $label . ' is ' . $formatted . '.';

            return [
                'success' => false,
                'message' => $message,
                'errors' => ['cart' => [$message]],
            ];
        }

        return null;
    }

    protected function getCart()
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
            $defaultCountryId = Setting::defaultCountryId();
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
                $defaultCountryId = Setting::defaultCountryId();
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
        // The subtotal used to come straight off the query string, so a caller could ask for the
        // free-shipping rate with ?subtotal=99999. Price from the cart; the parameter survives only
        // as a fallback for a page that has no cart yet.
        $cart = $this->getCart();
        $subtotal = $cart && ! $cart->isEmpty() ? (float) $cart->getSubtotal() : 0.0;

        return response()->json([
            'success' => true,
            'options' => $this->shippingOptionsFor($subtotal, $this->cityNameFrom($request->get('city'))),
        ]);
    }

    /**
     * The delivery choices this shop offers, priced by shippingCostFor().
     *
     * @return array<int, array{id: string, name: string, cost: float, estimated_days: string}>
     */
    private function shippingOptionsFor(float $subtotal, ?string $cityName = null): array
    {
        // Settings → Shipping → "Show delivery estimates". The switch was saved and read by nothing,
        // and was missing from the form entirely (so every save forced it to 0). Off means the
        // option still lists and prices correctly, it just carries no delivery window.
        $showEstimate = (string) Setting::get('enable_shipping_estimate', '1') !== '0';

        $options = [[
            'id' => 'home_delivery',
            'name' => 'Home Delivery',
            'cost' => $this->shippingCostFor('home_delivery', $subtotal, $cityName),
            'estimated_days' => $showEstimate ? '3-5 business days' : null,
        ]];

        if (Setting::get('local_pickup_enabled', '0') === '1') {
            $options[] = [
                'id' => 'local_pickup',
                'name' => 'Local Pickup',
                'cost' => $this->shippingCostFor('local_pickup', $subtotal, $cityName),
                'estimated_days' => $showEstimate ? 'Same day' : null,
            ];
        }

        return $options;
    }

    /**
     * One place that decides what delivery costs, used by the quote and by the order itself.
     *
     * local_pickup  -> its own setting, never the free-shipping override
     * home_delivery -> free above the threshold, else the active zone for the city when the shop
     *                  calculates by location, else the flat default
     */
    private function shippingCostFor(string $method, float $subtotal, ?string $cityName = null): float
    {
        if ($method === 'local_pickup') {
            return (float) Setting::get('local_pickup_cost', 0);
        }

        $freeShippingEnabled = Setting::get('free_shipping_enabled', '0') === '1';
        $freeShippingMinAmount = (float) Setting::get('free_shipping_min_amount', 0);

        if ($freeShippingEnabled && $freeShippingMinAmount > 0 && $subtotal >= $freeShippingMinAmount) {
            return 0.0;
        }

        if (Setting::get('shipping_calculation_type', 'flat') === 'location' && $cityName) {
            $zone = DeliveryZone::where('is_active', true)
                ->where('city', 'like', '%' . $cityName . '%')
                ->orderByDesc('is_default')
                ->orderBy('sort_order')
                ->first();

            if ($zone) {
                return (float) $zone->shipping_cost;
            }
        }

        return (float) Setting::get('default_shipping_cost', 0);
    }

    /** The checkout page posts a city id; the zone table is keyed by name. */
    private function cityNameFrom($city): ?string
    {
        if (empty($city)) {
            return null;
        }

        if (is_numeric($city)) {
            $row = City::find((int) $city);

            return $row?->name;
        }

        return (string) $city;
    }
}
