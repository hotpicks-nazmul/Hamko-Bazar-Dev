@extends('admin.layouts.app')

@section('title', $form->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="bi bi-ui-checks me-2"></i>{{ $form->name }}</h4>
        @if($form->title)
        <small class="text-muted">{{ $form->title }}</small>
        @endif
    </div>
    <div>
        <a href="{{ route('admin.form-builder.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Forms
        </a>
        <a href="{{ route('admin.form-builder.edit', $form->id) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit Form
        </a>
        <a href="{{ route('admin.form-builder.submissions', $form->id) }}" class="btn btn-outline-info">
            <i class="bi bi-inbox me-1"></i> Submissions ({{ $form->submissions_count }})
        </a>
    </div>
</div>

<!-- Form Info -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-3">Form Details</h6>
                <div class="mb-2">
                    <strong>Slug:</strong> <code>{{ $form->slug }}</code>
                </div>
                <div class="mb-2">
                    <strong>Fields:</strong> {{ $form->fields->count() }}
                </div>
                <div class="mb-2">
                    <strong>Submissions:</strong> {{ $form->submissions_count }}
                </div>
                <div class="mb-2">
                    <strong>Status:</strong> 
                    @if($form->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-3">Form URL</h6>
                <code class="d-block mb-2">{{ route('forms.show', $form->slug) }}</code>
                <small class="text-muted">Use this URL to display the form on your website</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-3">Quick Actions</h6>
                <button type="button" class="btn btn-sm {{ $form->is_active ? 'btn-warning' : 'btn-success' }}"
                        id="toggleStatusBtn" onclick="toggleFormStatus({{ $form->id }})">
                    <i class="bi bi-{{ $form->is_active ? 'pause' : 'play' }}-circle me-1"></i>
                    {{ $form->is_active ? 'Deactivate' : 'Activate' }}
                </button>
                <form action="{{ route('admin.form-builder.duplicate', $form->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-copy me-1"></i> Duplicate
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Description -->
@if($form->description)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="text-muted text-uppercase small mb-2">Description</h6>
        {{ $form->description }}
    </div>
</div>
@endif

<!-- Fields Preview -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Form Fields ({{ $form->fields->count() }})</h6>
    </div>
    <div class="card-body">
        @if($form->fields->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Label</th>
                        <th>Field Name</th>
                        <th>Type</th>
                        <th>Width</th>
                        <th>Required</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($form->fields->sortBy('order') as $field)
                    <tr>
                        <td>{{ $field->order + 1 }}</td>
                        <td>
                            <strong>{{ $field->label }}</strong>
                            @if($field->placeholder)
                            <br><small class="text-muted">Placeholder: {{ $field->placeholder }}</small>
                            @endif
                            @if($field->help_text)
                            <br><small class="text-muted">Help: {{ $field->help_text }}</small>
                            @endif
                        </td>
                        <td><code>{{ $field->name }}</code></td>
                        <td><span class="badge bg-secondary">{{ ucfirst($field->type) }}</span></td>
                        <td><span class="badge bg-primary">{{ $field->width }}/12</span></td>
                        <td>
                            @if($field->is_required)
                                <span class="badge bg-danger">Required</span>
                            @else
                                <span class="text-muted">Optional</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-ui-checks text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mb-2 mt-2">No fields added yet</p>
            <a href="{{ route('admin.form-builder.edit', $form->id) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Fields
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleFormStatus(formId) {
    const btn = document.getElementById('toggleStatusBtn');
    fetch(`{{ route('admin.form-builder.toggle-status', ':id') }}`.replace(':id', formId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.is_active) {
                btn.className = 'btn btn-sm btn-warning';
                btn.innerHTML = '<i class="bi bi-pause-circle me-1"></i> Deactivate';
            } else {
                btn.className = 'btn btn-sm btn-success';
                btn.innerHTML = '<i class="bi bi-play-circle me-1"></i> Activate';
            }
        }
    })
    .catch(err => console.error('Error toggling status:', err));
}
</script>
@endpush
