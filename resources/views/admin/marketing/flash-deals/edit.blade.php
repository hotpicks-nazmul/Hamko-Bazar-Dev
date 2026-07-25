@extends('admin.layouts.app')

@section('title', 'Edit Flash Deal')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Flash Deal</h4>
    <a href="{{ route('admin.marketing.flash-deals.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Flash Deals
    </a>
</div>

<form method="POST" action="{{ route('admin.marketing.flash-deals.update', $flashDeal->id) }}" id="itemForm">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $flashDeal->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Enter a catchy title for your flash deal</div>
                        @enderror
                    </div>

                    <!-- Date Range -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" id="start_date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $flashDeal->start_date->format('Y-m-d\TH:i')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" id="end_date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $flashDeal->end_date->format('Y-m-d\TH:i')) }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Deal will end at this time</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Banner Image -->
                    <div class="mt-3">
                        <label for="banner_image" class="form-label">Banner Image URL</label>
                        <input type="url" id="banner_image" name="banner_image" class="form-control @error('banner_image') is-invalid @enderror" value="{{ old('banner_image', $flashDeal->banner_image) }}" placeholder="https://example.com/banner.jpg">
                        @error('banner_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Recommended size: 1200x400px</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Styling Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Styling</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="background_color" class="form-label">Background Color</label>
                            <div class="input-group">
                                <input type="color" id="background_color" name="background_color" class="form-control form-control-color" value="{{ old('background_color', $flashDeal->background_color ?? '#ff0000') }}">
                                <input type="text" id="background_color_text" class="form-control" value="{{ old('background_color', $flashDeal->background_color ?? '#ff0000') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="text_color" class="form-label">Text Color</label>
                            <div class="input-group">
                                <input type="color" id="text_color" name="text_color" class="form-control form-control-color" value="{{ old('text_color', $flashDeal->text_color ?? '#ffffff') }}">
                                <input type="text" id="text_color_text" class="form-control" value="{{ old('text_color', $flashDeal->text_color ?? '#ffffff') }}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preview -->
                    <div class="mt-3">
                        <label class="form-label">Preview</label>
                        <div id="previewBanner" style="background-color: {{ $flashDeal->background_color ?? '#ff0000' }}; color: {{ $flashDeal->text_color ?? '#ffffff' }}; padding: 20px; border-radius: 8px; text-align: center;">
                            <strong style="font-size: 1.2rem;">{{ $flashDeal->title }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="inactive" {{ old('status', $flashDeal->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="active" {{ old('status', $flashDeal->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ old('status', $flashDeal->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Set to active to make the deal visible</div>
                        @enderror
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $flashDeal->is_featured) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">
                            <i class="bi bi-star text-warning me-1"></i> Featured
                        </label>
                        <div class="form-text">Featured deals appear on homepage</div>
                    </div>

                    <div class="mb-0">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $flashDeal->sort_order) }}" min="0">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Higher values show first</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Quick Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Quick Info</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Products:</span>
                        <strong>{{ $flashDeal->products->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Slug:</span>
                        <code>{{ $flashDeal->slug }}</code>
                    </div>
                    <hr>
                    <a href="{{ route('admin.marketing.flash-deals.products', $flashDeal->id) }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-box-seam me-1"></i> Manage Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.marketing.flash-deals.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="itemForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Flash Deal
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
    // Color sync
    const bgColorInput = document.getElementById('background_color');
    const bgColorText = document.getElementById('background_color_text');
    const textColorInput = document.getElementById('text_color');
    const textColorText = document.getElementById('text_color_text');
    const preview = document.getElementById('previewBanner');

    if (bgColorInput && bgColorText && preview) {
        bgColorInput.addEventListener('input', function() {
            bgColorText.value = this.value;
            preview.style.backgroundColor = this.value;
        });
        bgColorText.addEventListener('input', function() {
            bgColorInput.value = this.value;
            preview.style.backgroundColor = this.value;
        });
    }

    if (textColorInput && textColorText && preview) {
        textColorInput.addEventListener('input', function() {
            textColorText.value = this.value;
            preview.style.color = this.value;
        });
        textColorText.addEventListener('input', function() {
            textColorInput.value = this.value;
            preview.style.color = this.value;
        });
    }

    // Title preview
    const titleInput = document.getElementById('title');
    if (titleInput && preview) {
        titleInput.addEventListener('input', function() {
            preview.querySelector('strong').textContent = this.value || 'Your Flash Deal Title';
        });
    }
</script>
@endpush
