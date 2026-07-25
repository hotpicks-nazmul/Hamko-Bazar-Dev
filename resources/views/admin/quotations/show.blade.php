@extends('admin.layouts.app')

@section('title', 'Quotation ' . $quotation->quotation_number)

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Quotation {{ $quotation->quotation_number }}</h4>
            <p class="text-muted mb-0">
                Created {{ $quotation->created_at->format('M d, Y h:i A') }}
                @if($quotation->is_expired && $quotation->status !== 'converted')
                    <span class="badge bg-secondary ms-2">Expired</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('admin.quotations.print', $quotation) }}" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-printer me-1"></i> Print
            </a>
            @if($quotation->can_edit)
            <a href="{{ route('admin.quotations.edit', $quotation) }}" class="btn btn-outline-info">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            @endif
            @if($quotation->can_convert)
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#convertModal">
                <i class="bi bi-cart-check me-1"></i> Convert to Order
            </button>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Quotation Details Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Quotation Details</h6>
                    <span class="badge {{ $quotation->status_badge_class }} fs-6">{{ ucfirst($quotation->status) }}</span>
                </div>
                @if(auth()->user()->hasPermission('quotations.customer-info'))
@if(auth()->user()->hasPermission('quotations.notes'))
<div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Customer Information</h6>
                            <p class="mb-1 fw-medium">{{ $quotation->customer_name }}</p>
                            @if($quotation->customer_email)
                            <p class="mb-1 text-muted"><i class="bi bi-envelope me-1"></i> {{ $quotation->customer_email }}</p>
                            @endif
                            @if($quotation->customer_phone)
                            <p class="mb-1 text-muted"><i class="bi bi-telephone me-1"></i> {{ $quotation->customer_phone }}</p>
                            @endif
                            @if($quotation->customer_address)
                            <p class="mb-0 text-muted"><i class="bi bi-geo-alt me-1"></i> {{ $quotation->customer_full_address }}</p>
                            @endif
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-muted mb-2">Quotation Info</h6>
                            <p class="mb-1"><strong>Quotation #:</strong> {{ $quotation->quotation_number }}</p>
                            <p class="mb-1"><strong>Created:</strong> {{ $quotation->created_at->format('M d, Y') }}</p>
                            <p class="mb-1"><strong>Valid Until:</strong> 
                                <span class="{{ $quotation->is_expired ? 'text-danger' : '' }}">
                                    {{ $quotation->valid_until->format('M d, Y') }}
                                </span>
                            </p>
                            @if($quotation->user)
                            <p class="mb-0 text-muted"><strong>Customer Account:</strong> {{ $quotation->user->name }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Item</th>
                                    <th class="text-center" style="width: 100px;">Qty</th>
                                    <th class="text-end" style="width: 150px;">Unit Price</th>
                                    <th class="text-end" style="width: 150px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotation->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-medium">{{ $item->product_name }}</div>
                                        @if($item->description)
                                        <small class="text-muted">{{ $item->description }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Subtotal</strong></td>
                                    <td class="text-end">{{ number_format($quotation->subtotal, 2) }}</td>
                                </tr>
                                @if($quotation->tax > 0)
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Tax</strong></td>
                                    <td class="text-end">{{ number_format($quotation->tax, 2) }}</td>
                                </tr>
                                @endif
                                @if($quotation->discount > 0)
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Discount</strong></td>
                                    <td class="text-end">{{ number_format($quotation->discount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td colspan="4" class="text-end"><strong class="fs-5">Total</strong></td>
                                    <td class="text-end"><strong class="fs-5">{{ number_format($quotation->total, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($quotation->notes)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Notes</h6>
                        <p class="mb-0">{{ $quotation->notes }}</p>
                    </div>
                    @endif

                    @if($quotation->terms_conditions)
                    <div class="mb-0">
                        <h6 class="text-muted mb-2">Terms & Conditions</h6>
                        <p class="mb-0">{{ $quotation->terms_conditions }}</p>
                    </div>
                    @endif
                </div>
@endif
@endif
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status Actions -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h6>
                </div>
                @if(auth()->user()->hasPermission('quotations.timeline'))
<div class="card-body">
                    @if($quotation->status === 'pending')
                    <form action="{{ route('admin.quotations.send', $quotation) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-send me-1"></i> Mark as Sent
                        </button>
                    </form>
                    @endif

                    @if(in_array($quotation->status, ['pending', 'sent']))
                    <form action="{{ route('admin.quotations.status', $quotation) }}" method="POST" class="mb-2">
                        @csrf
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i> Mark as Accepted
                        </button>
                    </form>

                    <form action="{{ route('admin.quotations.status', $quotation) }}" method="POST" class="mb-2">
                        @csrf
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-x-circle me-1"></i> Mark as Rejected
                        </button>
                    </form>
                    @endif

                    @if($quotation->can_convert)
                    <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#convertModal">
                        <i class="bi bi-cart-check me-1"></i> Convert to Order
                    </button>
                    @endif

                    @if($quotation->converted_order_id)
                    <div class="alert alert-success mb-0">
                        <h6 class="alert-heading"><i class="bi bi-check-circle me-1"></i>Converted to Order</h6>
                        <p class="mb-1">Order: <a href="{{ route('admin.orders.in-house.show', $quotation->converted_order_id) }}" class="alert-link">{{ $quotation->convertedOrder?->order_number ?? '#' . $quotation->converted_order_id }}</a></p>
                        <p class="mb-0 small">By {{ $quotation->converted_by }} on {{ $quotation->converted_at?->format('M d, Y') }}</p>
                    </div>
                    @endif
                </div>
@endif
            </div>

            <!-- Timeline -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Timeline</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-secondary rounded-circle p-2">
                                        <i class="bi bi-plus-lg"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">Quotation Created</h6>
                                    <small class="text-muted">{{ $quotation->created_at->format('M d, Y h:i A') }}</small>
                                </div>
                            </div>
                        </li>
                        @if($quotation->sent_at)
                        <li class="mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-info rounded-circle p-2">
                                        <i class="bi bi-send"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">Quotation Sent</h6>
                                    <small class="text-muted">{{ $quotation->sent_at->format('M d, Y h:i A') }}</small>
                                </div>
                            </div>
                        </li>
                        @endif
                        @if($quotation->accepted_at)
                        <li class="mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-success rounded-circle p-2">
                                        <i class="bi bi-check-lg"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">Quotation Accepted</h6>
                                    <small class="text-muted">{{ $quotation->accepted_at->format('M d, Y h:i A') }}</small>
                                </div>
                            </div>
                        </li>
                        @endif
                        @if($quotation->rejected_at)
                        <li class="mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-danger rounded-circle p-2">
                                        <i class="bi bi-x-lg"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">Quotation Rejected</h6>
                                    <small class="text-muted">{{ $quotation->rejected_at->format('M d, Y h:i A') }}</small>
                                </div>
                            </div>
                        </li>
                        @endif
                        @if($quotation->converted_at)
                        <li class="mb-0">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-primary rounded-circle p-2">
                                        <i class="bi bi-cart-check"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">Converted to Order</h6>
                                    <small class="text-muted">{{ $quotation->converted_at->format('M d, Y h:i A') }}</small>
                                </div>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Delete -->
            @if($quotation->status !== 'converted')
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.quotations.destroy', $quotation) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this quotation?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete Quotation
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

<!-- Convert to Order Modal -->
@if($quotation->can_convert)
<div class="modal fade" id="convertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Convert to Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.quotations.convert-to-order', $quotation) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to convert this quotation to an order?</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="manual">Manual</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Order Status</label>
                        <select name="order_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="confirmed">Confirmed</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        This will create an in-house order and reduce product stock.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Convert to Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
