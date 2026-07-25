@extends('admin.layouts.app')

@section('title', 'Product Bundles')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-collection"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total</span>
            <span class="stat-card-value" id="stat-total">{{ $stats['total'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value" id="stat-active">{{ $stats['active'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value" id="stat-inactive">{{ $stats['inactive'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-star"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Featured</span>
            <span class="stat-card-value" id="stat-featured">{{ $stats['featured'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Expired</span>
            <span class="stat-card-value" id="stat-expired">{{ $stats['expired'] ?? 0 }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Product Bundles</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.product-bundles.export') }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Export
        </a>
        <a href="{{ route('admin.product-bundles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add New Bundle
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
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Name, slug..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </span>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    </select>
                </div>
                
                <!-- Featured -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Featured</label>
                    <select name="featured" id="filterFeatured" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="yes" {{ request('featured') === 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ request('featured') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                
                <!-- Per Page -->
                <div class="col-lg-1 col-md-2 col-sm-4">
                    <label class="form-label small text-muted">Per Page</label>
                    <select name="per_page" id="perPage" class="form-select form-select-sm">
                        <option value="10" {{ request('per_page', 25) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                
                <!-- Buttons -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.product-bundles.index') }}" class="btn btn-sm btn-outline-secondary flex-grow-1" id="resetFilters">
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
                <button type="button" class="btn btn-sm btn-outline-info" onclick="bulkAction('unfeature')">
                    <i class="bi bi-star-fill me-1"></i> Unfeature
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bundles Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="bundlesTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th style="width: 80px;">Image</th>
                        <th>
                            <a href="{{ route('admin.product-bundles.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Bundle Name
                                @if(request('sort') === 'name')
                                    <i class="bi bi-{{ request('direction') === 'asc' ? 'arrow-up' : 'arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 100px;">
                            <a href="{{ route('admin.product-bundles.index', array_merge(request()->query(), ['sort' => 'products_count', 'direction' => request('sort') === 'products_count' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Products
                                @if(request('sort') === 'products_count')
                                    <i class="bi bi-{{ request('direction') === 'asc' ? 'arrow-up' : 'arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 120px;">
                            <a href="{{ route('admin.product-bundles.index', array_merge(request()->query(), ['sort' => 'bundle_price', 'direction' => request('sort') === 'bundle_price' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Price
                                @if(request('sort') === 'bundle_price')
                                    <i class="bi bi-{{ request('direction') === 'asc' ? 'arrow-up' : 'arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 100px;">Discount</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 80px;">Featured</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($bundles as $bundle)
                        @include('admin.product-bundles.partials.table-row', ['bundle' => $bundle])
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-boxes text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">No product bundles found.</p>
                                <a href="{{ route('admin.product-bundles.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i> Create Your First Bundle
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($bundles) && method_exists($bundles, 'hasPages') && $bundles->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2" id="paginationContainer">
            <div class="text-muted small">
                Showing {{ $bundles->firstItem() }} - {{ $bundles->lastItem() }} of {{ $bundles->total() }} bundles
            </div>
            <div id="paginationLinks">
                {{ $bundles->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.product-bundles.bulk-action') }}">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ids" id="bulkIdsInput">
</form>
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
.bundle-checkbox:checked + td img {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.3);
}
.price-original {
    text-decoration: line-through;
    color: #999;
    font-size: 0.85em;
}
</style>
@endpush

@push('scripts')
<script>
let selectedBundles = new Set();

// Toggle select all
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.bundle-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedBundles.add(parseInt(cb.value));
        } else {
            selectedBundles.delete(parseInt(cb.value));
        }
    });
    updateBulkActions();
}

// Clear selection
function clearSelection() {
    selectedBundles.clear();
    const checkboxes = document.querySelectorAll('.bundle-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateBulkActions();
}

// Update bulk actions bar visibility
function updateBulkActions() {
    const count = selectedBundles.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    
    // Update select all checkbox state
    const checkboxes = document.querySelectorAll('.bundle-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    document.getElementById('selectAllCheckbox').checked = allChecked && checkboxes.length > 0;
}

// Perform bulk action
function bulkAction(action) {
    if (selectedBundles.size === 0) {
        showToast('Please select at least one bundle.', 'warning');
        return;
    }
    
    let confirmMsg = '';
    switch(action) {
        case 'delete':
            confirmMsg = `Are you sure you want to delete ${selectedBundles.size} bundle(s)?`;
            break;
        case 'activate':
            confirmMsg = `Activate ${selectedBundles.size} bundle(s)?`;
            break;
        case 'deactivate':
            confirmMsg = `Deactivate ${selectedBundles.size} bundle(s)?`;
            break;
        case 'feature':
            confirmMsg = `Mark ${selectedBundles.size} bundle(s) as featured?`;
            break;
        case 'unfeature':
            confirmMsg = `Remove ${selectedBundles.size} bundle(s) from featured?`;
            break;
        default:
            confirmMsg = `Apply this action to ${selectedBundles.size} bundle(s)?`;
    }
    
    if (!confirm(confirmMsg)) return;
    
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedBundles));
    document.getElementById('bulkActionForm').submit();
}

// Toggle status via AJAX
function initStatusToggles() {
    document.querySelectorAll('.status-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`{{ route('admin.product-bundles.toggle-status', ['productBundle' => 'ID']) }}`.replace('ID', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.textContent = data.status;
                    this.classList.toggle('btn-success', data.color === 'success');
                    this.classList.toggle('btn-secondary', data.color === 'secondary');
                    this.classList.toggle('btn-warning', data.color === 'warning');
                    this.classList.toggle('btn-danger', data.color === 'danger');
                    this.classList.toggle('btn-info', data.color === 'info');
                    showToast('Status updated successfully.', 'success');
                    updateStats();
                }
            });
        });
    });
}

// Toggle featured via AJAX
function initFeaturedToggles() {
    document.querySelectorAll('.featured-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`{{ route('admin.product-bundles.toggle-featured', ['productBundle' => 'ID']) }}`.replace('ID', id), {
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
                    showToast('Featured status updated.', 'success');
                    updateStats();
                }
            });
        });
    });
}

// Update stats via AJAX
function updateStats() {
    fetch(`{{ route('admin.product-bundles.index') }}?ajax=1`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.stats) {
            document.getElementById('stat-total').textContent = data.stats.total;
            document.getElementById('stat-active').textContent = data.stats.active;
            document.getElementById('stat-inactive').textContent = data.stats.inactive;
            document.getElementById('stat-featured').textContent = data.stats.featured;
            document.getElementById('stat-expired').textContent = data.stats.expired;
        }
    });
}

// Live search functionality
let searchTimeout;
const searchInput = document.getElementById('liveSearch');
const searchSpinner = document.getElementById('searchSpinner');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const searchTerm = this.value.trim();
    
    searchSpinner.style.display = 'block';
    
    searchTimeout = setTimeout(() => {
        performLiveSearch(searchTerm);
    }, 300);
});

// Filter dropdowns trigger search on change
const filterSelects = ['filterStatus', 'filterFeatured', 'perPage'];
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
    const params = new URLSearchParams();
    
    if (searchTerm) params.set('search', searchTerm);
    
    // Add filter values
    const status = document.getElementById('filterStatus').value;
    if (status) params.set('status', status);
    
    const featured = document.getElementById('filterFeatured').value;
    if (featured) params.set('featured', featured);
    
    const perPage = document.getElementById('perPage').value;
    params.set('per_page', perPage);
    
    // Keep existing sort
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
    if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
    
    // AJAX request
    fetch(`{{ route('admin.product-bundles.index') }}?${params.toString()}&ajax=1`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        searchSpinner.style.display = 'none';
        
        if (data.html) {
            document.querySelector('#tableBody').innerHTML = data.html;
            
            // Re-initialize toggles
            initStatusToggles();
            initFeaturedToggles();
            initCheckboxes();
            
            // Update URL without reload
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({}, '', newUrl);
        }
        
        if (data.stats) {
            document.getElementById('stat-total').textContent = data.stats.total;
            document.getElementById('stat-active').textContent = data.stats.active;
            document.getElementById('stat-inactive').textContent = data.stats.inactive;
            document.getElementById('stat-featured').textContent = data.stats.featured;
            document.getElementById('stat-expired').textContent = data.stats.expired;
        }
        
        // Update pagination
        if (data.pagination) {
            document.getElementById('paginationLinks').innerHTML = data.pagination;
        }
    });
}

// Initialize checkboxes
function initCheckboxes() {
    document.querySelectorAll('.bundle-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                selectedBundles.add(parseInt(this.value));
            } else {
                selectedBundles.delete(parseInt(this.value));
            }
            updateBulkActions();
        });
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initStatusToggles();
    initFeaturedToggles();
    initCheckboxes();
});
</script>
@endpush
