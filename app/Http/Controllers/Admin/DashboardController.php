<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\UserSearch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Orders that count as revenue: paid, and not cancelled/refunded.
     *
     * updateStatus() changes `status` without touching `payment_status`, so a cancelled
     * order can keep payment_status = 'paid'. Filtering on payment status alone counts
     * money that was cancelled or given back.
     */
    private function revenueOrders()
    {
        return Order::query()->revenue();
    }

    /**
     * Real stock per product: the sum of per-variant stock when the product has variant
     * stock rows, otherwise the flat products.quantity.
     */
    private function effectiveStock()
    {
        $variantStock = DB::table('product_attribute_values')
            ->select('product_id', DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_id');

        return DB::table('products as p')
            ->leftJoinSub($variantStock, 'v', 'v.product_id', '=', 'p.id');
    }

    /**
     * Status counts for every value the `orders.status` enum actually allows, so no order
     * is invisible and no card counts a status that cannot exist.
     */
    private function orderStatusBreakdown(): array
    {
        $labels = [
            'pending' => ['Pending', 'bg-warning', '#fbbf24'],
            'processing' => ['Processing', 'bg-info', '#3b82f6'],
            'confirmed' => ['Confirmed', 'bg-primary', '#6366f1'],
            'shipped' => ['Shipped', 'bg-secondary', '#f97316'],
            'delivered' => ['Delivered', 'bg-success', '#22c55e'],
            'cancelled' => ['Cancelled', 'bg-danger', '#ef4444'],
            'refunded' => ['Refunded', 'bg-dark', '#64748b'],
        ];

        $counts = Order::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $out = [];
        foreach ($labels as $key => [$label, $dot, $color]) {
            $out[$key] = [
                'label' => $label,
                'count' => (int) ($counts[$key] ?? 0),
                'dot' => $dot,
                'color' => $color,
            ];
        }

        return $out;
    }

    /**
     * Display admin dashboard.
     */
    public function index()
    {
        $totalSales = $this->revenueOrders()->sum('total');
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();

        $todaySales = $this->revenueOrders()
            ->whereDate('created_at', today())
            ->sum('total');

        $todayOrders = Order::whereDate('created_at', today())->count();

        $recentOrders = Order::latest()->take(5)->get();

        // Order status counts — every status the enum allows, so the cards always add up
        $orderStatuses = $this->orderStatusBreakdown();

        // Product status counts (variant-aware: see effectiveStock)
        $activeProducts = Product::where('is_active', true)->count();
        $outOfStockProducts = $this->effectiveStock()
            ->whereRaw('COALESCE(v.qty, p.quantity) <= 0')
            ->count();
        $lowStockProducts = $this->effectiveStock()
            ->whereRaw('COALESCE(v.qty, p.quantity) > 0 AND COALESCE(v.qty, p.quantity) <= COALESCE(p.low_stock_threshold, 10)')
            ->count();

        // Top products — two measures side by side, because they answer different questions:
        //   units_ordered : demand. Everything the shop was asked to fulfil, excluding only
        //                   cancelled/refunded orders (payment may still be pending — COD).
        //   revenue_paid  : money actually collected. Paid orders only.
        // Ranking by paid revenue alone leaves this card empty on a COD shop while real
        // orders keep arriving, so demand is the sort key and revenue is shown beside it.
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->select(
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as units_ordered'),
                DB::raw("SUM(CASE WHEN orders.payment_status = 'paid' THEN order_items.quantity * order_items.price ELSE 0 END) as revenue_paid")
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('units_ordered')
            ->take(5)
            ->get();

        // Category distribution
        $categoryDistribution = Category::withCount('products')
            ->orderByDesc('products_count')
            ->take(5)
            ->get();

        // Sales by month for current year (single grouped query)
        $salesByMonth = $this->revenueOrders()
            ->whereYear('created_at', date('Y'))
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('month')
            ->pluck('total', 'month');

        $ordersByMonth = Order::whereYear('created_at', date('Y'))
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total'))
            ->groupBy('month')
            ->pluck('total', 'month');

        // Prepare monthly sales data for chart (all 12 months)
        $monthlySalesData = [];
        $monthlyOrderCounts = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlySalesData[] = round($salesByMonth->get($i, 0), 2);
            $monthlyOrderCounts[] = (int) $ordersByMonth->get($i, 0);
        }

        // Last 7 days sales (single grouped query, zero-filled)
        $last7 = $this->revenueOrders()
            ->whereDate('created_at', '>=', now()->subDays(6)->toDateString())
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('SUM(total) as total'))
            ->groupBy('d')
            ->pluck('total', 'd');

        $last7DaysSales = [];
        $last7DaysLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $last7DaysLabels[] = $date->format('D');
            $last7DaysSales[] = round($last7->get($date->toDateString(), 0), 2);
        }

        // Today's hourly sales (single grouped query, zero-filled)
        $todayByHour = $this->revenueOrders()
            ->whereDate('created_at', today())
            ->select(DB::raw('HOUR(created_at) as h'), DB::raw('SUM(total) as total'))
            ->groupBy('h')
            ->pluck('total', 'h');

        $todayHourlySales = [];
        $todayHourlyLabels = [];
        for ($i = 0; $i < 24; $i++) {
            $todayHourlyLabels[] = sprintf('%02d:00', $i);
            $todayHourlySales[] = round($todayByHour->get($i, 0), 2);
        }

        // Last 30 days sales (single grouped query, zero-filled)
        $last30 = $this->revenueOrders()
            ->whereDate('created_at', '>=', now()->subDays(29)->toDateString())
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('SUM(total) as total'))
            ->groupBy('d')
            ->pluck('total', 'd');

        $last30DaysSales = [];
        $last30DaysLabels = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $last30DaysLabels[] = $date->format('d M');
            $last30DaysSales[] = round($last30->get($date->toDateString(), 0), 2);
        }

        // Growth: month-to-date vs the same stretch of last month. Comparing a partial
        // month against a whole month makes every early-month reading look like a collapse.
        $mtdStart = now()->copy()->startOfMonth();
        $mtdEnd = now()->copy();

        $prevStart = now()->copy()->subMonthNoOverflow()->startOfMonth();
        $prevEnd = $prevStart->copy()->addDays(now()->day - 1);
        if ($prevEnd->gt($prevStart->copy()->endOfMonth())) {
            $prevEnd = $prevStart->copy()->endOfMonth();
        }

        $currentMonthSales = $this->revenueOrders()
            ->whereBetween('created_at', [$mtdStart, $mtdEnd])
            ->sum('total');

        $lastMonthSales = $this->revenueOrders()
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('total');

        $salesGrowth = $lastMonthSales > 0
            ? round((($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1)
            : ($currentMonthSales > 0 ? 100 : 0);

        // Customer growth
        $lastMonthCustomers = User::where('role', 'customer')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $currentMonthCustomers = User::where('role', 'customer')
            ->whereBetween('created_at', [$mtdStart, $mtdEnd])
            ->count();

        $customerGrowth = $lastMonthCustomers > 0
            ? round((($currentMonthCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100, 1)
            : ($currentMonthCustomers > 0 ? 100 : 0);

        // Order growth (same window)
        $lastMonthOrders = Order::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $currentMonthOrders = Order::whereBetween('created_at', [$mtdStart, $mtdEnd])->count();

        $orderGrowth = $lastMonthOrders > 0
            ? round((($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : ($currentMonthOrders > 0 ? 100 : 0);

        // Sales by category (revenue-bearing orders only)
        $salesByCategory = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.payment_status', 'paid')
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->select('categories.name', DB::raw('SUM(order_items.quantity * order_items.price) as total_sales'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sales')
            ->take(6)
            ->get();

        // Payment method distribution
        $paymentMethods = $this->revenueOrders()
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();

        return view('admin.dashboard', compact(
            'totalSales',
            'totalOrders',
            'totalProducts',
            'totalCustomers',
            'todaySales',
            'todayOrders',
            'recentOrders',
            'topProducts',
            'salesByMonth',
            'monthlySalesData',
            'monthlyOrderCounts',
            'last7DaysSales',
            'last7DaysLabels',
            'last30DaysSales',
            'last30DaysLabels',
            'todayHourlySales',
            'todayHourlyLabels',
            'orderStatuses',
            'activeProducts',
            'outOfStockProducts',
            'lowStockProducts',
            'categoryDistribution',
            'salesGrowth',
            'customerGrowth',
            'currentMonthCustomers',
            'lastMonthCustomers',
            'orderGrowth',
            'salesByCategory',
            'paymentMethods'
        ));
    }

    /**
     * Display analytics page.
     */
    public function analytics(Request $request)
    {
        // Get date range from request or default to this month
        $period = $request->get('period', 'this_month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Validate custom date range
        if ($period === 'custom') {
            if ($startDate && $endDate) {
                try {
                    $start = Carbon::parse($startDate);
                    $end = Carbon::parse($endDate);
                    if ($start->gt($end)) {
                        return redirect()->route('admin.analytics')->with('error', 'End date must be after start date');
                    }
                } catch (\Exception $e) {
                    return redirect()->route('admin.analytics')->with('error', 'Invalid date format');
                }
            }
        }
        
        // Calculate date range based on period
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        
        // Compare with previous period for growth calculations
        $previousPeriod = $this->getPreviousPeriod($period, $start, $end);
        $prevStart = $previousPeriod['start'];
        $prevEnd = $previousPeriod['end'];
        
        // ============ SALES ANALYTICS ============
        // Shared revenue definition: paid, and not cancelled/refunded (Order::scopeRevenue).
        $currentSales = Order::query()->revenue()
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        $previousSales = Order::query()->revenue()
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('total');

        // Paid orders inside the window — the correct denominator for average order value.
        $currentPaidOrders = Order::query()->revenue()->whereBetween('created_at', [$start, $end])->count();
        $previousPaidOrders = Order::query()->revenue()->whereBetween('created_at', [$prevStart, $prevEnd])->count();
        
        $salesGrowth = $previousSales > 0 
            ? round((($currentSales - $previousSales) / $previousSales) * 100, 1) 
            : ($currentSales > 0 ? 100 : 0);
        
        // ============ ORDER ANALYTICS ============
        $currentOrders = DB::table('orders')->whereBetween('created_at', [$start, $end])->count();
        $previousOrders = DB::table('orders')->whereBetween('created_at', [$prevStart, $prevEnd])->count();
        
        $orderGrowth = $previousOrders > 0 
            ? round((($currentOrders - $previousOrders) / $previousOrders) * 100, 1) 
            : ($currentOrders > 0 ? 100 : 0);
        
        // ============ CUSTOMER ANALYTICS ============
        $currentCustomers = DB::table('users')
            ->where('role', 'customer')
            ->whereBetween('created_at', [$start, $end])
            ->count();
        $previousCustomers = DB::table('users')
            ->where('role', 'customer')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();
        
        $customerGrowth = $previousCustomers > 0 
            ? round((($currentCustomers - $previousCustomers) / $previousCustomers) * 100, 1) 
            : ($currentCustomers > 0 ? 100 : 0);
        
        // ============ PRODUCT ANALYTICS ============
        $totalProducts = DB::table('products')->count();
        $activeProducts = DB::table('products')->where('is_active', true)->count();
        $outOfStockProducts = DB::table('products')->where('quantity', 0)->count();
        $lowStockProducts = DB::table('products')->where('quantity', '>', 0)->where('quantity', '<=', 10)->count();
        
        // Products added in current period
        $newProducts = DB::table('products')->whereBetween('created_at', [$start, $end])->count();
        $prevNewProducts = DB::table('products')->whereBetween('created_at', [$prevStart, $prevEnd])->count();
        
        $productGrowth = $prevNewProducts > 0 
            ? round((($newProducts - $prevNewProducts) / $prevNewProducts) * 100, 1) 
            : ($newProducts > 0 ? 100 : 0);
        
        // ============ SALES BY PERIOD (for chart) ============
        $salesByDay = $this->getSalesByDay($start, $end);
        $salesByMonth = $this->getSalesByMonth($start, $end);
        
        // ============ TOP PRODUCTS ============
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get();
        
        // ============ TOP CATEGORIES ============
        $topCategories = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.payment_status', 'paid')
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->take(8)
            ->get();
        
        // ============ ORDER STATUS BREAKDOWN ============
        $orderStatus = DB::table('orders')->whereBetween('created_at', [$start, $end])
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('status')
            ->get()->keyBy('status');

        $orderStatusColors = [
            // Keys mirror the orders.status enum exactly — no 'completed', no 'paid'.
            'pending' => '#fbbf24',
            'processing' => '#3b82f6',
            'confirmed' => '#6366f1',
            'shipped' => '#f97316',
            'delivered' => '#22c55e',
            'cancelled' => '#ef4444',
            'refunded' => '#64748b',
        ];
        
        // ============ PAYMENT METHOD DISTRIBUTION ============
        $paymentMethods = DB::table('orders')->where('payment_status', 'paid')
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->whereBetween('created_at', [$start, $end])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();
        
        // ============ AVERAGE ORDER VALUE ============
        // Revenue ÷ paid orders. Dividing paid revenue by ALL orders (unpaid included) mixes
        // two different populations and understates the figure.
        $avgOrderValue = $currentPaidOrders > 0 ? round($currentSales / $currentPaidOrders, 2) : 0;
        $prevAvgOrderValue = $previousPaidOrders > 0 ? round($previousSales / $previousPaidOrders, 2) : 0;
        $avgGrowth = $prevAvgOrderValue > 0 
            ? round((($avgOrderValue - $prevAvgOrderValue) / $prevAvgOrderValue) * 100, 1) 
            : ($avgOrderValue > 0 ? 100 : 0);
        
        // ============ PAYMENT RATE (replaces the old "Conversion Rate") ============
        // The old figure was orders ÷ product searches and was labelled a conversion rate.
        // There is no visitor count in this app, so conversion cannot be measured; what can be
        // measured honestly is how much of the period's order volume has actually been paid.
        $paymentRate = $currentOrders > 0
            ? round(($currentPaidOrders / $currentOrders) * 100, 2)
            : 0;
        $totalSearches = UserSearch::whereBetween('created_at', [$start, $end])->count();
        $ordersPerSearch = $totalSearches > 0
            ? round($currentOrders / $totalSearches, 2)
            : null;
        
        // ============ USER SEARCH ANALYTICS ============
        $topSearches = UserSearch::select('query', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('query')
            ->orderByDesc('count')
            ->take(10)
            ->get();
        
        // ============ REVENUE BY DAY (Last 30 days for chart) ============
        $last30DaysRevenue = [];
        $last30DaysLabels = [];
        $revenue30 = Order::query()->revenue()
            ->whereDate('created_at', '>=', now()->subDays(29)->toDateString())
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('SUM(total) as total'))
            ->groupBy('d')
            ->pluck('total', 'd');
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $last30DaysLabels[] = $date->format('d M');
            $last30DaysRevenue[] = round($revenue30->get($date->toDateString(), 0), 2);
        }
        
        // ============ PERIOD-BASED COMPARISON CHART ============
        $chartStart = Carbon::parse($start);
        $chartEnd = Carbon::parse($end);
        $diffInDays = $chartStart->diffInDays($chartEnd);

        $chartLabels = [];
        $chartData = [];

        if ($diffInDays <= 31) {
            $byDay = Order::query()->revenue()
                ->whereBetween('created_at', [$chartStart, $chartEnd])
                ->select(DB::raw('DATE(created_at) as d'), DB::raw('SUM(total) as total'))
                ->groupBy('d')
                ->pluck('total', 'd');
            $current = $chartStart->copy();
            while ($current->lte($chartEnd)) {
                $chartLabels[] = $current->format('d M');
                $chartData[] = round($byDay->get($current->toDateString(), 0), 2);
                $current->addDay();
            }
        } else {
            $byMonth = Order::query()->revenue()
                ->whereBetween('created_at', [$chartStart, $chartEnd])
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as m'), DB::raw('SUM(total) as total'))
                ->groupBy('m')
                ->pluck('total', 'm');
            $current = $chartStart->copy()->startOfMonth();
            while ($current->lte($chartEnd)) {
                $chartLabels[] = $current->format('M Y');
                $chartData[] = round($byMonth->get($current->format('Y-m'), 0), 2);
                $current->addMonth();
            }
        }
        
        // ============ CUSTOMER SEGMENTS ============
        $newCustomers = DB::table('users')->where('role', 'customer')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // Returning = bought in this period AND had already bought before it started.
        // (The old code counted every distinct buyer with a paid order, which includes
        // first-time customers and made the number meaningless.)
        $returningCustomers = Order::query()->revenue()
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('user_id')
            ->whereIn('user_id', function ($q) use ($start) {
                $q->select('user_id')
                  ->from('orders')
                  ->where('payment_status', 'paid')
                  ->whereNotIn('status', ['cancelled', 'refunded'])
                  ->where('created_at', '<', $start)
                  ->whereNotNull('user_id');
            })
            ->distinct()
            ->count('user_id');
        
        return view('admin.analytics', compact(
            'period',
            'startDate',
            'endDate',
            'currentSales',
            'previousSales',
            'salesGrowth',
            'currentOrders',
            'previousOrders',
            'orderGrowth',
            'currentCustomers',
            'previousCustomers',
            'customerGrowth',
            'totalProducts',
            'activeProducts',
            'outOfStockProducts',
            'lowStockProducts',
            'newProducts',
            'productGrowth',
            'topProducts',
            'topCategories',
            'orderStatus',
            'orderStatusColors',
            'paymentMethods',
            'avgOrderValue',
            'avgGrowth',
            'paymentRate',
            'ordersPerSearch',
            'totalSearches',
            'topSearches',
            'last30DaysRevenue',
            'last30DaysLabels',
            'chartLabels',
            'chartData',
            'newCustomers',
            'returningCustomers',
            'salesByDay',
            'salesByMonth'
        ));
    }

    /**
     * Export analytics data to CSV
     */
    public function exportAnalytics(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        
        // Get data for export
        $currentSales = Order::query()->revenue()
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        $currentPaidOrders = Order::query()->revenue()->whereBetween('created_at', [$start, $end])->count();
        
        $currentOrders = DB::table('orders')->whereBetween('created_at', [$start, $end])->count();
        
        $currentCustomers = DB::table('users')
            ->where('role', 'customer')
            ->whereBetween('created_at', [$start, $end])
            ->count();
        
        // Same two-measure shape as the dashboard widget: demand and collected money.
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as units_ordered'),
                DB::raw("SUM(CASE WHEN orders.payment_status = 'paid' THEN order_items.quantity * order_items.price ELSE 0 END) as revenue_paid")
            )
            ->groupBy('order_items.product_name')
            ->orderByDesc('units_ordered')
            ->take(10)
            ->get();
        
        $filename = 'analytics-' . $start->format('Y-m-d') . '-to-' . $end->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $avgOrderValue = $currentPaidOrders > 0 ? round($currentSales / $currentPaidOrders, 2) : 0;

        $callback = function() use ($currentSales, $currentOrders, $currentPaidOrders, $currentCustomers, $avgOrderValue, $topProducts, $start, $end) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ['Analytics Report', $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d')]);
            fputcsv($file, []);

            // Key metrics. Revenue = paid, excluding cancelled/refunded (Order::scopeRevenue).
            fputcsv($file, ['Key Metrics']);
            fputcsv($file, ['Revenue (paid, excl. cancelled/refunded)', '৳' . number_format($currentSales, 2)]);
            fputcsv($file, ['Orders (all)', number_format($currentOrders)]);
            fputcsv($file, ['Orders paid', number_format($currentPaidOrders)]);
            fputcsv($file, ['Average order value (paid)', '৳' . number_format($avgOrderValue, 2)]);
            fputcsv($file, ['New customers', number_format($currentCustomers)]);
            fputcsv($file, []);

            // Top products: demand and collected revenue, same as the dashboard widget.
            fputcsv($file, ['Top Products']);
            fputcsv($file, ['Product Name', 'Units Ordered', 'Paid Revenue']);
            foreach ($topProducts as $product) {
                fputcsv($file, [
                    $product->product_name,
                    number_format($product->units_ordered),
                    '৳' . number_format($product->revenue_paid, 2)
                ]);
            }

            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Get date range based on period
     */
    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        $now = now();
        
        return match ($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay()
            ],
            'yesterday' => [
                'start' => $now->copy()->subDay()->startOfDay(),
                'end' => $now->copy()->subDay()->endOfDay()
            ],
            'this_week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek()
            ],
            'last_week' => [
                'start' => $now->copy()->subWeek()->startOfWeek(),
                'end' => $now->copy()->subWeek()->endOfWeek()
            ],
            'this_month' => [
                // Month-to-date, so growth compares like with like (the dashboard uses the same
                // rule, and the two screens must not quote different numbers).
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth()
            ],
            'this_year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()
            ],
            'custom' => [
                'start' => $startDate ? Carbon::parse($startDate)->startOfDay() : $now->copy()->startOfMonth(),
                'end' => $endDate ? Carbon::parse($endDate)->endOfDay() : $now->copy()->endOfDay()
            ],
            default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth()
            ]
        };
    }
    
    /**
     * Get previous period for comparison
     */
    private function getPreviousPeriod($period, $start, $end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        $diff = $start->diffInDays($end);

        // Elapsed days in the current window, used to build a like-for-like comparison window
        // for month/year presets (month-to-date must be compared with the same stretch).
        $elapsedDays = max(1, $end->dayOfYear - $start->dayOfYear + 1);

        return match ($period) {
            'today' => [
                'start' => $start->copy()->subDay()->startOfDay(),
                'end' => $start->copy()->subDay()->endOfDay()
            ],
            'yesterday' => [
                'start' => $start->copy()->subDays(2)->startOfDay(),
                'end' => $start->copy()->subDays(2)->endOfDay()
            ],
            'this_week' => [
                'start' => $start->copy()->subWeek()->startOfWeek(),
                'end' => $start->copy()->subWeek()->endOfWeek()
            ],
            'last_week' => [
                'start' => $start->copy()->subWeeks(2)->startOfWeek(),
                'end' => $start->copy()->subWeeks(2)->endOfWeek()
            ],
            'this_month' => (function () use ($start, $elapsedDays) {
                // Same day-range of the previous month, clamped to that month's length.
                $prevStart = $start->copy()->subMonthNoOverflow()->startOfMonth();
                $prevEnd = $prevStart->copy()->addDays($elapsedDays - 1);
                if ($prevEnd->gt($prevStart->copy()->endOfMonth())) {
                    $prevEnd = $prevStart->copy()->endOfMonth();
                }
                return ['start' => $prevStart, 'end' => $prevEnd->endOfDay()];
            })(),
            'last_month' => [
                // The month before the one being shown (it used to subtract two months, so
                // September was compared against July).
                'start' => $start->copy()->subMonthNoOverflow()->startOfMonth(),
                'end' => $start->copy()->subMonthNoOverflow()->endOfMonth()
            ],
            'this_year' => (function () use ($start, $elapsedDays) {
                $prevStart = $start->copy()->subYear()->startOfYear();
                $prevEnd = $prevStart->copy()->addDays($elapsedDays - 1);
                if ($prevEnd->gt($prevStart->copy()->endOfYear())) {
                    $prevEnd = $prevStart->copy()->endOfYear();
                }
                return ['start' => $prevStart, 'end' => $prevEnd->endOfDay()];
            })(),
            'custom' => [
                'start' => $start->copy()->subDays($diff + 1)->startOfDay(),
                'end' => $start->copy()->subDay()->endOfDay()
            ],
            default => [
                'start' => $start->copy()->subMonth()->startOfMonth(),
                'end' => $start->copy()->subMonth()->endOfMonth()
            ]
        };
    }
    
    /**
     * Get sales by day for chart
     */
    private function getSalesByDay($start, $end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        
        // If range is more than 60 days, switch to monthly
        if ($start->diffInDays($end) > 60) {
            return $this->getSalesByMonth($start, $end);
        }
        
        $sales = Order::query()->revenue()
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('date')
            ->pluck('total', 'date');
        
        $result = [];
        $labels = [];
        $current = $start->copy();
        
        while ($current->lte($end)) {
            $dateStr = $current->format('Y-m-d');
            $labels[] = $current->format('d M');
            $result[] = round($sales->get($dateStr, 0), 2);
            $current->addDay();
        }
        
        return ['labels' => $labels, 'data' => $result];
    }
    
    /**
     * Get sales by month for chart
     */
    private function getSalesByMonth($start, $end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        
        $sales = Order::query()->revenue()
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('year', 'month')
            ->get();
        
        $result = [];
        $labels = [];
        $current = $start->copy()->startOfMonth();
        
        while ($current->lte($end)) {
            $year = $current->year;
            $month = $current->month;
            $label = $current->format('M Y');
            
            $total = $sales->filter(function($s) use ($year, $month) {
                return $s->year == $year && $s->month == $month;
            })->first()->total ?? 0;
            
            $labels[] = $label;
            $result[] = round($total, 2);
            $current->addMonth();
        }
        
        return ['labels' => $labels, 'data' => $result];
    }

    /**
     * Get sales chart data via AJAX.
     */
    public function salesChart(Request $request)
    {
        $period = $request->get('period', 'week');

        $data = match ($period) {
            'week' => Order::query()->revenue()->where('created_at', '>=', now()->subWeek())
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy('date')
                ->get(),
            'month' => Order::query()->revenue()->where('created_at', '>=', now()->subMonth())
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy('date')
                ->get(),
            'year' => Order::query()->revenue()->where('created_at', '>=', now()->subYear())
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy('month')
                ->get(),
            default => collect(),
        };

        return response()->json($data);
    }

    /**
     * Display admin profile.
     */
    public function profile()
    {
        return view('admin.profile');
    }

    /**
     * Update admin profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only(['name', 'email']));

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update admin password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => \Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }
}
