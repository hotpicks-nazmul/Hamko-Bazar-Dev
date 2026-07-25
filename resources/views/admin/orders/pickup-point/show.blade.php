@extends('admin.layouts.app')

@section('title', 'Pick-up Point Order Details - ' . $order->order_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-geo-alt me-2"></i>Pick-up Point Order
            <span class="text-primary">{{ $order->order_number }}</span>
        </h4>
        <p class="text-muted mb-0">Placed on {{ $order->created_at->format('F d, Y \a\t H:i') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.pickup-point') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
        <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn btn-outline-primary" target="_blank">
            <i class="bi bi-receipt me-1"></i> View Invoice
        </a>
        @if(!$order->picked_up_at && $order->status !== 'cancelled')
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#markPickedUpModal">
                <i class="bi bi-box-seam me-1"></i> Mark as Picked Up
            </button>
        @endif
    </div>
</div>

<div class="row">
    <!-- Left Column - Order Details -->
    <div class="col-lg-8">
        <!-- Order Status Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Order Status</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Order Status</label>
                        <div class="mt-1">
                            @if($order->picked_up_at)
                                <span class="badge bg-success fs-6">
                                    <i class="bi bi-check-circle me-1"></i>Picked Up
                                </span>
                                <small class="text-muted d-block mt-1">
                                    {{ $order->picked_up_at->format('d M Y, H:i') }}
                                </small>
                            @else
                                <span class="badge {{ $order->status_badge_class }} fs-6">{{ ucfirst($order->status) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Payment Status</label>
                        <div class="mt-1">
                            <span class="badge {{ $order->payment_status_badge_class }} fs-6">{{ ucfirst($order->payment_status) }}</span>
                            <small class="text-muted d-block mt-1">{{ ucfirst($order->payment_method ?? 'N/A') }}</small>
                        </div>
                    </div>
                </div>

                @if($order->picked_up_at && $order->picked_up_by)
                    <hr>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Picked up by:</strong> {{ $order->picked_up_by }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Order Items -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-box me-2"></i>Order Items ({{ $order->items->count() }})</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                @if(auth()->user()->hasPermission('orders.view-pricing'))
                                <th class="text-center">Price</th>
                                @endif
                                <th class="text-center">Qty</th>
                                @if(auth()->user()->hasPermission('orders.view-pricing'))
                                <th class="text-end">Total</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->product && $item->product->featured_image)
                                            @php
                                                $imageUrl = $item->product->featured_image;
                                                if(!str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                                    $imageUrl = '/storage/' . $imageUrl;
                                                }
                                            @endphp
                                            <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2" style="width: 50px; height: 50px;">
                                                <i class="bi bi-image text-white"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-medium">{{ $item->product_name }}</div>
                                            @if($item->variation)
                                                <small class="text-muted">{{ $item->variation }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                @if(auth()->user()->hasPermission('orders.view-pricing'))
                                <td class="text-center">৳{{ number_format($item->price, 2) }}</td>
                                @endif
                                <td class="text-center">{{ $item->quantity }}</td>
                                @if(auth()->user()->hasPermission('orders.view-pricing'))
                                <td class="text-end fw-semibold">৳{{ number_format($item->total, 2) }}</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        @if(auth()->user()->hasPermission('orders.view-pricing'))
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end">Subtotal:</td>
                                <td class="text-end">৳{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            @if($order->discount > 0)
                            <tr>
                                <td colspan="3" class="text-end text-success">Discount:</td>
                                <td class="text-end text-success">-৳{{ number_format($order->discount, 2) }}</td>
                            </tr>
                            @endif
                            @if($order->shipping_cost > 0)
                            <tr>
                                <td colspan="3" class="text-end">Shipping:</td>
                                <td class="text-end">৳{{ number_format($order->shipping_cost, 2) }}</td>
                            </tr>
                            @endif
                            @if($order->tax > 0)
                            <tr>
                                <td colspan="3" class="text-end">Tax:</td>
                                <td class="text-end">৳{{ number_format($order->tax, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold fs-5 text-primary">৳{{ number_format($order->total, 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($order->notes)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{!! nl2br(e($order->notes)) !!}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column - Customer & Pickup Info -->
    <div class="col-lg-4">
        <!-- Pick-up Point Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Pick-up Point</h6>
            </div>
            <div class="card-body">
                @if($order->pickupPointLocation)
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                            <i class="bi bi-shop text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">{{ $order->pickupPointLocation->name }}</h6>
                            <p class="text-muted small mb-0">{{ $order->pickupPointLocation->code }}</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="text-muted small d-block"><i class="bi bi-geo me-1"></i>Address</label>
                        <p class="mb-0">{{ $order->pickupPointLocation->formatted_address }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small d-block"><i class="bi bi-telephone me-1"></i>Phone</label>
                        <p class="mb-0">{{ $order->pickupPointLocation->phone }}</p>
                    </div>
                    
                    @if($order->pickupPointLocation->email)
                    <div class="mb-3">
                        <label class="text-muted small d-block"><i class="bi bi-envelope me-1"></i>Email</label>
                        <p class="mb-0">{{ $order->pickupPointLocation->email }}</p>
                    </div>
                    @endif
                    
                    @if($order->pickupPointLocation->opening_hours)
                    <div class="mb-0">
                        <label class="text-muted small d-block"><i class="bi bi-clock me-1"></i>Opening Hours</label>
                        <p class="mb-0">{!! nl2br(e($order->pickupPointLocation->opening_hours)) !!}</p>
                    </div>
                    @endif
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No pick-up point assigned to this order.
                    </div>
                @endif
            </div>
        </div>

        <!-- Customer Information -->
        @if(auth()->user()->hasPermission('orders.view-customer'))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-light rounded-circle p-2 me-3">
                        <i class="bi bi-person fs-4 text-muted"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $order->billing_full_name }}</h6>
                        @if($order->user)
                            <small class="text-muted">Registered Customer</small>
                        @else
                            <small class="text-muted">Guest Customer</small>
                        @endif
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-2">
                    <label class="text-muted small d-block">Email</label>
                    <p class="mb-0">{{ $order->billing_email }}</p>
                </div>
                
                <div class="mb-0">
                    <label class="text-muted small d-block">Phone</label>
                    <p class="mb-0">{{ $order->billing_phone }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Update Status -->
        @if(!$order->picked_up_at && $order->status !== 'cancelled')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Update Status</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="mb-2">
                    @csrf
                    <div class="input-group">
                        <select name="status" class="form-select">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Ready for Pickup</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
                
                <form action="{{ route('admin.orders.payment-status', $order->id) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <select name="payment_status" class="form-select">
                            <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ $order->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Mark as Picked Up Modal -->
@if(!$order->picked_up_at && $order->status !== 'cancelled')
<div class="modal fade" id="markPickedUpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.orders.pickup-point.picked-up', $order->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-box-seam me-2"></i>Mark Order as Picked Up
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Order:</strong> {{ $order->order_number }}<br>
                        <strong>Customer:</strong> {{ $order->billing_full_name }}
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Picked Up By <span class="text-danger">*</span></label>
                        <input type="text" name="picked_up_by" class="form-control @error('picked_up_by') is-invalid @enderror" placeholder="Name of person picking up" required>
                        @error('picked_up_by')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Enter the name of the person who picked up the order</small>
                    </div>
                    
                    <div class="mb-0">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes about the pickup..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Confirm Pickup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
