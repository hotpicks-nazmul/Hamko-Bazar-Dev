@extends('admin.layouts.app')

@section('title', 'Form Builder')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-ui-checks me-2"></i>Form Builder</h4>
    <a href="{{ route('admin.form-builder.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Create New Form
    </a>
</div>

<!-- Stats Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-ui-checks"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Forms</span>
            <span class="stat-card-value">{{ number_format($stats['total'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active Forms</span>
            <span class="stat-card-value">{{ number_format($stats['active'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive Forms</span>
            <span class="stat-card-value">{{ number_format($stats['inactive'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-file-earmark-text"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Submissions</span>
            <span class="stat-card-value">{{ number_format($stats['total_submissions'] ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Search forms..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small text-muted">Sort By</label>
                    <select name="sort" id="filterSort" class="form-select form-select-sm">
                        <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Date Created</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name</option>
                        <option value="order" {{ request('sort') === 'order' ? 'selected' : '' }}>Display Order</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <select name="direction" id="filterDirection" class="form-select form-select-sm">
                        <option value="desc" {{ request('direction') === 'desc' ? 'selected' : '' }}>Descending</option>
                        <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>Ascending</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <a href="{{ route('admin.form-builder.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Forms Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Form Name</th>
                        <th>Slug</th>
                        <th>Fields</th>
                        <th>Submissions</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.form-builder.partials.table-rows', ['forms' => $forms])
                </tbody>
            </table>
        </div>
        
        @if($forms->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $forms->firstItem() }} - {{ $forms->lastItem() }} of {{ $forms->total() }} forms
            </div>
            <div>
                {{ $forms->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live search with debounce
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const filterForm = document.getElementById('filterForm');
    
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        
        // Show spinner
        searchSpinner.style.display = 'block';
        
        // Build URL with filters
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        
        // AJAX request
        fetch(`{{ route('admin.form-builder.index') }}?${params.toString()}&ajax=1`, {
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
        })
        .catch(err => {
            searchSpinner.style.display = 'none';
            console.error('Search error:', err);
        });
    }
    
    // Debounced search
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 300);
    });
    
    // Filter dropdowns trigger search on change
    const filterStatus = document.getElementById('filterStatus');
    const filterSort = document.getElementById('filterSort');
    const filterDirection = document.getElementById('filterDirection');
    
    if (filterStatus) {
        filterStatus.addEventListener('change', performSearch);
    }
    if (filterSort) {
        filterSort.addEventListener('change', performSearch);
    }
    if (filterDirection) {
        filterDirection.addEventListener('change', performSearch);
    }
    
    // Toggle form status via AJAX
    window.toggleFormStatus = function(formId, button) {
        fetch(`{{ route('admin.form-builder.toggle-status', ':id') }}`.replace(':id', formId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update button appearance
                if (data.is_active) {
                    button.className = 'btn btn-sm btn-outline-warning';
                    button.innerHTML = '<i class="bi bi-pause-circle"></i>';
                    button.title = 'Deactivate';
                } else {
                    button.className = 'btn btn-sm btn-outline-success';
                    button.innerHTML = '<i class="bi bi-play-circle"></i>';
                    button.title = 'Activate';
                }
            }
        })
        .catch(err => console.error('Error toggling status:', err));
    };
});
</script>
@endpush
