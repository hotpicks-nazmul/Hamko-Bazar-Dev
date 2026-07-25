<div class="flex flex-col md:flex-row gap-6">
    <div class="md:w-2/5 flex-shrink-0">
        <div class="relative rounded-lg overflow-hidden bg-gray-100">
            @php
                $imageUrl = $product->featured_image ?? $product->image ?? '';
                if ($imageUrl) {
                    $imageUrl = ltrim($imageUrl, '/');
                    if (!str_starts_with($imageUrl, 'http')) {
                        $imageUrl = str_starts_with($imageUrl, 'storage/') ? '/' . $imageUrl : asset('storage/' . $imageUrl);
                    }
                } else {
                    $imageUrl = 'https://placehold.co/400x400/e2e8f0/64748b?text=No+Image';
                }
            @endphp
            <img src="{{ $imageUrl }}" alt="{{ $product->name ?? 'Product' }}" class="w-full aspect-square object-cover" loading="lazy">
            @if(method_exists($product, 'isOnSale') && $product->isOnSale())
            <span class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded font-medium">-{{ $product->discount_percentage ?? 0 }}%</span>
            @endif
        </div>
    </div>
    <div class="md:w-3/5 flex flex-col">
        @if($product->category ?? null)
        <a href="{{ route('products.category', $product->category->slug) }}" class="text-xs text-primary hover:underline uppercase tracking-wider font-medium">
            {{ $product->category->name }}
        </a>
        @endif
        <h3 class="font-bold text-xl text-gray-900 mt-1">{{ $product->name ?? 'Unnamed Product' }}</h3>
        @php $reviewCount = $product->approved_reviews_count ?? 0; @endphp
        @if($reviewCount > 0)
        <div class="flex items-center mt-2">
            <div class="flex text-yellow-400">
                @php $avgRating = $product->average_rating ?? 0; @endphp
                @for($i = 1; $i <= 5; $i++)
                    @if($avgRating >= $i)
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                    @elseif($avgRating >= $i - 0.5)
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" clip-path="inset(0 50% 0 0)"/></svg>
                    @else
                    <svg class="w-4 h-4 fill-current text-gray-300" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                    @endif
                @endfor
            </div>
            <span class="text-xs text-gray-500 ml-1">({{ $reviewCount }})</span>
        </div>
        @endif
        <div class="mt-3 flex items-center gap-3">
            @php $currentPrice = $product->current_price ?? $product->price ?? 0; @endphp
            <span class="text-2xl font-bold text-gray-900">৳{{ number_format($currentPrice, 2) }}</span>
            @if(method_exists($product, 'isOnSale') && $product->isOnSale() && ($product->price ?? 0) > $currentPrice)
            <span class="text-base text-gray-500 line-through">৳{{ number_format($product->price, 2) }}</span>
            @endif
        </div>
        @if($product->short_description ?? null)
        <p class="text-gray-600 text-sm mt-3 leading-relaxed">{{ Str::limit($product->short_description, 200) }}</p>
        @endif
        @php $stockStatus = $product->stock_status ?? 'in_stock'; @endphp
        @if($stockStatus === 'out_of_stock')
        <p class="text-xs text-red-500 mt-2 font-medium">Out of Stock</p>
        @elseif(($product->quantity ?? 0) <= 5 && ($product->quantity ?? 0) > 0)
        <p class="text-xs text-orange-500 mt-2 font-medium">Only {{ $product->quantity }} left in stock!</p>
        @endif
        <div class="flex items-center gap-2 mt-auto pt-5">
            <button onclick="addToCart({{ $product->id }})" class="flex-1 bg-primary text-white px-4 py-2.5 rounded-lg font-medium hover:bg-primary-dark hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center whitespace-nowrap">
                <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span class="truncate">Add to Cart</span>
            </button>
            <a href="{{ route('products.show', $product->slug) }}" class="flex-1 bg-amber-500 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-amber-600 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center whitespace-nowrap">
                <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                <span class="truncate">Shop Now</span>
            </a>
            @auth
            @php $inWishlist = \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->exists(); @endphp
            <button onclick="quickViewToggleWishlist({{ $product->id }})" class="p-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 hover:shadow hover:-translate-y-0.5 transition-all duration-200 wishlist-btn-{{ $product->id }} {{ $inWishlist ? 'bg-red-50 border-red-200' : '' }}" title="{{ $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}">
                <svg class="w-5 h-5 {{ $inWishlist ? 'text-red-500 fill-current' : 'text-gray-500' }}" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
            @endauth
        </div>
    </div>
</div>

@auth
<script>
async function quickViewToggleWishlist(productId) {
    try {
        const response = await fetch('{{ route("wishlist.toggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ product_id: productId })
        });
        const data = await response.json();
        if (data.success) {
            const btn = document.querySelector(`.wishlist-btn-${productId}`);
            const svg = btn.querySelector('svg');
            if (data.added) {
                btn.classList.add('bg-red-50', 'border-red-200');
                svg.classList.add('text-red-500', 'fill-current');
                svg.classList.remove('text-gray-500');
                svg.setAttribute('fill', 'currentColor');
            } else {
                btn.classList.remove('bg-red-50', 'border-red-200');
                svg.classList.remove('text-red-500', 'fill-current');
                svg.classList.add('text-gray-500');
                svg.setAttribute('fill', 'none');
            }
            if (typeof updateWishlistCount === 'function') {
                updateWishlistCount(data.added ? 1 : -1);
            }
            if (typeof showToast === 'function') {
                showToast(data.message, 'success');
            }
        } else if (data.login_required) {
            if (typeof showToast === 'function') {
                showToast(data.message, 'error');
            }
            setTimeout(() => window.location.href = '{{ route("login") }}', 1500);
        }
    } catch (error) {
        console.error('Wishlist error:', error);
        if (typeof showToast === 'function') {
            showToast('An error occurred', 'error');
        }
    }
}
</script>
@endauth