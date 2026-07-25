@extends('admin.layouts.app')

@section('title', $warehouse->name . ' - Picking Dashboard')

@push('styles')
<style>
    .picking-card {
        border-left: 4px solid #6c757d;
        transition: all 0.2s;
    }
    .picking-card.ready {
        border-left-color: #0dcaf0;
    }
    .picking-card.picking {
        border-left-color: #0d6efd;
    }
    .picking-card.packed {
        border-left-color: #198754;
    }
    .picking-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .item-check {
        cursor: pointer;
    }
    .item-check.checked {
        text-decoration: line-through;
        color: #6c757d;
    }
    .picking-progress {
        height: 8px;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i>Picking Dashboard</h4>
        <p class="text-muted mb-0">{{ $warehouse->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Warehouses
        </a>
        <a href="{{ route('admin.warehouses.orders', $warehouse->id) }}" class="btn btn-outline-primary">
            <i class="bi bi-list-ul me-1"></i> All Orders
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Ready to Pick</span>
                <span class="stat-card-value">{{ $orders->total() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">In Picking</span>
                <span class="stat-card-value">{{ $orders->where('status', 'picking')->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="bi bi-check2-square"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Packed Today</span>
                <span class="stat-card-value">{{ $orders->where('status', 'packed')->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon"><i class="bi bi-truck"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Ready to Ship</span>
                <span class="stat-card-value">{{ $orders->where('status', 'packed')->count() }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted">Filter</label>
                <select name="filter" class="form-select form-select-sm">
                    <option value="">All Picking Orders</option>
                    <option value="ready" {{ request('filter') === 'ready' ? 'selected' : '' }}>Ready to Pick</option>
                    <option value="picking" {{ request('filter') === 'picking' ? 'selected' : '' }}>In Picking</option>
                    <option value="packed" {{ request('filter') === 'packed' ? 'selected' : '' }}>Packed</option>
                </select>
            </div>
            <div class="col-md-4">
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
                <a href="{{ route('admin.warehouses.picking', $warehouse->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle me-1"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Orders List -->
<div class="row">
    @forelse($orders as $order)
    <div class="col-lg-6 mb-3">
        <div class="card border-0 shadow-sm picking-card {{ $order->status }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1">
                            <a href="#" class="text-decoration-none">{{ $order->order_number }}</a>
                            <span class="badge {{ $order->status_badge_class }} ms-2">{{ $order->status_display_name }}</span>
                        </h6>
                        <small class="text-muted">
                            <i class="bi bi-person me-1"></i>{{ $order->billing_full_name }}
                            <span class="mx-2">|</span>
                            <i class="bi bi-telephone me-1"></i>{{ $order->billing_phone }}
                        </small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">৳{{ number_format($order->total, 2) }}</div>
                        <small class="text-muted">{{ $order->created_at->format('M d, h:i A') }}</small>
                    </div>
                </div>

                <!-- Items -->
                <div class="bg-light rounded p-2 mb-3">
                    @foreach($order->items as $item)
                    <div class="d-flex justify-content-between align-items-center py-1 item-check {{ $order->status === 'packed' ? 'checked' : '' }}">
                        <div>
                            <span class="fw-medium">{{ $item->qty }}x</span>
                            <span>{{ $item->product_name }}</span>
                            @if($item->variant_name)
                            <small class="text-muted">({{ $item->variant_name }})</small>
                            @endif
                        </div>
                        @if($order->status === 'packed')
                        <i class="bi bi-check-circle-fill text-success"></i>
                        @endif
                    </div>
                    @endforeach
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2">
                    @if(in_array($order->status, ['processing', 'confirmed', 'ready_to_pick']))
                    <form action="{{ route('admin.warehouses.orders.start-picking', [$warehouse->id, $order->id]) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-play-fill me-1"></i> Start Picking
                        </button>
                    </form>
                    @endif

                    @if($order->status === 'picking')
                    <form action="{{ route('admin.warehouses.orders.mark-packed', [$warehouse->id, $order->id]) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-check2-all me-1"></i> Mark as Packed
                        </button>
                    </form>
                    @endif

                    @if($order->status === 'packed')
                    <button class="btn btn-outline-success btn-sm" disabled>
                        <i class="bi bi-check-circle-fill me-1"></i> Packed
                    </button>
                    @if($order->packed_at)
                    <small class="text-muted align-self-center">
                        {{ $order->packed_at->format('M d, h:i A') }}
                    </small>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">No orders pending pick</p>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($orders->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $orders->appends(request()->query())->links() }}
</div>
@endif

@endsection