@extends('admin.layouts.app')

@section('title', 'Edit Color')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-pencil me-2"></i>Edit Color</h4>
        <p class="text-muted mb-0">Update color: {{ $color->name }}</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form id="colorForm" method="POST" action="{{ route('admin.colors.update', $color->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

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
                                   value="{{ old('name', $color->name) }}" placeholder="e.g., Red, Blue, Navy Blue">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $color->slug) }}" placeholder="Auto-generated from name">
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
                                   value="{{ old('code', $color->code) }}" placeholder="e.g., RED, BLU, NAV" maxlength="10">
                            <div class="form-text">Auto-generated from name if empty</div>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control"
                                   value="{{ old('display_order', $color->display_order) }}" min="0">
                            <div class="form-text">Lower numbers appear first</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional description for this color">{{ old('description', $color->description) }}</textarea>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" class="form-check-input" id="isActive" name="is_active" value="1" form="colorForm" {{ old('is_active', $color->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">
                            <i class="bi bi-check-circle text-success me-1"></i> Active
                        </label>
                        <div class="form-text">Enable this color to make it available for product variations</div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" class="form-check-input" id="isFilterable" name="is_filterable" value="1" form="colorForm" {{ old('is_filterable', $color->is_filterable) ? 'checked' : '' }}>
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

                    <div id="valuesContainer">
                    </div>
                </div>
            </div>

            <!-- Image Upload Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-image me-2"></i>Color Image <span class="text-muted">(Optional)</span></h6>
                </div>
                <div class="card-body">
                    @if($color->image)
                    <div class="mb-3" id="currentImageContainer">
                        <label class="form-label">Current Image</label>
                        <div class="position-relative d-inline-block" style="display: inline-block;">
                            <div style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                <img src="{{ asset($color->image) }}" alt="{{ $color->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <button type="button" class="badge bg-danger rounded-circle border-0 position-absolute p-0"
                                style="top: -4px; right: -4px; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer;"
                                onclick="removeColorImage({{ $color->id }})">
                                <i class="bi bi-x" style="font-size: 12px;"></i>
                            </button>
                        </div>
                        <input type="hidden" name="delete_image" id="deleteImageInput" value="0">
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">{{ $color->image ? 'Replace Image' : 'Swatch Image' }}</label>
                        <input type="file" name="image" id="imageInput" class="form-control @error('image') is-invalid @enderror"
                               accept="image/jpeg,image/png,image/jpg,webp">
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
        <i class="bi bi-check-lg me-1"></i> Update Color
    </button>
</div>
@endsection

@push('styles')
<style>
    .color-swatch {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: inline-block;
        vertical-align: middle;
        border: 1px solid #ddd;
    }
    .color-preview {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        display: inline-block;
        vertical-align: middle;
        border: 2px solid #ddd;
        transition: all 0.3s ease;
    }
    .color-preview:hover {
        transform: scale(1.05);
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
    // All code inside DOMContentLoaded to ensure DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
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

        populateExistingValues();
    });

    // Color Values management - defined outside DOMContentLoaded
    let valueIndex = 0;

    function addValueRow(value = '', hexCode = '', displayOrder = '', isActive = true, valueId = null) {
        const container = document.getElementById('valuesContainer');
        const row = document.createElement('div');
        row.className = 'value-item';
        row.id = `value-row-${valueIndex}`;
        const idField = valueId ? `<input type="hidden" name="values[${valueIndex}][id]" value="${valueId}">` : '';
        row.innerHTML = `
            <div class="row align-items-center">
                <div class="col-md-3 mb-2 mb-md-0">
                    ${idField}
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

    function populateExistingValues() {
        const existingValues = @json($color->values);
        const container = document.getElementById('valuesContainer');
        container.innerHTML = '';
        valueIndex = 0;

        if (existingValues && existingValues.length > 0) {
            existingValues.forEach(function(valueData) {
                addValueRow(
                    valueData.value || '',
                    valueData.hex_code || '#000000',
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
                    firstRow.querySelector('.col-md-3').appendChild(feedback);
                }

                const feedback = document.createElement('div');
                feedback.className = 'duplicate-feedback';
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

    // Delete existing color image (AJAX)
    function removeColorImage(colorId) {
        if (!confirm('Delete this image?')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const imageContainer = document.getElementById('currentImageContainer');

        fetch(`/admin/colors/${colorId}/image`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const deleteInput = document.getElementById('deleteImageInput');
                if (deleteInput) {
                    deleteInput.value = '1';
                }
                imageContainer.remove();
            } else {
                alert(data.message || 'Error deleting image');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting image: ' + error.message);
        });
    }
</script>
@endpush
