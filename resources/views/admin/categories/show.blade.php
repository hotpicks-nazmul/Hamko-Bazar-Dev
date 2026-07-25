@extends('admin.layouts.app')

@section('title', 'Category: ' . $category->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $category->name }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
                @foreach($category->breadcrumb as $crumb)
                    <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                        @if(!$loop->last)
                            <a href="{{ route('admin.categories.show', $crumb->id) }}">{{ $crumb->name }}</a>
                        @else
                            {{ $crumb->name }}
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Category Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Category Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        @if($category->image)
                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        @else
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px;">
                                <i class="bi bi-folder text-white" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted" style="width: 120px;">Name:</th>
                                <td>{{ $category->name }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Slug:</th>
                                <td><code>{{ $category->slug }}</code></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Parent:</th>
                                <td>
                                    @if($category->parent)
                                        <a href="{{ route('admin.categories.show', $category->parent->id) }}">{{ $category->parent->name }}</a>
                                    @else
                                        <span class="text-muted">None (Top Level)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status:</th>
                                <td>{!! $category->status_badge !!}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Sort Order:</th>
                                <td>{{ $category->sort_order }}</td>
                            </tr>
                            @if($category->icon)
                            <tr>
                                <th class="text-muted">Icon:</th>
                                <td><i class="{{ $category->icon }}"></i> <code class="ms-2">{{ $category->icon }}</code></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                
                @if($category->description)
                <hr>
                <div>
                    <h6 class="text-muted mb-2">Description</h6>
                    <p class="mb-0">{!! nl2br(e($category->description)) !!}</p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- SEO Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-search me-2"></i>SEO Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th class="text-muted" style="width: 120px;">Meta Title:</th>
                        <td>{{ $category->meta_title ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Meta Description:</th>
                        <td>{{ $category->meta_description ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Meta Keywords:</th>
                        <td>{{ $category->meta_keywords ?: '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Subcategories -->
        @if($category->children->count() > 0)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Subcategories ({{ $category->children->count() }})</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">Image</th>
                                <th>Name</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($category->children as $child)
                            <tr>
                                <td>
                                    @if($child->image)
                                        <img src="{{ $child->image_url }}" alt="{{ $child->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-folder text-white"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.categories.show', $child->id) }}">{{ $child->name }}</a>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $child->products()->count() }}</span>
                                </td>
                                <td>{!! $child->status_badge !!}</td>
                                <td>
                                    <a href="{{ route('admin.categories.edit', $child->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Products -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-box me-2"></i>Products ({{ $productsCount }})</h6>
                <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="btn btn-sm btn-outline-primary">
                    View All Products
                </a>
            </div>
            <div class="card-body p-0">
                @if($category->products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($category->products as $product)
                            <tr>
                                <td>
                                    @if($product->featured_image)
                                        <img src="{{ $product->featured_image }}" alt="{{ $product->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-box text-white"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.edit', $product->id) }}">{{ $product->name }}</a>
                                    @if($product->sku)
                                        <br><small class="text-muted">SKU: {{ $product->sku }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($product->sale_price)
                                        <del class="text-muted">৳{{ number_format($product->price, 2) }}</del>
                                        <span class="text-danger">৳{{ number_format($product->sale_price, 2) }}</span>
                                    @else
                                        ৳{{ number_format($product->price, 2) }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $product->quantity > 10 ? 'bg-success' : ($product->quantity > 0 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $product->quantity }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>
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
                @else
                <div class="text-center py-5">
                    <i class="bi bi-box text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No products in this category.</p>
                    <a href="{{ route('admin.products.create', ['category_id' => $category->id]) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add Product
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total Products:</span>
                    <span class="badge bg-info fs-6">{{ $productsCount }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Active Products:</span>
                    <span class="badge bg-success fs-6">{{ $activeProductsCount }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Subcategories:</span>
                    <span class="badge bg-warning fs-6">{{ $category->children()->count() }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Depth Level:</span>
                    <span class="badge bg-secondary fs-6">{{ $category->depth }}</span>
                </div>
            </div>
        </div>
        
        <!-- Visibility Settings -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Visibility Settings</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Show in Menu:</span>
                    <span class="badge {{ $category->show_in_menu ? 'bg-success' : 'bg-secondary' }}">
                        {{ $category->show_in_menu ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Show on Homepage:</span>
                    <span class="badge {{ $category->show_in_homepage ? 'bg-success' : 'bg-secondary' }}">
                        {{ $category->show_in_homepage ? 'Yes' : 'No' }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Timestamps -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Timestamps</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted small">Created:</span>
                    <div>{{ $category->created_at->format('M d, Y H:i') }}</div>
                </div>
                <div>
                    <span class="text-muted small">Updated:</span>
                    <div>{{ $category->updated_at->format('M d, Y H:i') }}</div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i> Edit Category
                    </a>
                    <a href="{{ route('admin.products.create', ['category_id' => $category->id]) }}" class="btn btn-outline-success">
                        <i class="bi bi-plus-lg me-1"></i> Add Product
                    </a>
                    @if($category->children()->count() == 0)
                        <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}" class="btn btn-outline-info">
                            <i class="bi bi-diagram-3 me-1"></i> Add Subcategory
                        </a>
                    @endif
                    @if($category->canBeDeleted())
                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash me-1"></i> Delete Category
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection