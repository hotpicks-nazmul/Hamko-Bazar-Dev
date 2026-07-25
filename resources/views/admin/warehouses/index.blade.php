@extends('admin.layouts.app')

@section('title', 'All Warehouses')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4" id="statsCards">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-building"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Warehouses</span>
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
        <div class="stat-card-icon"><i class="bi bi-geo-alt"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Cities</span>
            <span class="stat-card-value">{{ number_format($stats['cities'] ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0"><i class="bi bi-building me-2"></i>All Warehouses</h4>
        <small class="text-muted">Manage your warehouse locations, stock distribution centers</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Warehouse
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search -->
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Name, Code, City, Phone..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </span>
                    </div>
                </div>
                
                <!-- City Filter -->
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">City</label>
                    <select name="city" id="filterCity" class="form-select form-select-sm">
                        <option value="">All Cities</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-2 col-sm-4">
                    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-sm btn-outline-secondary w-100" id="resetFilters">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
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
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllWarehouses()">
                    Select All {{ $warehouses->total() }} Warehouses
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
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.warehouses.bulk-action') }}" style="display: none;">
    @csrf
    <input type="hidden" id="bulkActionInput" name="action" value="">
    <input type="hidden" id="bulkIdsInput" name="ids" value="">
</form>

<!-- Warehouses Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th>Warehouse</th>
                        <th>Location</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.warehouses.partials.table-rows', ['warehouses' => $warehouses])
                </tbody>
            </table>
        </div>
        
        <!-- Pagination & Per Page -->
        @if(isset($warehouses) && method_exists($warehouses, 'hasPages') && $warehouses->hasPages())
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
            <div id="paginationLinks">
                {{ $warehouses->appends(request()->query())->links() }}
            </div>
            <div class="text-muted small">
                Showing {{ $warehouses->firstItem() }} - {{ $warehouses->lastItem() }} of {{ $warehouses->total() }} warehouses
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Status Toggle Styles */
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
</style>
@endpush

@push('scripts')
<script>
let searchTimeout;
let selectedWarehouses = new Set();

// Bulk Selection Functions
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.warehouse-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedWarehouses.add(parseInt(cb.value));
        } else {
            selectedWarehouses.delete(parseInt(cb.value));
        }
    });
    updateBulkActions();
}

function selectAllWarehouses() {
    const checkboxes = document.querySelectorAll('.warehouse-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = true;
        selectedWarehouses.add(parseInt(cb.value));
    });
    updateBulkActions();
    document.getElementById('selectedCount').textContent = {{ $warehouses->total() ?? 0 }} + ' (all pages)';
}

function clearSelection() {
    selectedWarehouses.clear();
    const checkboxes = document.querySelectorAll('.warehouse-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateBulkActions();
}

function updateSelection(id, checked) {
    if (checked) {
        selectedWarehouses.add(id);
    } else {
        selectedWarehouses.delete(id);
    }
    updateBulkActions();
}

function updateBulkActions() {
    const count = selectedWarehouses.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
}

function bulkAction(action) {
    if (selectedWarehouses.size === 0) {
        toastr.warning('Please select at least one warehouse');
        return;
    }
    
    if (action === 'delete' && !confirm('Are you sure you want to delete selected warehouses?')) {
        return;
    }
    
    const form = document.getElementById('bulkActionForm');
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedWarehouses));
    form.submit();
}

// Live search with debouncing
const searchInput = document.getElementById('liveSearch');
const searchSpinner = document.getElementById('searchSpinner');

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
const filterSelects = ['filterCity', 'filterStatus'];
filterSelects.forEach(id => {
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
    const city = document.getElementById('filterCity').value;
    if (city) params.set('city', city);
    
    const status = document.getElementById('filterStatus').value;
    if (status) params.set('status', status);
    
    // Keep existing sort and per_page
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
    if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
    if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
    
    // AJAX request
    fetch(`{{ route('admin.warehouses.index') }}?${params.toString()}`, {
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
                updateStats(data.stats);
            }
            
            // Update pagination if available
            if (data.pagination) {
                document.getElementById('paginationLinks').innerHTML = data.pagination;
            }
            
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

// Update statistics cards
function updateStats(stats) {
    const statValues = document.querySelectorAll('#statsCards .stat-card-value');
    if (statValues[0]) statValues[0].textContent = stats.total ?? 0;
    if (statValues[1]) statValues[1].textContent = stats.active ?? 0;
    if (statValues[2]) statValues[2].textContent = stats.inactive ?? 0;
    if (statValues[3]) statValues[3].textContent = stats.cities ?? 0;
}

// Change per page
function changePerPage(perPage) {
    const params = new URLSearchParams(window.location.search);
    params.set('per_page', perPage);
    window.location.href = `${window.location.pathname}?${params.toString()}`;
}

// Toggle status
function toggleStatus(id) {
    fetch(`{{ route('admin.warehouses.toggle-status', ['id' => 'ID']) }}`.replace('ID', id), {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => {
                location.reload();
            }, 500);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        toastr.error('Failed to update status');
    });
}
</script>
@endpush
