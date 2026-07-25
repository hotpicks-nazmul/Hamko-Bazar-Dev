@forelse($segments as $segment)
<tr>
    <td>
        <a href="{{ route('admin.customers.segmentation.show', $segment->id) }}" class="text-decoration-none fw-semibold">
            {{ $segment->name }}
        </a>
    </td>
    <td>
        <span class="text-muted">
            {{ Str::limit($segment->description, 50) }}
        </span>
    </td>
    <td>
        <span class="badge bg-primary bg-opacity-10 text-primary">
            <i class="bi bi-people me-1"></i>
            {{ number_format($segment->customer_count) }}
        </span>
    </td>
    <td>
        @if($segment->is_active)
        <span class="badge bg-success">Active</span>
        @else
        <span class="badge bg-secondary">Inactive</span>
        @endif
    </td>
    <td>
        <span class="text-muted small">
            {{ $segment->created_at->format('M d, Y') }}
        </span>
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('admin.customers.segmentation.show', $segment->id) }}" class="btn btn-sm btn-outline-secondary" title="View Customers">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.customers.segmentation.edit', $segment->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.customers.segmentation.export', $segment->id) }}">
                            <i class="bi bi-download me-2"></i> Export
                        </a>
                    </li>
                    <li>
                        <form action="{{ route('admin.customers.segmentation.toggle-status', $segment->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="dropdown-item {{ $segment->is_active ? 'text-warning' : 'text-success' }}">
                                <i class="bi {{ $segment->is_active ? 'bi-pause-circle' : 'bi-play-circle' }} me-2"></i>
                                {{ $segment->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('admin.customers.segmentation.destroy', $segment->id) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this segment?')">
                                <i class="bi bi-trash me-2"></i> Delete
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center py-5">
        <i class="bi bi-diagram-3 text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No segments found</p>
        <a href="{{ route('admin.customers.segmentation.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Create First Segment
        </a>
    </td>
</tr>
@endforelse
