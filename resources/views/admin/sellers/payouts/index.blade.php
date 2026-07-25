@extends('admin.layouts.app')

@section('title', 'Seller Payouts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Seller Payouts</h4>
    <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Sellers
    </a>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row stat-card-row-6 mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Payouts</span>
            <span class="stat-card-value" id="statTotalPayouts">{{ $stats['total_payouts'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Amount</span>
            <span class="stat-card-value" id="statTotalAmount">৳{{ number_format($stats['total_amount'] ?? 0, 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Pending</span>
            <span class="stat-card-value" id="statPending">{{ $stats['pending'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Pending Amount</span>
            <span class="stat-card-value" id="statPendingAmount">৳{{ number_format($stats['pending_amount'] ?? 0, 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Completed</span>
            <span class="stat-card-value" id="statCompleted">{{ $stats['completed'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Rejected</span>
            <span class="stat-card-value" id="statRejected">{{ $stats['rejected'] ?? 0 }}</span>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search Input -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Seller name, email, shop..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                
                <!-- Payment Method Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Payment Method</label>
                    <select name="payment_method" id="filterPaymentMethod" class="form-select form-select-sm">
                        <option value="">All Methods</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="mobile_banking" {{ request('payment_method') === 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                        <option value="cheque" {{ request('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                    </select>
                </div>
                
                <!-- Date From -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Date From</label>
                    <input type="date" name="date_from" class="form-control form-select-sm" value="{{ request('date_from') }}">
                </div>
                
                <!-- Date To -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Date To</label>
                    <input type="date" name="date_to" class="form-control form-select-sm" value="{{ request('date_to') }}">
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-1 col-md-4 col-sm-6">
                    <a href="{{ route('admin.sellers.payouts') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Seller</th>
                        <th>Amount</th>
                        <th>Commission</th>
                        <th>Net Amount</th>
                        <th>Payment Method</th>
                        <th>Transaction ID</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.sellers.payouts.partials.table-rows')
                </tbody>
            </table>
        </div>
        
        <!-- Pagination inside card-body -->
        @if($payouts->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2" id="paginationFooter">
            <div class="text-muted small" id="paginationInfo">
                Showing {{ $payouts->firstItem() }} - {{ $payouts->lastItem() }} of {{ $payouts->total() }} payouts
            </div>
            <div id="paginationLinks">
                {{ $payouts->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Force Bootstrap Icons to display - SAME AS REFERENCE PAGE */
    .stat-card-icon i,
    .stat-card-icon i::before,
    .bi::before,
    [class*="bi bi-"]::before {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-family: 'bootstrap-icons' !important;
    }
    
    /* Override icon colors for stat cards */
    .stat-card-primary .stat-card-icon i::before { color: #0d6efd !important; }
    .stat-card-success .stat-card-icon i::before { color: #198754 !important; }
    .stat-card-info .stat-card-icon i::before { color: #0dcaf0 !important; }
    .stat-card-warning .stat-card-icon i::before { color: #ffc107 !important; }
    .stat-card-danger .stat-card-icon i::before { color: #dc3545 !important; }
    .stat-card-secondary .stat-card-icon i::before { color: #6c757d !important; }
    
    /* Make the whole icon colored */
    .stat-card-icon i { color: inherit !important; }
</style>
@endpush

@push('scripts')
<script>
    // Debounced live search
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        
        // Show spinner
        searchSpinner.style.display = 'block';
        
        // Debounce - wait 400ms after user stops typing
        searchTimeout = setTimeout(() => {
            performLiveSearch(searchTerm);
        }, 400);
    });

    // Filter dropdowns trigger search on change
    const filterSelects = ['filterStatus', 'filterPaymentMethod'];
    filterSelects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                performLiveSearch(searchInput.value.trim());
            });
        }
    });

    // Date inputs trigger search on change
    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.addEventListener('change', function() {
            performLiveSearch(searchInput.value.trim());
        });
    });

    // Live search function
    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();
        
        if (searchTerm) params.set('search', searchTerm);
        
        // Add filter values
        const status = document.getElementById('filterStatus').value;
        if (status) params.set('status', status);
        
        const paymentMethod = document.getElementById('filterPaymentMethod').value;
        if (paymentMethod) params.set('payment_method', paymentMethod);
        
        // Date filters
        const dateFrom = document.querySelector('input[name="date_from"]').value;
        if (dateFrom) params.set('date_from', dateFrom);
        
        const dateTo = document.querySelector('input[name="date_to"]').value;
        if (dateTo) params.set('date_to', dateTo);
        
        // Keep existing sort and per_page
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sort_by')) params.set('sort_by', urlParams.get('sort_by'));
        if (urlParams.get('sort_order')) params.set('sort_order', urlParams.get('sort_order'));
        if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
        
        // AJAX request
        fetch(`{{ route('admin.sellers.payouts') }}?${params.toString()}`, {
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
                
                // Update pagination info
                if (data.pagination_info) {
                    document.getElementById('paginationInfo').innerHTML = data.pagination_info;
                }
                
                // Update pagination links
                if (data.pagination) {
                    document.getElementById('paginationLinks').innerHTML = data.pagination;
                }
                
                // Update URL without reload
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
        })
        .catch(error => {
            searchSpinner.style.display = 'none';
            console.error('Search error:', error);
        });
    }
</script>
@endpush
