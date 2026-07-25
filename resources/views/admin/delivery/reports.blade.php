@extends('admin.layouts.app')

@section('title', 'Delivery Reports')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-bar-chart me-2"></i>Delivery Reports</h4>
        <p class="text-muted mb-0">Comprehensive delivery analytics and performance insights</p>
    </div>
    
    <!-- Export Button -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.delivery.reports.export', ['date_range' => $dateRange]) }}" class="btn btn-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
        </a>
    </div>
</div>

<!-- Date Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-calendar3 text-muted"></i>
                <span class="text-muted">Date Range:</span>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="date_range" id="today" value="today" {{ $dateRange == 'today' ? 'checked' : '' }}>
                    <label class="btn btn-sm btn-outline-secondary" for="today">Today</label>
                    
                    <input type="radio" class="btn-check" name="date_range" id="yesterday" value="yesterday" {{ $dateRange == 'yesterday' ? 'checked' : '' }}>
                    <label class="btn btn-sm btn-outline-secondary" for="yesterday">Yesterday</label>
                    
                    <input type="radio" class="btn-check" name="date_range" id="this_week" value="this_week" {{ $dateRange == 'this_week' ? 'checked' : '' }}>
                    <label class="btn btn-sm btn-outline-secondary" for="this_week">This Week</label>
                    
                    <input type="radio" class="btn-check" name="date_range" id="this_month" value="this_month" {{ $dateRange == 'this_month' ? 'checked' : '' }}>
                    <label class="btn btn-sm btn-outline-secondary" for="this_month">This Month</label>
                    
                    <input type="radio" class="btn-check" name="date_range" id="last_30_days" value="last_30_days" {{ $dateRange == 'last_30_days' ? 'checked' : '' }}>
                    <label class="btn btn-sm btn-outline-secondary" for="last_30_days">Last 30 Days</label>
                    
                    <input type="radio" class="btn-check" name="date_range" id="this_year" value="this_year" {{ $dateRange == 'this_year' ? 'checked' : '' }}>
                    <label class="btn btn-sm btn-outline-secondary" for="this_year">This Year</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-filter me-1"></i> Apply Filter
            </button>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-box-seam"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Total Orders</span>
                <span class="stat-card-value">{{ number_format($stats['total_orders']) }}</span>
            </div>
        </div>
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Delivered</span>
                <span class="stat-card-value">{{ number_format($stats['delivered']) }}</span>
            </div>
        </div>
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon"><i class="bi bi-truck"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">In Transit</span>
                <span class="stat-card-value">{{ number_format($stats['in_transit']) }}</span>
            </div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Pending</span>
                <span class="stat-card-value">{{ number_format($stats['pending']) }}</span>
            </div>
        </div>
        <div class="stat-card stat-card-danger">
            <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Cancelled</span>
                <span class="stat-card-value">{{ number_format($stats['cancelled']) }}</span>
            </div>
        </div>
        @if(auth()->user()->hasPermission('view-revenue'))
        <div class="stat-card stat-card-secondary">
            <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Shipping Revenue</span>
                <span class="stat-card-value">৳{{ number_format($stats['total_shipping_revenue'], 2) }}</span>
            </div>
        </div>
        @endif
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Delivery Trends Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Delivery Trends</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="deliveryTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Status Breakdown -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Status Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone Performance & Delivery Boy Performance -->
    <div class="row mb-4">
        <!-- Zone Performance -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Zone Performance</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Zone</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-center">Success</th>
                                    @if(auth()->user()->hasPermission('view-revenue'))
                                    <th class="text-center">Revenue</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($zonePerformance as $zone)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $zone['name'] }}</div>
                                    </td>
                                    <td class="text-center">{{ number_format($zone['total_orders']) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $zone['success_rate'] >= 80 ? 'bg-success' : ($zone['success_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $zone['success_rate'] }}%
                                        </span>
                                    </td>
                                    @if(auth()->user()->hasPermission('view-revenue'))
                                    <td class="text-center">৳{{ number_format($zone['revenue'], 2) }}</td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-geo-alt d-block mb-2" style="font-size: 2rem;"></i>
                                        No zone data available
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delivery Boy Performance -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Top Delivery Boys</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th class="text-center">Zone</th>
                                    <th class="text-center">Deliveries</th>
                                    <th class="text-center">Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deliveryBoyPerformance as $boy)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $boy['name'] }}</div>
                                                <small class="text-muted">{{ $boy['phone'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $boy['zone_name'] ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">{{ number_format($boy['total_deliveries']) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $boy['success_rate'] >= 80 ? 'bg-success' : ($boy['success_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $boy['success_rate'] }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-person-badge d-block mb-2" style="font-size: 2rem;"></i>
                                        No delivery boy data available
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Time Analysis & Failed Deliveries -->
    <div class="row mb-4">
        <!-- Delivery Time Analysis -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Delivery Time Analysis</h6>
                </div>
                <div class="card-body">
                    @if($deliveryTimeAnalysis['total_delivered'] == 0)
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-clock-history d-block mb-2" style="font-size: 2rem;"></i>
                        No delivered orders in the selected period
                    </div>
                    @else
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 text-success mb-1">{{ $deliveryTimeAnalysis['avg_hours'] > 0 ? $deliveryTimeAnalysis['avg_hours'] . 'h' : 'N/A' }}</div>
                            <small class="text-muted">Average Delivery Time</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1">{{ number_format($deliveryTimeAnalysis['total_delivered']) }}</div>
                            <small class="text-muted">Total Delivered</small>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>< 24 hours</span>
                            <span class="text-success">{{ $deliveryTimeAnalysis['within_24h_percent'] }}%</span>
                        </div>
                        <div class="progress progress-thin mb-3">
                            <div class="progress-bar bg-success" style="width: {{ $deliveryTimeAnalysis['within_24h_percent'] }}%"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>24-48 hours</span>
                            <span class="text-info">{{ $deliveryTimeAnalysis['within_48h_percent'] }}%</span>
                        </div>
                        <div class="progress progress-thin mb-3">
                            <div class="progress-bar bg-info" style="width: {{ $deliveryTimeAnalysis['within_48h_percent'] }}%"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>48-72 hours</span>
                            <span class="text-warning">{{ $deliveryTimeAnalysis['within_72h_percent'] }}%</span>
                        </div>
                        <div class="progress progress-thin mb-3">
                            <div class="progress-bar bg-warning" style="width: {{ $deliveryTimeAnalysis['within_72h_percent'] }}%"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>> 72 hours</span>
                            <span class="text-danger">{{ $deliveryTimeAnalysis['over_72h_percent'] }}%</span>
                        </div>
                        <div class="progress progress-thin">
                            <div class="progress-bar bg-danger" style="width: {{ $deliveryTimeAnalysis['over_72h_percent'] }}%"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Failed Deliveries -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Failed/Cancelled Deliveries</h6>
                    <span class="badge bg-danger">{{ $failedDeliveries->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Order #</th>
                                    <th>Status</th>
                                    @if(auth()->user()->hasPermission('view-sales'))
                                    <th>Amount</th>
                                    @endif
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($failedDeliveries as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="text-decoration-none">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ ucfirst($order->status) }}</span>
                                    </td>
                                    @if(auth()->user()->hasPermission('view-sales'))
                                    <td>৳{{ number_format($order->total, 2) }}</td>
                                    @endif
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-check-circle d-block mb-2 text-success" style="font-size: 2rem;"></i>
                                        No failed deliveries
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Carrier Revenue -->
    @if($carrierRevenue->isNotEmpty())
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-truck me-2"></i>Carrier Performance</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Carrier</th>
                                    <th class="text-center">Total Orders</th>
                                    @if(auth()->user()->hasPermission('view-revenue'))
                                    <th class="text-center">Shipping Revenue</th>
                                    <th class="text-center">Total Revenue</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($carrierRevenue as $carrier)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($carrier['logo'])
                                            <img src="{{ $carrier['logo'] }}" alt="{{ $carrier['name'] }}" class="me-2" style="width: 30px; height: 30px; object-fit: contain;">
                                            @else
                                            <div class="bg-secondary bg-opacity-10 rounded p-1 me-2">
                                                <i class="bi bi-truck text-secondary"></i>
                                            </div>
                                            @endif
                                            <span class="fw-medium">{{ $carrier['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ number_format($carrier['total_orders']) }}</td>
                                    @if(auth()->user()->hasPermission('view-revenue'))
                                    <td class="text-center">৳{{ number_format($carrier['shipping_revenue'], 2) }}</td>
                                    <td class="text-center">৳{{ number_format($carrier['total_revenue'], 2) }}</td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

@push('styles')
<style>
    .stat-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .table-card {
        border: none;
        border-radius: 12px;
    }
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .progress-thin {
        height: 8px;
        border-radius: 4px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Delivery Trends Chart
    const trendsData = @json($trends);
    const trendLabels = trendsData.length > 0 ? trendsData.map(t => t.date) : ['No Data'];
    const trendTotal = trendsData.length > 0 ? trendsData.map(t => t.total) : [0];
    const trendDelivered = trendsData.length > 0 ? trendsData.map(t => t.delivered) : [0];
    const trendFailed = trendsData.length > 0 ? trendsData.map(t => t.failed) : [0];

    const trendsCtx = document.getElementById('deliveryTrendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Total Orders',
                    data: trendTotal,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Delivered',
                    data: trendDelivered,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Failed',
                    data: trendFailed,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Status Breakdown Chart
    const statusData = @json($statusBreakdown);
    const statusLabels = statusData.length > 0 ? statusData.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1)) : ['No Data'];
    const statusCounts = statusData.length > 0 ? statusData.map(s => s.count) : [1];

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: statusData.length > 0 ? [
                    '#22c55e', // delivered - green
                    '#3b82f6', // shipped - blue
                    '#f59e0b', // pending - yellow
                    '#ef4444', // cancelled - red
                    '#8b5cf6', // refunded - purple
                    '#6b7280'  // other - gray
                ] : ['#e9ecef']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
@endsection
