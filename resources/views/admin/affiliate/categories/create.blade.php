@extends('admin.layouts.app')

@section('title', 'Create Affiliate Category')

@section('content')
<!-- Header with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create Affiliate Category</h4>
    <a href="{{ route('admin.affiliate.categories.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Categories
    </a>
</div>

<!-- Error Alert -->
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <!-- Main Form Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-folder me-2"></i>Category Details</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.affiliate.categories.store') }}" enctype="multipart/form-data" id="categoryForm">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g., Electronics">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="slug" class="form-label">Slug</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="auto-generated">
                                <button type="button" class="btn btn-outline-secondary" onclick="generateSlug()" title="Generate slug from name">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                            </div>
                            <div class="form-text">Leave empty to auto-generate from name. Uses lowercase letters, numbers, and hyphens.</div>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Brief description of this affiliate category...">{{ old('description') }}</textarea>
                        <div class="form-text">Optional description to help identify this category</div>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Category Image</label>
                        <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                        <div class="form-text">Recommended size: 200x200 pixels. Max file size: 2MB. Supported: JPG, PNG, GIF, WebP</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Settings Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Settings</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="commission_rate" class="form-label">Commission Rate (%) <span class="text-danger">*</span></label>
                    <input type="number" id="commission_rate" name="commission_rate" class="form-control @error('commission_rate') is-invalid @enderror" step="0.01" min="0" max="100" value="{{ old('commission_rate', '5.00') }}" required form="categoryForm">
                    <div class="form-text">Percentage of commission for affiliates</div>
                    @error('commission_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="status" name="status" value="active" {{ old('status', 'active') === 'active' ? 'checked' : '' }} form="categoryForm">
                    <label class="form-check-label" for="status">
                        <i class="bi bi-check-circle text-success me-1"></i> Active
                    </label>
                    <div class="form-text">Enable this category for affiliate use</div>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Help</h6>
            </div>
            <div class="card-body">
                <p class="mb-2 small"><strong>What is an Affiliate Category?</strong></p>
                <p class="small text-muted mb-0">Categories help organize affiliate products. Each category can have its own commission rate, making it easy to manage different payout structures.</p>
            </div>
        </div>
    </div>
</div>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.affiliate.categories.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="categoryForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Category
    </button>
</div>
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
    // Generate slug from name
    function generateSlug() {
        const name = document.getElementById('name').value;
        if (name) {
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            document.getElementById('slug').value = slug;
        }
    }

    // Auto-generate slug on name change if slug is empty
    document.getElementById('name').addEventListener('blur', function() {
        const slugField = document.getElementById('slug');
        if (!slugField.value && this.value) {
            generateSlug();
        }
    });

    // Preview image before upload
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
