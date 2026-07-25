@extends('admin.layouts.app')

@section('title', 'Digital Product Categories')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-folder"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Total</span><span class="stat-card-value text-primary" id="statTotal">{{ $stats['total'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Active</span><span class="stat-card-value text-success" id="statActive">{{ $stats['active'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Inactive</span><span class="stat-card-value text-danger" id="statInactive">{{ $stats['inactive'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-diagram-3"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Parent Categories</span><span class="stat-card-value text-info" id="statRoot">{{ $stats['root'] ?? 0 }}</span></div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Digital Product Categories</h4>
        <small class="text-muted">Manage categories for digital products</small>
    </div>
    <a href="{{ route('admin.digital-categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Category
    </a>
</div>

<!-- Filters -->
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
                               placeholder="Name, description..." value="{{ request('search') }}">
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
                
                <!-- Parent Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Parent</label>
                    <select name="parent" id="filterParent" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <option value="root" {{ request('parent') === 'root' ? 'selected' : '' }}>Root Only</option>
                        @foreach($allCategories as $category)
                            <option value="{{ $category->id }}" {{ request('parent') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Per Page -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Per Page</label>
                    <select name="per_page" id="filterPerPage" class="form-select form-select-sm">
                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                        <option value="25" {{ (request('per_page') == '25' || !request('per_page')) ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-3 col-md-4 col-sm-8">
                    <a href="{{ route('admin.digital-categories.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Categories Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div id="tableContent">
            @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">
                                <a href="#" class="text-decoration-none text-dark sort-link" data-sort="order">#</a>
                            </th>
                            <th>
                                <a href="#" class="text-decoration-none text-dark sort-link" data-sort="name">
                                    Category
                                    @if(request('sort') == 'name')
                                        <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="width: 120px;">Products</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 100px;">Order</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @include('admin.digital-categories.partials.table-rows')
                    </tbody>
                </table>
            </div>
            
            @if($categories->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2" id="paginationContainer">
                <div class="text-muted small" id="paginationInfo">
                    Showing {{ $categories->firstItem() ?? 0 }} - {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} entries
                </div>
                <div id="paginationLinks">
                    {{ $categories->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
            @else
            <div class="text-center py-5" id="emptyState">
                <i class="bi bi-folder-x display-4 text-muted"></i>
                <p class="text-muted mt-2">No categories found.</p>
                <a href="{{ route('admin.digital-categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add First Category
                </a>
            </div>
            @endif
        </div>
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
    
    /* Ensure stat card icons are visible */
    .stat-card-primary .stat-card-icon i { color: #0d6efd !important; }
    .stat-card-success .stat-card-icon i { color: #198754 !important; }
    .stat-card-info .stat-card-icon i { color: #0dcaf0 !important; }
    .stat-card-warning .stat-card-icon i { color: #ffc107 !important; }
    .stat-card-danger .stat-card-icon i { color: #dc3545 !important; }
    .stat-card-secondary .stat-card-icon i { color: #6c757d !important; }
</style>
@endpush

@push('scripts')
<script>
// Current sort parameters
let currentSort = '{{ request('sort') ?? 'order' }}';
let currentDirection = '{{ request('direction') ?? 'asc' }}';

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
const filterSelects = ['filterStatus', 'filterParent', 'filterPerPage'];
filterSelects.forEach(id => {
    const select = document.getElementById(id);
    if (select) {
        select.addEventListener('change', function() {
            performLiveSearch(searchInput.value.trim());
        });
    }
});

// Sort links
document.querySelectorAll('.sort-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const sort = this.dataset.sort;
        
        // Toggle direction if same sort
        if (currentSort === sort) {
            currentDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort = sort;
            currentDirection = 'asc';
        }
        
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
    
    const parent = document.getElementById('filterParent').value;
    if (parent) params.set('parent', parent);
    
    const perPage = document.getElementById('filterPerPage').value;
    params.set('per_page', perPage);
    
    // Add sort parameters
    params.set('sort', currentSort);
    params.set('direction', currentDirection);
    
    // AJAX request
    fetch(`{{ route('admin.digital-categories.index') }}?${params.toString()}&ajax=1`, {
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
            
            // Update pagination links
            if (data.pagination) {
                const paginationLinks = document.getElementById('paginationLinks');
                if (paginationLinks) {
                    paginationLinks.innerHTML = data.pagination;
                }
            }
            
            // Update pagination info
            if (data.pagination_info) {
                const paginationInfo = document.getElementById('paginationInfo');
                if (paginationInfo) {
                    const info = data.pagination_info;
                    if (info.has_pages) {
                        paginationInfo.textContent = `Showing ${info.first_item ?? 0} - ${info.last_item ?? 0} of ${info.total} entries`;
                    } else {
                        paginationInfo.textContent = `Showing ${info.total} entries`;
                    }
                }
            }
            
            // Update stats
            if (data.stats) {
                document.getElementById('statTotal').textContent = data.stats.total;
                document.getElementById('statActive').textContent = data.stats.active;
                document.getElementById('statInactive').textContent = data.stats.inactive;
                document.getElementById('statRoot').textContent = data.stats.root;
            }
            
            // Update URL without reload
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({}, '', newUrl);
            
            // Re-initialize status toggle and order input handlers
            initStatusToggle();
            initOrderInputs();
        }
    })
    .catch(err => {
        searchSpinner.style.display = 'none';
        console.error('Search error:', err);
    });
}

// Toggle status via AJAX
function initStatusToggle() {
    document.querySelectorAll('.status-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`/admin/digital-categories/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.textContent = data.status === 'active' ? 'Active' : 'Inactive';
                    this.classList.toggle('btn-success', data.status === 'active');
                    this.classList.toggle('btn-outline-secondary', data.status !== 'active');
                    this.dataset.status = data.status;
                    showToast(data.message || 'Status updated successfully', 'success');
                }
            });
        });
    });
}

// Toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 position-fixed`;
    toast.style.cssText = 'bottom: 20px; right: 20px; z-index: 9999;';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    setTimeout(() => toast.remove(), 5000);
}

// Order update functionality
function initOrderInputs() {
    let orderTimeout;
    document.querySelectorAll('.order-input').forEach(input => {
        input.addEventListener('change', function() {
            clearTimeout(orderTimeout);
            const id = this.dataset.id;
            const order = this.value;
            
            orderTimeout = setTimeout(() => {
                const orders = [];
                document.querySelectorAll('.order-input').forEach(inp => {
                    orders.push({ id: inp.dataset.id, order: inp.value });
                });
                
                fetch('{{ route("admin.digital-categories.update-order") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ orders: orders })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Order updated successfully');
                        }
                    }
                })
                .catch(err => {
                    console.error('Order update error:', err);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to update order');
                    }
                });
            }, 500);
        });
    });
}

// Initialize on page load
initStatusToggle();
initOrderInputs();
</script>
@endpush
