@extends('admin.layouts.app')

@section('title', 'Edit Form - ' . $form->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="bi bi-ui-checks me-2"></i>Edit Form: {{ $form->name }}</h4>
        <small class="text-muted">Add and manage form fields</small>
    </div>
    <div>
        <a href="{{ route('admin.form-builder.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Forms
        </a>
        <a href="{{ route('admin.form-builder.submissions', $form->id) }}" class="btn btn-outline-info">
            <i class="bi bi-inbox me-1"></i> Submissions ({{ $form->submissions_count }})
        </a>
    </div>
</div>

<!-- Form Settings -->
<div class="row mb-4">
    <div class="col-lg-12">
        <form id="formSettingsForm" method="POST" action="{{ route('admin.form-builder.update', $form->id) }}">
            @csrf
            @method('PUT')
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Form Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="name" class="form-label">Form Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ $form->name }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="title" class="form-label">Form Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="{{ $form->title }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="submit_button_text" class="form-label">Submit Button Text</label>
                            <input type="text" id="submit_button_text" name="submit_button_text" class="form-control" value="{{ $form->submit_button_text }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ $form->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="success_message" class="form-label">Success Message</label>
                            <textarea id="success_message" name="success_message" class="form-control" rows="2">{{ $form->success_message }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="redirect_url" class="form-label">Redirect URL</label>
                            <input type="url" id="redirect_url" name="redirect_url" class="form-control" value="{{ $form->redirect_url }}" placeholder="https://example.com/thank-you">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Add Field Section -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add New Field</h6>
            </div>
            <div class="card-body">
                <form id="addFieldForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Field Label <span class="text-danger">*</span></label>
                            <input type="text" name="label" id="fieldLabel" class="form-control" placeholder="e.g., Full Name" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Field Type</label>
                            <select name="type" id="fieldType" class="form-select">
                                @foreach($fieldTypes as $type => $label)
                                    <option value="{{ $type }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Placeholder</label>
                            <input type="text" name="placeholder" id="fieldPlaceholder" class="form-control" placeholder="Placeholder text">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Width</label>
                            <select name="width" id="fieldWidth" class="form-select">
                                <option value="12">Full (12)</option>
                                <option value="6">Half (6)</option>
                                <option value="4">One Third (4)</option>
                                <option value="3">One Quarter (3)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="fieldRequired" name="is_required">
                                <label class="form-check-label" for="fieldRequired">Required</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Options for select/radio/checkbox -->
                    <div class="row mt-3" id="optionsContainer" style="display: none;">
                        <div class="col-md-12">
                            <label class="form-label">Options (one per line)</label>
                            <textarea name="options_text" id="fieldOptionsText" class="form-control" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-lg me-1"></i> Add Field
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Form Fields -->
<div class="row">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Form Fields ({{ $form->fields->count() }})</h6>
            </div>
            <div class="card-body p-0">
                @if($form->fields->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="fieldsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;"><i class="bi bi-grip-vertical"></i></th>
                                <th>Label</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Width</th>
                                <th>Required</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortableFields">
                            @foreach($form->fields->sortBy('order') as $field)
                            <tr class="field-card" data-field-id="{{ $field->id }}">
                                <td><i class="bi bi-grip-vertical text-muted"></i></td>
                                <td>
                                    <strong>{{ $field->label }}</strong>
                                    @if($field->help_text)
                                    <br><small class="text-muted">{{ $field->help_text }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $field->name }}</code></td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($field->type) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $field->width }}/12</span>
                                </td>
                                <td>
                                    @if($field->is_required)
                                        <span class="badge bg-danger">Required</span>
                                    @else
                                        <span class="text-muted">Optional</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editField({{ $field->id }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteField({{ $field->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-ui-checks text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mb-2 mt-2">No fields added yet</p>
                    <p class="text-muted small">Add fields above to build your form</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Field Modal -->
<div class="modal fade" id="editFieldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editFieldForm">
                <div class="modal-body">
                    <input type="hidden" id="editFieldId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Field Label <span class="text-danger">*</span></label>
                            <input type="text" id="editFieldLabel" name="label" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Field Type</label>
                            <select id="editFieldType" name="type" class="form-select" disabled>
                                @foreach($fieldTypes as $type => $label)
                                    <option value="{{ $type }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Placeholder</label>
                            <input type="text" id="editFieldPlaceholder" name="placeholder" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Value</label>
                            <input type="text" id="editFieldDefault" name="default_value" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Help Text</label>
                        <input type="text" id="editFieldHelpText" name="help_text" class="form-control">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Width</label>
                            <select id="editFieldWidth" name="width" class="form-select">
                                <option value="12">Full (12)</option>
                                <option value="6">Half (6)</option>
                                <option value="4">One Third (4)</option>
                                <option value="3">One Quarter (3)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="editFieldRequired" name="is_required">
                                <label class="form-check-label" for="editFieldRequired">Required</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="editFieldVisible" name="is_visible" checked>
                                <label class="form-check-label" for="editFieldVisible">Visible</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="editOptionsContainer" style="display: none;">
                        <label class="form-label">Options (one per line)</label>
                        <textarea id="editFieldOptionsText" name="options_text" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Field</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.field-card {
    cursor: grab;
    transition: all 0.2s ease;
}
.field-card:active {
    cursor: grabbing;
}
.field-card.sortable-ghost {
    opacity: 0.4;
    background: #f8f9fa;
}
.field-card.sortable-chosen {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.field-options-row {
    display: none;
}
.field-options-row.show {
    display: block;
}
/* Add padding at bottom to prevent floating button overlap */
.content-area {
    padding-bottom: 100px !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formId = {{ $form->id }};
    
    // Show/hide options based on field type
    const fieldTypeSelect = document.getElementById('fieldType');
    const optionsContainer = document.getElementById('optionsContainer');
    
    fieldTypeSelect.addEventListener('change', function() {
        const hasOptions = ['select', 'radio', 'checkbox'].includes(this.value);
        optionsContainer.style.display = hasOptions ? 'block' : 'none';
    });
    
    // Add field
    document.getElementById('addFieldForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Convert options text to array
        const optionsText = formData.get('options_text');
        if (optionsText) {
            const options = optionsText.split('\n').filter(opt => opt.trim());
            formData.set('options', JSON.stringify(options));
        }
        formData.delete('options_text');
        
        fetch(`/admin/form-builder/${formId}/fields`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error adding field');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error adding field. Check console for details.');
        });
    });
    
    // Initialize Sortable
    const sortableList = document.getElementById('sortableFields');
    if (sortableList) {
        new Sortable(sortableList, {
            handle: '.bi-grip-vertical',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            animation: 150,
            onEnd: function(evt) {
                const fields = [];
                document.querySelectorAll('#sortableFields .field-card').forEach((card, index) => {
                    fields.push({
                        id: card.dataset.fieldId,
                        order: index
                    });
                });
                
                fetch(`/admin/form-builder/${formId}/fields/reorder`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ fields })
                })
                .then(res => res.json())
                .then(data => {
                    console.log('Fields reordered');
                })
                .catch(err => console.error(err));
            }
        });
    }
});

// Edit field
function editField(fieldId) {
    fetch(`/admin/form-builder/{{ $form->id }}/fields/${fieldId}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const field = data.field;
        
        document.getElementById('editFieldId').value = field.id;
        document.getElementById('editFieldLabel').value = field.label;
        document.getElementById('editFieldType').value = field.type;
        document.getElementById('editFieldPlaceholder').value = field.placeholder || '';
        document.getElementById('editFieldHelpText').value = field.help_text || '';
        document.getElementById('editFieldDefault').value = field.default_value || '';
        document.getElementById('editFieldWidth').value = field.width;
        document.getElementById('editFieldRequired').checked = field.is_required;
        document.getElementById('editFieldVisible').checked = field.is_visible;
        
        // Show options for select/radio/checkbox
        const hasOptions = ['select', 'radio', 'checkbox'].includes(field.type);
        document.getElementById('editOptionsContainer').style.display = hasOptions ? 'block' : 'none';
        
        // Parse options
        if (field.options && Array.isArray(field.options)) {
            document.getElementById('editFieldOptionsText').value = field.options.join('\n');
        } else if (typeof field.options === 'object') {
            document.getElementById('editFieldOptionsText').value = Object.values(field.options).join('\n');
        } else {
            document.getElementById('editFieldOptionsText').value = '';
        }
        
        const modal = new bootstrap.Modal(document.getElementById('editFieldModal'));
        modal.show();
    });
}

// Update field
document.getElementById('editFieldForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fieldId = document.getElementById('editFieldId').value;
    const formData = new FormData(this);
    const formId = {{ $form->id }};
    
    // Convert options text to array
    const optionsText = formData.get('options_text');
    if (optionsText) {
        const options = optionsText.split('\n').filter(opt => opt.trim());
        formData.set('options', JSON.stringify(options));
    }
    formData.delete('options_text');
    
    fetch(`/admin/form-builder/${formId}/fields/${fieldId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error updating field');
        }
    })
    .catch(err => console.error(err));
});

// Delete field
function deleteField(fieldId) {
    if (!confirm('Are you sure you want to delete this field?')) return;
    
    const formId = {{ $form->id }};
    
    fetch(`/admin/form-builder/${formId}/fields/${fieldId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error deleting field');
        }
    })
    .catch(err => console.error(err));
}
</script>
@endpush
