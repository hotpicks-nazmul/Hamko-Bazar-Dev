<div class="product-card bg-white rounded-xl shadow-sm overflow-hidden group hover:shadow-lg transition-shadow">
    <!-- Product Image -->
    <div class="product-image relative aspect-square overflow-hidden">
        <a href="{{ route('products.show', $product->slug) }}">
            @php
                $imageUrl = $product->featured_image ?? '';
                // Handle different image path formats
                if ($imageUrl) {
                    $imageUrl = ltrim($imageUrl, '/');
                    if (str_starts_with($imageUrl, 'http')) {
                        // External URL - use as is
                    } elseif (str_starts_with($imageUrl, 'storage/')) {
                        // Path starts with storage/ - prepend /
                        $imageUrl = '/' . $imageUrl;
                    } elseif (str_starts_with($imageUrl, 'uploads/')) {
                        // Old uploads format
                        $imageUrl = asset($imageUrl);
                    } else {
                        // Relative path - prepend storage
                        $imageUrl = asset('storage/' . $imageUrl);
                    }
                } else {
                    $imageUrl = 'https://placehold.co/300x300?text=No+Image';
                }
            @endphp
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        </a>
        
        <!-- Badges -->
        <div class="absolute top-2 left-2 flex flex-col gap-1">
            @if($product->is_featured)
            <span class="bg-primary text-white text-xs px-2 py-1 rounded">Featured</span>
            @endif
            @if($product->isOnSale())
            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded">-{{ $product->discount_percentage }}%</span>
            @endif
            @if($product->stock_status === 'out_of_stock')
            <span class="bg-gray-500 text-white text-xs px-2 py-1 rounded">Out of Stock</span>
            @endif
        </div>
        
        <!-- Quick Actions -->
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 to-transparent p-3 translate-y-full group-hover:translate-y-0 transition-transform">
            <div class="flex justify-center gap-2">
                <button onclick="addToCart({{ $product->id }})" class="bg-white text-gray-900 p-2 rounded-full hover:bg-primary hover:text-white transition-colors" title="Add to Cart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </button>
                <a href="{{ route('products.show', $product->slug) }}" class="bg-white text-gray-900 p-2 rounded-full hover:bg-primary hover:text-white transition-colors" title="Quick View">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </a>
                @auth
                @php $inWishlist = \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->exists(); @endphp
                <button onclick="toggleWishlist({{ $product->id }})" class="p-2 rounded-full transition-colors wishlist-btn-{{ $product->id }} {{ $inWishlist ? 'bg-red-500 text-white' : 'bg-white text-gray-900 hover:bg-red-500 hover:text-white' }}" title="{{ $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}">
                    <svg class="w-5 h-5" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </button>
                @endauth
            </div>
        </div>
    </div>
    
    <!-- Product Info -->
    <div class="p-4">
        <!-- Category -->
        @if($product->category)
        <a href="{{ route('products.category', $product->category->slug) }}" class="text-xs text-primary hover:underline">
            {{ $product->category->name }}
        </a>
        @endif
        
        <!-- Name -->
        <h3 class="font-semibold text-gray-900 mt-1 line-clamp-2">
            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-primary">
                {{ $product->name }}
            </a>
        </h3>
        
        <!-- Product Code -->
        @if($product->product_code)
        <span class="inline-block mt-1 text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $product->product_code }}</span>
        @endif
        
        <!-- Rating -->
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
        
        <!-- Price -->
        <div class="mt-2 flex items-center gap-2">
            <span class="text-lg font-bold text-gray-900">৳{{ number_format($product->current_price, 2) }}</span>
            @if($product->isOnSale())
            <span class="text-sm text-gray-500 line-through">৳{{ number_format($product->price, 2) }}</span>
            @endif
        </div>
        
        <!-- Stock Status -->
        @if($product->stock_status === 'in_stock' && $product->quantity <= 5)
        <p class="text-xs text-orange-500 mt-1">Only {{ $product->quantity }} left!</p>
        @endif
    </div>
</div>

<script>
async function toggleWishlist(productId) {
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
                btn.classList.add('bg-red-500', 'text-white');
                btn.classList.remove('text-gray-900', 'bg-white');
                svg.setAttribute('fill', 'currentColor');
            } else {
                btn.classList.remove('bg-red-500', 'text-white');
                btn.classList.add('text-gray-900', 'bg-white');
                svg.setAttribute('fill', 'none');
            }
            
            // Update wishlist count in header
            updateWishlistCount(data.added ? 1 : -1);
            
            // Show toast message
            showToast(data.message, 'success');
        } else if (data.login_required) {
            showToast(data.message, 'error');
            setTimeout(() => {
                window.location.href = '{{ route("login") }}';
            }, 1500);
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    }
}

function updateWishlistCount(change) {
    const countElements = document.querySelectorAll('.wishlist-count');
    countElements.forEach(el => {
        let count = parseInt(el.textContent) || 0;
        count += change;
        el.textContent = count;
        if (count <= 0) {
            el.classList.add('hidden');
        } else {
            el.classList.remove('hidden');
        }
    });
}

function showToast(message, type = 'info') {
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `toast-notification fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    } text-white`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
