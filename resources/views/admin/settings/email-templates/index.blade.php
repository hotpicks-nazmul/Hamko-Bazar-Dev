@extends('admin.layouts.app')

@section('title', 'Email Templates')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Email Templates</h4>
</div>

<!-- Stats Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-envelope"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Templates</span>
            <span class="stat-card-value">{{ $stats['total'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value">{{ $stats['active'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value">{{ $stats['inactive'] ?? 0 }}</span>
        </div>
    </div>
</div>

        <!-- Filters Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Search Input -->
                        <div class="col-lg-4 col-md-4 col-sm-6">
                            <label class="form-label small text-muted">Search</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" id="liveSearch" class="form-control" 
                                       placeholder="Search by subject or slug..." value="{{ request('search') }}">
                                <span class="input-group-text" id="searchSpinner" style="display: none;">
                                    <div class="spinner-border spinner-border-sm"></div>
                                </span>
                            </div>
                        </div>

                        <!-- Recipient Type Filter -->
                        <div class="col-lg-3 col-md-3 col-sm-6">
                            <label class="form-label small text-muted">Recipient Type</label>
                            <select name="recipient_type" id="filterRecipientType" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <option value="customer" {{ request('recipient_type') == 'customer' ? 'selected' : '' }}>Customer</option>
                                <option value="seller" {{ request('recipient_type') == 'seller' ? 'selected' : '' }}>Seller</option>
                                <option value="admin" {{ request('recipient_type') == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-lg-2 col-md-2 col-sm-6">
                            <label class="form-label small text-muted">Status</label>
                            <select name="status" id="filterStatus" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- Reset Button -->
                        <div class="col-lg-2 col-md-2 col-sm-6">
                            <a href="{{ route('admin.settings.email-templates.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-lg me-1"></i> Reset
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
                                <th>Template</th>
                                <th>Event</th>
                                <th>Recipient</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @include('admin.settings.email-templates.partials.table-rows', ['templates' => $templates])
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($templates->hasPages())
                <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">
                        Showing {{ $templates->firstItem() }} - {{ $templates->lastItem() }} of {{ $templates->total() }} templates
                    </div>
                    <div>
                        {{ $templates->appends(request()->query())->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
@endsection

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
        
        // Debounce - wait 300ms after user stops typing
        searchTimeout = setTimeout(() => {
            performLiveSearch(searchTerm);
        }, 300);
    });

    // Filter dropdowns trigger search on change
    const filterSelects = ['filterRecipientType', 'filterStatus'];
    filterSelects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                performLiveSearch(searchInput.value.trim());
            });
        }
    });

    // Live search function
    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();
        
        if (searchTerm) params.set('search', searchTerm);
        
        // Add filter values
        const recipientType = document.getElementById('filterRecipientType').value;
        if (recipientType) params.set('recipient_type', recipientType);
        
        const status = document.getElementById('filterStatus').value;
        if (status) params.set('status', status);
        
        // Keep existing sort and per_page
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
        if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
        if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
        
        // AJAX request
        fetch(`{{ route('admin.settings.email-templates.index') }}?${params.toString()}&ajax=1`, {
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
