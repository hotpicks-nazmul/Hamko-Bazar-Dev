@extends('admin.layouts.app')

@section('title', 'Edit Digital Product')

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
.current-file {
    background: #e8f5e9;
    border: 1px solid #a5d6a7;
    border-radius: 8px;
    padding: 1rem;
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
        <form id="productForm" method="POST" action="{{ route('admin.products.digital.update', $product->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Basic Information</h5>
                    <span class="badge bg-info">Digital Product</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">SKU <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" {{ old('category_id', $product->category_id) == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" class="form-control" value="{{ old('brand', $product->brand) }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <input type="text" name="short_description" class="form-control" value="{{ old('short_description', $product->short_description) }}" maxlength="500">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Full Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="5" required>{{ old('description', $product->long_description) }}</textarea>
                    </div>
                </div>
            </div>
            
            <!-- Digital File Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Digital File</h5>
                </div>
                <div class="card-body">
                    @if($product->file_path)
                    <div class="current-file mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-file-earmark-check text-success me-2"></i>
                                <strong>{{ $product->file_name }}</strong>
                                <span class="text-muted ms-2">({{ $product->file_size_formatted }})</span>
                                @if($product->file_format)
                                    <span class="badge bg-secondary ms-2">{{ $product->file_format }}</span>
                                @endif
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="viewFile('{{ $product->file_path }}', '{{ $product->file_name }}', '{{ $product->file_type }}')">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteFile()">
                                    <i class="bi bi-trash"></i> Replace
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ $product->file_path ? 'Replace File' : 'Upload Digital File' }}</label>
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
                            <input type="url" name="download_link" class="form-control" value="{{ old('download_link', $product->download_link) }}" placeholder="https://example.com/download/file.zip">
                            <small class="text-muted">Use this if file is hosted externally</small>
                            
                            <div class="mt-3">
                                <label class="form-label">Version</label>
                                <input type="text" name="version" class="form-control" value="{{ old('version', $product->version) }}" placeholder="1.0.0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Download Limit</label>
                            <input type="number" name="download_limit" class="form-control" value="{{ old('download_limit', $product->download_limit) }}" min="0">
                            <small class="text-muted">Maximum downloads per purchase (0 = unlimited)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Download Expiry (Days)</label>
                            <input type="number" name="download_expiry_days" class="form-control" value="{{ old('download_expiry_days', $product->download_expiry_days) }}" min="0">
                            <small class="text-muted">Days after purchase before download expires (0 = never)</small>
                        </div>
                    </div>
                    
                    <!-- Additional Files -->
                    <div class="mb-3">
                        <label class="form-label">Additional Files</label>
                        @if($product->additional_files && count($product->additional_files) > 0)
                        <div class="mb-2">
                            @foreach($product->additional_files as $index => $file)
                            <div class="additional-file-item" id="additionalFile{{ $index }}">
                                <div>
                                    <i class="bi bi-file-earmark me-2"></i>
                                    <span>{{ $file['name'] }}</span>
                                    <small class="text-muted ms-2">{{ number_format($file['size'] / 1024, 1) }} KB</small>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="viewFile('{{ $file['path'] }}', '{{ $file['name'] }}', '{{ $file['type'] }}')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAdditionalFile({{ $index }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
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
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">License Key Settings</h5>
                    @if($product->requires_license_key)
                    <span class="badge bg-success">{{ $licenseStats['available'] }} available / {{ $licenseStats['total'] }} total</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="requires_license_key" id="requires_license_key" class="form-check-input" value="1" {{ old('requires_license_key', $product->requires_license_key) ? 'checked' : '' }}>
                                <label class="form-check-label" for="requires_license_key">Requires License Key</label>
                            </div>
                            <small class="text-muted">Enable if product requires a license key for activation</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="auto_generate_license" id="auto_generate_license" class="form-check-input" value="1" {{ old('auto_generate_license', $product->auto_generate_license) ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_generate_license">Auto-generate License Keys</label>
                            </div>
                            <small class="text-muted">Automatically generate license keys for new orders</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3" id="licenseGenerationOptions" style="display: {{ $product->requires_license_key ? 'row' : 'none' }};">
                        <div class="col-md-6">
                            <label class="form-label">License Type</label>
                            <select name="license_type" class="form-select">
                                <option value="">Select Type</option>
                                <option value="single" {{ old('license_type', $product->license_type) == 'single' ? 'selected' : '' }}>Single User</option>
                                <option value="multi" {{ old('license_type', $product->license_type) == 'multi' ? 'selected' : '' }}>Multi-User</option>
                                <option value="site" {{ old('license_type', $product->license_type) == 'site' ? 'selected' : '' }}>Site License</option>
                                <option value="enterprise" {{ old('license_type', $product->license_type) == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            @if($product->requires_license_key)
                            <label class="form-label">Manage License Keys</label>
                            <div>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="showLicenseKeys()">
                                    <i class="bi bi-key me-1"></i> View License Keys
                                </button>
                            </div>
                            @endif
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
                        <textarea name="installation_instructions" class="form-control" rows="4" placeholder="Step-by-step installation guide...">{{ old('installation_instructions', $product->installation_instructions) }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">System Requirements</label>
                        <textarea name="system_requirements" class="form-control" rows="3" placeholder="Operating system, hardware requirements, dependencies...">{{ old('system_requirements', $product->system_requirements) }}</textarea>
                    </div>
                </div>
            </div>
            
            <!-- Images Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Product Images</h5>
                </div>
                <div class="card-body">
                    @php
                        $featuredImageUrl = null;
                        if ($product->featured_image) {
                            $img = ltrim($product->featured_image, '/');
                            if (str_starts_with($img, 'http')) {
                                $featuredImageUrl = $img;
                            } elseif (str_starts_with($img, 'storage/')) {
                                $featuredImageUrl = '/' . $img;
                            } else {
                                $featuredImageUrl = '/storage/' . $img;
                            }
                        }
                    @endphp
                    @if($product->featured_image)
                    <div class="mb-3">
                        <label class="form-label">Current Featured Image</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $featuredImageUrl }}" alt="Featured" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                            <div>
                                <input type="file" name="image" class="form-control form-control-sm" accept="image/*" onchange="previewFeaturedImage(this)">
                                <small class="text-muted">Upload new image to replace</small>
                                <div id="featuredImagePreview" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Featured Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewFeaturedImage(this)">
                            <div id="featuredImagePreview" class="mt-2"></div>
                        </div>
                    </div>
                    @endif
                    
                    @if($product->gallery && count($product->gallery) > 0)
                    <div class="mb-3">
                        <label class="form-label">Current Gallery</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($product->gallery as $img)
                            @php
                                $galleryImgUrl = ltrim($img, '/');
                                if (str_starts_with($galleryImgUrl, 'http')) {
                                    $galleryImgUrl = $galleryImgUrl;
                                } elseif (str_starts_with($galleryImgUrl, 'storage/')) {
                                    $galleryImgUrl = '/' . $galleryImgUrl;
                                } else {
                                    $galleryImgUrl = '/storage/' . $galleryImgUrl;
                                }
                            @endphp
                            <img src="{{ $galleryImgUrl }}" alt="Gallery" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
                            @endforeach
                        </div>
                        <small class="text-muted">Upload new images to replace current gallery</small>
                    </div>
                    @endif
                    
                    <div>
                        <label class="form-label">{{ ($product->gallery && count($product->gallery) > 0) ? 'Replace Gallery Images' : 'Gallery Images' }}</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple onchange="previewGalleryImages(this)">
                        <div id="galleryPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
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
                                <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sale Price</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price) }}" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Product Code</label>
                            <input type="text" name="product_code" class="form-control" value="{{ old('product_code', $product->product_code) }}">
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
                        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $product->meta_title) }}" maxlength="60">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="2" maxlength="160">{{ old('meta_description', $product->meta_description) }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $product->meta_keywords) }}">
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
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_featured" id="is_featured" class="form-check-input" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">Featured</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <form id="deleteForm" method="POST" action="{{ route('admin.products.digital.destroy', $product->id) }}">
            @csrf
            @method('DELETE')
        </form>
    </div>
    
    <div class="col-lg-4">
        <!-- Product Stats -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Product Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Sales</span>
                    <strong>{{ $product->orderItems()->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Downloads</span>
                    <strong>{{ $product->digitalDownloads()->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Revenue</span>
                    <strong>৳{{ number_format($product->orderItems()->sum('total'), 0) }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Created</span>
                    <small>{{ $product->created_at->format('M d, Y') }}</small>
                </div>
            </div>
        </div>
        
        @if($product->requires_license_key)
        <!-- License Key Stats -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">License Key Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Keys</span>
                    <strong>{{ $licenseStats['total'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Available</span>
                    <strong class="text-success">{{ $licenseStats['available'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Used</span>
                    <strong class="text-primary">{{ $licenseStats['used'] }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Disabled</span>
                    <strong class="text-danger">{{ $licenseStats['disabled'] }}</strong>
                </div>
            </div>
        </div>
        @endif
        
        <!-- File Info -->
        @if($product->file_path)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">File Information</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">File Name</span>
                    <small>{{ $product->file_name }}</small>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">File Size</span>
                    <small>{{ $product->file_size_formatted }}</small>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Format</span>
                    <small>{{ $product->file_format }}</small>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Type</span>
                    <small>{{ $product->file_type }}</small>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- File View Modal -->
<div class="modal fade" id="fileViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileViewTitle">View File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="fileViewBody">
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.products.digital.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <a href="{{ route('admin.products.digital.destroy', $product->id) }}" class="btn btn-outline-danger floating-reset-btn" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this product?')) { document.getElementById('deleteForm').submit(); }">
        <i class="bi bi-trash me-1"></i> Delete
    </a>
    <button type="submit" form="productForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Product
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
<script>
// File upload zone handling
const fileUploadZone = document.getElementById('fileUploadZone');
const digitalFileInput = document.getElementById('digital_file');

if (fileUploadZone) {
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
}

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
        for (let file of files) {
            html += `
                <div class="additional-file-item">
                    <div>
                        <i class="bi bi-file-earmark me-2"></i>
                        <span>${file.name}</span>
                        <small class="text-muted ms-2">${formatFileSize(file.size)}</small>
                    </div>
                </div>
            `;
        }
        additionalFilesList.innerHTML = html;
        additionalFilesZone.classList.add('has-file');
    }
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

if (requiresLicenseKey) {
    requiresLicenseKey.addEventListener('change', function() {
        licenseGenerationOptions.style.display = this.checked ? 'row' : 'none';
    });
}

// Delete additional file
function deleteAdditionalFile(index) {
    if (confirm('Are you sure you want to delete this file?')) {
        fetch(`{{ route('admin.products.digital.delete-additional-file', ['id' => $product->id, 'index' => '__INDEX__']) }}`.replace('__INDEX__', index), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('additionalFile' + index).remove();
            } else {
                alert(data.message);
            }
        });
    }
}

// Show license keys
function showLicenseKeys() {
    window.location.href = `{{ route('admin.products.digital.index') }}?show_licenses={{ $product->id }}`;
}

// Confirm delete file
function confirmDeleteFile() {
    return confirm('Are you sure you want to replace the current file?');
}

function previewFeaturedImage(input) {
    const preview = document.getElementById('featuredImagePreview');
    if (preview) {
        preview.innerHTML = '';
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
}

function previewGalleryImages(input) {
    const preview = document.getElementById('galleryPreview');
    if (preview) {
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
}

function viewFile(filePath, fileName, fileType) {
    const modal = document.getElementById('fileViewModal');
    const modalTitle = document.getElementById('fileViewTitle');
    const modalBody = document.getElementById('fileViewBody');
    
    modalTitle.textContent = fileName;
    modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    
    const url = '/storage/' + filePath;
    
    if (fileType && fileType.startsWith('image/')) {
        modalBody.innerHTML = '<img src="' + url + '" class="img-fluid" alt="' + fileName + '">';
    } else if (fileType === 'application/pdf' || fileName.toLowerCase().endsWith('.pdf')) {
        modalBody.innerHTML = '<iframe src="' + url + '" width="100%" height="500px" style="border:none;"></iframe>';
    } else if (fileType && (fileType.startsWith('video/') || fileName.toLowerCase().match(/\.(mp4|webm|ogg)$/))) {
        modalBody.innerHTML = '<video controls width="100%"><source src="' + url + '" type="' + fileType + '">Your browser does not support video playback.</video>';
    } else if (fileType && (fileType.startsWith('audio/') || fileName.toLowerCase().match(/\.(mp3|wav|ogg)$/))) {
        modalBody.innerHTML = '<audio controls width="100%"><source src="' + url + '" type="' + fileType + '">Your browser does not support audio playback.</audio>';
    } else if (fileName.toLowerCase().match(/\.(txt|json|xml|html|css|js|php)$/)) {
        fetch(url)
            .then(response => response.text())
            .then(text => {
                modalBody.innerHTML = '<pre class="bg-light p-3" style="max-height: 500px; overflow: auto;">' + text + '</pre>';
            })
            .catch(() => {
                modalBody.innerHTML = '<div class="text-center text-muted"><p>Unable to preview this file type.</p><a href="' + url + '" download class="btn btn-primary"><i class="bi bi-download"></i> Download File</a></div>';
            });
    } else {
        modalBody.innerHTML = '<div class="text-center text-muted"><p><i class="bi bi-file-earmark display-4"></i></p><p>Preview not available for this file type.</p><a href="' + url + '" download class="btn btn-primary"><i class="bi bi-download"></i> Download File</a></div>';
    }
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}
</script>
@endpush
