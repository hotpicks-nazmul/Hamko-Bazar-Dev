@extends('admin.layouts.app')

@section('title', 'Orders — ' . $customer->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Orders</h4>
        <p class="text-muted small mb-0">
            {{ $customer->name }}@if($customer->phone) · {{ $customer->phone }}@endif
            @if($customer->email) · {{ $customer->email }}@endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Customer
        </a>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-people me-1"></i> All Customers
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Orders</p>
                <h4 class="mb-0">{{ number_format($orders->total()) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Lifetime Value</p>
                <h4 class="mb-0">৳{{ number_format((float) $customer->orders()->sum('total'), 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Last Order</p>
                <h4 class="mb-0">
                    {{ optional($customer->orders()->latest()->first())->created_at?->format('d M Y') ?? '—' }}
                </h4>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-bag-check me-2"></i>Order History</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-end">Total</th>
                        <th style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-semibold text-decoration-none">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="text-muted small">{{ $order->created_at?->format('d M Y, h:i A') }}</td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $order->items_count ?? $order->items()->count() }}</span>
                        </td>
                        <td>
                            @php
                                $payClass = match ($order->payment_status) {
                                    'paid' => 'success',
                                    'refunded' => 'info',
                                    'failed', 'cancelled' => 'danger',
                                    default => 'warning',
                                };
                            @endphp
                            <span class="badge bg-{{ $payClass }} bg-opacity-10 text-{{ $payClass }}">
                                {{ ucfirst($order->payment_status ?? 'pending') }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusClass = match ($order->status) {
                                    'delivered' => 'success',
                                    'cancelled', 'refunded' => 'danger',
                                    'shipped', 'out_for_delivery' => 'info',
                                    'processing', 'confirmed' => 'primary',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                        </td>
                        <td class="text-end fw-semibold">৳{{ number_format((float) $order->total, 2) }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-bag-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-0 mt-2">This customer has no orders yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $orders->firstItem() }} - {{ $orders->lastItem() }} of {{ $orders->total() }} orders
            </div>
            <div>{{ $orders->links() }}</div>
        </div>
        @endif
    </div>
</div>
@endsection
