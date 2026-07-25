@forelse($forms as $form)
<tr>
    <td>
        <div class="fw-medium">{{ $form->name }}</div>
        @if($form->title)
        <small class="text-muted">{{ $form->title }}</small>
        @endif
    </td>
    <td>
        <code class="small">{{ $form->slug }}</code>
    </td>
    <td>
        <span class="badge bg-primary">{{ $form->fields->count() }}</span>
    </td>
    <td>
        <span class="badge bg-info">{{ $form->submissions_count }}</span>
    </td>
    <td>
        @if($form->is_active)
            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Active</span>
        @else
            <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> Inactive</span>
        @endif
    </td>
    <td>
        <small class="text-muted">{{ $form->created_at->format('d M, Y') }}</small>
    </td>
    <td>
        <div class="btn-group">
            <!-- Toggle Status (AJAX) -->
            <button type="button" class="btn btn-sm {{ $form->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                    onclick="toggleFormStatus({{ $form->id }}, this)"
                    title="{{ $form->is_active ? 'Deactivate' : 'Activate' }}">
                <i class="bi bi-{{ $form->is_active ? 'pause-circle' : 'play-circle' }}"></i>
            </button>
            
            <!-- View Form -->
            <a href="{{ route('admin.form-builder.show', $form->id) }}" class="btn btn-sm btn-outline-info" title="View">
                <i class="bi bi-eye"></i>
            </a>
            
            <!-- Edit Form (Build Fields) -->
            <a href="{{ route('admin.form-builder.edit', $form->id) }}" class="btn btn-sm btn-outline-primary" title="Edit & Add Fields">
                <i class="bi bi-pencil"></i>
            </a>
            
            <!-- View Submissions -->
            <a href="{{ route('admin.form-builder.submissions', $form->id) }}" class="btn btn-sm btn-outline-secondary" title="Submissions">
                <i class="bi bi-inbox"></i>
            </a>
            
            <!-- Delete -->
            <form action="{{ route('admin.form-builder.destroy', $form->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this form? All submissions will also be deleted.')">
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
        <i class="bi bi-ui-checks text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No forms found</p>
        <a href="{{ route('admin.form-builder.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Create First Form
        </a>
    </td>
</tr>
@endforelse
