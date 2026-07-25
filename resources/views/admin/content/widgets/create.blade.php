@extends('admin.layouts.app')

@section('title', 'Create Widget')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create Widget</h4>
    <a href="{{ route('admin.content.widgets.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Widgets
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Basic Information</h6>
            </div>
            <div class="card-body">
                <form id="widgetForm" method="POST" action="{{ route('admin.content.widgets.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Widget Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Internal name for this widget</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="widget_type" class="form-label">Widget Type <span class="text-danger">*</span></label>
                        <select id="widget_type" name="widget_type" class="form-select @error('widget_type') is-invalid @enderror" required>
                            <option value="">Select Widget Type</option>
                            @foreach($widgetTypes as $key => $type)
                                <option value="{{ $key }}" {{ old('widget_type') == $key ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                        @error('widget_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title') }}">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subtitle" class="form-label">Subtitle</label>
                                <input type="text" id="subtitle" name="subtitle" class="form-control @error('subtitle') is-invalid @enderror" 
                                       value="{{ old('subtitle') }}">
                                @error('subtitle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Category Selection (for category products widget) -->
                    <div class="mb-3" id="categorySection" style="display: none;">
                        <label for="category_id" class="form-label">Select Category</label>
                        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @if($category->children->count() > 0)
                                    @foreach($category->children as $child)
                                        <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>
                                            -- {{ $child->name }}
                                        </option>
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="product_limit" class="form-label">Product Limit</label>
                        <input type="number" id="product_limit" name="product_limit" class="form-control @error('product_limit') is-invalid @enderror" 
                               value="{{ old('product_limit', 10) }}" min="1" max="50">
                        @error('product_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Number of products to display (for product widgets)</div>
                    </div>
                    
                    <!-- Custom Content (for HTML/custom widgets) -->
                    <div class="mb-3" id="contentSection" style="display: none;">
                        <label for="content" class="form-label">Custom Content</label>
                        <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" 
                                  rows="6">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">HTML content for custom widgets</div>
                    </div>
                    
                    <!-- Settings (JSON for advanced options) -->
                    <div class="mb-3" id="settingsSection" style="display: none;">
                        <label for="settings" class="form-label">Additional Settings (JSON)</label>
                        <textarea id="settings" name="settings" class="form-control @error('settings') is-invalid @enderror" 
                                  rows="4" placeholder='{"key": "value"}'>{{ old('settings') }}</textarea>
                        @error('settings')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Additional widget settings in JSON format</div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Status</h6>
            </div>
            <div class="card-body">
                <input type="hidden" name="status" value="inactive" form="widgetForm">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="status" name="status" form="widgetForm"
                           value="active" {{ old('status', 'inactive') == 'active' ? 'checked' : '' }}>
                    <label class="form-check-label" for="status">
                        <i class="bi bi-check-circle text-success me-1"></i> Active
                    </label>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" form="widgetForm" 
                           value="1" {{ old('is_featured') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_featured">
                        <i class="bi bi-star text-warning me-1"></i> Featured
                    </label>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Display Order</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                           value="{{ old('sort_order', 0) }}" min="0" form="widgetForm">
                    @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Widgets will be displayed in ascending order</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.content.widgets.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="widgetForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Widget
    </button>
</div>

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
    // Show/hide sections based on widget type
    const widgetTypeSelect = document.getElementById('widget_type');
    const categorySection = document.getElementById('categorySection');
    const contentSection = document.getElementById('contentSection');
    const settingsSection = document.getElementById('settingsSection');
    
    function updateSections() {
        const widgetType = widgetTypeSelect.value;
        
        // Show category section for category products widget
        if (widgetType === 'category_products') {
            categorySection.style.display = 'block';
        } else {
            categorySection.style.display = 'none';
        }
        
        // Show content section for custom HTML and newsletter widgets
        if (widgetType === 'custom_html' || widgetType === 'newsletter') {
            contentSection.style.display = 'block';
        } else {
            contentSection.style.display = 'none';
        }
        
        // Show settings for banner, slider, testimonials
        if (widgetType === 'banner' || widgetType === 'slider' || widgetType === 'testimonials') {
            settingsSection.style.display = 'block';
        } else {
            settingsSection.style.display = 'none';
        }
    }
    
    widgetTypeSelect.addEventListener('change', updateSections);
    
    // Initial check
    updateSections();
</script>
@endpush
@endsection
