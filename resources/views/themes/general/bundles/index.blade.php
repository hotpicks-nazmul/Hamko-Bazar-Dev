@extends('themes.general.layouts.app')

@section('title', 'Product Bundles')

@push('styles')
<style>
.bundle-card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}
.bundle-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.bundle-card .bundle-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}
.bundle-card .bundle-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.bundle-card:hover .bundle-image img {
    transform: scale(1.05);
}
.bundle-discount-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
}
.bundle-products-preview {
    display: flex;
    gap: 5px;
    margin-top: 10px;
}
.bundle-products-preview img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    border: 2px solid #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.bundle-price-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-top: 10px;
}
.original-price {
    text-decoration: line-through;
    color: #999;
    font-size: 0.9rem;
}
.bundle-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2D5A27;
}
.savings-badge {
    background: #d4edda;
    color: #155724;
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}
.bundle-timer {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 8px 15px;
    border-radius: 8px;
    font-size: 0.85rem;
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Product Bundles</h1>
            <p class="text-muted">Save more when you buy our specially curated product bundles</p>
        </div>
    </div>

    @if($bundles->count() > 0)
    <!-- Bundles Grid -->
    <div class="row g-4">
        @foreach($bundles as $bundle)
        <div class="col-lg-4 col-md-6">
            <div class="bundle-card card h-100">
                <div class="bundle-image">
                    @if($bundle->featured_image)
                        <img src="{{ asset('storage/' . $bundle->featured_image) }}" alt="{{ $bundle->name }}">
                    @else
                        <img src="{{ asset('images/no-image.png') }}" alt="{{ $bundle->name }}">
                    @endif
                    
                    @if($bundle->discount_percentage > 0)
                    <span class="bundle-discount-badge">
                        <i class="bi bi-tag-fill me-1"></i>{{ $bundle->discount_percentage }}% OFF
                    </span>
                    @endif
                </div>
                
                <div class="card-body">
                    <h5 class="card-title">{{ $bundle->name }}</h5>
                    <p class="card-text text-muted small">{{ Str::limit($bundle->description, 80) }}</p>
                    
                    <!-- Products Preview -->
                    <div class="bundle-products-preview">
                        @foreach($bundle->items->take(4) as $item)
                            @if($item->product->featured_image)
                                <img src="{{ asset('storage/' . $item->product->featured_image) }}" 
                                     alt="{{ $item->product->name }}" 
                                     title="{{ $item->product->name }}">
                            @endif
                        @endforeach
                        @if($bundle->items->count() > 4)
                            <span class="badge bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                  style="width: 40px; height: 40px;">
                                +{{ $bundle->items->count() - 4 }}
                            </span>
                        @endif
                    </div>
                    
                    <!-- Price Section -->
                    <div class="bundle-price-section">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if($bundle->original_price > $bundle->final_price)
                                    <span class="original-price">৳{{ number_format($bundle->original_price, 2) }}</span>
                                @endif
                                <span class="bundle-price">৳{{ number_format($bundle->final_price, 2) }}</span>
                            </div>
                            @if($bundle->savings > 0)
                                <span class="savings-badge">
                                    <i class="bi bi-piggy-bank me-1"></i>Save ৳{{ number_format($bundle->savings, 2) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Timer for limited bundles -->
                    @if($bundle->expires_at && !$bundle->hasExpired())
                    <div class="bundle-timer mt-3 text-center">
                        <i class="bi bi-clock me-1"></i>
                        Ends: {{ $bundle->expires_at->format('M d, Y') }}
                    </div>
                    @endif
                    
                    <!-- Actions -->
                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ route('bundles.show', $bundle->slug) }}" class="btn btn-outline-primary flex-grow-1">
                            <i class="bi bi-eye me-1"></i> View Details
                        </a>
                        <form action="{{ route('bundles.add-to-cart', $bundle->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn text-white" style="background-color: var(--theme-primary, #4f46e5);" {{ !$bundle->canBePurchasedBy(auth()->user()) ? 'disabled' : '' }}>
                                <i class="bi bi-cart-plus"></i>
                            </button>
                        </form>
                    </div>
                    
                    @if(!$bundle->canBePurchasedBy(auth()->user()))
                        @if($bundle->hasExpired())
                            <small class="text-danger d-block mt-2"><i class="bi bi-x-circle me-1"></i>This bundle has expired</small>
                        @elseif($bundle->max_purchases && $bundle->total_purchases >= $bundle->max_purchases)
                            <small class="text-warning d-block mt-2"><i class="bi bi-exclamation-circle me-1"></i>Sold out</small>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Pagination -->
    @if($bundles->hasPages())
    <div class="row mt-4">
        <div class="col-12">
            {{ $bundles->links() }}
        </div>
    </div>
    @endif
    @else
    <!-- Empty State -->
    <div class="row">
        <div class="col-12 text-center py-5">
            <i class="bi bi-boxes text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3">No Bundles Available</h4>
            <p class="text-muted">Check back later for amazing bundle deals!</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">
                <i class="bi bi-box-seam me-1"></i> Browse Products
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
