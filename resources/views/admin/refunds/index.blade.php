@extends('admin.layouts.app')

@section('title', 'All Refunds')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-receipt"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total</span>
            <span class="stat-card-value" id="statTotal">{{ $stats['total'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Pending</span>
            <span class="stat-card-value" id="statPending">{{ $stats['pending'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Approved</span>
            <span class="stat-card-value" id="statApproved">{{ $stats['approved'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Rejected</span>
            <span class="stat-card-value" id="statRejected">{{ $stats['rejected'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Processed</span>
            <span class="stat-card-value" id="statProcessed">{{ $stats['processed'] ?? 0 }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">All Refunds</h4>
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
                
                <!-- Status Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                    </select>
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
                    <a href="{{ route('admin.refunds.index') }}" class="btn btn-sm btn-outline-secondary">
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
                <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('approve')">
                    <i class="bi bi-check-circle me-1"></i> Approve
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
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th>
                            <a href="{{ route('admin.refunds.index', array_merge(request()->query(), ['sort' => 'refund_number', 'direction' => request('sort') == 'refund_number' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
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
                            <a href="{{ route('admin.refunds.index', array_merge(request()->query(), ['sort' => 'refund_amount', 'direction' => request('sort') == 'refund_amount' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Amount
                                @if(request('sort') == 'refund_amount')
                                    <i class="bi bi-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.refunds.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Status
                                @if(request('sort') == 'status')
                                    <i class="bi bi-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.refunds.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
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

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.refunds.bulk') }}">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ids" id="bulkIdsInput">
</form>

@endsection

@push('scripts')
<script>
    let selectedItems = new Set();

    // Toggle select all on current page
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.refund-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
            if (checkbox.checked) {
                selectedItems.add(cb.value);
            } else {
                selectedItems.delete(cb.value);
            }
        });
        updateBulkActions();
    }

    // Toggle single item selection
    function toggleSelection(id, checkbox) {
        if (checkbox.checked) {
            selectedItems.add(id);
        } else {
            selectedItems.delete(id);
        }
        updateBulkActions();
    }

    // Clear selection
    function clearSelection() {
        selectedItems.clear();
        document.getElementById('selectAllCheckbox').checked = false;
        document.querySelectorAll('.refund-checkbox').forEach(cb => cb.checked = false);
        updateBulkActions();
    }

    // Update bulk actions bar visibility
    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    }

    // Perform bulk action
    function bulkAction(action) {
        if (selectedItems.size === 0) {
            alert('Please select at least one refund.');
            return;
        }
        
        const confirmMsg = action === 'approve' 
            ? `Are you sure you want to approve ${selectedItems.size} refund(s)?`
            : `Are you sure you want to reject ${selectedItems.size} refund(s)?`;
        
        if (!confirm(confirmMsg)) return;
        
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
        document.getElementById('bulkActionForm').submit();
    }

    // Debounced live search
    let searchTimeout;
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
    const filterSelects = ['filterStatus', 'filterReason', 'filterPerPage'];
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
        const status = document.getElementById('filterStatus')?.value;
        if (status) params.set('status', status);
        
        const reason = document.getElementById('filterReason')?.value;
        if (reason) params.set('reason', reason);
        
        const perPage = document.getElementById('filterPerPage')?.value;
        if (perPage) params.set('per_page', perPage);
        
        // Keep existing sort
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
        if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
        
        // AJAX request
        fetch(`{{ route('admin.refunds.index') }}?${params.toString()}&ajax=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (searchSpinner) searchSpinner.style.display = 'none';
            
            if (data.html) {
                // Update table body
                document.querySelector('#tableBody').innerHTML = data.html;
                
                // Update statistics cards
                if (data.stats) {
                    document.getElementById('statTotal').textContent = data.stats.total || 0;
                    document.getElementById('statPending').textContent = data.stats.pending || 0;
                    document.getElementById('statApproved').textContent = data.stats.approved || 0;
                    document.getElementById('statRejected').textContent = data.stats.rejected || 0;
                    document.getElementById('statProcessed').textContent = data.stats.processed || 0;
                }
                
                // Update URL without reload
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
        })
        .catch(error => {
            if (searchSpinner) searchSpinner.style.display = 'none';
            console.error('Search error:', error);
            // Fallback to regular form submission
            document.getElementById('filterForm').submit();
        });
    }
</script>
@endpush
