@extends('admin.layouts.app')

@section('title', 'Affiliate Product Details')

@section('content')
<!-- Header with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Affiliate Product Details</h4>
    <a href="{{ route('admin.affiliate.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Products
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Product Information Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-box me-2"></i>Product Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Product Name</label>
                        <p class="mb-0 fw-medium">{{ $product->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Category</label>
                        <p class="mb-0">
                            @if($product->category)
                            <span class="badge bg-secondary">{{ $product->category->name }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted small">External URL</label>
                    <p class="mb-0">
                        <a href="{{ $product->external_url }}" target="_blank" class="text-decoration-none">
                            {{ $product->external_url }}
                            <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </a>
                    </p>
                </div>
                
                @if($product->description)
                <div class="mb-3">
                    <label class="form-label text-muted small">Description</label>
                    <p class="mb-0">{{ $product->description }}</p>
                </div>
                @endif
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small">Price</label>
                        <p class="mb-0 fw-bold text-primary">${{ number_format($product->price, 2) }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small">Commission Rate</label>
                        <p class="mb-0"><span class="badge bg-info">{{ $product->commission_rate }}%</span></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small">Status</label>
                        <p class="mb-0">
                            @if($product->status === 'active')
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-danger">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small">Total Clicks</label>
                        <p class="mb-0 fw-bold">{{ number_format($product->clicks) }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small">Total Conversions</label>
                        <p class="mb-0 fw-bold">{{ number_format($product->conversions) }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small">Created At</label>
                        <p class="mb-0">{{ $product->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Product Image Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Product Image</h6>
            </div>
            <div class="card-body text-center">
                @if($product->image)
                @php
                    $imageUrl = $product->image;
                    if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                        $imageUrl = '/storage/' . $imageUrl;
                    }
                @endphp
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-width: 100%; max-height: 300px; object-fit: contain;">
                @else
                <div class="bg-light rounded p-5">
                    <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-2 mb-0">No image available</p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Actions Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.affiliate.products.edit', $product->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i> Edit Product
                    </a>
                    <form action="{{ route('admin.affiliate.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete Product
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
