@extends('admin.layouts.app')

@section('title', 'Seller Orders')

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
        <div class="stat-card-icon"><i class="bi bi-check2"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Confirmed</span><span class="stat-card-value">{{ $stats['confirmed'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-truck"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Shipped</span><span class="stat-card-value">{{ $stats['shipped'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check2-all"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Delivered</span><span class="stat-card-value">{{ $stats['delivered'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Cancelled</span><span class="stat-card-value">{{ $stats['cancelled'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-arrow-counterclockwise"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Refunded</span><span class="stat-card-value">{{ $stats['refunded'] ?? 0 }}</span></div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-people me-2"></i>Seller Orders</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.seller', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-secondary">
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
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
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

                <!-- Seller Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Seller</label>
                    <select name="seller_id" id="filterSeller" class="form-select form-select-sm">
                        <option value="">All Sellers</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->name }}
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
                    <a href="{{ route('admin.orders.seller') }}" class="btn btn-sm btn-outline-secondary w-100" id="resetFilters">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <a href="{{ route('admin.orders.seller', array_merge(request()->query(), ['sort' => 'order_number', 'direction' => request('sort') == 'order_number' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Order #
                                @if(request('sort') == 'order_number')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        @if(auth()->user()->hasPermission('orders.view-customer'))
                        <th>Customer</th>
                        @endif
                        <th>Seller(s)</th>
                        @if(auth()->user()->hasPermission('orders.view-pricing'))
                        <th>
                            <a href="{{ route('admin.orders.seller', array_merge(request()->query(), ['sort' => 'total', 'direction' => request('sort') == 'total' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Total
                                @if(request('sort') == 'total')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        @endif
                        <th>Payment</th>
                        <th>
                            <a href="{{ route('admin.orders.seller', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Status
                                @if(request('sort') == 'status')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.orders.seller', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
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
                    @include('admin.orders.partials.seller-order-rows', ['orders' => $orders])
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
const filterSelects = ['filterStatus', 'filterPaymentStatus', 'filterSeller', 'filterDateFrom', 'filterDateTo'];
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

    const sellerId = document.getElementById('filterSeller').value;
    if (sellerId) params.set('seller_id', sellerId);
    
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
    fetch(`{{ route('admin.orders.seller') }}?${params.toString()}`, {
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
    .catch(error => {
        console.error('Error:', error);
        searchSpinner.style.display = 'none';
        // Fallback to regular page load
        window.location.search = params.toString();
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
        'Confirmed': stats.confirmed ?? 0,
        'Shipped': stats.shipped ?? 0,
        'Delivered': stats.delivered ?? 0,
        'Cancelled': stats.cancelled ?? 0,
        'Refunded': stats.refunded ?? 0,
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
        fetch(`{{ route('admin.orders.seller') }}?${params.toString()}`, {
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
</script>
@endpush
