@extends('admin.layouts.app')

@section('title', 'Pick-up Point Details - ' . $pickupPoint->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-shop me-2"></i>{{ $pickupPoint->name }}
            @if($pickupPoint->code)
                <small class="text-muted fs-6">({{ $pickupPoint->code }})</small>
            @endif
        </h4>
        <p class="text-muted mb-0">
            <span class="badge {{ $pickupPoint->status_badge_class }}">{{ $pickupPoint->status_text }}</span>
            <span class="ms-2"><i class="bi bi-geo-alt me-1"></i>{{ $pickupPoint->city }}</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.pickup-points.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
        <a href="{{ route('admin.pickup-points.edit', $pickupPoint->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
    </div>
</div>

<div class="row">
    <!-- Left Column - Details -->
    <div class="col-lg-8">
        <!-- Contact Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Contact Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small d-block"><i class="bi bi-telephone me-1"></i>Phone</label>
                        <p class="mb-0 fs-5">{{ $pickupPoint->phone }}</p>
                    </div>
                    @if($pickupPoint->email)
                    <div class="col-md-6">
                        <label class="text-muted small d-block"><i class="bi bi-envelope me-1"></i>Email</label>
                        <p class="mb-0">{{ $pickupPoint->email }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Address</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">{{ $pickupPoint->address }}</p>
                <p class="mb-0 text-muted">
                    {{ $pickupPoint->city }}{{ $pickupPoint->state ? ', ' . $pickupPoint->state : '' }}{{ $pickupPoint->postcode ? ' - ' . $pickupPoint->postcode : '' }}
                    <br>
                    {{ $pickupPoint->country }}
                </p>
            </div>
        </div>

        <!-- Opening Hours & Notes -->
        <div class="row">
            @if($pickupPoint->opening_hours)
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Opening Hours</h6>
                    </div>
                    <div class="card-body">
                        {!! nl2br(e($pickupPoint->opening_hours)) !!}
                    </div>
                </div>
            </div>
            @endif
            @if($pickupPoint->notes)
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Notes</h6>
                    </div>
                    <div class="card-body">
                        {!! nl2br(e($pickupPoint->notes)) !!}
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Recent Orders -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-cart me-2"></i>Recent Orders</h6>
                <a href="{{ route('admin.orders.pickup-point') }}?pickup_point_id={{ $pickupPoint->id }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                @if($pickupPoint->orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pickupPoint->orders as $order)
                                <tr>
                                    <td class="fw-semibold">{{ $order->order_number }}</td>
                                    <td>{{ $order->billing_full_name }}</td>
                                    <td>
                                        @if($order->picked_up_at)
                                            <span class="badge bg-success">Picked Up</span>
                                        @else
                                            <span class="badge {{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('d M, Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.orders.pickup-point.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
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
                        <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
                        <p class="text-muted mb-0">No orders yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column - Stats & Actions -->
    <div class="col-lg-4">
        <!-- Statistics -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Order Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                    <span><i class="bi bi-cart me-2 text-primary"></i>Total Orders</span>
                    <span class="badge bg-primary fs-6">{{ $pickupPoint->orders()->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                    <span><i class="bi bi-hourglass me-2 text-warning"></i>Pending</span>
                    <span class="badge bg-warning">{{ $pickupPoint->orders()->where('status', 'pending')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                    <span><i class="bi bi-arrow-repeat me-2 text-info"></i>Processing</span>
                    <span class="badge bg-info">{{ $pickupPoint->orders()->where('status', 'processing')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                    <span><i class="bi bi-check-circle me-2 text-primary"></i>Ready</span>
                    <span class="badge bg-primary">{{ $pickupPoint->orders()->where('status', 'confirmed')->whereNull('picked_up_at')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                    <span><i class="bi bi-box-seam me-2 text-success"></i>Picked Up</span>
                    <span class="badge bg-success">{{ $pickupPoint->orders()->whereNotNull('picked_up_at')->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.pickup-points.toggle-status', $pickupPoint->id) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-{{ $pickupPoint->is_active ? 'warning' : 'success' }} w-100">
                        <i class="bi bi-{{ $pickupPoint->is_active ? 'pause-circle' : 'play-circle' }} me-1"></i>
                        {{ $pickupPoint->is_active ? 'Deactivate' : 'Activate' }} Location
                    </button>
                </form>
                <a href="{{ route('admin.pickup-points.edit', $pickupPoint->id) }}" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-pencil me-1"></i> Edit Details
                </a>
                <a href="{{ route('admin.orders.pickup-point') }}?pickup_point_id={{ $pickupPoint->id }}" class="btn btn-outline-primary w-100">
                    <i class="bi bi-list-ul me-1"></i> View All Orders
                </a>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted d-block">Created</small>
                    <span>{{ $pickupPoint->created_at->format('d M Y, H:i') }}</span>
                </div>
                <div class="mb-0">
                    <small class="text-muted d-block">Last Updated</small>
                    <span>{{ $pickupPoint->updated_at->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
