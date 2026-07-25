@extends('admin.layouts.app')

@section('title', 'Edit Attribute')

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-pencil me-2"></i>Edit Attribute</h4>
            <p class="text-muted mb-0">Update attribute: {{ $attribute->name }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="attributeForm" method="POST" action="{{ route('admin.attributes.update', $attribute->id) }}">
                @csrf
                @method('PUT')
                
                <!-- Basic Info Card -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $attribute->name) }}" placeholder="e.g., Size, Material, Weight Capacity">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" 
                                       value="{{ old('slug', $attribute->slug) }}" placeholder="Auto-generated from name">
                                <div class="form-text">Leave empty to auto-generate</div>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional description for this attribute">{{ old('description', $attribute->description) }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" name="display_order" class="form-control" 
                                       value="{{ old('display_order', $attribute->display_order) }}" min="0">
                                <div class="form-text">Lower numbers appear first</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="isActive" 
                                           {{ old('is_active', $attribute->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">
                                        <i class="bi bi-check-circle text-success me-1"></i> Active
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" name="is_filterable" class="form-check-input" id="isFilterable" 
                                           {{ old('is_filterable', $attribute->is_filterable) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isFilterable">
                                        <i class="bi bi-funnel text-info me-1"></i> Filterable
                                    </label>
                                </div>
                                <div class="form-text">Show in frontend filters</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attribute Values Card -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Attribute Values</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addValueBtn">
                            <i class="bi bi-plus-lg me-1"></i> Add Value
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Manage values for this attribute</p>

                        <div id="valuesContainer">
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger py-2 mt-3 mb-0">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <!-- Stats Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Values:</span>
                        <strong>{{ $attribute->values_count }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Active Values:</span>
                        <strong>{{ $attribute->active_values_count }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created:</span>
                        <strong>{{ $attribute->created_at->format('M d, Y') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Updated:</span>
                        <strong>{{ $attribute->updated_at->format('M d, Y') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-info me-2"></i>
                            Click <strong>Save</strong> button to update individual values
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-info me-2"></i>
                            Color code is optional - use for color-type attributes
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-info me-2"></i>
                            Display order affects sorting in frontend
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <form id="deleteForm" method="POST" action="{{ route('admin.attributes.destroy', $attribute->id) }}" style="display: inline;">
        @csrf
        @method('DELETE')
    </form>
    <button type="button" class="btn btn-outline-danger floating-reset-btn" onclick="if(confirm('Are you sure?')) document.getElementById('deleteForm').submit()">
        <i class="bi bi-trash me-1"></i> Delete
    </button>
    <button type="submit" form="attributeForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Attribute
    </button>
</div>
@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
    .value-item {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
    }
    .value-item:hover {
        background: #f1f3f5;
    }
    .toast-notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s ease;
    }
    .toast-notification.show {
        transform: translateY(0);
        opacity: 1;
    }
    .toast-content {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .toast-success .toast-content {
        border-left: 4px solid #28a745;
    }
    .toast-success .toast-content i {
        color: #28a745;
    }
    .toast-error .toast-content {
        border-left: 4px solid #dc3545;
    }
    .toast-error .toast-content i {
        color: #dc3545;
    }
    .value-item .is-duplicate {
        border-color: #dc3545 !important;
        background-color: #fff5f5;
    }
    .duplicate-feedback {
        color: #dc3545;
        font-size: 12px;
        margin-top: 4px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Attribute Values management - defined outside DOMContentLoaded
    let valueIndex = 0;

    function addValueRow(value = '', displayOrder = '', isActive = true, valueId = null) {
        const container = document.getElementById('valuesContainer');
        const row = document.createElement('div');
        row.className = 'value-item';
        row.id = `value-row-${valueIndex}`;
        const idField = valueId ? `<input type="hidden" name="values[${valueIndex}][id]" value="${valueId}">` : '';
        row.innerHTML = `
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    ${idField}
                    <input type="text" name="values[${valueIndex}][value]" class="form-control form-control-sm"
                           value="${value || ''}" placeholder="Value (e.g., Large)">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <input type="number" name="values[${valueIndex}][display_order]" class="form-control form-control-sm"
                           value="${displayOrder || ''}" placeholder="Order" min="0">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <div class="form-check form-switch mt-1">
                        <input type="checkbox" name="values[${valueIndex}][is_active]" class="form-check-input" id="valueActive${valueIndex}" ${isActive ? 'checked' : ''}>
                        <label class="form-check-label" for="valueActive${valueIndex}">Active</label>
                    </div>
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeValueRow(${valueIndex})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(row);
        valueIndex++;
    }

    function removeValueRow(index) {
        const row = document.getElementById(`value-row-${index}`);
        if (row) {
            row.remove();
        }
    }

    function populateExistingValues() {
        const existingValues = @json($attribute->values->toArray());
        const container = document.getElementById('valuesContainer');
        container.innerHTML = '';
        valueIndex = 0;

        if (existingValues && existingValues.length > 0) {
            existingValues.forEach(function(valueData, idx) {
                addValueRow(
                    valueData.value || '',
                    valueData.display_order || '',
                    valueData.is_active ? true : false,
                    valueData.id
                );
            });
        }

        // Always add an empty row at the end
        addValueRow();
        attachDuplicateCheckListeners();
    }

    function checkDuplicateValues() {
        const valueInputs = document.querySelectorAll('input[name^="values"][name$="[value]"]');
        const seenValues = new Map();

        valueInputs.forEach(input => {
            const row = input.closest('.value-item');
            if (!row) return;

            row.classList.remove('is-duplicate');
            const existingFeedback = row.querySelector('.duplicate-feedback');
            if (existingFeedback) existingFeedback.remove();

            const value = input.value.trim();
            if (!value) return;

            const lowerValue = value.toLowerCase();
            if (seenValues.has(lowerValue)) {
                const firstRow = seenValues.get(lowerValue);
                firstRow.classList.add('is-duplicate');
                row.classList.add('is-duplicate');

                if (!firstRow.querySelector('.duplicate-feedback')) {
                    const feedback = document.createElement('div');
                    feedback.className = 'duplicate-feedback';
                    feedback.textContent = 'Duplicate value';
                    firstRow.querySelector('.col-md-6').appendChild(feedback);
                }

                const feedback = document.createElement('div');
                feedback.className = 'duplicate-feedback';
                feedback.textContent = 'Duplicate value';
                row.querySelector('.col-md-6').appendChild(feedback);
            } else {
                seenValues.set(lowerValue, row);
            }
        });
    }

    function attachDuplicateCheckListeners() {
        const valueInputs = document.querySelectorAll('input[name^="values"][name$="[value]"]');
        valueInputs.forEach(input => {
            input.removeEventListener('input', checkDuplicateValues);
            input.removeEventListener('blur', checkDuplicateValues);
            input.addEventListener('input', checkDuplicateValues);
            input.addEventListener('blur', checkDuplicateValues);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Auto-generate slug from name (real-time)
        const nameInput = document.querySelector('input[name="name"]');
        const slugInput = document.querySelector('input[name="slug"]');

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                slugInput.value = this.value.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
            });
        }

// Add Value button handler
        document.getElementById('addValueBtn').addEventListener('click', function() {
            addValueRow();
            attachDuplicateCheckListeners();
        });

        populateExistingValues();
    });

    function clearValidationErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });
    }

    function showValidationErrors(errors) {
        clearValidationErrors();

        Object.entries(errors).forEach(([field, messages]) => {
            let input = document.querySelector(`[name="${field}"]`);

            if (!input) {
                const altName = field.replace(/(\w+)\.(\d+)\.(\w+)/, '$1[$2][$3]');
                input = document.querySelector(`[name="${altName}"]`);
            }

            if (!input) return;

            input.classList.add('is-invalid');

            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = Array.isArray(messages) ? messages.join(', ') : messages;

            if (input.parentElement.classList.contains('input-group')) {
                input.parentElement.parentElement.appendChild(errorDiv);
            } else {
                input.parentElement.appendChild(errorDiv);
            }
        });
    }

    const form = document.getElementById('attributeForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            clearValidationErrors();

            const formData = new FormData(form);
            const url = form.action;
            const scrollPosition = window.scrollY;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json()
                        .then(data => ({ status: response.status, data }))
                        .catch(() => ({ status: response.status, data: { success: false, message: 'Invalid JSON' } }));
                }
                return { status: response.status, data: { success: false, message: 'Non-JSON response' } };
            })
            .then(result => {
                if (!result) return;

                const { status, data } = result;

                if (status === 200 && data.success) {
                    if (typeof adminToast === 'function') {
                        adminToast('success', 'Success', data.message || 'Attribute updated successfully.');
                    }
                    if (data.attribute) {
                        const statsSection = document.querySelector('.col-lg-4 .card-body');
                        if (statsSection) {
                            const strongs = statsSection.querySelectorAll('strong');
                            if (strongs[0]) strongs[0].textContent = data.attribute.values_count || '0';
                            if (strongs[1]) strongs[1].textContent = data.attribute.active_values_count || '0';
                            if (strongs[3]) strongs[3].textContent = data.attribute.updated_at;
                        }
                    }
                    window.scrollTo(0, scrollPosition);
                } else if (status === 422 && data.errors) {
                    showValidationErrors(data.errors);
                    window.scrollTo(0, scrollPosition);
                }
            })
            .catch(error => {
                console.error('Request error:', error);
            });

            return false;
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    const successMsg = urlParams.get('success');
    const errorMsg = urlParams.get('error');

    if (successMsg) {
        if (typeof adminToast === 'function') {
            adminToast('success', 'Success', decodeURIComponent(successMsg));
        }
        window.history.replaceState({}, '', window.location.pathname);
    }

    if (errorMsg) {
        if (typeof adminToast === 'function') {
            adminToast('error', 'Error', decodeURIComponent(errorMsg));
        }
        window.history.replaceState({}, '', window.location.pathname);
    }
    </script>
@endpush
