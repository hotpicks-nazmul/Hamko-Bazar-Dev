@extends('admin.layouts.app')

@section('title', 'Affiliate Banners')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-images"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Banners</span>
            <span class="stat-card-value" id="stat-total">{{ number_format($stats['total'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value" id="stat-active">{{ number_format($stats['active'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value" id="stat-inactive">{{ number_format($stats['inactive'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-cursor"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Clicks</span>
            <span class="stat-card-value" id="stat-clicks">{{ number_format($stats['total_clicks'] ?? 0) }}</span>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Affiliate Banners</h4>
    <a href="{{ route('admin.affiliate.banners.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add New Banner
    </a>
</div>

<!-- Bulk Action Bar (hidden by default) -->
<div id="bulkActionBar" class="card border-0 shadow-sm mb-3" style="display: none;">
    <div class="card-body py-2 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <span class="me-3" id="selectedCount">0 selected</span>
            <button type="button" class="btn btn-sm btn-success me-2" onclick="bulkAction('activate')">
                <i class="bi bi-check-circle me-1"></i>Activate
            </button>
            <button type="button" class="btn btn-sm btn-secondary me-2" onclick="bulkAction('deactivate')">
                <i class="bi bi-x-circle me-1"></i>Deactivate
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
            <i class="bi bi-x-lg me-1"></i>Clear Selection
        </button>
    </div>
</div>

<!-- Search and Filter -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search banners..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary w-100" onclick="applyFilters()">
                    <i class="bi bi-filter me-1"></i>Filter
                </button>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </button>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border-0 shadow-sm mb-3" id="bannerTableCard">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-images me-2"></i>Banner List</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                        </th>
                        <th>Preview</th>
                        <th>Name</th>
                        <th>Size</th>
                        <th>Affiliate</th>
                        <th>Clicks</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="bannerTableBody">
                    @include('admin.affiliate.banners.partials.banner-rows', ['banners' => $banners])
                </tbody>
            </table>
        </div>

        <div id="paginationArea">
            @if($banners->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $banners->firstItem() }} - {{ $banners->lastItem() }} of {{ $banners->total() }} banners
                </div>
                <div>
                    {{ $banners->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let selectedIds = [];

    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:not(#selectAll)');
        checkboxes.forEach(cb => {
            cb.checked = source.checked;
            if (source.checked) {
                if (!selectedIds.includes(cb.dataset.id)) {
                    selectedIds.push(cb.dataset.id);
                }
            }
        });
        updateBulkBar();
    }

    function toggleBanner(id) {
        if (selectedIds.includes(id)) {
            selectedIds = selectedIds.filter(i => i !== id);
        } else {
            selectedIds.push(id);
        }
        updateBulkBar();
    }

    function updateBulkBar() {
        const bulkBar = document.getElementById('bulkActionBar');
        const countSpan = document.getElementById('selectedCount');

        if (selectedIds.length > 0) {
            bulkBar.style.display = 'block';
            countSpan.textContent = selectedIds.length + ' selected';
        } else {
            bulkBar.style.display = 'none';
        }
    }

    function clearSelection() {
        selectedIds = [];
        document.querySelectorAll('tbody input[type="checkbox"]').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        updateBulkBar();
    }

    function bulkAction(action) {
        if (selectedIds.length === 0) {
            alert('Please select at least one banner.');
            return;
        }

        if (action === 'delete' && !confirm('Are you sure you want to delete the selected banners?')) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.affiliate.banners.bulk-action') }}';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
        form.appendChild(actionInput);

        const idsInput = document.createElement('input');
        idsInput.type = 'hidden';
        idsInput.name = 'ids';
        idsInput.value = JSON.stringify(selectedIds);
        form.appendChild(idsInput);

        document.body.appendChild(form);
        form.submit();
    }

    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;

        let url = '{{ route('admin.affiliate.banners.index') }}?';
        const params = [];

        if (search) params.push('search=' + encodeURIComponent(search));
        if (status) params.push('status=' + encodeURIComponent(status));

        url += params.join('&');

        window.location.href = url;
    }

    function resetFilters() {
        window.location.href = '{{ route('admin.affiliate.banners.index') }}';
    }

    // Debounce function for AJAX search
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // AJAX live search
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const search = this.value;
            const status = document.getElementById('statusFilter').value;

            fetch('{{ route('admin.affiliate.banners.index') }}?' + new URLSearchParams({ search, status, ajax: 1 }), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('bannerTableBody').innerHTML = data.html;

                // Update stats
                if (data.stats) {
                    document.getElementById('stat-total').textContent = data.stats.total;
                    document.getElementById('stat-active').textContent = data.stats.active;
                    document.getElementById('stat-inactive').textContent = data.stats.inactive;
                    document.getElementById('stat-clicks').textContent = data.stats.total_clicks;
                }

                // Rebind checkbox events
                rebindCheckboxes();
            });
        }, 300);
    });

    function rebindCheckboxes() {
        selectedIds = [];
        document.querySelectorAll('tbody input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', function() {
                toggleBanner(this.dataset.id);
            });
        });
    }

    // Initial bind
    rebindCheckboxes();
</script>
@endpush