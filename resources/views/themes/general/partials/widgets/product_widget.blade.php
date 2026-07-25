@if($products && $products->count() > 0)
<section class="widget-section py-4">
    <div class="container">
        @if($widget->title || $widget->subtitle)
        <div class="widget-header mb-4">
            @if($widget->title)
            <h3 class="widget-title">{{ $widget->title }}</h3>
            @endif
            @if($widget->subtitle)
            <p class="widget-subtitle text-muted">{{ $widget->subtitle }}</p>
            @endif
        </div>
        @endif
        
        <div class="row g-3">
            @foreach($products as $product)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="product-card h-100 border rounded p-2">
                    <div class="product-image position-relative mb-2">
                        <a href="{{ route('product', $product->slug) }}">
                            @php
                                $imageUrl = $product->featured_image ?? $product->thumbnail_img;
                                if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                    $imageUrl = '/storage/' . $imageUrl;
                                }
                            @endphp
                            @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="img-fluid w-100" style="height: 180px; object-fit: cover;">
                            @else
                            <div class="no-image-placeholder d-flex align-items-center justify-content-center bg-light" style="height: 180px;">
                                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                            </div>
                            @endif
                        </a>
                        @if($product->has_discount || $product->discount > 0)
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">Sale</span>
                        @endif
                        <div class="product-actions mt-2">
                            <button class="btn btn-sm btn-outline-primary w-100" onclick="addToCart({{ $product->id }})">
                                <i class="bi bi-cart-plus me-1"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h6 class="product-name mb-1" style="font-size: 0.9rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <a href="{{ route('product', $product->slug) }}" class="text-decoration-none text-dark">
                                {{ $product->name ?? $product->name }}
                            </a>
                        </h6>
                        @if($product->product_code)
                        <span class="d-inline-block mt-1 badge bg-light text-muted border small" style="font-size: 0.7rem;">{{ $product->product_code }}</span>
                        @endif
                        <div class="product-price mb-1">
                            @if($product->has_discount || $product->discount > 0)
                            <span class="text-decoration-line-through text-muted small me-2">{{ format_price($product->unit_price ?? $product->price) }}</span>
                            @endif
                            <span class="fw-bold text-primary">{{ format_price($product->discounted_price ?? $product->unit_price ?? $product->price) }}</span>
                        </div>
                        <div class="product-rating">
                            @php
                                $rating = $product->rating ?? 0;
                                if(isset($product->reviews_avg_rating)) {
                                    $rating = $product->reviews_avg_rating;
                                }
                                $rating = round($rating);
                            @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $rating ? 'bi-star-fill text-warning' : 'bi-star' }}" style="font-size: 0.8rem;"></i>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($widget->settings['show_view_all'] ?? false)
        <div class="text-center mt-4">
            <a href="{{ route('products') }}" class="btn btn-outline-primary">View All Products</a>
        </div>
        @endif
    </div>
</section>
@endif
