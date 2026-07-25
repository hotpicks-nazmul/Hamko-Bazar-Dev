@extends('admin.layouts.app')

@section('title', 'Create Product Bundle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create New Product Bundle</h4>
    <a href="{{ route('admin.product-bundles.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Bundles
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.product-bundles.store') }}" method="POST" enctype="multipart/form-data" id="bundleForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Bundle Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., Summer Essentials Bundle">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="auto-generated-if-empty">
                        </div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Leave empty to auto-generate from name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Describe this bundle...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Bundle Products -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Bundle Products <span class="text-danger">*</span></h6>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#productSearchModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Products
                </button>
            </div>
            <div class="card-body">
                <div id="selectedProductsContainer">
                    <!-- Selected products will be displayed here -->
                    <div class="text-center text-muted py-4" id="noProductsMessage">
                        <i class="bi bi-box-seam" style="font-size: 2rem;"></i>
                        <p class="mt-2">No products selected. Click "Add Products" to start building your bundle.</p>
                    </div>
                </div>
                
                <!-- Price Summary -->
                <div class="border-top pt-3 mt-3" id="priceSummary" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Original Total:</span>
                                <strong id="originalTotal">$0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Bundle Price:</span>
                                <strong class="text-success" id="bundleTotal">$0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Customer Savings:</span>
                                <strong class="text-primary" id="savingsAmount">$0.00</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pricing -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Pricing</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="bundle_price" class="form-label">Fixed Bundle Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control @error('bundle_price') is-invalid @enderror" id="bundle_price" name="bundle_price" value="{{ old('bundle_price', 0) }}" min="0" step="0.01" form="bundleForm">
                        </div>
                        @error('bundle_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Set to 0 to use discount calculation instead.</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="discount_type" class="form-label">Discount Type</label>
                        <select class="form-select @error('discount_type') is-invalid @enderror" id="discount_type" name="discount_type" form="bundleForm">
                            <option value="percentage" {{ old('discount_type', 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                            <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        </select>
                        @error('discount_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="discount_value" class="form-label">Discount Value</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('discount_value') is-invalid @enderror" id="discount_value" name="discount_value" value="{{ old('discount_value', 0) }}" min="0" step="0.01" form="bundleForm">
                            <span class="input-group-text" id="discountSuffix">%</span>
                        </div>
                        @error('discount_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Availability -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-calendar me-2"></i>Availability</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="starts_at" class="form-label">Start Date</label>
                        <input type="datetime-local" class="form-control @error('starts_at') is-invalid @enderror" id="starts_at" name="starts_at" value="{{ old('starts_at') }}" form="bundleForm">
                        @error('starts_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Leave empty for immediate availability.</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="expires_at" class="form-label">End Date</label>
                        <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" id="expires_at" name="expires_at" value="{{ old('expires_at') }}" form="bundleForm">
                        @error('expires_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Leave empty for no expiration.</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="max_purchases" class="form-label">Maximum Purchases</label>
                        <input type="number" class="form-control @error('max_purchases') is-invalid @enderror" id="max_purchases" name="max_purchases" value="{{ old('max_purchases') }}" min="1" placeholder="Unlimited" form="bundleForm">
                        @error('max_purchases')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Total purchases allowed. Leave empty for unlimited.</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="max_purchases_per_user" class="form-label">Max Per User</label>
                        <input type="number" class="form-control @error('max_purchases_per_user') is-invalid @enderror" id="max_purchases_per_user" name="max_purchases_per_user" value="{{ old('max_purchases_per_user') }}" min="1" placeholder="Unlimited" form="bundleForm">
                        @error('max_purchases_per_user')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Purchases allowed per customer. Leave empty for unlimited.</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- SEO Settings -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-search me-2"></i>SEO Settings</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input type="text" class="form-control @error('meta_title') is-invalid @enderror" id="meta_title" name="meta_title" value="{{ old('meta_title') }}" placeholder="SEO title for search engines" form="bundleForm">
                    @error('meta_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="3" placeholder="SEO description for search engines" form="bundleForm">{{ old('meta_description') }}</textarea>
                    @error('meta_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Featured Image -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-image me-2"></i>Featured Image</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="image-upload-preview mb-2 text-center" id="imagePreview" style="display: none;">
                        <img src="" alt="Preview" class="img-thumbnail" style="max-width: 100%; max-height: 200px;">
                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage()">
                            <i class="bi bi-trash me-1"></i> Remove
                        </button>
                    </div>
                    <input type="file" class="form-control @error('featured_image') is-invalid @enderror" id="featured_image" name="featured_image" accept="image/*" form="bundleForm" onchange="previewImage(this)">
                    @error('featured_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Recommended: 800x600px. Max 5MB.</div>
                </div>
            </div>
        </div>
        
        <!-- Status & Visibility -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Status & Visibility</h6>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} form="bundleForm">
                    <label class="form-check-label" for="is_active">
                        <i class="bi bi-check-circle text-success me-1"></i> Active
                    </label>
                    <div class="form-text">Only active bundles will be visible</div>
                </div>
                
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} form="bundleForm">
                    <label class="form-check-label" for="is_featured">
                        <i class="bi bi-star text-warning me-1"></i> Featured Bundle
                    </label>
                    <div class="form-text">Featured bundles may be highlighted</div>
                </div>
                
                <div class="mb-3 mt-3">
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" form="bundleForm">
                    @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Lower numbers appear first.</div>
                </div>
            </div>
        </div>
        
        <!-- Tips -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small text-muted">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Add at least 2 products to create a meaningful bundle</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Offer a discount to encourage purchases</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Use an attractive featured image</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Set availability dates for limited-time offers</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Write a compelling description</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.product-bundles.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="bundleForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Bundle
    </button>
</div>

<!-- Product Search Modal -->
<div class="modal fade" id="productSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-search me-2"></i>Search Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="productSearchInput" placeholder="Search by product name or SKU...">
                </div>
                <div id="productSearchResults" class="list-group">
                    <!-- Search results will appear here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.content-area {
    padding-bottom: 100px !important;
}
.image-upload-preview img {
    max-width: 100%;
    max-height: 200px;
    object-fit: contain;
}
.selected-product-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    background: #fff;
    transition: all 0.2s;
}
.selected-product-item:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.selected-product-item .product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
}
.product-result-item {
    cursor: pointer;
    transition: background 0.2s;
}
.product-result-item:hover {
    background: #f8f9fa;
}
.product-result-item.selected {
    background: #d1e7dd;
}
</style>
@endpush

@push('scripts')
<script>
let selectedProducts = [];
let allProducts = @json($products);

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Update discount suffix based on type
    document.getElementById('discount_type').addEventListener('change', function() {
        document.getElementById('discountSuffix').textContent = this.value === 'percentage' ? '%' : '$';
    });
    
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const slugInput = document.getElementById('slug');
        if (!slugInput.value) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
        }
    });
    
    // Product search
    let searchTimeout;
    document.getElementById('productSearchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim().toLowerCase();
        
        searchTimeout = setTimeout(() => {
            if (searchTerm.length < 2) {
                document.getElementById('productSearchResults').innerHTML = 
                    '<div class="text-center text-muted py-3">Type at least 2 characters to search...</div>';
                return;
            }
            
            // Filter products
            const results = allProducts.filter(p => 
                p.name.toLowerCase().includes(searchTerm) || 
                (p.sku && p.sku.toLowerCase().includes(searchTerm))
            ).slice(0, 10);
            
            let html = '';
            results.forEach(product => {
                const isSelected = selectedProducts.some(sp => sp.id === product.id);
                const finalPrice = product.sale_price || product.price;
                
                html += `
                    <div class="list-group-item product-result-item ${isSelected ? 'selected' : ''}" data-id="${product.id}">
                        <div class="d-flex align-items-center gap-3">
                            <img src="${product.featured_image ? '/storage/' + product.featured_image : '/images/no-image.png'}" 
                                 alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">${product.name}</div>
                                <small class="text-muted">${product.sku || 'N/A'}</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold">$${parseFloat(finalPrice).toFixed(2)}</div>
                                ${product.sale_price ? '<small class="text-decoration-line-through text-muted">$' + parseFloat(product.price).toFixed(2) + '</small>' : ''}
                            </div>
                            <div>
                                ${isSelected ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-plus-circle text-primary"></i>'}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            document.getElementById('productSearchResults').innerHTML = html || 
                '<div class="text-center text-muted py-3">No products found.</div>';
        }, 300);
    });
    
    // Handle product selection from search results
    document.getElementById('productSearchResults').addEventListener('click', function(e) {
        const item = e.target.closest('.product-result-item');
        if (item) {
            const productId = parseInt(item.dataset.id);
            const product = allProducts.find(p => p.id === productId);
            
            if (product) {
                const existingIndex = selectedProducts.findIndex(sp => sp.id === productId);
                
                if (existingIndex >= 0) {
                    // Remove product
                    selectedProducts.splice(existingIndex, 1);
                    item.classList.remove('selected');
                    item.querySelector('.bi-check-circle-fill').classList.replace('bi-check-circle-fill', 'bi-plus-circle');
                    item.querySelector('.bi-plus-circle').classList.replace('text-success', 'text-primary');
                } else {
                    // Add product
                    selectedProducts.push({
                        id: product.id,
                        name: product.name,
                        price: parseFloat(product.sale_price || product.price),
                        original_price: parseFloat(product.price),
                        featured_image: product.featured_image,
                        quantity: 1,
                        custom_price: null
                    });
                    item.classList.add('selected');
                    item.querySelector('.bi-plus-circle').classList.replace('bi-plus-circle', 'bi-check-circle-fill');
                    item.querySelector('.bi-check-circle-fill').classList.replace('text-primary', 'text-success');
                }
                
                updateSelectedProductsDisplay();
            }
        }
    });
});

// Update selected products display
function updateSelectedProductsDisplay() {
    const container = document.getElementById('selectedProductsContainer');
    const noProductsMsg = document.getElementById('noProductsMessage');
    const priceSummary = document.getElementById('priceSummary');
    
    if (selectedProducts.length === 0) {
        noProductsMsg.style.display = 'block';
        priceSummary.style.display = 'none';
        container.innerHTML = '';
        container.appendChild(noProductsMsg);
        updateHiddenInputs();
        return;
    }
    
    noProductsMsg.style.display = 'none';
    priceSummary.style.display = 'block';
    
    let html = '';
    selectedProducts.forEach((product, index) => {
        const effectivePrice = product.custom_price !== null ? product.custom_price : product.price;
        
        html += `
            <div class="selected-product-item" data-index="${index}">
                <div class="d-flex align-items-center gap-3">
                    <img src="${product.featured_image ? '/storage/' + product.featured_image : '/images/no-image.png'}" 
                         alt="${product.name}" class="product-image">
                    <div class="flex-grow-1">
                        <div class="fw-semibold">${product.name}</div>
                        <small class="text-muted">Original: $${product.original_price.toFixed(2)}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 100px;">
                            <span class="input-group-text">Qty</span>
                            <input type="number" class="form-control product-quantity" value="${product.quantity}" min="1" 
                                   onchange="updateProductQuantity(${index}, this.value)">
                        </div>
                        <div class="input-group input-group-sm" style="width: 120px;">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control product-price" value="${product.custom_price !== null ? product.custom_price : ''}" 
                                   placeholder="Auto" step="0.01" min="0"
                                   onchange="updateProductPrice(${index}, this.value)">
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProduct(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    updatePriceSummary();
    updateHiddenInputs();
}

// Update product quantity
function updateProductQuantity(index, quantity) {
    selectedProducts[index].quantity = parseInt(quantity) || 1;
    updatePriceSummary();
    updateHiddenInputs();
}

// Update product custom price
function updateProductPrice(index, price) {
    selectedProducts[index].custom_price = price ? parseFloat(price) : null;
    updatePriceSummary();
    updateHiddenInputs();
}

// Remove product from selection
function removeProduct(index) {
    selectedProducts.splice(index, 1);
    updateSelectedProductsDisplay();
}

// Update price summary
function updatePriceSummary() {
    let originalTotal = 0;
    let bundlePrice = 0;
    
    selectedProducts.forEach(product => {
        const price = product.custom_price !== null ? product.custom_price : product.price;
        originalTotal += product.original_price * product.quantity;
        bundlePrice += price * product.quantity;
    });
    
    // Apply discount if bundle price is not set
    const fixedBundlePrice = parseFloat(document.getElementById('bundle_price').value) || 0;
    const discountType = document.getElementById('discount_type').value;
    const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
    
    if (fixedBundlePrice > 0) {
        bundlePrice = fixedBundlePrice;
    } else {
        if (discountType === 'percentage') {
            bundlePrice = bundlePrice * (1 - discountValue / 100);
        } else {
            bundlePrice = Math.max(0, bundlePrice - discountValue);
        }
    }
    
    const savings = originalTotal - bundlePrice;
    
    document.getElementById('originalTotal').textContent = '$' + originalTotal.toFixed(2);
    document.getElementById('bundleTotal').textContent = '$' + bundlePrice.toFixed(2);
    document.getElementById('savingsAmount').textContent = '$' + savings.toFixed(2);
}

// Update hidden inputs for form submission
function updateHiddenInputs() {
    // Remove existing hidden inputs
    document.querySelectorAll('.product-hidden-input').forEach(el => el.remove());
    
    const form = document.getElementById('bundleForm');
    
    selectedProducts.forEach((product, index) => {
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = `products[${index}][id]`;
        idInput.value = product.id;
        idInput.className = 'product-hidden-input';
        form.appendChild(idInput);
        
        const qtyInput = document.createElement('input');
        qtyInput.type = 'hidden';
        qtyInput.name = `products[${index}][quantity]`;
        qtyInput.value = product.quantity;
        qtyInput.className = 'product-hidden-input';
        form.appendChild(qtyInput);
        
        const priceInput = document.createElement('input');
        priceInput.type = 'hidden';
        priceInput.name = `products[${index}][custom_price]`;
        priceInput.value = product.custom_price || '';
        priceInput.className = 'product-hidden-input';
        form.appendChild(priceInput);
    });
}

// Image preview
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    document.getElementById('featured_image').value = '';
    document.getElementById('imagePreview').style.display = 'none';
}

// Listen for pricing changes
document.getElementById('bundle_price').addEventListener('input', updatePriceSummary);
document.getElementById('discount_type').addEventListener('change', updatePriceSummary);
document.getElementById('discount_value').addEventListener('input', updatePriceSummary);

// Form validation
document.getElementById('bundleForm').addEventListener('submit', function(e) {
    if (selectedProducts.length === 0) {
        e.preventDefault();
        alert('Please add at least one product to the bundle.');
        return false;
    }
});
</script>
@endpush
