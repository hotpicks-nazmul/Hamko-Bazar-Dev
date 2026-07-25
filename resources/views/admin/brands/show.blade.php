@extends('admin.layouts.app')

@section('title', 'Brand Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Brand Details: {{ $brand->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Brand
        </a>
        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Brands
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Brand Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Brand Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Brand Name:</div>
                    <div class="col-md-9 fw-bold">{{ $brand->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Slug:</div>
                    <div class="col-md-9">
                        <code>{{ $brand->slug }}</code>
                    </div>
                </div>
                @if($brand->website)
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Website:</div>
                    <div class="col-md-9">
                        <a href="{{ $brand->website }}" target="_blank" rel="noopener">
                            {{ $brand->website }} <i class="bi bi-box-arrow-up-right small"></i>
                        </a>
                    </div>
                </div>
                @endif
                @if($brand->description)
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Description:</div>
                    <div class="col-md-9">{{ $brand->description }}</div>
                </div>
                @endif
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Sort Order:</div>
                    <div class="col-md-9">{{ $brand->sort_order }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Status:</div>
                    <div class="col-md-9">
                        @if($brand->is_active)
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Active</span>
                        @else
                            <span class="badge bg-secondary"><i class="bi bi-pause-circle me-1"></i> Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Featured:</div>
                    <div class="col-md-9">
                        @if($brand->is_featured)
                            <span class="badge bg-info"><i class="bi bi-star-fill me-1"></i> Featured</span>
                        @else
                            <span class="badge bg-light text-dark">Not Featured</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- SEO Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-search me-2"></i>SEO Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Meta Title:</div>
                    <div class="col-md-9">{{ $brand->meta_title ?: '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Meta Description:</div>
                    <div class="col-md-9">{{ $brand->meta_description ?: '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Meta Keywords:</div>
                    <div class="col-md-9">
                        @if($brand->meta_keywords)
                            @foreach(explode(',', $brand->meta_keywords) as $keyword)
                                <span class="badge bg-light text-dark me-1">{{ trim($keyword) }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Products List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Products ({{ $brand->products_count }})</h6>
                @if($brand->products_count > 0)
                <a href="{{ route('admin.products.index', ['brand' => $brand->id]) }}" class="btn btn-sm btn-outline-primary">
                    View All Products
                </a>
                @endif
            </div>
            <div class="card-body p-0">
                @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">Image</th>
                                <th>Product Name</th>
                                <th style="width: 100px;">Price</th>
                                <th style="width: 80px;">Stock</th>
                                <th style="width: 80px;">Status</th>
                                <th style="width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>
                                    @if($product->featured_image_url)
                                        <img src="{{ $product->featured_image_url }}" alt="{{ $product->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-box text-white"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="text-decoration-none">
                                        {{ $product->name }}
                                    </a>
                                </td>
                                <td>
                                    @if($product->isOnSale())
                                        <del class="text-muted small">{{ config('app.currency', '$') }}{{ number_format($product->price, 2) }}</del>
                                        <br>
                                        <span class="text-danger">{{ config('app.currency', '$') }}{{ number_format($product->sale_price, 2) }}</span>
                                    @else
                                        {{ config('app.currency', '$') }}{{ number_format($product->price, 2) }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $product->quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $product->quantity }}
                                    </span>
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($products->hasPages())
                <div class="card-footer bg-white">
                    {{ $products->links('vendor.pagination.bootstrap-5-admin') }}
                </div>
                @endif
                @else
                <div class="text-center py-5">
                    <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No products associated with this brand.</p>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add Product
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Brand Logo -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-image me-2"></i>Brand Logo</h6>
            </div>
            <div class="card-body text-center">
                @if($brand->logo_url)
                    <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="img-thumbnail" style="max-width: 100%; max-height: 200px;">
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                        <div class="text-center text-muted">
                            <i class="bi bi-image" style="font-size: 3rem;"></i>
                            <p class="mb-0 mt-2">No logo uploaded</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total Products:</span>
                    <span class="fw-bold">{{ $brand->products_count }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Active Products:</span>
                    <span class="fw-bold text-success">{{ $brand->products()->where('is_active', true)->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Created:</span>
                    <span>{{ $brand->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Last Updated:</span>
                    <span>{{ $brand->updated_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i> Edit Brand
                    </a>
                    <a href="{{ route('admin.products.create', ['brand' => $brand->id]) }}" class="btn btn-outline-success">
                        <i class="bi bi-plus-lg me-1"></i> Add Product
                    </a>
                    <a href="{{ route('admin.products.index', ['brand' => $brand->id]) }}" class="btn btn-outline-info">
                        <i class="bi bi-box-seam me-1"></i> View Products
                    </a>
                    @if($brand->products_count === 0)
                    <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this brand?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete Brand
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
