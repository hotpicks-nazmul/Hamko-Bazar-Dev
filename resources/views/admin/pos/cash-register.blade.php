@extends('admin.layouts.app')

@section('title', 'Cash Register')

@section('content')
<div class="cash-register-page">
    <!-- Date Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Select Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> View
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('admin.pos.terminal') }}" class="btn btn-success">
                        <i class="bi bi-plus-lg me-1"></i> New Sale
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="stat-card-row mb-4" id="statsCards">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Total Sales</span>
                <span class="stat-card-value">৳{{ number_format($totalSales, 2) }}</span>
            </div>
        </div>
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Transactions</span>
                <span class="stat-card-value">{{ $transactionCount }}</span>
            </div>
        </div>
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Cash</span>
                <span class="stat-card-value">৳{{ number_format($totalCash, 2) }}</span>
            </div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon"><i class="bi bi-credit-card"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Card/Digital</span>
                <span class="stat-card-value">৳{{ number_format($totalCard + $totalDigital, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Transactions Table (Primary - Database Orders) -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Transactions - {{ $date }}</h5>
        </div>
        <div class="card-body p-0">
            @if(count($dbOrders) > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Subtotal</th>
                            <th>Discount</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th style="width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dbOrders as $order)
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $order->order_number }}</span>
                            </td>
                            <td>{{ $order->created_at->format('H:i:s') }}</td>
                            <td>{{ $order->billing_first_name ?: 'Walk-in Customer' }}</td>
                            <td>৳{{ number_format($order->subtotal, 2) }}</td>
                            <td>৳{{ number_format($order->discount ?? 0, 2) }}</td>
                            <td class="fw-bold">৳{{ number_format($order->total, 2) }}</td>
                            <td>
                                @if($order->payment_method === 'cash')
                                    <span class="badge bg-info">Cash</span>
                                @elseif($order->payment_method === 'card')
                                    <span class="badge bg-primary">Card</span>
                                @elseif($order->payment_method === 'digital_wallet')
                                    <span class="badge bg-warning">Digital</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($order->payment_method) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-success">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.orders.in-house.show', $order->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
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
                <p class="text-muted mt-2">No transactions for this date</p>
                <a href="{{ route('admin.pos.terminal') }}" class="btn btn-sm btn-primary mt-2">
                    <i class="bi bi-plus-lg me-1"></i> Make a Sale
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Force Bootstrap Icons to display (same as inventory page) */
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
@endpush