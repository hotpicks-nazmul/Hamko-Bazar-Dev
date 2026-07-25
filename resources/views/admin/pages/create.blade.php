@extends('admin.layouts.app')

@section('title', 'Create Page')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create New Page</h4>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Pages
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Page Content</h6>
            </div>
            <div class="card-body">
                <form id="pageForm" method="POST" action="{{ route('admin.pages.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title') }}" placeholder="Enter page title" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="page-content" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea id="page-content" name="content" class="form-control @error('content') is-invalid @enderror" 
                                  rows="10" placeholder="Write your page content here..." required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Use the editor below to format your content with headings, images, links, etc.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="featured_image" class="form-label">Featured Image</label>
                        <input type="file" id="featured_image" name="featured_image" class="form-control" accept="image/*" form="pageForm">
                        <div class="form-text">Recommended size: 1200x630px. Leave empty for no featured image.</div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-search me-2"></i>SEO Settings</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input type="text" id="meta_title" name="meta_title" class="form-control" value="{{ old('meta_title') }}" 
                           placeholder="SEO title (defaults to page title)" form="pageForm">
                    <div class="form-text">Leave empty to use the page title</div>
                </div>
                
                <div class="mb-3">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" class="form-control" rows="2" 
                              placeholder="Brief description for search engines" form="pageForm">{{ old('meta_description') }}</textarea>
                    <div class="form-text">A concise description for search engine results (150-160 characters)</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Publish</h6>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} form="pageForm">
                    <label class="form-check-label" for="isActive">
                        <i class="bi bi-check-circle text-success me-1"></i> Publish Page
                    </label>
                </div>
                <div class="form-text">Uncheck to save as draft</div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Use descriptive page titles</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Add meta descriptions for SEO</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Use headings to organize content</li>
                    <li class="mb-0"><i class="bi bi-check-circle text-success me-2"></i>Preview before publishing</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="pageForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Page
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
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
$(document).ready(function() {
    tinymce.init({
        selector: '#page-content',
        height: 400,
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
});
</script>
@endpush
