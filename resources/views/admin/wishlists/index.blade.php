@extends('admin.layouts.app')

@section('title', 'Wishlist Management')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Wishlist Management</h4>
        <small class="text-muted">Manage customer wishlists and track product interests</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.wishlists.export') }}" class="btn btn-outline-primary">
            <i class="bi bi-download me-1"></i> Export All
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-heart-fill"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Total Wishlist Items</span>
                <span class="stat-card-value" id="stat-total">{{ $stats['total_wishlists'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="bi bi-box-seam"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Unique Products</span>
                <span class="stat-card-value" id="stat-products">{{ $stats['unique_products'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Active Customers</span>
                <span class="stat-card-value" id="stat-users">{{ $stats['unique_users'] }}</span>
            </div>
        </div>
    </div>
</div>

    <!-- Filter Card -->
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
                                   placeholder="Product name, SKU, Customer..." value="{{ request('search') }}">
                            <span class="input-group-text" id="searchSpinner" style="display: none;">
                                <div class="spinner-border spinner-border-sm"></div>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Product Filter -->
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Product</label>
                        <select name="product" id="filterProduct" class="form-select form-select-sm">
                            <option value="">All Products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>
                                    {{ Str::limit($product->name, 30) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- User Filter -->
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Customer</label>
                        <select name="user" id="filterUser" class="form-select form-select-sm">
                            <option value="">All Customers</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                    {{ Str::limit($user->name, 20) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Sort By</label>
                        <select name="sort" id="filterSort" class="form-select form-select-sm">
                            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date Added</option>
                            <option value="user_id" {{ request('sort') == 'user_id' ? 'selected' : '' }}>Customer</option>
                            <option value="product_id" {{ request('sort') == 'product_id' ? 'selected' : '' }}>Product</option>
                        </select>
                    </div>

                    <!-- Direction -->
                    <div class="col-lg-1 col-md-2 col-sm-4">
                        <label class="form-label small text-muted">Order</label>
                        <select name="direction" id="filterDirection" class="form-select form-select-sm">
                            <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Desc</option>
                            <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Asc</option>
                        </select>
                    </div>
                    
                    <!-- Reset Button -->
                    <div class="col-lg-2 col-md-4 col-sm-8">
                        <a href="{{ route('admin.wishlists.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div class="card border-0 shadow-sm mb-3" id="bulkActionsCard" style="display: none;">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted"><span id="selectedCount">0</span> selected</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="selectAll()">
                        Select All {{ $wishlists->total() }} Items
                    </button>
                    <button type="button" class="btn btn-sm btn-link" onclick="clearSelection()">Clear</button>
                </div>
                <div class="d-flex gap-2">
                    <select id="bulkActionSelect" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Bulk Actions</option>
                        <option value="delete">Delete Selected</option>
                        <option value="export">Export Selected</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-primary" onclick="performBulkAction()">
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Wishlists Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll()">
                            </th>
                            <th>Customer Name</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Date Added</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @include('admin.wishlists.partials.table-rows', ['wishlists' => $wishlists])
                    </tbody>
                </table>
            </div>
            
            @if($wishlists->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $wishlists->firstItem() ?? 0 }} - {{ $wishlists->lastItem() ?? 0 }} of {{ $wishlists->total() }} entries
                </div>
                <div>
                    {{ $wishlists->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

<!-- Hidden form for bulk actions -->
<form id="bulkActionForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ids" id="bulkIdsInput">
</form>

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let selectedItems = new Set();
    let allSelected = false;

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Live search
        const searchInput = document.getElementById('liveSearch');
        const searchSpinner = document.getElementById('searchSpinner');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchSpinner.style.display = 'block';
            
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 300);
        });

        // Filter dropdowns
        ['filterProduct', 'filterUser', 'filterSort', 'filterDirection'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', performSearch);
            }
        });
    });

    function performSearch() {
        const searchTerm = document.getElementById('liveSearch').value;
        const product = document.getElementById('filterProduct').value;
        const user = document.getElementById('filterUser').value;
        const sort = document.getElementById('filterSort').value;
        const direction = document.getElementById('filterDirection').value;

        const params = new URLSearchParams();
        if (searchTerm) params.set('search', searchTerm);
        if (product) params.set('product', product);
        if (user) params.set('user', user);
        if (sort) params.set('sort', sort);
        if (direction) params.set('direction', direction);
        params.set('per_page', '{{ $wishlists->perPage() }}');

        fetch(`{{ route('admin.wishlists.index') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('searchSpinner').style.display = 'none';
            
            if (data.html) {
                document.querySelector('#tableBody').innerHTML = data.html;
                
                // Update pagination - find the card-footer inside the table card
                if (data.pagination) {
                    const paginationContainer = document.querySelector('.card-body .card-footer');
                    if (paginationContainer) {
                        paginationContainer.outerHTML = data.pagination;
                    }
                }
                
                // Update stats
                if (data.stats) {
                    document.getElementById('stat-total').textContent = data.stats.total_wishlists;
                    document.getElementById('stat-products').textContent = data.stats.unique_products;
                    document.getElementById('stat-users').textContent = data.stats.unique_users;
                }

                // Update URL
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);

                // Clear selection after search
                clearSelection();
            }
        })
        .catch(err => {
            document.getElementById('searchSpinner').style.display = 'none';
            console.error('Search error:', err);
        });
    }

    // Selection functions
    function toggleCheckbox(id) {
        if (selectedItems.has(id)) {
            selectedItems.delete(id);
        } else {
            selectedItems.add(id);
        }
        updateBulkActions();
    }

    function toggleSelectAll() {
        const checkbox = document.getElementById('selectAllCheckbox');
        
        if (checkbox.checked) {
            // Select all on current page
            document.querySelectorAll('.wishlist-checkbox').forEach(cb => {
                cb.checked = true;
                selectedItems.add(parseInt(cb.value));
            });
            allSelected = true;
        } else {
            clearSelection();
        }
        updateBulkActions();
    }

    function clearSelection() {
        selectedItems.clear();
        allSelected = false;
        document.querySelectorAll('.wishlist-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAllCheckbox').checked = false;
        updateBulkActions();
    }

    function updateBulkActions() {
        const count = selectedItems.size;
        const bulkCard = document.getElementById('bulkActionsCard');
        const countSpan = document.getElementById('selectedCount');
        
        if (count > 0) {
            bulkCard.style.display = 'block';
            countSpan.textContent = count;
        } else {
            bulkCard.style.display = 'none';
        }
    }

    function performBulkAction() {
        const action = document.getElementById('bulkActionSelect').value;
        
        if (!action) {
            alert('Please select an action');
            return;
        }

        if (selectedItems.size === 0) {
            alert('Please select at least one item');
            return;
        }

        let confirmMsg = '';
        switch (action) {
            case 'delete':
                confirmMsg = `Are you sure you want to delete ${selectedItems.size} wishlist item(s)?`;
                break;
            case 'export':
                confirmMsg = `Export ${selectedItems.size} wishlist item(s) to CSV?`;
                break;
        }

        if (confirm(confirmMsg)) {
            document.getElementById('bulkActionInput').value = action;
            document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
            
            if (action === 'export') {
                // For export, submit form and get file download
                const form = document.getElementById('bulkActionForm');
                form.action = '{{ route("admin.wishlists.bulk-action") }}';
                form.submit();
                
                setTimeout(() => {
                    clearSelection();
                    performSearch();
                }, 1000);
            } else {
                // For delete, use AJAX
                fetch('{{ route("admin.wishlists.bulk-action") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: action,
                        ids: Array.from(selectedItems)
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        clearSelection();
                        performSearch();
                    } else {
                        alert(data.message || 'An error occurred');
                    }
                });
            }
        }
    }

    // Delete single item
    function deleteWishlist(id) {
        if (confirm('Are you sure you want to remove this item from wishlist?')) {
            fetch(`{{ route('admin.wishlists.destroy-single', '') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    performSearch();
                } else {
                    alert(data.message || 'An error occurred');
                }
            });
        }
    }
</script>
@endpush
