<div class="bg-white rounded-xl shadow-md overflow-hidden product-card group hover:shadow-xl transition-all duration-300 flex flex-col sm:flex-row">
    <div class="relative sm:w-56 shrink-0">
        <a href="{{ route('products.show', $product->slug) }}">
            @php
                $imagePath = $product->featured_image ?? $product->image ?? '';
                $imageUrl = 'https://placehold.co/300x300/e2e8f0/64748b?text=No+Image';
                if ($imagePath) {
                    $imagePath = ltrim($imagePath, '/');
                    if (str_starts_with($imagePath, 'http')) {
                        $imageUrl = $imagePath;
                    } elseif (str_starts_with($imagePath, 'storage/')) {
                        $imageUrl = '/' . $imagePath;
                    } elseif (str_starts_with($imagePath, 'uploads/')) {
                        $imageUrl = asset($imagePath);
                    } else {
                        $imageUrl = asset('storage/' . $imagePath);
                    }
                }
            @endphp
            <img src="{{ $imageUrl }}"
                alt="{{ $product->name }}"
                class="w-full sm:w-56 h-48 sm:h-full object-cover group-hover:scale-105 transition-transform duration-300"
                loading="lazy"
                onerror="this.src='https://placehold.co/300x300?text=No+Image'">
        </a>
        <div class="absolute top-2 left-2 flex flex-col space-y-1">
            @if(($product->discount_percent ?? 0) > 0)
            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full font-medium">-{{ $product->discount_percent }}%</span>
            @endif
            @if($product->stock <= 0)
            <span class="bg-gray-700 text-white text-xs px-2 py-1 rounded-full font-medium">Out of Stock</span>
            @endif
        </div>
    </div>
    <div class="p-5 flex-1 flex flex-col justify-between">
        <div>
            @if($product->category)
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="text-xs text-halal-green hover:underline">{{ $product->category->name }}</a>
            @endif
            <h3 class="font-semibold text-gray-800 mt-1 hover:text-halal-green transition-colors line-clamp-2">
                <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
            </h3>
            @php
                $showProductCode = \App\Models\Setting::get('homepage_show_product_code', '1');
                $showRating = \App\Models\Setting::get('homepage_show_rating', '1');
            @endphp
            @if($product->product_code && $showProductCode == '1')
            <span class="inline-block mt-1 text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $product->product_code }}</span>
            @endif
            @if($product->short_description)
            <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $product->short_description }}</p>
            @endif
            @if($showRating == '1')
            <div class="flex items-center mt-2">
                <div class="flex text-halal-gold">
                    @php $avgRating = $product->average_rating ?? 0; @endphp
                    @for($i = 1; $i <= 5; $i++)
                        @if($avgRating >= $i)
                            <i class="bi bi-star-fill text-sm"></i>
                        @elseif($avgRating >= $i - 0.5)
                            <i class="bi bi-star-half text-sm"></i>
                        @else
                            <i class="bi bi-star text-sm text-gray-300"></i>
                        @endif
                    @endfor
                </div>
                <span class="text-xs text-gray-500 ml-2">({{ $product->approved_reviews_count ?? 0 }})</span>
            </div>
            @endif
        </div>
        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
            <div>
                @if($product->isOnSale())
                    <span class="text-xl font-bold text-halal-green">৳{{ number_format($product->sale_price) }}</span>
                    <span class="text-sm text-gray-400 line-through ml-2">৳{{ number_format($product->price) }}</span>
                @else
                    <span class="text-xl font-bold text-halal-green">৳{{ number_format($product->price) }}</span>
                @endif
                @if($product->unit)
                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded ml-2">{{ $product->unit }}</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <button onclick="if(typeof addToWishlist==='function')addToWishlist({{ $product->id }})" class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center hover:bg-halal-green hover:text-white transition-colors" title="Add to Wishlist">
                    <i class="bi bi-heart"></i>
                </button>
                @php $showShopNowList = \App\Models\Setting::get('homepage_show_shop_now_button', '1'); @endphp
                @if($showShopNowList == '1')
                <a href="{{ route('products.show', $product->slug) }}" class="bg-amber-500 text-white px-5 py-2 rounded-lg hover:bg-amber-600 transition-colors font-medium flex items-center gap-2 whitespace-nowrap">
                    <i class="bi bi-bag-check"></i>Shop Now
                </a>
                @endif
                @if($product->stock > 0)
                <button onclick="if(typeof addToCart==='function')addToCart({{ $product->id }})" class="bg-halal-green text-white px-5 py-2 rounded-lg hover:text-amber-400 transition-colors font-medium flex items-center gap-2">
                    <i class="bi bi-cart-plus"></i>Add to Cart
                </button>
                @else
                <button disabled class="bg-gray-400 text-white px-5 py-2 rounded-lg cursor-not-allowed font-medium flex items-center gap-2">
                    <i class="bi bi-x-circle"></i>Out of Stock
                </button>
                @endif
            </div>
        </div>
    </div>
</div>