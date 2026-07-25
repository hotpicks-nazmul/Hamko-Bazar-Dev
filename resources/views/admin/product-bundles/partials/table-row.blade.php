<tr data-id="{{ $bundle->id }}">
    <td>
        <input type="checkbox" class="form-check-input bundle-checkbox" value="{{ $bundle->id }}" 
               {{ in_array($bundle->id, selectedBundles ?? []) ? 'checked' : '' }}>
    </td>
    <td>
        @if($bundle->featured_image)
            <img src="{{ asset('storage/' . $bundle->featured_image) }}" 
                 alt="{{ $bundle->name }}" 
                 class="rounded" 
                 style="width: 60px; height: 60px; object-fit: cover;">
        @else
            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                 style="width: 60px; height: 60px;">
                <i class="bi bi-boxes text-muted" style="font-size: 1.5rem;"></i>
            </div>
        @endif
    </td>
    <td>
        <div class="fw-semibold">{{ $bundle->name }}</div>
        <small class="text-muted">{{ Str::limit($bundle->description, 50) }}</small>
        @if($bundle->starts_at || $bundle->expires_at)
            <div class="small text-muted mt-1">
                @if($bundle->starts_at && !$bundle->hasStarted())
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-clock me-1"></i>Starts: {{ $bundle->starts_at->format('M d, Y') }}
                    </span>
                @elseif($bundle->expires_at)
                    @if($bundle->hasExpired())
                        <span class="badge bg-danger">
                            <i class="bi bi-x-circle me-1"></i>Expired
                        </span>
                    @else
                        <span class="badge bg-info">
                            <i class="bi bi-clock me-1"></i>Ends: {{ $bundle->expires_at->format('M d, Y') }}
                        </span>
                    @endif
                @endif
            </div>
        @endif
    </td>
    <td>
        <span class="badge bg-light text-dark">{{ $bundle->products_count ?? 0 }} items</span>
    </td>
    <td>
        <div class="fw-semibold">{{ config('app.currency', '$') }}{{ number_format($bundle->final_price, 2) }}</div>
        @if($bundle->original_price > $bundle->final_price)
            <div class="price-original">{{ config('app.currency', '$') }}{{ number_format($bundle->original_price, 2) }}</div>
        @endif
    </td>
    <td>
        @if($bundle->discount_value > 0)
            @if($bundle->discount_type === 'percentage')
                <span class="badge bg-success">{{ $bundle->discount_value }}% off</span>
            @else
                <span class="badge bg-success">{{ config('app.currency', '$') }}{{ number_format($bundle->discount_value, 2) }} off</span>
            @endif
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        <button type="button" class="btn btn-sm status-toggle btn-{{ $bundle->status_color }}" data-id="{{ $bundle->id }}">
            {{ $bundle->status_label }}
        </button>
    </td>
    <td>
        <button type="button" class="btn btn-sm featured-toggle {{ $bundle->is_featured ? 'btn-info' : 'btn-outline-secondary' }}" data-id="{{ $bundle->id }}">
            <i class="bi {{ $bundle->is_featured ? 'bi-star-fill' : 'bi-star' }}"></i>
        </button>
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('admin.product-bundles.show', $bundle->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.product-bundles.edit', $bundle->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.product-bundles.destroy', $bundle->id) }}" method="POST" style="display: inline;" id="deleteForm-{{ $bundle->id }}">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="confirmDelete({{ $bundle->id }})">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this bundle?')) {
        document.getElementById('deleteForm-' + id).submit();
    }
}
</script>
