@extends('admin.layouts.app')

@section('title', 'In-House Product Sale Report')

@section('content')
<div class="mb-4">
    <h4 class="mb-0"><i class="bi bi-house-door me-2"></i>In-House Product Sale Report</h4>
    <p class="text-muted mb-0">Product-wise sales analysis for in-house orders</p>
</div>

<!-- Summary Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon">
            <i class="bi bi-cart-plus"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Products Sold</span>
            <span class="stat-card-value">{{ number_format($totalQtySold) }}</span>
        </div>
    </div>
    @if(auth()->user()->hasPermission('view-sales'))
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon">
            <i class="bi bi-currency-dollar"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Sales</span>
            <span class="stat-card-value">৳{{ number_format($totalSales, 2) }}</span>
        </div>
    </div>
    @endif
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon">
            <i class="bi bi-receipt"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Orders</span>
            <span class="stat-card-value">{{ number_format($totalOrders) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon">
            <i class="bi bi-box-seam"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Unique Products</span>
            <span class="stat-card-value">{{ number_format($uniqueProducts) }}</span>
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
                               placeholder="Search by product name..." value="{{ $search }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                
                <!-- Start Date -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" 
                           value="{{ $startDate }}">
                </div>
                
                <!-- End Date -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" 
                           value="{{ $endDate }}">
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
                
                <!-- Sort By -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Sort By</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="qty_desc" {{ $sortBy == 'qty_desc' ? 'selected' : '' }}>Qty (High to Low)</option>
                        <option value="qty_asc" {{ $sortBy == 'qty_asc' ? 'selected' : '' }}>Qty (Low to High)</option>
                        <option value="sales_desc" {{ $sortBy == 'sales_desc' ? 'selected' : '' }}>Sales (High to Low)</option>
                        <option value="sales_asc" {{ $sortBy == 'sales_asc' ? 'selected' : '' }}>Sales (Low to High)</option>
                        <option value="name_asc" {{ $sortBy == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name_desc" {{ $sortBy == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                    </select>
                </div>
                
                <!-- Filter Buttons -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.in-house-product-sale') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                        <a href="{{ route('admin.reports.in-house-product-sale.export', request()->query()) }}" class="btn btn-sm btn-outline-success">
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
        <h6 class="mb-0"><i class="bi bi-table me-2"></i>Product Sales Details</h6>
        <span class="text-muted small">Showing {{ $productSales->firstItem() ?? 0 }} - {{ $productSales->lastItem() ?? 0 }} of {{ $productSales->total() }} products</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Product Name</th>
                        <th class="text-center" style="width: 120px;">Orders</th>
                        <th class="text-end" style="width: 120px;">Qty Sold</th>
                        @if(auth()->user()->hasPermission('view-sales'))
                        <th class="text-end" style="width: 150px;">Avg. Price</th>
                        @endif
                        @if(auth()->user()->hasPermission('view-sales'))
                        <th class="text-end" style="width: 150px;">Total Sales</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($productSales as $index => $item)
                    <tr>
                        <td>{{ $productSales->firstItem() + $index }}</td>
                        <td>
                            <a href="{{ route('admin.products.show', $item->product_id) }}" class="text-decoration-none">
                                <div class="fw-medium text-primary">{{ $item->product_name }}</div>
                            </a>
                            @if($item->product && $item->product->sku)
                            <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ $item->order_count }}</span>
                        </td>
                        <td class="text-end">
                            <span class="fw-medium">{{ number_format($item->total_qty) }}</span>
                        </td>
                        @if(auth()->user()->hasPermission('view-sales'))
                        <td class="text-end">৳{{ number_format($item->avg_price, 2) }}</td>
                        @endif
                        @if(auth()->user()->hasPermission('view-sales'))
                        <td class="text-end">
                            <span class="fw-bold text-success">৳{{ number_format($item->total_sales, 2) }}</span>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No product sales data found</p>
                            <p class="text-muted small">Try adjusting your filter criteria</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($productSales->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <div class="text-muted small">
                Page {{ $productSales->currentPage() }} of {{ $productSales->lastPage() }}
            </div>
            <div>
                {{ $productSales->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table td {
        font-size: 0.9rem;
    }
    .badge {
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live search with debounce
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const filterForm = document.getElementById('filterForm');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            
            // Show spinner
            if (searchSpinner) {
                searchSpinner.style.display = 'block';
            }
            
            // Debounce - wait 500ms after user stops typing
            searchTimeout = setTimeout(() => {
                if (searchSpinner) {
                    searchSpinner.style.display = 'none';
                }
                // Auto-submit form on search
                filterForm.submit();
            }, 500);
        });
    }
    
    // Filter dropdown changes trigger auto-submit
    const filterSelects = filterForm.querySelectorAll('select[name="category"], select[name="sort"]');
    filterSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            filterForm.submit();
        });
    });
    
    // Date inputs trigger auto-submit on change
    const dateInputs = filterForm.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            filterForm.submit();
        });
    });
    
});
</script>
@endpush
