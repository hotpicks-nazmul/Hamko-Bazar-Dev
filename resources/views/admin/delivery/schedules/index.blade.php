@extends('admin.layouts.app')

@section('title', 'Delivery Schedules')

@section('content')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-calendar-week me-2"></i>Delivery Schedules</h4>
            <p class="text-muted mb-0">Configure scheduled delivery time slots</p>
        </div>
        <a href="{{ route('admin.delivery.schedules.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Schedule
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="stat-card-row mb-4">
        <div class="col">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-card-label">Total Schedules</span>
                    <span class="stat-card-value">{{ number_format($stats['total'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card stat-card-success">
                <div class="stat-card-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-card-label">Active</span>
                    <span class="stat-card-value">{{ number_format($stats['active'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card stat-card-info">
                <div class="stat-card-icon">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-card-label">Same Day</span>
                    <span class="stat-card-value">{{ number_format($stats['same_day'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card stat-card-warning">
                <div class="stat-card-icon">
                    <i class="bi bi-arrow-right-circle"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-card-label">Next Day</span>
                    <span class="stat-card-value">{{ number_format($stats['next_day'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card stat-card-danger">
                <div class="stat-card-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-card-label">Express</span>
                    <span class="stat-card-value">{{ number_format($stats['express'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card stat-card-secondary">
                <div class="stat-card-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-card-label">Scheduled</span>
                    <span class="stat-card-value">{{ number_format($stats['scheduled'] ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" id="filterForm">
                <div class="row g-2 align-items-end">
                    <!-- Search -->
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <label class="form-label small text-muted">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="liveSearch" class="form-control" 
                                   placeholder="Search by name..." value="{{ request('search') }}">
                            <span class="input-group-text" id="searchSpinner" style="display: none;">
                                <div class="spinner-border spinner-border-sm"></div>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Type Filter -->
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Type</label>
                        <select name="type" id="filterType" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="same_day" {{ request('type') === 'same_day' ? 'selected' : '' }}>Same Day</option>
                            <option value="next_day" {{ request('type') === 'next_day' ? 'selected' : '' }}>Next Day</option>
                            <option value="express" {{ request('type') === 'express' ? 'selected' : '' }}>Express</option>
                            <option value="scheduled" {{ request('type') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" id="filterStatus" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <!-- Per Page -->
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Per Page</label>
                        <select name="per_page" id="filterPerPage" class="form-select form-select-sm">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    
                    <!-- Reset -->
                    <div class="col-lg-2 col-md-4 col-sm-8">
                        <a href="{{ route('admin.delivery.schedules.index') }}" class="btn btn-sm btn-outline-secondary">
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
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted"><span id="selectedCount">0</span> selected</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="clearSelection()">
                        Clear Selection
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('activate')">
                        <i class="bi bi-check-circle me-1"></i> Activate
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('deactivate')">
                        <i class="bi bi-x-circle me-1"></i> Deactivate
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                            </th>
                            <th>
                                <a href="javascript:void(0)" class="text-decoration-none text-dark" onclick="sortBy('name')">
                                    Name
                                    @if(request('sort') == 'name')
                                        <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="javascript:void(0)" class="text-decoration-none text-dark" onclick="sortBy('type')">
                                    Type
                                    @if(request('sort') == 'type')
                                        <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="javascript:void(0)" class="text-decoration-none text-dark" onclick="sortBy('day_of_week')">
                                    Schedule
                                    @if(request('sort') == 'day_of_week')
                                        <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="javascript:void(0)" class="text-decoration-none text-dark" onclick="sortBy('start_time')">
                                    Time
                                    @if(request('sort') == 'start_time')
                                        <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="javascript:void(0)" class="text-decoration-none text-dark" onclick="sortBy('additional_fee')">
                                    Fee
                                    @if(request('sort') == 'additional_fee')
                                        <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="javascript:void(0)" class="text-decoration-none text-dark" onclick="sortBy('is_active')">
                                    Status
                                    @if(request('sort') == 'is_active')
                                        <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @include('admin.delivery.schedules.partials.table-rows', ['schedules' => $schedules])
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($schedules->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $schedules->firstItem() }} - {{ $schedules->lastItem() }} of {{ $schedules->total() }} schedules
                </div>
                <div>
                    {{ $schedules->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.delivery.schedules.bulk-action') }}" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ids" id="bulkIdsInput">
</form>

@push('styles')
<style>
.stat-card {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 20px 24px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #f0f0f0;
    transition: all 0.2s ease;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.stat-card-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 22px;
}
.stat-card-primary .stat-card-icon { background: #e8f4fd; color: #0d6efd; }
.stat-card-warning .stat-card-icon { background: #fff3cd; color: #ffc107; }
.stat-card-info .stat-card-icon { background: #cff4fc; color: #0dcaf0; }
.stat-card-success .stat-card-icon { background: #d1e7dd; color: #198754; }
.stat-card-danger .stat-card-icon { background: #f8d7da; color: #dc3545; }

.stat-card-content {
    display: flex;
    flex-direction: column;
    text-align: center;
}
.stat-card-label {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 2px;
}
.stat-card-value {
    font-size: 24px;
    font-weight: 700;
    color: #212529;
    line-height: 1.2;
}
</style>
@endpush

@push('scripts')
<script>
    let selectedItems = new Set();

    // Live Search
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            
            searchSpinner.style.display = 'block';
            
            searchTimeout = setTimeout(() => {
                performLiveSearch(searchTerm);
            }, 300);
        });
    }

    // Filter dropdowns trigger search on change
    const filterSelects = ['filterType', 'filterStatus', 'filterPerPage'];
    filterSelects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                performLiveSearch(searchInput ? searchInput.value.trim() : '');
            });
        }
    });

    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();
        
        if (searchTerm) params.set('search', searchTerm);
        
        const type = document.getElementById('filterType')?.value;
        if (type) params.set('type', type);
        
        const status = document.getElementById('filterStatus')?.value;
        if (status) params.set('status', status);
        
        const perPage = document.getElementById('filterPerPage')?.value;
        if (perPage) params.set('per_page', perPage);
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
        if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
        
        fetch(`{{ route('admin.delivery.schedules.index') }}?${params.toString()}`, {
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
                
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
        })
        .catch(err => {
            searchSpinner.style.display = 'none';
            console.error('Search error:', err);
            // Fallback to regular page load with proper URL parameters
            const searchTerm = searchInput ? searchInput.value.trim() : '';
            const params = new URLSearchParams();
            if (searchTerm) params.set('search', searchTerm);
            const type = document.getElementById('filterType')?.value;
            if (type) params.set('type', type);
            const status = document.getElementById('filterStatus')?.value;
            if (status) params.set('status', status);
            const perPage = document.getElementById('filterPerPage')?.value;
            if (perPage) params.set('per_page', perPage);
            window.location.href = `${window.location.pathname}?${params.toString()}`;
        });
    }

    // Sorting
    function sortBy(column) {
        const urlParams = new URLSearchParams(window.location.search);
        const currentSort = urlParams.get('sort');
        const currentDirection = urlParams.get('direction', 'desc');
        
        let newDirection = 'desc';
        if (currentSort === column && currentDirection === 'desc') {
            newDirection = 'asc';
        }
        
        const params = new URLSearchParams();
        
        const searchTerm = document.getElementById('liveSearch')?.value?.trim();
        if (searchTerm) params.set('search', searchTerm);
        
        const type = document.getElementById('filterType')?.value;
        if (type) params.set('type', type);
        
        const status = document.getElementById('filterStatus')?.value;
        if (status) params.set('status', status);
        
        const perPage = document.getElementById('filterPerPage')?.value;
        if (perPage) params.set('per_page', perPage);
        
        params.set('sort', column);
        params.set('direction', newDirection);
        
        // If AJAX is supported, use it
        fetch(`{{ route('admin.delivery.schedules.index') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.html) {
                document.querySelector('#tableBody').innerHTML = data.html;
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            } else {
                window.location.search = params.toString();
            }
        })
        .catch(() => {
            window.location.search = params.toString();
        });
    }

    // Select All
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            if (this.checked) {
                checkboxes.forEach(cb => {
                    selectedItems.add(parseInt(cb.value));
                    cb.checked = true;
                });
            } else {
                selectedItems.clear();
                checkboxes.forEach(cb => cb.checked = false);
            }
            updateBulkActions();
        });
    }

    // Individual checkbox
    function toggleItem(id) {
        id = parseInt(id);
        if (selectedItems.has(id)) {
            selectedItems.delete(id);
        } else {
            selectedItems.add(id);
        }
        updateBulkActions();
    }

    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
        
        // Update select all checkbox
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const totalCheckboxes = document.querySelectorAll('.item-checkbox').length;
        const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked').length;
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = count > 0 && count === totalCheckboxes;
            selectAllCheckbox.indeterminate = count > 0 && count < totalCheckboxes;
        }
    }

    function clearSelection() {
        selectedItems.clear();
        document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = false);
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
        updateBulkActions();
    }

    function bulkAction(action) {
        if (selectedItems.size === 0) {
            alert('Please select at least one schedule.');
            return;
        }
        
        const messages = {
            activate: 'activate',
            deactivate: 'deactivate',
            delete: 'delete'
        };
        
        if (!confirm(`Are you sure you want to ${messages[action]} ${selectedItems.size} schedule(s)?`)) return;
        
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
        document.getElementById('bulkActionForm').submit();
    }
</script>
@endpush
@endsection
