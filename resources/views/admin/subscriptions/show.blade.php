@extends('admin.layouts.app')

@section('title', 'Subscription Details')

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Subscription Details</h4>
            <p class="text-muted mb-0">{{ $subscription->subscription_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Status Banner -->
            <div class="alert alert-{{ $subscription->status_badge_class === 'bg-success' ? 'success' : ($subscription->status_badge_class === 'bg-danger' ? 'danger' : ($subscription->status_badge_class === 'bg-warning' ? 'warning' : 'info')) }} d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-{{ $subscription->status === 'active' ? 'check-circle' : ($subscription->status === 'cancelled' ? 'x-circle' : 'info-circle') }} fs-4 me-3"></i>
                    <div>
                        <h5 class="mb-0">{{ ucfirst($subscription->status) }}</h5>
                        <p class="mb-0 small">Subscription is currently {{ $subscription->status }}</p>
                    </div>
                </div>
                <span class="badge bg-white text-dark">{{ $subscription->billing_frequency_label }}</span>
            </div>

            <!-- Customer & Product Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="bi bi-person text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $subscription->shipping_full_name }}</h6>
                                    <small class="text-muted">{{ $subscription->user->email ?? $subscription->shipping_email }}</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <small class="text-muted d-block">Email</small>
                                    <span>{{ $subscription->shipping_email }}</span>
                                </div>
                                <div class="col-6 mb-2">
                                    <small class="text-muted d-block">Phone</small>
                                    <span>{{ $subscription->shipping_phone }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-box me-2"></i>Product Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                @php
                                    $imageUrl = $subscription->product->featured_image ?? null;
                                    if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                        $imageUrl = '/storage/' . $imageUrl;
                                    }
                                @endphp
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-white fs-4"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ $subscription->product->name ?? 'N/A' }}</h6>
                                    <small class="text-muted">SKU: {{ $subscription->product->sku ?? 'N/A' }}</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <small class="text-muted d-block">Quantity</small>
                                    <span>{{ $subscription->quantity }}</span>
                                </div>
                                <div class="col-6 mb-2">
                                    <small class="text-muted d-block">Unit Price</small>
                                    <span>৳{{ number_format($subscription->unit_price, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Subscription Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Plan Name</small>
                            <span class="fw-medium">{{ $subscription->plan_name }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Billing Frequency</small>
                            <span class="badge bg-light text-dark">{{ $subscription->billing_frequency_label }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Start Date</small>
                            <span>{{ $subscription->start_date->format('M d, Y') }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">End Date</small>
                            <span>{{ $subscription->end_date ? $subscription->end_date->format('M d, Y') : 'Ongoing' }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Next Billing Date</small>
                            <span class="{{ $subscription->next_billing_date && $subscription->next_billing_date->isPast() ? 'text-danger' : '' }}">
                                {{ $subscription->next_billing_date ? $subscription->next_billing_date->format('M d, Y') : 'N/A' }}
                                @if($subscription->next_billing_date && $subscription->next_billing_date->isPast())
                                    <small class="text-danger">(Overdue)</small>
                                @endif
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Last Billed</small>
                            <span>{{ $subscription->last_billing_at ? $subscription->last_billing_at->format('M d, Y H:i') : 'Never' }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Billing Cycles</small>
                            <span>
                                @if($subscription->hasUnlimitedCycles())
                                    {{ $subscription->completed_billing_cycles }} / Unlimited
                                @else
                                    {{ $subscription->completed_billing_cycles }} / {{ $subscription->total_billing_cycles }}
                                    @php
                                        $progress = $subscription->total_billing_cycles > 0 
                                            ? ($subscription->completed_billing_cycles / $subscription->total_billing_cycles) * 100 
                                            : 0;
                                    @endphp
                                    <div class="progress mt-1" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%"></div>
                                    </div>
                                @endif
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Remaining Cycles</small>
                            <span>{{ $subscription->getRemainingCycles() ?? 'Unlimited' }}</span>
                        </div>
                    </div>
                    @if($subscription->description)
                    <div class="mb-0">
                        <small class="text-muted d-block">Description</small>
                        <p class="mb-0">{{ $subscription->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Shipping Address</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Name</small>
                            <span>{{ $subscription->shipping_full_name }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Contact</small>
                            <span>{{ $subscription->shipping_email }}<br>{{ $subscription->shipping_phone }}</span>
                        </div>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Address</small>
                        <p class="mb-0">{{ $subscription->shipping_full_address }}</p>
                    </div>
                </div>
            </div>

            @if($subscription->notes)
            <!-- Notes -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-sticky me-2"></i>Notes</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $subscription->notes }}</p>
                </div>
            </div>
            @endif

            @if($subscription->isCancelled())
            <!-- Cancellation Info -->
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-danger">
                <div class="card-header bg-white">
                    <h6 class="mb-0 text-danger"><i class="bi bi-x-circle me-2"></i>Cancellation Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Cancelled At</small>
                            <span>{{ $subscription->cancelled_at?->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Cancelled By</small>
                            <span>{{ $subscription->cancelledBy?->name ?? 'System' }}</span>
                        </div>
                    </div>
                    @if($subscription->cancellation_reason)
                    <div class="mb-0">
                        <small class="text-muted d-block">Cancellation Reason</small>
                        <p class="mb-0">{{ $subscription->cancellation_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Summary Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Pricing Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Unit Price</span>
                        <span>৳{{ number_format($subscription->unit_price, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Quantity</span>
                        <span>{{ $subscription->quantity }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Total per Cycle</span>
                        <span class="fw-bold text-primary fs-5">৳{{ number_format($subscription->total_price, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Status</h6>
                </div>
                @if(auth()->user()->hasPermission('subscriptions.timeline'))
<div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Current Status</span>
                        <span class="badge {{ $subscription->payment_status_badge_class }}">
                            {{ ucfirst($subscription->payment_status) }}
                        </span>
                    </div>
                    @if($subscription->order)
                    <div class="mb-0">
                        <small class="text-muted d-block">Last Order</small>
                        <a href="{{ route('admin.orders.show', $subscription->order) }}" class="text-decoration-none">
                            {{ $subscription->order->order_number }}
                        </a>
                    </div>
                    @endif
                </div>
@endif
            </div>

            <!-- Timeline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker bg-primary"></div>
                                <div>
                                    <small class="text-muted">Created</small>
                                    <p class="mb-0 small">{{ $subscription->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        @if($subscription->activated_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker bg-success"></div>
                                <div>
                                    <small class="text-muted">Activated</small>
                                    <p class="mb-0 small">{{ $subscription->activated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($subscription->last_billing_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker bg-info"></div>
                                <div>
                                    <small class="text-muted">Last Billed</small>
                                    <p class="mb-0 small">{{ $subscription->last_billing_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($subscription->cancelled_at)
                        <div class="timeline-item">
                            <div class="d-flex">
                                <div class="timeline-marker bg-danger"></div>
                                <div>
                                    <small class="text-muted">Cancelled</small>
                                    <p class="mb-0 small">{{ $subscription->cancelled_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($subscription->status === 'active')
                            @if($subscription->next_billing_date && $subscription->next_billing_date->lte(today()))
                            <form method="POST" action="{{ route('admin.subscriptions.process-billing', $subscription) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-cash-coin me-1"></i> Process Billing
                                </button>
                            </form>
                            @endif
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#pauseModal">
                                <i class="bi bi-pause-circle me-1"></i> Pause Subscription
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="bi bi-x-circle me-1"></i> Cancel Subscription
                            </button>
                        @elseif($subscription->status === 'paused')
                            <form method="POST" action="{{ route('admin.subscriptions.activate', $subscription) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-play-circle me-1"></i> Activate Subscription
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="bi bi-x-circle me-1"></i> Cancel Subscription
                            </button>
                        @elseif($subscription->status === 'pending')
                            <form method="POST" action="{{ route('admin.subscriptions.activate', $subscription) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-play-circle me-1"></i> Activate Subscription
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="bi bi-x-circle me-1"></i> Cancel Subscription
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Pause Modal -->
@if($subscription->status === 'active')
<div class="modal fade" id="pauseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.subscriptions.pause', $subscription) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Pause Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to pause this subscription?</p>
                    <p class="text-muted small">The subscription will remain paused until manually activated again. No billing will occur while paused.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Pause Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Cancel Modal -->
@if(in_array($subscription->status, ['active', 'paused', 'pending']))
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.subscriptions.cancel', $subscription) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this subscription?</p>
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required placeholder="Please provide a reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Cancel Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
    }
    .timeline-item {
        position: relative;
    }
    .timeline-marker {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 12px;
        margin-top: 4px;
        flex-shrink: 0;
    }
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 5px;
        top: 20px;
        bottom: -10px;
        width: 2px;
        background: #e9ecef;
    }
</style>
@endpush