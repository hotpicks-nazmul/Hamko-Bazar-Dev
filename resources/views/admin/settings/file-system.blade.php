@extends('admin.layouts.app')

@section('title', 'File System & Cache')

@push('styles')
<style>
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="bi bi-hdd text-primary me-2"></i> File System & Cache
                        </h4>
                        <p class="text-muted mb-0 small">Manage storage settings, file uploads, image optimization, and cache configuration</p>
                    </div>
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form id="fileSystemForm" method="POST" action="{{ route('admin.settings.file-system.update') }}">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <!-- File Upload Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-upload me-2"></i>File Upload Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="max_upload_size" class="form-label">Max Upload Size (KB)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-hdd"></i></span>
                                <input type="number" id="max_upload_size" name="max_upload_size" 
                                       class="form-control" value="{{ $settings['max_upload_size'] ?? 5120 }}" min="100" max="102400">
                            </div>
                            <div class="form-text">Maximum file size for uploads (in KB). Default: 5120 KB (5 MB)</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="allowed_file_types" class="form-label">Allowed File Types</label>
                            <input type="text" id="allowed_file_types" name="allowed_file_types" 
                                   class="form-control" value="{{ $settings['allowed_file_types'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,mp4,mp3' }}">
                            <div class="form-text">Comma-separated list of allowed extensions</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="max_image_width" class="form-label">Max Image Width (px)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-arrows-angle-expand"></i></span>
                                <input type="number" id="max_image_width" name="max_image_width" 
                                       class="form-control" value="{{ $settings['max_image_width'] ?? 2000 }}" min="100" max="10000">
                            </div>
                            <div class="form-text">Maximum width for uploaded images</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="max_image_height" class="form-label">Max Image Height (px)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-arrows-angle-expand"></i></span>
                                <input type="number" id="max_image_height" name="max_image_height" 
                                       class="form-control" value="{{ $settings['max_image_height'] ?? 2000 }}" min="100" max="10000">
                            </div>
                            <div class="form-text">Maximum height for uploaded images</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-image me-2"></i>Image Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="image_quality" class="form-label">Image Quality (%)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-star"></i></span>
                                <input type="number" id="image_quality" name="image_quality" 
                                       class="form-control" value="{{ $settings['image_quality'] ?? 85 }}" min="10" max="100">
                            </div>
                            <div class="form-text">Image compression quality (10-100). Higher = better quality but larger file</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="thumbnail_width" class="form-label">Thumbnail Width (px)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-grid"></i></span>
                                <input type="number" id="thumbnail_width" name="thumbnail_width" 
                                       class="form-control" value="{{ $settings['thumbnail_width'] ?? 150 }}" min="50" max="500">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="thumbnail_height" class="form-label">Thumbnail Height (px)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-grid"></i></span>
                                <input type="number" id="thumbnail_height" name="thumbnail_height" 
                                       class="form-control" value="{{ $settings['thumbnail_height'] ?? 150 }}" min="50" max="500">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="watermark_position" class="form-label">Watermark Position</label>
                            <select id="watermark_position" name="watermark_position" class="form-select">
                                <option value="center" {{ ($settings['watermark_position'] ?? '') == 'center' ? 'selected' : '' }}>Center</option>
                                <option value="top-left" {{ ($settings['watermark_position'] ?? '') == 'top-left' ? 'selected' : '' }}>Top Left</option>
                                <option value="top-right" {{ ($settings['watermark_position'] ?? '') == 'top-right' ? 'selected' : '' }}>Top Right</option>
                                <option value="bottom-left" {{ ($settings['watermark_position'] ?? '') == 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                                <option value="bottom-right" {{ ($settings['watermark_position'] ?? 'bottom-right') == 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="thumbnail_enabled" name="thumbnail_enabled" 
                                       {{ ($settings['thumbnail_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="thumbnail_enabled">
                                    <i class="bi bi-check-circle text-success me-1"></i> Enable Thumbnails
                                </label>
                                <div class="form-text">Automatically generate thumbnails for uploaded images</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="watermark_enabled" name="watermark_enabled" 
                                       {{ ($settings['watermark_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="watermark_enabled">
                                    <i class="bi bi-check-circle text-success me-1"></i> Enable Watermark
                                </label>
                                <div class="form-text">Add watermark to uploaded images</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cache Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-lightning me-2"></i>Cache Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cache_driver" class="form-label">Cache Driver</label>
                            <select id="cache_driver" name="cache_driver" class="form-select">
                                <option value="file" {{ ($settings['cache_driver'] ?? 'file') == 'file' ? 'selected' : '' }}>File</option>
                                <option value="redis" {{ ($settings['cache_driver'] ?? '') == 'redis' ? 'selected' : '' }}>Redis</option>
                                <option value="memcached" {{ ($settings['cache_driver'] ?? '') == 'memcached' ? 'selected' : '' }}>Memcached</option>
                                <option value="array" {{ ($settings['cache_driver'] ?? '') == 'array' ? 'selected' : '' }}>Array (No Cache)</option>
                            </select>
                            <div class="form-text">Select cache driver for application caching</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cache_ttl" class="form-label">Cache TTL (seconds)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                <input type="number" id="cache_ttl" name="cache_ttl" 
                                       class="form-control" value="{{ $settings['cache_ttl'] ?? 3600 }}" min="60" max="86400">
                            </div>
                            <div class="form-text">Default cache time-to-live (60-86400 seconds)</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="query_cache_ttl" class="form-label">Query Cache TTL (seconds)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-database"></i></span>
                                <input type="number" id="query_cache_ttl" name="query_cache_ttl" 
                                       class="form-control" value="{{ $settings['query_cache_ttl'] ?? 300 }}" min="60" max="3600">
                            </div>
                            <div class="form-text">Cache duration for database queries</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enable_query_cache" name="enable_query_cache" 
                                       {{ ($settings['enable_query_cache'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_query_cache">
                                    <i class="bi bi-check-circle text-success me-1"></i> Enable Query Cache
                                </label>
                                <div class="form-text">Cache frequently queried database results</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Storage Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-folder me-2"></i>Storage Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="storage_disk" class="form-label">Default Storage Disk</label>
                            <select id="storage_disk" name="storage_disk" class="form-select">
                                <option value="public" {{ ($settings['storage_disk'] ?? 'public') == 'public' ? 'selected' : '' }}>Public</option>
                                <option value="local" {{ ($settings['storage_disk'] ?? '') == 'local' ? 'selected' : '' }}>Local</option>
                                <option value="s3" {{ ($settings['storage_disk'] ?? '') == 's3' ? 'selected' : '' }}>Amazon S3</option>
                            </select>
                            <div class="form-text">Default filesystem disk for file storage</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cloud_driver" class="form-label">Cloud Storage Driver</label>
                            <select id="cloud_driver" name="cloud_driver" class="form-select">
                                <option value="s3" {{ ($settings['cloud_driver'] ?? 's3') == 's3' ? 'selected' : '' }}>Amazon S3</option>
                                <option value="digitalocean" {{ ($settings['cloud_driver'] ?? '') == 'digitalocean' ? 'selected' : '' }}>DigitalOcean Spaces</option>
                                <option value="wasabi" {{ ($settings['cloud_driver'] ?? '') == 'wasabi' ? 'selected' : '' }}>Wasabi</option>
                            </select>
                            <div class="form-text">Cloud storage provider for remote file storage</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enable_cloud_storage" name="enable_cloud_storage" 
                                       {{ ($settings['enable_cloud_storage'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_cloud_storage">
                                    <i class="bi bi-check-circle text-success me-1"></i> Enable Cloud Storage
                                </label>
                                <div class="form-text">Use cloud storage instead of local storage</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Optimization Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-speedometer2 me-2"></i>Optimization Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="lazy_load_images" name="lazy_load_images" 
                                       {{ ($settings['lazy_load_images'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="lazy_load_images">
                                    <i class="bi bi-check-circle text-success me-1"></i> Lazy Load Images
                                </label>
                                <div class="form-text">Load images only when they come into viewport</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="optimize_images" name="optimize_images" 
                                       {{ ($settings['optimize_images'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="optimize_images">
                                    <i class="bi bi-check-circle text-success me-1"></i> Optimize Images
                                </label>
                                <div class="form-text">Automatically optimize uploaded images</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enable_static_cache" name="enable_static_cache" 
                                       {{ ($settings['enable_static_cache'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_static_cache">
                                    <i class="bi bi-check-circle text-success me-1"></i> Enable Static Cache
                                </label>
                                <div class="form-text">Cache static assets for better performance</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Cache Management -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-trash me-2"></i>Cache Management</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Clear different types of cache to refresh your application.</p>
                    
                    <form action="{{ route('admin.settings.file-system.clear-cache') }}" method="POST" class="mb-3" id="clearCacheForm">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100" id="clearCacheBtn">
                            <i class="bi bi-trash me-1"></i> Clear All Cache
                        </button>
                    </form>

                    <hr>
                    
                    <h6 class="mb-3">Cache Information</h6>
                    <div class="d-flex flex-column gap-2">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-box-seam text-primary me-2"></i>
                                    <div>
                                        <div class="fw-medium small">Application Cache</div>
                                        <div class="text-muted small">Config, routes, views</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-code text-success me-2"></i>
                                    <div>
                                        <div class="fw-medium small">View Cache</div>
                                        <div class="text-muted small">Compiled templates</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-signpost-2 text-warning me-2"></i>
                                    <div>
                                        <div class="fw-medium small">Route Cache</div>
                                        <div class="text-muted small">Cached routes</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-gear text-info me-2"></i>
                                    <div>
                                        <div class="fw-medium small">Config Cache</div>
                                        <div class="text-muted small">Merged config</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Storage Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-folder2-open me-2"></i>Storage Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                            <i class="bi bi-folder2 text-primary fs-5"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Public Storage</div>
                            <div class="fw-medium small">/storage/app/public</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 p-2 rounded me-3">
                            <i class="bi bi-cloud-upload text-success fs-5"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Uploads Folder</div>
                            <div class="fw-medium small">/storage/app/public/uploads</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-1"></i> 
                        <small>Configure storage disks in <code>config/filesystems.php</code></small>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.settings.order-configuration') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-bag-check me-1"></i> Order Configuration
                        </a>
                        <a href="{{ route('admin.settings.email') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-envelope me-1"></i> Email Settings
                        </a>
                        <a href="{{ route('admin.settings.general') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-gear me-1"></i> General Settings
                        </a>
                        <a href="{{ route('admin.settings.shipping') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-truck me-1"></i> Shipping Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="fileSystemForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save Settings
    </button>
</div>
@endsection

@push('scripts')
<script>
    // Clear cache form submission with loading state
    document.addEventListener('DOMContentLoaded', function() {
        const clearCacheBtn = document.getElementById('clearCacheBtn');
        const clearCacheForm = document.getElementById('clearCacheForm');
        
        if (clearCacheBtn && clearCacheForm) {
            clearCacheBtn.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to clear all cache? This will temporarily slow down the application.')) {
                    e.preventDefault();
                    return;
                }
                
                // Show loading state
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Clearing...';
                
                // Submit the form
                clearCacheForm.submit();
            });
        }
    });
</script>
@endpush
