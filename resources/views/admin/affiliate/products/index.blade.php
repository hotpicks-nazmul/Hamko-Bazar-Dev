@extends('admin.layouts.app')

@section('title', 'Affiliate Products')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-box"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Products</span>
            <span class="stat-card-value">{{ number_format($stats['total'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value">{{ number_format($stats['active'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value">{{ number_format($stats['inactive'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Avg. Price</span>
            <span class="stat-card-value">${{ number_format($stats['avg_price'] ?? 0, 2) }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Affiliate Products</h4>
    <a href="{{ route('admin.affiliate.products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add New Product
    </a>
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
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
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
                
                <!-- Category Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Category</label>
                    <select name="category" id="filterCategory" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($affiliateCategories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Clear Filters -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <a href="{{ route('admin.affiliate.products.index') }}" class="btn btn-sm btn-outline-secondary">
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
            <table class="table table-hover align-middle mb-0" id="affiliateProductsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                        </th>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Commission</th>
                        <th>Clicks</th>
                        <th>Conversions</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($products as $product)
                    @php
                        $search = request('search');
                        $isMatch = $search && (
                            stripos($product->name, $search) !== false || 
                            stripos($product->description ?? '', $search) !== false
                        );
                    @endphp
                    <tr class="{{ $isMatch ? 'table-warning' : '' }}">
                        <td>
                            <input type="checkbox" class="form-check-input row-checkbox" value="{{ $product->id }}">
                        </td>
                        <td>{{ $product->id }}</td>
                        <td>
                            @php
                                $imageUrl = $product->image;
                                if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                    $imageUrl = '/storage/' . $imageUrl;
                                }
                            @endphp
                            @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-image text-white"></i>
                            </div>
                            @endif
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>
                            @if($product->category)
                            <span class="badge bg-secondary">{{ $product->category->name }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>${{ number_format($product->price, 2) }}</td>
                        <td><span class="badge bg-info">{{ $product->commission_rate }}%</span></td>
                        <td>{{ number_format($product->clicks) }}</td>
                        <td>{{ number_format($product->conversions) }}</td>
                        <td>
                            @if($product->status === 'active')
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.affiliate.products.show', $product->id) }}" class="btn btn-sm btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.affiliate.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.affiliate.products.destroy', $product->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <i class="bi bi-folder text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No products found</p>
                            <a href="{{ route('admin.affiliate.products.create') }}" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-plus-lg me-1"></i> Add First Product
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination inside card-body -->
        @if($products->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $products->firstItem() }} - {{ $products->lastItem() }} of {{ $products->total() }} items
            </div>
            <div>
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all checkbox
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                document.querySelectorAll('.row-checkbox').forEach(function(checkbox) {
                    checkbox.checked = this.checked;
                }.bind(this));
            });
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
                if (searchSpinner) searchSpinner.style.display = 'block';
                
                // Debounce - wait 300ms after user stops typing
                searchTimeout = setTimeout(() => {
                    performLiveSearch(searchTerm);
                }, 300);
            });
        }
        
        // Filter dropdowns trigger search on change
        const filterSelects = ['filterStatus', 'filterCategory'];
        filterSelects.forEach(function(id) {
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
            const status = document.getElementById('filterStatus');
            if (status && status.value) params.set('status', status.value);
            
            const category = document.getElementById('filterCategory');
            if (category && category.value) params.set('category', category.value);
            
            // Keep existing sort and per_page
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
            if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
            
            // AJAX request
            fetch(`{{ route('admin.affiliate.products.index') }}?${params.toString()}&ajax=1`, {
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
                    
                    // Update URL without reload
                    const newUrl = `${window.location.pathname}?${params.toString()}`;
                    window.history.pushState({}, '', newUrl);
                } else {
                    // Fallback: submit form normally
                    document.getElementById('filterForm').submit();
                }
            })
            .catch(() => {
                if (searchSpinner) searchSpinner.style.display = 'none';
                // Fallback: submit form normally
                document.getElementById('filterForm').submit();
            });
        }
    });
</script>
@endpush
