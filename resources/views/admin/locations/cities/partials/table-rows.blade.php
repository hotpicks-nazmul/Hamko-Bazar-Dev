@forelse($cities as $city)
<tr>
    <td>{{ $city->id }}</td>
    <td><strong>{{ $city->name }}</strong></td>
    <td><span class="badge bg-secondary">{{ $city->state->name ?? 'N/A' }}</span></td>
    <td><span class="badge bg-info">{{ $city->countryRelation->name ?? $city->country }}</span></td>
    <td><span class="badge bg-secondary">{{ $city->areas->count() }}</span></td>
    <td>
        <div class="form-check form-switch">
            <input class="form-check-input toggle-status" type="checkbox" data-url="{{ route('admin.locations.cities.toggle-status', $city->id) }}" {{ $city->is_active ? 'checked' : '' }}>
        </div>
    </td>
    <td>{{ $city->sort_order }}</td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.locations.areas.index', ['city_id' => $city->id]) }}" class="btn btn-sm btn-outline-info" title="Areas">
                <i class="bi bi-geo-alt"></i>
            </a>
            <a href="{{ route('admin.locations.cities.edit', $city->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.locations.cities.destroy', $city->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this city?')">
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
    <td colspan="8" class="text-center py-5">
        <i class="bi bi-folder text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No cities found</p>
        <a href="{{ route('admin.locations.cities.create') }}" class="btn btn-sm btn-primary mt-1">Add City</a>
    </td>
</tr>
@endforelse
