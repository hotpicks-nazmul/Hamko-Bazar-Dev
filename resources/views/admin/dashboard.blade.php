@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
@if(auth()->user()->hasPermission('dashboard') && auth()->user()->hasPermission('dashboard.view'))
@if(auth()->user()->hasPermission('dashboard.view-revenue'))
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <!-- Total Sales -->
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon">
            <i class="bi bi-wallet2"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Sales</span>
            <span class="stat-card-value">৳{{ number_format($totalSales, 2) }}</span>
        </div>
    </div>
    
    <!-- Today's Sales -->
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Today's Sales</span>
            <span class="stat-card-value">৳{{ number_format($todaySales, 2) }}</span>
        </div>
    </div>
    
    <!-- Total Orders -->
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon">
            <i class="bi bi-cart-check"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Orders</span>
            <span class="stat-card-value">{{ $totalOrders }}</span>
        </div>
    </div>
    
    <!-- Total Customers -->
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Customers</span>
            <span class="stat-card-value">{{ $totalCustomers }}</span>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->hasPermission('dashboard.view-revenue'))
<!-- Main Charts Row -->
<div class="row mb-4">
    <!-- Sales Overview Chart -->
    <div class="col-xl-8 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-semibold">Sales Overview</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="btnDaily">Daily</button>
                        <button type="button" class="btn btn-outline-primary" id="btnWeekly">Weekly</button>
                        <button type="button" class="btn btn-outline-primary" id="btnMonthly">Monthly</button>
                        <button type="button" class="btn btn-outline-primary" id="btnYearly">Yearly</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <canvas id="salesOverviewChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Order Status Doughnut Chart -->
    <div class="col-xl-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Order Status</h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div class="position-relative" style="width: 220px; height: 220px;">
                    <canvas id="orderStatusChart"></canvas>
                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                        <h4 class="mb-0 fw-bold">{{ $totalOrders }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 pt-0">
                <div class="d-flex flex-column gap-3">
                    <!-- Pending -->
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="status-dot bg-warning rounded-circle me-2"></div>
                            <span class="text-muted small">Pending</span>
                        </div>
                        <span class="fw-semibold">{{ $pendingOrders }}</span>
                    </div>
                    <!-- Processing -->
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="status-dot bg-info rounded-circle me-2"></div>
                            <span class="text-muted small">Processing</span>
                        </div>
                        <span class="fw-semibold">{{ $processingOrders }}</span>
                    </div>
                    <!-- Completed -->
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="status-dot bg-success rounded-circle me-2"></div>
                            <span class="text-muted small">Completed</span>
                        </div>
                        <span class="fw-semibold">{{ $completedOrders }}</span>
                    </div>
                    <!-- Cancelled -->
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="status-dot bg-danger rounded-circle me-2"></div>
                            <span class="text-muted small">Cancelled</span>
                        </div>
                        <span class="fw-semibold">{{ $cancelledOrders ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Charts Row -->
<div class="row mb-4 align-items-stretch">
    <!-- Revenue by Category -->
    <div class="col-xl-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Revenue by Category</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryRevenueChart" height="280"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Monthly Sales Comparison -->
    <div class="col-xl-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Monthly Sales & Orders</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlySalesChart" height="280"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Data Tables Row -->
<div class="row mb-4">
    <!-- Recent Orders -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-semibold">Recent Orders</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                @if(auth()->user()->hasPermission('dashboard.view-sales'))
                                <th>Total</th>
                                @endif
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            <tr>
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>{{ $order->billing_full_name }}</td>
                                @if(auth()->user()->hasPermission('dashboard.view-sales'))
                                <td>৳{{ number_format($order->total, 2) }}</td>
                                @endif
                                <td>
                                    <span class="badge {{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span>
                                </td>
                                <td>{{ $order->created_at->format('d M, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No orders found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Selling Products Row -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="min-height: 400px;">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Top Selling Products</h5>
            </div>
            <div class="card-body">
                @forelse($topProducts as $index => $product)
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <span class="badge bg-primary rounded-circle p-2">{{ $index + 1 }}</span>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 small">{{ $product->product_name }}</h6>
                        <small class="text-muted">{{ $product->total_sold }} sold</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-light text-dark">{{ $product->total_sold }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">No data available</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Customer Stats & Product Status Row -->
<div class="row mb-4">
    <!-- Customer Stats -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-semibold">Customer Stats</h5>
                <span class="badge bg-primary rounded-pill">{{ number_format($totalCustomers) }}</span>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center px-3 py-2 rounded-pill {{ $customerGrowth >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10' }}">
                        <i class="bi {{ $customerGrowth >= 0 ? 'bi-graph-up-arrow text-success' : 'bi-graph-down-arrow text-danger' }} me-2"></i>
                        <span class="fw-semibold {{ $customerGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format(abs($customerGrowth), 1) }}% {{ $customerGrowth >= 0 ? 'growth' : 'decline' }}
                        </span>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">This Month</span>
                    <span class="fw-semibold">{{ number_format($currentMonthCustomers) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Last Month</span>
                    <span class="fw-semibold text-muted">{{ number_format($lastMonthCustomers) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Status -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-semibold">Product Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded p-2 me-2">
                            <i class="bi bi-check-circle text-success"></i>
                        </div>
                        <span>Active Products</span>
                    </div>
                    <span class="fw-semibold">{{ $activeProducts }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 rounded p-2 me-2">
                            <i class="bi bi-x-circle text-danger"></i>
                        </div>
                        <span>Out of Stock</span>
                    </div>
                    <span class="fw-semibold text-danger">{{ $outOfStockProducts }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded p-2 me-2">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                        </div>
                        <span>Low Stock</span>
                    </div>
                    <span class="fw-semibold text-warning">{{ $lowStockProducts }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning border-0 shadow-sm mb-4">
    <i class="bi bi-shield-exclamation me-2"></i>
    <strong>Access Restricted:</strong> You don't have permission to view the dashboard.
    Please contact an administrator if you need access.
</div>
@endif
@endsection

@push('styles')
<style>
    /* Status dot indicators for Order Status legend */
    .status-dot {
        width: 8px;
        height: 8px;
        display: inline-block;
    }
    .status-dot.bg-warning { background-color: #f59e0b; }
    .status-dot.bg-info { background-color: #0ea5e9; }
    .status-dot.bg-success { background-color: #10b981; }
    .status-dot.bg-danger { background-color: #ef4444; }
</style>
@endpush

@push('scripts')
@if(auth()->user()->hasPermission('dashboard.view-revenue'))
<script>
    // Chart.js default configuration
    Chart.defaults.font.family = "'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif";
    Chart.defaults.plugins.legend.display = true;
    Chart.defaults.plugins.legend.position = 'bottom';
    
    // Color palette
    const colors = {
        primary: {
            main: '#667eea',
            light: 'rgba(102, 126, 234, 0.1)',
            gradient: ['rgba(102, 126, 234, 0.4)', 'rgba(102, 126, 234, 0.0)']
        },
        success: {
            main: '#10b981',
            light: 'rgba(16, 185, 129, 0.1)',
            gradient: ['rgba(16, 185, 129, 0.4)', 'rgba(16, 185, 129, 0.0)']
        },
        warning: {
            main: '#f59e0b',
            light: 'rgba(245, 158, 11, 0.1)'
        },
        info: {
            main: '#0ea5e9',
            light: 'rgba(14, 165, 233, 0.1)'
        },
        danger: {
            main: '#ef4444',
            light: 'rgba(239, 68, 68, 0.1)'
        }
    };
    
    // Monthly labels
    const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    // Sales Overview Chart (Area Chart)
    const salesCtx = document.getElementById('salesOverviewChart').getContext('2d');
    const salesGradient = salesCtx.createLinearGradient(0, 0, 0, 300);
    salesGradient.addColorStop(0, colors.primary.gradient[0]);
    salesGradient.addColorStop(1, colors.primary.gradient[1]);
    
    const weeklyData = @json($last7DaysSales);
    const weeklyLabels = @json($last7DaysLabels);
    const monthlyData = @json($last30DaysSales);
    const monthlyLabels = @json($last30DaysLabels);
    const yearlyData = @json($monthlySalesData);
    const dailyData = @json($todayHourlySales);
    const dailyLabels = @json($todayHourlyLabels);
    
    let salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Sales (৳)',
                data: dailyData,
                borderColor: colors.primary.main,
                backgroundColor: salesGradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.primary.main,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return '৳' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '৳' + value.toLocaleString();
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    
    // Chart period switcher
    document.getElementById('btnDaily').addEventListener('click', function() {
        updateChartPeriod('daily', this);
    });
    document.getElementById('btnWeekly').addEventListener('click', function() {
        updateChartPeriod('weekly', this);
    });
    document.getElementById('btnMonthly').addEventListener('click', function() {
        updateChartPeriod('monthly', this);
    });
    document.getElementById('btnYearly').addEventListener('click', function() {
        updateChartPeriod('yearly', this);
    });
    
    function updateChartPeriod(period, btn) {
        document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        if (period === 'daily') {
            salesChart.data.labels = dailyLabels;
            salesChart.data.datasets[0].data = dailyData;
        } else if (period === 'weekly') {
            salesChart.data.labels = weeklyLabels;
            salesChart.data.datasets[0].data = weeklyData;
        } else if (period === 'monthly') {
            salesChart.data.labels = monthlyLabels;
            salesChart.data.datasets[0].data = monthlyData;
        } else {
            salesChart.data.labels = monthLabels;
            salesChart.data.datasets[0].data = yearlyData;
        }
        salesChart.update();
    }
    
    // Order Status Doughnut Chart
    const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(orderStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Processing', 'Completed', 'Cancelled'],
            datasets: [{
                data: [{{ $pendingOrders }}, {{ $processingOrders }}, {{ $completedOrders }}, {{ $cancelledOrders ?? 0 }}],
                backgroundColor: [
                    colors.warning.main,
                    colors.info.main,
                    colors.success.main,
                    colors.danger.main
                ],
                borderWidth: 0,
                hoverOffset: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    
    // Category Revenue Chart (Horizontal Bar)
    const categoryCtx = document.getElementById('categoryRevenueChart').getContext('2d');
    const categoryData = @json($salesByCategory);
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categoryData.map(item => item.name),
            datasets: [{
                label: 'Revenue (৳)',
                data: categoryData.map(item => item.total_sales),
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(14, 165, 233, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)'
                ],
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return '৳' + context.parsed.x.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '৳' + value.toLocaleString();
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Monthly Sales & Orders Chart (Combo Chart)
    const monthlyCtx = document.getElementById('monthlySalesChart').getContext('2d');
    const monthlyGradient = monthlyCtx.createLinearGradient(0, 0, 0, 280);
    monthlyGradient.addColorStop(0, colors.success.gradient[0]);
    monthlyGradient.addColorStop(1, colors.success.gradient[1]);
    
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                type: 'line',
                label: 'Sales (৳)',
                data: @json($monthlySalesData),
                borderColor: colors.success.main,
                backgroundColor: 'transparent',
                borderWidth: 3,
                tension: 0.4,
                pointBackgroundColor: colors.success.main,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                yAxisID: 'y'
            }, {
                type: 'bar',
                label: 'Orders',
                data: @json($monthlyOrderCounts),
                backgroundColor: 'rgba(102, 126, 234, 0.6)',
                borderRadius: 6,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Sales (৳)') {
                                return 'Sales: ৳' + context.parsed.y.toLocaleString();
                            }
                            return 'Orders: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '৳' + value.toLocaleString();
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
</script>
@endif
@endpush
