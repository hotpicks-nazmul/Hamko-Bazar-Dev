@extends('admin.layouts.app')

@section('title', 'Quotations')

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Quotations</h4>
            <p class="text-muted mb-0">Manage customer quotations and convert to orders</p>
        </div>
        <a href="{{ route('admin.quotations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Create Quotation
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="stat-card-row stat-card-row-7 mb-4" id="statsCards">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Total</span><span class="stat-card-value" id="statTotal">{{ $stats['total'] ?? 0 }}</span></div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Pending</span><span class="stat-card-value" id="statPending">{{ $stats['pending'] ?? 0 }}</span></div>
        </div>
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon"><i class="bi bi-send"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Sent</span><span class="stat-card-value" id="statSent">{{ $stats['sent'] ?? 0 }}</span></div>
        </div>
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="bi bi-check2-circle"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Accepted</span><span class="stat-card-value" id="statAccepted">{{ $stats['accepted'] ?? 0 }}</span></div>
        </div>
        <div class="stat-card stat-card-danger">
            <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Rejected</span><span class="stat-card-value" id="statRejected">{{ $stats['rejected'] ?? 0 }}</span></div>
        </div>
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-arrow-repeat"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Converted</span><span class="stat-card-value" id="statConverted">{{ $stats['converted'] ?? 0 }}</span></div>
        </div>
        <div class="stat-card stat-card-secondary">
            <div class="stat-card-icon"><i class="bi bi-calendar-x"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Expired</span><span class="stat-card-value" id="statExpired">{{ $stats['expired'] ?? 0 }}</span></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <label class="form-label small text-muted">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="liveSearch" class="form-control" 
                                   placeholder="Quotation #, Customer..." value="{{ request('search') }}">
                            <span class="input-group-text" id="searchSpinner" style="display: none;">
                                <div class="spinner-border spinner-border-sm"></div>
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" id="filterStatus" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Per Page</label>
                        <select name="per_page" id="filterPerPage" class="form-select form-select-sm">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == '25' || !request('per_page') ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4 col-sm-8">
                        <a href="{{ route('admin.quotations.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
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
                    <button type="button" class="btn btn-sm btn-info" onclick="bulkAction('mark_sent')">
                        <i class="bi bi-send me-1"></i> Mark as Sent
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quotations Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>
                                <a href="{{ route('admin.quotations.index', array_merge(request()->query(), ['sort' => 'quotation_number', 'direction' => request('sort') == 'quotation_number' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Quotation #
                                    @if(request('sort') == 'quotation_number')
                                        <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Customer</th>
                            <th>
                                <a href="{{ route('admin.quotations.index', array_merge(request()->query(), ['sort' => 'total', 'direction' => request('sort') == 'total' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Total
                                    @if(request('sort') == 'total')
                                        <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.quotations.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Status
                                    @if(request('sort') == 'status')
                                        <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.quotations.index', array_merge(request()->query(), ['sort' => 'valid_until', 'direction' => request('sort') == 'valid_until' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Valid Until
                                    @if(request('sort') == 'valid_until')
                                        <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.quotations.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Created
                                    @if(request('sort') == 'created_at' || !request('sort'))
                                        <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="width: 120px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @include('admin.quotations.partials.table-rows', ['search' => $search ?? ''])
                    </tbody>
                </table>
            </div>
            
            @if($quotations->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $quotations->firstItem() }} - {{ $quotations->lastItem() }} of {{ $quotations->total() }} items
                </div>
                <div id="paginationContainer">
                    {{ $quotations->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.quotations.bulk-action') }}" class="d-none">
    @csrf
    <input type="hidden" name="ids" id="bulkIdsInput">
    <input type="hidden" name="action" id="bulkActionInput">
</form>
@endsection

@push('scripts')
<script>
    // Debounced live search
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const filterStatus = document.getElementById('filterStatus');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        
        searchSpinner.style.display = 'block';
        
        searchTimeout = setTimeout(() => {
            performLiveSearch(searchTerm);
        }, 300);
    });

    filterStatus.addEventListener('change', function() {
        performLiveSearch(searchInput.value.trim());
    });

    document.querySelectorAll('input[name="date_from"], input[name="date_to"]').forEach(input => {
        input.addEventListener('change', function() {
            performLiveSearch(searchInput.value.trim());
        });
    });

    // Per page filter
    const filterPerPage = document.getElementById('filterPerPage');
    if (filterPerPage) {
        filterPerPage.addEventListener('change', function() {
            performLiveSearch(searchInput.value.trim());
        });
    }

    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();

        if (searchTerm) params.set('search', searchTerm);

        const status = filterStatus.value;
        if (status) params.set('status', status);

        const dateFrom = document.querySelector('input[name="date_from"]').value;
        if (dateFrom) params.set('date_from', dateFrom);

        const dateTo = document.querySelector('input[name="date_to"]').value;
        if (dateTo) params.set('date_to', dateTo);

        const perPage = filterPerPage ? filterPerPage.value : '25';
        params.set('per_page', perPage);

        fetch(`{{ route('admin.quotations.index') }}?${params.toString()}&ajax=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            searchSpinner.style.display = 'none';

            if (data.html) {
                document.getElementById('tableBody').innerHTML = data.html;

                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);

                // Re-initialize checkboxes
                initCheckboxes();
            }

            // Update stats if provided
            if (data.stats) {
                updateStats(data.stats);
            }
        });
    }

    // Update statistics cards
    function updateStats(stats) {
        const statIds = {
            'statTotal': stats.total ?? 0,
            'statPending': stats.pending ?? 0,
            'statSent': stats.sent ?? 0,
            'statAccepted': stats.accepted ?? 0,
            'statRejected': stats.rejected ?? 0,
            'statConverted': stats.converted ?? 0,
            'statExpired': stats.expired ?? 0,
        };

        for (const [id, value] of Object.entries(statIds)) {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        }
    }

    // Bulk selection
    let selectedItems = new Set();

    function initCheckboxes() {
        document.querySelectorAll('.quotation-checkbox').forEach(cb => {
            cb.checked = selectedItems.has(cb.value);
            cb.addEventListener('change', function() {
                if (this.checked) {
                    selectedItems.add(this.value);
                } else {
                    selectedItems.delete(this.value);
                }
                updateBulkActions();
            });
        });
    }

    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    }

    function clearSelection() {
        selectedItems.clear();
        document.querySelectorAll('.quotation-checkbox, #selectAll').forEach(cb => cb.checked = false);
        updateBulkActions();
    }

    function bulkAction(action) {
        if (selectedItems.size === 0) {
            alert('Please select at least one quotation.');
            return;
        }
        
        const message = action === 'delete' 
            ? `Are you sure you want to delete ${selectedItems.size} quotation(s)?`
            : `Are you sure you want to ${action.replace('_', ' ')} ${selectedItems.size} quotation(s)?`;
        
        if (!confirm(message)) return;
        
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
        document.getElementById('bulkActionForm').submit();
    }

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.quotation-checkbox').forEach(cb => {
            cb.checked = this.checked;
            if (this.checked) {
                selectedItems.add(cb.value);
            } else {
                selectedItems.delete(cb.value);
            }
        });
        updateBulkActions();
    });

    // Initialize checkboxes on load
    initCheckboxes();
</script>
@endpush
