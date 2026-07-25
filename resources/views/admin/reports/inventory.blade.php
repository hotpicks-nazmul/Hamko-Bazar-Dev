@extends('admin.layouts.app')

@section('title', 'Products Stock Report')

@section('content')
<div class="mb-4">
    <h4 class="mb-0"><i class="bi bi-boxes me-2"></i>Products Stock Report</h4>
    <p class="text-muted mb-0">Inventory overview and stock management</p>
</div>

<!-- Summary Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon">
            <i class="bi bi-box-seam"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Products</span>
            <span class="stat-card-value">{{ number_format($totalProducts) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">In Stock</span>
            <span class="stat-card-value">{{ number_format($inStockCount) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Low Stock</span>
            <span class="stat-card-value">{{ number_format($lowStockCount) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon">
            <i class="bi bi-x-circle"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Out of Stock</span>
            <span class="stat-card-value">{{ number_format($outOfStockCount) }}</span>
        </div>
    </div>
</div>

<!-- Stock Value Summary -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon">
            <i class="bi bi-currency-dollar"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Stock Quantity</span>
            <span class="stat-card-value">{{ number_format($totalStockQty) }} units</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Stock Value (Cost)</span>
            <span class="stat-card-value">৳{{ number_format($totalStockValue, 2) }}</span>
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
                    <label class="form-label small text-muted">Search Product</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Search by name or SKU..." value="{{ $search }}">
                    </div>
                </div>
                
                <!-- Category Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Stock Status Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Stock Status</label>
                    <select name="stock_status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="in_stock" {{ $stockStatus == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ $stockStatus == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ $stockStatus == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                
                <!-- Sort By -->
                <div class="col-lg-2 col-md-2 col-sm-6">
                    <label class="form-label small text-muted">Sort By</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="stock_asc" {{ $sortBy == 'stock_asc' ? 'selected' : '' }}>Stock (Low to High)</option>
                        <option value="stock_desc" {{ $sortBy == 'stock_desc' ? 'selected' : '' }}>Stock (High to Low)</option>
                        <option value="name_asc" {{ $sortBy == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name_desc" {{ $sortBy == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="price_asc" {{ $sortBy == 'price_asc' ? 'selected' : '' }}>Price (Low to High)</option>
                        <option value="price_desc" {{ $sortBy == 'price_desc' ? 'selected' : '' }}>Price (High to Low)</option>
                    </select>
                </div>
                
                <!-- Per Page -->
                <div class="col-lg-1 col-md-2 col-sm-6">
                    <label class="form-label small text-muted">Per Page</label>
                    <select name="per_page" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 || !request('per_page') ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                
                <!-- Filter Buttons -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.inventory') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                        <a href="{{ route('admin.reports.inventory.export', request()->query()) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download me-1"></i> Export
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0"><i class="bi bi-table me-2"></i>Product Inventory Details</h6>
        <span class="text-muted small">Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Product</th>
                        <th class="text-center" style="width: 100px;">Product Code</th>
                        <th class="text-center" style="width: 120px;">Category</th>
                        <th class="text-center" style="width: 100px;">Stock</th>
                        <th class="text-center" style="width: 100px;">Low Stock Threshold</th>
                        <th class="text-center" style="width: 100px;">Status</th>
                        <th class="text-end" style="width: 120px;">Price</th>
                        <th class="text-end" style="width: 120px;">Stock Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $index => $product)
                    <tr>
                        <td>{{ $products->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @php
                                    $imageUrl = $product->featured_image;
                                    if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                        $imageUrl = '/storage/' . $imageUrl;
                                    }
                                @endphp
                                @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                    <i class="bi bi-image text-white"></i>
                                </div>
                                @endif
                                <div>
                                    <div class="fw-medium">{{ $product->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center" style="white-space: nowrap;">
    <div class="small text-truncate" style="max-width: 120px;">
        <span class="badge bg-primary">{{ $product->sku ?? 'N/A' }}</span>
    </div>
</td>
                        <td class="text-center">
                            {{ $product->category->name ?? 'Uncategorized' }}
                        </td>
                        <td class="text-center">
                            <span class="fw-medium">{{ number_format($product->quantity ?? 0) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-muted">{{ $product->low_stock_threshold ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            @if($product->quantity <= 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($product->quantity <= $product->low_stock_threshold)
                                <span class="badge bg-warning">Low Stock</span>
                            @else
                                <span class="badge bg-success">In Stock</span>
                            @endif
                        </td>
                        <td class="text-end">৳{{ number_format($product->price ?? 0, 2) }}</td>
                        <td class="text-end">
                            <span class="fw-medium">৳{{ number_format(($product->quantity ?? 0) * ($product->cost_price ?? $product->price ?? 0), 2) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No products found</p>
                            <p class="text-muted small">Try adjusting your filter criteria</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination & Per Page -->
        @if(isset($products) && method_exists($products, 'hasPages') && $products->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show:</span>
                <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 || !request('per_page') ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="text-muted small">per page</span>
            </div>
            <div>
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Change per page
    function changePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // Live search functionality
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const filterForm = document.getElementById('filterForm');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterForm.submit();
            }, 500);
        });
    }
</script>
@endpush
@endsection
