@extends('admin.layouts.app')

@section('title', 'Sales Report')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Sales Report</h4>
</div>

<div class="row mb-4">
    @if(auth()->user()->hasPermission('view-revenue'))
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted">Total Sales</h6>
                <h3>৳{{ number_format($totalSales ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    @endif
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted">Total Orders</h6>
                <h3>{{ $totalOrders ?? 0 }}</h3>
            </div>
        </div>
    </div>
    @if(auth()->user()->hasPermission('view-revenue'))
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted">Average Order Value</h6>
                <h3>৳{{ number_format($avgOrderValue ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    @endif
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted">Total Products Sold</h6>
                <h3>{{ $totalProductsSold ?? 0 }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Filter Report</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Sales Data</h5>
        <a href="{{ route('admin.reports.sales', request()->all() + ['export' => 'csv']) }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="salesTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Items</th>
                        @if(auth()->user()->hasPermission('view-sales'))
                        <th>Total</th>
                        @endif
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders ?? [] as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->created_at->format('d M, Y') }}</td>
                        <td>{{ $order->billing_first_name }} {{ $order->billing_last_name }}</td>
                        <td>{{ $order->items_count ?? $order->items->count() ?? 0 }}</td>
                        @if(auth()->user()->hasPermission('view-sales'))
                        <td>৳{{ number_format($order->total, 2) }}</td>
                        @endif
                        <td>
                            <span class="badge {{ $order->status === 'completed' ? 'bg-success' : ($order->status === 'cancelled' ? 'bg-danger' : 'bg-warning') }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No sales data found for the selected period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($orders) && method_exists($orders, 'hasPages') && $orders->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#salesTable').DataTable({
        pageLength: 25,
        order: [[1, 'desc']]
    });
});
</script>
@endpush
