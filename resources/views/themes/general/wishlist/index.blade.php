@extends('themes.general.layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ route('home') }}" class="text-gray-500 hover:text-primary">Home</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li><a href="{{ route('account.dashboard') }}" class="text-gray-500 hover:text-primary">Account</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li class="text-gray-900 font-medium">Wishlist</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden lg:sticky lg:top-24">
                <div class="p-6 text-center border-b">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-3 overflow-hidden">
                        @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                        @else
                        <i class="bi bi-person text-3xl text-primary"></i>
                        @endif
                    </div>
                    <h3 class="font-semibold text-gray-900">{{ auth()->user()->name }}</h3>
                    <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                </div>
                <nav class="p-4">
                    <a href="{{ route('account.dashboard') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-grid mr-3"></i> Dashboard
                    </a>
                    <a href="{{ route('account.profile') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-person mr-3"></i> Profile
                    </a>
                    <a href="{{ route('account.orders') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-bag mr-3"></i> Orders
                    </a>
                    <a href="{{ route('account.wishlist') }}" class="flex items-center p-3 rounded-lg bg-primary/10 text-primary font-medium">
                        <i class="bi bi-heart mr-3"></i> Wishlist
                    </a>
                    <a href="{{ route('account.addresses') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-geo-alt mr-3"></i> Addresses
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center p-3 rounded-lg text-red-500 hover:bg-red-50">
                            <i class="bi bi-box-arrow-right mr-3"></i> Logout
                        </button>
                    </form>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">My Wishlist</h1>
                            <p class="text-gray-500 text-sm mt-1">{{ $wishlist->count() }} items saved</p>
                        </div>
                        @if($wishlist->count() > 0)
                        <button onclick="if(typeof clearWishlist==='function')clearWishlist()" class="text-red-500 hover:text-red-600 text-sm font-medium">
                            <i class="bi bi-trash mr-1"></i> Clear All
                        </button>
                        @endif
                    </div>
                </div>

                @if($wishlist->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($wishlist as $item)
                    <div class="p-6 flex items-center gap-6 wishlist-item" data-id="{{ $item->product->id }}">
                        <!-- Product Image -->
                        <div class="w-24 h-24 flex-shrink-0">
                            @php
                                $imagePath = $item->product->featured_image ?? $item->product->image ?? '';
                                $imageUrl = 'https://placehold.co/100x100?text=No+Image';
                                if ($imagePath) {
                                    if (str_starts_with($imagePath, 'http')) {
                                        $imageUrl = $imagePath;
                                    } elseif (str_starts_with($imagePath, '/storage/')) {
                                        $imageUrl = $imagePath;
                                    } elseif (str_starts_with($imagePath, '/uploads/')) {
                                        $imageUrl = asset($imagePath);
                                    } else {
                                        $imageUrl = asset('storage/' . $imagePath);
                                    }
                                }
                            @endphp
                            <a href="{{ route('products.show', $item->product->slug) }}">
                                <img src="{{ $imageUrl }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover rounded-lg">
                            </a>
                        </div>

                        <!-- Product Info -->
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('products.show', $item->product->slug) }}" class="text-lg font-semibold text-gray-900 hover:text-primary line-clamp-1">
                                {{ $item->product->name }}
                            </a>
                            @if($item->product->category)
                            <p class="text-sm text-gray-500 mt-1">{{ $item->product->category->name }}</p>
                            @endif
                            
                            <!-- Price -->
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-lg font-bold text-gray-900">৳{{ number_format($item->product->current_price, 2) }}</span>
                                @if($item->product->isOnSale())
                                <span class="text-sm text-gray-500 line-through">৳{{ number_format($item->product->price, 2) }}</span>
                                <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded">-{{ $item->product->discount_percentage }}%</span>
                                @endif
                            </div>

                            <!-- Stock Status -->
                            @if($item->product->stock_status === 'out_of_stock')
                            <p class="text-sm text-red-500 mt-1"><i class="bi bi-x-circle mr-1"></i>Out of Stock</p>
                            @elseif($item->product->quantity <= 5)
                            <p class="text-sm text-orange-500 mt-1"><i class="bi bi-exclamation-circle mr-1"></i>Only {{ $item->product->quantity }} left!</p>
                            @else
                            <p class="text-sm text-green-500 mt-1"><i class="bi bi-check-circle mr-1"></i>In Stock</p>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3">
                            @if($item->product->stock_status === 'in_stock')
                            <button onclick="if(typeof addToCartFromWishlist==='function')addToCartFromWishlist({{ $item->product->id }})" class="bg-halal-green text-white px-4 py-2 rounded-lg hover:text-amber-400 transition-colors text-sm font-medium">
                                <i class="bi bi-cart-plus mr-1"></i> Add to Cart
                            </button>
                            @endif
                            <button onclick="if(typeof removeFromWishlist==='function')removeFromWishlist({{ $item->product->id }})" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors text-sm font-medium">
                                <i class="bi bi-heart-fill mr-1"></i> Remove
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-12 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-heart text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Your wishlist is empty</h3>
                    <p class="text-gray-500 mb-6">Save items you love by clicking the heart icon on products</p>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center bg-halal-green text-white px-6 py-3 rounded-lg hover:text-amber-400 transition-colors font-medium">
                        <i class="bi bi-shopping-bag mr-2"></i> Start Shopping
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function removeFromWishlist(productId) {
        fetch('{{ route("wishlist.toggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`.wishlist-item[data-id="${productId}"]`);
                if (item) {
                    item.remove();
                }
                updateWishlistCount();
                
                // Check if wishlist is now empty
                const remainingItems = document.querySelectorAll('.wishlist-item');
                if (remainingItems.length === 0) {
                    location.reload();
                }
                
                showToast(data.message, 'success');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }

    function addToCartFromWishlist(productId) {
        fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Product added to cart!', 'success');
                updateCartCount();
            } else {
                showToast(data.message || 'Failed to add to cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }

    function clearWishlist() {
        if (!confirm('Are you sure you want to clear your wishlist?')) return;
        
        const productIds = @json($wishlist->pluck('product_id'));
        
        Promise.all(productIds.map(id => 
            fetch('{{ route("wishlist.toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ product_id: id })
            })
        ))
        .then(() => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }

    function updateWishlistCount() {
        const count = document.querySelectorAll('.wishlist-item').length;
        const countElements = document.querySelectorAll('.wishlist-count');
        countElements.forEach(el => {
            el.textContent = count;
            if (count === 0) {
                el.style.display = 'none';
            } else {
                el.style.display = 'flex';
            }
        });
    }

    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'
        } text-white`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
</script>
@endpush
@endsection
