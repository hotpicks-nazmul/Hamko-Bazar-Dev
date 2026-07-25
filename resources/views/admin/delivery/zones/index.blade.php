@extends('admin.layouts.app')

@section('title', 'Delivery Zones')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-geo-alt"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Zones</span>
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
            <span class="stat-card-label">Default</span>
            <span class="stat-card-value" id="stat-default">{{ $stats['default'] ?? 0 }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-map me-2"></i>Delivery Zones</h4>
    <a href="{{ route('admin.delivery.zones.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Zone
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
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Name, region, city..." value="{{ request('search') }}">
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
                
                <!-- Area Type -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Area Type</label>
                    <select name="area_type" id="filterAreaType" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="nationwide" {{ request('area_type') === 'nationwide' ? 'selected' : '' }}>Nationwide</option>
                        <option value="regional" {{ request('area_type') === 'regional' ? 'selected' : '' }}>Regional</option>
                        <option value="city" {{ request('area_type') === 'city' ? 'selected' : '' }}>City</option>
                        <option value="district" {{ request('area_type') === 'district' ? 'selected' : '' }}>District</option>
                        <option value="thana" {{ request('area_type') === 'thana' ? 'selected' : '' }}>Thana/Upazila</option>
                        <option value="zone" {{ request('area_type') === 'zone' ? 'selected' : '' }}>Custom Zone</option>
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
                        <a href="{{ route('admin.delivery.zones.index') }}" class="btn btn-sm btn-outline-secondary flex-grow-1" id="resetFilters">
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
                <button type="button" class="btn btn-sm btn-info" onclick="bulkAction('set-default')">
                    <i class="bi bi-star me-1"></i> Set Default
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Zones Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>Zone Name</th>
                        <th>Area Type</th>
                        <th>Location</th>
                        <th>Shipping Cost</th>
                        <th>Est. Days</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.delivery.zones.partials.table-rows')
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($zones) && method_exists($zones, 'hasPages') && $zones->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $zones->firstItem() ?? 0 }} - {{ $zones->lastItem() ?? 0 }} of {{ $zones->total() }} zones
            </div>
            <div>
                {{ $zones->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Delete Form (hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    // Helper function to show alerts
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }
    
    // Live Search
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
    const filterSelects = ['filterStatus', 'filterAreaType', 'perPage'];
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
        
        const status = document.getElementById('filterStatus').value;
        if (status) params.set('status', status);
        
        const areaType = document.getElementById('filterAreaType').value;
        if (areaType) params.set('area_type', areaType);
        
        const perPage = document.getElementById('perPage').value;
        if (perPage) params.set('per_page', perPage);
        
        fetch(`{{ route('admin.delivery.zones.index') }}?${params.toString()}`, {
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
                
                // Update stats
                if (data.stats) {
                    document.getElementById('stat-total').textContent = data.stats.total;
                    document.getElementById('stat-active').textContent = data.stats.active;
                    document.getElementById('stat-inactive').textContent = data.stats.inactive;
                    document.getElementById('stat-default').textContent = data.stats.default;
                }
                
                // Update URL without reload
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
                
                // Re-attach event listeners
                attachEventListeners();
            }
        })
        .catch(error => {
            searchSpinner.style.display = 'none';
            console.error('Search error:', error);
            showAlert('danger', 'Failed to load data. Please try again.');
        });
    }

    // Select All
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.zone-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionsBar();
    });

    // Individual checkbox change
    function attachEventListeners() {
        document.querySelectorAll('.zone-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActionsBar);
        });
        
        // Toggle status
        document.querySelectorAll('.toggle-status').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        performLiveSearch(searchInput.value.trim());
                    }
                });
            });
        });
        
        // Toggle default
        document.querySelectorAll('.toggle-default').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        performLiveSearch(searchInput.value.trim());
                    }
                });
            });
        });
    }
    
    // Initial attach
    attachEventListeners();

    function updateBulkActionsBar() {
        const checkboxes = document.querySelectorAll('.zone-checkbox:checked');
        const bulkBar = document.getElementById('bulkActionsBar');
        const countSpan = document.getElementById('selectedCount');
        
        if (checkboxes.length > 0) {
            bulkBar.style.display = 'block';
            countSpan.textContent = checkboxes.length;
        } else {
            bulkBar.style.display = 'none';
        }
        
        // Update select all checkbox state
        const selectAll = document.getElementById('selectAll');
        const totalCheckboxes = document.querySelectorAll('.zone-checkbox').length;
        selectAll.checked = checkboxes.length === totalCheckboxes && totalCheckboxes > 0;
    }

    function clearSelection() {
        document.querySelectorAll('.zone-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('selectAll').checked = false;
        updateBulkActionsBar();
    }

    function bulkAction(action) {
        const checkboxes = document.querySelectorAll('.zone-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value);
        
        if (ids.length === 0) return;
        
        if (action === 'delete' && !confirm('Are you sure you want to delete selected zones?')) {
            return;
        }
        
        fetch('{{ route("admin.delivery.zones.bulk-action") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action, ids })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                clearSelection();
                performLiveSearch(searchInput.value.trim());
                showAlert('success', data.message);
            }
        })
        .catch(error => {
            console.error('Bulk action error:', error);
            showAlert('danger', 'Failed to perform bulk action. Please try again.');
        });
    }
</script>
@endpush
