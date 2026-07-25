@forelse($areas as $area)
<tr>
    <td>{{ $area->id }}</td>
    <td><strong>{{ $area->name }}</strong></td>
    <td>{{ $area->city->name ?? 'N/A' }}</td>
    <td><span class="badge bg-info">{{ $area->city->countryRelation->name ?? $area->city->country ?? 'N/A' }}</span></td>
    <td>
        <div class="form-check form-switch">
            <input class="form-check-input toggle-status" type="checkbox" data-url="{{ route('admin.locations.areas.toggle-status', $area->id) }}" {{ $area->is_active ? 'checked' : '' }}>
        </div>
    </td>
    <td>{{ $area->sort_order }}</td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.locations.areas.edit', $area->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.locations.areas.destroy', $area->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this area?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-5">
        <i class="bi bi-folder text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No areas found</p>
        <a href="{{ route('admin.locations.areas.create') }}" class="btn btn-sm btn-primary mt-1">Add Area</a>
    </td>
</tr>
@endforelse
