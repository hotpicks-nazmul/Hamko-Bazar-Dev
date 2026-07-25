@extends('admin.layouts.app')

@section('title', 'Related Products - ' . $product->name)

@push('styles')
<style>
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
    
    .product-card {
        transition: all 0.2s ease;
        border: 1px solid #e9ecef;
    }
    
    .product-card:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .product-card.dragging {
        opacity: 0.5;
        transform: scale(0.98);
    }
    
    .product-card .drag-handle {
        cursor: grab;
        opacity: 0.3;
        transition: opacity 0.2s;
    }
    
    .product-card:hover .drag-handle {
        opacity: 0.7;
    }
    
    .product-card .drag-handle:active {
        cursor: grabbing;
    }
    
    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .search-result-item {
        transition: all 0.15s ease;
        border: 1px solid #e9ecef;
    }
    
    .search-result-item:hover {
        background-color: #f8f9fa;
        border-color: #0d6efd;
    }
    
    .search-result-item.selected {
        background-color: #e7f1ff;
        border-color: #0d6efd;
    }
    
    .empty-state {
        padding: 3rem;
        text-align: center;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .badge-stock {
        font-size: 0.7rem;
    }
</style>
@endpush

@section('content')
@if(auth()->user()->hasPermission('products') && auth()->user()->hasPermission('products.view'))
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Related Products</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.products.edit', $product->id) }}">{{ Str::limit($product->name, 30) }}</a></li>
                <li class="breadcrumb-item active">Related Products</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Edit
    </a>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Current Related Products -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-diagram-3 me-2"></i>Current Related Products
                    <span class="badge bg-primary ms-2" id="relatedCount">{{ $product->relatedProducts->count() }}</span>
                </h6>
                <div class="d-flex gap-2" id="bulkActions" style="display: none !important;">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="bulkRemove()">
                        <i class="bi bi-trash me-1"></i> Remove Selected
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                        Clear Selection
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @if($product->relatedProducts->count() > 0)
                    <div class="list-group list-group-flush" id="relatedProductsList">
                        @foreach($product->relatedProducts as $index => $relatedProduct)
                            @php
                                $relImages = is_string($relatedProduct->images) ? json_decode($relatedProduct->images, true) : $relatedProduct->images;
                                $relImage = $relatedProduct->featured_image ?? ($relImages[0] ?? null);
                            @endphp
                            <div class="list-group-item product-card" data-id="{{ $relatedProduct->id }}" draggable="true">
                                <div class="d-flex align-items-center">
                                    <div class="drag-handle me-3">
                                        <i class="bi bi-grip-vertical fs-5"></i>
                                    </div>
                                    <div class="form-check me-3">
                                        <input type="checkbox" class="form-check-input product-checkbox" value="{{ $relatedProduct->id }}" onchange="updateBulkActions()">
                                    </div>
                                    <img src="{{ $relImage ?? asset('images/placeholder.png') }}" 
                                         alt="{{ $relatedProduct->name }}" class="product-image me-3">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <h6 class="mb-0 me-2">{{ $relatedProduct->name }}</h6>
                                            @if($relatedProduct->is_active)
                                                <span class="badge bg-success badge-stock">Active</span>
                                            @else
                                                <span class="badge bg-secondary badge-stock">Inactive</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">
                                            SKU: {{ $relatedProduct->sku }} 
                                            @if($relatedProduct->category)
                                                | Category: {{ $relatedProduct->category->name }}
                                            @endif
                                        </small>
                                        <div class="mt-1">
                                            @if($relatedProduct->isOnSale())
                                                <span class="text-decoration-line-through text-muted me-2">৳{{ number_format($relatedProduct->price, 0) }}</span>
                                                <span class="text-danger fw-semibold">৳{{ number_format($relatedProduct->sale_price, 0) }}</span>
                                                <span class="badge bg-danger ms-1">-{{ $relatedProduct->discount_percentage }}%</span>
                                            @else
                                                <span class="fw-semibold">৳{{ number_format($relatedProduct->price, 0) }}</span>
                                            @endif
                                            <span class="text-muted ms-2">Stock: {{ $relatedProduct->quantity }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-dark sort-order-badge">Order: {{ $relatedProduct->pivot->sort_order }}</span>
                                        <a href="{{ route('admin.products.edit', $relatedProduct->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRelated({{ $relatedProduct->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state" id="emptyState">
                        <i class="bi bi-diagram-3"></i>
                        <h6>No Related Products</h6>
                        <p class="mb-0">Add related products to suggest complementary items to customers.</p>
                    </div>
                @endif
            </div>
            @if($product->relatedProducts->count() > 1)
                <div class="card-footer bg-white">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Drag and drop to reorder. Products are displayed in the order shown.
                    </small>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Sidebar - Add Products -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Related Products</h6>
            </div>
            <div class="card-body">
                <!-- Search Form -->
                <div class="mb-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search products...">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="mb-3">
                    <select class="form-select form-select-sm" id="categoryFilter">
                        <option value="">All Categories</option>
                        @foreach(\App\Models\Category::where('status', 'active')->get() as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Auto Suggest Button -->
                <button type="button" class="btn btn-sm btn-outline-info w-100 mb-3" onclick="autoSuggest()">
                    <i class="bi bi-magic me-1"></i> Auto-Suggest Products
                </button>
                
                <!-- Search Results -->
                <div id="searchResults" class="search-results">
                    <div class="text-center text-muted py-3">
                        <small>Search for products to add as related items</small>
                    </div>
                </div>
                
                <!-- Selected Products to Add -->
                <div id="selectedProducts" class="mt-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted"><span id="selectedCount">0</span> product(s) selected</span>
                        <button type="button" class="btn btn-sm btn-link p-0" onclick="clearSelectedProducts()">Clear</button>
                    </div>
                    <div id="selectedProductsList" class="list-group mb-2"></div>
                    <button type="button" class="btn btn-sm btn-primary w-100" onclick="addSelectedProducts()">
                        <i class="bi bi-plus-lg me-1"></i> Add Selected
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tips Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Related products appear on the product detail page
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Use auto-suggest to find similar products
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Drag to reorder display position
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Maximum 10-15 related products recommended
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Back to Edit
    </a>
    <a href="{{ route('admin.products.index') }}" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Done
    </a>
</div>
@else
<div class="alert alert-warning border-0 shadow-sm mb-4">
    <i class="bi bi-shield-exclamation me-2"></i>
    <strong>Access Restricted:</strong> You don't have permission to access products.
    Please contact an administrator if you need access.
</div>
@endif
@endsection

@push('scripts')
<script>
// Selected products to add (using Map for better ID-based lookup)
let selectedProducts = new Map();
let searchTimeout;

// Initialize drag and drop
function initDragAndDrop() {
    const list = document.getElementById('relatedProductsList');
    if (!list) return;
    
    const items = list.querySelectorAll('.product-card');
    
    items.forEach(item => {
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragend', handleDragEnd);
        item.addEventListener('dragover', handleDragOver);
        item.addEventListener('drop', handleDrop);
        item.addEventListener('dragenter', handleDragEnter);
        item.addEventListener('dragleave', handleDragLeave);
    });
}

let draggedItem = null;

function handleDragStart(e) {
    draggedItem = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
    document.querySelectorAll('.product-card').forEach(item => {
        item.classList.remove('drag-over');
    });
    saveOrder();
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
}

function handleDragEnter(e) {
    this.classList.add('drag-over');
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    if (draggedItem !== this) {
        const list = document.getElementById('relatedProductsList');
        const items = Array.from(list.querySelectorAll('.product-card'));
        const draggedIndex = items.indexOf(draggedItem);
        const targetIndex = items.indexOf(this);
        
        if (draggedIndex < targetIndex) {
            this.parentNode.insertBefore(draggedItem, this.nextSibling);
        } else {
            this.parentNode.insertBefore(draggedItem, this);
        }
        
        updateOrderBadges();
    }
    this.classList.remove('drag-over');
}

function updateOrderBadges() {
    const items = document.querySelectorAll('#relatedProductsList .product-card');
    items.forEach((item, index) => {
        const badge = item.querySelector('.sort-order-badge');
        if (badge) {
            badge.textContent = `Order: ${index + 1}`;
        }
    });
}

function saveOrder() {
    const items = document.querySelectorAll('#relatedProductsList .product-card');
    const order = Array.from(items).map(item => parseInt(item.dataset.id));
    
    fetch(`{{ route('admin.products.related.order', $product->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ order: order })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Order saved', 'success');
        }
    });
}

// Search functionality
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(performSearch, 300);
});

categoryFilter.addEventListener('change', performSearch);

document.getElementById('clearSearch').addEventListener('click', function() {
    searchInput.value = '';
    categoryFilter.value = '';
    document.getElementById('searchResults').innerHTML = '<div class="text-center text-muted py-3"><small>Search for products to add as related items</small></div>';
});

function performSearch() {
    const search = searchInput.value.trim();
    const category = categoryFilter.value;
    
    if (!search && !category) {
        document.getElementById('searchResults').innerHTML = '<div class="text-center text-muted py-3"><small>Search for products to add as related items</small></div>';
        return;
    }
    
    fetch(`{{ route('admin.products.related.search', $product->id) }}?search=${encodeURIComponent(search)}&category=${category}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderSearchResults(data.products);
        }
    });
}

function renderSearchResults(products) {
    const container = document.getElementById('searchResults');
    
    if (products.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-3"><small>No products found</small></div>';
        return;
    }
    
    let html = '<div class="list-group list-group-flush">';
    products.forEach(product => {
        const isSelected = selectedProducts.has(product.id);
        html += `
            <div class="list-group-item search-result-item p-2 ${isSelected ? 'selected' : ''}" data-id="${product.id}">
                <div class="d-flex align-items-center">
                    <img src="${product.image || '{{ asset('images/placeholder.png') }}'}" 
                         alt="${product.name}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                    <div class="flex-grow-1 ms-2" style="min-width: 0;">
                        <div class="small text-truncate">${product.name}</div>
                        <div class="small text-muted">
                            ${product.sku} | ৳${product.final_price}
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm ${isSelected ? 'btn-success' : 'btn-outline-primary'}" 
                            onclick="toggleProductSelection(${product.id}, '${product.name.replace(/'/g, "\\'")}', '${product.image || ''}', ${product.final_price})">
                        <i class="bi ${isSelected ? 'bi-check' : 'bi-plus'}"></i>
                    </button>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function toggleProductSelection(id, name, image, price) {
    if (selectedProducts.has(id)) {
        selectedProducts.delete(id);
    } else {
        selectedProducts.set(id, { id, name, image, price });
    }
    updateSelectedProductsUI();
    performSearch(); // Refresh to update button states
}

function updateSelectedProductsUI() {
    const container = document.getElementById('selectedProducts');
    const list = document.getElementById('selectedProductsList');
    const count = document.getElementById('selectedCount');
    
    if (selectedProducts.size > 0) {
        container.style.display = 'block';
        count.textContent = selectedProducts.size;
        
        let html = '';
        selectedProducts.forEach((product, id) => {
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                    <span class="small text-truncate" style="max-width: 200px;">${product.name}</span>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeFromSelection(${id})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
        });
        list.innerHTML = html;
    } else {
        container.style.display = 'none';
    }
}

function removeFromSelection(id) {
    selectedProducts.delete(id);
    updateSelectedProductsUI();
    performSearch();
}

function clearSelectedProducts() {
    selectedProducts.clear();
    updateSelectedProductsUI();
    performSearch();
}

function addSelectedProducts() {
    if (selectedProducts.size === 0) return;
    
    const productIds = Array.from(selectedProducts.keys());
    
    fetch(`{{ route('admin.products.related.add', $product->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ product_ids: productIds })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            selectedProducts.clear();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message || 'Error adding products', 'error');
        }
    })
    .catch(error => {
        showToast('Error adding products', 'error');
        console.error(error);
    });
}

// Auto suggest
function autoSuggest() {
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Loading...';
    
    fetch(`{{ route('admin.products.related.auto-suggest', $product->id) }}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-magic me-1"></i> Auto-Suggest Products';
        
        if (data.success && data.products.length > 0) {
            renderSearchResults(data.products);
            showToast(`Found ${data.products.length} suggested products`, 'info');
        } else {
            showToast('No suggestions available', 'info');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-magic me-1"></i> Auto-Suggest Products';
        showToast('Error loading suggestions', 'error');
        console.error(error);
    });
}

// Remove related product
function removeRelated(id) {
    if (!confirm('Remove this related product?')) return;
    
    // Build URL manually for DELETE route: admin/products/{product}/related/{relatedId}
    const baseUrl = '{{ url('admin/products') }}';
    const productId = '{{ $product->id }}';
    const url = `${baseUrl}/${productId}/related/${id}`;
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Related product removed', 'success');
            location.reload();
        }
    })
    .catch(error => {
        showToast('Error removing product', 'error');
        console.error(error);
    });
}

// Bulk actions
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    bulkActions.style.display = checkboxes.length > 0 ? 'flex' : 'none';
}

function clearSelection() {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
    updateBulkActions();
}

function bulkRemove() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    if (checkboxes.length === 0) return;
    
    if (!confirm(`Remove ${checkboxes.length} selected product(s)?`)) return;
    
    const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    fetch(`{{ route('admin.products.related.bulk-remove', $product->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            location.reload();
        }
    })
    .catch(error => {
        showToast('Error removing products', 'error');
        console.error(error);
    });
}

// Toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 200px;';
    toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>${message}`;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initDragAndDrop();
});
</script>
@endpush
