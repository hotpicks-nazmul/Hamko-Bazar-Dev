@if($products->count() > 0)
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
                <div class="product-card h-100">
                    <div class="product-image position-relative">
                        <a href="{{ route('product', $product->slug) }}">
                            @php
                                $imageUrl = $product->featured_image;
                                if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                    $imageUrl = '/storage/' . $imageUrl;
                                }
                            @endphp
                            @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="img-fluid">
                            @else
                            <div class="no-image-placeholder d-flex align-items-center justify-content-center bg-light">
                                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                            </div>
                            @endif
                        </a>
                        @if($product->has_discount)
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">Sale</span>
                        @endif
                    </div>
                    <div class="product-info p-2">
                        <h6 class="product-name mb-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <a href="{{ route('product', $product->slug) }}" class="text-decoration-none text-dark">
                                {{ $product->name }}
                            </a>
                        </h6>
                        @if($product->product_code)
                        <span class="d-inline-block mt-1 badge bg-light text-muted border small" style="font-size: 0.7rem;">{{ $product->product_code }}</span>
                        @endif
                        <div class="product-price">
                            @if($product->has_discount)
                            <span class="text-decoration-line-through text-muted small">{{ format_price($product->unit_price) }}</span>
                            @endif
                            <span class="fw-bold text-primary">{{ format_price($product->discounted_price) }}</span>
                        </div>
                        <div class="product-rating mt-1">
                            @php
                                $rating = $product->reviews_avg_rating ?? 0;
                                $ratingCount = $product->reviews_count ?? 0;
                            @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= round($rating) ? 'bi-star-fill text-warning' : 'bi-star' }}"></i>
                            @endfor
                            <span class="text-muted small">({{ $ratingCount }})</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
