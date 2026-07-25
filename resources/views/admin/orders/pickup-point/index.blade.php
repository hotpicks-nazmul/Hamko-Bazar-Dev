@extends('admin.layouts.app')

@section('title', 'Pick-up Point Orders')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4" id="statsCards">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-cart-fill"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Total Orders</span><span class="stat-card-value">{{ $stats['total'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Pending</span><span class="stat-card-value">{{ $stats['pending'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-gear"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Processing</span><span class="stat-card-value">{{ $stats['processing'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-box-seam"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Ready</span><span class="stat-card-value">{{ $stats['ready'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check2-all"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Picked Up</span><span class="stat-card-value">{{ $stats['picked_up'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Cancelled</span><span class="stat-card-value">{{ $stats['cancelled'] ?? 0 }}</span></div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Pick-up Point Orders</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.pickup-point') }}?{{ http_build_query(array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Export CSV
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
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Order #, Name, Email, Phone..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </span>
                    </div>
                </div>
                
                <!-- Order Status -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Order Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Ready for Pickup</option>
                        <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                
                <!-- Payment Status -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Payment Status</label>
                    <select name="payment_status" id="filterPaymentStatus" class="form-select form-select-sm">
                        <option value="">All Payment</option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>

                <!-- Pickup Point Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Pick-up Point</label>
                    <select name="pickup_point_id" id="filterPickupPoint" class="form-select form-select-sm">
                        <option value="">All Locations</option>
                        @foreach($pickupPoints as $point)
                            <option value="{{ $point->id }}" {{ request('pickup_point_id') == $point->id ? 'selected' : '' }}>
                                {{ $point->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Date From -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Date From</label>
                    <input type="date" name="date_from" id="filterDateFrom" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                
                <!-- Date To -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Date To</label>
                    <input type="date" name="date_to" id="filterDateTo" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-1 col-md-2 col-sm-4">
                    <a href="{{ route('admin.orders.pickup-point') }}" class="btn btn-sm btn-outline-secondary w-100" id="resetFilters">
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
                <button type="button" class="btn btn-sm btn-primary" onclick="bulkStatusUpdate('processing')">
                    <i class="bi bi-arrow-repeat me-1"></i> Mark Processing
                </button>
                <button type="button" class="btn btn-sm btn-success" onclick="bulkStatusUpdate('confirmed')">
                    <i class="bi bi-check-circle me-1"></i> Mark Ready
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkStatusUpdate('cancelled')">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
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
                            <a href="{{ route('admin.orders.pickup-point', array_merge(request()->query(), ['sort' => 'order_number', 'direction' => request('sort') == 'order_number' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Order #
                                @if(request('sort') == 'order_number')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        @if(auth()->user()->hasPermission('orders.view-customer'))
                        <th>Customer</th>
                        @endif
                        <th>Pick-up Point</th>
                        @if(auth()->user()->hasPermission('orders.view-pricing'))
                        <th>
                            <a href="{{ route('admin.orders.pickup-point', array_merge(request()->query(), ['sort' => 'total', 'direction' => request('sort') == 'total' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Total
                                @if(request('sort') == 'total')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        @endif
                        <th>Payment</th>
                        <th>
                            <a href="{{ route('admin.orders.pickup-point', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Status
                                @if(request('sort') == 'status')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.orders.pickup-point', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Date
                                @if(request('sort') == 'created_at' || !request('sort'))
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody">
                    @include('admin.orders.partials.pickup-point-order-rows', ['orders' => $orders])
                </tbody>
            </table>
        </div>
        
        <!-- Pagination & Per Page -->
        @if(isset($orders) && method_exists($orders, 'hasPages') && $orders->hasPages())
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
                {{ $orders->appends(request()->query())->links() }}
            </div>
            <div class="text-muted small">
                Showing {{ $orders->firstItem() }} - {{ $orders->lastItem() }} of {{ $orders->total() }} orders
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Fix delete button border-radius to match btn-group siblings */
    .btn-group .btn-outline-danger {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
    .btn-group .btn-outline-success {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
    .table > :not(caption) > * > * {
        padding: 0.75rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
let searchTimeout;

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
const filterSelects = ['filterStatus', 'filterPaymentStatus', 'filterPickupPoint', 'filterDateFrom', 'filterDateTo'];
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
    const status = document.getElementById('filterStatus').value;
    if (status) params.set('status', status);
    
    const paymentStatus = document.getElementById('filterPaymentStatus').value;
    if (paymentStatus) params.set('payment_status', paymentStatus);

    const pickupPoint = document.getElementById('filterPickupPoint').value;
    if (pickupPoint) params.set('pickup_point_id', pickupPoint);
    
    const dateFrom = document.getElementById('filterDateFrom').value;
    if (dateFrom) params.set('date_from', dateFrom);
    
    const dateTo = document.getElementById('filterDateTo').value;
    if (dateTo) params.set('date_to', dateTo);
    
    // Keep existing sort and per_page
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
    if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
    if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
    
    // AJAX request
    fetch(`{{ route('admin.orders.pickup-point') }}?${params.toString()}`, {
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
            document.querySelector('#orderTableBody').innerHTML = data.html;
            
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
    const statsContainer = document.getElementById('statsCards');
    if (!statsContainer) return;

    const statMappings = {
        'Total Orders': stats.total ?? 0,
        'Pending': stats.pending ?? 0,
        'Processing': stats.processing ?? 0,
        'Ready': stats.ready ?? 0,
        'Picked Up': stats.picked_up ?? 0,
        'Cancelled': stats.cancelled ?? 0,
    };

    const statCards = statsContainer.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        const labelEl = card.querySelector('.stat-card-label');
        const valueEl = card.querySelector('.stat-card-value');
        if (labelEl && valueEl) {
            const labelText = labelEl.textContent.trim();
            if (statMappings[labelText] !== undefined) {
                valueEl.textContent = statMappings[labelText];
            }
        }
    });
}

// Change per page
function changePerPage(perPage) {
    const params = new URLSearchParams(window.location.search);
    params.set('per_page', perPage);
    params.delete('page'); // Reset to first page

    // Check if we're in AJAX mode
    if (document.querySelector('#orderTableBody')) {
        fetch(`{{ route('admin.orders.pickup-point') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.html) {
                document.querySelector('#orderTableBody').innerHTML = data.html;
                if (data.stats) updateStats(data.stats);
                if (data.pagination) document.getElementById('paginationLinks').innerHTML = data.pagination;
                window.history.pushState({}, '', `${window.location.pathname}?${params.toString()}`);
            }
        });
    } else {
        window.location.search = params.toString();
    }
}

// Bulk selection functionality
let selectedItems = new Set();

function updateBulkActions() {
    const count = selectedItems.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
}

function clearSelection() {
    selectedItems.clear();
    document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateBulkActions();
}

function bulkStatusUpdate(status) {
    if (selectedItems.size === 0) {
        alert('Please select at least one order.');
        return;
    }
    
    if (!confirm(`Are you sure you want to update ${selectedItems.size} order(s) to ${status}?`)) return;
    
    fetch('{{ route('admin.orders.bulk-status') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            order_ids: Array.from(selectedItems),
            status: status
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Bulk update error:', err);
        alert('An error occurred while updating orders.');
    });
}

// Select all checkbox
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.order-checkbox');
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
    
    // Individual checkbox change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('order-checkbox')) {
            if (e.target.checked) {
                selectedItems.add(e.target.value);
            } else {
                selectedItems.delete(e.target.value);
            }
            
            // Update select all checkbox
            const allCheckboxes = document.querySelectorAll('.order-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
            const selectAll = document.getElementById('selectAllCheckbox');
            if (selectAll) {
                selectAll.checked = allCheckboxes.length > 0 && checkedCheckboxes.length === allCheckboxes.length;
            }
            
            updateBulkActions();
        }
    });
});
</script>
@endpush
