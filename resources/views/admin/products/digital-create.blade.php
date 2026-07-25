@extends('admin.layouts.app')

@section('title', 'Create Digital Product')

@push('styles')
<style>
.file-upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s;
    cursor: pointer;
}
.file-upload-zone:hover, .file-upload-zone.dragover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}
.file-upload-zone.has-file {
    border-color: #198754;
    background-color: #d1e7dd;
}
.additional-file-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 1rem;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}
/* Add padding at bottom to prevent floating button overlap */
.content-area {
    padding-bottom: 100px !important;
}
</style>
@endpush

@section('content')
@if(auth()->user()->hasPermission('products') && auth()->user()->hasPermission('products.view'))
<!-- Back Button -->
<div class="mb-3">
    <a href="{{ route('admin.products.digital.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Digital Products
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Basic Information</h5>
            </div>
            <div class="card-body">
                <form id="productForm" method="POST" action="{{ route('admin.products.digital.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="productName" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">SKU <span class="text-success">(Auto-generated)</span></label>
                            <div class="input-group">
                                <input type="text" name="sku" id="skuInput" class="form-control" value="{{ old('sku') }}" readonly style="background-color: #f8f9fa;">
                                <button type="button" class="btn btn-outline-secondary" id="regenerateSku" title="Regenerate SKU">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <small class="text-muted">Auto-generated from date and time</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" {{ old('category_id') == (string)$id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" class="form-control" value="{{ old('brand') }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <input type="text" name="short_description" class="form-control" value="{{ old('short_description') }}" maxlength="500">
                        <small class="text-muted">Brief description for product listings (max 500 characters)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Full Description <span class="text-danger">*</span></label>
                        <textarea name="description" id="productDescription" class="form-control" rows="5" required>{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
            
            <!-- Digital File Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Digital File</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Upload Digital File</label>
                            <div class="file-upload-zone" id="fileUploadZone" onclick="document.getElementById('digital_file').click()">
                                <input type="file" name="digital_file" id="digital_file" class="d-none" accept=".zip,.rar,.pdf,.doc,.docx,.mp3,.mp4,.exe,.dmg,.apk,.ipa">
                                <div id="fileUploadContent">
                                    <i class="bi bi-cloud-arrow-up display-4 text-muted"></i>
                                    <p class="mb-0 mt-2">Click to upload or drag and drop</p>
                                    <small class="text-muted">ZIP, RAR, PDF, EXE, APK, etc. (Max 500MB)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Or External Download Link</label>
                            <input type="url" name="download_link" class="form-control" value="{{ old('download_link') }}" placeholder="https://example.com/download/file.zip">
                            <small class="text-muted">Use this if file is hosted externally</small>
                            
                            <div class="mt-3">
                                <label class="form-label">Version</label>
                                <input type="text" name="version" class="form-control" value="{{ old('version') }}" placeholder="1.0.0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Download Limit</label>
                            <input type="number" name="download_limit" class="form-control" value="{{ old('download_limit') }}" min="0">
                            <small class="text-muted">Maximum downloads per purchase (0 = unlimited)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Download Expiry (Days)</label>
                            <input type="number" name="download_expiry_days" class="form-control" value="{{ old('download_expiry_days') }}" min="0">
                            <small class="text-muted">Days after purchase before download expires (0 = never)</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Additional Files</label>
                        <div class="file-upload-zone" id="additionalFilesZone" onclick="document.getElementById('additional_files').click()">
                            <input type="file" name="additional_files[]" id="additional_files" class="d-none" multiple accept=".pdf,.doc,.docx,.txt,.jpg,.png">
                            <div id="additionalFilesContent">
                                <i class="bi bi-paperclip display-4 text-muted"></i>
                                <p class="mb-0 mt-2">Upload additional files (manuals, guides, etc.)</p>
                                <small class="text-muted">PDF, DOC, TXT, Images (Max 100MB each)</small>
                            </div>
                        </div>
                        <div id="additionalFilesList" class="mt-2"></div>
                    </div>
                </div>
            </div>
            
            <!-- License Key Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">License Key Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="requires_license_key" id="requires_license_key" class="form-check-input" value="1" {{ old('requires_license_key') ? 'checked' : '' }}>
                                <label class="form-check-label" for="requires_license_key">Requires License Key</label>
                            </div>
                            <small class="text-muted">Enable if product requires a license key for activation</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="auto_generate_license" id="auto_generate_license" class="form-check-input" value="1" {{ old('auto_generate_license') ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_generate_license">Auto-generate License Keys</label>
                            </div>
                            <small class="text-muted">Automatically generate license keys on product creation</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3" id="licenseGenerationOptions" style="display: none;">
                        <div class="col-md-6">
                            <label class="form-label">Number of Keys to Generate</label>
                            <input type="number" name="license_count" class="form-control" value="{{ old('license_count', 10) }}" min="1" max="1000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">License Type</label>
                            <select name="license_type" class="form-select">
                                <option value="">Select Type</option>
                                <option value="single" {{ old('license_type') == 'single' ? 'selected' : '' }}>Single User</option>
                                <option value="multi" {{ old('license_type') == 'multi' ? 'selected' : '' }}>Multi-User</option>
                                <option value="site" {{ old('license_type') == 'site' ? 'selected' : '' }}>Site License</option>
                                <option value="enterprise" {{ old('license_type') == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Instructions Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Instructions & Requirements</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Installation Instructions</label>
                        <textarea name="installation_instructions" class="form-control" rows="4" placeholder="Step-by-step installation guide...">{{ old('installation_instructions') }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">System Requirements</label>
                        <textarea name="system_requirements" class="form-control" rows="3" placeholder="Operating system, hardware requirements, dependencies...">{{ old('system_requirements') }}</textarea>
                    </div>
                </div>
            </div>
            
            <!-- Images Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Product Images</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Featured Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewFeaturedImage(this)">
                            <small class="text-muted">Main product image</small>
                            <div id="featuredImagePreview" class="mt-2"></div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Gallery Images</label>
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple onchange="previewGalleryImages(this)">
                            <small class="text-muted">Additional product images (screenshots, previews)</small>
                            <div id="galleryPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pricing Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Pricing</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Regular Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" name="price" class="form-control" value="{{ old('price') }}" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sale Price</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" name="sale_price" class="form-control" value="{{ old('sale_price') }}" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Product Code</label>
                            <input type="text" name="product_code" class="form-control" value="{{ old('product_code') }}">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SEO Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">SEO Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}" maxlength="60">
                        <small class="text-muted">Recommended: 50-60 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="2" maxlength="160">{{ old('meta_description') }}</textarea>
                        <small class="text-muted">Recommended: 150-160 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords') }}" placeholder="keyword1, keyword2, keyword3">
                    </div>
                </div>
            </div>
            
            <!-- Status Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_featured" id="is_featured" class="form-check-input" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">Featured</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Tips for Digital Products</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Add clear installation instructions
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Specify system requirements
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Include screenshots and previews
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Set appropriate download limits
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Use license keys for software
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Keep version number updated
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Supported File Types</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-2">
                        <span class="badge bg-light text-dark">ZIP</span>
                        <span class="badge bg-light text-dark">RAR</span>
                    </div>
                    <div class="col-6 mb-2">
                        <span class="badge bg-light text-dark">PDF</span>
                        <span class="badge bg-light text-dark">DOC</span>
                    </div>
                    <div class="col-6 mb-2">
                        <span class="badge bg-light text-dark">EXE</span>
                        <span class="badge bg-light text-dark">DMG</span>
                    </div>
                    <div class="col-6 mb-2">
                        <span class="badge bg-light text-dark">APK</span>
                        <span class="badge bg-light text-dark">IPA</span>
                    </div>
                    <div class="col-6 mb-2">
                        <span class="badge bg-light text-dark">MP3</span>
                        <span class="badge bg-light text-dark">MP4</span>
                    </div>
                    <div class="col-6">
                        <span class="badge bg-light text-dark">And more...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.products.digital.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="productForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Digital Product
    </button>
</div>
@else
<div class="alert alert-warning border-0 shadow-sm mb-4">
    <i class="bi bi-shield-exclamation me-2"></i>
    <strong>Access Restricted:</strong> You don't have permission to access products.
    Please contact an administrator if you need access.
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
$(document).ready(function() {
    // Initialize TinyMCE editor
    tinymce.init({
        selector: '#productDescription',
        height: 400,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample'
        ],
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | codesample forecolor backcolor | removeformat help',
        branding: false,
        automatic_uploads: true,
        images_upload_handler: function(blobInfo, success, failure, progress) {
            var formData = new FormData();
            formData.append('image', blobInfo.blob());
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            
            $.ajax({
                url: '{{ route("admin.media.upload") }}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.url) {
                        success(response.url);
                    } else {
                        failure('Upload failed');
                    }
                },
                error: function() {
                    failure('Upload failed');
                }
            });
        },
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
    });
});

// SKU Auto-generation
function generateSKU(name) {
    // Generate SKU in format: DIG-YYYYMMDDHHMMSS
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    
    return `DIG-${year}${month}${day}${hours}${minutes}${seconds}`;
}

function updateSKU() {
    const skuInput = document.getElementById('skuInput');
    
    if (skuInput) {
        // Always generate new SKU with current date/time
        skuInput.value = generateSKU();
    }
}

// SKU is auto-generated based on date/time on page load

// Regenerate SKU button
document.getElementById('regenerateSku').addEventListener('click', function() {
    const skuInput = document.getElementById('skuInput');
    skuInput.value = generateSKU();
});

// Generate initial SKU on page load
document.addEventListener('DOMContentLoaded', function() {
    const skuInput = document.getElementById('skuInput');
    
    // Always generate SKU on page load
    if (skuInput) {
        updateSKU();
    }
});

// File upload zone handling
const fileUploadZone = document.getElementById('fileUploadZone');
const digitalFileInput = document.getElementById('digital_file');

fileUploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    fileUploadZone.classList.add('dragover');
});

fileUploadZone.addEventListener('dragleave', () => {
    fileUploadZone.classList.remove('dragover');
});

fileUploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    fileUploadZone.classList.remove('dragover');
    
    if (e.dataTransfer.files.length) {
        digitalFileInput.files = e.dataTransfer.files;
        updateFileDisplay();
    }
});

digitalFileInput.addEventListener('change', updateFileDisplay);

function updateFileDisplay() {
    const file = digitalFileInput.files[0];
    if (file) {
        const size = formatFileSize(file.size);
        document.getElementById('fileUploadContent').innerHTML = `
            <i class="bi bi-file-earmark-check display-4 text-success"></i>
            <p class="mb-0 mt-2 fw-semibold">${file.name}</p>
            <small class="text-muted">${size}</small>
        `;
        fileUploadZone.classList.add('has-file');
    }
}

// Additional files handling
const additionalFilesZone = document.getElementById('additionalFilesZone');
const additionalFilesInput = document.getElementById('additional_files');
const additionalFilesList = document.getElementById('additionalFilesList');

additionalFilesZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    additionalFilesZone.classList.add('dragover');
});

additionalFilesZone.addEventListener('dragleave', () => {
    additionalFilesZone.classList.remove('dragover');
});

additionalFilesZone.addEventListener('drop', (e) => {
    e.preventDefault();
    additionalFilesZone.classList.remove('dragover');
    
    if (e.dataTransfer.files.length) {
        additionalFilesInput.files = e.dataTransfer.files;
        updateAdditionalFilesDisplay();
    }
});

additionalFilesInput.addEventListener('change', updateAdditionalFilesDisplay);

function updateAdditionalFilesDisplay() {
    const files = additionalFilesInput.files;
    if (files.length) {
        let html = '';
        for (let i = 0; i < files.length; i++) {
            html += `
                <div class="additional-file-item">
                    <div>
                        <i class="bi bi-file-earmark me-2"></i>
                        <span>${files[i].name}</span>
                        <small class="text-muted ms-2">${formatFileSize(files[i].size)}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAdditionalFile(${i})">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            `;
        }
        additionalFilesList.innerHTML = html;
        additionalFilesZone.classList.add('has-file');
    } else {
        additionalFilesList.innerHTML = '';
        additionalFilesZone.classList.remove('has-file');
    }
}

function removeAdditionalFile(index) {
    const dt = new DataTransfer();
    const files = additionalFilesInput.files;
    for (let i = 0; i < files.length; i++) {
        if (i !== index) {
            dt.items.add(files[i]);
        }
    }
    additionalFilesInput.files = dt.files;
    updateAdditionalFilesDisplay();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// License key options toggle
const requiresLicenseKey = document.getElementById('requires_license_key');
const licenseGenerationOptions = document.getElementById('licenseGenerationOptions');

requiresLicenseKey.addEventListener('change', function() {
    licenseGenerationOptions.style.display = this.checked ? 'row' : 'none';
});

// Form validation
document.getElementById('productForm').addEventListener('submit', function(e) {
    const digitalFile = document.getElementById('digital_file').files[0];
    const downloadLink = document.querySelector('input[name="download_link"]').value;
    
    if (!digitalFile && !downloadLink) {
        e.preventDefault();
        alert('Please either upload a digital file or provide an external download link.');
    }
});

function previewFeaturedImage(input) {
    const preview = document.getElementById('featuredImagePreview');
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewGalleryImages(input) {
    const preview = document.getElementById('galleryPreview');
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'position-relative';
                div.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">';
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}
</script>
@endpush
