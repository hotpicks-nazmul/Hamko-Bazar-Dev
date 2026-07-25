@forelse($sellers as $seller)
<tr>
    <td>
        <div class="d-flex align-items-center">
            @php
                $avatarUrl = $seller->shop_logo;
                if($avatarUrl && !str_starts_with($avatarUrl, '/storage/') && !str_starts_with($avatarUrl, 'http')) {
                    $avatarUrl = '/storage/' . $avatarUrl;
                }
            @endphp
            @if($avatarUrl)
            <img src="{{ $avatarUrl }}" alt="{{ $seller->name }}" class="shop-logo-thumb me-2">
            @else
            <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                <i class="bi bi-shop text-white"></i>
            </div>
            @endif
            <div>
                <div class="fw-medium">{{ $seller->name }}</div>
                <div class="small text-muted">{{ $seller->email }}</div>
                <div class="small text-muted">{{ $seller->phone ?? 'No phone' }}</div>
            </div>
        </div>
    </td>
    <td>
        <div class="fw-medium">{{ $seller->shop_name ?? 'No shop name' }}</div>
        @if($seller->company_name)
        <div class="small text-muted">{{ $seller->company_name }}</div>
        @endif
    </td>
    <td>
        @if($seller->seller_type === 'company')
        <span class="badge bg-primary">Company</span>
        @else
        <span class="badge bg-info text-dark">Individual</span>
        @endif
    </td>
    <td>
        @if($seller->verification_status === 'verified')
        <span class="badge bg-success">
            <i class="bi bi-check-circle me-1"></i> Verified
        </span>
        @elseif($seller->verification_status === 'pending')
        <span class="badge bg-warning text-dark">
            <i class="bi bi-clock me-1"></i> Pending
        </span>
        @else
        <span class="badge bg-danger">
            <i class="bi bi-x-circle me-1"></i> Rejected
        </span>
        @endif
        @if($seller->verification_notes)
        <div class="small text-muted mt-1" data-bs-toggle="tooltip" title="{{ $seller->verification_notes }}">
            <i class="bi bi-info-circle"></i> Has notes
        </div>
        @endif
    </td>
    <td>
        <div class="small">{{ $seller->created_at->format('d M Y') }}</div>
        <div class="small text-muted">{{ $seller->created_at->format('h:i A') }}</div>
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                <i class="bi bi-eye"></i>
            </a>
            
            @if($seller->verification_status !== 'verified')
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $seller->id }}" title="Approve">
                <i class="bi bi-check-lg"></i>
            </button>
            @endif
            
            @if($seller->verification_status !== 'rejected')
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $seller->id }}" title="Reject">
                <i class="bi bi-x-lg"></i>
            </button>
            @endif
        </div>
    </td>
</tr>

<!-- Approve Modal -->
@if($seller->verification_status !== 'verified')
<div class="modal fade" id="approveModal{{ $seller->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Seller Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.sellers.verification.process', $seller->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to approve <strong>{{ $seller->name }}</strong> ({{ $seller->shop_name }})?</p>
                    <p class="text-muted small">This will mark the seller as verified and activate their account.</p>
                    
                    <div class="mb-3">
                        <label for="notes{{ $seller->id }}" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes{{ $seller->id }}" name="verification_notes" rows="3" placeholder="Add any notes about this approval..."></textarea>
                    </div>
                    
                    <input type="hidden" name="action" value="approve">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Approve Verification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Reject Modal -->
@if($seller->verification_status !== 'rejected')
<div class="modal fade" id="rejectModal{{ $seller->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Seller Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.sellers.verification.process', $seller->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to reject <strong>{{ $seller->name }}</strong> ({{ $seller->shop_name }})?</p>
                    
                    <div class="mb-3">
                        <label for="rejectNotes{{ $seller->id }}" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectNotes{{ $seller->id }}" name="verification_notes" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                        <div class="form-text">This will be visible to the seller.</div>
                    </div>
                    
                    <input type="hidden" name="action" value="reject">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg me-1"></i> Reject Verification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@empty
<tr>
    <td colspan="6" class="text-center py-5">
        <i class="bi bi-patch-check text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No verification requests found</p>
        <a href="{{ route('admin.sellers.index') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-arrow-left me-1"></i> Back to Sellers
        </a>
    </td>
</tr>
@endforelse
