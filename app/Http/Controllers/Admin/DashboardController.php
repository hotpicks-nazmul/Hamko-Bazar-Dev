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
     * Display admin dashboard.
     */
    public function index()
    {
        $totalSales = Order::where('payment_status', 'paid')->sum('total');
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();
        
        $todaySales = Order::where('payment_status', 'paid')
            ->whereDate('created_at', today())
            ->sum('total');
        
        $todayOrders = Order::whereDate('created_at', today())->count();
        
        $recentOrders = Order::latest()->take(5)->get();
        
        // Order status counts
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        
        // Product status counts
        $activeProducts = Product::where('is_active', true)->count();
        $outOfStockProducts = Product::where('quantity', 0)->count();
        $lowStockProducts = Product::where('quantity', '>', 0)->where('quantity', '<=', 10)->count();
        
        // Top selling products
        $topProducts = DB::table('order_items')
            ->select('product_name', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();
        
        // Category distribution
        $categoryDistribution = Category::withCount('products')
            ->orderByDesc('products_count')
            ->take(5)
            ->get();
        
        // Sales by month for current year (for line/bar chart)
        $salesByMonth = Order::where('payment_status', 'paid')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('total', 'month');

        // Prepare monthly sales data for chart (all 12 months)
        $monthlySalesData = [];
        $monthlyOrderCounts = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlySalesData[] = round($salesByMonth->get($i, 0), 2);
            $monthlyOrderCounts[] = Order::whereYear('created_at', date('Y'))
                ->whereMonth('created_at', $i)
                ->count();
        }

        // Last 7 days sales data (for area chart)
        $last7DaysSales = [];
        $last7DaysLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $last7DaysLabels[] = $date->format('D');
            $last7DaysSales[] = round(Order::where('payment_status', 'paid')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->sum('total'), 2);
        }

        // Today's hourly sales data (for daily view)
        $todayHourlySales = [];
        $todayHourlyLabels = [];
        for ($i = 0; $i < 24; $i++) {
            $todayHourlyLabels[] = sprintf('%02d:00', $i);
            $todayHourlySales[] = round(Order::where('payment_status', 'paid')
                ->whereDate('created_at', today())
                ->whereRaw('HOUR(created_at) = ?', [$i])
                ->sum('total'), 2);
        }

        // Last 30 days sales data
        $last30DaysSales = [];
        $last30DaysLabels = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $last30DaysLabels[] = $date->format('d M');
            $last30DaysSales[] = round(Order::where('payment_status', 'paid')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->sum('total'), 2);
        }

        // Growth calculations
        $lastMonthSales = Order::where('payment_status', 'paid')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total');
        
        $currentMonthSales = Order::where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $salesGrowth = $lastMonthSales > 0 
            ? round((($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1) 
            : ($currentMonthSales > 0 ? 100 : 0);

        // Customer growth
        $lastMonthCustomers = User::where('role', 'customer')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        $currentMonthCustomers = User::where('role', 'customer')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $customerGrowth = $lastMonthCustomers > 0 
            ? round((($currentMonthCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100, 1) 
            : ($currentMonthCustomers > 0 ? 100 : 0);

        // Order growth
        $lastMonthOrders = Order::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        $currentMonthOrders = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $orderGrowth = $lastMonthOrders > 0 
            ? round((($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1) 
            : ($currentMonthOrders > 0 ? 100 : 0);

        // Sales by category for pie/doughnut chart
        $salesByCategory = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(order_items.quantity * order_items.price) as total_sales'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sales')
            ->take(6)
            ->get();

        // Payment method distribution
        $paymentMethods = Order::where('payment_status', 'paid')
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
            'pendingOrders',
            'processingOrders',
            'completedOrders',
            'cancelledOrders',
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
        $currentSales = DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');
        
        $previousSales = DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('total');
        
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
            'pending' => '#fbbf24',
            'processing' => '#3b82f6',
            'completed' => '#22c55e',
            'cancelled' => '#ef4444',
            'delivered' => '#8b5cf6',
            'shipped' => '#f97316',
            'paid' => '#14b8a6',
            'refunded' => '#64748b',
        ];
        
        // ============ PAYMENT METHOD DISTRIBUTION ============
        $paymentMethods = DB::table('orders')->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();
        
        // ============ AVERAGE ORDER VALUE ============
        $avgOrderValue = $currentOrders > 0 ? round($currentSales / $currentOrders, 2) : 0;
        $prevAvgOrderValue = $previousOrders > 0 ? round($previousSales / $previousOrders, 2) : 0;
        $avgGrowth = $prevAvgOrderValue > 0 
            ? round((($avgOrderValue - $prevAvgOrderValue) / $prevAvgOrderValue) * 100, 1) 
            : ($avgOrderValue > 0 ? 100 : 0);
        
        // ============ CONVERSION RATE (Orders / Visitors) ============
        // Using user searches as a proxy for visitors
        $totalSearches = UserSearch::whereBetween('created_at', [$start, $end])->count();
        $conversionRate = $totalSearches > 0 ? round(($currentOrders / $totalSearches) * 100, 2) : 0;
        
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
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $last30DaysLabels[] = $date->format('d M');
            $last30DaysRevenue[] = round(DB::table('orders')->where('payment_status', 'paid')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->sum('total'), 2);
        }
        
        // ============ PERIOD-BASED COMPARISON CHART ============
        $chartStart = Carbon::parse($start);
        $chartEnd = Carbon::parse($end);
        $diffInDays = $chartStart->diffInDays($chartEnd);

        $chartLabels = [];
        $chartData = [];

        if ($diffInDays <= 31) {
            $current = $chartStart->copy();
            while ($current->lte($chartEnd)) {
                $chartLabels[] = $current->format('d M');
                $chartData[] = round(DB::table('orders')->where('payment_status', 'paid')
                    ->whereDate('created_at', $current->format('Y-m-d'))
                    ->sum('total'), 2);
                $current->addDay();
            }
        } else {
            $current = $chartStart->copy()->startOfMonth();
            while ($current->lte($chartEnd)) {
                $chartLabels[] = $current->format('M Y');
                $chartData[] = round(DB::table('orders')->where('payment_status', 'paid')
                    ->whereYear('created_at', $current->year)
                    ->whereMonth('created_at', $current->month)
                    ->sum('total'), 2);
                $current->addMonth();
            }
        }
        
        // ============ CUSTOMER SEGMENTS ============
        $newCustomers = DB::table('users')->where('role', 'customer')
            ->whereBetween('created_at', [$start, $end])
            ->count();
        $returningCustomers = DB::table('orders')->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('user_id')
            ->distinct('user_id')
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
            'conversionRate',
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
        $currentSales = DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');
        
        $currentOrders = DB::table('orders')->whereBetween('created_at', [$start, $end])->count();
        
        $currentCustomers = DB::table('users')
            ->where('role', 'customer')
            ->whereBetween('created_at', [$start, $end])
            ->count();
        
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get();
        
        $filename = 'analytics-' . $start->format('Y-m-d') . '-to-' . $end->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($currentSales, $currentOrders, $currentCustomers, $topProducts, $start, $end) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['Analytics Report', $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d')]);
            fputcsv($file, []);
            
            // Key Metrics
            fputcsv($file, ['Key Metrics']);
            fputcsv($file, ['Total Revenue', '৳' . number_format($currentSales, 2)]);
            fputcsv($file, ['Total Orders', number_format($currentOrders)]);
            fputcsv($file, ['New Customers', number_format($currentCustomers)]);
            fputcsv($file, []);
            
            // Top Products
            fputcsv($file, ['Top Selling Products']);
            fputcsv($file, ['Product Name', 'Units Sold', 'Revenue']);
            foreach ($topProducts as $product) {
                fputcsv($file, [
                    $product->product_name,
                    number_format($product->total_sold),
                    '৳' . number_format($product->total_revenue, 2)
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
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth()
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth()
            ],
            'this_year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear()
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
            'this_month' => [
                'start' => $start->copy()->subMonth()->startOfMonth(),
                'end' => $start->copy()->subMonth()->endOfMonth()
            ],
            'last_month' => [
                'start' => $start->copy()->subMonths(2)->startOfMonth(),
                'end' => $start->copy()->subMonths(2)->endOfMonth()
            ],
            'this_year' => [
                'start' => $start->copy()->subYear()->startOfYear(),
                'end' => $start->copy()->subYear()->endOfYear()
            ],
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
        
        $sales = DB::table('orders')->where('payment_status', 'paid')
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
        
        $sales = DB::table('orders')->where('payment_status', 'paid')
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
            'week' => DB::table('orders')->where('created_at', '>=', now()->subWeek())
                ->where('payment_status', 'paid')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy('date')
                ->get(),
            'month' => DB::table('orders')->where('created_at', '>=', now()->subMonth())
                ->where('payment_status', 'paid')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy('date')
                ->get(),
            'year' => DB::table('orders')->where('created_at', '>=', now()->subYear())
                ->where('payment_status', 'paid')
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
