@extends('admin.layouts.app')

@section('title', 'Edit Blog Post')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Blog Post</h4>
    <div class="d-flex gap-2">
        @if($blog->status === 'published')
        <a href="{{ route('blogs.show', $blog->slug) }}" target="_blank" class="btn btn-outline-primary">
            <i class="bi bi-eye me-1"></i> View Post
        </a>
        @endif
        <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Posts
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Basic Info Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Basic Information</h6>
            </div>
            <div class="card-body">
                <form id="itemForm" method="POST" action="{{ route('admin.blogs.update', $blog->slug) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $blog->title) }}" placeholder="Enter blog post title" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <div class="input-group">
                            <span class="input-group-text">/</span>
                            <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" 
                                   value="{{ old('slug', $blog->slug) }}" placeholder="url-friendly-slug">
                        </div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Excerpt</label>
                        <textarea id="excerpt" name="excerpt" class="form-control" rows="2" 
                                  placeholder="Brief summary of the post">{{ old('excerpt', $blog->excerpt) }}</textarea>
                        <div class="form-text">A short description for previews (optional)</div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Content Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-body-text me-2"></i>Content</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="blog-content" class="form-label">Content <span class="text-danger">*</span></label>
                    <textarea name="content" id="blog-content" class="form-control @error('content') is-invalid @enderror" 
                              rows="15" placeholder="Write your blog content here..." required>{{ old('content', $blog->content) }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- SEO Settings Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-search me-2"></i>SEO Settings</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input type="text" id="meta_title" name="meta_title" class="form-control" 
                           value="{{ old('meta_title', $blog->meta_title) }}" 
                           placeholder="SEO title (defaults to post title)" form="itemForm">
                    <div class="form-text">Leave empty to use the post title</div>
                </div>
                
                <div class="mb-3">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" class="form-control" rows="2" 
                              placeholder="Brief description for search engines" form="itemForm">{{ old('meta_description', $blog->meta_description) }}</textarea>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Featured Image Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-image me-2"></i>Featured Image</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="file" name="featured_image" class="form-control" accept="image/*" form="itemForm">
                    <div class="form-text">Recommended size: 800x400px</div>
                </div>
                @if($blog->featured_image)
                @php
                    $imageUrl = $blog->featured_image;
                    if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                        $imageUrl = '/storage/' . $imageUrl;
                    }
                @endphp
                <div class="text-center">
                    <img src="{{ $imageUrl }}" alt="Current image" class="img-thumbnail" style="max-height: 150px;">
                    <small class="text-muted d-block mt-1">Current image. Upload new to replace.</small>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Category Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-folder me-2"></i>Category</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <select name="category_id" id="category_id" class="form-select" form="itemForm">
                        <option value="">-- Select Category --</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $blog->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="tags" class="form-label">Tags</label>
                    <input type="text" name="tags" id="tags" class="form-control" 
                           value="{{ old('tags', is_array($blog->tags) ? implode(', ', $blog->tags) : $blog->tags) }}" 
                           placeholder="Enter tags separated by commas" form="itemForm">
                    <div class="form-text">Example: halal, recipes, tips</div>
                </div>
            </div>
        </div>
        
        <!-- Status Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Publishing</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" form="itemForm">
                        <option value="draft" {{ old('status', $blog->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $blog->status) == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="published_at" class="form-label">Publish Date</label>
                    <input type="datetime-local" name="published_at" id="published_at" class="form-control" 
                           value="{{ old('published_at', $blog->published_at ? $blog->published_at->format('Y-m-d\TH:i') : '') }}" form="itemForm">
                    <div class="form-text">Leave empty to publish immediately when status is published</div>
                </div>
            </div>
        </div>
        
        <!-- Post Info Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Post Info</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Author:</strong> {{ $blog->author->name ?? 'N/A' }}</p>
                <p class="mb-2"><strong>Created:</strong> {{ $blog->created_at->format('M d, Y H:i') }}</p>
                <p class="mb-0"><strong>Last Updated:</strong> {{ $blog->updated_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
        
        <!-- Tips Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Use a catchy title</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Add a featured image</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Write engaging content</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Use relevant tags</li>
                    <li class="mb-0"><i class="bi bi-check-circle text-success me-2"></i>Optimize for SEO</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <a href="{{ route('admin.blogs.destroy', $blog->slug) }}" 
       class="btn btn-outline-danger floating-reset-btn" 
       onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this post?')) { document.getElementById('deleteForm').submit(); }">
        <i class="bi bi-trash me-1"></i> Delete
    </a>
    <button type="submit" form="itemForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Post
    </button>
</div>

<!-- Delete Form (hidden) -->
<form id="deleteForm" action="{{ route('admin.blogs.destroy', $blog->slug) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
$(document).ready(function() {
    tinymce.init({
        selector: '#blog-content',
        height: 450,
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
        }
    });

    // Auto-generate slug from title
    $('#title').on('blur', function() {
        var slugInput = $('#slug');
        if (!slugInput.val() && $(this).val()) {
            slugInput.val($(this).val().toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, ''));
        }
    });
    
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
</script>
@endpush
