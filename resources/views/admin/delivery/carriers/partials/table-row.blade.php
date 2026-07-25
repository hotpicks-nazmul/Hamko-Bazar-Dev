<tr>
    <td>
        <input type="checkbox" class="form-check-input carrier-checkbox" value="{{ $carrier->id }}" onclick="event.stopPropagation();">
    </td>
    <td>
        @php
            $logoUrl = $carrier->logo;
            if($logoUrl && !str_starts_with($logoUrl, '/storage/') && !str_starts_with($logoUrl, 'http')) {
                $logoUrl = '/storage/' . $logoUrl;
            }
        @endphp
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $carrier->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
        @else
            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-truck text-white"></i>
            </div>
        @endif
    </td>
    <td>
        <div class="fw-medium">{{ $carrier->name }}</div>
        <small class="text-muted">{{ $carrier->slug }}</small>
    </td>
    <td>
        <span class="badge bg-info">{{ $carrier->carrier_type_label }}</span>
    </td>
    <td>
        <span class="badge bg-secondary">{{ $carrier->service_type_label }}</span>
    </td>
    <td>
        ৳{{ number_format($carrier->base_rate, 2) }}
    </td>
    <td>
        @if($carrier->supports_tracking)
            <span class="badge bg-success"><i class="bi bi-check-circle"></i></span>
        @else
            <span class="badge bg-light text-dark"><i class="bi bi-x-circle"></i></span>
        @endif
    </td>
    <td>
        @if($carrier->supports_cod)
            <span class="badge bg-success"><i class="bi bi-check-circle"></i></span>
        @else
            <span class="badge bg-light text-dark"><i class="bi bi-x-circle"></i></span>
        @endif
    </td>
    <td>
        <span class="badge {{ $carrier->is_active ? 'bg-success' : 'bg-secondary' }}" id="status-badge-{{ $carrier->id }}">
            {{ $carrier->is_active ? 'Active' : 'Inactive' }}
        </span>
    </td>
    <td>
        <span class="badge {{ $carrier->is_featured ? 'bg-warning text-dark' : 'bg-light text-dark' }}" id="featured-badge-{{ $carrier->id }}">
            @if($carrier->is_featured)
                <i class="bi bi-star-fill me-1"></i>Yes
            @else
                No
            @endif
        </span>
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.delivery.carriers.edit', $carrier->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-success status-toggle" 
                    onclick="toggleStatus({{ $carrier->id }})" 
                    title="Toggle Status">
                <i class="bi bi-{{ $carrier->is_active ? 'pause' : 'play' }}-circle"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-info featured-toggle" 
                    onclick="toggleFeatured({{ $carrier->id }})" 
                    title="Toggle Featured">
                <i class="bi bi-{{ $carrier->is_featured ? 'star-fill' : 'star' }}"></i>
            </button>
            <form action="{{ route('admin.delivery.carriers.destroy', $carrier->id) }}" method="POST" class="d-flex" onsubmit="return confirm('Are you sure you want to delete this carrier?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger delete-btn" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
