@extends('admin.layouts.app')

@section('title', 'Seller Order Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Order #{{ $order->order_number }}</h4>
        <small class="text-muted">
            <i class="bi bi-calendar3 me-1"></i>Created: {{ $order->created_at->format('d M, Y H:i') }}
            <span class="badge bg-purple ms-2" style="background-color: #6f42c1;">Seller Order</span>
        </small>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('orders.show-invoice'))
        <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn btn-outline-secondary" target="_blank">
            <i class="bi bi-receipt me-1"></i> Invoice
        </a>
        @endif
        <a href="{{ route('admin.orders.seller') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Seller Orders
        </a>
    </div>
</div>

<!-- Status Overview Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="text-muted small text-uppercase">Order Status</div>
                <span class="badge {{ $order->status_badge_class }} fs-6">{{ ucfirst($order->status) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="text-muted small text-uppercase">Payment Status</div>
                <span class="badge {{ $order->payment_status_badge_class }} fs-6">{{ ucfirst($order->payment_status) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="text-muted small text-uppercase">Payment Method</div>
                <div class="fw-semibold">{{ ucfirst($order->payment_method ?? 'N/A') }}</div>
            </div>
        </div>
    </div>
    @if(auth()->user()->hasPermission('orders.view-pricing'))
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="text-muted small text-uppercase">Order Total</div>
                <div class="h4 mb-0 text-primary">৳{{ number_format($order->total, 2) }}</div>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="row">
    <!-- Order Status & Actions & Items -->
    <div class="col-lg-8 mb-4">
        <!-- Order Status Management -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-sliders me-2"></i>Order Status Management</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" id="statusForm">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Update Order Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="refunded" {{ $order->status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Update Payment Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="failed" {{ $order->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-lg me-1"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
                
                @if(in_array($order->status, ['processing', 'confirmed']))
                <hr>
                <form action="{{ route('admin.orders.ship', $order->id) }}" method="POST" id="shipForm">
                    @csrf
                    <h6 class="fw-semibold mb-3"><i class="bi bi-truck me-2"></i>Ship Order</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Tracking Number</label>
                            <input type="text" name="tracking_number" class="form-control" placeholder="Enter tracking number" value="{{ $order->tracking_number }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Shipping Company</label>
                            <input type="text" name="shipping_company" class="form-control" placeholder="e.g., SSL Commerce, Pathao" value="{{ $order->shipping_company }}" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-box-arrow-up me-1"></i> Ship Order
                            </button>
                        </div>
                    </div>
                </form>
                @endif
                
                @if($order->tracking_number)
                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-geo-alt me-2"></i>
                    <strong>Tracking Information:</strong> {{ $order->tracking_number }}
                    @if($order->shipping_company)
                        <span class="text-muted">via {{ $order->shipping_company }}</span>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- Seller Information -->
        @php
            $sellers = [];
            foreach($order->items as $item) {
                if($item->product && $item->product->seller) {
                    $sellers[$item->product->seller->id] = $item->product->seller;
                }
            }
        @endphp
        
        @if(count($sellers) > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-shop me-2"></i>Seller Information</h5>
            </div>
            <div class="card-body">
                @foreach($sellers as $seller)
                <div class="d-flex align-items-center mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                    <div class="bg-light rounded-circle p-3 me-3">
                        <i class="bi bi-shop fs-4 text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $seller->name }}</h6>
                        <p class="mb-0 text-muted small">
                            <i class="bi bi-envelope me-1"></i>{{ $seller->email }}
                            @if($seller->phone)
                                <span class="ms-2"><i class="bi bi-telephone me-1"></i>{{ $seller->phone }}</span>
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i> View Seller
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Order Items -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Order Items ({{ $order->items->count() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Product</th>
                                <th>Seller</th>
                                @if(auth()->user()->hasPermission('orders.view-pricing'))
                                <th>Price</th>
                                @endif
                                <th>Qty</th>
                                @if(auth()->user()->hasPermission('orders.view-pricing'))
                                <th>Total</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->product && $item->product->featured_image)
                                            @php
                                                $imageUrl = $item->product->featured_image;
                                                if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                                    $imageUrl = '/storage/' . $imageUrl;
                                                }
                                            @endphp
                                            <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
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
                                <td>
                                    @if($item->product && $item->product->seller)
                                        <span class="badge bg-info text-dark">
                                            <i class="bi bi-shop me-1"></i>{{ $item->product->seller->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @if(auth()->user()->hasPermission('orders.view-pricing'))
                                <td>৳{{ number_format($item->price, 2) }}</td>
                                @endif
                                <td>{{ $item->quantity }}</td>
                                @if(auth()->user()->hasPermission('orders.view-pricing'))
                                <td class="fw-semibold">৳{{ number_format($item->total, 2) }}</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        @if(auth()->user()->hasPermission('orders.view-pricing'))
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end"><strong>Subtotal:</strong></td>
                                <td>৳{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            @if($order->shipping_cost > 0)
                            <tr>
                                <td colspan="5" class="text-end"><strong>Shipping:</strong></td>
                                <td>৳{{ number_format($order->shipping_cost, 2) }}</td>
                            </tr>
                            @endif
                            @if($order->tax > 0)
                            <tr>
                                <td colspan="5" class="text-end"><strong>Tax:</strong></td>
                                <td>৳{{ number_format($order->tax, 2) }}</td>
                            </tr>
                            @endif
                            @if($order->discount > 0)
                            <tr>
                                <td colspan="5" class="text-end"><strong>Discount:</strong></td>
                                <td>-৳{{ number_format($order->discount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="5" class="text-end"><strong class="fs-5">Total:</strong></td>
                                <td class="fw-bold fs-5 text-primary">৳{{ number_format($order->total, 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer & Shipping Info -->
    <div class="col-lg-4">
        <!-- Customer Info -->
        @if(auth()->user()->hasPermission('orders.view-customer'))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h5>
            </div>
            <div class="card-body">
                @if($order->user)
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light rounded-circle p-2 me-3">
                            <i class="bi bi-person fs-4 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $order->user->name }}</h6>
                            <small class="text-muted">Registered Customer</small>
                        </div>
                    </div>
                    <hr>
                @endif
                
                <h6 class="fw-semibold mb-2">Billing Address</h6>
                <p class="mb-0">
                    <strong>{{ $order->billing_full_name }}</strong><br>
                    {{ $order->billing_email }}<br>
                    {{ $order->billing_phone }}<br>
                    {{ $order->billing_address }}<br>
                    {{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_postcode }}<br>
                    {{ $order->billing_country }}
                </p>
            </div>
        </div>
        @endif

        <!-- Shipping Info -->
        @if(auth()->user()->hasPermission('orders.view-customer'))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Shipping Address</h5>
            </div>
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Shipping To</h6>
                <p class="mb-0">
                    <strong>{{ $order->shipping_full_name }}</strong><br>
                    {{ $order->shipping_email }}<br>
                    {{ $order->shipping_phone }}<br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postcode }}<br>
                    {{ $order->shipping_country }}
                </p>
            </div>
        </div>
        @endif

        <!-- Order Notes -->
        @if($order->notes)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-sticky me-2"></i>Order Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $order->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
