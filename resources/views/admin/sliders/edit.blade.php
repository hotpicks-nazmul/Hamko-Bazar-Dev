@extends('admin.layouts.app')

@section('title', 'Edit Slider')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="bi bi-pencil text-primary me-2"></i> Edit Slider
                        </h4>
                        <p class="text-muted mb-0 small">Modify slider details</p>
                    </div>
                    <a href="{{ route('admin.sliders.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('admin.sliders.update', $slider) }}" method="POST" enctype="multipart/form-data" id="slider-form">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Card 1: Slider Content -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-card-text me-2"></i>Slider Content</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $slider->title) }}" required>
                        <div class="form-text">This will be displayed as the main heading on the slide.</div>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="subtitle" class="form-label">Subtitle</label>
                        <input type="text" id="subtitle" name="subtitle" class="form-control @error('subtitle') is-invalid @enderror" value="{{ old('subtitle', $slider->subtitle) }}">
                        <div class="form-text">Optional subtitle displayed above the title.</div>
                        @error('subtitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="link" class="form-label">Link URL</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                            <input type="url" id="link" name="link" class="form-control @error('link') is-invalid @enderror" value="{{ old('link', $slider->link) }}">
                        </div>
                        @error('link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="mb-3"><i class="bi bi-hand-index me-2"></i>Button Configuration</h6>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="button_text" class="form-label">Button Text</label>
                            <input type="text" id="button_text" name="button_text" class="form-control @error('button_text') is-invalid @enderror" value="{{ old('button_text', $slider->button_text) }}">
                            @error('button_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="button_color" class="form-label">Button Color</label>
                            <div class="input-group">
                                <input type="color" id="button_color_picker" class="form-control form-control-color" value="{{ old('button_color', $slider->button_color ?? '#D4AF37') }}" style="width: 50px; padding: 2px;">
                                <input type="text" id="button_color" name="button_color" class="form-control @error('button_color') is-invalid @enderror" placeholder="#D4AF37" value="{{ old('button_color', $slider->button_color ?? '#D4AF37') }}">
                            </div>
                            @error('button_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="button_text_color" class="form-label">Text Color</label>
                            <div class="input-group">
                                <input type="color" id="button_text_color_picker" class="form-control form-control-color" value="{{ old('button_text_color', $slider->button_text_color ?? '#FFFFFF') }}" style="width: 50px; padding: 2px;">
                                <input type="text" id="button_text_color" name="button_text_color" class="form-control @error('button_text_color') is-invalid @enderror" placeholder="#FFFFFF" value="{{ old('button_text_color', $slider->button_text_color ?? '#FFFFFF') }}">
                            </div>
                            @error('button_text_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label for="button_icon" class="form-label">Icon</label>
                            <select id="button_icon" name="button_icon" class="form-select @error('button_icon') is-invalid @enderror">
                                <option value="bi-arrow-right" {{ (old('button_icon', $slider->button_icon ?? 'bi-arrow-right')) == 'bi-arrow-right' ? 'selected' : '' }}>Arrow Right</option>
                                <option value="bi-cart3" {{ old('button_icon', $slider->button_icon) == 'bi-cart3' ? 'selected' : '' }}>Cart</option>
                                <option value="bi-bag" {{ old('button_icon', $slider->button_icon) == 'bi-bag' ? 'selected' : '' }}>Bag</option>
                                <option value="bi-chevron-right" {{ old('button_icon', $slider->button_icon) == 'bi-chevron-right' ? 'selected' : '' }}>Chevron Right</option>
                                <option value="bi-arrow-left" {{ old('button_icon', $slider->button_icon) == 'bi-arrow-left' ? 'selected' : '' }}>Arrow Left</option>
                                <option value="bi-star" {{ old('button_icon', $slider->button_icon) == 'bi-star' ? 'selected' : '' }}>Star</option>
                                <option value="bi-heart" {{ old('button_icon', $slider->button_icon) == 'bi-heart' ? 'selected' : '' }}>Heart</option>
                                <option value="bi-hand-thumbs-up" {{ old('button_icon', $slider->button_icon) == 'bi-hand-thumbs-up' ? 'selected' : '' }}>Thumbs Up</option>
                                <option value="bi-box-seam" {{ old('button_icon', $slider->button_icon) == 'bi-box-seam' ? 'selected' : '' }}>Package</option>
                                <option value="bi-shop" {{ old('button_icon', $slider->button_icon) == 'bi-shop' ? 'selected' : '' }}>Shop</option>
                            </select>
                            @error('button_icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="button_icon_color" class="form-label">Icon Color</label>
                            <div class="input-group">
                                <input type="color" id="button_icon_color_picker" class="form-control form-control-color" value="{{ old('button_icon_color', $slider->button_icon_color ?? '#FFFFFF') }}" style="width: 50px; padding: 2px;">
                                <input type="text" id="button_icon_color" name="button_icon_color" class="form-control @error('button_icon_color') is-invalid @enderror" placeholder="#FFFFFF" value="{{ old('button_icon_color', $slider->button_icon_color ?? '#FFFFFF') }}">
                            </div>
                            @error('button_icon_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Card 2: Slider Image -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-image me-2"></i>Slider Image</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        @php
                            $imageUrl = $slider->image;
                            if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                $imageUrl = '/storage/' . $imageUrl;
                            }
                        @endphp
                        @if($imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $slider->title }}" class="img-fluid rounded mb-3" style="max-height: 150px;">
                        @else
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center mb-3" style="height: 150px;">
                            <i class="bi bi-image text-white" style="font-size: 2rem;"></i>
                        </div>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">New Image</label>
                        <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        <div class="form-text">Leave empty to keep current image.</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" class="mb-3" style="display: none;">
                        <img id="previewImage" src="" alt="Preview" class="img-fluid rounded" style="max-height: 150px;">
                    </div>
                </div>
            </div>
            
            <!-- Card 3: Status -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-toggle-on me-2"></i>Status</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $slider->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            <i class="bi bi-check-circle text-success me-1"></i> Active
                        </label>
                        <div class="form-text">Inactive sliders won't be displayed on the frontend.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.sliders.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="button" class="btn btn-outline-danger floating-reset-btn" onclick="confirmDelete()">
        <i class="bi bi-trash me-1"></i> Delete
    </button>
    <button type="submit" form="slider-form" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Slider
    </button>
</div>

<!-- Hidden delete form -->
<form id="deleteForm" action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<style>
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-scroll to first error field
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
    });

    // Confirm delete action
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this slider?')) {
            document.getElementById('deleteForm').submit();
        }
    }

    // Image preview functionality
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
    
    // Sync button color picker with text input
    const colorPicker = document.getElementById('button_color_picker');
    const colorInput = document.getElementById('button_color');
    
    if (colorPicker && colorInput) {
        colorPicker.addEventListener('input', function() {
            colorInput.value = this.value;
        });
        colorInput.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                colorPicker.value = this.value;
            }
        });
    }
    
    // Sync button text color picker with text input
    const textColorPicker = document.getElementById('button_text_color_picker');
    const textColorInput = document.getElementById('button_text_color');
    
    if (textColorPicker && textColorInput) {
        textColorPicker.addEventListener('input', function() {
            textColorInput.value = this.value;
        });
        textColorInput.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                textColorPicker.value = this.value;
            }
        });
    }
    
    // Sync button icon color picker with text input
    const iconColorPicker = document.getElementById('button_icon_color_picker');
    const iconColorInput = document.getElementById('button_icon_color');
    
    if (iconColorPicker && iconColorInput) {
        iconColorPicker.addEventListener('input', function() {
            iconColorInput.value = this.value;
        });
        iconColorInput.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                iconColorPicker.value = this.value;
            }
        });
    }
</script>
@endpush
