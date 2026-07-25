@extends('admin.layouts.app')

@section('title', 'Create Inhouse Order')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Create Inhouse Order</h4>
        <small class="text-muted">Create a new order manually for customers</small>
    </div>
</div>

<form id="orderForm" method="POST" action="{{ route('admin.orders.in-house.store') }}">
    @csrf
    
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Customer Selection -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h5>
                </div>
                <div class="card-body">
                    <!-- Customer Search -->
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="customerSearch" class="form-control" placeholder="Search customers by name, email or phone..." autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearCustomerSelection()">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div id="customerSearchResults" class="list-group position-absolute w-50" style="z-index: 1000; max-height: 300px; overflow-y: auto; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Selected Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customerSelect" class="form-select" onchange="loadCustomerDetails()">
                                <option value="">-- Select a Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                        data-name="{{ $customer->name }}"
                                        data-email="{{ $customer->email }}"
                                        data-phone="{{ $customer->phone }}"
                                        data-address="{{ $customer->address }}"
                                        data-city="{{ $customer->city }}"
                                        data-state="{{ $customer->state }}"
                                        data-country="{{ $customer->country ?? 'Bangladesh' }}"
                                        data-postal="{{ $customer->postal_code }}">
                                        {{ $customer->name }} - {{ $customer->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Selection -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-box me-2"></i>Add Products</h5>
                </div>
                <div class="card-body">
                    <!-- Search Product -->
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="productSearch" class="form-control" placeholder="Search products by name or SKU...">
                        </div>
                    </div>
                    
                    <!-- Product List -->
                    <div class="row g-2 mb-3" id="productList" style="max-height: 400px; overflow-y: auto;">
                        @forelse($products as $product)
                        <div class="col-md-6 col-sm-6">
                            <div class="card border product-select-card p-2" onclick="addProductToOrder({{ $product->id }}, '{{ $product->name }}', {{ $product->price }}, {{ $product->quantity }})">
                                <div class="d-flex align-items-center">
                                    @php
                                        $imageUrl = $product->featured_image;
                                        if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                            $imageUrl = '/storage/' . $imageUrl;
                                        }
                                    @endphp
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-2" style="width: 50px; height: 50px;">
                                            <i class="bi bi-box text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-medium text-truncate">{{ $product->name }}</div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-success">৳{{ number_format($product->price, 2) }}</small>
                                            <small class="text-muted">Stock: {{ $product->quantity }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                            <p class="text-muted">No active products found.</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Order Items Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="orderItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th style="width: 100px;">Price</th>
                                    <th style="width: 100px;">Qty</th>
                                    <th style="width: 100px;">Total</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="orderItemsBody">
                                <tr id="noItemsRow">
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-cart d-block mb-2 fs-4"></i>
                                        No products added yet. Click on products above to add them.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Billing Address</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="billing_first_name" id="billing_first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="billing_last_name" id="billing_last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="billing_email" id="billing_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="billing_phone" id="billing_phone" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea name="billing_address" id="billing_address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="billing_city" id="billing_city" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" name="billing_state" id="billing_state" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Postal Code <span class="text-danger">*</span></label>
                            <input type="text" name="billing_postcode" id="billing_postcode" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" name="billing_country" id="billing_country" class="form-control" value="Bangladesh" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Shipping Address</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sameAsBilling" onchange="copyBillingAddress()">
                            <label class="form-check-label" for="sameAsBilling">
                                Same as Billing
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="shipping_first_name" id="shipping_first_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="shipping_last_name" id="shipping_last_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="shipping_email" id="shipping_email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="shipping_phone" id="shipping_phone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="shipping_address" id="shipping_address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="shipping_city" id="shipping_city" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="shipping_state" id="shipping_state" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="shipping_postcode" id="shipping_postcode" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="shipping_country" id="shipping_country" class="form-control" value="Bangladesh">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Notes -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-sticky me-2"></i>Order Notes</h5>
                </div>
                <div class="card-body">
                    <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes for this order..."></textarea>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Payment Information -->
            <div class="card border-0 shadow-sm mb-4 order-summary">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">-- Select Payment Method --</option>
                            <option value="cash">Cash on Delivery</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Status <span class="text-danger">*</span></label>
                        <select name="payment_status" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span class="fw-semibold">৳<span id="subtotalAmount">0.00</span></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping Cost</span>
                        <span class="fw-semibold">৳<span id="shippingCost">0.00</span></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-0">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-primary h5 mb-0">৳<span id="totalAmount">0.00</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Save Buttons -->
    <div class="floating-save-container">
        <a href="{{ route('admin.orders.in-house') }}" class="btn btn-secondary floating-reset-btn">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button type="submit" form="orderForm" class="btn btn-primary floating-save-btn">
            <i class="bi bi-check-lg me-1"></i> Create Order
        </button>
    </div>
</form>

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
    .product-select-card {
        cursor: pointer;
        transition: all 0.2s;
    }
    .product-select-card:hover {
        border-color: #6c757d !important;
    }
    .product-select-card.selected {
        border-color: #0d6efd !important;
        background-color: #f0f7ff;
    }
    .order-summary {
        position: sticky;
        top: 100px;
    }
    #customerSearchResults {
        border: 1px solid #dee2e6;
        border-top: none;
    }
    #customerSearchResults .list-group-item {
        border-radius: 0;
    }
    #customerSearchResults .list-group-item:first-child {
        border-top: none;
    }
</style>
@endpush

@push('scripts')
<script>
let orderItems = [];
let subtotal = 0;
let customerSearchTimeout;

// Search customers via AJAX
document.getElementById('customerSearch').addEventListener('input', function() {
    clearTimeout(customerSearchTimeout);
    const searchTerm = this.value.trim();
    const resultsContainer = document.getElementById('customerSearchResults');
    
    if (searchTerm.length < 2) {
        resultsContainer.style.display = 'none';
        return;
    }
    
    customerSearchTimeout = setTimeout(() => {
        fetch(`{{ route('admin.orders.search-customers') }}?q=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.customers.length > 0) {
                    let html = '';
                    data.customers.forEach(customer => {
                        html += `
                            <button type="button" class="list-group-item list-group-item-action" 
                                onclick="selectCustomer(${customer.id}, '${customer.name}', '${customer.email}', '${customer.phone || ''}')">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-medium">${customer.name}</div>
                                        <small class="text-muted">${customer.email}</small>
                                    </div>
                                    <small class="text-muted">${customer.phone || ''}</small>
                                </div>
                            </button>
                        `;
                    });
                    resultsContainer.innerHTML = html;
                    resultsContainer.style.display = 'block';
                } else {
                    resultsContainer.innerHTML = '<div class="list-group-item text-muted">No customers found</div>';
                    resultsContainer.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error searching customers:', error);
                resultsContainer.style.display = 'none';
            });
    }, 300);
});

// Select customer from search results
function selectCustomer(id, name, email, phone) {
    const select = document.getElementById('customerSelect');
    select.value = id;
    
    // Populate customer details (basic info - address fields need to be filled manually)
    document.getElementById('billing_first_name').value = name.split(' ')[0];
    document.getElementById('billing_last_name').value = name.split(' ').slice(1).join(' ') || '';
    document.getElementById('billing_email').value = email;
    document.getElementById('billing_phone').value = phone || '';
    // Address fields left empty - user can fill them
    document.getElementById('billing_address').value = '';
    document.getElementById('billing_city').value = '';
    document.getElementById('billing_state').value = '';
    document.getElementById('billing_postcode').value = '';
    document.getElementById('billing_country').value = 'Bangladesh';
    
    // Clear search
    document.getElementById('customerSearch').value = '';
    document.getElementById('customerSearchResults').style.display = 'none';
    
    // Show selected customer info
    showSelectedCustomerInfo(name, email, phone);
}

// Show selected customer info
function showSelectedCustomerInfo(name, email, phone) {
    let infoDiv = document.getElementById('selectedCustomerInfo');
    if (!infoDiv) {
        const select = document.getElementById('customerSelect');
        const label = select.parentElement;
        infoDiv = document.createElement('div');
        infoDiv.id = 'selectedCustomerInfo';
        infoDiv.className = 'mt-2 p-2 bg-success bg-opacity-10 rounded border border-success';
        label.parentElement.insertBefore(infoDiv, label.nextSibling);
    }
    infoDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>${name}</strong><br>
                <small>${email} ${phone ? ' | ' + phone : ''}</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCustomerSelection()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    `;
}

// Clear customer selection
function clearCustomerSelection() {
    document.getElementById('customerSelect').value = '';
    document.getElementById('customerSearch').value = '';
    document.getElementById('customerSearchResults').style.display = 'none';
    
    // Clear billing fields
    document.getElementById('billing_first_name').value = '';
    document.getElementById('billing_last_name').value = '';
    document.getElementById('billing_email').value = '';
    document.getElementById('billing_phone').value = '';
    document.getElementById('billing_address').value = '';
    document.getElementById('billing_city').value = '';
    document.getElementById('billing_state').value = '';
    document.getElementById('billing_postcode').value = '';
    document.getElementById('billing_country').value = 'Bangladesh';
    
    // Remove selected customer info
    const infoDiv = document.getElementById('selectedCustomerInfo');
    if (infoDiv) infoDiv.remove();
}

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    const searchInput = document.getElementById('customerSearch');
    const resultsContainer = document.getElementById('customerSearchResults');
    if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
        resultsContainer.style.display = 'none';
    }
});

// Load customer details when customer is selected from dropdown
function loadCustomerDetails() {
    const select = document.getElementById('customerSelect');
    const option = select.options[select.selectedIndex];
    
    if (option && option.value) {
        document.getElementById('billing_first_name').value = option.dataset.name ? option.dataset.name.split(' ')[0] : '';
        document.getElementById('billing_last_name').value = option.dataset.name ? option.dataset.name.split(' ').slice(1).join(' ') : '';
        document.getElementById('billing_email').value = option.dataset.email || '';
        document.getElementById('billing_phone').value = option.dataset.phone || '';
        document.getElementById('billing_address').value = option.dataset.address || '';
        document.getElementById('billing_city').value = option.dataset.city || '';
        document.getElementById('billing_state').value = option.dataset.state || '';
        document.getElementById('billing_postcode').value = option.dataset.postal || '';
        document.getElementById('billing_country').value = option.dataset.country || 'Bangladesh';
        
        showSelectedCustomerInfo(option.dataset.name, option.dataset.email, option.dataset.phone);
    } else {
        clearCustomerSelection();
    }
}

// Add product to order
function addProductToOrder(productId, productName, productPrice, productStock) {
    // Check if product already exists
    const existingItem = orderItems.find(item => item.id === productId);
    if (existingItem) {
        // Increase quantity
        existingItem.quantity++;
        existingItem.total = existingItem.quantity * existingItem.price;
    } else {
        // Add new item
        orderItems.push({
            id: productId,
            name: productName,
            price: productPrice,
            quantity: 1,
            total: productPrice,
            stock: productStock
        });
    }
    
    renderOrderItems();
}

// Remove product from order
function removeProduct(productId) {
    orderItems = orderItems.filter(item => item.id !== productId);
    renderOrderItems();
}

// Update quantity
function updateQuantity(productId, newQuantity) {
    const item = orderItems.find(i => i.id === productId);
    if (item && newQuantity > 0) {
        item.quantity = newQuantity;
        item.total = item.quantity * item.price;
        renderOrderItems();
    }
}

// Render order items table
function renderOrderItems() {
    const tbody = document.getElementById('orderItemsBody');
    const noItemsRow = document.getElementById('noItemsRow');
    
    if (orderItems.length === 0) {
        tbody.innerHTML = `
            <tr id="noItemsRow">
                <td colspan="5" class="text-center py-4 text-muted">
                    <i class="bi bi-cart d-block mb-2 fs-4"></i>
                    No products added yet. Click on products above to add them.
                </td>
            </tr>
        `;
    } else {
        let html = '';
        orderItems.forEach(item => {
            html += `
                <tr>
                    <td>
                        <div class="fw-medium">${item.name}</div>
                        <input type="hidden" name="products[${item.id}][product_id]" value="${item.id}">
                    </td>
                    <td>৳${item.price.toFixed(2)}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm" 
                            value="${item.quantity}" min="1" max="${item.stock}"
                            onchange="updateQuantity(${item.id}, parseInt(this.value))">
                        <input type="hidden" name="products[${item.id}][quantity]" value="${item.quantity}">
                    </td>
                    <td>৳${item.total.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProduct(${item.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
    
    // Update summary
    subtotal = orderItems.reduce((sum, item) => sum + item.total, 0);
    document.getElementById('subtotalAmount').textContent = subtotal.toFixed(2);
    document.getElementById('totalAmount').textContent = (subtotal + 0).toFixed(2); // Add shipping cost if needed
}

// Copy billing address to shipping
function copyBillingAddress() {
    const sameAsBilling = document.getElementById('sameAsBilling');
    if (sameAsBilling.checked) {
        document.getElementById('shipping_first_name').value = document.getElementById('billing_first_name').value;
        document.getElementById('shipping_last_name').value = document.getElementById('billing_last_name').value;
        document.getElementById('shipping_email').value = document.getElementById('billing_email').value;
        document.getElementById('shipping_phone').value = document.getElementById('billing_phone').value;
        document.getElementById('shipping_address').value = document.getElementById('billing_address').value;
        document.getElementById('shipping_city').value = document.getElementById('billing_city').value;
        document.getElementById('shipping_state').value = document.getElementById('billing_state').value;
        document.getElementById('shipping_postcode').value = document.getElementById('billing_postcode').value;
        document.getElementById('shipping_country').value = document.getElementById('billing_country').value;
    } else {
        // Clear shipping fields
        document.getElementById('shipping_first_name').value = '';
        document.getElementById('shipping_last_name').value = '';
        document.getElementById('shipping_email').value = '';
        document.getElementById('shipping_phone').value = '';
        document.getElementById('shipping_address').value = '';
        document.getElementById('shipping_city').value = '';
        document.getElementById('shipping_state').value = '';
        document.getElementById('shipping_postcode').value = '';
        document.getElementById('shipping_country').value = 'Bangladesh';
    }
}

// Search products
document.getElementById('productSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const productCards = document.querySelectorAll('#productList .product-select-card');
    
    productCards.forEach(card => {
        const name = card.textContent.toLowerCase();
        if (name.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});

// Form validation
document.getElementById('orderForm').addEventListener('submit', function(e) {
    if (orderItems.length === 0) {
        e.preventDefault();
        alert('Please add at least one product to the order.');
        return;
    }
    
    // Update quantity inputs before submit
    orderItems.forEach(item => {
        const qtyInput = document.querySelector(`input[name="products[${item.id}][quantity]"]`);
        if (qtyInput) {
            qtyInput.value = item.quantity;
        }
    });
});
</script>
@endpush
@endsection
