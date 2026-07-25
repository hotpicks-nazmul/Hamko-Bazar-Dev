@extends('admin.layouts.app')

@section('title', 'Edit Blog Tag')

@section('content')
        <!-- Header with Back Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Edit Blog Tag</h4>
            <a href="{{ route('admin.blog-tags.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Tags
            </a>
        </div>

        <form method="POST" action="{{ route('admin.blog-tags.update', $blogTag->id) }}" id="itemForm">
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
                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Tag Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $blogTag->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">The name is how it appears on your site.</div>
                            </div>

                            <!-- Slug -->
                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug</label>
                                <div class="input-group">
                                    <span class="input-group-text">/tag/</span>
                                    <input type="text" id="slug" name="slug" 
                                           class="form-control @error('slug') is-invalid @enderror" 
                                           value="{{ old('slug', $blogTag->slug) }}">
                                </div>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">The "slug" is the URL-friendly version of the name.</div>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" 
                                          class="form-control @error('description') is-invalid @enderror" 
                                          rows="4">{{ old('description', $blogTag->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Optional description for this tag.</div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Card -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-search me-2"></i>SEO Settings</h6>
                        </div>
                        <div class="card-body">
                            <!-- Meta Title -->
                            <div class="mb-3">
                                <label for="meta_title" class="form-label">Meta Title</label>
                                <input type="text" id="meta_title" name="meta_title" 
                                       class="form-control @error('meta_title') is-invalid @enderror" 
                                       value="{{ old('meta_title', $blogTag->meta_title) }}">
                                @error('meta_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave empty to use tag name. Recommended: 50-60 characters.</div>
                            </div>

                            <!-- Meta Description -->
                            <div class="mb-3">
                                <label for="meta_description" class="form-label">Meta Description</label>
                                <textarea id="meta_description" name="meta_description" 
                                          class="form-control @error('meta_description') is-invalid @enderror" 
                                          rows="3">{{ old('meta_description', $blogTag->meta_description) }}</textarea>
                                @error('meta_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Recommended: 150-160 characters.</div>
                            </div>

                            <!-- Meta Keywords -->
                            <div class="mb-3">
                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                <input type="text" id="meta_keywords" name="meta_keywords" 
                                       class="form-control @error('meta_keywords') is-invalid @enderror" 
                                       value="{{ old('meta_keywords', $blogTag->meta_keywords) }}">
                                @error('meta_keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Separate keywords with commas.</div>
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
                                <label for="status" class="form-label">Tag Status</label>
                                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="active" {{ old('status', $blogTag->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $blogTag->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Inactive tags won't be shown on the frontend.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Card -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Display Order</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" id="sort_order" name="sort_order" 
                                       class="form-control @error('sort_order') is-invalid @enderror" 
                                       value="{{ old('sort_order', $blogTag->sort_order) }}" 
                                       min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Tags will be sorted by this number (ascending).</div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Tag Info</h6>
                        </div>
                        <div class="card-body">
                            <div class="small text-muted">
                                <p class="mb-1"><strong>Blog Posts:</strong> {{ $blogTag->blogs_count }}</p>
                                <p class="mb-1"><strong>Created:</strong> {{ $blogTag->created_at->format('M d, Y') }}</p>
                                <p class="mb-0"><strong>Last Updated:</strong> {{ $blogTag->updated_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Delete Form -->
        <form id="deleteForm" method="POST" action="{{ route('admin.blog-tags.destroy', $blogTag->id) }}" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

        <!-- Floating Buttons -->
        <div class="floating-save-container">
            <a href="{{ route('admin.blog-tags.index') }}" class="btn btn-secondary floating-reset-btn">
                <i class="bi bi-x-lg me-1"></i> Cancel
            </a>
            <a href="#" class="btn btn-outline-danger floating-reset-btn" 
               onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this tag?')) { document.getElementById('deleteForm').submit(); }">
                <i class="bi bi-trash me-1"></i> Delete
            </a>
            <button type="submit" form="itemForm" class="btn btn-primary floating-save-btn">
                <i class="bi bi-check-lg me-1"></i> Update Tag
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
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        const slugInput = document.getElementById('slug');
        
        // Only auto-generate slug if it hasn't been manually edited
        if (!slugInput.dataset.manuallyEdited) {
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            slugInput.value = slug;
        }
    });

    // Track manual slug edits
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
    });
</script>
@endpush
