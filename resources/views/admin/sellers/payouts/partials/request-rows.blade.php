@forelse($payouts as $payout)
<tr>
    <td>
        <div class="d-flex align-items-center">
            <div class="me-2">
                @if($payout->seller->shop_logo)
                    @php
                        $logoUrl = $payout->seller->shop_logo;
                        if($logoUrl && !str_starts_with($logoUrl, '/storage/') && !str_starts_with($logoUrl, 'http')) {
                            $logoUrl = '/storage/' . $logoUrl;
                        }
                    @endphp
                    <img src="{{ $logoUrl }}" alt="{{ $payout->seller->shop_name }}" class="rounded" style="width: 36px; height: 36px; object-fit: cover;">
                @else
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-shop text-white"></i>
                    </div>
                @endif
            </div>
            <div>
                <div class="fw-medium">{{ $payout->seller->shop_name ?? $payout->seller->name }}</div>
                <small class="text-muted">{{ $payout->seller->email }}</small>
            </div>
        </div>
    </td>
    <td>
        <span class="fw-medium">৳{{ number_format($payout->amount, 2) }}</span>
    </td>
    <td>
        <span class="text-muted">৳{{ number_format($payout->commission, 2) }}</span>
    </td>
    <td>
        <span class="fw-medium text-success">৳{{ number_format($payout->net_amount, 2) }}</span>
    </td>
    <td>
        @if($payout->payment_method)
            @switch($payout->payment_method)
                @case('bank_transfer')
                    <span class="badge bg-primary"><i class="bi bi-bank me-1"></i> Bank Transfer</span>
                    @break
                @case('cash')
                    <span class="badge bg-info"><i class="bi bi-cash me-1"></i> Cash</span>
                    @break
                @case('mobile_banking')
                    <span class="badge bg-success"><i class="bi bi-phone me-1"></i> Mobile Banking</span>
                    @break
                @case('cheque')
                    <span class="badge bg-warning text-dark"><i class="bi bi-file-earmark-text me-1"></i> Cheque</span>
                    @break
                @default
                    <span class="badge bg-secondary">{{ $payout->payment_method }}</span>
            @endswitch
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        @if($payout->bank_name)
            <small>
                <div>{{ $payout->bank_name }}</div>
                <div class="text-muted">{{ $payout->account_number }}</div>
            </small>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        @switch($payout->status)
            @case('pending')
                <span class="badge bg-warning">Pending</span>
                @break
            @case('approved')
                <span class="badge bg-info">Approved</span>
                @break
            @case('completed')
                <span class="badge bg-success">Completed</span>
                @break
            @case('rejected')
                <span class="badge bg-danger">Rejected</span>
                @break
            @default
                <span class="badge bg-secondary">{{ $payout->status }}</span>
        @endswitch
    </td>
    <td>
        <div>{{ $payout->created_at->format('d M Y') }}</div>
        <small class="text-muted">{{ $payout->created_at->format('h:i A') }}</small>
    </td>
    <td class="text-center">
        <div class="btn-group">
            <a href="{{ route('admin.sellers.payouts.show', $payout->id) }}" class="btn btn-sm btn-outline-secondary" title="View Details">
                <i class="bi bi-eye"></i>
            </a>
            @if($payout->status === 'pending' || $payout->status === 'approved')
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $payout->id }}" title="Approve">
                    <i class="bi bi-check-lg"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $payout->id }}" title="Reject">
                    <i class="bi bi-x-lg"></i>
                </button>
            @endif
        </div>
        
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal{{ $payout->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Payout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('admin.sellers.payout-requests.approve', $payout->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>Approve payout of <strong>৳{{ number_format($payout->amount, 2) }}</strong> for <strong>{{ $payout->seller->shop_name ?? $payout->seller->name }}</strong>?</p>
                            
                            <div class="mb-3">
                                <label class="form-label">Transaction ID (Optional)</label>
                                <input type="text" name="transaction_id" class="form-control" placeholder="Enter transaction ID">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Admin Notes (Optional)</label>
                                <textarea name="admin_notes" class="form-control" rows="2" placeholder="Any notes..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Approve & Complete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal{{ $payout->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Payout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('admin.sellers.payout-requests.reject', $payout->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>Reject payout of <strong>৳{{ number_format($payout->amount, 2) }}</strong> for <strong>{{ $payout->seller->shop_name ?? $payout->seller->name }}</strong>?</p>
                            <p class="text-muted small">The seller's pending balance will be restored.</p>
                            
                            <div class="mb-3">
                                <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                <textarea name="admin_notes" class="form-control" rows="3" required placeholder="Enter reason for rejection..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Reject Payout</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-5">
        <i class="bi bi-cash-stack text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No payout requests found</p>
        <a href="{{ route('admin.sellers.index') }}" class="btn btn-sm btn-outline-primary mt-1">
            <i class="bi bi-arrow-left me-1"></i> Back to Sellers
        </a>
    </td>
</tr>
@endforelse
