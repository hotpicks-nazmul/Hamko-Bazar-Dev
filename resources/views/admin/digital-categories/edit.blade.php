@extends('admin.layouts.app')

@section('title', 'Edit Digital Category')

@section('content')
<!-- Back Button -->
<div class="mb-3">
    <a href="{{ route('admin.digital-categories.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Categories
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form id="categoryForm" method="POST" action="{{ route('admin.digital-categories.update', $digitalCategory->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="categoryName" class="form-control" value="{{ old('name', $digitalCategory->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="categorySlug" class="form-control" value="{{ old('slug', $digitalCategory->slug) }}">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" class="form-select">
                                <option value="">None (Root Category)</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" {{ old('parent_id', $digitalCategory->parent_id) == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', $digitalCategory->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $digitalCategory->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Icon Class</label>
                            <input type="text" name="icon" class="form-control" value="{{ old('icon', $digitalCategory->icon) }}" placeholder="bi bi-folder">
                            <small class="text-muted">Bootstrap Icons class (e.g., bi bi-software, bi bi-file-earmark)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="order" class="form-control" value="{{ old('order', $digitalCategory->order) }}" min="0">
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Category description...">{{ old('description', $digitalCategory->description) }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        @if($digitalCategory->image)
                            @php
                                $imgPath = ltrim($digitalCategory->image, '/');
                                if (!str_starts_with($imgPath, 'http') && !str_starts_with($imgPath, 'storage/')) {
                                    $imgPath = '/storage/' . $imgPath;
                                } elseif (str_starts_with($imgPath, 'storage/')) {
                                    $imgPath = '/' . $imgPath;
                                }
                            @endphp
                            <div class="image-upload-preview mb-2" id="imagePreview">
                                <img src="{{ $imgPath }}" alt="{{ $digitalCategory->name }}" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeCategoryImage()">
                                    <i class="bi bi-trash me-1"></i> Remove Image
                                </button>
                            </div>
                        @else
                            <div class="image-upload-preview mb-2" id="imagePreview" style="display: none;">
                                <img src="" alt="Preview" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeCategoryImage()">
                                    <i class="bi bi-trash me-1"></i> Remove Image
                                </button>
                            </div>
                        @endif
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewCategoryImage(this)">
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
                        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $digitalCategory->meta_title) }}" maxlength="60">
                        <small class="text-muted">Recommended: 50-60 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="2" maxlength="160">{{ old('meta_description', $digitalCategory->meta_description) }}</textarea>
                        <small class="text-muted">Recommended: 150-160 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $digitalCategory->meta_keywords) }}" placeholder="keyword1, keyword2, keyword3">
                    </div>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h4 mb-0 text-primary">{{ $digitalCategory->product_count }}</div>
                                <small class="text-muted">Products</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h4 mb-0 text-info">{{ $digitalCategory->children->count() }}</div>
                                <small class="text-muted">Subcategories</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h4 mb-0 text-secondary">{{ $digitalCategory->order }}</div>
                                <small class="text-muted">Display Order</small>
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
        
        @if($digitalCategory->children->count() > 0)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Subcategories</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($digitalCategory->children as $child)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $child->name }}
                            <span class="badge {{ $child->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $child->status }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.digital-categories.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="categoryForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Category
    </button>
</div>
@endsection

@push('scripts')
<script>
function previewCategoryImage(input) {
    const preview = document.getElementById('imagePreview');
    const img = preview.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeCategoryImage() {
    if (confirm('Are you sure you want to remove this image?')) {
        fetch('{{ route("admin.digital-categories.delete-image", $digitalCategory->id) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const preview = document.getElementById('imagePreview');
                preview.style.display = 'none';
                preview.querySelector('img').src = '';
                document.querySelector('input[name="image"]').value = '';
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

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
