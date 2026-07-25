<!-- Wishlist Sidebar Overlay -->
<div id="wishlistOverlay" class="fixed inset-0 bg-black/50 z-50 hidden" onclick="closeWishlistSidebar()"></div>

<!-- Wishlist Sidebar -->
<div id="wishlistSidebar" class="fixed top-0 left-0 w-full max-w-md h-full bg-white z-50 transform -translate-x-full transition-transform duration-300 shadow-2xl">
    <div class="flex flex-col h-full">
        <!-- Header -->
        <div class="p-4 border-b bg-red-500 text-white">
            <div class="flex items-center justify-between">
                <h3 class="font-poppins text-xl font-bold flex items-center">
                    <i class="bi bi-heart-fill mr-2"></i> My Wishlist
                </h3>
                <button onclick="closeWishlistSidebar()" class="text-white/80 hover:text-white">
                    <i class="bi bi-x-lg text-2xl"></i>
                </button>
            </div>
            <p class="text-sm text-red-100 mt-1">
                <span id="wishlistItemCount">0</span> items in your wishlist
            </p>
        </div>
        
        <!-- Wishlist Items -->
        <div id="wishlistItems" class="flex-1 overflow-y-auto p-4">
            <!-- Empty Wishlist State -->
            <div id="emptyWishlist" class="text-center py-12">
                <div class="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="bi bi-heart text-4xl text-gray-400"></i>
                </div>
                <h4 class="text-gray-600 font-medium mb-2">Your wishlist is empty</h4>
                <p class="text-gray-400 text-sm mb-4">Save items you love to your wishlist</p>
                <a href="{{ route('products.index') }}" onclick="closeWishlistSidebar()" class="inline-block bg-halal-green text-white px-6 py-2 rounded-full hover:text-amber-400 transition-colors">
                    Start Shopping
                </a>
            </div>
            
            <!-- Wishlist Items Container -->
            <div id="wishlistItemsContainer" class="space-y-4 hidden">
                <!-- Items will be loaded here via JavaScript -->
            </div>
            
            <!-- Footer -->
            <div id="wishlistFooter" class="border-t p-4 bg-gray-50 hidden">
                <!-- Actions -->
                <div class="space-y-2">
                <a href="{{ route('account.wishlist') }}" onclick="closeWishlistSidebar()" class="block w-full bg-halal-green text-white text-center py-3 rounded-lg font-medium hover:text-amber-400 transition-colors">
                    <i class="bi bi-heart mr-2"></i> View Full Wishlist
                </a>
                <button onclick="addAllToCart()" class="block w-full bg-halal-gold text-white text-center py-3 rounded-lg font-medium hover:bg-yellow-600 transition-colors">
                    <i class="bi bi-cart-plus mr-2"></i> Add All to Cart
                </button>
            </div>
        </div>
    </div>
</div>
</div>

<script>
// Wishlist functionality
let wishlistItems = [];

async function loadWishlist() {
    console.log('Loading wishlist...');
    try {
        const response = await fetch('/api/wishlist/items', {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        console.log('Load wishlist response status:', response.status);
        const data = await response.json();
        console.log('Load wishlist data:', data);
        
        wishlistItems = data.items || [];
        updateWishlistUI();
    } catch (error) {
        console.error('Error loading wishlist:', error);
    }
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function updateWishlistUI() {
    const itemCount = wishlistItems.length;
    
    // Update header count
    document.querySelectorAll('.wishlist-count').forEach(el => {
        el.textContent = itemCount;
        if (itemCount > 0) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    });
    
    // Update sidebar
    document.getElementById('wishlistItemCount').textContent = itemCount;
    
    // Show/hide elements
    const emptyWishlist = document.getElementById('emptyWishlist');
    const itemsContainer = document.getElementById('wishlistItemsContainer');
    const wishlistFooter = document.getElementById('wishlistFooter');
    
    if (wishlistItems.length === 0) {
        emptyWishlist.classList.remove('hidden');
        itemsContainer.classList.add('hidden');
        wishlistFooter.classList.add('hidden');
    } else {
        emptyWishlist.classList.add('hidden');
        itemsContainer.classList.remove('hidden');
        wishlistFooter.classList.remove('hidden');
        
        // Render items
        itemsContainer.innerHTML = wishlistItems.map(item => `
            <div class="flex items-center space-x-3 bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                <img src="${escapeHtml(item.image || 'https://placehold.co/80')}" alt="${escapeHtml(item.name)}" class="w-16 h-16 object-cover rounded-lg">
                <div class="flex-1">
                    <h4 class="font-medium text-gray-800 text-sm line-clamp-2">${escapeHtml(item.name)}</h4>
                    <div class="flex items-center space-x-2 mt-1">
                        ${item.sale_price ? `
                            <span class="text-halal-green font-medium">৳${parseFloat(item.sale_price).toLocaleString()}</span>
                            <span class="text-gray-400 text-sm line-through">৳${parseFloat(item.price).toLocaleString()}</span>
                        ` : `
                            <span class="text-halal-green font-medium">৳${parseFloat(item.price).toLocaleString()}</span>
                        `}
                    </div>
                    <div class="flex items-center space-x-2 mt-2">
                        <button onclick="addWishlistItemToCart(${item.id})" class="text-xs bg-halal-green text-white px-3 py-1 rounded-full hover:bg-halal-dark transition-colors">
                            <i class="bi bi-cart-plus mr-1"></i> Add to Cart
                        </button>
                    </div>
                </div>
                <button onclick="removeWishlistItem(${item.id})" class="text-red-500 hover:text-red-700 p-1">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `).join('');
    }
}

async function toggleWishlist(productId) {
    console.log('Toggling wishlist:', productId);
    try {
        const response = await fetch('/api/wishlist/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ product_id: productId })
        });

        const data = await response.json();

        if (data.success) {
            wishlistItems = data.items || [];
            updateWishlistUI();
            showWishlistNotification(data.message, 'success');
            
            // Update button state if exists
            var btn = $('.wishlist-btn-' + productId);
            if (btn.length) {
                if (data.added) {
                    btn.addClass('bg-red-500 text-white').removeClass('text-gray-900 bg-white');
                    btn.find('svg').attr('fill', 'currentColor');
                    btn.find('i.bi-heart').removeClass('bi-heart').addClass('bi-heart-fill');
                } else {
                    btn.removeClass('bg-red-500 text-white').addClass('text-gray-900 bg-white');
                    btn.find('svg').attr('fill', 'none');
                    btn.find('i.bi-heart-fill').removeClass('bi-heart-fill').addClass('bi-heart');
                }
            }
        } else if (data.login_required) {
            showWishlistNotification(data.message, 'error');
            setTimeout(function() {
                window.location.href = '/login';
            }, 1500);
        }
    } catch (error) {
        console.error('Error toggling wishlist:', error);
        showWishlistNotification('Error updating wishlist', 'error');
    }
}

async function removeWishlistItem(productId) {
    try {
        const response = await fetch('/api/wishlist/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ product_id: productId })
        });

        const data = await response.json();

        if (data.success) {
            wishlistItems = data.items || [];
            updateWishlistUI();
            showWishlistNotification('Item removed from wishlist', 'success');
            
            // Update product card button state
            var btn = $('.wishlist-btn-' + productId);
            if (btn.length) {
                btn.removeClass('bg-red-500 text-white').addClass('text-gray-900 bg-white');
                btn.find('svg').attr('fill', 'none');
                btn.find('i.bi-heart-fill').removeClass('bi-heart-fill').addClass('bi-heart');
            }
        }
    } catch (error) {
        console.error('Error removing from wishlist:', error);
    }
}

async function addWishlistItemToCart(productId) {
    await addToCart(productId, 1);
    showWishlistNotification('Item added to cart!', 'success');
}

async function addAllToCart() {
    if (wishlistItems.length === 0) return;
    
    let addedCount = 0;
    for (const item of wishlistItems) {
        try {
            await addToCart(item.id, 1);
            addedCount++;
        } catch (error) {
            console.error('Error adding item to cart:', error);
        }
    }
    
    if (addedCount > 0) {
        showWishlistNotification(`${addedCount} items added to cart!`, 'success');
    }
}

function openWishlistSidebar() {
    document.getElementById('wishlistSidebar').classList.remove('-translate-x-full');
    document.getElementById('wishlistOverlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    loadWishlist();
}

function closeWishlistSidebar() {
    document.getElementById('wishlistSidebar').classList.add('-translate-x-full');
    document.getElementById('wishlistOverlay').classList.add('hidden');
    document.body.style.overflow = '';
}

function showWishlistNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 left-4 z-50 px-6 py-3 rounded-lg shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white flex items-center space-x-2`;
    notification.innerHTML = `
        <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Load wishlist on page load
document.addEventListener('DOMContentLoaded', loadWishlist);
</script>
