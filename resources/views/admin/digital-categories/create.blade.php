@extends('admin.layouts.app')

@section('title', 'Create Digital Category')

@section('content')
<!-- Back Button -->
<div class="mb-3">
    <a href="{{ route('admin.digital-categories.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Categories
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form id="categoryForm" method="POST" action="{{ route('admin.digital-categories.store') }}" enctype="multipart/form-data">
            @csrf
            
            <!-- Basic Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="categoryName" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="categorySlug" class="form-control" value="{{ old('slug') }}" placeholder="Auto-generated">
                            <small class="text-muted">Leave empty for auto-generation</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" class="form-select">
                                <option value="">None (Root Category)</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" {{ old('parent_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Icon Class</label>
                            <input type="text" name="icon" class="form-control" value="{{ old('icon') }}" placeholder="bi bi-folder">
                            <small class="text-muted">Bootstrap Icons class (e.g., bi bi-software, bi bi-file-earmark)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="order" class="form-control" value="{{ old('order', 0) }}" min="0">
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Category description...">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Recommended size: 200x200px (JPG, PNG, WebP)</small>
                    </div>
                </div>
            </div>
            
            <!-- SEO Settings -->
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
        </form>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Category Icons</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Common icons for digital products:</p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-light text-dark"><i class="bi bi-software me-1"></i> bi-software</span>
                    <span class="badge bg-light text-dark"><i class="bi bi-file-earmark me-1"></i> bi-file-earmark</span>
                    <span class="badge bg-light text-dark"><i class="bi bi-book me-1"></i> bi-book</span>
                    <span class="badge bg-light text-dark"><i class="bi bi-music-note me-1"></i> bi-music-note</span>
                    <span class="badge bg-light text-dark"><i class="bi bi-camera-video me-1"></i> bi-camera-video</span>
                    <span class="badge bg-light text-dark"><i class="bi bi-controller me-1"></i> bi-controller</span>
                    <span class="badge bg-light text-dark"><i class="bi bi-phone me-1"></i> bi-phone</span>
                    <span class="badge bg-light text-dark"><i class="bi bi-palette me-1"></i> bi-palette</span>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Use descriptive category names
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Create subcategories for better organization
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Add relevant icons for visual appeal
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Set proper SEO meta tags
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.digital-categories.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="categoryForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Category
    </button>
</div>
@endsection

@push('scripts')
<script>
// Auto-generate slug from name
document.getElementById('categoryName').addEventListener('input', function() {
    const name = this.value;
    const slugInput = document.getElementById('categorySlug');
    
    if (!slugInput.value || slugInput.dataset.auto === 'true') {
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        slugInput.value = slug;
        slugInput.dataset.auto = 'true';
    }
});

document.getElementById('categorySlug').addEventListener('input', function() {
    this.dataset.auto = 'false';
});
</script>
@endpush
