@extends('admin.layouts.app')

@section('title', 'Affiliate Reports')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Affiliate Reports</h4>
    <a href="{{ route('admin.affiliate.reports.export', request()->query()) }}" class="btn btn-primary">
        <i class="bi bi-download me-1"></i>Export Report
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-people"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Affiliates</span>
            <span class="stat-card-value" id="statTotalAffiliates">{{ number_format($stats['total_affiliates']) }}</span>
        </div>
    </div>
    @if(auth()->user()->hasPermission('view-revenue'))
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Sales</span>
            <span class="stat-card-value" id="statTotalSales">${{ number_format($stats['total_sales'], 2) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-graph-up"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Commissions</span>
            <span class="stat-card-value" id="statTotalCommissions">${{ number_format($stats['total_commissions'], 2) }}</span>
        </div>
    </div>
    @endif
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-cursor"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Clicks</span>
            <span class="stat-card-value" id="statTotalClicks">{{ number_format($stats['total_clicks']) }}</span>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search Input -->
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Affiliate code, name, email..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <a href="{{ route('admin.affiliate.reports') }}" class="btn btn-sm btn-outline-secondary">
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
                <button type="button" class="btn btn-sm btn-primary" onclick="bulkExport()">
                    <i class="bi bi-download me-1"></i> Export Selected
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Affiliate Performance Report</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="affiliateReportsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                        </th>
                        <th>ID</th>
                        <th>Affiliate</th>
                        <th>Code</th>
                        <th>Clicks</th>
                        <th>Sales</th>
                        @if(auth()->user()->hasPermission('view-revenue'))
                        <th>Total Sales</th>
                        <th>Commission</th>
                        @endif
                        <th style="width: 100px;">Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.affiliate.reports.partials.table-rows', ['affiliates' => $affiliates])
                </tbody>
            </table>
        </div>
        
        @if($affiliates->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show:</span>
                <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('per_page') == 15 || !request('per_page') ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
                <span class="text-muted small">per page</span>
            </div>
            <div>
                {{ $affiliates->links() }}
            </div>
            <div class="text-muted small">
                Showing {{ $affiliates->firstItem() }} - {{ $affiliates->lastItem() }} of {{ $affiliates->total() }} affiliates
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
/* Search highlighting */
.table-warning {
    --bs-table-bg: #fff3cd;
}
</style>
@endpush

@push('scripts')
<script>
let selectedItems = new Set();
let searchTimeout;

// Format number consistently (matches PHP number_format)
function formatNumber(num, decimals = 0) {
    if (num === null || num === undefined) return '0';
    const parsed = parseFloat(num);
    if (isNaN(parsed)) return '0';
    return parsed.toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    document.getElementById('selectAllCheckbox')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.affiliate-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
            if (this.checked) {
                selectedItems.add(cb.value);
            } else {
                selectedItems.delete(cb.value);
            }
        });
        updateBulkActions();
    });
    
    // Live search
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            
            // Show spinner
            if (searchSpinner) searchSpinner.style.display = 'block';
            
            // Debounce - wait 300ms after user stops typing
            searchTimeout = setTimeout(() => {
                performLiveSearch(searchTerm);
            }, 300);
        });
    }
    
    // Filter dropdowns trigger search on change
    const filterStatus = document.getElementById('filterStatus');
    if (filterStatus) {
        filterStatus.addEventListener('change', function() {
            const searchTerm = document.getElementById('liveSearch').value.trim();
            performLiveSearch(searchTerm);
        });
    }
    
    // Initialize checkbox listeners
    initCheckboxListeners();
});

// Live search function
function performLiveSearch(searchTerm) {
    const params = new URLSearchParams();
    
    if (searchTerm) params.set('search', searchTerm);
    
    // Add filter values
    const status = document.getElementById('filterStatus').value;
    if (status) params.set('status', status);
    
    // Keep existing sort and per_page
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
    if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
    if (urlParams.get('page')) params.set('page', urlParams.get('page'));
    
    // AJAX request
    fetch(`{{ route('admin.affiliate.reports') }}?${params.toString()}&ajax=1`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const searchSpinner = document.getElementById('searchSpinner');
        if (searchSpinner) searchSpinner.style.display = 'none';
        
        if (data.html) {
            // Update table body
            document.getElementById('tableBody').innerHTML = data.html;
            
            // Update stats with consistent formatting
            if (data.stats) {
                document.getElementById('statTotalAffiliates').textContent = formatNumber(data.stats.total_affiliates);
                @if(auth()->user()->hasPermission('view-revenue'))
                document.getElementById('statTotalSales').textContent = '$' + formatNumber(data.stats.total_sales, 2);
                document.getElementById('statTotalCommissions').textContent = '$' + formatNumber(data.stats.total_commissions, 2);
                @endif
                document.getElementById('statTotalClicks').textContent = formatNumber(data.stats.total_clicks);
            }
            
            // Update URL without reload
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({}, '', newUrl);
            
            // Reinitialize checkbox listeners
            initCheckboxListeners();
            
            // Reinitialize select all checkbox
            initSelectAllCheckbox();
            selectedItems.clear();
            updateBulkActions();
        }
    })
    .catch(error => {
        const searchSpinner = document.getElementById('searchSpinner');
        if (searchSpinner) searchSpinner.style.display = 'none';
        console.error('Search error:', error);
    });
}

// Initialize checkbox listeners
function initCheckboxListeners() {
    const checkboxes = document.querySelectorAll('.affiliate-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                selectedItems.add(this.value);
            } else {
                selectedItems.delete(this.value);
            }
            updateSelectAllCheckbox();
            updateBulkActions();
        });
    });
}

// Update select all checkbox state
function initSelectAllCheckbox() {
    const selectAll = document.getElementById('selectAllCheckbox');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.affiliate-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                if (this.checked) {
                    selectedItems.add(cb.value);
                } else {
                    selectedItems.delete(cb.value);
                }
            });
            updateBulkActions();
        });
    }
}

function updateSelectAllCheckbox() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.affiliate-checkbox');
    const checkedCount = document.querySelectorAll('.affiliate-checkbox:checked').length;
    
    if (selectAll) {
        selectAll.checked = checkedCount > 0 && checkedCount === checkboxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
    }
}

// Update bulk actions bar visibility
function updateBulkActions() {
    const count = selectedItems.size;
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    
    if (bulkBar) {
        bulkBar.style.display = count > 0 ? 'block' : 'none';
    }
    if (selectedCount) {
        selectedCount.textContent = count;
    }
}

// Clear selection
function clearSelection() {
    selectedItems.clear();
    const checkboxes = document.querySelectorAll('.affiliate-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('selectAllCheckbox');
    if (selectAll) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    }
    updateBulkActions();
}

// Bulk export
function bulkExport() {
    if (selectedItems.size === 0) {
        alert('Please select at least one affiliate to export.');
        return;
    }
    
    // Build URL with selected IDs
    const params = new URLSearchParams();
    selectedItems.forEach(id => params.append('ids[]', id));
    
    // Redirect to export with selected IDs
    window.location.href = `{{ route('admin.affiliate.reports.export') }}?${params.toString()}`;
}

// Change per page
function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
</script>
@endpush
