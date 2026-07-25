@extends('admin.layouts.app')

@section('title', 'Carriers')

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
    .carrier-checkbox:checked + td img {
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.3);
    }
</style>
@endpush

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-truck"></i></div>
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
    <h4 class="mb-0"><i class="bi bi-truck me-2"></i>Carriers</h4>
    <a href="{{ route('admin.delivery.carriers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Carrier
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
                
                <!-- Carrier Type -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Carrier Type</label>
                    <select name="carrier_type" id="filterCarrierType" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="international" {{ request('carrier_type') === 'international' ? 'selected' : '' }}>International</option>
                        <option value="regional" {{ request('carrier_type') === 'regional' ? 'selected' : '' }}>Regional</option>
                        <option value="local" {{ request('carrier_type') === 'local' ? 'selected' : '' }}>Local</option>
                        <option value="express" {{ request('carrier_type') === 'express' ? 'selected' : '' }}>Express</option>
                        <option value="freight" {{ request('carrier_type') === 'freight' ? 'selected' : '' }}>Freight</option>
                        <option value="all" {{ request('carrier_type') === 'all' ? 'selected' : '' }}>All Types</option>
                    </select>
                </div>
                
                <!-- Service Type -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Service Type</label>
                    <select name="service_type" id="filterServiceType" class="form-select form-select-sm">
                        <option value="">All Services</option>
                        <option value="express" {{ request('service_type') === 'express' ? 'selected' : '' }}>Express</option>
                        <option value="standard" {{ request('service_type') === 'standard' ? 'selected' : '' }}>Standard</option>
                        <option value="economy" {{ request('service_type') === 'economy' ? 'selected' : '' }}>Economy</option>
                        <option value="overnight" {{ request('service_type') === 'overnight' ? 'selected' : '' }}>Overnight</option>
                        <option value="international" {{ request('service_type') === 'international' ? 'selected' : '' }}>International</option>
                        <option value="freight" {{ request('service_type') === 'freight' ? 'selected' : '' }}>Freight</option>
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
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" id="resetFilters" onclick="resetFilters()">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </button>
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

<!-- Carriers Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="carriersTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th style="width: 60px;">Logo</th>
                        <th>
                            <a href="{{ route('admin.delivery.carriers.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Carrier Name
                                @if(request('sort') === 'name')
                                    <i class="bi bi-{{ request('direction') === 'asc' ? 'arrow-up' : 'arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 120px;">Type</th>
                        <th style="width: 120px;">Service</th>
                        <th style="width: 100px;">Base Rate</th>
                        <th style="width: 80px;">Tracking</th>
                        <th style="width: 80px;">COD</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 80px;">Featured</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($carriers as $carrier)
                        @include('admin.delivery.carriers.partials.table-row', ['carrier' => $carrier])
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <i class="bi bi-truck text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">No carriers found.</p>
                                <a href="{{ route('admin.delivery.carriers.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i> Add Your First Carrier
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($carriers) && method_exists($carriers, 'hasPages') && $carriers->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2" id="paginationContainer">
            <div class="text-muted small">
                Showing {{ $carriers->firstItem() }} - {{ $carriers->lastItem() }} of {{ $carriers->total() }} carriers
            </div>
            <div id="paginationLinks">
                {{ $carriers->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.delivery.carriers.bulk-action') }}">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ids" id="bulkIdsInput">
</form>
@endsection

@push('scripts')
<script>
let selectedCarriers = new Set();

// Toggle select all
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.carrier-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedCarriers.add(parseInt(cb.value));
        } else {
            selectedCarriers.delete(parseInt(cb.value));
        }
    });
    updateBulkActions();
}

// Clear selection
function clearSelection() {
    selectedCarriers.clear();
    const checkboxes = document.querySelectorAll('.carrier-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateBulkActions();
}

// Update bulk actions bar visibility
function updateBulkActions() {
    const count = selectedCarriers.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    
    // Update select all checkbox state
    const checkboxes = document.querySelectorAll('.carrier-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    document.getElementById('selectAllCheckbox').checked = allChecked && checkboxes.length > 0;
}

// Perform bulk action
function bulkAction(action) {
    if (selectedCarriers.size === 0) {
        showToast('Please select at least one carrier.', 'warning');
        return;
    }
    
    let confirmMsg = '';
    switch(action) {
        case 'delete':
            confirmMsg = `Are you sure you want to delete ${selectedCarriers.size} carrier(s)?`;
            break;
        case 'activate':
            confirmMsg = `Activate ${selectedCarriers.size} carrier(s)?`;
            break;
        case 'deactivate':
            confirmMsg = `Deactivate ${selectedCarriers.size} carrier(s)?`;
            break;
        case 'feature':
            confirmMsg = `Mark ${selectedCarriers.size} carrier(s) as featured?`;
            break;
        case 'unfeature':
            confirmMsg = `Remove ${selectedCarriers.size} carrier(s) from featured?`;
            break;
        default:
            confirmMsg = `Apply this action to ${selectedCarriers.size} carrier(s)?`;
    }
    
    if (!confirm(confirmMsg)) return;
    
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedCarriers));
    document.getElementById('bulkActionForm').submit();
}

// Toggle single carrier status
function toggleStatus(carrierId) {
    fetch(`{{ route('admin.delivery.carriers.toggle-status', ':id') }}`.replace(':id', carrierId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Update the status badge
            const statusBadge = document.getElementById(`status-badge-${carrierId}`);
            if (data.is_active) {
                statusBadge.className = 'badge bg-success';
                statusBadge.textContent = 'Active';
            } else {
                statusBadge.className = 'badge bg-secondary';
                statusBadge.textContent = 'Inactive';
            }
        }
    })
    .catch(err => {
        showToast('Error updating status', 'error');
    });
}

// Toggle featured status
function toggleFeatured(carrierId) {
    fetch(`{{ route('admin.delivery.carriers.toggle-featured', ':id') }}`.replace(':id', carrierId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Update the featured badge
            const featuredBadge = document.getElementById(`featured-badge-${carrierId}`);
            if (data.is_featured) {
                featuredBadge.className = 'badge bg-warning text-dark';
                featuredBadge.innerHTML = '<i class="bi bi-star-fill me-1"></i>Yes';
            } else {
                featuredBadge.className = 'badge bg-light text-dark';
                featuredBadge.textContent = 'No';
            }
        }
    })
    .catch(err => {
        showToast('Error updating featured status', 'error');
    });
}

// Live search
let searchTimeout;
const searchInput = document.getElementById('liveSearch');
const searchSpinner = document.getElementById('searchSpinner');

// Reset filters function
function resetFilters() {
    // Clear search input
    if (searchInput) searchInput.value = '';
    
    // Reset dropdowns
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterCarrierType').value = '';
    document.getElementById('filterServiceType').value = '';
    document.getElementById('perPage').value = '25';
    
    // Clear selection
    selectedCarriers.clear();
    updateBulkActions();
    
    // Reset URL and reload
    const baseUrl = window.location.pathname;
    window.history.pushState({}, '', baseUrl);
    
    // Trigger search with empty values
    performLiveSearch('');
}

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        
        // Show spinner
        searchSpinner.style.display = 'block';
        
        // Debounce - wait 300ms after user stops typing
        searchTimeout = setTimeout(() => {
            performLiveSearch(searchTerm);
        }, 300);
    });
}

// Filter dropdowns trigger search on change
const filterIds = ['filterStatus', 'filterCarrierType', 'filterServiceType', 'perPage'];
filterIds.forEach(id => {
    const select = document.getElementById(id);
    if (select) {
        select.addEventListener('change', function() {
            performLiveSearch(searchInput ? searchInput.value.trim() : '');
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
    
    const carrierType = document.getElementById('filterCarrierType').value;
    if (carrierType) params.set('carrier_type', carrierType);
    
    const serviceType = document.getElementById('filterServiceType').value;
    if (serviceType) params.set('service_type', serviceType);
    
    const perPage = document.getElementById('perPage').value;
    if (perPage) params.set('per_page', perPage);
    
    // Keep existing sort
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
    if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
    
    // AJAX request
    fetch(`{{ route('admin.delivery.carriers.index') }}?${params.toString()}`, {
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
            document.querySelector('#tableBody').innerHTML = data.html;
            
            // Update stats
            if (data.stats) {
                document.getElementById('stat-total').textContent = data.stats.total || 0;
                document.getElementById('stat-active').textContent = data.stats.active || 0;
                document.getElementById('stat-inactive').textContent = data.stats.inactive || 0;
                document.getElementById('stat-featured').textContent = data.stats.featured || 0;
            }
            
            // Update URL without reload
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({}, '', newUrl);
            
            // Reset selection
            selectedCarriers.clear();
            updateBulkActions();
        }
    })
    .catch(err => {
        searchSpinner.style.display = 'none';
        console.error('Search error:', err);
    });
}

// Delete carrier function
function deleteCarrier(carrierId) {
    if (!confirm('Are you sure you want to delete this carrier?')) return;
    
    fetch(`{{ route('admin.delivery.carriers.destroy', ':id') }}`.replace(':id', carrierId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Carrier deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Error deleting carrier', 'error');
        }
    })
    .catch(err => {
        showToast('Error deleting carrier', 'error');
    });
}

// Toast notification function
function showToast(message, type = 'info') {
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
</script>
@endpush
