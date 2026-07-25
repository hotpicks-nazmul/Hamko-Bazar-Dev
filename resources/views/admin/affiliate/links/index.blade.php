@extends('admin.layouts.app')

@section('title', 'Affiliate Links')

@section('content')
<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-link"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Total Links</span>
                <span class="stat-card-value">{{ number_format($stats['total'] ?? 0) }}</span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Active</span>
                <span class="stat-card-value">{{ number_format($stats['active'] ?? 0) }}</span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon"><i class="bi bi-cursor"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Total Clicks</span>
                <span class="stat-card-value">{{ number_format($stats['total_clicks'] ?? 0) }}</span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon"><i class="bi bi-graph-up"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Conversions</span>
                <span class="stat-card-value">{{ number_format($stats['total_conversions'] ?? 0) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Affiliate Links</h4>
    <a href="{{ route('admin.affiliate.links.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Link
    </a>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Search links..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.affiliate.links.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                </div>
            </div>
        </form>
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

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">
                            <a href="{{ route('admin.affiliate.links.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                ID
                                @if(request('sort') == 'id')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.affiliate.links.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Link Name
                                @if(request('sort') == 'name')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th>Affiliate</th>
                        <th>Product</th>
                        <th style="width: 200px;">Code</th>
                        <th style="width: 80px;">
                            <a href="{{ route('admin.affiliate.links.index', array_merge(request()->query(), ['sort' => 'clicks', 'direction' => request('sort') == 'clicks' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Clicks
                                @if(request('sort') == 'clicks')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 100px;">
                            <a href="{{ route('admin.affiliate.links.index', array_merge(request()->query(), ['sort' => 'conversions', 'direction' => request('sort') == 'conversions' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Conversions
                                @if(request('sort') == 'conversions')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 90px;">Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.affiliate.links.partials.link-rows')
                </tbody>
            </table>
        </div>
        
        @if($links->hasPages())
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
                {{ $links->appends(request()->query())->links() }}
            </div>
            <div class="text-muted small">
                Showing {{ $links->firstItem() ?? 0 }} - {{ $links->lastItem() ?? 0 }} of {{ $links->total() }} links
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Force Bootstrap Icons to display */
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
    
    /* Make the whole icon colored */
    .stat-card-icon i { color: inherit !important; }
</style>
@endpush

@push('scripts')
<script>
    function copyLink(inputId) {
        var copyText = document.getElementById(inputId);
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        
        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = '<div class="toast show" role="alert"><div class="toast-body"><i class="bi bi-check-circle text-success me-2"></i>Link copied to clipboard!</div></div>';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    // Change per page
    function changePerPage(value) {
        const params = new URLSearchParams(window.location.search);
        params.set('per_page', value);
        window.location.href = '{{ route('admin.affiliate.links.index') }}?' + params.toString();
    }

    // Live Search
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const filterForm = document.getElementById('filterForm');

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

    // Filter dropdowns
    const filterStatus = document.getElementById('filterStatus');
    if (filterStatus) {
        filterStatus.addEventListener('change', function() {
            performLiveSearch(searchInput ? searchInput.value.trim() : '');
        });
    }

    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();
        
        if (searchTerm) params.set('search', searchTerm);
        
        const status = document.getElementById('filterStatus').value;
        if (status) params.set('status', status);

        // Keep existing sort and per_page
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
        if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
        if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
        
        fetch(`{{ route('admin.affiliate.links.index') }}?${params.toString()}&ajax=1`, {
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
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
        })
        .catch(() => {
            searchSpinner.style.display = 'none';
            filterForm.submit();
        });
    }
</script>
@endpush
