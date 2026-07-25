@extends('admin.layouts.app')

@section('title', 'In-House Products')

@section('content')
@if(auth()->user()->hasPermission('products') && auth()->user()->hasPermission('products.view'))
<!-- Statistics Cards -->
<div class="stat-card-row stat-card-row-6 mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-grid-3x3-gap"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Total</span><span class="stat-card-value" id="statTotal">{{ $stats['total'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Active</span><span class="stat-card-value" id="statActive">{{ $stats['active'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Low Stock</span><span class="stat-card-value" id="statLowStock">{{ $stats['low_stock'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Out of Stock</span><span class="stat-card-value" id="statOutOfStock">{{ $stats['out_of_stock'] ?? 0 }}</span></div>
    </div>
    @if(auth()->user()->hasPermission('products.view-revenue'))
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Stock Value</span><span class="stat-card-value" id="statStockValue">৳{{ number_format($stats['total_stock_value'] ?? 0, 0) }}</span></div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-tag"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Retail Value</span><span class="stat-card-value" id="statRetailValue">৳{{ number_format($stats['total_retail_value'] ?? 0, 0) }}</span></div>
    </div>
    @endif
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">In-House Products</h4>
        <small class="text-muted">Products sold directly by your store (not by sellers)</small>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-warning" onclick="showLowStockAlerts()">
            <i class="bi bi-exclamation-triangle me-1"></i> Stock Alerts
            <span class="badge bg-warning text-dark" id="lowStockBadge">{{ ($stats['low_stock'] ?? 0) + ($stats['out_of_stock'] ?? 0) }}</span>
        </button>
        <a href="{{ route('admin.products.export-in-house', request()->query()) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
        <a href="{{ route('admin.products.create', ['from' => 'in-house']) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add New Product
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search -->
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Name, SKU..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </span>
                    </div>
                </div>
                
                <!-- Category -->
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <label class="form-label small text-muted">Category</label>
                    <select name="category" id="filterCategory" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Brand -->
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <label class="form-label small text-muted">Brand</label>
                    <select name="brand" id="filterBrand" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                                {{ $brand }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Stock Status -->
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <label class="form-label small text-muted">Stock</label>
                    <select name="stock_status" id="filterStock" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out</option>
                    </select>
                </div>
                
                <!-- Status -->
                <div class="col-lg-1 col-md-2 col-sm-4">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                <div class="col-lg-2 col-md-4 col-sm-12">
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.products.in-house') }}" class="btn btn-sm btn-outline-secondary flex-grow-1" id="resetFilters">
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
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('activate')">
                    <i class="bi bi-check-circle me-1"></i> Activate
                </button>
                <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('deactivate')">
                    <i class="bi bi-pause-circle me-1"></i> Deactivate
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="bulkAction('feature')">
                    <i class="bi bi-star me-1"></i> Feature
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkAction('unfeature')">
                    <i class="bi bi-star-fill me-1"></i> Unfeature
                </button>
                <button type="button" class="btn btn-sm btn-info" onclick="showBulkStockModal()">
                    <i class="bi bi-box-seam me-1"></i> Update Stock
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
                            <a href="{{ route('admin.products.in-house', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Product
                                @if(request('sort') == 'name')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th>Product Code</th>
                        <th>Category</th>
                        @if(auth()->user()->hasPermission('products.view-cost'))
                        <th>
                            <a href="{{ route('admin.products.in-house', array_merge(request()->query(), ['sort' => 'purchase_price', 'direction' => request('sort') == 'purchase_price' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Cost
                                @if(request('sort') == 'purchase_price')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        @endif
                        <th>
                            <a href="{{ route('admin.products.in-house', array_merge(request()->query(), ['sort' => 'price', 'direction' => request('sort') == 'price' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Price
                                @if(request('sort') == 'price')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.products.in-house', array_merge(request()->query(), ['sort' => 'quantity', 'direction' => request('sort') == 'quantity' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Stock
                                @if(request('sort') == 'quantity')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th>Stock Value</th>
                        <th>Status</th>
                        <th style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @include('admin.products.partials.in-house-product-rows')
                </tbody>
            </table>
        </div>
        
        <!-- Pagination & Per Page -->
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2" id="paginationFooter">
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
            <div id="paginationLinks">
                {{ $products->appends(request()->query())->links() }}
            </div>
            <div class="text-muted small" id="paginationInfo">
                Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() ?? 0 }} products
            </div>
        </div>
    </div>
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="stockForm">
                    @csrf
                    <input type="hidden" id="stockProductId">
                    
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" id="stockProductName" class="form-control" readonly>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Current Stock</label>
                            <input type="text" id="stockCurrentQuantity" class="form-control" readonly>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Low Stock Threshold</label>
                            <input type="number" id="stockLowThreshold" class="form-control" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Operation</label>
                        <select id="stockOperation" class="form-select">
                            <option value="set">Set to specific value</option>
                            <option value="add">Add to current stock</option>
                            <option value="subtract">Subtract from current stock</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" id="stockQuantity" class="form-control" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveStock()">
                    <i class="bi bi-check-lg me-1"></i> Update Stock
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Stock Update Modal -->
<div class="modal fade" id="bulkStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Stock Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkStockForm">
                    @csrf
                    <p class="text-muted">Update stock for <span id="bulkStockCount">0</span> selected products</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Operation</label>
                        <select id="bulkStockOperation" class="form-select">
                            <option value="set">Set all to specific value</option>
                            <option value="add">Add to all stocks</option>
                            <option value="subtract">Subtract from all stocks</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" id="bulkStockQuantity" class="form-control" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveBulkStock()">
                    <i class="bi bi-check-lg me-1"></i> Update All
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Alerts Modal -->
<div class="modal fade" id="lowStockAlertsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Stock Alerts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="lowStockAlertsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.products.in-house.bulk-action') }}">
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
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
    
    .status-toggle {
        min-width: 70px;
        transition: all 0.2s;
    }
    .status-toggle:hover {
        transform: scale(1.05);
    }
    .table > :not(caption) > * > * {
        padding: 0.75rem 0.5rem;
    }
    .product-checkbox:checked + td img {
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.3);
    }
    .table thead th {
        background: #f8f9fa !important;
    }
    
    /* Force Bootstrap Icons to display - SAME AS REFERENCE PAGE */
    .stat-card-icon i,
    .stat-card-icon i::before,
    .bi::before,
    [class*="bi bi-"]::before {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-family: 'bootstrap-icons' !important;
    }
    
    /* Override icon colors for stat cards */
    .stat-card-primary .stat-card-icon i::before { color: #0d6efd !important; }
    .stat-card-success .stat-card-icon i::before { color: #198754 !important; }
    .stat-card-info .stat-card-icon i::before { color: #0dcaf0 !important; }
    .stat-card-warning .stat-card-icon i::before { color: #ffc107 !important; }
    .stat-card-danger .stat-card-icon i::before { color: #dc3545 !important; }
    .stat-card-secondary .stat-card-icon i::before { color: #6c757d !important; }
    
    /* Make the whole icon colored */
    .stat-card-icon i { color: inherit !important; }
</style>
@endpush

@push('scripts')
<script>
let selectedProducts = new Set();
let stockModal, bulkStockModal, lowStockAlertsModal;

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
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = true;
        selectedProducts.add(parseInt(cb.value));
    });
    updateBulkActions();
    const totalProducts = {{ $products->total() ?? 0 }};
    document.getElementById('selectedCount').textContent = totalProducts + ' (all pages)';
}

// Clear selection
function clearSelection() {
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
    
    if (confirmMsg && !confirm(confirmMsg)) return;
    
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedProducts));
    document.getElementById('bulkActionForm').submit();
}

// Show stock update modal
function showStockModal(id, name, quantity, lowThreshold) {
    document.getElementById('stockProductId').value = id;
    document.getElementById('stockProductName').value = name;
    document.getElementById('stockCurrentQuantity').value = quantity;
    document.getElementById('stockLowThreshold').value = lowThreshold;
    document.getElementById('stockQuantity').value = '';
    document.getElementById('stockOperation').value = 'set';
    
    stockModal = new bootstrap.Modal(document.getElementById('stockModal'));
    stockModal.show();
}

// Save stock update
function saveStock() {
    const id = document.getElementById('stockProductId').value;
    const operation = document.getElementById('stockOperation').value;
    const quantity = document.getElementById('stockQuantity').value;
    const lowThreshold = document.getElementById('stockLowThreshold').value;
    
    // Update stock
    fetch(`{{ route('admin.products.update-stock', ['product' => 'ID']) }}`.replace('ID', id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ operation, quantity })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update low stock threshold if changed
            return fetch(`{{ route('admin.products.update-low-stock-threshold', ['product' => 'ID']) }}`.replace('ID', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ threshold: lowThreshold })
            });
        }
        return data;
    })
    .then(() => {
        stockModal.hide();
        showToast('Stock updated successfully', 'success');
        setTimeout(() => location.reload(), 500);
    });
}

// Show bulk stock modal
function showBulkStockModal() {
    if (selectedProducts.size === 0) {
        alert('Please select at least one product.');
        return;
    }
    
    document.getElementById('bulkStockCount').textContent = selectedProducts.size;
    document.getElementById('bulkStockQuantity').value = '';
    document.getElementById('bulkStockOperation').value = 'set';
    
    bulkStockModal = new bootstrap.Modal(document.getElementById('bulkStockModal'));
    bulkStockModal.show();
}

// Save bulk stock update
function saveBulkStock() {
    const operation = document.getElementById('bulkStockOperation').value;
    const quantity = document.getElementById('bulkStockQuantity').value;
    
    fetch(`{{ route('admin.products.bulk-stock-update') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            ids: Array.from(selectedProducts),
            operation,
            quantity
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            bulkStockModal.hide();
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        }
    });
}

// Show low stock alerts
function showLowStockAlerts() {
    lowStockAlertsModal = new bootstrap.Modal(document.getElementById('lowStockAlertsModal'));
    lowStockAlertsModal.show();
    
    fetch(`{{ route('admin.products.low-stock-alerts') }}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let html = '';
                if (data.products.length === 0) {
                    html = '<div class="text-center py-4"><i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i><p class="mt-3">All products are well stocked!</p></div>';
                } else {
                    html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Product</th><th>Product Code</th><th>Current</th><th>Threshold</th><th>Status</th><th>Action</th></tr></thead><tbody>';
                    data.products.forEach(p => {
                        const status = p.quantity <= 0 ? 
                            '<span class="badge bg-danger">Out of Stock</span>' : 
                            '<span class="badge bg-warning text-dark">Low Stock</span>';
                        html += `<tr>
                            <td>${p.name}</td>
                            <td style="white-space: nowrap;">
    <div class="small text-truncate" style="max-width: 120px;">
        <span class="badge bg-primary">${p.sku}</span>
    </div>
</td>
                            <td>${p.quantity}</td>
                            <td>${p.low_stock_threshold}</td>
                            <td>${status}</td>
                            <td><button class="btn btn-sm btn-outline-primary" onclick="lowStockAlertsModal.hide(); showStockModal(${p.id}, '${p.name}', ${p.quantity}, ${p.low_stock_threshold})">Update</button></td>
                        </tr>`;
                    });
                    html += '</tbody></table></div>';
                }
                document.getElementById('lowStockAlertsContent').innerHTML = html;
            }
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
    
    // Initialize status toggle event listeners on page load
    reinitializeEventListeners();
    
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
    const filterSelects = ['filterCategory', 'filterBrand', 'filterStatus', 'filterStock', 'filterFeatured'];
    filterSelects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                performLiveSearch(searchInput.value.trim());
            });
        }
    });
    
    // Initialize stock edit buttons
    reinitializeStockButtons();
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
    
    const brand = document.getElementById('filterBrand').value;
    if (brand) params.set('brand', brand);
    
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
    fetch(`{{ route('admin.products.in-house') }}?${params.toString()}&ajax=1`, {
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
            if (data.pagination) {
                document.getElementById('paginationLinks').innerHTML = data.pagination;
            }
            
            // Update stats
            updateStats(data.stats);
            
            // Update pagination info
            if (data.total !== undefined) {
                const info = `Showing 1 - ${Math.min(data.total, parseInt(params.get('per_page') || 25))} of ${data.total} products`;
                document.getElementById('paginationInfo').textContent = info;
            }
            
            // Reinitialize event listeners
            reinitializeEventListeners();
            reinitializeStockButtons();
            
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

// Update stats cards
function updateStats(stats) {
    if (!stats) return;
    
    const statMap = {
        'total': 'statTotal',
        'active': 'statActive',
        'low_stock': 'statLowStock',
        'out_of_stock': 'statOutOfStock',
        'total_stock_value': 'statStockValue',
        'total_retail_value': 'statRetailValue'
    };
    
    Object.keys(statMap).forEach(key => {
        const el = document.getElementById(statMap[key]);
        if (el && stats[key] !== undefined) {
            if (key === 'total_stock_value' || key === 'total_retail_value') {
                el.textContent = '৳' + parseInt(stats[key]).toLocaleString();
            } else {
                el.textContent = stats[key];
            }
        }
    });
    
    // Update low stock badge
    const lowStockBadge = document.getElementById('lowStockBadge');
    if (lowStockBadge) {
        lowStockBadge.textContent = (stats.low_stock || 0) + (stats.out_of_stock || 0);
    }
}

// Reinitialize stock edit buttons
function reinitializeStockButtons() {
    document.querySelectorAll('.stock-edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const quantity = parseInt(this.dataset.quantity);
            const threshold = parseInt(this.dataset.threshold);
            showStockModal(id, name, quantity, threshold);
        });
    });
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
    
    // Product checkboxes
    document.querySelectorAll('.product-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                selectedProducts.add(parseInt(this.value));
            } else {
                selectedProducts.delete(parseInt(this.value));
            }
            updateBulkActions();
        });
    });
}
</script>
@endpush