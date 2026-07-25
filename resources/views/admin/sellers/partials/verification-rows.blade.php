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
        <div class="btn-group">
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

{{-- Modals are NOT part of the rows: a modal inside <tbody> is invalid table content and the HTML
     parser hoists it out of the cell, which breaks the button target. They live in
     admin.sellers.partials.verification-modals and are swapped in with the rows. --}}
