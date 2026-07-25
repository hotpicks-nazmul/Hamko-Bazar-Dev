@extends('admin.layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Order #{{ $order->order_number }}</h4>
        <small class="text-muted">
            <i class="bi bi-calendar3 me-1"></i>Created: {{ $order->created_at->format('d M, Y H:i') }}
            <span class="badge {{ $order->order_type === 'pos' ? 'bg-warning' : 'bg-info' }} ms-2">
                                    {{ $order->order_type === 'pos' ? 'POS Order' : 'Inhouse Order' }}
                                </span>
        </small>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('orders.show-invoice'))
        <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn btn-outline-secondary" target="_blank">
            <i class="bi bi-receipt me-1"></i> Invoice
        </a>
        @endif
        <a href="{{ route('admin.orders.in-house') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Inhouse Orders
        </a>
    </div>
</div>

<!-- Status Overview Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-clipboard-check"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Order Status</span>
            <span class="stat-card-value"><span class="badge {{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span></span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-credit-card-2-front"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Payment Status</span>
            <span class="stat-card-value"><span class="badge {{ $order->payment_status_badge_class }}">{{ ucfirst($order->payment_status) }}</span></span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-wallet2"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Payment Method</span>
            <span class="stat-card-value">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
        </div>
    </div>
    @if(auth()->user()->hasPermission('orders.view-pricing'))
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Order Total</span>
            <span class="stat-card-value">৳{{ number_format($order->total, 2) }}</span>
        </div>
    </div>
    @endif
</div>

<div class="row">
    <!-- Order Status & Actions & Items -->
    <div class="col-lg-8 mb-4">
        <!-- Order Status Management -->
        @if(auth()->user()->hasPermission('orders.show-update-status') || auth()->user()->hasPermission('orders.show-update-payment') || auth()->user()->hasPermission('orders.show-ship-order'))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-sliders me-2"></i>Order Status Management</h5>
            </div>
            <div class="card-body">
                @if(auth()->user()->hasPermission('orders.show-update-status') || auth()->user()->hasPermission('orders.show-update-payment'))
                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" id="statusForm">
                    @csrf
                    <div class="row g-3 align-items-end">
                        @if(auth()->user()->hasPermission('orders.show-update-status'))
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
                        @endif
                        @if(auth()->user()->hasPermission('orders.show-update-payment'))
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Update Payment Status</label>
                            <select name="payment_status" class="form-select" form="paymentForm">
                                <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="failed" {{ $order->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                        @endif
                        @if(auth()->user()->hasPermission('orders.show-update-status') || auth()->user()->hasPermission('orders.show-update-payment'))
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-lg me-1"></i> Update
                            </button>
                        </div>
                        @endif
                    </div>
                </form>
                
                <form action="{{ route('admin.orders.payment-status', $order->id) }}" method="POST" id="paymentForm" class="d-none"></form>
                @endif
                
                @if(auth()->user()->hasPermission('orders.show-ship-order') && in_array($order->status, ['processing', 'confirmed']))
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
        @endif
        
        @if(auth()->user()->hasPermission('orders.show-order-items'))
        <!-- Order Items -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Order Items ({{ $order->items->count() }})</h5>
                @if(auth()->user()->hasPermission('orders.inhouse-add-product'))
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Product
                </button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Product</th>
                                @if(auth()->user()->hasPermission('orders.view-pricing'))
                                <th>Price</th>
                                @endif
                                <th>Qty</th>
                                @if(auth()->user()->hasPermission('orders.view-pricing'))
                                <th class="text-end">Total</th>
                                @endif
                                @if(auth()->user()->hasPermission('orders.inhouse-edit-item') || auth()->user()->hasPermission('orders.inhouse-remove-item'))
                                <th style="width: 80px;">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @php
                                            $imageUrl = $item->product ? $item->product->featured_image : null;
                                            if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                                $imageUrl = '/storage/' . $imageUrl;
                                            }
                                        @endphp
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="bi bi-box text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ $item->product_name }}</h6>
                                            @if($item->variation && is_array($item->variation))
                                                <small class="text-muted">
                                                    @foreach($item->variation as $key => $value)
                                                        {{ ucfirst($key) }}: {{ $value }} 
                                                    @endforeach
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                @if(auth()->user()->hasPermission('orders.view-pricing'))
                                <td>৳{{ number_format($item->price, 2) }}</td>
                                @endif
                                <td>{{ $item->quantity }}</td>
                                @if(auth()->user()->hasPermission('orders.view-pricing'))
                                <td class="text-end">৳{{ number_format($item->total, 2) }}</td>
                                @endif
                                @if(auth()->user()->hasPermission('orders.inhouse-edit-item') || auth()->user()->hasPermission('orders.inhouse-remove-item'))
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if(auth()->user()->hasPermission('orders.inhouse-edit-item'))
                                        <button type="button" class="btn btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#editItemModal" data-item-id="{{ $item->id }}" data-product-id="{{ $item->product_id }}" data-item-name="{{ $item->product_name }}" data-item-qty="{{ $item->quantity }}" data-item-price="{{ $item->price }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('orders.inhouse-remove-item'))
                                        <form action="{{ route('admin.orders.in-house.remove-item', [$order->id, $item->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove {{ $item->product_name }} from order?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        @if(auth()->user()->hasPermission('orders.view-pricing'))
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end">৳{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            @if($order->discount > 0)
                            <tr>
                                <td colspan="4" class="text-end"><strong>Discount:</strong></td>
                                <td class="text-end text-danger">-৳{{ number_format($order->discount, 2) }}</td>
                            </tr>
                            @endif
                            @if($order->shipping_cost > 0)
                            <tr>
                                <td colspan="4" class="text-end"><strong>Shipping:</strong></td>
                                <td class="text-end">৳{{ number_format($order->shipping_cost, 2) }}</td>
                            </tr>
                            @endif
                            @if($order->tax > 0)
                            <tr>
                                <td colspan="4" class="text-end"><strong>Tax:</strong></td>
                                <td class="text-end">৳{{ number_format($order->tax, 2) }}</td>
                            </tr>
                            @endif
                            @if($order->coupon_code)
                            <tr>
                                <td colspan="4" class="text-end"><strong>Coupon ({{ $order->coupon_code }}):</strong></td>
                                <td class="text-end text-success">-৳{{ number_format($order->discount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong class="text-primary fs-5">৳{{ number_format($order->total, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Customer & Payment Info -->
    <div class="col-lg-4">
        @if(auth()->user()->hasPermission('orders.show-customer-info'))
        <!-- Customer Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Customer</h5>
            </div>
            <div class="card-body">
                @if(auth()->user()->hasPermission('orders.view-customer'))
                @if($order->user)
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="bi bi-person-fill text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $order->user->name }}</h6>
                        <small class="text-muted">{{ $order->user->email }}</small>
                    </div>
                </div>
                <a href="{{ route('admin.customers.show', $order->user->id) }}" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-eye me-1"></i> View Customer Profile
                </a>
                @else
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-secondary bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="bi bi-person text-secondary"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $order->billing_full_name }}</h6>
                        <small class="text-muted">Manual Order</small>
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>
        @endif
        
        @if(auth()->user()->hasPermission('orders.show-billing-address'))
        @if(auth()->user()->hasPermission('orders.view-customer'))
        <!-- Billing Address -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-file-earmark-post me-2"></i>Billing Address</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ $order->billing_full_name }}</strong></p>
                <p class="mb-1"><i class="bi bi-envelope me-2 text-muted"></i>{{ $order->billing_email }}</p>
                @if($order->billing_phone)
                <p class="mb-1"><i class="bi bi-phone me-2 text-muted"></i>{{ $order->billing_phone }}</p>
                @endif
                <p class="mb-0 mt-2">
                    <i class="bi bi-geo-alt me-2 text-muted"></i>
                    {{ $order->billing_address }}<br>
                    {{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_postcode }}<br>
                    {{ $order->billing_country }}
                </p>
            </div>
        </div>
        @endif
        @endif

        @if(auth()->user()->hasPermission('orders.show-shipping-address'))
        @if(auth()->user()->hasPermission('orders.view-customer'))
        <!-- Shipping Address -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Shipping Address</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ $order->shipping_full_name }}</strong></p>
                @if($order->shipping_email)
                <p class="mb-1"><i class="bi bi-envelope me-2 text-muted"></i>{{ $order->shipping_email }}</p>
                @endif
                @if($order->shipping_phone)
                <p class="mb-1"><i class="bi bi-phone me-2 text-muted"></i>{{ $order->shipping_phone }}</p>
                @endif
                <p class="mb-0 mt-2">
                    <i class="bi bi-geo-alt me-2 text-muted"></i>
                    {{ $order->shipping_address ?? $order->billing_address }}<br>
                    {{ $order->shipping_city ?? $order->billing_city }}, {{ $order->shipping_state ?? $order->billing_state }} {{ $order->shipping_postcode ?? $order->billing_postcode }}<br>
                    {{ $order->shipping_country ?? $order->billing_country }}
                </p>
            </div>
        </div>
        @endif
        @endif
        
        @if(auth()->user()->hasPermission('orders.inhouse-change-warehouse'))
        <!-- Warehouse Assignment -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Warehouse</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.orders.in-house.change-warehouse', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-2">
                        <select name="warehouse_id" class="form-select form-select-sm">
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $order->warehouse_id == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} @if($wh->is_primary)(Primary)@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-arrow-repeat me-1"></i> Change Warehouse
                    </button>
                </form>
            </div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('orders.show-payment-details'))
        <!-- Payment Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Details</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Payment Method:</span>
                    <span class="fw-semibold">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Payment Status:</span>
                    <span class="badge {{ $order->payment_status_badge_class }}">{{ ucfirst($order->payment_status) }}</span>
                </div>
                @if($order->transaction_id)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Transaction ID:</span>
                    <span class="fw-semibold small">{{ $order->transaction_id }}</span>
                </div>
                @endif
                @if($order->payment_gateway)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Gateway:</span>
                    <span class="fw-semibold">{{ ucfirst($order->payment_gateway) }}</span>
                </div>
                @endif
                @if($order->notes)
                <hr>
                <div>
                    <span class="text-muted">Order Notes:</span>
                    <p class="mb-0 mt-1 small">{{ $order->notes }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
        
        @if(auth()->user()->hasPermission('orders.show-timeline'))
        <!-- Order Timeline -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Order Timeline</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Order Created:</span>
                    <span class="small">{{ $order->created_at->format('d M, Y H:i') }}</span>
                </div>
                @if($order->updated_at != $order->created_at)
                <div class="d-flex justify-content-between mb-0">
                    <span class="text-muted">Last Updated:</span>
                    <span class="small">{{ $order->updated_at->format('d M, Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Add Product Modal (Multi-Product) -->
@if(auth()->user()->hasPermission('orders.inhouse-add-product') || auth()->user()->hasPermission('orders.inhouse-edit-item') || auth()->user()->hasPermission('orders.inhouse-remove-item'))
<div class="modal fade" id="addProductModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Products to Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Search Product</label>
                    <input type="text" id="addProductSearch" class="form-control" placeholder="Type product name or SKU..." autocomplete="off">
                    <div id="addSearchResults" class="list-group mt-2" style="max-height: 250px; overflow-y: auto; display: none;"></div>
                </div>
                <div id="pendingItemsContainer" style="display:none;">
                    <hr>
                    <label class="form-label fw-semibold mb-2">Products to Add</label>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" id="pendingItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th style="width:100px;">Qty</th>
                                    <th style="width:120px;">Price</th>
                                    <th style="width:100px;">Total</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="pendingItemsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addProductsSubmit" disabled>
                    <i class="bi bi-plus-lg me-1"></i> Add All to Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Item Modal (Full Product Edit) -->
<div class="modal fade" id="editItemModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Order Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <img id="editItemImage" src="" alt="" class="img-fluid rounded border" style="max-height:180px;object-fit:contain;">
                    </div>
                    <div class="col-md-8">
                        <h5 id="editItemName" class="mb-1"></h5>
                        <small class="text-muted" id="editItemSku"></small>
                        <div class="mt-2">
                            <span class="badge bg-secondary" id="editItemStock"></span>
                        </div>
                    </div>
                </div>
                <hr>
                <div id="editColorsSection" style="display:none;">
                    <label class="form-label fw-semibold">Color</label>
                    <div class="d-flex flex-wrap gap-2 mb-3" id="editColorOptions"></div>
                </div>
                <div id="editAttributesSection"></div>
                <div class="row mt-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Quantity</label>
                        <input type="number" id="editQty" class="form-control" min="1">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Unit Price</label>
                        <input type="number" id="editPrice" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="col-md-4 mb-3 d-flex flex-column justify-content-end">
                        <label class="form-label fw-semibold">Line Total</label>
                        <div class="fs-4 fw-bold text-primary" id="editLineTotal">৳0.00</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="editSaveBtn">
                    <i class="bi bi-check-lg me-1"></i> Update Item
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    // ===== Multi-Product Add Modal =====
    const addSearch = document.getElementById('addProductSearch');
    const addResults = document.getElementById('addSearchResults');
    const pendingBody = document.getElementById('pendingItemsBody');
    const pendingContainer = document.getElementById('pendingItemsContainer');
    const addSubmitBtn = document.getElementById('addProductsSubmit');
    let pendingItems = [];
    let searchTimeout;

    function renderPendingItems() {
        pendingBody.innerHTML = '';
        if (pendingItems.length === 0) {
            pendingContainer.style.display = 'none';
            addSubmitBtn.disabled = true;
            return;
        }
        pendingContainer.style.display = 'block';
        addSubmitBtn.disabled = false;
        let grandTotal = 0;
        pendingItems.forEach((item, idx) => {
            const total = item.qty * item.price;
            grandTotal += total;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><small>${item.name}</small></td>
                <td><input type="number" class="form-control form-control-sm pending-qty" value="${item.qty}" min="1" data-idx="${idx}" style="width:80px;"></td>
                <td><input type="number" class="form-control form-control-sm pending-price" value="${item.price}" step="0.01" min="0" data-idx="${idx}" style="width:100px;"></td>
                <td class="text-end align-middle"><strong>৳${total.toFixed(2)}</strong></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger pending-remove" data-idx="${idx}"><i class="bi bi-x"></i></button></td>`;
            pendingBody.appendChild(tr);
        });
        const tr = document.createElement('tr');
        tr.className = 'table-light fw-bold';
        tr.innerHTML = '<td colspan="3" class="text-end">Grand Total:</td><td class="text-end">৳' + grandTotal.toFixed(2) + '</td><td></td>';
        pendingBody.appendChild(tr);
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('pending-qty')) {
            const idx = parseInt(e.target.dataset.idx);
            pendingItems[idx].qty = Math.max(1, parseInt(e.target.value) || 1);
            renderPendingItems();
        }
        if (e.target.classList.contains('pending-price')) {
            const idx = parseInt(e.target.dataset.idx);
            pendingItems[idx].price = Math.max(0, parseFloat(e.target.value) || 0);
            renderPendingItems();
        }
    });
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pending-remove')) {
            const btn = e.target.closest('.pending-remove');
            const idx = parseInt(btn.dataset.idx);
            pendingItems.splice(idx, 1);
            renderPendingItems();
        }
    });

    if (addSearch) {
        addSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            if (q.length < 2) { addResults.style.display = 'none'; return; }
            searchTimeout = setTimeout(() => {
                fetch('{{ route('admin.orders.search-products') }}?q=' + encodeURIComponent(q))
                    .then(r => r.json()).then(data => {
                        addResults.innerHTML = '';
                        if (data.success && data.products.length > 0) {
                            data.products.forEach(p => {
                                const a = document.createElement('a');
                                a.href = '#';
                                a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                                a.innerHTML = '<span>' + p.name + ' <small class="text-muted">' + (p.sku ? '(' + p.sku + ')' : '') + '</small></span><span class="badge bg-secondary">Stock:' + p.stock + ' | ৳' + p.price + '</span>';
                                a.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    pendingItems.push({ product_id: p.id, name: p.name, qty: 1, price: p.price, sku: p.sku });
                                    renderPendingItems();
                                    addSearch.value = '';
                                    addResults.style.display = 'none';
                                    addSearch.focus();
                                });
                                addResults.appendChild(a);
                            });
                            addResults.style.display = 'block';
                        } else {
                            addResults.innerHTML = '<div class="list-group-item text-muted">No products found</div>';
                            addResults.style.display = 'block';
                        }
                    });
            }, 300);
        });
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#addProductSearch') && !e.target.closest('#addSearchResults')) {
                addResults.style.display = 'none';
            }
        });
    }

    if (addSubmitBtn) {
        addSubmitBtn.addEventListener('click', function() {
            if (pendingItems.length === 0) return;
            const items = pendingItems.map(function(p) { return { product_id: p.product_id, quantity: p.qty, price: p.price }; });
            addSubmitBtn.disabled = true;
            addSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
            fetch('{{ route('admin.orders.in-house.add-items', $order->id) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ items: items })
            })
            .then(function(r) { return r.json(); }).then(function(data) {
                if (data.success) { location.reload(); }
                else { alert('Error: ' + (data.message || 'Unknown')); addSubmitBtn.disabled = false; addSubmitBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Add All to Order'; }
            })
            .catch(function() { alert('Network error'); addSubmitBtn.disabled = false; addSubmitBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Add All to Order'; });
        });
    }

    // ===== Full Product Edit Modal =====
    let editProductData = null;
    let editItemId = null;
    let selectedColorId = null;
    let selectedAttrValues = {};
    const editModal = document.getElementById('editItemModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            editItemId = btn.getAttribute('data-item-id');
            const productId = btn.getAttribute('data-product-id');
            const itemName = btn.getAttribute('data-item-name');
            const itemQty = parseInt(btn.getAttribute('data-item-qty'));
            const itemPrice = parseFloat(btn.getAttribute('data-item-price'));

            document.getElementById('editItemName').textContent = itemName;
            document.getElementById('editQty').value = itemQty;
            document.getElementById('editPrice').value = itemPrice;
            updateEditLineTotal();

            if (productId) {
                fetch('{{ route('admin.orders.product-detail', 'PID') }}'.replace('PID', productId))
                    .then(function(r) { return r.json(); }).then(function(data) {
                        if (data.success) {
                            editProductData = data.product;
                            renderEditColors(data.product.colors);
                            renderEditAttributes(data.product.attributes);
                            const img = document.getElementById('editItemImage');
                            img.src = data.product.image || '';
                            document.getElementById('editItemSku').textContent = data.product.sku ? 'SKU: ' + data.product.sku : '';
                            document.getElementById('editItemStock').textContent = 'Stock: ' + data.product.stock;
                            if (!document.getElementById('editPrice').value || document.getElementById('editPrice').value == '0') {
                                document.getElementById('editPrice').value = data.product.price;
                                updateEditLineTotal();
                            }
                        }
                    });
            }
        });
    }

    function renderEditColors(colors) {
        const section = document.getElementById('editColorsSection');
        const container = document.getElementById('editColorOptions');
        container.innerHTML = '';
        if (!colors || colors.length === 0) { section.style.display = 'none'; return; }
        section.style.display = 'block';
        selectedColorId = null;
        colors.forEach(function(c) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm rounded-circle border-2';
            btn.style.cssText = 'width:36px;height:36px;background:' + c.hex_code + ';';
            btn.title = c.name + (c.price_adjustment ? ' (৳' + c.price_adjustment + ')' : '');
            btn.dataset.id = c.id;
            btn.dataset.adj = c.price_adjustment;
            btn.dataset.name = c.name;
            btn.addEventListener('click', function() {
                document.querySelectorAll('#editColorOptions button').forEach(function(b) { b.classList.remove('ring-2', 'ring-primary', 'ring-offset-1'); });
                this.classList.add('ring-2', 'ring-primary', 'ring-offset-1');
                selectedColorId = parseInt(this.dataset.id);
                updateEditLineTotal();
            });
            container.appendChild(btn);
        });
    }

    function renderEditAttributes(attrs) {
        const section = document.getElementById('editAttributesSection');
        section.innerHTML = '';
        if (!attrs || attrs.length === 0) return;
        selectedAttrValues = {};
        attrs.forEach(function(attr) {
            const div = document.createElement('div');
            div.className = 'mb-3';
            div.innerHTML = '<label class="form-label fw-semibold">' + attr.name + '</label>';
            const btnGroup = document.createElement('div');
            btnGroup.className = 'd-flex flex-wrap gap-2';
            attr.values.forEach(function(v) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-outline-secondary attr-btn';
                btn.textContent = v.value + (v.price ? ' (+৳' + v.price + ')' : '');
                btn.dataset.attrName = attr.name;
                btn.dataset.valueId = v.id;
                btn.dataset.price = v.price;
                btn.addEventListener('click', function() {
                    btnGroup.querySelectorAll('.attr-btn').forEach(function(b) { b.classList.remove('btn-primary', 'btn-outline-primary'); });
                    this.classList.remove('btn-outline-secondary');
                    this.classList.add('btn-primary');
                    selectedAttrValues[attr.name] = { id: parseInt(this.dataset.valueId), price: parseFloat(this.dataset.price) };
                    updateEditLineTotal();
                });
                btnGroup.appendChild(btn);
            });
            div.appendChild(btnGroup);
            section.appendChild(div);
        });
    }

    function updateEditLineTotal() {
        const qty = parseInt(document.getElementById('editQty').value) || 1;
        const price = parseFloat(document.getElementById('editPrice').value) || 0;
        document.getElementById('editLineTotal').textContent = '৳' + (qty * price).toFixed(2);
    }

    document.getElementById('editQty')?.addEventListener('input', updateEditLineTotal);
    document.getElementById('editPrice')?.addEventListener('input', updateEditLineTotal);

    document.getElementById('editSaveBtn')?.addEventListener('click', function() {
        if (!editItemId) return;
        const qty = parseInt(document.getElementById('editQty').value) || 1;
        const price = parseFloat(document.getElementById('editPrice').value) || 0;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
        fetch('{{ route('admin.orders.in-house.update-item', [$order->id, 'ITEM_ID']) }}'.replace('ITEM_ID', editItemId), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ quantity: qty, price: price })
        })
        .then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) { location.reload(); }
            else { alert('Error: ' + (data.message || 'Failed to update')); btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg"></i> Update Item'; }
        })
        .catch(function() { alert('Network error'); btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg"></i> Update Item'; });
    });
</script>
@endpush
@endsection
