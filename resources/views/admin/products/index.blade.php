@extends('admin.layouts.app')

@section('title', 'All Products')

@section('content')
@if(auth()->user()->hasPermission('products') && auth()->user()->hasPermission('products.view'))
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-box-seam"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Products</span>
            <span class="stat-card-value">{{ number_format($stats['total'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value">{{ number_format($stats['active'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value">{{ number_format($stats['inactive'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-star"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Featured</span>
            <span class="stat-card-value">{{ number_format($stats['featured'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Low Stock</span>
            <span class="stat-card-value">{{ number_format($stats['low_stock'] ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">All Products</h4>
    <div class="d-flex gap-2">
        @php $canExport = auth()->user()->hasPermission('products.export'); @endphp
        @if($canExport)
        <a href="{{ route('admin.products.export', request()->query()) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
        @else
        <span class="btn btn-outline-secondary disabled" title="No permission" style="pointer-events:none;opacity:0.5">
            <i class="bi bi-download me-1"></i> Export CSV
        </span>
        @endif
        @php $canCreate = auth()->user()->hasPermission('products.create'); @endphp
        @if($canCreate)
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add New Product
        </a>
        @else
        <span class="btn btn-primary disabled" title="No permission" style="pointer-events:none;opacity:0.5">
            <i class="bi bi-plus-lg me-1"></i> Add New Product
        </span>
        @endif
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Name, SKU, Product Code..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </span>
                    </div>
                </div>
                
                <!-- Category -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Category</label>
                    <select name="category" id="filterCategory" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Status -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <!-- Stock Status -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Stock</label>
                    <select name="stock_status" id="filterStock" class="form-select form-select-sm">
                        <option value="">All Stock</option>
                        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                
                <!-- Featured -->
                <div class="col-lg-1 col-md-2 col-sm-4">
                    <label class="form-label small text-muted">Featured</label>
                    <select name="featured" id="filterFeatured" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="yes" {{ request('featured') === 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ request('featured') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                
                <!-- Buttons -->
                <div class="col-lg-2 col-md-4 col-sm-8">
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary flex-grow-1" id="resetFilters">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div class="card border-0 shadow-sm mb-3" id="bulkActionsBar" style="display: none;">
    <div class="card-body py-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted"><span id="selectedCount">0</span> selected</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllProducts()">
                    Select All {{ $products->total() }} Products
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                    Clear Selection
                </button>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('activate')">
                    <i class="bi bi-check-circle me-1"></i> Activate
                </button>
                <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('deactivate')">
                    <i class="bi bi-pause-circle me-1"></i> Deactivate
                </button>
                <button type="button" class="btn btn-sm btn-info" onclick="bulkAction('feature')">
                    <i class="bi bi-star me-1"></i> Feature
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="bulkAction('unfeature')">
                    <i class="bi bi-star-fill me-1"></i> Unfeature
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="bulkAction('duplicate')">
                    <i class="bi bi-copy me-1"></i> Duplicate
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="productsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th style="width: 60px;">Image</th>
                        <th>
                            <a href="{{ route('admin.products.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Name
                                @if(request('sort') == 'name')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th>Product Code</th>
                        <th>Category</th>
                        <th>
                            <a href="{{ route('admin.products.index', array_merge(request()->query(), ['sort' => 'price', 'direction' => request('sort') == 'price' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Price
                                @if(request('sort') == 'price')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.products.index', array_merge(request()->query(), ['sort' => 'quantity', 'direction' => request('sort') == 'quantity' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Stock
                                @if(request('sort') == 'quantity')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @include('admin.products.partials.product-rows')
                </tbody>
            </table>
        </div>
        
        <!-- Pagination & Per Page -->
        @if(isset($products) && method_exists($products, 'hasPages') && $products->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show:</span>
                <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 || !request('per_page') ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="text-muted small">per page</span>
            </div>
            <div>
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Quick Edit Modal -->
<div class="modal fade" id="quickEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickEditForm">
                    @csrf
                    <input type="hidden" name="product_id" id="quickEditProductId">
                    
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" id="quickEditName" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" id="quickEditShortDescription" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Product Code</label>
                            <input type="text" name="product_code" id="quickEditProductCode" class="form-control" placeholder="e.g., PRD-001">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="quantity" id="quickEditQuantity" class="form-control" min="0" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Regular Price (৳)</label>
                            <input type="number" name="price" id="quickEditPrice" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Sale Price (৳)</label>
                            <input type="number" name="sale_price" id="quickEditSalePrice" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="quickEditCategory" class="form-select" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveQuickEdit()">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.products.bulk-action') }}">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ids" id="bulkIdsInput">
</form>
@else
<div class="alert alert-warning border-0 shadow-sm mb-4">
    <i class="bi bi-shield-exclamation me-2"></i>
    <strong>Access Restricted:</strong> You don't have permission to access products.
    Please contact an administrator if you need access.
</div>
@endif
@endsection

@push('styles')
<style>
.status-toggle, .featured-toggle {
    min-width: 70px;
    transition: all 0.2s;
}
.status-toggle:hover, .featured-toggle:hover {
    transform: scale(1.05);
}
.table > :not(caption) > * > * {
    padding: 0.75rem 0.5rem;
}
.product-checkbox:checked + td img {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.3);
}
/* Fix delete button border-radius to match btn-group siblings */
.btn-group .delete-btn {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}
</style>
@endpush

@push('scripts')
<script>
let selectedProducts = new Set();
let allProductsSelected = false;

// Toggle select all on current page
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedProducts.add(parseInt(cb.value));
        } else {
            selectedProducts.delete(parseInt(cb.value));
        }
    });
    updateBulkActions();
}

// Select all products (across all pages)
function selectAllProducts() {
    allProductsSelected = true;
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = true;
        selectedProducts.add(parseInt(cb.value));
    });
    updateBulkActions();
    document.getElementById('selectedCount').textContent = '{{ $products->total() }} (all pages)';
}

// Clear selection
function clearSelection() {
    allProductsSelected = false;
    selectedProducts.clear();
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateBulkActions();
}

// Update bulk actions bar visibility
function updateBulkActions() {
    const count = selectedProducts.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    
    // Update select all checkbox state
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    document.getElementById('selectAllCheckbox').checked = allChecked && checkboxes.length > 0;
}

// Perform bulk action
function bulkAction(action) {
    if (selectedProducts.size === 0) {
        alert('Please select at least one product.');
        return;
    }
    
    let confirmMsg = '';
    switch(action) {
        case 'delete':
            confirmMsg = `Are you sure you want to delete ${selectedProducts.size} product(s)? This action cannot be undone.`;
            break;
        case 'activate':
            confirmMsg = `Activate ${selectedProducts.size} product(s)?`;
            break;
        case 'deactivate':
            confirmMsg = `Deactivate ${selectedProducts.size} product(s)?`;
            break;
        case 'feature':
            confirmMsg = `Mark ${selectedProducts.size} product(s) as featured?`;
            break;
        case 'unfeature':
            confirmMsg = `Remove ${selectedProducts.size} product(s) from featured?`;
            break;
        case 'duplicate':
            confirmMsg = `Duplicate ${selectedProducts.size} product(s)?`;
            break;
    }
    
    if (!confirm(confirmMsg)) return;
    
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedProducts));
    document.getElementById('bulkActionForm').submit();
}

// Toggle status via AJAX
document.querySelectorAll('.status-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch(`{{ route('admin.products.toggle-status', ['product' => 'ID']) }}`.replace('ID', id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.textContent = data.is_active ? 'Active' : 'Inactive';
                this.classList.toggle('btn-success', data.is_active);
                this.classList.toggle('btn-outline-secondary', !data.is_active);
                showToast(data.message, 'success');
            }
        });
    });
});

// Toggle featured via AJAX
document.querySelectorAll('.featured-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch(`{{ route('admin.products.toggle-featured', ['product' => 'ID']) }}`.replace('ID', id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const icon = this.querySelector('i');
                icon.classList.toggle('bi-star', !data.is_featured);
                icon.classList.toggle('bi-star-fill', data.is_featured);
                this.classList.toggle('btn-info', data.is_featured);
                this.classList.toggle('btn-outline-secondary', !data.is_featured);
                showToast(data.message, 'success');
            }
        });
    });
});

// Quick Edit Modal
let quickEditModal;
function openQuickEdit(btn) {
    const row = btn.closest('tr');
    const id = btn.dataset.id;
    
    // Get current values from row data attributes
    document.getElementById('quickEditProductId').value = id;
    document.getElementById('quickEditName').value = row.dataset.name || '';
    document.getElementById('quickEditProductCode').value = row.dataset.productCode || '';
    document.getElementById('quickEditShortDescription').value = row.dataset.shortDescription || '';
    document.getElementById('quickEditPrice').value = row.dataset.price || '0';
    document.getElementById('quickEditSalePrice').value = row.dataset.salePrice || '';
    document.getElementById('quickEditQuantity').value = row.dataset.quantity || '0';
    document.getElementById('quickEditCategory').value = row.dataset.categoryId || '';
    
    // Show modal
    quickEditModal = new bootstrap.Modal(document.getElementById('quickEditModal'));
    quickEditModal.show();
}

// Initialize quick edit buttons
document.querySelectorAll('.quick-edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        openQuickEdit(this);
    });
});

function saveQuickEdit() {
    const id = document.getElementById('quickEditProductId').value;
    const form = document.getElementById('quickEditForm');
    const formData = new FormData(form);
    
    // Send quick update for each field
    const updates = [
        { field: 'name', value: formData.get('name') },
        { field: 'price', value: formData.get('price') },
        { field: 'quantity', value: formData.get('quantity') },
        { field: 'category_id', value: formData.get('category_id') }
    ];
    
    if (formData.get('product_code')) {
        updates.push({ field: 'product_code', value: formData.get('product_code') });
    }
    
    if (formData.get('short_description')) {
        updates.push({ field: 'short_description', value: formData.get('short_description') });
    }
    
    if (formData.get('sale_price')) {
        updates.push({ field: 'sale_price', value: formData.get('sale_price') });
    }
    
    Promise.all(updates.map(update => 
        fetch(`{{ route('admin.products.quick-update', ['product' => 'ID']) }}`.replace('ID', id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(update)
        })
    ))
    .then(() => {
        quickEditModal.hide();
        showToast('Product updated successfully', 'success');
        setTimeout(() => location.reload(), 500);
    });
}

// Change per page
function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// Toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 position-fixed`;
    toast.style.cssText = 'bottom: 20px; right: 20px; z-index: 9999;';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    setTimeout(() => toast.remove(), 5000);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Clear selection on page load
    clearSelection();
    
    // Live Search functionality
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        
        // Show spinner
        searchSpinner.style.display = 'block';
        
        // Debounce search - wait 300ms after user stops typing
        searchTimeout = setTimeout(() => {
            performLiveSearch(searchTerm);
        }, 300);
    });
    
    // Live filter for dropdowns
    const filterSelects = ['filterCategory', 'filterStatus', 'filterStock', 'filterFeatured'];
    filterSelects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                performLiveSearch(searchInput.value.trim());
            });
        }
    });
});

// Live search function
function performLiveSearch(searchTerm) {
    const searchSpinner = document.getElementById('searchSpinner');
    
    // Build query parameters
    const params = new URLSearchParams();
    
    if (searchTerm) {
        params.set('search', searchTerm);
    }
    
    const category = document.getElementById('filterCategory').value;
    if (category) params.set('category', category);
    
    const status = document.getElementById('filterStatus').value;
    if (status) params.set('status', status);
    
    const stockStatus = document.getElementById('filterStock').value;
    if (stockStatus) params.set('stock_status', stockStatus);
    
    const featured = document.getElementById('filterFeatured').value;
    if (featured) params.set('featured', featured);
    
    // Keep existing sort and per_page
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
    if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
    if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
    
    // Make AJAX request
    fetch(`{{ route('admin.products.index') }}?${params.toString()}&ajax=1`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        searchSpinner.style.display = 'none';
        
        if (data.html) {
            // Update table body
            const tbody = document.querySelector('#productsTable tbody');
            tbody.innerHTML = data.html;
            
            // Update pagination
            updatePagination(data.pagination);
            
            // Update stats
            updateStats(data.stats);
            
            // Reinitialize event listeners
            reinitializeEventListeners();
            
            // Clear selection
            clearSelection();
            
            // Update URL without reload
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({}, '', newUrl);
        }
    })
    .catch(err => {
        searchSpinner.style.display = 'none';
        console.error('Search error:', err);
    });
}

// Update pagination
function updatePagination(paginationHtml) {
    const paginationContainer = document.querySelector('.card-footer.bg-white');
    if (paginationContainer && paginationHtml) {
        // Find the pagination div (second child - after per-page selector)
        const paginationDiv = paginationContainer.querySelector('div:nth-child(2)');
        if (paginationDiv) {
            paginationDiv.innerHTML = paginationHtml;
        }
    }
}

// Update stats cards
function updateStats(stats) {
    if (!stats) return;
    
    // Map stat keys to their corresponding stat-card-value elements by label
    const statCardMap = {
        'total': 'Total Products',
        'active': 'Active',
        'inactive': 'Inactive',
        'featured': 'Featured',
        'low_stock': 'Low Stock',
        'out_of_stock': 'Out of Stock'
    };
    
    // Find all stat cards and update by matching label text
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        const label = card.querySelector('.stat-card-label');
        const value = card.querySelector('.stat-card-value');
        if (label && value) {
            const labelText = label.textContent.trim();
            for (const [key, expectedLabel] of Object.entries(statCardMap)) {
                if (labelText === expectedLabel && stats[key] !== undefined) {
                    value.textContent = typeof stats[key] === 'number' ? number_format(stats[key]) : stats[key];
                    break;
                }
            }
        }
    });
}

// Helper function to format numbers with commas
function number_format(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Reinitialize event listeners after AJAX update
function reinitializeEventListeners() {
    // Status toggle
    document.querySelectorAll('.status-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`{{ route('admin.products.toggle-status', ['product' => 'ID']) }}`.replace('ID', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.textContent = data.is_active ? 'Active' : 'Inactive';
                    this.classList.toggle('btn-success', data.is_active);
                    this.classList.toggle('btn-outline-secondary', !data.is_active);
                    showToast(data.message, 'success');
                }
            });
        });
    });
    
    // Featured toggle
    document.querySelectorAll('.featured-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`{{ route('admin.products.toggle-featured', ['product' => 'ID']) }}`.replace('ID', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const icon = this.querySelector('i');
                    icon.classList.toggle('bi-star', !data.is_featured);
                    icon.classList.toggle('bi-star-fill', data.is_featured);
                    this.classList.toggle('btn-info', data.is_featured);
                    this.classList.toggle('btn-outline-secondary', !data.is_featured);
                    showToast(data.message, 'success');
                }
            });
        });
    });
    
    // Quick edit buttons
    document.querySelectorAll('.quick-edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            openQuickEdit(this);
        });
    });
    
    // Product checkboxes
    document.querySelectorAll('.product-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });
}
</script>
@endpush
