<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use App\Models\DeliverySchedule;
use App\Models\DeliveryPartner;
use App\Models\DeliveryZone;
use App\Models\DeliveryBoy;
use App\Models\Carrier;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DeliveryController extends Controller
{
    /**
     * Display the Delivery Dashboard.
     */
    public function index(Request $request)
    {
        // Date range filter
        $dateRange = $request->get('date_range', 'today');
        $startDate = $this->getStartDate($dateRange);
        $endDate = Carbon::now()->endOfDay();

        // Get order statistics
        $stats = $this->getDeliveryStats($startDate, $endDate);
        
        // Get recent orders pending shipment
        $pendingShipments = Order::whereIn('status', ['pending', 'confirmed'])
            ->whereIn('payment_status', ['paid'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get orders in transit
        $inTransit = Order::where('status', 'shipped')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Get recently delivered
        $recentlyDelivered = Order::where('status', 'delivered')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Get failed/returned deliveries
        $failedDeliveries = Order::whereIn('status', ['cancelled', 'refunded'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Delivery performance metrics
        $performance = $this->getDeliveryPerformance($startDate, $endDate);

        return view('admin.delivery.dashboard', compact(
            'stats',
            'pendingShipments',
            'inTransit',
            'recentlyDelivered',
            'failedDeliveries',
            'performance',
            'dateRange'
        ));
    }

    /**
     * Get delivery statistics based on date range.
     */
    private function getDeliveryStats($startDate, $endDate)
    {
        // Total orders in period
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();

        // Pending shipments
        $pendingShipments = Order::whereIn('status', ['pending', 'confirmed'])
            ->whereIn('payment_status', ['paid'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Shipped/In Transit
        $inTransit = Order::where('status', 'shipped')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Delivered
        $delivered = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Cancelled/Refunded
        $failed = Order::whereIn('status', ['cancelled', 'refunded'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Total shipping revenue
        $shippingRevenue = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('shipping_cost');

        // Average delivery time (in days)
        $avgDeliveryTime = $this->getAverageDeliveryTime($startDate, $endDate);

        // Success rate
        $successRate = $totalOrders > 0 ? round(($delivered / $totalOrders) * 100, 1) : 0;

        return [
            'total_orders' => $totalOrders,
            'pending_shipments' => $pendingShipments,
            'in_transit' => $inTransit,
            'delivered' => $delivered,
            'failed' => $failed,
            'shipping_revenue' => $shippingRevenue,
            'avg_delivery_time' => $avgDeliveryTime,
            'success_rate' => $successRate,
        ];
    }

    /**
     * Calculate average delivery time in days.
     */
    private function getAverageDeliveryTime($startDate, $endDate)
    {
        $deliveredOrders = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        if ($deliveredOrders->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        foreach ($deliveredOrders as $order) {
            $created = Carbon::parse($order->created_at);
            $updated = Carbon::parse($order->updated_at);
            $totalDays += $created->diffInDays($updated);
        }

        return round($totalDays / $deliveredOrders->count(), 1);
    }

    /**
     * Get delivery performance metrics.
     */
    private function getDeliveryPerformance($startDate, $endDate)
    {
        // Daily delivery data for chart
        $dailyDeliveries = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Status breakdown
        $statusBreakdown = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Payment status breakdown
        $paymentBreakdown = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('payment_status, COUNT(*) as count')
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status')
            ->toArray();

        return [
            'daily_deliveries' => $dailyDeliveries,
            'status_breakdown' => $statusBreakdown,
            'payment_breakdown' => $paymentBreakdown,
        ];
    }

    /**
     * Get start date based on date range filter.
     */
    private function getStartDate($dateRange)
    {
        return match($dateRange) {
            'today' => Carbon::today(),
            'yesterday' => Carbon::yesterday(),
            'this_week' => Carbon::now()->startOfWeek(),
            'last_week' => Carbon::now()->subWeek()->startOfWeek(),
            'this_month' => Carbon::now()->startOfMonth(),
            'last_month' => Carbon::now()->subMonth()->startOfMonth(),
            'last_30_days' => Carbon::now()->subDays(30),
            'this_year' => Carbon::now()->startOfYear(),
            'last_7_days' => Carbon::now()->subDays(7),
            'custom' => Carbon::now()->startOfMonth(),
            default => Carbon::now()->startOfMonth(),
        };
    }

    /**
     * Quick ship order action.
     */
    public function quickShip(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'tracking_number' => 'nullable|string|max:100',
        ]);

        $order = Order::findOrFail($request->order_id);
        
        if (!in_array($order->payment_status, ['paid'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order must be paid before shipping.'
            ], 400);
        }

        $order->status = 'shipped';
        $order->tracking_number = $request->tracking_number ?? $order->tracking_number;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order marked as shipped.'
        ]);
    }

    /**
     * Mark order as delivered.
     */
    public function markDelivered(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);
        $order->status = 'delivered';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order marked as delivered.'
        ]);
    }

    /**
     * Get orders for delivery table (AJAX).
     */
    public function getOrders(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');
        
        $query = Order::query();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_first_name', 'like', "%{$search}%")
                  ->orWhere('shipping_last_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json([
            'html' => view('admin.delivery.partials.order-table', compact('orders'))->render(),
            'pagination' => $orders->links()->toHtml(),
        ]);
    }

    // Placeholder methods for other delivery routes
    
    /**
     * Display delivery partners list.
     */
    public function partners(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $serviceType = $request->get('service_type');
        $sort = $request->get('sort', 'sort_order');
        $direction = $request->get('direction', 'asc');
        $perPage = $request->get('per_page', 25);
        
        // Statistics
        $stats = [
            'total' => DeliveryPartner::count(),
            'active' => DeliveryPartner::where('is_active', true)->count(),
            'inactive' => DeliveryPartner::where('is_active', false)->count(),
            'featured' => DeliveryPartner::where('is_featured', true)->count(),
        ];
        
        // Build query
        $query = DeliveryPartner::query();
        
        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active');
        }
        
        // Service type filter
        if ($serviceType !== null && $serviceType !== '') {
            $query->where('service_type', $serviceType);
        }
        
        // Sorting
        $validSorts = ['name', 'sort_order', 'created_at', 'service_type'];
        if (in_array($sort, $validSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('sort_order')->orderBy('name');
        }
        
        $partners = $query->paginate($perPage)->appends($request->query());
        
        // AJAX response
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.delivery.partners.partials.table-rows', compact('partners'))->render();
            
            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $partners->links()->toHtml(),
                'total' => $partners->total()
            ]);
        }
        
        return view('admin.delivery.partners.index', compact('partners', 'stats'));
    }

    /**
     * Show create form for delivery partner.
     */
    public function createPartner()
    {
        return view('admin.delivery.partners.create');
    }

    /**
     * Store new delivery partner.
     */
    public function storePartner(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:delivery_partners,slug',
            'logo' => 'nullable|image|max:5120',
            'description' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'service_type' => 'nullable|in:express,standard,overnight,international,all',
            'coverage_area' => 'nullable|string|max:255',
            'base_rate' => 'nullable|numeric|min:0',
            'cod_charge' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        $data = $request->only([
            'name', 'slug', 'description', 'contact_person', 'phone', 'email',
            'address', 'website', 'service_type', 'coverage_area',
            'base_rate', 'cod_charge', 'free_shipping_threshold',
            'is_active', 'is_featured', 'sort_order'
        ]);
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // Handle checkbox values
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['base_rate'] = $data['base_rate'] ?? 0;
        $data['cod_charge'] = $data['cod_charge'] ?? 0;
        $data['free_shipping_threshold'] = $data['free_shipping_threshold'] ?? 0;
        
        // Upload logo
        if ($request->hasFile('logo')) {
            if (ImageHelper::isValidImage($request->file('logo'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('logo'),
                    'delivery-partners',
                    1920,
                    300,
                    85
                );
                $data['logo'] = ltrim($imageResult['path'], '/');
            }
        }
        
        DeliveryPartner::create($data);
        
        return redirect()->route('admin.delivery.partners.index')
            ->with('success', 'Delivery partner created successfully.');
    }

    /**
     * Show edit form for delivery partner.
     */
    public function editPartner(DeliveryPartner $partner)
    {
        return view('admin.delivery.partners.edit', compact('partner'));
    }

    /**
     * Update delivery partner.
     */
    public function updatePartner(Request $request, DeliveryPartner $partner)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:delivery_partners,slug,' . $partner->id,
            'logo' => 'nullable|image|max:5120',
            'description' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'service_type' => 'nullable|in:express,standard,overnight,international,all',
            'coverage_area' => 'nullable|string|max:255',
            'base_rate' => 'nullable|numeric|min:0',
            'cod_charge' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        $data = $request->only([
            'name', 'slug', 'description', 'contact_person', 'phone', 'email',
            'address', 'website', 'service_type', 'coverage_area',
            'base_rate', 'cod_charge', 'free_shipping_threshold',
            'is_active', 'is_featured', 'sort_order'
        ]);
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // Handle checkbox values
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        
        // Upload new logo
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($partner->logo) {
                ImageHelper::deleteImage($partner->logo);
            }
            if (ImageHelper::isValidImage($request->file('logo'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('logo'),
                    'delivery-partners',
                    1920,
                    300,
                    85
                );
                $data['logo'] = ltrim($imageResult['path'], '/');
            }
        }
        
        // Remove logo if requested
        if ($request->has('remove_logo') && $partner->logo) {
            ImageHelper::deleteImage($partner->logo);
            $data['logo'] = null;
        }
        
        $partner->update($data);
        
        return redirect()->route('admin.delivery.partners.index')
            ->with('success', 'Delivery partner updated successfully.');
    }

    /**
     * Delete delivery partner.
     */
    public function destroyPartner(DeliveryPartner $partner)
    {
        // Delete logo
        if ($partner->logo) {
            ImageHelper::deleteImage($partner->logo);
        }
        
        $partner->delete();
        
        return redirect()->route('admin.delivery.partners.index')
            ->with('success', 'Delivery partner deleted successfully.');
    }

    /**
     * Toggle delivery partner status.
     */
    public function togglePartnerStatus(DeliveryPartner $partner)
    {
        $partner->update([
            'is_active' => !$partner->is_active
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Partner status updated.',
            'is_active' => $partner->is_active
        ]);
    }

    /**
     * Toggle delivery partner featured status.
     */
    public function togglePartnerFeatured(DeliveryPartner $partner)
    {
        $partner->update([
            'is_featured' => !$partner->is_featured
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Partner featured status updated.',
            'is_featured' => $partner->is_featured
        ]);
    }

    /**
     * Bulk actions for delivery partners.
     */
    public function bulkPartnerAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,feature,unfeature,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:delivery_partners,id',
        ]);
        
        $ids = $request->ids;
        $action = $request->action;
        
        switch ($action) {
            case 'activate':
                DeliveryPartner::whereIn('id', $ids)->update(['is_active' => true]);
                $message = count($ids) . ' partner(s) activated successfully.';
                break;
                
            case 'deactivate':
                DeliveryPartner::whereIn('id', $ids)->update(['is_active' => false]);
                $message = count($ids) . ' partner(s) deactivated successfully.';
                break;
                
            case 'feature':
                DeliveryPartner::whereIn('id', $ids)->update(['is_featured' => true]);
                $message = count($ids) . ' partner(s) marked as featured.';
                break;
                
            case 'unfeature':
                DeliveryPartner::whereIn('id', $ids)->update(['is_featured' => false]);
                $message = count($ids) . ' partner(s) removed from featured.';
                break;
                
            case 'delete':
                // Delete logos
                $partners = DeliveryPartner::whereIn('id', $ids)->whereNotNull('logo')->get();
                foreach ($partners as $partner) {
                    ImageHelper::deleteImage($partner->logo);
                }
                
                DeliveryPartner::whereIn('id', $ids)->delete();
                $message = count($ids) . ' partner(s) deleted successfully.';
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    // Placeholder methods for other delivery routes
    public function carriers(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $carrierType = $request->get('carrier_type');
        $serviceType = $request->get('service_type');
        $sort = $request->get('sort', 'sort_order');
        $direction = $request->get('direction', 'asc');
        $perPage = $request->get('per_page', 25);
        
        // Statistics
        $stats = [
            'total' => Carrier::count(),
            'active' => Carrier::where('is_active', true)->count(),
            'inactive' => Carrier::where('is_active', false)->count(),
            'featured' => Carrier::where('is_featured', true)->count(),
            'api_configured' => Carrier::where('is_api_configured', true)->count(),
        ];
        
        // Build query
        $query = Carrier::query();
        
        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active');
        }
        
        // Carrier type filter
        if ($carrierType !== null && $carrierType !== '') {
            $query->where('carrier_type', $carrierType);
        }
        
        // Service type filter
        if ($serviceType !== null && $serviceType !== '') {
            $query->where('service_type', $serviceType);
        }
        
        // Sorting
        $validSorts = ['name', 'sort_order', 'created_at', 'carrier_type', 'service_type', 'base_rate'];
        if (in_array($sort, $validSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('sort_order')->orderBy('name');
        }
        
        $carriers = $query->paginate($perPage)->appends($request->query());
        
        // AJAX response
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.delivery.carriers.partials.table-rows', compact('carriers'))->render();
            
            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $carriers->links()->toHtml(),
                'total' => $carriers->total()
            ]);
        }
        
        return view('admin.delivery.carriers.index', compact('carriers', 'stats'));
    }

    /**
     * Show create form for carrier.
     */
    public function createCarrier()
    {
        return view('admin.delivery.carriers.create');
    }

    /**
     * Store new carrier.
     */
    public function storeCarrier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:carriers,slug',
            'logo' => 'nullable|image|max:5120',
            'description' => 'nullable|string',
            'carrier_type' => 'nullable|in:international,regional,local,express,freight,all',
            'service_type' => 'nullable|in:express,standard,economy,overnight,international,freight,all',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'api_token' => 'nullable|string',
            'account_number' => 'nullable|string|max:100',
            'api_mode' => 'nullable|in:sandbox,production',
            'tracking_url_pattern' => 'nullable|url|max:500',
            'tracking_prefix' => 'nullable|string|max:50',
            'base_rate' => 'nullable|numeric|min:0',
            'per_kg_rate' => 'nullable|numeric|min:0',
            'fuel_surcharge_percent' => 'nullable|numeric|min:0|max:100',
            'cod_charge' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'coverage_countries' => 'nullable|string',
            'excluded_countries' => 'nullable|string',
            'estimated_delivery_days' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'supports_tracking' => 'boolean',
            'supports_cod' => 'boolean',
            'supports_insurance' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        $data = $request->only([
            'name', 'slug', 'description', 'carrier_type', 'service_type',
            'contact_person', 'phone', 'email', 'address', 'website',
            'api_key', 'api_secret', 'api_token', 'account_number', 'api_mode',
            'tracking_url_pattern', 'tracking_prefix', 'base_rate', 'per_kg_rate',
            'fuel_surcharge_percent', 'cod_charge', 'free_shipping_threshold',
            'coverage_countries', 'excluded_countries', 'estimated_delivery_days',
            'is_active', 'is_featured', 'supports_tracking', 'supports_cod',
            'supports_insurance', 'sort_order'
        ]);
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // Handle checkbox values
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['supports_tracking'] = $request->has('supports_tracking');
        $data['supports_cod'] = $request->has('supports_cod');
        $data['supports_insurance'] = $request->has('supports_insurance');
        
        // Check if API is configured
        $data['is_api_configured'] = !empty($data['api_key']) || !empty($data['api_token']);
        
        // Set defaults
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['base_rate'] = $data['base_rate'] ?? 0;
        $data['per_kg_rate'] = $data['per_kg_rate'] ?? 0;
        $data['fuel_surcharge_percent'] = $data['fuel_surcharge_percent'] ?? 0;
        $data['cod_charge'] = $data['cod_charge'] ?? 0;
        $data['free_shipping_threshold'] = $data['free_shipping_threshold'] ?? 0;
        
        // Upload logo
        if ($request->hasFile('logo')) {
            if (ImageHelper::isValidImage($request->file('logo'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('logo'),
                    'carriers',
                    1920,
                    300,
                    85
                );
                $data['logo'] = ltrim($imageResult['path'], '/');
            }
        }
        
        Carrier::create($data);
        
        return redirect()->route('admin.delivery.carriers.index')
            ->with('success', 'Carrier created successfully.');
    }

    /**
     * Show edit form for carrier.
     */
    public function editCarrier(Carrier $carrier)
    {
        return view('admin.delivery.carriers.edit', compact('carrier'));
    }

    /**
     * Update carrier.
     */
    public function updateCarrier(Request $request, Carrier $carrier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:carriers,slug,' . $carrier->id,
            'logo' => 'nullable|image|max:5120',
            'description' => 'nullable|string',
            'carrier_type' => 'nullable|in:international,regional,local,express,freight,all',
            'service_type' => 'nullable|in:express,standard,economy,overnight,international,freight,all',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'api_token' => 'nullable|string',
            'account_number' => 'nullable|string|max:100',
            'api_mode' => 'nullable|in:sandbox,production',
            'tracking_url_pattern' => 'nullable|url|max:500',
            'tracking_prefix' => 'nullable|string|max:50',
            'base_rate' => 'nullable|numeric|min:0',
            'per_kg_rate' => 'nullable|numeric|min:0',
            'fuel_surcharge_percent' => 'nullable|numeric|min:0|max:100',
            'cod_charge' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'coverage_countries' => 'nullable|string',
            'excluded_countries' => 'nullable|string',
            'estimated_delivery_days' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'supports_tracking' => 'boolean',
            'supports_cod' => 'boolean',
            'supports_insurance' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        $data = $request->only([
            'name', 'slug', 'description', 'carrier_type', 'service_type',
            'contact_person', 'phone', 'email', 'address', 'website',
            'api_key', 'api_secret', 'api_token', 'account_number', 'api_mode',
            'tracking_url_pattern', 'tracking_prefix', 'base_rate', 'per_kg_rate',
            'fuel_surcharge_percent', 'cod_charge', 'free_shipping_threshold',
            'coverage_countries', 'excluded_countries', 'estimated_delivery_days',
            'is_active', 'is_featured', 'supports_tracking', 'supports_cod',
            'supports_insurance', 'sort_order'
        ]);
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // Handle checkbox values
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['supports_tracking'] = $request->has('supports_tracking');
        $data['supports_cod'] = $request->has('supports_cod');
        $data['supports_insurance'] = $request->has('supports_insurance');
        
        // Check if API is configured
        $data['is_api_configured'] = !empty($data['api_key']) || !empty($data['api_token']);
        
        // Only update API secret if a new value is provided
        if (!empty($request->api_secret)) {
            $data['api_secret'] = $request->api_secret;
        } else {
            unset($data['api_secret']);
        }
        
        // Upload new logo
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($carrier->logo) {
                ImageHelper::deleteImage($carrier->logo);
            }
            if (ImageHelper::isValidImage($request->file('logo'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('logo'),
                    'carriers',
                    1920,
                    300,
                    85
                );
                $data['logo'] = ltrim($imageResult['path'], '/');
            }
        }
        
        // Remove logo if requested
        if ($request->has('remove_logo') && $carrier->logo) {
            ImageHelper::deleteImage($carrier->logo);
            $data['logo'] = null;
        }
        
        $carrier->update($data);
        
        return redirect()->route('admin.delivery.carriers.index')
            ->with('success', 'Carrier updated successfully.');
    }

    /**
     * Delete carrier.
     */
    public function destroyCarrier(Carrier $carrier)
    {
        // Delete logo
        if ($carrier->logo) {
            ImageHelper::deleteImage($carrier->logo);
        }
        
        $carrier->delete();
        
        return redirect()->route('admin.delivery.carriers.index')
            ->with('success', 'Carrier deleted successfully.');
    }

    /**
     * Toggle carrier status.
     */
    public function toggleCarrierStatus(Carrier $carrier)
    {
        $carrier->update([
            'is_active' => !$carrier->is_active
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Carrier status updated.',
            'is_active' => $carrier->is_active
        ]);
    }

    /**
     * Toggle carrier featured status.
     */
    public function toggleCarrierFeatured(Carrier $carrier)
    {
        $carrier->update([
            'is_featured' => !$carrier->is_featured
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Carrier featured status updated.',
            'is_featured' => $carrier->is_featured
        ]);
    }

    /**
     * Bulk actions for carriers.
     */
    public function bulkCarrierAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,feature,unfeature,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:carriers,id',
        ]);
        
        $ids = $request->ids;
        $action = $request->action;
        
        switch ($action) {
            case 'activate':
                Carrier::whereIn('id', $ids)->update(['is_active' => true]);
                $message = count($ids) . ' carrier(s) activated successfully.';
                break;
                
            case 'deactivate':
                Carrier::whereIn('id', $ids)->update(['is_active' => false]);
                $message = count($ids) . ' carrier(s) deactivated successfully.';
                break;
                
            case 'feature':
                Carrier::whereIn('id', $ids)->update(['is_featured' => true]);
                $message = count($ids) . ' carrier(s) marked as featured.';
                break;
                
            case 'unfeature':
                Carrier::whereIn('id', $ids)->update(['is_featured' => false]);
                $message = count($ids) . ' carrier(s) removed from featured.';
                break;
                
            case 'delete':
                // Delete logos
                $carriers = Carrier::whereIn('id', $ids)->whereNotNull('logo')->get();
                foreach ($carriers as $carrier) {
                    ImageHelper::deleteImage($carrier->logo);
                }
                
                Carrier::whereIn('id', $ids)->delete();
                $message = count($ids) . ' carrier(s) deleted successfully.';
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function tracking(Request $request)
    {
        // Get statistics for all shipments
        $stats = $this->getShipmentStats();
        
        // Build query for shipments (orders with tracking info or shipped orders)
        $query = Order::query()
            ->where(function($q) {
                $q->whereNotNull('tracking_number')
                  ->orWhereIn('status', ['shipped', 'processing', 'confirmed', 'pending']);
            })
            ->with(['user']);
        
        // Search by order number or tracking number
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhere('billing_phone', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by payment status
        if ($request->payment_status && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by carrier
        if ($request->carrier) {
            $query->where('shipping_company', $request->carrier);
        }
        
        // Filter by date range
        if ($request->date_range) {
            $dateRange = $request->date_range;
            $startDate = match($dateRange) {
                'today' => Carbon::today(),
                'yesterday' => Carbon::yesterday(),
                'last_7_days' => Carbon::now()->subDays(7),
                'last_30_days' => Carbon::now()->subDays(30),
                'this_month' => Carbon::now()->startOfMonth(),
                'last_month' => Carbon::now()->subMonth()->startOfMonth(),
                default => Carbon::now()->subDays(30),
            };
            $query->whereDate('created_at', '>=', $startDate);
        }
        
        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);
        
        // Pagination
        $perPage = $request->per_page ?? 25;
        $shipments = $query->paginate($perPage);
        
        // Get carriers for filter
        $carriers = Carrier::where('is_active', 1)->orderBy('name')->get();
        
        // If searching for a specific order, show tracking details
        $trackingOrder = null;
        if ($request->search) {
            $trackingOrder = Order::where('order_number', 'like', "%{$request->search}%")
                ->orWhere('tracking_number', 'like', "%{$request->search}%")
                ->first();
        }
        
        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.delivery.partials.tracking-table-rows', compact('shipments'))->render(),
                'pagination' => $shipments->links()->toHtml(),
                'stats' => $stats
            ]);
        }
        
        return view('admin.delivery.tracking', compact('shipments', 'stats', 'carriers', 'trackingOrder'));
    }
    
    /**
     * Get shipment statistics.
     */
    private function getShipmentStats()
    {
        return [
            'total' => Order::whereNotNull('tracking_number')->orWhereIn('status', ['shipped', 'processing'])->count(),
            'in_transit' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'pending' => Order::whereIn('status', ['pending', 'confirmed'])->count(),
            'returned' => Order::where('status', 'cancelled')->count(),
        ];
    }
    
    /**
     * Update shipment tracking status.
     */
    public function updateTrackingStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,confirmed,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $order->update([
            'status' => $request->status,
            'tracking_number' => $request->tracking_number ?? $order->tracking_number,
            'notes' => $request->notes ?? $order->notes,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Shipment status updated successfully.',
            'order' => $order->fresh()
        ]);
    }
    
    /**
     * Generate tracking number for an order.
     */
    public function generateTrackingNumber(Request $request, Order $order)
    {
        // Check if order is paid before generating tracking number
        if (!in_array($order->payment_status, ['paid'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order must be paid before generating tracking number.'
            ], 400);
        }
        
        $trackingNumber = 'TRK' . strtoupper(uniqid()) . rand(1000, 9999);
        
        $order->update([
            'tracking_number' => $trackingNumber,
            'status' => 'shipped',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Tracking number generated successfully.',
            'tracking_number' => $trackingNumber
        ]);
    }
    
    /**
     * Bulk update tracking status via AJAX.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,confirmed,shipped,delivered,cancelled',
            'ids' => 'required|array',
            'ids.*' => 'exists:orders,id',
        ]);
        
        $count = Order::whereIn('id', $request->ids)->update([
            'status' => $request->status
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "{$count} shipments updated successfully."
        ]);
    }
    
    /**
     * Bulk tracking action (form submission).
     */
    public function bulkTrackingAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:shipped,delivered,cancelled,pending',
            'ids' => 'required|array',
            'ids.*' => 'exists:orders,id',
        ]);
        
        $count = Order::whereIn('id', $request->ids)->update([
            'status' => $request->action
        ]);
        
        return redirect()->route('admin.delivery.tracking')
            ->with('success', "{$count} shipments updated successfully.");
    }
    
    public function zones(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $areaType = $request->get('area_type');
        $sort = $request->get('sort', 'sort_order');
        $direction = $request->get('direction', 'asc');
        $perPage = $request->get('per_page', 25);
        
        // Statistics
        $stats = [
            'total' => DeliveryZone::count(),
            'active' => DeliveryZone::where('is_active', true)->count(),
            'inactive' => DeliveryZone::where('is_active', false)->count(),
            'default' => DeliveryZone::where('is_default', true)->count(),
        ];
        
        // Build query
        $query = DeliveryZone::query();
        
        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('region', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active');
        }
        
        // Area type filter
        if ($areaType !== null && $areaType !== '') {
            $query->where('area_type', $areaType);
        }
        
        // Sorting
        $validSorts = ['name', 'sort_order', 'created_at', 'area_type', 'estimated_days'];
        if (in_array($sort, $validSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('sort_order')->orderBy('name');
        }
        
        $zones = $query->paginate($perPage)->appends($request->query());
        
        // AJAX response
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.delivery.zones.partials.table-rows', compact('zones'))->render();
            
            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $zones->links()->toHtml(),
                'total' => $zones->total()
            ]);
        }
        
        return view('admin.delivery.zones.index', compact('zones', 'stats'));
    }
    
    /**
     * Show create form for delivery zone.
     */
    public function createZone()
    {
        return view('admin.delivery.zones.create');
    }
    
    /**
     * Store new delivery zone.
     */
    public function storeZone(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:delivery_zones,slug',
            'description' => 'nullable|string',
            'region' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'area_type' => 'nullable|in:nationwide,regional,city,district,thana,zone',
            'cod_enabled' => 'boolean',
            'cod_charge' => 'nullable|numeric|min:0',
            'cod_charge_type' => 'nullable|in:flat,percentage',
            'free_shipping_enabled' => 'boolean',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'shipping_cost_type' => 'nullable|in:flat,weight,free',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_weight' => 'nullable|numeric|min:0',
            'estimated_days' => 'nullable|integer|min:1|max:30',
            'delivery_time_start' => 'nullable|string|max:50|regex:/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9](\s)?(AM|PM|am|pm)?$/',
            'delivery_time_end' => 'nullable|string|max:50|regex:/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9](\s)?(AM|PM|am|pm)?$/',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        $data = $request->only([
            'name', 'slug', 'description', 'region', 'country', 'state', 'city',
            'postal_code', 'area_type', 'cod_enabled', 'cod_charge', 'cod_charge_type',
            'free_shipping_enabled', 'free_shipping_threshold', 'shipping_cost',
            'shipping_cost_type', 'min_order_amount', 'max_order_weight',
            'estimated_days', 'delivery_time_start', 'delivery_time_end',
            'is_active', 'is_default', 'sort_order'
        ]);
        
        // Handle checkbox values
        $data['cod_enabled'] = $request->has('cod_enabled');
        $data['free_shipping_enabled'] = $request->has('free_shipping_enabled');
        $data['is_active'] = $request->has('is_active');
        $data['is_default'] = $request->has('is_default');
        
        // Set defaults
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['cod_charge'] = $data['cod_charge'] ?? 0;
        $data['free_shipping_threshold'] = $data['free_shipping_threshold'] ?? 0;
        $data['shipping_cost'] = $data['shipping_cost'] ?? 0;
        $data['min_order_amount'] = $data['min_order_amount'] ?? 0;
        $data['estimated_days'] = $data['estimated_days'] ?? 3;
        
        // If setting as default, unset other defaults
        if ($data['is_default']) {
            DeliveryZone::where('is_default', true)->update(['is_default' => false]);
        }
        
        DeliveryZone::create($data);
        
        return redirect()->route('admin.delivery.zones.index')
            ->with('success', 'Delivery zone created successfully.');
    }
    
    /**
     * Show edit form for delivery zone.
     */
    public function editZone(DeliveryZone $zone)
    {
        return view('admin.delivery.zones.edit', compact('zone'));
    }
    
    /**
     * Update delivery zone.
     */
    public function updateZone(Request $request, DeliveryZone $zone)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:delivery_zones,slug,' . $zone->id,
            'description' => 'nullable|string',
            'region' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'area_type' => 'nullable|in:nationwide,regional,city,district,thana,zone',
            'cod_enabled' => 'boolean',
            'cod_charge' => 'nullable|numeric|min:0',
            'cod_charge_type' => 'nullable|in:flat,percentage',
            'free_shipping_enabled' => 'boolean',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'shipping_cost_type' => 'nullable|in:flat,weight,free',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_weight' => 'nullable|numeric|min:0',
            'estimated_days' => 'nullable|integer|min:1|max:30',
            'delivery_time_start' => 'nullable|string|max:50|regex:/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9](\s)?(AM|PM|am|pm)?$/',
            'delivery_time_end' => 'nullable|string|max:50|regex:/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9](\s)?(AM|PM|am|pm)?$/',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        $data = $request->only([
            'name', 'slug', 'description', 'region', 'country', 'state', 'city',
            'postal_code', 'area_type', 'cod_enabled', 'cod_charge', 'cod_charge_type',
            'free_shipping_enabled', 'free_shipping_threshold', 'shipping_cost',
            'shipping_cost_type', 'min_order_amount', 'max_order_weight',
            'estimated_days', 'delivery_time_start', 'delivery_time_end',
            'is_active', 'is_default', 'sort_order'
        ]);
        
        // Handle checkbox values
        $data['cod_enabled'] = $request->has('cod_enabled');
        $data['free_shipping_enabled'] = $request->has('free_shipping_enabled');
        $data['is_active'] = $request->has('is_active');
        $data['is_default'] = $request->has('is_default');
        
        // If setting as default, unset other defaults
        if ($data['is_default'] && !$zone->is_default) {
            DeliveryZone::where('is_default', true)->update(['is_default' => false]);
        }
        
        $zone->update($data);
        
        return redirect()->route('admin.delivery.zones.index')
            ->with('success', 'Delivery zone updated successfully.');
    }
    
    /**
     * Delete delivery zone.
     */
    public function destroyZone(DeliveryZone $zone)
    {
        $zone->delete();
        
        return redirect()->route('admin.delivery.zones.index')
            ->with('success', 'Delivery zone deleted successfully.');
    }
    
    /**
     * Toggle delivery zone status.
     */
    public function toggleZoneStatus(DeliveryZone $zone)
    {
        $zone->update([
            'is_active' => !$zone->is_active
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Zone status updated.',
            'is_active' => $zone->is_active
        ]);
    }
    
    /**
     * Toggle delivery zone default status.
     */
    public function toggleZoneDefault(DeliveryZone $zone)
    {
        // If making it default, unset other defaults
        if (!$zone->is_default) {
            DeliveryZone::where('is_default', true)->update(['is_default' => false]);
        }
        
        $zone->update([
            'is_default' => !$zone->is_default
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Zone default status updated.',
            'is_default' => $zone->is_default
        ]);
    }
    
    /**
     * Bulk actions for delivery zones.
     */
    public function bulkZoneAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,set-default,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:delivery_zones,id',
        ]);
        
        $ids = $request->ids;
        $action = $request->action;
        
        switch ($action) {
            case 'activate':
                DeliveryZone::whereIn('id', $ids)->update(['is_active' => true]);
                $message = count($ids) . ' zone(s) activated successfully.';
                break;
                
            case 'deactivate':
                DeliveryZone::whereIn('id', $ids)->update(['is_active' => false]);
                $message = count($ids) . ' zone(s) deactivated successfully.';
                break;
                
            case 'set-default':
                DeliveryZone::whereIn('id', $ids)->update(['is_default' => true]);
                DeliveryZone::whereNotIn('id', $ids)->update(['is_default' => false]);
                $message = 'Default zone set successfully.';
                break;
                
            case 'delete':
                DeliveryZone::whereIn('id', $ids)->delete();
                $message = count($ids) . ' zone(s) deleted successfully.';
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Display Courier Integration page with Bangladeshi couriers.
     */
    public function courierIntegration(Request $request)
    {
        // Get all carriers
        $carriers = Carrier::ordered()->get();
        
        // Get active carriers
        $activeCarriers = Carrier::active()->ordered()->get();
        
        // Get carriers with API configured
        $apiConfiguredCarriers = Carrier::active()->apiConfigured()->ordered()->get();
        
        // Statistics
        $stats = [
            'total' => Carrier::count(),
            'active' => Carrier::active()->count(),
            'api_configured' => Carrier::apiConfigured()->count(),
            'supports_cod' => Carrier::active()->supportsCod()->count(),
            'supports_tracking' => Carrier::active()->supportsTracking()->count(),
        ];
        
        // Popular Bangladeshi couriers templates (pre-configured)
        $bangladeshiCouriers = [
            [
                'name' => 'Pathao',
                'slug' => 'pathao',
                'description' => 'Bangladesh\'s largest delivery platform with nationwide coverage',
                'logo' => 'pathao',
                'carrier_type' => 'regional',
                'service_type' => 'express',
                'tracking_url_pattern' => 'https://pathao.com/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => true,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 60,
                'per_kg_rate' => 15,
                'cod_charge' => 0,
                'estimated_delivery_days' => '1-3',
            ],
            [
                'name' => 'SSL Commercial',
                'slug' => 'ssl-commercial',
                'description' => 'SSL Wireless - Trusted payment and delivery solutions',
                'logo' => 'ssl',
                'carrier_type' => 'regional',
                'service_type' => 'standard',
                'tracking_url_pattern' => 'https://sslcommerce.com/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => false,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 50,
                'per_kg_rate' => 12,
                'cod_charge' => 0,
                'estimated_delivery_days' => '2-4',
            ],
            [
                'name' => 'Steadfast',
                'slug' => 'steadfast',
                'description' => 'Fast and reliable courier service in Bangladesh',
                'logo' => 'steadfast',
                'carrier_type' => 'local',
                'service_type' => 'express',
                'tracking_url_pattern' => 'https://steadfast.com.bd/t/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => false,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 45,
                'per_kg_rate' => 10,
                'cod_charge' => 0,
                'estimated_delivery_days' => '1-2',
            ],
            [
                'name' => 'Paperfly',
                'slug' => 'paperfly',
                'description' => 'E-commerce focused delivery service',
                'logo' => 'paperfly',
                'carrier_type' => 'local',
                'service_type' => 'standard',
                'tracking_url_pattern' => 'https://paperfly.com/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => false,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 40,
                'per_kg_rate' => 8,
                'cod_charge' => 0,
                'estimated_delivery_days' => '2-3',
            ],
            [
                'name' => 'eCourier',
                'slug' => 'ecourier',
                'description' => 'Digital courier service with COD support',
                'logo' => 'ecourier',
                'carrier_type' => 'regional',
                'service_type' => 'express',
                'tracking_url_pattern' => 'https://ecourier.com.bd/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => true,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 55,
                'per_kg_rate' => 12,
                'cod_charge' => 0,
                'estimated_delivery_days' => '1-3',
            ],
            [
                'name' => 'RedX',
                'slug' => 'redx',
                'description' => 'Fast delivery service with premium features',
                'logo' => 'redx',
                'carrier_type' => 'regional',
                'service_type' => 'express',
                'tracking_url_pattern' => 'https://redx.com.bd/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => true,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 65,
                'per_kg_rate' => 15,
                'cod_charge' => 0,
                'estimated_delivery_days' => '1-2',
            ],
            [
                'name' => 'Delivery Tiger',
                'slug' => 'delivery-tiger',
                'description' => 'Reliable delivery partner for businesses',
                'logo' => 'delivery-tiger',
                'carrier_type' => 'local',
                'service_type' => 'standard',
                'tracking_url_pattern' => 'https://deliverytiger.com.bd/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => false,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 42,
                'per_kg_rate' => 9,
                'cod_charge' => 0,
                'estimated_delivery_days' => '2-4',
            ],
            [
                'name' => 'Chittagong Courier',
                'slug' => 'chittagong-courier',
                'description' => 'Specialized courier service for Chittagong region',
                'logo' => 'chittagong-courier',
                'carrier_type' => 'local',
                'service_type' => 'standard',
                'tracking_url_pattern' => 'https://chittagongcourier.com/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => false,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 35,
                'per_kg_rate' => 8,
                'cod_charge' => 0,
                'estimated_delivery_days' => '1-3',
            ],
        ];
        
        return view('admin.delivery.courier-integration', compact(
            'carriers',
            'activeCarriers',
            'apiConfiguredCarriers',
            'stats',
            'bangladeshiCouriers'
        ));
    }
    
    /**
     * Add a courier from pre-configured Bangladeshi template.
     */
    public function addCourierFromTemplate(Request $request)
    {
        $request->validate([
            'courier_name' => 'required|string|max:255',
        ]);
        
        // Pre-configured Bangladeshi couriers templates
        $courierTemplates = [
            'Pathao' => [
                'name' => 'Pathao',
                'slug' => 'pathao',
                'description' => 'Bangladesh\'s largest delivery platform with nationwide coverage',
                'carrier_type' => 'regional',
                'service_type' => 'express',
                'tracking_url_pattern' => 'https://pathao.com/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => true,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 60,
                'per_kg_rate' => 15,
                'cod_charge' => 0,
                'estimated_delivery_days' => '1-3',
            ],
            'SSL Commercial' => [
                'name' => 'SSL Commercial',
                'slug' => 'ssl-commercial',
                'description' => 'SSL Wireless - Trusted payment and delivery solutions',
                'carrier_type' => 'regional',
                'service_type' => 'standard',
                'tracking_url_pattern' => 'https://sslcommerce.com/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => false,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 50,
                'per_kg_rate' => 12,
                'cod_charge' => 0,
                'estimated_delivery_days' => '2-4',
            ],
            'Steadfast' => [
                'name' => 'Steadfast',
                'slug' => 'steadfast',
                'description' => 'Fast and reliable courier service in Bangladesh',
                'carrier_type' => 'local',
                'service_type' => 'express',
                'tracking_url_pattern' => 'https://steadfast.com.bd/t/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => false,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 45,
                'per_kg_rate' => 10,
                'cod_charge' => 0,
                'estimated_delivery_days' => '1-2',
            ],
            'Paperfly' => [
                'name' => 'Paperfly',
                'slug' => 'paperfly',
                'description' => 'E-commerce focused delivery service',
                'carrier_type' => 'local',
                'service_type' => 'standard',
                'tracking_url_pattern' => 'https://paperfly.com/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => false,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 40,
                'per_kg_rate' => 8,
                'cod_charge' => 0,
                'estimated_delivery_days' => '2-3',
            ],
            'eCourier' => [
                'name' => 'eCourier',
                'slug' => 'ecourier',
                'description' => 'Digital courier service with COD support',
                'carrier_type' => 'regional',
                'service_type' => 'express',
                'tracking_url_pattern' => 'https://ecourier.com.bd/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => true,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 55,
                'per_kg_rate' => 12,
                'cod_charge' => 0,
                'estimated_delivery_days' => '1-3',
            ],
            'RedX' => [
                'name' => 'RedX',
                'slug' => 'redx',
                'description' => 'Fast delivery service with premium features',
                'carrier_type' => 'regional',
                'service_type' => 'express',
                'tracking_url_pattern' => 'https://redx.com.bd/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => true,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 65,
                'per_kg_rate' => 15,
                'cod_charge' => 0,
                'estimated_delivery_days' => '1-2',
            ],
            'Delivery Tiger' => [
                'name' => 'Delivery Tiger',
                'slug' => 'delivery-tiger',
                'description' => 'Reliable delivery partner for businesses',
                'carrier_type' => 'local',
                'service_type' => 'standard',
                'tracking_url_pattern' => 'https://deliverytiger.com.bd/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => false,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 42,
                'per_kg_rate' => 9,
                'cod_charge' => 0,
                'estimated_delivery_days' => '2-4',
            ],
            'Chittagong Courier' => [
                'name' => 'Chittagong Courier',
                'slug' => 'chittagong-courier',
                'description' => 'Specialized courier service for Chittagong region',
                'carrier_type' => 'local',
                'service_type' => 'standard',
                'tracking_url_pattern' => 'https://chittagongcourier.com/track/{{tracking_number}}',
                'supports_tracking' => true,
                'supports_cod' => true,
                'supports_insurance' => false,
                'coverage_countries' => 'Bangladesh',
                'base_rate' => 35,
                'per_kg_rate' => 8,
                'cod_charge' => 0,
                'estimated_delivery_days' => '1-3',
            ],
        ];
        
        $courierName = $request->input('courier_name');
        
        // Check if courier template exists
        if (!isset($courierTemplates[$courierName])) {
            return redirect()->route('admin.delivery.courier-integration')
                ->with('error', 'Invalid courier selected.');
        }
        
        // Check if courier already exists
        if (Carrier::where('slug', $courierTemplates[$courierName]['slug'])->exists()) {
            return redirect()->route('admin.delivery.courier-integration')
                ->with('error', $courierName . ' is already added. You can edit it from the carriers list.');
        }
        
        // Create the carrier
        $data = $courierTemplates[$courierName];
        $data['is_active'] = true;
        $data['is_featured'] = true;
        $data['is_api_configured'] = false;
        $data['sort_order'] = Carrier::max('sort_order') + 1;
        
        Carrier::create($data);
        
        return redirect()->route('admin.delivery.courier-integration')
            ->with('success', $courierName . ' has been added successfully! Please configure API settings to enable full functionality.');
    }

    public function deliveryBoys(Request $request)
    {
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $zone = $request->get('zone', '');
        $availability = $request->get('availability', '');

        $deliveryBoys = DeliveryBoy::with('zone')
            ->when($search, function ($query) use ($search) {
                return $query->search($search);
            })
            ->when($status, function ($query) use ($status) {
                return $query->status($status);
            })
            ->when($zone, function ($query) use ($zone) {
                return $query->where('zone_id', $zone);
            })
            ->when($availability !== '', function ($query) use ($availability) {
                return $query->where('is_available', $availability);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $zones = DeliveryZone::active()->ordered()->get();

        // Statistics
        $stats = [
            'total' => DeliveryBoy::count(),
            'active' => DeliveryBoy::where('status', 'active')->count(),
            'available' => DeliveryBoy::where('is_available', true)->where('status', 'active')->count(),
            'on_delivery' => DeliveryBoy::where('status', 'on_delivery')->count(),
            'on_leave' => DeliveryBoy::where('status', 'on_leave')->count(),
            'inactive' => DeliveryBoy::where('status', 'inactive')->count(),
        ];

        // AJAX response
        if ($request->ajax()) {
            $html = view('admin.delivery.delivery-boys.partials.table-rows', compact('deliveryBoys'))->render();
            $pagination = $deliveryBoys->links()->toHtml();
            
            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $pagination,
                'total' => $deliveryBoys->total()
            ]);
        }

        return view('admin.delivery.delivery-boys.index', compact(
            'deliveryBoys',
            'zones',
            'stats',
            'search',
            'status',
            'zone',
            'availability'
        ));
    }

    /**
     * Show the form for creating a new delivery boy.
     */
    public function createDeliveryBoy()
    {
        $zones = DeliveryZone::active()->ordered()->get();
        return view('admin.delivery.delivery-boys.create', compact('zones'));
    }

    /**
     * Store a newly created delivery boy.
     */
    public function storeDeliveryBoy(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:delivery_boys,email',
            'phone' => 'required|string|max:20|unique:delivery_boys,phone',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:500',
            'vehicle_type' => 'nullable|string|in:bicycle,bike,car,van,truck',
            'vehicle_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date|before:today',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'salary' => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'shift_start' => 'nullable|date_format:H:i',
            'shift_end' => 'nullable|date_format:H:i|after:shift_start',
            'zone_id' => 'nullable|exists:delivery_zones,id',
            'status' => 'nullable|in:active,inactive,on_leave,suspended',
            'is_available' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Handle file upload with ImageHelper
        if ($request->hasFile('photo')) {
            $result = ImageHelper::processImage(
                $request->file('photo'),
                'delivery-boys',
                800,
                300,
                85
            );
            $validated['photo'] = $result['filename'];
        }

        // Set defaults
        $validated['is_active'] = $request->has('is_active');
        $validated['is_available'] = $request->has('is_available');
        $validated['created_by'] = auth()->id();
        $validated['status'] = $validated['status'] ?? 'active';

        DeliveryBoy::create($validated);

        return redirect()->route('admin.delivery.delivery-boys.index')
            ->with('success', 'Delivery boy has been created successfully!');
    }

    /**
     * Show the form for editing a delivery boy.
     */
    public function editDeliveryBoy(DeliveryBoy $deliveryBoy)
    {
        $zones = DeliveryZone::active()->ordered()->get();
        return view('admin.delivery.delivery-boys.edit', compact('deliveryBoy', 'zones'));
    }

    /**
     * Update the specified delivery boy.
     */
    public function updateDeliveryBoy(Request $request, DeliveryBoy $deliveryBoy)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:delivery_boys,email,' . $deliveryBoy->id,
            'phone' => 'required|string|max:20|unique:delivery_boys,phone,' . $deliveryBoy->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:500',
            'vehicle_type' => 'nullable|string|in:bicycle,bike,car,van,truck',
            'vehicle_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date|before:today',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'salary' => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'shift_start' => 'nullable|date_format:H:i',
            'shift_end' => 'nullable|date_format:H:i|after:shift_start',
            'zone_id' => 'nullable|exists:delivery_zones,id',
            'status' => 'nullable|in:active,inactive,on_leave,suspended',
            'is_available' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Handle file upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($deliveryBoy->photo) {
                ImageHelper::deleteImage($deliveryBoy->photo);
            }
            $result = ImageHelper::processImage(
                $request->file('photo'),
                'delivery-boys',
                800,
                300,
                85
            );
            $validated['photo'] = $result['filename'];
        }

        // Handle remove photo
        if ($request->has('remove_photo') && $request->remove_photo == 1 && $deliveryBoy->photo) {
            ImageHelper::deleteImage($deliveryBoy->photo);
            $validated['photo'] = null;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['is_available'] = $request->has('is_available');

        $deliveryBoy->update($validated);

        return redirect()->route('admin.delivery.delivery-boys.index')
            ->with('success', 'Delivery boy has been updated successfully!');
    }

    /**
     * Remove the specified delivery boy.
     */
    public function destroyDeliveryBoy(DeliveryBoy $deliveryBoy)
    {
        // Delete photo using ImageHelper
        if ($deliveryBoy->photo) {
            ImageHelper::deleteImage($deliveryBoy->photo);
        }

        $deliveryBoy->delete();

        return redirect()->route('admin.delivery.delivery-boys.index')
            ->with('success', 'Delivery boy has been deleted successfully!');
    }

    /**
     * Toggle delivery boy status.
     */
    public function toggleDeliveryBoyStatus(DeliveryBoy $deliveryBoy)
    {
        $deliveryBoy->update(['is_active' => !$deliveryBoy->is_active]);

        $status = $deliveryBoy->is_active ? 'activated' : 'deactivated';
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Delivery boy has been {$status} successfully!",
                'is_active' => $deliveryBoy->is_active
            ]);
        }
        
        return back()->with('success', "Delivery boy has been {$status} successfully!");
    }

    /**
     * Toggle delivery boy availability.
     */
    public function toggleDeliveryBoyAvailability(DeliveryBoy $deliveryBoy)
    {
        $deliveryBoy->update(['is_available' => !$deliveryBoy->is_available]);

        $status = $deliveryBoy->is_available ? 'marked as available' : 'marked as unavailable';
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Delivery boy has been {$status} successfully!",
                'is_available' => $deliveryBoy->is_available
            ]);
        }
        
        return back()->with('success', "Delivery boy has been {$status} successfully!");
    }

    /**
     * Bulk action on delivery boys.
     */
    public function bulkDeliveryBoyAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:activate,deactivate,delete,available,unavailable',
            'delivery_boys' => 'required|array|min:1',
            'delivery_boys.*' => 'exists:delivery_boys,id',
        ]);

        $deliveryBoys = DeliveryBoy::whereIn('id', $request->delivery_boys);
        $count = $deliveryBoys->count();

        switch ($request->action) {
            case 'activate':
                $deliveryBoys->update(['is_active' => true]);
                return back()->with('success', "{$count} delivery boys have been activated!");

            case 'deactivate':
                $deliveryBoys->update(['is_active' => false]);
                return back()->with('success', "{$count} delivery boys have been deactivated!");

            case 'available':
                $deliveryBoys->update(['is_available' => true]);
                return back()->with('success', "{$count} delivery boys have been marked as available!");

            case 'unavailable':
                $deliveryBoys->update(['is_available' => false]);
                return back()->with('success', "{$count} delivery boys have been marked as unavailable!");

            case 'delete':
                // Delete photos using ImageHelper
                $deliveryBoys->each(function ($boy) {
                    if ($boy->photo) {
                        ImageHelper::deleteImage($boy->photo);
                    }
                });
                $deliveryBoys->delete();
                return back()->with('success', "{$count} delivery boys have been deleted!");

            default:
                return back()->with('error', 'Invalid action selected!');
        }
    }

    public function schedules(Request $request)
    {
        $query = DeliverySchedule::query();
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }
        
        // Filter by type
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        // Filter by status
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }
        
        // Sorting
        $validSorts = ['created_at', 'updated_at', 'name', 'type', 'day_of_week', 'start_time', 'additional_fee', 'is_active', 'sort_order'];
        $sort = in_array($request->sort, $validSorts) ? $request->sort : 'created_at';
        $direction = in_array($request->direction, ['asc', 'desc']) ? $request->direction : 'desc';
        $query->orderBy($sort, $direction);
        
        // Pagination
        $perPage = $request->per_page ?? 25;
        $schedules = $query->paginate($perPage);
        
        // Get delivery zones for filter
        $zones = DeliveryZone::where('is_active', true)->get();
        
        // Statistics
        $stats = [
            'total' => DeliverySchedule::count(),
            'active' => DeliverySchedule::where('is_active', true)->count(),
            'inactive' => DeliverySchedule::where('is_active', false)->count(),
            'same_day' => DeliverySchedule::ofType('same_day')->count(),
            'next_day' => DeliverySchedule::ofType('next_day')->count(),
            'express' => DeliverySchedule::ofType('express')->count(),
            'scheduled' => DeliverySchedule::ofType('scheduled')->count(),
        ];
        
        // AJAX request
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.delivery.schedules.partials.table-rows', compact('schedules'))->render(),
                'pagination' => $schedules->links()->toHtml(),
            ]);
        }
        
        return view('admin.delivery.schedules.index', compact('schedules', 'stats', 'zones'));
    }
    
    /**
     * Show the form for creating a new delivery schedule.
     */
    public function createSchedule()
    {
        $zones = DeliveryZone::where('is_active', true)->get();
        return view('admin.delivery.schedules.create', compact('zones'));
    }
    
    /**
     * Store a newly created delivery schedule.
     */
    public function storeSchedule(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'day_of_week' => 'nullable|integer|min:0|max:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'type' => 'required|in:same_day,next_day,express,scheduled',
            'max_orders' => 'nullable|integer|min:1',
            'additional_fee' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        // Validate end_time: allow if after start_time OR if it's an overnight schedule (end_time < start_time)
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        
        // Allow overnight schedules (end_time < start_time means it spans past midnight)
        // Also allow same time (for full-day schedules) or end_time after start_time
        if ($startTime === $endTime) {
            // Same time allowed for full-day schedules
        } elseif ($endTime > $startTime) {
            // Normal schedule (e.g., 9 AM to 5 PM)
        } else {
            // Overnight schedule (e.g., 10 PM to 2 AM) - allowed
        }
        
        $schedule = DeliverySchedule::create([
            'name' => $request->name,
            'description' => $request->description,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'cutoff_time' => $request->cutoff_time,
            'type' => $request->type,
            'is_active' => $request->is_active ?? true,
            'max_orders' => $request->max_orders,
            'additional_fee' => $request->additional_fee ?? 0,
            'min_order_amount' => $request->min_order_amount ?? 0,
            'delivery_zones' => $request->delivery_zones ?? [],
            'available_from' => $request->available_from,
            'available_to' => $request->available_to,
            'sort_order' => $request->sort_order ?? 0,
        ]);
        
        return redirect()->route('admin.delivery.schedules.index')
            ->with('success', 'Delivery schedule "' . $schedule->name . '" has been created successfully!');
    }
    
    /**
     * Show the form for editing a delivery schedule.
     */
    public function editSchedule($id)
    {
        $schedule = DeliverySchedule::findOrFail($id);
        $zones = DeliveryZone::where('is_active', true)->get();
        return view('admin.delivery.schedules.edit', compact('schedule', 'zones'));
    }
    
    /**
     * Update a delivery schedule.
     */
    public function updateSchedule(Request $request, $id)
    {
        $schedule = DeliverySchedule::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'day_of_week' => 'nullable|integer|min:0|max:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'type' => 'required|in:same_day,next_day,express,scheduled',
            'max_orders' => 'nullable|integer|min:1',
            'additional_fee' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        $schedule->update([
            'name' => $request->name,
            'description' => $request->description,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'cutoff_time' => $request->cutoff_time,
            'type' => $request->type,
            'is_active' => $request->is_active ?? true,
            'max_orders' => $request->max_orders,
            'additional_fee' => $request->additional_fee ?? 0,
            'min_order_amount' => $request->min_order_amount ?? 0,
            'delivery_zones' => $request->delivery_zones ?? [],
            'available_from' => $request->available_from,
            'available_to' => $request->available_to,
            'sort_order' => $request->sort_order ?? 0,
        ]);
        
        return redirect()->route('admin.delivery.schedules.index')
            ->with('success', 'Delivery schedule "' . $schedule->name . '" has been updated successfully!');
    }
    
    /**
     * Toggle schedule status.
     */
    public function toggleScheduleStatus($id)
    {
        $schedule = DeliverySchedule::findOrFail($id);
        $schedule->is_active = !$schedule->is_active;
        $schedule->save();
        
        $status = $schedule->is_active ? 'activated' : 'deactivated';
        return back()->with('success', 'Schedule has been ' . $status . ' successfully!');
    }
    
    /**
     * Bulk action on schedules.
     */
    public function bulkScheduleAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);
        
        $ids = $request->ids;
        $action = $request->action;
        $count = count($ids);
        
        switch ($action) {
            case 'activate':
                DeliverySchedule::whereIn('id', $ids)->update(['is_active' => true]);
                return back()->with('success', $count . ' schedule(s) activated successfully!');
                
            case 'deactivate':
                DeliverySchedule::whereIn('id', $ids)->update(['is_active' => false]);
                return back()->with('success', $count . ' schedule(s) deactivated successfully!');
                
            case 'delete':
                DeliverySchedule::whereIn('id', $ids)->delete();
                return back()->with('success', $count . ' schedule(s) deleted successfully!');
                
            default:
                return back()->with('error', 'Invalid action selected!');
        }
    }
    
    /**
     * Delete a delivery schedule.
     */
    public function destroySchedule($id)
    {
        $schedule = DeliverySchedule::findOrFail($id);
        $name = $schedule->name;
        $schedule->delete();
        
        return redirect()->route('admin.delivery.schedules.index')
            ->with('success', 'Delivery schedule "' . $name . '" has been deleted successfully!');
    }

    public function reports(Request $request)
    {
        // Date range filter
        $dateRange = $request->get('date_range', 'this_month');
        $startDate = $this->getStartDate($dateRange);
        $endDate = Carbon::now()->endOfDay();
        
        // Get overall delivery statistics
        $stats = $this->getDeliveryReportStats($startDate, $endDate);
        
        // Get delivery trends (last 7 days)
        $trends = $this->getDeliveryTrends($startDate, $endDate);
        
        // Get zone performance
        $zonePerformance = $this->getZonePerformance($startDate, $endDate);
        
        // Get delivery boy performance
        $deliveryBoyPerformance = $this->getDeliveryBoyPerformance($startDate, $endDate);
        
        // Get failed deliveries
        $failedDeliveries = $this->getFailedDeliveries($startDate, $endDate);
        
        // Get shipping revenue by carrier
        $carrierRevenue = $this->getCarrierRevenue($startDate, $endDate);
        
        // Get delivery time analysis
        $deliveryTimeAnalysis = $this->getDeliveryTimeAnalysis($startDate, $endDate);
        
        // Get status breakdown
        $statusBreakdown = $this->getStatusBreakdown($startDate, $endDate);
        
        return view('admin.delivery.reports', compact(
            'stats',
            'trends',
            'zonePerformance',
            'deliveryBoyPerformance',
            'failedDeliveries',
            'carrierRevenue',
            'deliveryTimeAnalysis',
            'statusBreakdown',
            'dateRange'
        ));
    }
    
    /**
     * Get overall delivery report statistics.
     */
    private function getDeliveryReportStats($startDate, $endDate)
    {
        // Total orders with shipping in the period
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('shipping_cost', '>', 0)
            ->count();
        
        // Delivered orders
        $delivered = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->where('shipping_cost', '>', 0)
            ->count();
        
        // Shipped/In Transit
        $inTransit = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'shipped')
            ->where('shipping_cost', '>', 0)
            ->count();
        
        // Pending shipments
        $pending = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('shipping_cost', '>', 0)
            ->count();
        
        // Failed/Cancelled/Refunded
        $cancelled = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'cancelled')
            ->where('shipping_cost', '>', 0)
            ->count();
        
        $failed = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['cancelled', 'refunded', 'returned'])
            ->where('shipping_cost', '>', 0)
            ->count();
        
        // Calculate success rate
        $successRate = $totalOrders > 0 ? round(($delivered / $totalOrders) * 100, 1) : 0;
        
        // Total shipping revenue
        $totalShippingRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('payment_status', ['paid', 'completed'])
            ->sum('shipping_cost');
        
        // Total order revenue (for context)
        $totalOrderRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('payment_status', ['paid', 'completed'])
            ->sum('total');
        
        // Average shipping cost per order
        $avgShippingCost = $totalOrders > 0 ? round($totalShippingRevenue / $totalOrders, 2) : 0;
        
        return [
            'total_orders' => $totalOrders,
            'delivered' => $delivered,
            'in_transit' => $inTransit,
            'pending' => $pending,
            'cancelled' => $cancelled,
            'failed' => $failed,
            'success_rate' => $successRate,
            'total_shipping_revenue' => round($totalShippingRevenue, 2),
            'total_order_revenue' => round($totalOrderRevenue, 2),
            'avg_shipping_cost' => $avgShippingCost,
        ];
    }
    
    /**
     * Get delivery trends for the selected period.
     */
    private function getDeliveryTrends($startDate, $endDate)
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('shipping_cost', '>', 0)
            ->selectRaw('DATE(created_at) as date, 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered,
                        SUM(CASE WHEN status = "shipped" THEN 1 ELSE 0 END) as shipped,
                        SUM(CASE WHEN status IN ("cancelled", "refunded", "returned") THEN 1 ELSE 0 END) as failed,
                        SUM(shipping_cost) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return $orders;
    }
    
    /**
     * Get zone performance breakdown (by shipping city).
     */
    private function getZonePerformance($startDate, $endDate)
    {
        // Since orders don't have zone_id, we'll use shipping_city for zone analysis
        $cities = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('shipping_cost', '>', 0)
            ->whereNotNull('shipping_city')
            ->selectRaw('shipping_city as name, 
                        COUNT(*) as total_orders,
                        SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered,
                        SUM(CASE WHEN status IN ("cancelled", "refunded", "returned") THEN 1 ELSE 0 END) as failed,
                        SUM(shipping_cost) as revenue')
            ->groupBy('shipping_city')
            ->orderByDesc('total_orders')
            ->limit(10)
            ->get()
            ->map(function($city) {
                return [
                    'id' => null,
                    'name' => $city->name ?? 'Unknown',
                    'total_orders' => $city->total_orders,
                    'delivered' => $city->delivered,
                    'failed' => $city->failed,
                    'success_rate' => $city->total_orders > 0 ? round(($city->delivered / $city->total_orders) * 100, 1) : 0,
                    'revenue' => round($city->revenue, 2),
                ];
            });
        
        return $cities;
    }
    
    /**
     * Get delivery boy performance.
     */
    private function getDeliveryBoyPerformance($startDate, $endDate)
    {
        $deliveryBoys = DeliveryBoy::active()
            ->with('zone')
            ->get()
            ->map(function($boy) {
                $total = $boy->total_deliveries ?? 0;
                $delivered = $boy->successful_deliveries ?? 0;
                $failed = $boy->failed_deliveries ?? 0;
                
                return [
                    'id' => $boy->id,
                    'name' => $boy->name,
                    'phone' => $boy->phone,
                    'zone_name' => $boy->zone ? $boy->zone->name : 'Unassigned',
                    'total_deliveries' => $total,
                    'successful_deliveries' => $delivered,
                    'failed_deliveries' => $failed,
                    'success_rate' => $total > 0 ? round(($delivered / $total) * 100, 1) : 0,
                    'avg_delivery_hours' => 'N/A',
                    'rating' => $boy->rating ?? 0,
                    'is_available' => $boy->is_available,
                ];
            })
            ->sortByDesc('total_deliveries')
            ->take(10);
        
        return $deliveryBoys;
    }
    
    /**
     * Get failed deliveries.
     */
    private function getFailedDeliveries($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['cancelled', 'refunded', 'returned'])
            ->where('shipping_cost', '>', 0)
            ->select('id', 'order_number', 'status', 'total', 'shipping_cost', 'shipping_city', 'created_at', 'notes')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }
    
    /**
     * Get carrier revenue breakdown.
     */
    private function getCarrierRevenue($startDate, $endDate)
    {
        // Get carriers with their orders in the period
        $carriers = Carrier::active()
            ->get()
            ->map(function($carrier) use ($startDate, $endDate) {
                // Get orders assigned to this carrier in the period
                $orderStats = Order::whereBetween('created_at', [$startDate, $endDate])
                    ->where('shipping_company', $carrier->name)
                    ->whereIn('payment_status', ['paid', 'completed'])
                    ->selectRaw('COUNT(*) as total_orders, SUM(shipping_cost) as shipping_revenue, SUM(total) as total_revenue')
                    ->first();
                
                return [
                    'id' => $carrier->id,
                    'name' => $carrier->name,
                    'logo' => $carrier->logo,
                    'total_orders' => $orderStats ? $orderStats->total_orders : 0,
                    'shipping_revenue' => round($orderStats ? $orderStats->shipping_revenue : 0, 2),
                    'total_revenue' => round($orderStats ? $orderStats->total_revenue : 0, 2),
                ];
            })
            ->filter(function($carrier) {
                return $carrier['total_orders'] > 0;
            })
            ->sortByDesc('total_orders');
        
        return $carriers;
    }
    
    /**
     * Get delivery time analysis.
     */
    private function getDeliveryTimeAnalysis($startDate, $endDate)
    {
        $deliveredOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->whereNotNull('picked_up_at')
            ->get();
        
        $totalDelivered = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->count();
        
        $within24h = 0;
        $within48h = 0;
        $within72h = 0;
        $over72h = 0;
        $totalHours = 0;
        $ordersWithPickup = 0;
        
        foreach ($deliveredOrders as $order) {
            $pickupDate = $order->picked_up_at;
            $deliveredDate = $order->updated_at;
            
            $hours = $pickupDate->diffInHours($deliveredDate);
            $totalHours += $hours;
            $ordersWithPickup++;
            
            if ($hours < 24) {
                $within24h++;
            } elseif ($hours < 48) {
                $within48h++;
            } elseif ($hours < 72) {
                $within72h++;
            } else {
                $over72h++;
            }
        }
        
        $avgHours = $ordersWithPickup > 0 ? round($totalHours / $ordersWithPickup, 1) : 0;
        
        return [
            'avg_hours' => $avgHours,
            'total_delivered' => $totalDelivered,
            'within_24h' => $within24h,
            'within_48h' => $within48h,
            'within_72h' => $within72h,
            'over_72h' => $over72h,
            'within_24h_percent' => $ordersWithPickup > 0 ? round(($within24h / $ordersWithPickup) * 100, 1) : 0,
            'within_48h_percent' => $ordersWithPickup > 0 ? round(($within48h / $ordersWithPickup) * 100, 1) : 0,
            'within_72h_percent' => $ordersWithPickup > 0 ? round(($within72h / $ordersWithPickup) * 100, 1) : 0,
            'over_72h_percent' => $ordersWithPickup > 0 ? round(($over72h / $ordersWithPickup) * 100, 1) : 0,
        ];
    }
    
    /**
     * Get status breakdown.
     */
    private function getStatusBreakdown($startDate, $endDate)
    {
        $breakdown = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('shipping_cost', '>', 0)
            ->selectRaw('status, COUNT(*) as count, SUM(shipping_cost) as revenue')
            ->groupBy('status')
            ->get()
            ->map(function($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                    'revenue' => round($item->revenue, 2),
                ];
            });
        
        return $breakdown;
    }
    
    /**
     * Export reports to CSV.
     */
    public function exportReports(Request $request)
    {
        $dateRange = $request->get('date_range', 'this_month');
        $startDate = $this->getStartDate($dateRange);
        $endDate = Carbon::now()->endOfDay();
        
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('shipping_cost', '>', 0)
            ->select('order_number', 'status', 'payment_status', 'total', 'shipping_cost', 'shipping_city', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $filename = 'delivery_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Order Number', 'Status', 'Payment Status', 'Total', 'Shipping Cost', 'City', 'Order Date']);
            
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->status,
                    $order->payment_status,
                    $order->total,
                    $order->shipping_cost,
                    $order->shipping_city,
                    $order->created_at->format('Y-m-d H:i'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
