@extends('admin.layouts.app')

@section('title', 'Delivery Partners')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-building"></i></div>
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
        <div class="stat-card-icon"><i class="bi bi-pause-circle"></i></div>
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
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Delivery Partners</h4>
    <a href="{{ route('admin.delivery.partners.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Partner
    </a>
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
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Name, contact, email..." value="{{ request('search') }}">
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
                    </select>
                </div>
                
                <!-- Service Type -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Service Type</label>
                    <select name="service_type" id="filterServiceType" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="express" {{ request('service_type') === 'express' ? 'selected' : '' }}>Express</option>
                        <option value="standard" {{ request('service_type') === 'standard' ? 'selected' : '' }}>Standard</option>
                        <option value="overnight" {{ request('service_type') === 'overnight' ? 'selected' : '' }}>Overnight</option>
                        <option value="international" {{ request('service_type') === 'international' ? 'selected' : '' }}>International</option>
                        <option value="all" {{ request('service_type') === 'all' ? 'selected' : '' }}>All Services</option>
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
                        <a href="{{ route('admin.delivery.partners.index') }}" class="btn btn-sm btn-outline-secondary flex-grow-1" id="resetFilters">
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

<!-- Partners Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="partnersTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th style="width: 60px;">Logo</th>
                        <th>
                            <a href="{{ route('admin.delivery.partners.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Partner Name
                                @if(request('sort') === 'name')
                                    <i class="bi bi-{{ request('direction') === 'asc' ? 'arrow-up' : 'arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 140px;">Service Type</th>
                        <th style="width: 120px;">Contact Person</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 80px;">Featured</th>
                        <th style="width: 100px;">Sort Order</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($partners as $partner)
                        @include('admin.delivery.partners.partials.table-row', ['partner' => $partner])
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-truck text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">No delivery partners found.</p>
                                <a href="{{ route('admin.delivery.partners.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i> Add Your First Partner
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($partners) && method_exists($partners, 'hasPages') && $partners->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2" id="paginationContainer">
            <div class="text-muted small">
                Showing {{ $partners->firstItem() }} - {{ $partners->lastItem() }} of {{ $partners->total() }} partners
            </div>
            <div id="paginationLinks">
                {{ $partners->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.delivery.partners.bulk-action') }}">
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
    .partner-checkbox:checked + td img {
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.3);
    }
</style>
@endpush

@push('scripts')
<script>
function showToast(message, type = 'info') {
    const title = type === 'success' ? 'Success' :
                  type === 'error' ? 'Error' :
                  type === 'warning' ? 'Warning' : 'Info';
    adminToast(type, title, message);
}

function adminToast(type, title, message) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }

    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    setTimeout(() => {
        toast.remove();
    }, 5000);
}

let selectedPartners = new Set();

// Toggle select all
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.partner-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedPartners.add(parseInt(cb.value));
        } else {
            selectedPartners.delete(parseInt(cb.value));
        }
    });
    updateBulkActions();
}

// Clear selection
function clearSelection() {
    selectedPartners.clear();
    const checkboxes = document.querySelectorAll('.partner-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateBulkActions();
}

// Update bulk actions bar visibility
function updateBulkActions() {
    const count = selectedPartners.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    
    // Update select all checkbox state
    const checkboxes = document.querySelectorAll('.partner-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    document.getElementById('selectAllCheckbox').checked = allChecked && checkboxes.length > 0;
}

// Perform bulk action
function bulkAction(action) {
    if (selectedPartners.size === 0) {
        showToast('Please select at least one partner.', 'warning');
        return;
    }
    
    let confirmMsg = '';
    switch(action) {
        case 'delete':
            confirmMsg = `Are you sure you want to delete ${selectedPartners.size} partner(s)?`;
            break;
        case 'activate':
            confirmMsg = `Activate ${selectedPartners.size} partner(s)?`;
            break;
        case 'deactivate':
            confirmMsg = `Deactivate ${selectedPartners.size} partner(s)?`;
            break;
        case 'feature':
            confirmMsg = `Mark ${selectedPartners.size} partner(s) as featured?`;
            break;
        case 'unfeature':
            confirmMsg = `Remove ${selectedPartners.size} partner(s) from featured?`;
            break;
        default:
            confirmMsg = `Apply this action to ${selectedPartners.size} partner(s)?`;
    }
    
    if (!confirm(confirmMsg)) return;
    
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedPartners));
    document.getElementById('bulkActionForm').submit();
}

// Toggle status via AJAX
function initStatusToggles() {
    document.querySelectorAll('.status-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`{{ route('admin.delivery.partners.toggle-status', ['partner' => 'ID']) }}`.replace('ID', id), {
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
                    updateStats();
                } else {
                    showToast(data.message || 'Failed to update status', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred while updating status', 'error');
            });
        });
    });
}

// Toggle featured via AJAX
function initFeaturedToggles() {
    document.querySelectorAll('.featured-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`{{ route('admin.delivery.partners.toggle-featured', ['partner' => 'ID']) }}`.replace('ID', id), {
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
                    updateStats();
                } else {
                    showToast(data.message || 'Failed to update featured status', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred while updating featured status', 'error');
            });
        });
    });
}

// Update stats via AJAX
function updateStats() {
    fetch(`{{ route('admin.delivery.partners.index') }}?ajax=1`, {
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
const filterSelects = ['filterStatus', 'filterServiceType', 'perPage'];
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
    
    const serviceType = document.getElementById('filterServiceType').value;
    if (serviceType) params.set('service_type', serviceType);
    
    const perPage = document.getElementById('perPage').value;
    params.set('per_page', perPage);
    
    // Keep existing sort
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
    if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
    
    // AJAX request
    fetch(`{{ route('admin.delivery.partners.index') }}?${params.toString()}&ajax=1`, {
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
        }
        
        // Update pagination
        if (data.pagination) {
            document.getElementById('paginationLinks').innerHTML = data.pagination;
        }
    });
}

// Initialize checkboxes
function initCheckboxes() {
    document.querySelectorAll('.partner-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                selectedPartners.add(parseInt(this.value));
            } else {
                selectedPartners.delete(parseInt(this.value));
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
