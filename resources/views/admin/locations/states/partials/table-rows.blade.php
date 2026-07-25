@forelse($states as $state)
<tr>
    <td>{{ $state->id }}</td>
    <td><strong>{{ $state->name }}</strong></td>
    <td><span class="badge bg-info">{{ $state->countryRelation->name ?? $state->country }}</span></td>
    <td><span class="badge bg-secondary">{{ $state->cities_count }}</span></td>
    <td>
        <div class="form-check form-switch">
            <input class="form-check-input toggle-status" type="checkbox" data-url="{{ route('admin.locations.states.toggle-status', $state->id) }}" {{ $state->is_active ? 'checked' : '' }}>
        </div>
    </td>
    <td>{{ $state->sort_order }}</td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.locations.states.edit', $state->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.locations.states.destroy', $state->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this state?')">
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
        <p class="text-muted mb-2 mt-2">No states found</p>
        <a href="{{ route('admin.locations.states.create') }}" class="btn btn-sm btn-primary mt-1">Add State</a>
    </td>
</tr>
@endforelse
