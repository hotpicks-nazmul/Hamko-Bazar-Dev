@forelse($warehouses as $warehouse)
<tr>
    <td>
        <input type="checkbox" class="form-check-input warehouse-checkbox" value="{{ $warehouse->id }}" onchange="updateSelection({{ $warehouse->id }}, this.checked)">
    </td>
    <td>
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <div class="bg-light rounded p-2 text-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-building"></i>
                </div>
            </div>
            <div class="ms-3">
                <div class="fw-semibold">
                    {{ $warehouse->name }}
                    @if($warehouse->is_primary)
                        <span class="badge bg-warning text-dark ms-1">Primary</span>
                    @endif
                </div>
                @if($warehouse->code)
                    <small class="text-muted">Code: {{ $warehouse->code }}</small>
                @endif
            </div>
        </div>
    </td>
    <td>
        <div>{{ $warehouse->address }}</div>
        @if($warehouse->city || $warehouse->state || $warehouse->country)
            <small class="text-muted">
                {{ implode(', ', array_filter([$warehouse->city, $warehouse->state, $warehouse->country])) }}
            </small>
        @endif
    </td>
    <td>
        @if($warehouse->phone)
            <div><i class="bi bi-phone me-1"></i>{{ $warehouse->phone }}</div>
        @endif
        @if($warehouse->email)
            <small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $warehouse->email }}</small>
        @endif
    </td>
    <td>
        <button type="button" class="btn btn-sm {{ $warehouse->is_active ? 'btn-success' : 'btn-outline-secondary' }}"
                onclick="toggleStatus({{ $warehouse->id }})" title="Toggle Status">
            <i class="bi {{ $warehouse->is_active ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
            {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
        </button>
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.warehouses.destroy', $warehouse->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this warehouse?')">
                @csrf
                @method('DELETE')
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
        <div class="text-muted">
            <i class="bi bi-building display-4"></i>
            <p class="mt-2">No warehouses found.</p>
            <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Warehouse
            </a>
        </div>
    </td>
</tr>
@endforelse
