@extends('admin.layouts.app')

@section('title', 'Edit Store')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-shop me-2"></i>Edit Store</h4>
    <a href="{{ route('admin.multi-store.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Stores
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
            </div>
            <div class="card-body">
                <form id="storeForm" method="POST" action="{{ route('admin.multi-store.update', $store->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Store Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $store->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $store->slug) }}" placeholder="auto-generated-from-name">
                            <div class="form-text">URL-friendly version of the name</div>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Store Code</label>
                            <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $store->code) }}" placeholder="e.g., STORE-001">
                            <div class="form-text">Unique identifier for this store</div>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description', $store->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Location Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Location Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" form="storeForm" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $store->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" id="city" name="city" form="storeForm" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $store->city) }}">
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="state" class="form-label">State/Province</label>
                        <input type="text" id="state" name="state" form="storeForm" class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $store->state) }}">
                        @error('state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="postcode" class="form-label">Postal Code</label>
                        <input type="text" id="postcode" name="postcode" form="storeForm" class="form-control @error('postcode') is-invalid @enderror" value="{{ old('postcode', $store->postcode) }}">
                        @error('postcode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" id="country" name="country" form="storeForm" class="form-control @error('country') is-invalid @enderror" value="{{ old('country', $store->country) }}">
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" id="latitude" name="latitude" form="storeForm" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $store->latitude) }}" placeholder="-90 to 90">
                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" id="longitude" name="longitude" form="storeForm" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $store->longitude) }}" placeholder="-180 to 180">
                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Contact Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" form="storeForm" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $store->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" id="phone" name="phone" form="storeForm" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $store->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Person -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Contact Person</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="contact_person_name" class="form-label">Name</label>
                        <input type="text" id="contact_person_name" name="contact_person_name" form="storeForm" class="form-control @error('contact_person_name') is-invalid @enderror" value="{{ old('contact_person_name', $store->contact_person_name) }}">
                        @error('contact_person_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="contact_person_phone" class="form-label">Phone</label>
                        <input type="text" id="contact_person_phone" name="contact_person_phone" form="storeForm" class="form-control @error('contact_person_phone') is-invalid @enderror" value="{{ old('contact_person_phone', $store->contact_person_phone) }}">
                        @error('contact_person_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="contact_person_email" class="form-label">Email</label>
                        <input type="email" id="contact_person_email" name="contact_person_email" form="storeForm" class="form-control @error('contact_person_email') is-invalid @enderror" value="{{ old('contact_person_email', $store->contact_person_email) }}">
                        @error('contact_person_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Additional Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="opening_hours" class="form-label">Opening Hours</label>
                    <textarea id="opening_hours" name="opening_hours" form="storeForm" class="form-control @error('opening_hours') is-invalid @enderror" rows="2" placeholder="e.g., Mon-Fri: 9AM-6PM, Sat: 10AM-4PM">{{ old('opening_hours', $store->opening_hours) }}</textarea>
                    @error('opening_hours')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Status -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Status</h6>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" form="storeForm" {{ old('is_active', $store->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        <i class="bi bi-check-circle text-success me-1"></i> Active
                    </label>
                    <div class="form-text">Enable to make this store visible</div>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default" form="storeForm" {{ old('is_default', $store->is_default) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_default">
                        <i class="bi bi-star text-warning me-1"></i> Default Store
                    </label>
                    <div class="form-text">
                        @if($store->is_default)
                            This is the default store
                        @else
                            Set as the primary store
                        @endif
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_physical" name="is_physical" form="storeForm" {{ old('is_physical', $store->is_physical) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_physical">
                        <i class="bi bi-building text-info me-1"></i> Physical Store
                    </label>
                    <div class="form-text">Has a physical location</div>
                </div>
            </div>
        </div>

        <!-- Store Order -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Display Order</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" form="storeForm" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $store->sort_order) }}" min="0">
                    <div class="form-text">Lower numbers appear first</div>
                    @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Branding & Images -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-image me-2"></i>Branding</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    @if($store->logo)
                    <div class="mb-2">
                        <img src="{{ $store->logo_url }}" alt="Current Logo" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                        <span class="text-muted ms-2">Current logo</span>
                    </div>
                    @endif
                    <input type="file" id="logo" name="logo" form="storeForm" class="form-control @error('logo') is-invalid @enderror" accept="image/*" onchange="previewImage(this, 'logoPreview')">
                    <div class="form-text">Store logo. Max 2MB. Recommended: 400x400px</div>
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="logoPreview" class="mt-2"></div>
                </div>
                <div class="mb-3">
                    <label for="favicon" class="form-label">Favicon</label>
                    @if($store->favicon)
                    <div class="mb-2">
                        <img src="{{ $store->favicon_url }}" alt="Current Favicon" class="img-thumbnail" style="max-width: 32px; max-height: 32px;">
                        <span class="text-muted ms-2">Current favicon</span>
                    </div>
                    @endif
                    <input type="file" id="favicon" name="favicon" form="storeForm" class="form-control @error('favicon') is-invalid @enderror" accept="image/*" onchange="previewImage(this, 'faviconPreview')">
                    <div class="form-text">Browser tab icon. Max 1MB. Recommended: 32x32px</div>
                    @error('favicon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="faviconPreview" class="mt-2"></div>
                </div>
                <div class="mb-3">
                    <label for="banner" class="form-label">Banner</label>
                    @if($store->banner)
                    <div class="mb-2">
                        <img src="{{ $store->banner_url }}" alt="Current Banner" class="img-thumbnail" style="max-width: 200px;">
                        <span class="text-muted ms-2">Current banner</span>
                    </div>
                    @endif
                    <input type="file" id="banner" name="banner" form="storeForm" class="form-control @error('banner') is-invalid @enderror" accept="image/*" onchange="previewImage(this, 'bannerPreview')">
                    <div class="form-text">Store banner image. Max 5MB. Recommended: 1920x600px</div>
                    @error('banner')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="bannerPreview" class="mt-2"></div>
                </div>
            </div>
        </div>

        <!-- Colors -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Colors</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="primary_color" class="form-label">Primary Color</label>
                    <div class="input-group">
                        <input type="color" id="primary_color" name="primary_color" form="storeForm" class="form-control form-control-color @error('primary_color') is-invalid @enderror" value="{{ old('primary_color', $store->primary_color ?? '#4f46e5') }}">
                        <input type="text" id="primary_color_text" class="form-control" value="{{ old('primary_color', $store->primary_color ?? '#4f46e5') }}" oninput="syncColor('primary_color', this.value)">
                    </div>
                    <div class="form-text">Main brand color</div>
                    @error('primary_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="secondary_color" class="form-label">Secondary Color</label>
                    <div class="input-group">
                        <input type="color" id="secondary_color" name="secondary_color" form="storeForm" class="form-control form-control-color @error('secondary_color') is-invalid @enderror" value="{{ old('secondary_color', $store->secondary_color ?? '#7c3aed') }}">
                        <input type="text" id="secondary_color_text" class="form-control" value="{{ old('secondary_color', $store->secondary_color ?? '#7c3aed') }}" oninput="syncColor('secondary_color', this.value)">
                    </div>
                    <div class="form-text">Accent/secondary brand color</div>
                    @error('secondary_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- SEO -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-search me-2"></i>SEO</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input type="text" id="meta_title" name="meta_title" form="storeForm" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $store->meta_title) }}">
                    @error('meta_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" form="storeForm" class="form-control @error('meta_description') is-invalid @enderror" rows="2">{{ old('meta_description', $store->meta_description) }}</textarea>
                    @error('meta_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                    <input type="text" id="meta_keywords" name="meta_keywords" form="storeForm" class="form-control @error('meta_keywords') is-invalid @enderror" value="{{ old('meta_keywords', $store->meta_keywords) }}" placeholder="keyword1, keyword2, keyword3">
                    @error('meta_keywords')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.multi-store.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="storeForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Store
    </button>
</div>
@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-generate slug from name
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    nameInput.addEventListener('input', function() {
        if (!slugInput.dataset.modified) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    });
    
    slugInput.addEventListener('input', function() {
        slugInput.dataset.modified = 'true';
    });

    // Image preview function
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        preview.innerHTML = '';
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 150px; max-height: 100px;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Sync color picker with text input
    function syncColor(colorId, value) {
        const colorInput = document.getElementById(colorId);
        const textInput = document.getElementById(colorId + '_text');
        if (/^#[0-9A-Fa-f]{6}$/i.test(value)) {
            colorInput.value = value;
            textInput.value = value;
        }
    }

    // Sync color picker changes to text input
    document.getElementById('primary_color').addEventListener('input', function() {
        document.getElementById('primary_color_text').value = this.value;
    });
    document.getElementById('secondary_color').addEventListener('input', function() {
        document.getElementById('secondary_color_text').value = this.value;
    });
</script>
@endpush
