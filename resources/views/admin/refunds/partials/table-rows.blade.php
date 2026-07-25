@forelse($refunds as $refund)
<tr>
    <td style="width: 40px;">
        @if(in_array($refund->status, ['pending', 'approved']))
        <input type="checkbox" class="form-check-input refund-checkbox" value="{{ $refund->id }}" onchange="toggleSelection('{{ $refund->id }}', this)">
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        <div class="fw-medium">{{ $refund->refund_number }}</div>
    </td>
    <td>
        @if($refund->order)
        <a href="{{ route('admin.orders.show', $refund->order->id) }}" class="text-decoration-none">
            {{ $refund->order->order_number }}
        </a>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    @if(auth()->user()->hasPermission('refund.view-customer'))
    <td>
        @if($refund->user)
        <div>{{ $refund->user->name }}</div>
        <small class="text-muted">{{ $refund->user->email }}</small>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    @endif
    <td>
        <span class="badge bg-light text-dark">{{ $refund->reason_label }}</span>
    </td>
    <td class="text-end">
        <span class="fw-medium">${{ number_format($refund->refund_amount, 2) }}</span>
    </td>
    <td>
        <span class="badge {{ $refund->status_badge_class }}">
            {{ ucfirst($refund->status) }}
        </span>
    </td>
    <td>
        <small class="text-muted">{{ $refund->created_at->format('M d, Y') }}</small>
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.refunds.show', $refund->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                <i class="bi bi-eye"></i>
            </a>
            @if($refund->status === 'pending')
            <button type="button" class="btn btn-sm btn-outline-success" title="Approve"
                    data-bs-toggle="modal" data-bs-target="#approveModal{{ $refund->id }}">
                <i class="bi bi-check-lg"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" title="Reject"
                    data-bs-toggle="modal" data-bs-target="#rejectModal{{ $refund->id }}">
                <i class="bi bi-x-lg"></i>
            </button>
            @endif
            @if($refund->status === 'approved')
            <button type="button" class="btn btn-sm btn-outline-success" title="Process Refund"
                    data-bs-toggle="modal" data-bs-target="#processModal{{ $refund->id }}">
                <i class="bi bi-cash"></i>
            </button>
            @endif
        </div>

        @if($refund->status === 'pending')
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal{{ $refund->id }}" tabindex="-1" aria-hidden="true">
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
                            <div class="bg-light p-3 rounded">
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
                            <div class="mt-3">
                                <label for="approve_note_{{ $refund->id }}" class="form-label">Note (Optional)</label>
                                <textarea name="admin_note" id="approve_note_{{ $refund->id }}" class="form-control" rows="2" 
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
        <div class="modal fade" id="rejectModal{{ $refund->id }}" tabindex="-1" aria-hidden="true">
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
                            <div class="bg-light p-3 rounded">
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
                            <div class="mt-3">
                                <label for="reject_note_{{ $refund->id }}" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                <textarea name="admin_note" id="reject_note_{{ $refund->id }}" class="form-control" rows="3" 
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

        @if($refund->status === 'approved')
        <!-- Process Modal -->
        <div class="modal fade" id="processModal{{ $refund->id }}" tabindex="-1" aria-hidden="true">
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
                            <div class="bg-light p-3 rounded">
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
                            <div class="mt-3">
                                <label for="process_note_{{ $refund->id }}" class="form-label">Additional Note (Optional)</label>
                                <textarea name="admin_note" id="process_note_{{ $refund->id }}" class="form-control" rows="2" 
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
    </td>
</tr>
@empty
<tr>
    <td colspan="{{ auth()->user()->hasPermission('refund.view-customer') ? 9 : 8 }}" class="text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No refund requests found</p>
        <a href="{{ route('admin.refunds.configuration') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-gear me-1"></i> Configure Refund Settings
        </a>
    </td>
</tr>
@endforelse
