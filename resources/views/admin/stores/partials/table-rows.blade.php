@forelse($stores as $store)
<tr>
    <td>{{ $loop->iteration + ($stores->currentPage() - 1) * $stores->perPage() }}</td>
    <td>
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                @if($store->logo_url)
                    <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                @else
                    <div class="bg-light rounded p-2 text-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-shop"></i>
                    </div>
                @endif
            </div>
            <div class="ms-3">
                <div class="fw-semibold">
                    {{ $store->name }}
                    @if($store->is_default)
                        <span class="badge bg-warning text-dark ms-1">Default</span>
                    @endif
                </div>
                @if($store->code)
                    <small class="text-muted">Code: {{ $store->code }}</small>
                @endif
            </div>
        </div>
    </td>
    <td>
        <div>{{ $store->address }}</div>
        @if($store->city || $store->state || $store->country)
            <small class="text-muted">
                {{ implode(', ', array_filter([$store->city, $store->state, $store->country])) }}
            </small>
        @endif
    </td>
    <td>
        @if($store->phone)
            <div><i class="bi bi-phone me-1"></i>{{ $store->phone }}</div>
        @endif
        @if($store->email)
            <small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $store->email }}</small>
        @endif
    </td>
    <td>
        <span class="badge {{ $store->status_badge_class }}">
            {{ $store->status_text }}
        </span>
        @if($store->is_physical)
            <span class="badge bg-info ms-1">Physical</span>
        @else
            <span class="badge bg-secondary ms-1">Online</span>
        @endif
    </td>
    <td>
        <span class="badge bg-primary" title="Products">
            <i class="bi bi-box me-1"></i>{{ $store->products_count }}
        </span>
    </td>
    <td>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.multi-store.show', $store->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.multi-store.edit', $store->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            @if($store->is_default)
                <button type="button" class="btn btn-sm btn-outline-secondary" title="Cannot delete default store" disabled>
                    <i class="bi bi-trash"></i>
                </button>
            @elseif($store->products_count > 0)
                <button type="button" class="btn btn-sm btn-outline-secondary" title="Cannot delete store with products" disabled>
                    <i class="bi bi-trash"></i>
                </button>
            @else
                <form action="{{ route('admin.multi-store.destroy', $store->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this store?')">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @endif
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-5">
        <i class="bi bi-shop text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No stores found</p>
        <a href="{{ route('admin.multi-store.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First Store
        </a>
    </td>
</tr>
@endforelse
