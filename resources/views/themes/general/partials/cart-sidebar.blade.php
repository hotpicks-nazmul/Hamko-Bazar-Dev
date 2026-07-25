<!-- Cart Sidebar Overlay -->
<div id="cartOverlay" class="fixed inset-0 bg-black/50 z-50 hidden" onclick="closeCartSidebar()"></div>

<!-- Cart Sidebar -->
<div id="cartSidebar" class="fixed top-0 right-0 w-full max-w-md h-full bg-white z-50 transform translate-x-full transition-transform duration-300 shadow-2xl">
    <div class="flex flex-col h-full">
        <!-- Header -->
        <div class="p-4 border-b bg-halal-green text-white">
            <div class="flex items-center justify-between">
                <h3 class="font-poppins text-xl font-bold flex items-center">
                    <i class="bi bi-cart3 mr-2"></i> Shopping Cart
                </h3>
                <button onclick="closeCartSidebar()" class="text-white/80 hover:text-white">
                    <i class="bi bi-x-lg text-2xl"></i>
                </button>
            </div>
            <p class="text-sm text-green-100 mt-1">
                <span id="cartItemCount">0</span> items in your cart
            </p>
        </div>
        
        <!-- Cart Items -->
        <div id="cartItems" class="flex-1 overflow-y-auto p-4">
            <!-- Empty Cart State -->
            <div id="emptyCart" class="text-center py-12">
                <div class="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="bi bi-cart-x text-4xl text-gray-400"></i>
                </div>
                <h4 class="text-gray-600 font-medium mb-2">Your cart is empty</h4>
                <p class="text-gray-400 text-sm mb-4">Add some products to get started!</p>
                <a href="{{ route('products.index') }}" onclick="closeCartSidebar()" class="inline-block bg-halal-green text-white px-6 py-2 rounded-full hover:text-amber-400 transition-colors">
                    Start Shopping
                </a>
            </div>
            
            <!-- Cart Items Container -->
            <div id="cartItemsContainer" class="space-y-4 hidden">
                <!-- Items will be loaded here via JavaScript -->
            </div>
        </div>
        
        <!-- Footer -->
        <div id="cartFooter" class="border-t p-4 bg-gray-50 hidden">
            <!-- Subtotal -->
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span id="cartSubtotal">৳0</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Delivery</span>
                    <span id="cartDelivery" class="text-halal-green">Free</span>
                </div>
                <hr>
                <div class="flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span id="cartTotal" class="text-halal-green">৳0</span>
                </div>
            </div>
            
            <!-- Free Delivery Notice -->
            <div id="freeDeliveryNotice" class="bg-green-50 text-halal-green text-sm p-3 rounded-lg mb-4 hidden">
                <i class="bi bi-truck mr-1"></i>
                <span>You've got free delivery!</span>
            </div>
            
            <!-- Actions -->
            <div class="space-y-2">
                <a href="{{ route('cart.index') }}" onclick="closeCartSidebar()" class="block w-full bg-halal-green text-white text-center py-3 rounded-lg font-medium hover:text-amber-400 transition-colors">
                    <i class="bi bi-cart-check mr-2"></i> View Cart
                </a>
                <a href="{{ route('checkout.index') }}" onclick="closeCartSidebar()" class="block w-full bg-halal-gold text-white text-center py-3 rounded-lg font-medium hover:bg-yellow-600 transition-colors">
                    <i class="bi bi-credit-card mr-2"></i> Checkout
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Cart functionality
let cart = [];

async function loadCart() {
    console.log('Loading cart...');
    try {
        const response = await fetch('/api/cart/items', {
            credentials: 'same-origin'
        });
        console.log('Load cart response status:', response.status);
        const data = await response.json();
        console.log('Load cart data:', data);
        
        // Store cart_id in session storage for immediate access
        if (data.cart_id) {
            sessionStorage.setItem('cart_id', data.cart_id);
        }
        
        cart = data.items || [];
        updateCartUI();
    } catch (error) {
        console.error('Error loading cart:', error);
    }
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function updateCartUI() {
    const itemCount = cart.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
    const subtotal = cart.reduce((sum, item) => sum + (Number(item.price || 0) * Number(item.quantity || 0)), 0);
    
    // Update header count
    document.querySelectorAll('.cart-count').forEach(el => {
        el.textContent = itemCount;
    });
    
    // Update sidebar
    document.getElementById('cartItemCount').textContent = itemCount;
    document.getElementById('cartSubtotal').textContent = '৳' + subtotal.toLocaleString();
    document.getElementById('cartTotal').textContent = '৳' + subtotal.toLocaleString();
    
    // Show/hide elements
    const emptyCart = document.getElementById('emptyCart');
    const itemsContainer = document.getElementById('cartItemsContainer');
    const cartFooter = document.getElementById('cartFooter');
    
    if (cart.length === 0) {
        emptyCart.classList.remove('hidden');
        itemsContainer.classList.add('hidden');
        cartFooter.classList.add('hidden');
        // If checkout page is open behind the popup, update it too
        if (typeof showEmptyCartState === 'function') {
            showEmptyCartState();
        }
    } else {
        emptyCart.classList.add('hidden');
        itemsContainer.classList.remove('hidden');
        cartFooter.classList.remove('hidden');
        
        // Render items
        itemsContainer.innerHTML = cart.map(item => {
            let badgesHtml = '';
            
            // Build badges from variant_badges if available
            if (item.variant_badges && item.variant_badges.length > 0) {
                badgesHtml = `<div class="flex flex-wrap items-center justify-start gap-1">`;
                badgesHtml += item.variant_badges.map(badge => {
                    if (badge.type === 'color') {
                        const label = badge.label || 'Color';
                        if (badge.hex && badge.hex.match(/^#[0-9A-Fa-f]{6}$/)) {
                            return `<span class="inline-flex items-center bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded-full"><span class="w-2 h-2 rounded-full border border-gray-400 flex-shrink-0" style="background-color:${badge.hex}"></span><span class="ms-1">${escapeHtml(label)}: ${escapeHtml(badge.value)}</span></span>`;
                        }
                        return `<span class="inline-flex items-center bg-halal-green/10 text-halal-green text-xs px-2 py-0.5 rounded-full">${escapeHtml(label)}: ${escapeHtml(badge.value)}</span>`;
                    }
                    return `<span class="inline-flex items-center bg-halal-green/10 text-halal-green text-xs px-2 py-0.5 rounded-full">${escapeHtml(badge.label)}: ${escapeHtml(badge.value)}</span>`;
                }).join('');
                badgesHtml += `</div>`;
            } else {
                // Fallback: build badges from color_name and attributes directly
                let colorBadge = '';
                if (item.color_name) {
                    if (item.color_hex && item.color_hex.match(/^#[0-9A-Fa-f]{6}$/)) {
                        colorBadge = `<span class="inline-flex items-center bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded-full"><span class="w-2 h-2 rounded-full border border-gray-400 flex-shrink-0" style="background-color:${item.color_hex}"></span><span class="ms-1">Color: ${escapeHtml(item.color_name)}</span></span>`;
                    } else {
                        colorBadge = `<span class="inline-flex items-center bg-halal-green/10 text-halal-green text-xs px-2 py-0.5 rounded-full">Color: ${escapeHtml(item.color_name)}</span>`;
                    }
                }
                
                let attrBadges = '';
                if (item.attributes && item.attributes.length > 0) {
                    attrBadges = item.attributes.map(attr => {
                        return `<span class="inline-flex items-center bg-halal-green/10 text-halal-green text-xs px-2 py-0.5 rounded-full">${escapeHtml(attr.attribute_name || '')}: ${escapeHtml(attr.value || '')}</span>`;
                    }).join('');
                }
                
                badgesHtml = `<div class="flex flex-wrap items-center justify-center gap-1">` + colorBadge + attrBadges + `</div>`;
            }
            
            // Pre-compute quantity values to avoid scope issues in onclick
            const qty = Number(item.quantity) || 0;
            const qtyMinus = qty - 1;
            const qtyPlus = qty + 1;
            
            return `
            <div class="flex items-center space-x-3 bg-white p-3 rounded-lg shadow-sm">
                <img src="${escapeHtml(item.image || 'https://placehold.co/80')}" alt="${escapeHtml(item.name)}" class="w-16 h-16 object-cover rounded-lg">
                <div class="flex-1">
                    <h4 class="font-medium text-gray-800 text-sm">${escapeHtml(item.name)}</h4>
                    ${badgesHtml}
                    <p class="text-halal-green font-medium">৳${parseFloat(item.price).toLocaleString()}</p>
                    <div class="flex items-center space-x-2 mt-1">
                        <button onclick="updateCartItem('${escapeHtml(item.cart_item_id)}', ${qtyMinus})" class="w-6 h-6 bg-gray-100 rounded flex items-center justify-center hover:bg-gray-200">
                            <i class="bi bi-dash text-xs"></i>
                        </button>
                        <span class="text-sm font-medium">${qty}</span>
                        <button onclick="updateCartItem('${escapeHtml(item.cart_item_id)}', ${qtyPlus})" class="w-6 h-6 bg-gray-100 rounded flex items-center justify-center hover:bg-gray-200">
                            <i class="bi bi-plus text-xs"></i>
                        </button>
                    </div>
                </div>
                <button onclick="removeCartItem('${escapeHtml(item.cart_item_id)}')" class="text-red-500 hover:text-red-700">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `}).join('');
    }
    
    // Free delivery notice
    const freeDeliveryNotice = document.getElementById('freeDeliveryNotice');
    const deliveryEl = document.getElementById('cartDelivery');
    if (subtotal >= 500) {
        freeDeliveryNotice.classList.remove('hidden');
        deliveryEl.textContent = 'Free';
        deliveryEl.classList.add('text-halal-green');
    } else {
        freeDeliveryNotice.classList.add('hidden');
        deliveryEl.textContent = '৳60';
        deliveryEl.classList.remove('text-halal-green');
        document.getElementById('cartTotal').textContent = '৳' + (subtotal + 60).toLocaleString();
    }
}

async function addToCart(productId, quantity = 1) {
    console.log('Adding to cart:', productId, quantity);
    try {
        const response = await fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin',
            body: JSON.stringify({ product_id: productId, quantity: quantity })
        });

        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Response data:', data);

        if (data.success) {
            // Use items directly from response
            cart = data.items || [];
            updateCartUI();
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification(data.message || 'Error adding to cart', 'error');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        showNotification('Error adding to cart', 'error');
    }
}

async function updateCartItem(cartItemId, quantity) {
    if (quantity < 1) {
        removeCartItem(cartItemId);
        return;
    }
    
    if (quantity > 99) {
        showToast('Maximum quantity is 99', 'error');
        return;
    }

    try {
        console.log('updateCartItem called:', cartItemId, quantity);
        console.log('CSRF token:', document.querySelector('meta[name="csrf-token"]').content);
        const response = await fetch('/cart/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ cart_item_id: cartItemId, quantity: quantity })
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers.get('content-type'));
        
        if (!response.ok) {
            const text = await response.text();
            console.log('Error response:', text);
            throw new Error('HTTP error ' + response.status);
        }
        
        const data = await response.json();
        console.log('updateCartItem response:', data);

        if (data.success) {
            await loadCart();
        }
    } catch (error) {
        console.error('Error updating cart:', error);
    }
}

async function removeCartItem(cartItemId) {
    try {
        const response = await fetch('/cart/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin',
            body: JSON.stringify({ cart_item_id: cartItemId })
        });

        const data = await response.json();

        if (data.success) {
            await loadCart();
            showNotification('Item removed from cart', 'success');
        }
    } catch (error) {
        console.error('Error removing from cart:', error);
    }
}

function openCartSidebar() {
    document.getElementById('cartSidebar').classList.remove('translate-x-full');
    document.getElementById('cartOverlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCartSidebar() {
    document.getElementById('cartSidebar').classList.add('translate-x-full');
    document.getElementById('cartOverlay').classList.add('hidden');
    document.body.style.overflow = '';
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white flex items-center space-x-2 animate-pulse`;
    notification.innerHTML = `
        <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Load cart on page load
document.addEventListener('DOMContentLoaded', loadCart);
</script>
