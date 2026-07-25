@extends('admin.layouts.app')

@section('title', 'Shipment Tracking')

@push('styles')
<style>
    .tracking-timeline {
        position: relative;
        padding-left: 30px;
    }
    .tracking-timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    .tracking-item {
        position: relative;
        padding-bottom: 20px;
    }
    .tracking-item:last-child {
        padding-bottom: 0;
    }
    .tracking-item::before {
        content: '';
        position: absolute;
        left: -26px;
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #667eea;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #667eea;
    }
    .tracking-item.completed::before {
        background: #10b981;
        box-shadow: 0 0 0 2px #10b981;
    }
    .tracking-item.current::before {
        background: #667eea;
        box-shadow: 0 0 0 2px #667eea;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 2px #667eea; }
        50% { box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.3); }
        100% { box-shadow: 0 0 0 2px #667eea; }
    }
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-geo-alt me-2"></i>Shipment Tracking</h4>
            <p class="text-muted mb-0">Track and manage all shipments in real-time</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-box-seam"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Shipments</span>
            <span class="stat-card-value" id="stat-total">{{ $stats['total'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Pending</span>
            <span class="stat-card-value" id="stat-pending">{{ $stats['pending'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-truck"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">In Transit</span>
            <span class="stat-card-value" id="stat-in_transit">{{ $stats['in_transit'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Delivered</span>
            <span class="stat-card-value" id="stat-delivered">{{ $stats['delivered'] ?? 0 }}</span>
        </div>
    </div>
</div>

    <!-- Filter Form -->
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
                                placeholder="Order #, Tracking #, Phone" value="{{ request('search') }}">
                            <span class="input-group-text" id="searchSpinner" style="display: none;">
                                <div class="spinner-border spinner-border-sm"></div>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" id="filterStatus" class="form-select form-select-sm">
                            <option value="all">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    
                    <!-- Carrier Filter -->
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Carrier</label>
                        <select name="carrier" id="filterCarrier" class="form-select form-select-sm">
                            <option value="">All Carriers</option>
                            @foreach($carriers ?? [] as $carrier)
                                <option value="{{ $carrier->name }}" {{ request('carrier') == $carrier->name ? 'selected' : '' }}>{{ $carrier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Payment Status Filter -->
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Payment</label>
                        <select name="payment_status" id="filterPaymentStatus" class="form-select form-select-sm">
                            <option value="all">All Payment</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    
                    <!-- Date Range Filter -->
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Date Range</label>
                        <select name="date_range" id="filterDateRange" class="form-select form-select-sm">
                            <option value="">All Time</option>
                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="last_7_days" {{ request('date_range') == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="last_30_days" {{ request('date_range') == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        </select>
                    </div>
                    
                    <!-- Reset Button -->
                    <div class="col-lg-1 col-md-2 col-sm-6">
                        <a href="{{ route('admin.delivery.tracking') }}" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-x-lg me-1"></i>Reset
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
                    <button type="button" class="btn btn-sm btn-primary" onclick="bulkStatusUpdate('shipped')">
                        <i class="bi bi-truck me-1"></i>Mark Shipped
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="bulkStatusUpdate('delivered')">
                        <i class="bi bi-check-circle me-1"></i>Mark Delivered
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('cancelled')">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Shipments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">All Shipments</h6>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show</span>
                <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 || !request('per_page') ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                                </div>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'order_number', 'direction' => request('direction') == 'asc' && request('sort') == 'order_number' ? 'desc' : 'asc']) }}" class="text-dark text-decoration-none">
                                    Order #
                                    @if(request('sort') == 'order_number')
                                        <i class="bi bi-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Customer</th>
                            <th>Tracking #</th>
                            <th>Carrier</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('direction') == 'asc' && request('sort') == 'status' ? 'desc' : 'asc']) }}" class="text-dark text-decoration-none">
                                    Status
                                    @if(request('sort') == 'status')
                                        <i class="bi bi-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Payment</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @include('admin.delivery.partials.tracking-table-rows', ['shipments' => $shipments])
                    </tbody>
                </table>
            </div>
            
            @if($shipments->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $shipments->firstItem() ?? 0 }} to {{ $shipments->lastItem() ?? 0 }} of {{ $shipments->total() }} entries
                </div>
                <div>
                    {{ $shipments->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ids" id="bulkIdsInput">
</form>

@push('scripts')
<script>
    let selectedItems = new Set();

    // Debounced live search
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
    const filterSelects = ['filterStatus', 'filterPaymentStatus', 'filterDateRange', 'filterCarrier'];
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
        if (status && status !== 'all') params.set('status', status);
        
        const carrier = document.getElementById('filterCarrier').value;
        if (carrier) params.set('carrier', carrier);
        
        const paymentStatus = document.getElementById('filterPaymentStatus').value;
        if (paymentStatus && paymentStatus !== 'all') params.set('payment_status', paymentStatus);
        
        const dateRange = document.getElementById('filterDateRange').value;
        if (dateRange) params.set('date_range', dateRange);
        
        // Keep existing sort and per_page
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
        if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
        
        // AJAX request
        fetch(`{{ route('admin.delivery.tracking') }}?${params.toString()}&ajax=1`, {
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
                
                // Update URL without reload
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
            
            // Update stats if provided
            if (data.stats) {
                updateStatsCards(data.stats);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            searchSpinner.style.display = 'none';
        });
    }

    // Change per page
    function changePerPage(value) {
        const params = new URLSearchParams(window.location.search);
        params.set('per_page', value);
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    // Bulk selection functions
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.shipment-checkbox');
        
        if (selectAll.checked) {
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                selectedItems.add(checkbox.value);
            });
        } else {
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            selectedItems.clear();
        }
        
        updateBulkActions();
    }

    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    }
    
    // Update stats cards after AJAX filtering
    function updateStatsCards(stats) {
        if (stats.total !== undefined) {
            const totalEl = document.getElementById('stat-total');
            if (totalEl) totalEl.textContent = stats.total;
        }
        if (stats.pending !== undefined) {
            const pendingEl = document.getElementById('stat-pending');
            if (pendingEl) pendingEl.textContent = stats.pending;
        }
        if (stats.in_transit !== undefined) {
            const inTransitEl = document.getElementById('stat-in_transit');
            if (inTransitEl) inTransitEl.textContent = stats.in_transit;
        }
        if (stats.delivered !== undefined) {
            const deliveredEl = document.getElementById('stat-delivered');
            if (deliveredEl) deliveredEl.textContent = stats.delivered;
        }
    }

    function clearSelection() {
        selectedItems.clear();
        document.getElementById('selectAll').checked = false;
        document.querySelectorAll('.shipment-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateBulkActions();
    }

    // Bulk action function - batch update
    function bulkStatusUpdate(status) {
        if (selectedItems.size === 0) {
            alert('Please select at least one shipment.');
            return;
        }
        
        if (!confirm(`Are you sure you want to update ${selectedItems.size} shipment(s) to ${status}?`)) return;
        
        // Use batch API instead of individual calls
        fetch('{{ route('admin.delivery.tracking.bulk-update-status') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                status: status,
                ids: Array.from(selectedItems)
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                clearSelection();
                performLiveSearch(searchInput ? searchInput.value.trim() : '');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update shipments. Please try again.');
        });
    }

    function bulkAction(action) {
        if (selectedItems.size === 0) {
            alert('Please select at least one shipment.');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${selectedItems.size} shipment(s)?`)) return;
        
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
        document.getElementById('bulkActionForm').action = '{{ route('admin.delivery.tracking.bulk-action') }}';
        document.getElementById('bulkActionForm').submit();
    }

    // Generate tracking number
    function generateTrackingNumber(orderId) {
        if (!confirm('Generate a tracking number for this order?')) return;
        
        fetch(`/admin/delivery/tracking/${orderId}/generate-number`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Tracking number generated: ' + data.tracking_number);
                location.reload();
            }
        });
    }

    // Update status
    function updateStatus(event, orderId) {
        event.preventDefault();
        
        const form = document.getElementById('statusForm' + orderId);
        const formData = new FormData(form);
        
        fetch(`/admin/delivery/tracking/${orderId}/update-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Status updated successfully!');
                location.reload();
            }
        });
    }

    // Add click handler for checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('shipment-checkbox')) {
            if (e.target.checked) {
                selectedItems.add(e.target.value);
            } else {
                selectedItems.delete(e.target.value);
            }
            updateBulkActions();
        }
    });
</script>
@endpush
@endsection
