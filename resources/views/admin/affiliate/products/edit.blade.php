@extends('admin.layouts.app')

@section('title', 'Edit Affiliate Product')

@section('content')
<!-- Header with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Affiliate Product</h4>
    <a href="{{ route('admin.affiliate.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Products
    </a>
</div>

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
        <!-- Card 1: Product Details -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-box me-2"></i>Product Details</h6>
            </div>
            <div class="card-body">
                <form id="productForm" method="POST" action="{{ route('admin.affiliate.products.update', $product->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="external_url" class="form-label">External URL <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                            <input type="url" id="external_url" name="external_url" class="form-control @error('external_url') is-invalid @enderror" value="{{ old('external_url', $product->external_url) }}" required>
                        </div>
                        @error('external_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Card 2: Pricing -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Pricing & Commission</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="price" name="price" form="productForm" class="form-control @error('price') is-invalid @enderror" step="0.01" min="0" value="{{ old('price', $product->price) }}" required>
                        </div>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="commission_rate" class="form-label">Commission Rate (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" id="commission_rate" name="commission_rate" form="productForm" class="form-control @error('commission_rate') is-invalid @enderror" step="0.01" min="0" max="100" value="{{ old('commission_rate', $product->commission_rate) }}" required>
                            <span class="input-group-text">%</span>
                        </div>
                        @error('commission_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Sidebar Card: Status -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Status</h6>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="status" name="status" form="productForm" value="active" {{ old('status', $product->status) === 'active' ? 'checked' : '' }}>
                    <label class="form-check-label" for="status">
                        <i class="bi bi-check-circle text-success me-1"></i> Active
                    </label>
                    <div class="form-text">Enable to make product available for affiliates</div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar Card: Image -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Product Image</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="image" class="form-label">Upload Image</label>
                    <input type="file" id="image" name="image" form="productForm" class="form-control @error('image') is-invalid @enderror" accept="image/*" onchange="previewFeaturedImage(this)">
                    <div class="form-text">Recommended size: 400x400 pixels. Max file size: 2MB</div>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="featuredImagePreview" class="mt-2">
                        @if($product->image)
                        @php
                            $imageUrl = $product->image;
                            if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                $imageUrl = '/storage/' . $imageUrl;
                            }
                        @endphp
                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.affiliate.products.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="productForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Product
    </button>
</div>
@endsection

@push('scripts')
<script>
    // Preview featured image
    function previewFeaturedImage(input) {
        const preview = document.getElementById('featuredImagePreview');
        preview.innerHTML = '';
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
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
