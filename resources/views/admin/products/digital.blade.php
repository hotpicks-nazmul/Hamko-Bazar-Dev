@extends('admin.layouts.app')

@section('title', 'Digital Products')

@section('content')
@if(auth()->user()->hasPermission('products') && auth()->user()->hasPermission('products.view'))
<!-- Statistics Cards -->
<div class="stat-card-row stat-card-row-6 mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-collection"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total</span>
            <span class="stat-card-value" id="statTotal">{{ $stats['total'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value" id="statActive">{{ $stats['active'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-graph-up"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Sales</span>
            <span class="stat-card-value" id="statTotalSales">{{ $stats['total_sales'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value" id="statInactive">{{ $stats['inactive'] ?? 0 }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Digital Products</h4>
        <small class="text-muted">Manage downloadable products, software, e-books, and more</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.products.digital.export', request()->query()) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
        <a href="{{ route('admin.products.digital.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Digital Product
        </a>
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
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Name, SKU..." value="{{ request('search') }}">
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
                
                <!-- File Type -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">File Type</label>
                    <select name="file_type" id="filterFileType" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach($fileTypes as $type)
                            <option value="{{ $type }}" {{ request('file_type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- License Requirement -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">License</label>
                    <select name="requires_license" id="filterLicense" class="form-select form-select-sm">
                        <option value="">All Products</option>
                        <option value="yes" {{ request('requires_license') === 'yes' ? 'selected' : '' }}>Requires License</option>
                        <option value="no" {{ request('requires_license') === 'no' ? 'selected' : '' }}>No License</option>
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
                
                <!-- Buttons -->
                <div class="col-lg-2 col-md-4 col-sm-8">
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.products.digital.index') }}" class="btn btn-sm btn-outline-secondary flex-grow-1" id="resetFilters">
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
                <button type="button" class="btn btn-sm btn-info" onclick="bulkAction('feature')">
                    <i class="bi bi-star me-1"></i> Feature
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
                        <th class="text-center">Product</th>
                        <th class="text-center">File Info</th>
                        <th class="text-center">Category</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Sales</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-checkbox" value="{{ $product->id }}" onchange="updateSelection({{ $product->id }}, this.checked)">
                        </td>
                        <td>
                            @php
                                $imageUrl = $product->featured_image;
                                if($imageUrl) {
                                    $imageUrl = ltrim($imageUrl, '/');
                                    if (str_starts_with($imageUrl, 'http')) {
                                        // Already full URL - use as is
                                    } elseif (str_starts_with($imageUrl, 'storage/')) {
                                        $imageUrl = '/' . $imageUrl;
                                    } elseif (str_starts_with($imageUrl, 'products/')) {
                                        $imageUrl = '/storage/' . $imageUrl;
                                    } else {
                                        $imageUrl = '/storage/' . $imageUrl;
                                    }
                                }
                            @endphp
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-file-earmark text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div>
                                <a href="{{ route('admin.products.digital.edit', $product->id) }}" class="fw-semibold text-decoration-none">
                                    {{ $product->name }}
                                </a>
                                @if($product->is_featured)
                                    <span class="badge bg-warning text-dark ms-1">Featured</span>
                                @endif
                                @if($product->requires_license_key)
                                    <span class="badge bg-info ms-1">License</span>
                                @endif
                            </div>
                            <small class="text-muted">SKU: {{ $product->sku }}</small>
                            @if($product->version)
                                <span class="badge bg-secondary ms-1">v{{ $product->version }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($product->file_format)
                                <div>
                                    <span class="badge bg-light text-dark">{{ $product->file_format }}</span>
                                    <span class="text-muted small">{{ $product->file_size_formatted }}</span>
                                </div>
                            @elseif($product->download_link)
                                <div>
                                    <span class="badge bg-light text-dark">External Link</span>
                                </div>
                            @else
                                <span class="text-muted">No file</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($product->digitalCategory)
                                <span class="badge bg-light text-dark">{{ $product->digitalCategory->name }}</span>
                            @elseif($product->category)
                                <span class="badge bg-light text-dark">{{ $product->category->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div>
                                @if($product->sale_price && $product->sale_price < $product->price)
                                    <del class="text-muted small">৳{{ number_format($product->price, 0) }}</del>
                                    <span class="text-danger fw-semibold">৳{{ number_format($product->sale_price, 0) }}</span>
                                @else
                                    <span class="fw-semibold">৳{{ number_format($product->price, 0) }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="text-center">
                                <div class="fw-semibold">{{ $product->digitalDownloads()->count() }}</div>
                                <small class="text-muted">downloads</small>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm status-toggle {{ $product->is_active ? 'btn-success' : 'btn-outline-secondary' }}" 
                                    data-id="{{ $product->id }}" 
                                    data-status="{{ $product->is_active ? 'active' : 'inactive' }}"
                                    title="Click to toggle status">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('admin.products.digital.edit', $product->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="showDownloadStats({{ $product->id }}); event.stopPropagation();" title="Download Stats">
                                    <i class="bi bi-bar-chart"></i>
                                </button>
                                @if($product->requires_license_key)
                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="showLicenseKeys({{ $product->id }}); event.stopPropagation();" title="License Keys">
                                    <i class="bi bi-key"></i>
                                </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteProduct({{ $product->id }}); event.stopPropagation();" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-file-earmark display-4"></i>
                                <p class="mt-2">No digital products found.</p>
                                <a href="{{ route('admin.products.digital.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i> Add Digital Product
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination & Per Page -->
        @if($products->hasPages())
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
        </div>
        @endif
    </div>
</div>

<!-- License Keys Modal -->
<div class="modal fade" id="licenseKeysModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-key me-2"></i>License Keys</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div id="licenseStats" class="text-muted"></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportLicenseKeys()">
                            <i class="bi bi-download me-1"></i> Export CSV
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" onclick="showGenerateLicenseModal()">
                            <i class="bi bi-plus-lg me-1"></i> Generate Keys
                        </button>
                    </div>
                </div>
                <div id="licenseKeysContent">
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

<!-- Generate License Keys Modal -->
<div class="modal fade" id="generateLicenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate License Keys</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="generateLicenseForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Number of Keys</label>
                        <input type="number" name="count" id="licenseCount" class="form-control" value="10" min="1" max="1000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Key Format</label>
                        <input type="text" name="format" id="licenseFormat" class="form-control" value="XXXX-XXXX-XXXX-XXXX" placeholder="XXXX-XXXX-XXXX-XXXX">
                        <small class="text-muted">Use X for random alphanumeric characters</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="generateLicenseKeys()">
                    <i class="bi bi-key me-1"></i> Generate Keys
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Download Stats Modal -->
<div class="modal fade" id="downloadStatsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bar-chart me-2"></i>Download Statistics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="downloadStatsContent">
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
<form id="bulkActionForm" method="POST" action="{{ route('admin.products.digital.bulk-action') }}">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ids" id="bulkIdsInput">
</form>

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="">
    @csrf
    @method('DELETE')
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
let currentProductId = null;
let licenseKeysModal, generateLicenseModal, downloadStatsModal;

document.addEventListener('DOMContentLoaded', function() {
    licenseKeysModal = new bootstrap.Modal(document.getElementById('licenseKeysModal'));
    generateLicenseModal = new bootstrap.Modal(document.getElementById('generateLicenseModal'));
    downloadStatsModal = new bootstrap.Modal(document.getElementById('downloadStatsModal'));
});

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

// Update selection
function updateSelection(id, checked) {
    if (checked) {
        selectedProducts.add(id);
    } else {
        selectedProducts.delete(id);
    }
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
            confirmMsg = `Feature ${selectedProducts.size} product(s)?`;
            break;
        case 'unfeature':
            confirmMsg = `Remove feature from ${selectedProducts.size} product(s)?`;
            break;
    }
    
    if (confirmMsg && !confirm(confirmMsg)) return;
    
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedProducts));
    document.getElementById('bulkActionForm').submit();
}

// Toggle status via AJAX
document.querySelectorAll('.status-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch(`{{ route('admin.products.digital.toggle-status', ['id' => 'ID']) }}`.replace('ID', id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.textContent = data.status === 'active' ? 'Active' : 'Inactive';
                this.classList.toggle('btn-success', data.status === 'active');
                this.classList.toggle('btn-outline-secondary', data.status !== 'active');
                this.dataset.status = data.status;
                showToast(data.message || 'Status updated successfully', 'success');
            }
        });
    });
});

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

// Delete product
function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) return;
    
    fetch(`{{ route('admin.products.digital.destroy', ['id' => 'ID']) }}`.replace('ID', id), {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            performLiveSearch(document.getElementById('liveSearch').value.trim());
        } else {
            showToast(data.message || 'Error deleting product', 'error');
        }
    })
    .catch(err => {
        console.error('Delete error:', err);
        showToast('Error deleting product', 'error');
    });
}

// Show license keys
function showLicenseKeys(productId) {
    currentProductId = productId;
    licenseKeysModal.show();
    loadLicenseKeys();
}

// Load license keys
function loadLicenseKeys() {
    fetch(`{{ route('admin.products.digital.license-keys', ['id' => '__ID__']) }}`.replace('__ID__', currentProductId))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderLicenseKeys(data.data);
            }
        });
}

// Render license keys
function renderLicenseKeys(keys) {
    const container = document.getElementById('licenseKeysContent');
    
    if (keys.data.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-muted">No license keys found. Generate some keys to get started.</div>';
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>License Key</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Expires</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    keys.data.forEach(key => {
        const statusClass = key.status === 'available' ? 'success' : (key.status === 'used' ? 'primary' : 'danger');
        html += `
            <tr>
                <td><code>${key.license_key}</code></td>
                <td><span class="badge bg-${statusClass}">${key.status}</span></td>
                <td>${key.user ? key.user.name : '-'}</td>
                <td>${key.expires_at || '-'}</td>
                <td>
                    ${key.status !== 'used' ? `<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteLicenseKey(${key.id})"><i class="bi bi-trash"></i></button>` : ''}
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

// Show generate license modal
function showGenerateLicenseModal() {
    document.getElementById('licenseCount').value = 10;
    document.getElementById('licenseFormat').value = 'XXXX-XXXX-XXXX-XXXX';
    generateLicenseModal.show();
}

// Generate license keys
function generateLicenseKeys() {
    const count = document.getElementById('licenseCount').value;
    const format = document.getElementById('licenseFormat').value;
    
    fetch(`{{ route('admin.products.digital.generate-license-keys', ['id' => '__ID__']) }}`.replace('__ID__', currentProductId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ count, format })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            generateLicenseModal.hide();
            loadLicenseKeys();
            alert(data.message);
        } else {
            alert(data.message);
        }
    });
}

// Export license keys
function exportLicenseKeys() {
    window.location.href = `{{ route('admin.products.digital.export-license-keys', ['id' => '__ID__']) }}`.replace('__ID__', currentProductId);
}

// Delete license key
function deleteLicenseKey(keyId) {
    if (!confirm('Are you sure you want to delete this license key?')) return;
    
    fetch(`{{ route('admin.products.digital.delete-license-key', ['id' => '__ID__', 'keyId' => '__KEY__']) }}`
        .replace('__ID__', currentProductId)
        .replace('__KEY__', keyId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadLicenseKeys();
        } else {
            alert(data.message);
        }
    });
}

// Show download stats
function showDownloadStats(productId) {
    currentProductId = productId;
    downloadStatsModal.show();
    
    fetch(`{{ route('admin.products.digital.download-stats', ['id' => '__ID__']) }}`.replace('__ID__', productId))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderDownloadStats(data.data);
            }
        });
}

// Render download stats
function renderDownloadStats(stats) {
    const container = document.getElementById('downloadStatsContent');
    
    let html = `
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary">${stats.total_downloads}</h3>
                        <small class="text-muted">Total Downloads</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success">${stats.unique_downloads}</h3>
                        <small class="text-muted">Unique Downloads</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    if (stats.recent_downloads.length > 0) {
        html += `
            <h6>Recent Downloads</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Downloads</th>
                            <th>Last Download</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        stats.recent_downloads.forEach(dl => {
            html += `
                <tr>
                    <td>${dl.user ? dl.user.name : 'Unknown'}</td>
                    <td>${dl.download_count}</td>
                    <td>${dl.last_download_at || '-'}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
    }
    
    container.innerHTML = html;
}

// Change per page
function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    window.location.href = url.toString();
}

// Live search
let searchTimeout;
const searchInput = document.getElementById('liveSearch');
const searchSpinner = document.getElementById('searchSpinner');

searchInput?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const searchTerm = this.value.trim();
    
    // Show spinner
    searchSpinner.style.display = 'block';
    
    // Debounce - wait 300ms after user stops typing
    searchTimeout = setTimeout(() => {
        performLiveSearch(searchTerm);
    }, 300);
});

// Filter dropdowns trigger search on change
const filterSelects = ['filterCategory', 'filterFileType', 'filterLicense', 'filterStatus'];
filterSelects.forEach(id => {
    const select = document.getElementById(id);
    if (select) {
        select.addEventListener('change', function() {
            performLiveSearch(searchInput.value.trim());
        });
    }
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
    
    const fileType = document.getElementById('filterFileType').value;
    if (fileType) params.set('file_type', fileType);
    
    const license = document.getElementById('filterLicense').value;
    if (license) params.set('requires_license', license);
    
    const status = document.getElementById('filterStatus').value;
    if (status) params.set('status', status);
    
    // Keep existing sort and per_page
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
    if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
    if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
    
    // Make AJAX request
    fetch(`{{ route('admin.products.digital.index') }}?${params.toString()}&ajax=1`, {
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
            const tbody = document.querySelector('#productTableBody');
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
        'inactive': 'statInactive',
        'total_sales': 'statTotalSales',
        'total_downloads': 'statTotalDownloads',
        'total_revenue': 'statTotalRevenue',
        'license_based': 'statLicense',
        'available_licenses': 'statAvailableLicenses'
    };
    
    Object.keys(statMap).forEach(key => {
        const el = document.getElementById(statMap[key]);
        if (el && stats[key] !== undefined) {
            if (key === 'total_revenue') {
                el.textContent = '৳' + parseInt(stats[key]).toLocaleString();
            } else {
                el.textContent = stats[key];
            }
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Product checkboxes
    document.querySelectorAll('.product-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            updateSelection(parseInt(this.value), this.checked);
        });
    });
    
    console.log('Digital products page initialized');
});
</script>
@endpush
