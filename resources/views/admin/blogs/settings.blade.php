@extends('admin.layouts.app')

@section('title', 'Blog Settings')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-gear me-2"></i> Blog Settings</h4>
    <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Posts
    </a>
</div>

<form id="settingsForm" action="{{ route('admin.blog-settings.update') }}" method="POST">
    @csrf
    
    <div class="row">
        <!-- General Settings -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-sliders me-2"></i>General Settings</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="blog_title" class="form-label">Blog Title</label>
                        <input type="text" id="blog_title" name="blog_title" class="form-control"
                               value="{{ $settings['blog_title']->value ?? 'Blog' }}">
                        <div class="form-text">Title displayed on the blog page header</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="blog_subtitle" class="form-label">Blog Subtitle</label>
                        <input type="text" id="blog_subtitle" name="blog_subtitle" class="form-control"
                               value="{{ $settings['blog_subtitle']->value ?? '' }}">
                        <div class="form-text">Subtitle displayed below the title</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="blog_posts_per_page" class="form-label">Posts Per Page</label>
                        <input type="number" id="blog_posts_per_page" name="blog_posts_per_page" class="form-control"
                               value="{{ $settings['blog_posts_per_page']->value ?? 9 }}" min="3" max="30">
                        <div class="form-text">Number of posts to display per page</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Display Options -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Display Options</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="blog_show_author" id="showAuthor" form="settingsForm"
                               {{ ($settings['blog_show_author']->value ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="showAuthor">
                            <i class="bi bi-person text-primary me-1"></i> Show Author
                        </label>
                        <div class="form-text">Display author name and avatar on posts</div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="blog_show_date" id="showDate" form="settingsForm"
                               {{ ($settings['blog_show_date']->value ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="showDate">
                            <i class="bi bi-calendar text-info me-1"></i> Show Publish Date
                        </label>
                        <div class="form-text">Display publication date on posts</div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="blog_show_category" id="showCategory" form="settingsForm"
                               {{ ($settings['blog_show_category']->value ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="showCategory">
                            <i class="bi bi-folder text-success me-1"></i> Show Category
                        </label>
                        <div class="form-text">Display category badge on posts</div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="blog_show_tags" id="showTags" form="settingsForm"
                               {{ ($settings['blog_show_tags']->value ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="showTags">
                            <i class="bi bi-tag text-warning me-1"></i> Show Tags
                        </label>
                        <div class="form-text">Display tags on single post page</div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="blog_show_share_buttons" id="showShare" form="settingsForm"
                               {{ ($settings['blog_show_share_buttons']->value ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="showShare">
                            <i class="bi bi-share text-secondary me-1"></i> Show Share Buttons
                        </label>
                        <div class="form-text">Display social share buttons on posts</div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="blog_show_related_posts" id="showRelated" form="settingsForm"
                               {{ ($settings['blog_show_related_posts']->value ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="showRelated">
                            <i class="bi bi-link-45deg text-primary me-1"></i> Show Related Posts
                        </label>
                        <div class="form-text">Display related posts carousel on single post page</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="blog_related_posts_count" class="form-label">Related Posts Count</label>
                        <input type="number" id="blog_related_posts_count" name="blog_related_posts_count" class="form-control"
                               value="{{ $settings['blog_related_posts_count']->value ?? 4 }}" min="2" max="10" form="settingsForm">
                        <div class="form-text">Number of related posts to display</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Settings -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-layout-sidebar me-2"></i>Sidebar Settings</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="blog_sidebar_show_search" id="showSearch" form="settingsForm"
                               {{ ($settings['blog_sidebar_show_search']->value ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="showSearch">
                            <i class="bi bi-search text-primary me-1"></i> Show Search
                        </label>
                        <div class="form-text">Display search box in sidebar</div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="blog_sidebar_show_categories" id="showCategories" form="settingsForm"
                               {{ ($settings['blog_sidebar_show_categories']->value ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="showCategories">
                            <i class="bi bi-folder text-success me-1"></i> Show Categories
                        </label>
                        <div class="form-text">Display categories list in sidebar</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="settingsForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save Settings
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
</script>
@endpush
