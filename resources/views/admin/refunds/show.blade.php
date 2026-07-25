@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Refund Details</h4>
    @php
        $previousUrl = url()->previous();
        $refundUrls = [
            route('admin.refunds.index'),
            route('admin.refunds.requests'),
            route('admin.refunds.approved'),
            route('admin.refunds.rejected'),
            route('admin.refunds.configuration'),
        ];
        $backUrl = in_array($previousUrl, $refundUrls) ? $previousUrl : route('admin.refunds.index');
    @endphp
    <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Refund Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Refund Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Refund Number</label>
                        <div class="fw-medium">{{ $refund->refund_number }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Status</label>
                        <div>
                            <span class="badge {{ $refund->status_badge_class }}">
                                {{ ucfirst($refund->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Refund Amount</label>
                        <div class="fw-medium text-success h5">${{ number_format($refund->refund_amount, 2) }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Request Date</label>
                        <div>{{ $refund->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Information -->
        @if($refund->order)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bag me-2"></i>Order Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Order Number</label>
                        <div>
                            <a href="{{ route('admin.orders.show', $refund->order->id) }}" class="text-decoration-none">
                                {{ $refund->order->order_number }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Order Total</label>
                        <div class="fw-medium">${{ number_format($refund->order->total, 2) }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Order Status</label>
                        <div>
                            <span class="badge {{ $refund->order->status_badge_class }}">
                                {{ ucfirst($refund->order->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Payment Status</label>
                        <div>
                            <span class="badge {{ $refund->order->payment_status_badge_class }}">
                                {{ ucfirst($refund->order->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                @if($refund->order->items->count() > 0)
                <div class="mt-3">
                    <label class="form-label text-muted small">Order Items</label>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($refund->order->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $item->product_name }}</div>
                                        @if($item->variation)
                                        <small class="text-muted">
                                            @foreach($item->variation as $key => $value)
                                                {{ $key }}: {{ $value }} 
                                            @endforeach
                                        </small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">${{ number_format($item->price, 2) }}</td>
                                    <td class="text-end">${{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Refund Reason -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>Refund Reason</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Reason</label>
                        <div>
                            <span class="badge bg-light text-dark">{{ $refund->reason_label }}</span>
                        </div>
                    </div>
                    @if($refund->reason_details)
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted small">Additional Details</label>
                        <div>{{ $refund->reason_details }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Admin Notes -->
        @if($refund->admin_note)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-sticky me-2"></i>Admin Notes</h6>
            </div>
            <div class="card-body">
                <div>{{ $refund->admin_note }}</div>
                @if($refund->processed_by)
                <div class="mt-2 text-muted small">
                    Processed by: {{ $refund->processedBy->name ?? 'Admin' }}
                    @if($refund->processed_at)
                    on {{ $refund->processed_at->format('M d, Y h:i A') }}
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Customer Information -->
        @if(auth()->user()->hasPermission('refund.view-customer'))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h6>
            </div>
            <div class="card-body">
                @if($refund->user)
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 48px; height: 48px;">
                        <span class="text-white fw-medium">{{ substr($refund->user->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <div class="fw-medium">{{ $refund->user->name }}</div>
                        <small class="text-muted">{{ $refund->user->email }}</small>
                    </div>
                </div>
                @if($refund->user->phone)
                <div class="mb-2">
                    <label class="form-label text-muted small">Phone</label>
                    <div>{{ $refund->user->phone }}</div>
                </div>
                @endif
                @else
                <div class="text-muted">Customer information not available</div>
                @endif
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
                @if($refund->status === 'pending')
                <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="bi bi-check-lg me-1"></i> Approve Refund
                </button>
                <button type="button" class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-lg me-1"></i> Reject Refund
                </button>
                @elseif($refund->status === 'approved')
                <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#processModal">
                    <i class="bi bi-cash me-1"></i> Process Refund
                </button>
                @else
                <div class="text-muted text-center">
                    <i class="bi bi-check-circle text-success"></i> Refund {{ $refund->status }}
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Refunds</span>
                    <span class="fw-medium">{{ \App\Models\Refund::count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Pending</span>
                    <span class="fw-medium text-warning">{{ \App\Models\Refund::pending()->count() }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Processed</span>
                    <span class="fw-medium text-success">{{ \App\Models\Refund::processed()->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
@if($refund->status === 'pending')
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.refunds.approve', $refund->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Refund</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this refund request?</p>
                    <div class="bg-light p-3 rounded mb-3">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Refund #</small>
                                <div class="fw-medium">{{ $refund->refund_number }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Amount</small>
                                <div class="fw-medium text-success">${{ number_format($refund->refund_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="admin_note" class="form-label">Note (Optional)</label>
                        <textarea name="admin_note" id="admin_note" class="form-control" rows="2" 
                                  placeholder="Add a note about this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Approve Refund
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.refunds.reject', $refund->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Refund</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject this refund request?</p>
                    <div class="bg-light p-3 rounded mb-3">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Refund #</small>
                                <div class="fw-medium">{{ $refund->refund_number }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Amount</small>
                                <div class="fw-medium text-danger">${{ number_format($refund->refund_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="admin_note" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea name="admin_note" id="admin_note" class="form-control" rows="3" 
                                  placeholder="Explain why this refund is being rejected..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg me-1"></i> Reject Refund
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Process Modal -->
@if($refund->status === 'approved')
<div class="modal fade" id="processModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.refunds.process', $refund->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Refund</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Mark this refund as processed (completed).</p>
                    <div class="bg-light p-3 rounded mb-3">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Refund #</small>
                                <div class="fw-medium">{{ $refund->refund_number }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Amount</small>
                                <div class="fw-medium text-success">${{ number_format($refund->refund_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="admin_note" class="form-label">Additional Note (Optional)</label>
                        <textarea name="admin_note" id="admin_note" class="form-control" rows="2" 
                                  placeholder="Add any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Mark as Processed
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
