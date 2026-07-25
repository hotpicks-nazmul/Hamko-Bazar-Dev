@forelse($zones as $zone)
<tr>
    <td style="width: 40px;">
        <input type="checkbox" class="form-check-input zone-checkbox" value="{{ $zone->id }}">
    </td>
    <td>
        <div class="fw-medium">{{ $zone->name }}</div>
        @if($zone->is_default)
            <span class="badge bg-info ms-1">Default</span>
        @endif
        @if($zone->region)
            <div class="small text-muted">{{ $zone->region }}</div>
        @endif
    </td>
    <td>
        <span class="badge bg-secondary">{{ $zone->area_type_label }}</span>
    </td>
    <td>
        <div class="small">
            @if($zone->city)
                <div>{{ $zone->city }}</div>
            @endif
            @if($zone->state)
                <div class="text-muted">{{ $zone->state }}</div>
            @endif
            @if(!$zone->city && !$zone->state)
                <span class="text-muted">All Areas</span>
            @endif
        </div>
    </td>
    <td>
        <div class="small">
            @if($zone->shipping_cost_type === 'free')
                <span class="text-success fw-bold">Free</span>
            @else
                <span>{{ config('app.currency_symbol', '৳') }} {{ number_format($zone->shipping_cost, 2) }}</span>
            @endif
            @if($zone->free_shipping_enabled && $zone->free_shipping_threshold > 0)
                <div class="text-muted">Free over {{ config('app.currency_symbol', '৳') }} {{ number_format($zone->free_shipping_threshold) }}</div>
            @endif
        </div>
    </td>
    <td>
        @if($zone->estimated_days > 0)
            {{ $zone->estimated_days }} days
        @else
            <span class="text-muted">N/A</span>
        @endif
    </td>
    <td>
        @if($zone->is_active)
            <span class="badge bg-success">Active</span>
        @else
            <span class="badge bg-secondary">Inactive</span>
        @endif
    </td>
    <td style="width: 120px;">
        <div class="d-flex gap-1">
            <a href="{{ route('admin.delivery.zones.edit', $zone->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            
            @if(!$zone->is_default)
            <a href="{{ route('admin.delivery.zones.toggle-status', $zone->id) }}" 
               class="btn btn-sm {{ $zone->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} toggle-status"
               title="{{ $zone->is_active ? 'Deactivate' : 'Activate' }}">
                <i class="bi {{ $zone->is_active ? 'bi-pause-circle' : 'bi-check-circle' }}"></i>
            </a>
            @else
            <button class="btn btn-sm btn-outline-info" title="Default Zone" disabled>
                <i class="bi bi-star"></i>
            </button>
            @endif
            
            @if(!$zone->is_default)
            <a href="{{ route('admin.delivery.zones.toggle-default', $zone->id) }}" 
               class="btn btn-sm btn-outline-warning toggle-default"
               title="Set as Default">
                <i class="bi bi-star"></i>
            </a>
            @endif
            
            <form action="{{ route('admin.delivery.zones.destroy', $zone->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-outline-danger" 
                        onclick="if(confirm('Are you sure you want to delete this zone?')) { this.closest('form').submit(); }"
                        title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <i class="bi bi-map text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No delivery zones found</p>
        <a href="{{ route('admin.delivery.zones.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First Zone
        </a>
    </td>
</tr>
@endforelse
