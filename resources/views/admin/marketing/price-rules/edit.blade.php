@extends('admin.layouts.app')

@section('title', 'Edit Price Rule')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Price Rule</h4>
    <a href="{{ route('admin.marketing.price-rules.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Price Rules
    </a>
</div>

<form method="POST" action="{{ route('admin.marketing.price-rules.update', $priceRule->id) }}" id="itemForm">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Rule Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $priceRule->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Enter a descriptive name for your price rule</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $priceRule->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Optional description for internal use</div>
                        @enderror
                    </div>

                    <!-- Discount Type and Value -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="discount_type" class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <select id="discount_type" name="discount_type" class="form-select @error('discount_type') is-invalid @enderror" required>
                                <option value="percent" {{ old('discount_type', $priceRule->discount_type) === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('discount_type', $priceRule->discount_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount ($)</option>
                            </select>
                            @error('discount_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="discount_value" class="form-label">Discount Value <span class="text-danger">*</span></label>
                            <input type="number" id="discount_value" name="discount_value" class="form-control @error('discount_value') is-invalid @enderror" value="{{ old('discount_value', $priceRule->discount_value) }}" step="0.01" min="0" required>
                            @error('discount_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="max_discount_amount" class="form-label">Max Discount Amount</label>
                            <input type="number" id="max_discount_amount" name="max_discount_amount" class="form-control @error('max_discount_amount') is-invalid @enderror" value="{{ old('max_discount_amount', $priceRule->max_discount_amount) }}" step="0.01" min="0" placeholder="Optional">
                            @error('max_discount_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Leave empty for no limit</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Min Quantity and Min Order Amount -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="min_quantity" class="form-label">Minimum Quantity</label>
                            <input type="number" id="min_quantity" name="min_quantity" class="form-control @error('min_quantity') is-invalid @enderror" value="{{ old('min_quantity', $priceRule->min_quantity) }}" min="1">
                            @error('min_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Minimum product quantity to qualify</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="min_order_amount" class="form-label">Minimum Order Amount</label>
                            <input type="number" id="min_order_amount" name="min_order_amount" class="form-control @error('min_order_amount') is-invalid @enderror" value="{{ old('min_order_amount', $priceRule->min_order_amount) }}" step="0.01" min="0" placeholder="Optional">
                            @error('min_order_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Minimum cart total to qualify</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date & Time</label>
                            <input type="datetime-local" id="start_date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $priceRule->start_date ? $priceRule->start_date->format('Y-m-d\TH:i') : '') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Leave empty for immediate activation</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date & Time</label>
                            <input type="datetime-local" id="end_date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $priceRule->end_date ? $priceRule->end_date->format('Y-m-d\TH:i') : '') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Leave empty for no expiration</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Selection Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Select Products</h6>
                </div>
                <div class="card-body">
                    <!-- Search Products -->
                    <div class="mb-3">
                        <label class="form-label">Search Products</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="productSearch" class="form-control" placeholder="Search by name or SKU...">
                        </div>
                    </div>

                    <!-- Selected Products List -->
                    <div class="mb-3">
                        <label class="form-label">Selected Products</label>
                        <div id="selectedProductsList" class="border rounded p-2" style="min-height: 100px; max-height: 300px; overflow-y: auto;">
                            @if($priceRule->products->count() > 0)
                                @foreach($priceRule->products as $product)
                                <div class="selected-product-item">
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <br><small class="text-muted">${{ number_format($product->unit_price, 2) }}</small>
                                    </div>
                                    <i class="bi bi-x-circle-fill remove-product-btn" onclick="removeProduct({{ $product->id }})" title="Remove"></i>
                                </div>
                                @endforeach
                            @else
                                <p class="text-muted text-center py-3 mb-0" id="noProductsMessage">No products selected. Search and add products above.</p>
                            @endif
                        </div>
                        <input type="hidden" name="product_ids" id="productIds" value="{{ json_encode($priceRule->products->pluck('id')->toArray()) }}">
                    </div>
                </div>
            </div>

            <!-- Categories Selection Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-folder me-2"></i>Select Categories</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Leave empty to apply to all categories, or select specific categories.</p>
                    <div class="row">
                        @forelse($categories as $category)
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input category-checkbox" type="checkbox" name="category_ids[]" value="{{ $category->id }}" id="category_{{ $category->id }}" {{ $priceRule->categories->contains($category->id) ? 'checked' : '' }}>
                                <label class="form-check-label" for="category_{{ $category->id }}">
                                    {{ $category->name }}
                                </label>
                            </div>
                            @if($category->children && $category->children->count() > 0)
                                @foreach($category->children as $child)
                                <div class="form-check ms-4">
                                    <input class="form-check-input category-checkbox" type="checkbox" name="category_ids[]" value="{{ $child->id }}" id="category_{{ $child->id }}" {{ $priceRule->categories->contains($child->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="category_{{ $child->id }}">
                                        - {{ $child->name }}
                                    </label>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        @empty
                        <div class="col-12">
                            <p class="text-muted">No categories available.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="inactive" {{ old('status', $priceRule->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="active" {{ old('status', $priceRule->status) === 'active' ? 'selected' : '' }}>Active</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Set to active to apply the rule</div>
                        @enderror
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $priceRule->is_featured) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">
                            <i class="bi bi-star text-warning me-1"></i> Featured
                        </label>
                        <div class="form-text">Featured rules get priority</div>
                    </div>

                    <div class="mb-0">
                        <label for="priority" class="form-label">Priority</label>
                        <input type="number" id="priority" name="priority" class="form-control @error('priority') is-invalid @enderror" value="{{ old('priority', $priceRule->priority) }}" min="0">
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Higher priority rules apply first</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Quick Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Quick Info</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Products:</span>
                        <strong>{{ $priceRule->products->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Categories:</span>
                        <strong>{{ $priceRule->categories->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Slug:</span>
                        <code>{{ $priceRule->slug }}</code>
                    </div>
                    <hr>
                    <a href="{{ route('admin.marketing.price-rules.products', $priceRule->id) }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-box-seam me-1"></i> Manage Products
                    </a>
                </div>
            </div>

            <!-- Discount Preview Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Discount Preview</h6>
                </div>
                <div class="card-body">
                    <div id="discountPreview">
                        <p class="text-muted small mb-2">Enter discount values to see preview</p>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Original Price:</span>
                            <span class="fw-bold">$100.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <span class="text-success fw-bold" id="previewDiscount">- ${{ number_format($priceRule->discount_type === 'percent' ? (100 * $priceRule->discount_value / 100) : $priceRule->discount_value, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Final Price:</span>
                            <span class="fw-bold" id="previewFinal">${{ number_format($priceRule->discount_type === 'percent' ? (100 - (100 * $priceRule->discount_value / 100)) : (100 - $priceRule->discount_value), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-2"><i class="bi bi-check2 me-1"></i> Set a higher priority for rules that should apply first</li>
                        <li class="mb-2"><i class="bi bi-check2 me-1"></i> Use percentage for general sales</li>
                        <li class="mb-2"><i class="bi bi-check2 me-1"></i> Use fixed amount for specific deals</li>
                        <li><i class="bi bi-check2 me-1"></i> Add products or categories to target specific items</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.marketing.price-rules.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="itemForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Price Rule
    </button>
</div>
@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
    .selected-product-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px;
        border-bottom: 1px solid #eee;
    }
    .selected-product-item:last-child {
        border-bottom: none;
    }
    .remove-product-btn {
        cursor: pointer;
        color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize selected products from PHP
    let selectedProducts = @json($priceRule->products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => $p->unit_price]));

    // Discount preview calculation
    const discountTypeSelect = document.getElementById('discount_type');
    const discountValueInput = document.getElementById('discount_value');
    const maxDiscountInput = document.getElementById('max_discount_amount');
    const previewDiscount = document.getElementById('previewDiscount');
    const previewFinal = document.getElementById('previewFinal');

    const originalPrice = 100.00;

    function updatePreview() {
        const discountType = discountTypeSelect.value;
        const discountValue = parseFloat(discountValueInput.value) || 0;
        const maxDiscount = parseFloat(maxDiscountInput.value) || null;
        
        let discount = 0;
        
        if (discountType === 'percent') {
            discount = (originalPrice * discountValue) / 100;
        } else {
            discount = discountValue;
        }
        
        // Apply max discount cap
        if (maxDiscount !== null && discount > maxDiscount) {
            discount = maxDiscount;
        }
        
        const finalPrice = Math.max(0, originalPrice - discount);
        
        previewDiscount.textContent = '- $' + discount.toFixed(2);
        previewFinal.textContent = '$' + finalPrice.toFixed(2);
    }

    discountTypeSelect.addEventListener('change', updatePreview);
    discountValueInput.addEventListener('input', updatePreview);
    maxDiscountInput.addEventListener('input', updatePreview);

    // Initial preview update
    updatePreview();

    // Product selection
    const productSearch = document.getElementById('productSearch');
    const selectedProductsList = document.getElementById('selectedProductsList');
    const noProductsMessage = document.getElementById('noProductsMessage');
    const productIdsInput = document.getElementById('productIds');

    // Search products with debounce
    let searchTimeout;
    productSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchProducts(this.value);
        }, 300);
    });

    function searchProducts(query) {
        if (!query || query.length < 2) {
            return;
        }

        fetch(`{{ route('admin.marketing.price-rules.get-products') }}?search=${encodeURIComponent(query)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(products => {
            showProductSearchResults(products);
        })
        .catch(err => console.error('Error searching products:', err));
    }

    function showProductSearchResults(products) {
        let resultsDiv = document.getElementById('searchResults');
        if (!resultsDiv) {
            resultsDiv = document.createElement('div');
            resultsDiv.id = 'searchResults';
            resultsDiv.className = 'border rounded mt-2 p-2';
            resultsDiv.style.maxHeight = '200px';
            resultsDiv.style.overflowY = 'auto';
            productSearch.parentNode.parentNode.appendChild(resultsDiv);
        }

        if (products.length === 0) {
            resultsDiv.innerHTML = '<p class="text-muted mb-0 p-2">No products found</p>';
            return;
        }

        resultsDiv.innerHTML = products.map(product => `
            <div class="d-flex justify-content-between align-items-center p-2 border-bottom" style="cursor: pointer;" onclick="addProduct(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.unit_price})">
                <div>
                    <strong>${product.name}</strong>
                    ${product.sku ? `<br><small class="text-muted">SKU: ${product.sku}</small>` : ''}
                </div>
                <div class="text-end">
                    <span class="badge bg-primary">$${parseFloat(product.unit_price).toFixed(2)}</span>
                    <i class="bi bi-plus-circle text-success ms-2"></i>
                </div>
            </div>
        `).join('');
    }

    // Hide search results when clicking outside
    document.addEventListener('click', function(e) {
        const resultsDiv = document.getElementById('searchResults');
        if (resultsDiv && !resultsDiv.contains(e.target) && e.target !== productSearch) {
            resultsDiv.remove();
        }
    });

    window.addProduct = function(id, name, price) {
        // Check if already selected
        if (selectedProducts.find(p => p.id === id)) {
            return;
        }

        selectedProducts.push({ id, name, price });
        updateSelectedProductsList();
        
        // Remove search results
        const resultsDiv = document.getElementById('searchResults');
        if (resultsDiv) {
            resultsDiv.remove();
        }
        productSearch.value = '';
    };

    window.removeProduct = function(id) {
        selectedProducts = selectedProducts.filter(p => p.id !== id);
        updateSelectedProductsList();
    };

    function updateSelectedProductsList() {
        if (selectedProducts.length === 0) {
            selectedProductsList.innerHTML = '<p class="text-muted text-center py-3 mb-0" id="noProductsMessage">No products selected. Search and add products above.</p>';
            productIdsInput.value = '';
            return;
        }

        selectedProductsList.innerHTML = selectedProducts.map(product => `
            <div class="selected-product-item">
                <div>
                    <strong>${product.name}</strong>
                    <br><small class="text-muted">$${parseFloat(product.price).toFixed(2)}</small>
                </div>
                <i class="bi bi-x-circle-fill remove-product-btn" onclick="removeProduct(${product.id})" title="Remove"></i>
            </div>
        `).join('');

        productIdsInput.value = JSON.stringify(selectedProducts.map(p => p.id));
    }
</script>
@endpush
