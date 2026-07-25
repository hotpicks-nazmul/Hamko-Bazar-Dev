@extends('admin.layouts.app')

@section('title', 'Approved Refunds')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4" id="statsCards">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-receipt"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total</span>
            <span class="stat-card-value">{{ $stats['total'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Pending</span>
            <span class="stat-card-value">{{ $stats['pending'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Approved</span>
            <span class="stat-card-value">{{ $stats['approved'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Rejected</span>
            <span class="stat-card-value">{{ $stats['rejected'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Processed</span>
            <span class="stat-card-value">{{ $stats['processed'] ?? 0 }}</span>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Approved Refunds</h4>
    <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> All Refunds
    </a>
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
                               placeholder="Refund #, Order #, Customer..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                
                <!-- Reason Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Reason</label>
                    <select name="reason" id="filterReason" class="form-select form-select-sm">
                        <option value="">All Reasons</option>
                        @foreach(App\Models\Refund::getReasonOptions() as $value => $label)
                            <option value="{{ $value }}" {{ request('reason') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
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
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-4 col-sm-8">
                    <a href="{{ route('admin.refunds.approved') }}" class="btn btn-sm btn-outline-secondary">
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
                <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('process')">
                    <i class="bi bi-cash me-1"></i> Process
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('reject')">
                    <i class="bi bi-x-circle me-1"></i> Reject
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
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
                            <a href="{{ route('admin.refunds.approved', array_merge(request()->query(), ['sort' => 'refund_number', 'direction' => request('sort') == 'refund_number' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Refund Details
                                @if(request('sort') == 'refund_number')
                                    <i class="bi bi-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Order</th>
                        @if(auth()->user()->hasPermission('refund.view-customer'))
                        <th>Customer</th>
                        @endif
                        <th>Reason</th>
                        <th class="text-end">
                            <a href="{{ route('admin.refunds.approved', array_merge(request()->query(), ['sort' => 'refund_amount', 'direction' => request('sort') == 'refund_amount' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Amount
                                @if(request('sort') == 'refund_amount')
                                    <i class="bi bi-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.refunds.approved', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Status
                                @if(request('sort') == 'status')
                                    <i class="bi bi-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.refunds.approved', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Date
                                @if(request('sort') == 'created_at')
                                    <i class="bi bi-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.refunds.partials.table-rows', ['refunds' => $refunds])
                </tbody>
            </table>
        </div>
        
        <!-- Pagination inside card-body -->
        @if($refunds->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $refunds->firstItem() }} - {{ $refunds->lastItem() }} of {{ $refunds->total() }} items
            </div>
            <div>
                {{ $refunds->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Bulk selection
    let selectedItems = new Set();

    function updateBulkActions() {
        const count = selectedItems.size;
        const bulkBar = document.getElementById('bulkActionsBar');
        if (bulkBar) {
            const countSpan = document.getElementById('selectedCount');
            if (countSpan) countSpan.textContent = count;
            bulkBar.style.display = count > 0 ? 'block' : 'none';
        }
    }

    function toggleSelection(refundId, checkbox) {
        if (checkbox.checked) {
            selectedItems.add(refundId);
        } else {
            selectedItems.delete(refundId);
        }
        updateBulkActions();
    }

    function clearSelection() {
        selectedItems.clear();
        document.querySelectorAll('.refund-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('selectAllCheckbox');
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
        updateBulkActions();
    }

    function bulkAction(action) {
        if (selectedItems.size === 0) {
            alert('Please select at least one item.');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${selectedItems.size} refund(s)?`)) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('admin.refunds.bulk') }}`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
        form.appendChild(actionInput);
        
        const idsInput = document.createElement('input');
        idsInput.type = 'hidden';
        idsInput.name = 'ids';
        idsInput.value = JSON.stringify(Array.from(selectedItems));
        form.appendChild(idsInput);
        
        document.body.appendChild(form);
        form.submit();
    }

    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.refund-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                const refundId = cb.value;
                if (this.checked) {
                    selectedItems.add(refundId);
                } else {
                    selectedItems.delete(refundId);
                }
            });
            updateBulkActions();
        });
    }

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
    const filterSelects = ['filterReason', 'filterPerPage'];
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
        
        const reason = document.getElementById('filterReason')?.value;
        if (reason) params.set('reason', reason);
        
        const perPage = document.getElementById('filterPerPage')?.value;
        if (perPage) params.set('per_page', perPage);
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
        if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
        
        fetch(`{{ route('admin.refunds.approved') }}?${params.toString()}&ajax=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (searchSpinner) searchSpinner.style.display = 'none';
            
            if (data.html) {
                document.querySelector('#tableBody').innerHTML = data.html;
                
                if (data.pagination) {
                    const paginationContainer = document.querySelector('.card-footer div:last-child');
                    if (paginationContainer) {
                        paginationContainer.innerHTML = data.pagination;
                    }
                }
                
                // Update statistics cards
                if (data.stats) {
                    const statsContainer = document.querySelector('#statsCards');
                    if (statsContainer) {
                        statsContainer.innerHTML = `
                            <div class="stat-card stat-card-primary">
                                <div class="stat-card-icon"><i class="bi bi-receipt"></i></div>
                                <div class="stat-card-content">
                                    <span class="stat-card-label">Total</span>
                                    <span class="stat-card-value">${data.stats.total ?? 0}</span>
                                </div>
                            </div>
                            <div class="stat-card stat-card-warning">
                                <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
                                <div class="stat-card-content">
                                    <span class="stat-card-label">Pending</span>
                                    <span class="stat-card-value">${data.stats.pending ?? 0}</span>
                                </div>
                            </div>
                            <div class="stat-card stat-card-success">
                                <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
                                <div class="stat-card-content">
                                    <span class="stat-card-label">Approved</span>
                                    <span class="stat-card-value">${data.stats.approved ?? 0}</span>
                                </div>
                            </div>
                            <div class="stat-card stat-card-danger">
                                <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
                                <div class="stat-card-content">
                                    <span class="stat-card-label">Rejected</span>
                                    <span class="stat-card-value">${data.stats.rejected ?? 0}</span>
                                </div>
                            </div>
                            <div class="stat-card stat-card-info">
                                <div class="stat-card-icon"><i class="bi bi-cash-stack"></i></div>
                                <div class="stat-card-content">
                                    <span class="stat-card-label">Processed</span>
                                    <span class="stat-card-value">${data.stats.processed ?? 0}</span>
                                </div>
                            </div>
                        `;
                    }
                }
                
                // Reset bulk selection
                clearSelection();
                
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
        })
        .catch(error => {
            if (searchSpinner) searchSpinner.style.display = 'none';
            document.getElementById('filterForm').submit();
        });
    }
</script>
@endpush
