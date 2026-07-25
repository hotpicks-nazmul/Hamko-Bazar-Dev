<tr data-id="{{ $brand->id }}">
    <td>
        <input type="checkbox" class="form-check-input brand-checkbox" value="{{ $brand->id }}" onchange="updateBulkActions()">
    </td>
    <td>
        @if($brand->logo)
            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
        @else
            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-award text-white"></i>
            </div>
        @endif
    </td>
    <td>
        <a href="{{ route('admin.brands.edit', $brand->id) }}" class="text-decoration-none fw-medium">
            {{ $brand->name }}
        </a>
        @if($brand->website)
            <br><small class="text-muted"><i class="bi bi-link-45deg"></i> {{ $brand->website }}</small>
        @endif
    </td>
    <td>
        <span class="badge {{ $brand->products_count > 0 ? 'bg-info' : 'bg-light text-dark' }}">
            {{ $brand->products_count }}
        </span>
    </td>
    <td>
        <button type="button" class="btn btn-sm status-toggle {{ $brand->is_active ? 'btn-success' : 'btn-outline-secondary' }}" data-id="{{ $brand->id }}">
            {{ $brand->is_active ? 'Active' : 'Inactive' }}
        </button>
    </td>
    <td>
        <button type="button" class="btn btn-sm featured-toggle {{ $brand->is_featured ? 'btn-info' : 'btn-outline-secondary' }}" data-id="{{ $brand->id }}">
            <i class="bi {{ $brand->is_featured ? 'bi-star-fill' : 'bi-star' }}"></i>
        </button>
    </td>
    <td>
        <span class="text-muted">{{ $brand->sort_order }}</span>
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.brands.show', $brand->id) }}" class="btn btn-sm btn-outline-info" title="View">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            @if($brand->products_count === 0)
                <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this brand?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @else
                <button type="button" class="btn btn-sm btn-outline-secondary disabled" title="Cannot delete - has products" disabled>
                    <i class="bi bi-trash"></i>
                </button>
            @endif
        </div>
    </td>
</tr>
