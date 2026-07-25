@extends('admin.layouts.app')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Membership Plans</h4>
    <a href="{{ route('admin.customers.membership.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Plan
    </a>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row stat-card-row-6 mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-card-checklist"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Plans</span>
            <span class="stat-card-value">{{ number_format($stats['total']) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value">{{ number_format($stats['active']) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-pause-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value">{{ number_format($stats['inactive']) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-star"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Featured</span>
            <span class="stat-card-value">{{ number_format($stats['featured']) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-people"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Members</span>
            <span class="stat-card-value">{{ number_format($stats['total_members']) }}</span>
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
                               placeholder="Name, slug..." value="{{ request('search') }}">
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
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Featured Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Featured</label>
                    <select name="featured" id="filterFeatured" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Featured Only</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Sort By</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="sort_order" {{ request('sort') == 'sort_order' ? 'selected' : '' }}>Sort Order</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>Price</option>
                        <option value="duration_days" {{ request('sort') == 'duration_days' ? 'selected' : '' }}>Duration</option>
                        <option value="discount_percentage" {{ request('sort') == 'discount_percentage' ? 'selected' : '' }}>Discount</option>
                        <option value="members_count" {{ request('sort') == 'members_count' ? 'selected' : '' }}>Members</option>
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Created Date</option>
                    </select>
                </div>

                <!-- Direction -->
                <div class="col-lg-1 col-md-2 col-sm-4">
                    <label class="form-label small text-muted">Order</label>
                    <select name="direction" class="form-select form-select-sm">
                        <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Asc</option>
                        <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Desc</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Per Page</label>
                    <select name="per_page" class="form-select form-select-sm">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="col-lg-1 col-md-2 col-sm-4">
                    <a href="{{ route('admin.customers.membership.index') }}" class="btn btn-sm btn-outline-secondary w-100">
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
                        <th style="width: 50px;">#</th>
                        <th>Plan Name</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Duration</th>
                        <th class="text-center">Discount</th>
                        <th class="text-center">Members</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Featured</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.customers.membership.partials.table-rows')
                </tbody>
            </table>
        </div>
        
        @if($plans->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $plans->firstItem() }} - {{ $plans->lastItem() }} of {{ $plans->total() }} plans
            </div>
            <div>
                {{ $plans->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    color: white;
}
/* Stat cards use global styles from app.blade.php */
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const filterForm = document.getElementById('filterForm');
    const filterSelects = ['filterStatus', 'filterFeatured'];
    let searchTimeout;

    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();
        
        if (searchTerm) params.set('search', searchTerm);
        
        const status = document.getElementById('filterStatus').value;
        if (status) params.set('status', status);
        
        const featured = document.getElementById('filterFeatured').value;
        if (featured) params.set('featured', featured);
        
        const sort = filterForm.querySelector('select[name="sort"]').value;
        if (sort) params.set('sort', sort);
        
        const direction = filterForm.querySelector('select[name="direction"]').value;
        if (direction) params.set('direction', direction);
        
        const perPage = filterForm.querySelector('select[name="per_page"]').value;
        if (perPage) params.set('per_page', perPage);
        
        fetch(`{{ route('admin.customers.membership.index') }}?${params.toString()}&ajax=1`, {
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
                
                const paginationContainer = document.querySelector('.card-footer');
                if (paginationContainer && data.pagination) {
                    paginationContainer.innerHTML = `
                        <div class="text-muted small">
                            Showing ${data.stats.total > 0 ? (data.plans_firstitem || 1) + ' - ' + (data.plans_lastitem || data.stats.total) : '0'} of ${data.stats.total} plans
                        </div>
                        <div>${data.pagination}</div>
                    `;
                }
                
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
        })
        .catch(error => {
            searchSpinner.style.display = 'none';
            console.error('Search error:', error);
        });
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        
        searchSpinner.style.display = 'block';
        
        searchTimeout = setTimeout(() => {
            performLiveSearch(searchTerm);
        }, 300);
    });

    filterSelects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                performLiveSearch(searchInput.value.trim());
            });
        }
    });

    filterForm.querySelectorAll('select[name="sort"], select[name="direction"], select[name="per_page"]').forEach(select => {
        select.addEventListener('change', function() {
            performLiveSearch(searchInput.value.trim());
        });
    });

    // Toggle status via AJAX
    document.addEventListener('click', function(e) {
        const statusBtn = e.target.closest('.status-toggle');
        if (statusBtn) {
            e.preventDefault();
            const id = statusBtn.dataset.id;
            const icon = statusBtn.querySelector('i');
            
            fetch(`{{ route('admin.customers.membership.toggle-status', ':id') }}`.replace(':id', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update button appearance
                    const isActive = data.is_active;
                    statusBtn.className = `btn btn-sm btn-outline-${isActive ? 'warning' : 'success'} status-toggle`;
                    statusBtn.dataset.id = id;
                    statusBtn.title = isActive ? 'Deactivate' : 'Activate';
                    icon.className = `bi bi-${isActive ? 'pause' : 'play'}-fill`;
                    
                    // Update the row's status badge
                    const row = statusBtn.closest('tr');
                    const statusBadge = row.querySelector('td:nth-child(7) .badge');
                    if (statusBadge) {
                        statusBadge.className = `badge bg-${isActive ? 'success' : 'secondary'}`;
                        statusBadge.textContent = isActive ? 'Active' : 'Inactive';
                    }
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Toggle status error:', error);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to update status');
                }
            });
        }
    });

    // Toggle featured via AJAX
    document.addEventListener('click', function(e) {
        const featuredBtn = e.target.closest('.featured-toggle');
        if (featuredBtn) {
            e.preventDefault();
            const id = featuredBtn.dataset.id;
            const icon = featuredBtn.querySelector('i');
            
            fetch(`{{ route('admin.customers.membership.toggle-featured', ':id') }}`.replace(':id', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update button appearance
                    const isFeatured = data.is_featured;
                    featuredBtn.className = `btn btn-sm btn-outline-${isFeatured ? 'warning' : 'secondary'} featured-toggle`;
                    featuredBtn.dataset.id = id;
                    featuredBtn.title = isFeatured ? 'Unfeature' : 'Feature';
                    icon.className = `bi bi-star${isFeatured ? '-fill' : ''}`;
                    
                    // Update the row's featured badge
                    const row = featuredBtn.closest('tr');
                    const featuredCell = row.querySelector('td:nth-child(8)');
                    if (featuredCell) {
                        if (isFeatured) {
                            featuredCell.innerHTML = '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Featured</span>';
                        } else {
                            featuredCell.innerHTML = '<span class="text-muted">-</span>';
                        }
                    }
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Toggle featured error:', error);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to update featured status');
                }
            });
        }
    });
});
</script>
@endpush
