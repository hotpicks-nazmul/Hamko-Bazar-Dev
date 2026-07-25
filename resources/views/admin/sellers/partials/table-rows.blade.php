@forelse($sellers as $seller)
@php
    $search = request('search');
    $isMatch = $search && (
        stripos($seller->name, $search) !== false ||
        stripos($seller->email, $search) !== false ||
        stripos($seller->shop_name, $search) !== false
    );
@endphp
<tr class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input seller-checkbox" value="{{ $seller->id }}">
    </td>
    <td>
        <div class="d-flex align-items-center">
            @if($seller->shop_logo)
                @php
                    $logoUrl = $seller->shop_logo;
                    if($logoUrl && !str_starts_with($logoUrl, '/storage/') && !str_starts_with($logoUrl, 'http')) {
                        $logoUrl = '/storage/uploads/shop_logos/' . $logoUrl;
                    }
                @endphp
                @if(file_exists(public_path('uploads/shop_logos/' . $seller->shop_logo)))
                    <img src="{{ asset('uploads/shop_logos/' . $seller->shop_logo) }}" alt="{{ $seller->shop_name ?? $seller->name }}" class="shop-logo-thumb me-2">
                @else
                    <img src="{{ $logoUrl }}" alt="{{ $seller->shop_name ?? $seller->name }}" class="shop-logo-thumb me-2">
                @endif
            @elseif($seller->avatar)
                @php
                    $avatarUrl = $seller->avatar;
                    if($avatarUrl && !str_starts_with($avatarUrl, '/storage/') && !str_starts_with($avatarUrl, 'http')) {
                        $avatarUrl = '/storage/uploads/avatars/' . $avatarUrl;
                    }
                @endphp
                @if(file_exists(public_path('uploads/avatars/' . $seller->avatar)))
                    <img src="{{ asset('uploads/avatars/' . $seller->avatar) }}" alt="{{ $seller->name }}" class="seller-avatar me-2">
                @else
                    <img src="{{ $avatarUrl }}" alt="{{ $seller->name }}" class="seller-avatar me-2">
                @endif
            @else
                <div class="seller-avatar me-2 bg-light d-flex align-items-center justify-content-center">
                    <i class="bi bi-person text-muted"></i>
                </div>
            @endif
            <div>
                <div class="fw-medium">{{ $seller->name }}</div>
                <div class="small text-muted">{{ $seller->email }}</div>
                @if($seller->phone)
                    <div class="small text-muted">{{ $seller->phone }}</div>
                @endif
            </div>
        </div>
    </td>
    <td>
        @if($seller->shop_name)
            <div class="fw-medium">{{ $seller->shop_name }}</div>
            <div class="small text-muted">
                <span class="badge bg-{{ $seller->seller_type === 'company' ? 'primary' : 'secondary' }}">
                    {{ ucfirst($seller->seller_type) }}
                </span>
            </div>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        <span class="badge bg-info">{{ $seller->products_count ?? 0 }}</span>
    </td>
    <td>
        <div class="small">
            <div>Wallet: <span class="text-success">৳{{ number_format($seller->wallet_balance ?? 0, 2) }}</span></div>
            <div>Pending: <span class="text-warning">৳{{ number_format($seller->pending_balance ?? 0, 2) }}</span></div>
        </div>
    </td>
    <td>
        @if($seller->status === 'active')
            <span class="badge bg-success">Active</span>
        @else
            <span class="badge bg-secondary">Inactive</span>
        @endif
    </td>
    <td>
        @if($seller->verification_status === 'verified')
            <span class="badge bg-success">
                <i class="bi bi-check-circle me-1"></i>Verified
            </span>
        @elseif($seller->verification_status === 'pending')
            <span class="badge bg-warning text-dark">
                <i class="bi bi-clock me-1"></i>Pending
            </span>
        @else
            <span class="badge bg-danger">
                <i class="bi bi-x-circle me-1"></i>Rejected
            </span>
        @endif
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.sellers.edit', $seller->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            @if($seller->status === 'active')
                <form action="{{ route('admin.sellers.suspend', $seller->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Suspend" onclick="return confirm('Are you sure you want to suspend this seller?')">
                        <i class="bi bi-pause-circle"></i>
                    </button>
                </form>
            @else
                <form action="{{ route('admin.sellers.activate', $seller->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success" title="Activate">
                        <i class="bi bi-play-circle"></i>
                    </button>
                </form>
            @endif
            <form action="{{ route('admin.sellers.destroy', $seller->id) }}" method="POST" class="d-inline" id="deleteForm{{ $seller->id }}">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="confirmDelete('deleteForm{{ $seller->id }}')">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <i class="bi bi-shop text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No sellers found</p>
        <a href="{{ route('admin.sellers.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First Seller
        </a>
    </td>
</tr>
@endforelse
