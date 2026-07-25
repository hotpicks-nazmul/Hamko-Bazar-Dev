@extends('admin.layouts.app')

@section('title', $warehouse->name . ' - Orders')

@push('styles')
<style>
    .content-area { padding-bottom: 100px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="bi bi-cart3 me-2"></i>Orders</h4>
        <p class="text-muted mb-0">{{ $warehouse->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Warehouses
        </a>
        <a href="{{ route('admin.warehouses.picking', $warehouse->id) }}" class="btn btn-success">
            <i class="bi bi-box-seam me-1"></i> Picking Dashboard
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="picking" {{ request('status') === 'picking' ? 'selected' : '' }}>Picking</option>
                    <option value="packed" {{ request('status') === 'packed' ? 'selected' : '' }}>Packed</option>
                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Payment</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">All Payments</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Order #, Name, Phone" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-3 text-end">
                <a href="{{ route('admin.warehouses.orders', $warehouse->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle me-1"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $order->order_number }}</div>
                            @if($order->is_pickup_order)
                            <span class="badge bg-info">Pickup</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $order->billing_full_name }}</div>
                            <small class="text-muted">{{ $order->billing_phone }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $order->items->sum('qty') }} items</span>
                        </td>
                        <td>
                            <div class="fw-semibold">৳{{ number_format($order->total, 2) }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $order->status_badge_class }}">{{ $order->status_display_name }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $order->payment_status_badge_class }}">{{ ucfirst($order->payment_status) }}</span>
                        </td>
                        <td>
                            <small>{{ $order->created_at->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.in-house.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No orders found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($orders->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $orders->appends(request()->query())->links() }}
</div>
@endif

@endsection