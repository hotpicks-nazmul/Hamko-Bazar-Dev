@extends('admin.layouts.app')

@section('title', 'Create Color')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-plus-circle me-2"></i>Create Color</h4>
        <p class="text-muted mb-0">Add a new product color</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form id="colorForm" method="POST" action="{{ route('admin.colors.store') }}" enctype="multipart/form-data">
            @csrf
            
            <!-- Basic Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Color Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" placeholder="e.g., Red, Blue, Navy Blue">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" 
                                   value="{{ old('slug') }}" placeholder="Auto-generated from name">
                            <div class="form-text">Leave empty to auto-generate</div>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Code <span class="text-muted">(Short code like RED, BLU)</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                   value="{{ old('code') }}" placeholder="e.g., RED, BLU, NAV" maxlength="10">
                            <div class="form-text">Auto-generated from name if empty</div>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" 
                                   value="{{ old('display_order', 0) }}" min="0">
                            <div class="form-text">Lower numbers appear first</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional description for this color">{{ old('description') }}</textarea>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" class="form-check-input" id="isActive" name="is_active" value="1" form="colorForm" checked>
                        <label class="form-check-label" for="isActive">
                            <i class="bi bi-check-circle text-success me-1"></i> Active
                        </label>
                        <div class="form-text">Enable this color to make it available for product variations</div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" class="form-check-input" id="isFilterable" name="is_filterable" value="1" form="colorForm" checked>
                        <label class="form-check-label" for="isFilterable">
                            <i class="bi bi-funnel text-info me-1"></i> Filterable
                        </label>
                        <div class="form-text">Enable to show this color in frontend product filters</div>
                    </div>
                </div>
            </div>

            <!-- Color Values Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Color Values <span class="text-danger">*</span></h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addValueBtn">
                        <i class="bi bi-plus-lg me-1"></i> Add Value
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Add color values with their hex codes (e.g., Red: #FF0000, Light Red: #FF6666)</p>

                    <div id="valuesContainer" style="overflow: visible !important;">
                    </div>
                </div>
            </div>

            <!-- Image Upload Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-image me-2"></i>Color Image <span class="text-muted">(Optional)</span></h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Swatch Image</label>
                        <input type="file" name="image" id="imageInput" class="form-control @error('image') is-invalid @enderror" 
                               accept="image/jpeg,image/png,image/jpg,image/webp">
                        <div class="form-text">Upload a texture or pattern image for this color (e.g., wood grain, fabric pattern)</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                            <button type="button" id="removeImage" class="btn btn-sm btn-outline-danger ms-2">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <!-- Tips Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Use descriptive names like "Navy Blue" instead of just "Blue"
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        The hex code determines the color displayed to customers
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Upload a swatch image for textured colors (wood, fabric)
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Use display order to control sorting in dropdowns
                    </li>
                </ul>
            </div>
        </div>

        <!-- Common Colors Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-palette2 me-2"></i>Common Color Names</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-2">
                        <span class="color-swatch me-2" style="background-color: #FF0000; width: 16px; height: 16px;"></span>
                        Red
                    </div>
                    <div class="col-6 mb-2">
                        <span class="color-swatch me-2" style="background-color: #0000FF; width: 16px; height: 16px;"></span>
                        Blue
                    </div>
                    <div class="col-6 mb-2">
                        <span class="color-swatch me-2" style="background-color: #008000; width: 16px; height: 16px;"></span>
                        Green
                    </div>
                    <div class="col-6 mb-2">
                        <span class="color-swatch me-2" style="background-color: #FFFF00; width: 16px; height: 16px;"></span>
                        Yellow
                    </div>
                    <div class="col-6 mb-2">
                        <span class="color-swatch me-2" style="background-color: #FFA500; width: 16px; height: 16px;"></span>
                        Orange
                    </div>
                    <div class="col-6 mb-2">
                        <span class="color-swatch me-2" style="background-color: #800080; width: 16px; height: 16px;"></span>
                        Purple
                    </div>
                    <div class="col-6 mb-2">
                        <span class="color-swatch me-2" style="background-color: #FFC0CB; width: 16px; height: 16px;"></span>
                        Pink
                    </div>
                    <div class="col-6 mb-2">
                        <span class="color-swatch me-2" style="background-color: #8B4513; width: 16px; height: 16px;"></span>
                        Brown
                    </div>
                    <div class="col-6 mb-2">
                        <span class="color-swatch me-2" style="background-color: #000000; width: 16px; height: 16px;"></span>
                        Black
                    </div>
                    <div class="col-6 mb-2">
                        <span class="color-swatch me-2" style="background-color: #FFFFFF; width: 16px; height: 16px; border: 1px solid #ddd;"></span>
                        White
                    </div>
                    <div class="col-6 mb-2">
                        <span class="color-swatch me-2" style="background-color: #808080; width: 16px; height: 16px;"></span>
                        Grey
                    </div>
                    <div class="col-6 mb-2">
                        <span class="color-swatch me-2" style="background-color: #000080; width: 16px; height: 16px;"></span>
                        Navy
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.colors.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="colorForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Color
    </button>
</div>
@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
        overflow: visible !important;
    }
    .col-lg-8, .col-lg-4 {
        overflow: visible !important;
    }
    .row {
        overflow: visible !important;
    }
    .color-swatch {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: inline-block;
    }
    .value-item {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        position: relative;
        z-index: 1000;
        overflow: visible !important;
    }
    .value-item:hover {
        background: #f1f3f5;
    }
    .value-item select,
    .value-item .form-select {
        position: relative;
        z-index: 1001;
        overflow: visible !important;
    }
    .card-header {
        overflow: visible !important;
    }
    .floating-save-container {
        z-index: 500 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Clear all previous validation errors
        function clearValidationErrors() {
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            document.querySelectorAll('.invalid-feedback').forEach(el => {
                el.remove();
            });
        }

        // Show validation errors below fields using Bootstrap style
        function showValidationErrors(errors) {
            clearValidationErrors();

            Object.entries(errors).forEach(([field, messages]) => {
                let input = document.querySelector(`[name="${field}"]`);

                // Try alternative formats: values.0.value -> values[0][value]
                if (!input) {
                    const altName = field.replace(/(\w+)\.(\d+)\.(\w+)/, '$1[$2][$3]');
                    input = document.querySelector(`[name="${altName}"]`);
                }

                if (!input) {
                    return;
                }

                input.classList.add('is-invalid');

                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = Array.isArray(messages) ? messages.join(', ') : (typeof messages === 'string' ? messages : JSON.stringify(messages));

                if (input.parentElement.classList.contains('input-group')) {
                    input.parentElement.parentElement.appendChild(errorDiv);
                } else {
                    input.parentElement.appendChild(errorDiv);
                }
            });
        }

        // AJAX form submission
        const form = document.getElementById('colorForm');
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
                            adminToast('success', 'Success', data.message || 'Color created successfully.');
                        }
                        setTimeout(() => {
                            window.location.href = data.redirect_url || '{{ route('admin.colors.index') }}';
                        }, 500);
                    } else if (status === 422 && data.errors) {
                        showValidationErrors(data.errors);
                        window.scrollTo(0, scrollPosition);
                    } else if (status === 200 && !data.success) {
                        showValidationErrors({ form: [data.message || 'Something went wrong.'] });
                    }
                })
                .catch(error => {
                    console.error('Request error:', error);
                });

                return false;
            });
        }

        // Show toast on page load if URL has success parameter (after redirect)
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

        // Scroll to first error if validation fails
        @if($errors->any())
            var firstErrorField = document.querySelector('.is-invalid');
            if (firstErrorField) {
                setTimeout(function() {
                    firstErrorField.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstErrorField.focus();
                }, 100);
            }
        @endif

        // Auto-generate slug and code from name (real-time)
        const nameInput = document.querySelector('input[name="name"]');
        const slugInput = document.querySelector('input[name="slug"]');
        const codeInput = document.querySelector('input[name="code"]');

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                slugInput.value = this.value.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');

                if (codeInput) {
                    codeInput.value = this.value.toUpperCase()
                        .replace(/[^A-Z]/g, '')
                        .substring(0, 3);
                }
            });
        }

        // Image preview
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const removeImage = document.getElementById('removeImage');

        if (imageInput) {
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });

            removeImage.addEventListener('click', function() {
                imageInput.value = '';
                previewImg.src = '';
                imagePreview.style.display = 'none';
            });
        }

        // Add Value button handler
        document.getElementById('addValueBtn').addEventListener('click', function() {
            addValueRow();
            attachDuplicateCheckListeners();
        });

        @if(old('values'))
            repopulateValues();
        @else
            addValueRow();
        @endif
        attachDuplicateCheckListeners();
    });

    // Color Values management - defined outside DOMContentLoaded
    let valueIndex = 0;

    function addValueRow(value = '', hexCode = '', displayOrder = '', isActive = true) {
        const container = document.getElementById('valuesContainer');
        const row = document.createElement('div');
        row.className = 'value-item';
        row.id = `value-row-${valueIndex}`;
        row.innerHTML = `
            <div class="row align-items-center">
                <div class="col-md-3 mb-2 mb-md-0">
                    <input type="text" name="values[${valueIndex}][value]" class="form-control form-control-sm"
                           value="${value || ''}" placeholder="Value (e.g., Light Red)">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <div class="input-group input-group-sm">
                        <input type="color" class="form-control form-control-color" style="width: 40px; padding: 2px;"
                               value="${hexCode || '#000000'}" onchange="updateValueHex(this, ${valueIndex})">
                        <input type="text" name="values[${valueIndex}][hex_code]" class="form-control form-control-sm"
                               value="${hexCode || '#000000'}" placeholder="#000000" maxlength="7">
                    </div>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <input type="number" name="values[${valueIndex}][display_order]" class="form-control form-control-sm"
                           value="${displayOrder || ''}" placeholder="Order" min="0">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <div class="form-check form-switch mt-1">
                        <input type="checkbox" name="values[${valueIndex}][is_active]" class="form-check-input" id="valueActive${valueIndex}" ${isActive ? 'checked' : ''}>
                        <label class="form-check-label" for="valueActive${valueIndex}">Active</label>
                    </div>
                </div>
                <div class="col-md-2 text-end">
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

    function updateValueHex(input, index) {
        const textInput = input.nextElementSibling;
        textInput.value = input.value.toUpperCase();
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
                    feedback.className = 'duplicate-feedback text-danger mt-1';
                    feedback.textContent = 'Duplicate value';
                    firstRow.querySelector('.col-md-3').appendChild(feedback);
                }

                const feedback = document.createElement('div');
                feedback.className = 'duplicate-feedback text-danger mt-1';
                feedback.textContent = 'Duplicate value';
                row.querySelector('.col-md-3').appendChild(feedback);
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

    // Re-populate values after all functions are defined
    function repopulateValues() {
        const oldValuesRaw = @json(old('values', []));
        const container = document.getElementById('valuesContainer');
        container.innerHTML = '';
        valueIndex = 0;

        if (oldValuesRaw && Array.isArray(oldValuesRaw) && oldValuesRaw.length > 0) {
            oldValuesRaw.forEach(function(valueData) {
                if (valueData && (valueData.value !== '' && valueData.value !== null && valueData.value !== undefined)) {
                    addValueRow(
                        valueData.value || '',
                        valueData.hex_code || '#000000',
                        valueData.display_order || '',
                        valueData.is_active ? true : false
                    );
                }
            });
            if (valueIndex === 0) {
                addValueRow();
            }
        } else {
            addValueRow();
        }
    }

    // Always add initial row on page load
</script>
@endpush
