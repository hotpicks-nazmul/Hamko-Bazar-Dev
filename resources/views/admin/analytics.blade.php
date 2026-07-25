@extends('admin.layouts.app')

@section('title', 'Analytics')

@section('content')
@if(auth()->user()->hasPermission('analytics') && auth()->user()->hasPermission('analytics.view'))
<!-- Page Header -->
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Analytics</h4>
    @if(auth()->user()->hasPermission('analytics.export'))
    <a href="{{ route('admin.analytics.export', request()->query()) }}" class="btn btn-success">
        <i class="bi bi-download me-1"></i> Export
    </a>
    @endif
</div>

<!-- Period Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.analytics') }}" class="row g-3 align-items-end">
            <div class="col-lg-2 col-md-3 col-sm-6">
                <label class="form-label small text-muted">Period</label>
                <select name="period" id="periodSelect" class="form-select form-select-sm">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="last_week" {{ $period === 'last_week' ? 'selected' : '' }}>Last Week</option>
                    <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
            
            <!-- Custom Date Range (shown when custom is selected) -->
            <div class="col-lg-3 col-md-3 col-sm-6 custom-date-range" style="{{ $period !== 'custom' ? 'display:none;' : '' }}">
                <label class="form-label small text-muted">Start Date</label>
                <input type="date" name="start_date" id="startDate" class="form-control form-control-sm" value="{{ $startDate ?? '' }}">
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6 custom-date-range" style="{{ $period !== 'custom' ? 'display:none;' : '' }}">
                <label class="form-label small text-muted">End Date</label>
                <input type="date" name="end_date" id="endDate" class="form-control form-control-sm" value="{{ $endDate ?? '' }}">
            </div>
            
            <div class="col-lg-2 col-md-2 col-sm-4">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
            </div>
            
            <div class="col-lg-2 col-md-3 col-sm-4 ms-auto">
                <a href="{{ route('admin.analytics') }}" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-x-lg me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

@if(auth()->user()->hasPermission('analytics.view-revenue'))
<!-- Top Financial Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Revenue</span>
            <span class="stat-card-value">৳{{ number_format($currentSales, 2) }}</span>
        </div>
    </div>
    <div class="stat-card {{ $salesGrowth >= 0 ? 'stat-card-success' : 'stat-card-danger' }}">
        <div class="stat-card-icon"><i class="bi {{ $salesGrowth >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }}"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Sales Growth</span>
            <span class="stat-card-value">{{ number_format($salesGrowth, 1) }}%</span>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->hasPermission('analytics.view-sales'))
<!-- Sales/Order Stats -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-cart-check"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Orders</span>
            <span class="stat-card-value">{{ number_format($currentOrders) }}</span>
        </div>
    </div>
</div>
@endif

<!-- Operational Stats (always visible) -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-people"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">New Customers</span>
            <span class="stat-card-value">{{ number_format($currentCustomers) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-box-seam"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Products</span>
            <span class="stat-card-value">{{ number_format($totalProducts) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Out of Stock</span>
            <span class="stat-card-value">{{ number_format($outOfStockProducts) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-exclamation-diamond"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Low Stock</span>
            <span class="stat-card-value">{{ number_format($lowStockProducts) }}</span>
        </div>
    </div>
</div>

<!-- Growth Metrics Cards -->
<div class="stat-card-row mb-4">
    @if(auth()->user()->hasPermission('analytics.view-sales'))
    <!-- Orders Growth -->
    <div class="stat-card {{ $orderGrowth >= 0 ? 'stat-card-success' : 'stat-card-danger' }}">
        <div class="stat-card-icon"><i class="bi {{ $orderGrowth >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }}"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Orders Growth</span>
            <span class="stat-card-value">{{ number_format($orderGrowth, 1) }}%</span>
        </div>
    </div>
    @endif
    
    <!-- Customers Growth -->
    <div class="stat-card {{ $customerGrowth >= 0 ? 'stat-card-success' : 'stat-card-danger' }}">
        <div class="stat-card-icon"><i class="bi {{ $customerGrowth >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }}"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Customers Growth</span>
            <span class="stat-card-value">{{ number_format($customerGrowth, 1) }}%</span>
        </div>
    </div>
    
    <!-- Products Growth -->
    <div class="stat-card {{ $productGrowth >= 0 ? 'stat-card-success' : 'stat-card-danger' }}">
        <div class="stat-card-icon"><i class="bi {{ $productGrowth >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }}"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Products Growth</span>
            <span class="stat-card-value">{{ number_format($productGrowth, 1) }}%</span>
        </div>
    </div>
    
    <!-- Avg Order Value Growth -->
    <div class="stat-card {{ $avgGrowth >= 0 ? 'stat-card-success' : 'stat-card-danger' }}">
        <div class="stat-card-icon"><i class="bi {{ $avgGrowth >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }}"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Avg Value Growth</span>
            <span class="stat-card-value">{{ number_format($avgGrowth, 1) }}%</span>
        </div>
    </div>
</div>

@if(auth()->user()->hasPermission('analytics.view-revenue'))
<!-- Key Metrics Cards -->
<div class="stat-card-row mb-4">
    <!-- Average Order Value -->
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon">
            <i class="bi bi-receipt"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Avg. Order Value</span>
            <span class="stat-card-value">৳{{ number_format($avgOrderValue, 2) }}</span>
        </div>
    </div>
    
    <!-- Conversion Rate -->
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon">
            <i class="bi bi-bullseye"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Conversion Rate</span>
            <span class="stat-card-value">{{ $conversionRate }}%</span>
        </div>
    </div>
    
    <!-- Total Searches -->
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon">
            <i class="bi bi-search"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Searches</span>
            <span class="stat-card-value">{{ number_format($totalSearches) }}</span>
        </div>
    </div>
    
    <!-- Active Products -->
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active Products</span>
            <span class="stat-card-value">{{ number_format($activeProducts) }}</span>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->hasPermission('analytics.view-revenue'))
<!-- Revenue Trend Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-semibold">Revenue Trend</h5>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenueTrendChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->hasPermission('analytics.view-sales'))
<!-- Order Status Distribution -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Order Status</h5>
            </div>
            @if($orderStatus->isNotEmpty())
            <div class="card-body">
                <div style="position: relative; width: 180px; height: 180px; margin: 0 auto 15px;">
                    <canvas id="orderStatusChart"></canvas>
                </div>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    @foreach($orderStatus as $status)
                    <div class="text-center" style="min-width: 50px;">
                        <span class="badge rounded-circle d-block mx-auto mb-1" style="width: 10px; height: 10px; background-color: {{ $orderStatusColors[$status->status] ?? '#6c757d' }};"></span>
                        <div class="small text-muted text-capitalize">{{ $status->status }}</div>
                        <div class="fw-bold">{{ $status->count }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="card-body d-flex align-items-center justify-content-center">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-pie-chart" style="font-size: 3rem;"></i>
                    <p class="mt-2">No order data available</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@if(auth()->user()->hasPermission('analytics.view-revenue'))
<!-- Second Row Charts -->
<div class="row mb-4">
    <!-- Sales by Category -->
    <div class="col-xl-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Sales by Category</h5>
            </div>
            <div class="card-body">
                @if($topCategories->isNotEmpty())
                    <canvas id="categoryChart" height="250"></canvas>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-folder" style="font-size: 3rem;"></i>
                        <p class="mt-2">No category data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Payment Methods -->
    <div class="col-xl-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Payment Methods</h5>
            </div>
            <div class="card-body">
                @if($paymentMethods->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Method</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalPaymentAmount = $paymentMethods->sum('total'); @endphp
                                @foreach($paymentMethods as $method)
                                <tr>
                                    <td>
                                        <span class="text-capitalize">{{ $method->payment_method ?? 'Unknown' }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format($method->count) }}</td>
                                    <td class="text-end">৳{{ number_format($method->total, 2) }}</td>
                                    <td class="text-end">
                                        @php $share = $totalPaymentAmount > 0 ? ($method->total / $totalPaymentAmount) * 100 : 0; @endphp
                                        <div class="progress" style="height: 6px; width: 60px; display: inline-block; vertical-align: middle;">
                                            <div class="progress-bar bg-primary" style="width: {{ $share }}%"></div>
                                        </div>
                                        <span class="ms-2 small">{{ number_format($share, 1) }}%</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-credit-card" style="font-size: 3rem;"></i>
                        <p class="mt-2">No payment data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Third Row - Tables -->
<div class="row mb-4">
    <!-- Top Products -->
    <div class="col-xl-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Top Selling Products</h5>
            </div>
            <div class="card-body p-0">
                @if($topProducts->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Sold</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topProducts as $product)
                                <tr>
                                    <td>
                                        <div class="fw-medium text-truncate" style="max-width: 200px;" title="{{ $product->product_name }}">
                                            {{ $product->product_name }}
                                        </div>
                                    </td>
                                    <td class="text-end">{{ number_format($product->total_sold) }}</td>
                                    <td class="text-end">৳{{ number_format($product->total_revenue, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-box-seam" style="font-size: 3rem;"></i>
                        <p class="mt-2">No product sales data</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Top Categories -->
    <div class="col-xl-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Top Categories</h5>
            </div>
            <div class="card-body p-0">
                @if($topCategories->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Sold</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCategories as $category)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $category->name }}</div>
                                    </td>
                                    <td class="text-end">{{ number_format($category->total_sold) }}</td>
                                    <td class="text-end">৳{{ number_format($category->total_revenue, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-folder" style="font-size: 3rem;"></i>
                        <p class="mt-2">No category data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Fourth Row - More Analytics -->
<div class="row mb-4">
    <!-- Customer Analytics -->
    <div class="col-xl-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Customer Analytics</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">New Customers</span>
                        <span class="fw-medium">{{ number_format($newCustomers) }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Returning Customers</span>
                        <span class="fw-medium">{{ number_format($returningCustomers) }}</span>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Total Products</span>
                        <span class="fw-medium">{{ number_format($totalProducts) }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Active Products</span>
                        <span class="fw-medium text-success">{{ number_format($activeProducts) }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Out of Stock</span>
                        <span class="fw-medium text-danger">{{ number_format($outOfStockProducts) }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Low Stock</span>
                        <span class="fw-medium text-warning">{{ number_format($lowStockProducts) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Search Queries -->
    <div class="col-xl-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Top Search Queries</h5>
            </div>
            <div class="card-body p-0">
                @if($topSearches->isNotEmpty())
                    <div class="list-group list-group-flush">
                        @foreach($topSearches as $search)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span class="text-truncate" style="max-width: 150px;" title="{{ $search->query }}">
                                <i class="bi bi-search me-2 text-muted"></i>{{ $search->query }}
                            </span>
                            <span class="badge bg-primary rounded-pill">{{ $search->count }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-search" style="font-size: 2rem;"></i>
                        <p class="mt-2 small">No search data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Conversion Rate -->
    <div class="col-xl-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Conversion Metrics</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <div class="display-4 fw-bold text-primary">{{ $conversionRate }}%</div>
                    <p class="text-muted mb-0">Conversion Rate</p>
                    <small class="text-muted">(Orders / Searches)</small>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <div class="h4 mb-0">{{ number_format($totalSearches) }}</div>
                        <small class="text-muted">Total Searches</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 mb-0">{{ number_format($currentOrders) }}</div>
                        <small class="text-muted">Total Orders</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Period-Based Comparison Chart -->
@if(auth()->user()->hasPermission('analytics.view-revenue'))
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">{{ $period === 'this_year' || $period === 'last_year' ? 'Monthly Revenue (Last 12 Months)' : 'Revenue Comparison' }}</h5>
            </div>
            <div class="card-body">
                @if(count($chartLabels) > 0 && array_sum($chartData) > 0)
                <canvas id="periodChart" style="height: 120px;"></canvas>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-bar-chart" style="font-size: 2rem;"></i>
                    <p class="mt-2 mb-0">No revenue data for selected period</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
@else
<div class="alert alert-warning border-0 shadow-sm mb-4">
    <i class="bi bi-shield-exclamation me-2"></i>
    <strong>Access Restricted:</strong> You don't have permission to view analytics.
    Please contact an administrator if you need access.
</div>
@endif
@endsection

@push('styles')
@if(auth()->user()->hasPermission('analytics.view-revenue'))
<style>
    /* Force Bootstrap Icons to display - SAME AS REFERENCE PAGE */
    .stat-card-icon i,
    .stat-card-icon i::before,
    .bi::before,
    [class*="bi bi-"]::before {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-family: 'bootstrap-icons' !important;
    }
    
    /* Override icon colors for stat cards */
    .stat-card-primary .stat-card-icon i::before { color: #0d6efd !important; }
    .stat-card-success .stat-card-icon i::before { color: #198754 !important; }
    .stat-card-info .stat-card-icon i::before { color: #0dcaf0 !important; }
    .stat-card-warning .stat-card-icon i::before { color: #ffc107 !important; }
    .stat-card-danger .stat-card-icon i::before { color: #dc3545 !important; }
    .stat-card-secondary .stat-card-icon i::before { color: #6c757d !important; }
    
    /* Make the whole icon colored */
    .stat-card-icon i { color: inherit !important; }
</style>
@endif
@endpush

@push('scripts')
@if(auth()->user()->hasPermission('analytics.view-revenue'))
<script>
    // Period select handler
    document.getElementById('periodSelect').addEventListener('change', function() {
        const customFields = document.querySelectorAll('.custom-date-range');
        if (this.value === 'custom') {
            customFields.forEach(field => field.style.display = 'block');
        } else {
            customFields.forEach(field => field.style.display = 'none');
        }
    });

    // Revenue Trend Chart
    const revenueCtx = document.getElementById('revenueTrendChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($salesByDay['labels'] ?? $last30DaysLabels) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode($salesByDay['data'] ?? $last30DaysRevenue) !!},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '৳' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endif

@if(auth()->user()->hasPermission('analytics.view-sales'))
<script>
    // Order Status Chart
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    const statusData = {!! json_encode($orderStatus->values()) !!};
    const statusColors = {!! json_encode($orderStatusColors) !!};
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1)),
            datasets: [{
                data: statusData.map(s => s.count),
                backgroundColor: statusData.map(s => statusColors[s.status] || '#6c757d'),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false }
            }
        },
        plugins: [{
            id: 'centerText',
            beforeDraw: function(chart) {
                const ctx = chart.ctx;
                const fontStyle = 'bold';
                const fontSize = 24;
                ctx.font = fontSize + 'px ' + fontStyle;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                const centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                const centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;

                ctx.fillStyle = '#333';
                ctx.fillText('{{ $currentOrders }}', centerX, centerY - 8);

                ctx.font = '12px sans-serif';
                ctx.fillStyle = '#888';
                ctx.fillText('Orders', centerX, centerY + 14);
            }
        }]
    });
</script>
@endif

@if(auth()->user()->hasPermission('analytics.view-revenue'))
<script>
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryData = {!! json_encode($topCategories) !!};
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categoryData.map(c => c.name),
            datasets: [{
                label: 'Revenue',
                data: categoryData.map(c => c.total_revenue),
                backgroundColor: [
                    '#6366f1',
                    '#8b5cf6',
                    '#ec4899',
                    '#f43f5e',
                    '#f97316',
                    '#eab308',
                    '#22c55e',
                    '#14b8a6'
                ],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '৳' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Period-Based Comparison Chart
    const periodCtx = document.getElementById('periodChart').getContext('2d');
    new Chart(periodCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode($chartData) !!},
                backgroundColor: '#6366f1',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '৳' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endif
@endpush
