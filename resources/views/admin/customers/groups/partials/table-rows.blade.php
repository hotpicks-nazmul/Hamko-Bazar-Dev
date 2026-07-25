@forelse($customerGroups as $customerGroup)
<tr>
    <td>{{ $loop->iteration + ($customerGroups->currentPage() - 1) * $customerGroups->perPage() }}</td>
    <td>
        <div class="fw-medium">{{ $customerGroup->name }}</div>
        @if($customerGroup->description)
        <small class="text-muted">{{ Str::limit($customerGroup->description, 50) }}</small>
        @endif
    </td>
    <td>
        <code class="small">{{ $customerGroup->slug }}</code>
    </td>
    <td>
        @if($customerGroup->discount_percentage > 0)
        <span class="badge bg-info">{{ $customerGroup->discount_percentage }}%</span>
        @else
        <span class="text-muted">0%</span>
        @endif
    </td>
    <td>
        <span class="badge bg-secondary">{{ $customerGroup->users_count ?? 0 }}</span>
    </td>
    <td>
        @if($customerGroup->is_active)
        <span class="badge bg-success">Active</span>
        @else
        <span class="badge bg-secondary">Inactive</span>
        @endif
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.customers.groups.edit', $customerGroup->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.customers.groups.toggle-status', $customerGroup->id) }}" method="POST" class="d-inline">
                @csrf
                @method('POST')
                <button type="submit" class="btn btn-sm btn-outline-{{ $customerGroup->is_active ? 'warning' : 'success' }}" title="{{ $customerGroup->is_active ? 'Deactivate' : 'Activate' }}">
                    <i class="bi bi-{{ $customerGroup->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                </button>
            </form>
            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="confirmDelete('delete-form-{{ $customerGroup->id }}')">
                <i class="bi bi-trash"></i>
            </button>
            <form id="delete-form-{{ $customerGroup->id }}" action="{{ route('admin.customers.groups.destroy', $customerGroup->id) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-5">
        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No customer groups found</p>
        <a href="{{ route('admin.customers.groups.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First Group
        </a>
    </td>
</tr>
@endforelse
