@extends('admin.layouts.app')

@section('title', 'Edit Subscription')

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Subscription</h4>
            <p class="text-muted mb-0">{{ $subscription->subscription_number }} - {{ $subscription->plan_name }}</p>
        </div>
        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Subscriptions
        </a>
    </div>

    <form id="subscriptionForm" method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Customer Information (Read Only) -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Customer</label>
                                <p class="fw-medium mb-0">{{ $subscription->user->name ?? 'N/A' }}</p>
                                <small class="text-muted">{{ $subscription->user->email ?? '' }}</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Product</label>
                                <div class="d-flex align-items-center">
                                    @php
                                        $imageUrl = $subscription->product->featured_image ?? null;
                                        if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                            $imageUrl = '/storage/' . $imageUrl;
                                        }
                                    @endphp
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                            <i class="bi bi-image text-white"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="fw-medium mb-0">{{ $subscription->product->name ?? 'N/A' }}</p>
                                        <small class="text-muted">SKU: {{ $subscription->product->sku ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Plan Details -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Subscription Plan Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product <span class="text-danger">*</span></label>
                                <select name="product_id" id="productSelect" class="form-select @error('product_id') is-invalid @enderror" required>
                                    <option value="">Choose a product...</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-sku="{{ $product->sku }}" data-stock="{{ $product->quantity }}" {{ old('product_id', $subscription->product_id) == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} (SKU: {{ $product->sku }}) - ৳{{ number_format($product->price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                                <input type="text" name="plan_name" class="form-control @error('plan_name') is-invalid @enderror" 
                                       value="{{ old('plan_name', $subscription->plan_name) }}" required>
                                @error('plan_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Billing Frequency <span class="text-danger">*</span></label>
                                <select name="billing_frequency" class="form-select @error('billing_frequency') is-invalid @enderror" required>
                                    <option value="weekly" {{ old('billing_frequency', $subscription->billing_frequency) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="bi_weekly" {{ old('billing_frequency', $subscription->billing_frequency) === 'bi_weekly' ? 'selected' : '' }}>Bi-Weekly</option>
                                    <option value="monthly" {{ old('billing_frequency', $subscription->billing_frequency) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="quarterly" {{ old('billing_frequency', $subscription->billing_frequency) === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    <option value="semi_annually" {{ old('billing_frequency', $subscription->billing_frequency) === 'semi_annually' ? 'selected' : '' }}>Semi-Annually</option>
                                    <option value="annually" {{ old('billing_frequency', $subscription->billing_frequency) === 'annually' ? 'selected' : '' }}>Annually</option>
                                </select>
                                @error('billing_frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                                       value="{{ old('quantity', $subscription->quantity) }}" min="1" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit Price</label>
                                <input type="text" class="form-control" value="৳{{ number_format($subscription->unit_price, 2) }}" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="pending" {{ old('status', $subscription->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="active" {{ old('status', $subscription->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="paused" {{ old('status', $subscription->status) === 'paused' ? 'selected' : '' }}>Paused</option>
                                    <option value="cancelled" {{ old('status', $subscription->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="expired" {{ old('status', $subscription->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date', $subscription->start_date->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Next Billing Date</label>
                                <input type="date" name="next_billing_date" class="form-control @error('next_billing_date') is-invalid @enderror" 
                                       value="{{ old('next_billing_date', $subscription->next_billing_date?->format('Y-m-d')) }}">
                                @error('next_billing_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">End Date (Optional)</label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                                       value="{{ old('end_date', $subscription->end_date?->format('Y-m-d')) }}">
                                <small class="text-muted">Leave empty for ongoing</small>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Billing Cycles (Optional)</label>
                                <input type="number" name="total_billing_cycles" class="form-control @error('total_billing_cycles') is-invalid @enderror" 
                                       value="{{ old('total_billing_cycles', $subscription->total_billing_cycles) }}" min="1">
                                <small class="text-muted">Leave empty for unlimited</small>
                                @error('total_billing_cycles')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Completed Cycles</label>
                                <input type="text" class="form-control" value="{{ $subscription->completed_billing_cycles }}" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3">{{ old('description', $subscription->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Price Preview -->
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-calculator me-2"></i>Total Price:</span>
                            <strong class="fs-5">৳{{ number_format($subscription->total_price, 2) }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Shipping Address</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_first_name" 
                                       class="form-control @error('shipping_first_name') is-invalid @enderror" 
                                       value="{{ old('shipping_first_name', $subscription->shipping_first_name) }}" required>
                                @error('shipping_first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_last_name" 
                                       class="form-control @error('shipping_last_name') is-invalid @enderror" 
                                       value="{{ old('shipping_last_name', $subscription->shipping_last_name) }}" required>
                                @error('shipping_last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="shipping_email" 
                                       class="form-control @error('shipping_email') is-invalid @enderror" 
                                       value="{{ old('shipping_email', $subscription->shipping_email) }}" required>
                                @error('shipping_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_phone" 
                                       class="form-control @error('shipping_phone') is-invalid @enderror" 
                                       value="{{ old('shipping_phone', $subscription->shipping_phone) }}" required>
                                @error('shipping_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea name="shipping_address" 
                                      class="form-control @error('shipping_address') is-invalid @enderror" 
                                      rows="2" required>{{ old('shipping_address', $subscription->shipping_address) }}</textarea>
                            @error('shipping_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_city" 
                                       class="form-control @error('shipping_city') is-invalid @enderror" 
                                       value="{{ old('shipping_city', $subscription->shipping_city) }}" required>
                                @error('shipping_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">State <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_state" 
                                       class="form-control @error('shipping_state') is-invalid @enderror" 
                                       value="{{ old('shipping_state', $subscription->shipping_state) }}" required>
                                @error('shipping_state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Postcode <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_postcode" 
                                       class="form-control @error('shipping_postcode') is-invalid @enderror" 
                                       value="{{ old('shipping_postcode', $subscription->shipping_postcode) }}" required>
                                @error('shipping_postcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_country" 
                                       class="form-control @error('shipping_country') is-invalid @enderror" 
                                       value="{{ old('shipping_country', $subscription->shipping_country) }}" required>
                                @error('shipping_country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-sticky me-2"></i>Additional Notes</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                  rows="3">{{ old('notes', $subscription->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Summary Card -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Subscription Info</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subscription #:</span>
                            <span class="fw-medium">{{ $subscription->subscription_number }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Created:</span>
                            <span>{{ $subscription->created_at->format('M d, Y') }}</span>
                        </div>
                        @if($subscription->activated_at)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Activated:</span>
                            <span>{{ $subscription->activated_at->format('M d, Y') }}</span>
                        </div>
                        @endif
                        @if($subscription->last_billing_at)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Last Billed:</span>
                            <span>{{ $subscription->last_billing_at->format('M d, Y') }}</span>
                        </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total:</span>
                            <span class="fw-bold text-primary">৳{{ number_format($subscription->total_price, 2) }}</span>
                        </div>
                    </div>
                </div>

                @if($subscription->isCancelled())
                <!-- Cancellation Info -->
                <div class="card border-0 shadow-sm mb-3 border-start border-4 border-danger">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 text-danger"><i class="bi bi-x-circle me-2"></i>Cancellation Info</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Cancelled At:</span>
                            <span>{{ $subscription->cancelled_at?->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Cancelled By:</span>
                            <span>{{ $subscription->cancelledBy?->name ?? 'System' }}</span>
                        </div>
                        @if($subscription->cancellation_reason)
                        <div class="mt-2">
                            <span class="text-muted">Reason:</span>
                            <p class="mb-0 small">{{ $subscription->cancellation_reason }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-outline-primary">
                                <i class="bi bi-eye me-1"></i> View Details
                            </a>
                            
                            @if($subscription->status === 'active')
                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#pauseModal">
                                <i class="bi bi-pause-circle me-1"></i> Pause Subscription
                            </button>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="bi bi-x-circle me-1"></i> Cancel Subscription
                            </button>
                            @elseif($subscription->status === 'paused')
                            <form method="POST" action="{{ route('admin.subscriptions.activate', $subscription) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <i class="bi bi-play-circle me-1"></i> Activate Subscription
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="bi bi-x-circle me-1"></i> Cancel Subscription
                            </button>
                            @elseif(in_array($subscription->status, ['pending']))
                            <form method="POST" action="{{ route('admin.subscriptions.activate', $subscription) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <i class="bi bi-play-circle me-1"></i> Activate Subscription
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    @if(in_array($subscription->status, ['pending', 'cancelled', 'expired']))
    <a href="{{ route('admin.subscriptions.destroy', $subscription) }}" 
       class="btn btn-outline-danger floating-reset-btn"
       onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this subscription?')) { document.getElementById('deleteForm').submit(); }">
        <i class="bi bi-trash me-1"></i> Delete
    </a>
    @endif
    <button type="submit" form="subscriptionForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Subscription
    </button>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="{{ route('admin.subscriptions.destroy', $subscription) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

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
                    <p class="text-muted small">The subscription will remain paused until manually activated again.</p>
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
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush