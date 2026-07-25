@extends('admin.layouts.app')

@section('title', 'POS Reports')

@section('content')
<div class="pos-reports-page">
    <!-- Date Range Filter -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" id="filterForm" class="row g-2 align-items-end">
                <!-- From Date -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">From Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                </div>
                <!-- To Date -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">To Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                </div>
                <!-- Filter Button -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                </div>
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <a href="{{ route('admin.pos.reports') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
                <!-- New Sale Button -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('admin.pos.terminal') }}" class="btn btn-sm btn-success w-100">
                        <i class="bi bi-plus-lg me-1"></i> New Sale
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="stat-card-row stat-card-row-4 mb-4" id="statsCards">
        @if(auth()->user()->hasPermission('view-revenue'))
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Total Sales</span>
                <span class="stat-card-value">৳{{ number_format($totalSales, 2) }}</span>
            </div>
        </div>
        @endif
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="bi bi-cart3"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Total Orders</span>
                <span class="stat-card-value">{{ $totalOrders }}</span>
            </div>
        </div>
        @if(auth()->user()->hasPermission('view-revenue'))
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon"><i class="bi bi-calculator"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Avg. Order Value</span>
                <span class="stat-card-value">৳{{ number_format($averageOrderValue, 2) }}</span>
            </div>
        </div>
        @endif
        @if(auth()->user()->hasPermission('view-revenue'))
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon"><i class="bi bi-calendar-check"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Daily Avg.</span>
                <span class="stat-card-value">{{ $totalOrders > 0 ? round(($totalSales / max(1, count($dailySales))), 2) : 0 }}</span>
            </div>
        </div>
        @endif
    </div>

    @if(auth()->user()->hasPermission('view-revenue'))
    <!-- Payment Method Breakdown -->
    <div class="stat-card-row stat-card-row-3 mb-4">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Cash Payments</span>
                <span class="stat-card-value">৳{{ number_format($cashSales, 2) }}</span>
            </div>
            <div class="stat-card-progress">
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: {{ $totalSales > 0 ? ($cashSales / $totalSales * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-credit-card"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Card Payments</span>
                <span class="stat-card-value">৳{{ number_format($cardSales, 2) }}</span>
            </div>
            <div class="stat-card-progress">
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-primary" style="width: {{ $totalSales > 0 ? ($cardSales / $totalSales * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon"><i class="bi bi-wallet2"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Digital Wallet</span>
                <span class="stat-card-value">৳{{ number_format($digitalSales, 2) }}</span>
            </div>
            <div class="stat-card-progress">
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: {{ $totalSales > 0 ? ($digitalSales / $totalSales * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Daily Sales Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Daily Sales</h6>
                </div>
                <div class="card-body">
                    @if(count($dailySales) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                        <th>Orders</th>
                                        @if(auth()->user()->hasPermission('view-revenue'))
                                        <th>Total</th>
                                        @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailySales as $date => $data)
                                <tr>
                                    <td>{{ $date }}</td>
                                    <td>{{ $data['count'] }}</td>
                                    @if(auth()->user()->hasPermission('view-revenue'))
                                    <td class="fw-medium">৳{{ number_format($data['total'], 2) }}</td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-4">
                        No data available
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Selling Products -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-star me-2"></i>Top Selling Products</h6>
                </div>
                <div class="card-body">
                    @if(count($orderItems) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty Sold</th>
                                    @if(auth()->user()->hasPermission('view-revenue'))
                                    <th>Revenue</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderItems as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->total_qty }}</td>
                                    @if(auth()->user()->hasPermission('view-revenue'))
                                    <td class="fw-medium">৳{{ number_format($item->total_sales, 2) }}</td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-4">
                        No data available
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Order Details</h5>
        </div>
        <div class="card-body p-0">
            @if(count($orders) > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Items</th>
                            @if(auth()->user()->hasPermission('view-sales'))
                            <th>Subtotal</th>
                            <th>Discount</th>
                            <th>Total</th>
                            @endif
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $order->order_number }}</span>
                            </td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $order->items->count() }}</td>
                            @if(auth()->user()->hasPermission('view-sales'))
                            <td>৳{{ number_format($order->subtotal, 2) }}</td>
                            <td>৳{{ number_format($order->discount ?? 0, 2) }}</td>
                            <td class="fw-bold">৳{{ number_format($order->total, 2) }}</td>
                            @endif
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($order->payment_method) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.orders.in-house.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No orders found for this period</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* POS Reports page - uses global stat-card styles from layout */
</style>
@endpush