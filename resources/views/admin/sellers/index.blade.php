@extends('admin.layouts.app')

@section('title', 'All Sellers')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row stat-card-row-6 mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-people-fill"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Sellers</span>
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
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value" id="stat-inactive">{{ $stats['inactive'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-patch-check"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Verified</span>
            <span class="stat-card-value" id="stat-verified">{{ $stats['verified'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Pending</span>
            <span class="stat-card-value" id="stat-pending">{{ $stats['pending'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-octagon"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Rejected</span>
            <span class="stat-card-value" id="stat-rejected">{{ $stats['rejected'] ?? 0 }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">All Sellers</h4>
    <a href="{{ route('admin.sellers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Seller
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
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Name, email, shop name..." value="{{ request('search') }}">
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

                <!-- Verification Status -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Verification</label>
                    <select name="verification_status" id="filterVerification" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="verified" {{ request('verification_status') === 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="pending" {{ request('verification_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('verification_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Per Page</label>
                    <select name="per_page" id="perPage" class="form-select form-select-sm">
                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('admin.sellers.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card border-0 shadow-sm mb-3" id="bulkActionsCard" style="display: none;">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small" id="selectedCount">0 selected</span>
            <select id="bulkActionSelect" class="form-select form-select-sm" style="width: auto;">
                <option value="">Bulk Actions</option>
                <option value="active">Set Active</option>
                <option value="inactive">Set Inactive</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button type="button" id="applyBulkAction" class="btn btn-sm btn-primary">
                Apply
            </button>
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
                        <th>Seller</th>
                        <th>Shop Info</th>
                        <th>Products</th>
                        <th>Wallet</th>
                        <th>Status</th>
                        <th>Verification</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.sellers.partials.table-rows')
                </tbody>
            </table>
        </div>

        <!-- Pagination inside card-body -->
        @if($sellers->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $sellers->firstItem() }} - {{ $sellers->lastItem() }} of {{ $sellers->total() }} sellers
            </div>
            <div>
                {{ $sellers->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .seller-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    .shop-logo-thumb {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        object-fit: cover;
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script>
    // Live search with debounce
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const filterForm = document.getElementById('filterForm');
    const tableBody = document.getElementById('tableBody');

    function fetchResults() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchSpinner.style.display = 'block';

            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);

            fetch('{{ route("admin.sellers.index") }}?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = data.html;

                // Update stats
                document.getElementById('stat-total').textContent = data.stats.total;
                document.getElementById('stat-active').textContent = data.stats.active;
                document.getElementById('stat-inactive').textContent = data.stats.inactive;
                document.getElementById('stat-verified').textContent = data.stats.verified;
                document.getElementById('stat-pending').textContent = data.stats.pending;
                document.getElementById('stat-rejected').textContent = data.stats.rejected;

                // Update pagination
                const paginationContainer = document.querySelector('.card-footer');
                if (paginationContainer && data.pagination) {
                    const firstItem = data.pagination_info.firstItem || 0;
                    const lastItem = data.pagination_info.lastItem || 0;
                    const total = data.pagination_info.total || 0;
                    paginationContainer.innerHTML = `
                        <div class="text-muted small">
                            Showing ${firstItem} - ${lastItem} of ${total} sellers
                        </div>
                        <div>${data.pagination}</div>
                    `;
                }

                // Reinitialize checkbox listeners
                initCheckboxListeners();
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                searchSpinner.style.display = 'none';
            });
        }, 300);
    }

    // Search input event
    if (searchInput) {
        searchInput.addEventListener('input', fetchResults);
    }

    // Filter change events
    document.getElementById('filterStatus')?.addEventListener('change', fetchResults);
    document.getElementById('filterVerification')?.addEventListener('change', fetchResults);
    document.getElementById('perPage')?.addEventListener('change', () => {
        filterForm.submit();
    });

    // Bulk Actions
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const bulkActionsCard = document.getElementById('bulkActionsCard');
    const selectedCount = document.getElementById('selectedCount');
    const bulkActionSelect = document.getElementById('bulkActionSelect');
    const applyBulkAction = document.getElementById('applyBulkAction');

    function initCheckboxListeners() {
        const checkboxes = tableBody.querySelectorAll('.seller-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', toggleSelectAll);
        }
    }

    function updateBulkActions() {
        const checkboxes = tableBody.querySelectorAll('.seller-checkbox:checked');
        const count = checkboxes.length;

        if (count > 0) {
            bulkActionsCard.style.display = 'block';
            selectedCount.textContent = count + ' selected';
        } else {
            bulkActionsCard.style.display = 'none';
        }
    }

    function toggleSelectAll() {
        const checkboxes = tableBody.querySelectorAll('.seller-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateBulkActions();
    }

    // Apply bulk action
    if (applyBulkAction) {
        applyBulkAction.addEventListener('click', function() {
            const action = bulkActionSelect.value;
            if (!action) return;

            const checkboxes = tableBody.querySelectorAll('.seller-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);

            if (action === 'delete') {
                if (!confirm('Are you sure you want to delete ' + ids.length + ' seller(s)? This will also remove their products.')) {
                    return;
                }
            }

            let url = '{{ route("admin.sellers.bulk-update") }}';
            if (action === 'delete') {
                url = '{{ route("admin.sellers.bulk-delete") }}';
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    seller_ids: ids,
                    status: action !== 'delete' ? action : undefined
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    }

    // Initialize on page load
    initCheckboxListeners();

    // Delete confirmation
    function confirmDelete(formId) {
        if (confirm('Are you sure you want to delete this seller? This will also remove their products.')) {
            document.getElementById(formId).submit();
        }
    }
</script>
@endpush
