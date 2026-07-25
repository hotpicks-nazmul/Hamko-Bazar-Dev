@extends('admin.layouts.app')

@section('title', 'POS Terminal')

@section('content')
<div class="pos-terminal">
    <div class="row g-0">
        <!-- Products Panel - Left Side -->
        <div class="col-lg-8">
            <div class="pos-products-panel h-100 d-flex flex-column">
                <!-- Search and Filter Bar -->
                <div class="pos-search-bar p-3 border-bottom bg-white">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="posSearch" class="form-control" placeholder="Search products by name, SKU, or barcode...">
                                <span class="input-group-text" id="searchSpinner" style="display: none;">
                                    <div class="spinner-border spinner-border-sm"></div>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select id="posCategory" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary w-100" onclick="clearProductSearch()">
                                <i class="bi bi-x-lg"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="pos-products-grid flex-grow-1 p-3" style="overflow-y: auto;">
                    <div id="productsContainer" class="row g-3">
                        <div class="col-12 text-center text-muted py-5">
                            <i class="bi bi-search" style="font-size: 3rem;"></i>
                            <p class="mt-2">Search for products to add to cart</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Panel - Right Side -->
        <div class="col-lg-4">
            <div class="pos-cart-panel h-100 d-flex flex-column bg-light">
                <!-- Cart Header -->
                <div class="pos-cart-header p-3 border-bottom bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Current Sale</h5>
                        <button class="btn btn-sm btn-outline-danger" onclick="clearPOSCart()">
                            <i class="bi bi-trash"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="pos-cart-items flex-grow-1 p-3" style="overflow-y: auto;">
                    <div id="cartItemsContainer">
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                            <p class="mt-2">No items in cart</p>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="pos-cart-summary p-3 border-top bg-white">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span id="cartSubtotal">৳0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount</span>
                            <span id="cartDiscount">-৳0.00</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total</span>
                            <span id="cartTotal">৳0.00</span>
                        </div>
                    </div>

                    <!-- Discount Button -->
                    <button class="btn btn-outline-secondary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#discountModal">
                        <i class="bi bi-percent me-1"></i> Apply Discount
                    </button>

                    <!-- Checkout Button -->
                    <button class="btn btn-primary w-100 btn-lg" data-bs-toggle="modal" data-bs-target="#checkoutModal" onclick="prepareCheckout()">
                        <i class="bi bi-credit-card me-1"></i> Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Discount Modal -->
<div class="modal fade" id="discountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Discount Type</label>
                    <select id="discountType" class="form-select">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount (৳)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Discount Value</label>
                    <input type="number" id="discountValue" class="form-control" min="0" placeholder="Enter value">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyPOSDiscount()">Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Checkout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="checkoutForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" id="customerName" name="customer_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Customer Phone</label>
                                <input type="text" id="customerPhone" name="customer_phone" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Customer Email</label>
                        <input type="email" id="customerEmail" name="customer_email" class="form-control">
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="paymentCash" value="cash" checked>
                                <label class="form-check-label" for="paymentCash">
                                    <i class="bi bi-cash me-1"></i> Cash
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="paymentCard" value="card">
                                <label class="form-check-label" for="paymentCard">
                                    <i class="bi bi-credit-card me-1"></i> Card
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="paymentDigital" value="digital_wallet">
                                <label class="form-check-label" for="paymentDigital">
                                    <i class="bi bi-wallet2 me-1"></i> Digital Wallet
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount Paid <span class="text-danger">*</span></label>
                        <input type="number" id="paidAmount" name="paid_amount" class="form-control form-control-lg" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea id="orderNotes" name="notes" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between">
                            <span>Total:</span>
                            <strong id="checkoutTotal">৳0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span>Change:</span>
                            <strong id="checkoutChange">৳0.00</strong>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-lg" onclick="processCheckout()">
                    <i class="bi bi-check-lg me-1"></i> Complete Sale
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sale Complete!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h4>Order #<span id="receiptOrderNumber"></span></h4>
                <hr>
                <div class="text-start" id="receiptContent">
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Change Due:</span>
                    <span id="receiptChange">৳0.00</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* POS Terminal - Full height layout (Preference.md #21 - No extra padding/wrappers) */
    .pos-terminal {
        height: calc(100vh - 140px);
        display: flex;
        flex-direction: column;
    }

    .pos-products-panel {
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .pos-products-grid {
        max-height: calc(100vh - 300px);
        overflow-y: auto;
    }

    .pos-cart-panel {
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Product Card Styles */
    .pos-product-card {
        border: 1px solid var(--color-gray-200, #e5e7eb);
        border-radius: var(--radius-lg, 0.5rem);
        padding: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: var(--color-white, #fff);
        text-align: center;
    }

    .pos-product-card:hover {
        border-color: var(--color-primary, #4f46e5);
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.15);
        transform: translateY(-2px);
    }

    .pos-product-card .product-image {
        width: 100%;
        height: 100px;
        object-fit: cover;
        border-radius: var(--radius-md, 0.375rem);
        margin-bottom: 8px;
        background-color: var(--color-gray-100, #f3f4f6);
    }

    .pos-product-card .product-name {
        font-size: 14px;
        font-weight: 500;
        color: var(--color-gray-800, #1f2937);
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pos-product-card .product-price {
        font-size: 16px;
        font-weight: 700;
        color: var(--color-primary, #4f46e5);
        margin-bottom: 2px;
    }

    .pos-product-card .product-stock {
        font-size: 12px;
        color: var(--color-gray-500, #6b7280);
    }

    /* Cart Item Styles */
    .pos-cart-item {
        display: flex;
        align-items: center;
        padding: 10px;
        margin-bottom: 8px;
        background: var(--color-white, #fff);
        border-radius: var(--radius-md, 0.375rem);
        border: 1px solid var(--color-gray-200, #e5e7eb);
    }

    .pos-cart-item .item-image {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: var(--radius-sm, 0.25rem);
        background-color: var(--color-gray-100, #f3f4f6);
        flex-shrink: 0;
    }

    .pos-cart-item .item-details {
        flex: 1;
        min-width: 0;
        margin-left: 8px;
    }

    .pos-cart-item .item-name {
        font-size: 13px;
        font-weight: 500;
        color: var(--color-gray-800, #1f2937);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pos-cart-item .item-price {
        font-size: 12px;
        color: var(--color-gray-500, #6b7280);
    }

    .pos-cart-item .item-quantity {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .pos-cart-item .item-quantity .qty-btn {
        width: 24px;
        height: 24px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }

    .pos-cart-item .item-total {
        font-size: 14px;
        font-weight: 600;
        color: var(--color-gray-800, #1f2937);
        white-space: nowrap;
    }

    .pos-cart-item .remove-btn {
        background: none;
        border: none;
        color: var(--color-danger, #ef4444);
        cursor: pointer;
        padding: 4px;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .pos-cart-item .remove-btn:hover {
        opacity: 1;
    }

    /* Force Bootstrap Icons to display (same as inventory page) */
    .bi::before,
    [class*="bi bi-"]::before {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-family: 'bootstrap-icons' !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let posCart = @json($cart ?? []);
    let discount = { type: 'fixed', value: 0, amount: 0 };
    let currentSubtotal = 0;
    let currentTotal = 0;

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadCartFromSession();
        // Load initial products if none are showing
        if (Object.keys(posCart).length === 0) {
            searchProducts();
        }
    });

    // Product Search
    let searchTimeout;
    document.getElementById('posSearch').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        document.getElementById('searchSpinner').style.display = 'block';
        searchTimeout = setTimeout(() => {
            searchProducts();
        }, 300);
    });

    document.getElementById('posCategory').addEventListener('change', function() {
        searchProducts();
    });

    function clearProductSearch() {
        document.getElementById('posSearch').value = '';
        document.getElementById('posCategory').value = '';
        searchProducts();
    }

    function searchProducts() {
        const search = document.getElementById('posSearch').value;
        const category = document.getElementById('posCategory').value;

        fetch(`{{ route('admin.pos.products.search') }}?search=${search}&category_id=${category}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('searchSpinner').style.display = 'none';
            renderProducts(data.products);
        })
        .catch(err => {
            document.getElementById('searchSpinner').style.display = 'none';
            console.error(err);
        });
    }

    function renderProducts(products) {
        const container = document.getElementById('productsContainer');
        
        if (products.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center text-muted py-5">
                    <i class="bi bi-search" style="font-size: 3rem;"></i>
                    <p class="mt-2">No products found</p>
                </div>
            `;
            return;
        }

        let html = '';
        products.forEach(product => {
            const imageUrl = product.image_url || '';
            html += `
                <div class="col-md-4 col-sm-6">
                    <div class="pos-product-card" onclick="addToCart(${product.id})">
                        ${imageUrl ? `<img src="${imageUrl}" class="product-image" alt="${product.name}">` : 
                          `<div class="product-image d-flex align-items-center justify-content-center"><i class="bi bi-image text-muted" style="font-size: 2rem;"></i></div>`}
                        <div class="product-name">${product.name}</div>
                        <div class="product-price">৳${parseFloat(product.unit_price).toFixed(2)}</div>
                        <div class="product-stock">Stock: ${product.stock}</div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    // Cart Functions
    function addToCart(productId) {
        fetch(`{{ route('admin.pos.cart.add') }}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                posCart = data.cart;
                currentSubtotal = data.cart_total;
                updateCartUI();
                showToast('Product added to cart');
            } else {
                showToast(data.message, 'error');
            }
        });
    }

    function updateCartItem(productId, quantity) {
        fetch(`{{ route('admin.pos.cart.update') }}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId, quantity: quantity })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                posCart = data.cart;
                currentSubtotal = data.cart_total;
                updateCartUI();
            } else {
                showToast(data.message, 'error');
            }
        });
    }

    function removeFromCart(productId) {
        fetch(`{{ route('admin.pos.cart.remove') }}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(res => res.json())
        .then(data => {
            posCart = data.cart;
            currentSubtotal = data.cart_total;
            updateCartUI();
        });
    }

    function clearPOSCart() {
        if (!confirm('Are you sure you want to clear the cart?')) return;
        
        fetch(`{{ route('admin.pos.cart.clear') }}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            posCart = {};
            currentSubtotal = 0;
            discount = { type: 'fixed', value: 0, amount: 0 };
            updateCartUI();
        });
    }

    function applyPOSDiscount() {
        const type = document.getElementById('discountType').value;
        const value = parseFloat(document.getElementById('discountValue').value) || 0;

        fetch(`{{ route('admin.pos.cart.discount') }}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ discount_type: type, discount_value: value })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                discount = { type: type, value: value, amount: data.discount };
                currentTotal = data.total;
                updateCartUI();
                $('#discountModal').modal('hide');
                showToast('Discount applied');
            }
        });
    }

    function updateCartUI() {
        // Update Cart Items
        const cartContainer = document.getElementById('cartItemsContainer');
        const cartKeys = Object.keys(posCart);
        
        if (cartKeys.length === 0) {
            cartContainer.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                    <p class="mt-2">No items in cart</p>
                </div>
            `;
        } else {
            let itemsHtml = '';
            cartKeys.forEach(key => {
                const item = posCart[key];
                const itemTotal = item.price * item.quantity;
                itemsHtml += `
                    <div class="pos-cart-item">
                        ${item.image ? `<img src="${item.image}" class="item-image" alt="${item.name}">` : 
                          `<div class="item-image d-flex align-items-center justify-content-center"><i class="bi bi-image text-muted"></i></div>`}
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            <div class="item-price">৳${parseFloat(item.price).toFixed(2)} × ${item.quantity}</div>
                        </div>
                        <div class="item-quantity">
                            <button class="btn btn-sm btn-outline-secondary qty-btn" onclick="updateCartItem(${item.product_id}, ${item.quantity - 1})">-</button>
                            <span>${item.quantity}</span>
                            <button class="btn btn-sm btn-outline-secondary qty-btn" onclick="updateCartItem(${item.product_id}, ${item.quantity + 1})">+</button>
                        </div>
                        <div class="item-total ms-3">৳${itemTotal.toFixed(2)}</div>
                        <button class="remove-btn ms-2" onclick="removeFromCart(${item.product_id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
            });
            cartContainer.innerHTML = itemsHtml;
        }

        // Update Summary
        const discountAmount = discount.amount || 0;
        const total = currentSubtotal - discountAmount;
        
        document.getElementById('cartSubtotal').textContent = '৳' + currentSubtotal.toFixed(2);
        document.getElementById('cartDiscount').textContent = '-৳' + discountAmount.toFixed(2);
        document.getElementById('cartTotal').textContent = '৳' + total.toFixed(2);
        currentTotal = total;

        // Update checkout modal totals
        document.getElementById('checkoutTotal').textContent = '৳' + total.toFixed(2);
        document.getElementById('paidAmount').value = total.toFixed(2);
        document.getElementById('checkoutChange').textContent = '৳0.00';
    }

    // Update change amount when paid amount changes
    document.getElementById('paidAmount').addEventListener('input', function() {
        const paid = parseFloat(this.value) || 0;
        const change = paid - currentTotal;
        document.getElementById('checkoutChange').textContent = '৳' + (change >= 0 ? change.toFixed(2) : '0.00');
        document.getElementById('checkoutChange').classList.toggle('text-success', change >= 0);
        document.getElementById('checkoutChange').classList.toggle('text-danger', change < 0);
    });

    function prepareCheckout() {
        document.getElementById('paidAmount').value = currentTotal.toFixed(2);
        document.getElementById('checkoutChange').textContent = '৳0.00';
    }

    function processCheckout() {
        const form = document.getElementById('checkoutForm');
        const formData = new FormData(form);
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        fetch(`{{ route('admin.pos.checkout') }}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                customer_name: formData.get('customer_name'),
                customer_phone: formData.get('customer_phone'),
                customer_email: formData.get('customer_email'),
                payment_method: formData.get('payment_method'),
                paid_amount: parseFloat(formData.get('paid_amount')),
                notes: formData.get('notes')
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                $('#checkoutModal').modal('hide');
                showReceipt(data.receipt);
                posCart = {};
                currentSubtotal = 0;
                discount = { type: 'fixed', value: 0, amount: 0 };
                updateCartUI();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(err => {
            showToast('An error occurred', 'error');
        });
    }

    function showReceipt(receipt) {
        document.getElementById('receiptOrderNumber').textContent = receipt.order_number;
        document.getElementById('receiptChange').textContent = '৳' + receipt.change.toFixed(2);
        
        let itemsHtml = '';
        receipt.items.forEach(item => {
            itemsHtml += `
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <strong>${item.name}</strong><br>
                        <small class="text-muted">${item.quantity} × ৳${parseFloat(item.price).toFixed(2)}</small>
                    </div>
                    <div>৳${(item.price * item.quantity).toFixed(2)}</div>
                </div>
            `;
        });

        document.getElementById('receiptContent').innerHTML = `
            <div class="mb-3">
                ${itemsHtml}
            </div>
            <div class="d-flex justify-content-between">
                <span>Subtotal:</span>
                <span>৳${receipt.subtotal.toFixed(2)}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Discount:</span>
                <span>-৳${receipt.discount.toFixed(2)}</span>
            </div>
            <div class="d-flex justify-content-between fw-bold">
                <span>Total:</span>
                <span>৳${receipt.total.toFixed(2)}</span>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <span>Paid (${receipt.payment_method}):</span>
                <span>৳${receipt.paid_amount.toFixed(2)}</span>
            </div>
        `;

        $('#receiptModal').modal('show');
    }

    function printReceipt() {
        window.print();
    }

    function loadCartFromSession() {
        // Load cart from session data passed from controller
        const cartKeys = Object.keys(posCart);
        if (cartKeys.length > 0) {
            currentSubtotal = 0;
            cartKeys.forEach(key => {
                const item = posCart[key];
                currentSubtotal += item.price * item.quantity;
            });
        } else {
            currentSubtotal = 0;
        }
        updateCartUI();
    }

    function showToast(message, type = 'success') {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `toast-container position-fixed top-0 end-0 p-3`;
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header ${type === 'error' ? 'bg-danger' : 'bg-success'} text-white">
                    <strong class="me-auto">${type === 'error' ? 'Error' : 'Success'}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
</script>
@endpush