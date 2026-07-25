@extends('admin.layouts.app')

@section('title', 'Create Banner')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create Banner</h4>
    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Banners
    </a>
</div>

<form id="bannerForm" method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
    @csrf
    
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
                        <label for="title" class="form-label">Banner Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter a descriptive title for the banner</div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Optional description or subtitle for the banner</div>
                    </div>

                    <!-- Image -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Banner Image <span class="text-danger">*</span></label>
                        <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror"
                               accept="image/*" required>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Recommended size: 1200x400 pixels. Max file size: 2MB.</div>
                        
                        <!-- Image Preview -->
                        <div id="imagePreview" class="mt-2" style="display: none;">
                            <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    </div>

                    <!-- Link -->
                    <div class="mb-3">
                        <label for="link" class="form-label">Banner Link</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                            <input type="text" id="link" name="link" class="form-control @error('link') is-invalid @enderror"
                                   value="{{ old('link') }}" placeholder="https://example.com">
                        </div>
                        @error('link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">URL to redirect when banner is clicked</div>
                    </div>

                    <!-- Button Text -->
                    <div class="mb-3">
                        <label for="button_text" class="form-label">Button Text</label>
                        <input type="text" id="button_text" name="button_text" class="form-control @error('button_text') is-invalid @enderror"
                               value="{{ old('button_text') }}" placeholder="Shop Now">
                        @error('button_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Text to display on the button (optional)</div>
                    </div>
                </div>
            </div>

            <!-- Styling Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Styling Options</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Text Color -->
                        <div class="col-md-4 mb-3">
                            <label for="text_color" class="form-label">Text Color</label>
                            <div class="input-group">
                                <input type="color" id="text_color" name="text_color" class="form-control form-control-color"
                                       value="{{ old('text_color', '#ffffff') }}"
                                       oninput="document.getElementById('text_color_text').value = this.value">
                                <input type="text" id="text_color_text" class="form-control"
                                       value="{{ old('text_color', '#ffffff') }}"
                                       oninput="document.getElementById('text_color').value = this.value">
                            </div>
                        </div>

                        <!-- Button Color -->
                        <div class="col-md-4 mb-3">
                            <label for="button_color" class="form-label">Button Color</label>
                            <div class="input-group">
                                <input type="color" id="button_color" name="button_color" class="form-control form-control-color"
                                       value="{{ old('button_color', '#000000') }}"
                                       oninput="document.getElementById('button_color_text').value = this.value">
                                <input type="text" id="button_color_text" class="form-control"
                                       value="{{ old('button_color', '#000000') }}"
                                       oninput="document.getElementById('button_color').value = this.value">
                            </div>
                        </div>

                        <!-- Background Color -->
                        <div class="col-md-4 mb-3">
                            <label for="background_color" class="form-label">Background Color</label>
                            <div class="input-group">
                                <input type="color" id="background_color" name="background_color" class="form-control form-control-color"
                                       value="{{ old('background_color', '#ffffff') }}"
                                       oninput="document.getElementById('background_color_text').value = this.value">
                                <input type="text" id="background_color_text" class="form-control"
                                       value="{{ old('background_color', '#ffffff') }}"
                                       oninput="document.getElementById('background_color').value = this.value">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Position Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Position</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="position" class="form-label">Banner Position <span class="text-danger">*</span></label>
                        <select id="position" name="position" class="form-select @error('position') is-invalid @enderror" required>
                            @foreach($positions as $key => $label)
                                <option value="{{ $key }}" {{ old('position') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Select where this banner should be displayed</div>
                    </div>

                    <!-- Sort Order -->
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                               value="{{ old('sort_order', 0) }}" min="0">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Lower numbers appear first</div>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Status</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            <i class="bi bi-check-circle text-success me-1"></i> Active
                        </label>
                        <div class="form-text">Enable to make this banner visible on the site</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="bannerForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Banner
    </button>
</div>

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Image preview
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
@endsection
