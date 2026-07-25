@extends('admin.layouts.app')

@section('title', 'Products Wishlist Report')

@section('content')
<div class="mb-4">
    <h4 class="mb-0"><i class="bi bi-heart me-2"></i>Products Wishlist Report</h4>
    <p class="text-muted mb-0">Product-wise wishlist analysis - ranked by popularity</p>
</div>

<!-- Summary Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon">
            <i class="bi bi-heart-fill"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Wishlists</span>
            <span class="stat-card-value">{{ number_format($totalWishlists) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon">
            <i class="bi bi-box-seam"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Unique Products</span>
            <span class="stat-card-value">{{ number_format($uniqueProducts) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Unique Users</span>
            <span class="stat-card-value">{{ number_format($uniqueUsers) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon">
            <i class="bi bi-graph-up"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Avg. Wishlists/Product</span>
            <span class="stat-card-value">{{ $avgWishlistPerProduct }}</span>
        </div>
    </div>
</div>

<!-- Top Wishlisted Product Alert -->
@if($topProduct)
<div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center">
    <i class="bi bi-trophy-fill text-warning fs-4 me-3"></i>
    <div>
        <strong>Most Wishlisted Product:</strong> {{ $topProduct->name }}
        <span class="mx-2">|</span>
        <span class="text-muted">{{ number_format(\App\Models\Wishlist::where('product_id', $topProduct->id)->count()) }} wishlists</span>
    </div>
</div>
@endif

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
                               placeholder="Search by product name or SKU..." value="{{ $search }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                
                <!-- Date Range -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Date Range</label>
                    <input type="text" name="date_range" id="dateRange" class="form-control form-control-sm"
                           placeholder="Select date range" value="{{ $dateRange }}">
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
                        <option value="wishlist_desc" {{ $sortBy == 'wishlist_desc' ? 'selected' : '' }}>Most Wishlisted</option>
                        <option value="wishlist_asc" {{ $sortBy == 'wishlist_asc' ? 'selected' : '' }}>Least Wishlisted</option>
                        <option value="users_desc" {{ $sortBy == 'users_desc' ? 'selected' : '' }}>Most Users</option>
                        <option value="users_asc" {{ $sortBy == 'users_asc' ? 'selected' : '' }}>Least Users</option>
                        <option value="newest" {{ $sortBy == 'newest' ? 'selected' : '' }}>Recently Added</option>
                        <option value="oldest" {{ $sortBy == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    </select>
                </div>
                
                <!-- Filter Buttons -->
                <div class="col-lg-4 col-md-5 col-sm-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.wishlist') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                        <a href="{{ route('admin.reports.wishlist.export', request()->query()) }}" class="btn btn-sm btn-outline-success">
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
        <h6 class="mb-0"><i class="bi bi-table me-2"></i>Product Wishlist Details</h6>
        <span class="text-muted small">Showing {{ $wishlists->firstItem() ?? 0 }} - {{ $wishlists->lastItem() ?? 0 }} of {{ $wishlists->total() }} products</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 80px;">Image</th>
                        <th>Product Name</th>
                        <th class="text-center" style="width: 120px;">Category</th>
                        <th class="text-end" style="width: 120px;">Price</th>
                        <th class="text-center" style="width: 120px;">Wishlists</th>
                        <th class="text-center" style="width: 120px;">Unique Users</th>
                        <th class="text-center" style="width: 150px;">First Added</th>
                        <th class="text-center" style="width: 150px;">Last Added</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($wishlists as $index => $item)
                    <tr>
                        <td>{{ $wishlists->firstItem() + $index }}</td>
                        <td>
                            @php
                                $imageUrl = $productData[$item->product_id]['featured_image'] ?? null;
                                if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                    $imageUrl = '/storage/' . $imageUrl;
                                }
                            @endphp
                            @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $productData[$item->product_id]['name'] ?? 'Product' }}" 
                                 class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-image text-white"></i>
                            </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-medium">{{ $productData[$item->product_id]['name'] ?? 'N/A' }}</div>
                            @if(isset($productData[$item->product_id]['sku']) && $productData[$item->product_id]['sku'])
                            <small class="text-muted">SKU: {{ $productData[$item->product_id]['sku'] }}</small>
                            @endif
                            @if(isset($productData[$item->product_id]['status']))
                            <span class="badge {{ $productData[$item->product_id]['status'] ? 'bg-success' : 'bg-secondary' }} ms-1">
                                {{ $productData[$item->product_id]['status'] ? 'Active' : 'Inactive' }}
                            </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="text-muted">{{ $productData[$item->product_id]['category'] ?? 'N/A' }}</span>
                        </td>
                        <td class="text-end">
                            ৳{{ number_format($productData[$item->product_id]['price'] ?? 0, 2) }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger">{{ number_format($item->wishlist_count) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ number_format($item->unique_users) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-muted small">
                                @if($item->first_wishlisted)
                                    {{ \Carbon\Carbon::parse($item->first_wishlisted)->format('d M Y') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="text-muted small">
                                @if($item->last_wishlisted)
                                    {{ \Carbon\Carbon::parse($item->last_wishlisted)->format('d M Y') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-heart text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No wishlist data found</p>
                            <p class="text-muted small">Try adjusting your filter criteria</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($wishlists->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <div class="text-muted small">
                Page {{ $wishlists->currentPage() }} of {{ $wishlists->lastPage() }}
            </div>
            <div>
                {{ $wishlists->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date range picker
    const dateRangeInput = document.getElementById('dateRange');
    if (dateRangeInput) {
        flatpickr(dateRangeInput, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            allowInput: true,
            placeholder: 'Select date range'
        });
    }
    
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
                filterForm.submit();
            }, 500);
        });
    }

    // Auto-submit on category change
    const categorySelect = document.querySelector('select[name="category"]');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }

    // Auto-submit on sort change
    const sortSelect = document.querySelector('select[name="sort"]');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }
    
    // Quick date presets
    document.querySelectorAll('.date-preset').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const days = parseInt(this.dataset.days);
            const startInput = document.querySelector('input[name="start_date"]');
            const endInput = document.querySelector('input[name="end_date"]');
            
            if (startInput && endInput) {
                const end = new Date();
                const start = new Date();
                start.setDate(start.getDate() - days);
                
                startInput.value = start.toISOString().split('T')[0];
                endInput.value = end.toISOString().split('T')[0];
                
                filterForm.submit();
            }
        });
    });
});
</script>
@endpush
