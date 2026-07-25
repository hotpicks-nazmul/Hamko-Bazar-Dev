@extends('admin.layouts.app')

@section('title', 'Inventory Management')

@section('content')

<div class="stat-card-row stat-card-row-6 mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-box"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Total Products</span><span class="stat-card-value" id="statTotalProducts">{{ $stats['total_products'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-stack"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Total Stock</span><span class="stat-card-value" id="statTotalStock">{{ number_format($stats['total_stock'] ?? 0) }}</span></div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">In Stock</span><span class="stat-card-value" id="statInStock">{{ $stats['in_stock'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Low Stock</span><span class="stat-card-value" id="statLowStock">{{ $stats['low_stock'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Out of Stock</span><span class="stat-card-value" id="statOutStock">{{ $stats['out_of_stock'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Stock Value</span><span class="stat-card-value" id="statValue">{{ config('app.currency_symbol', '৳') }}{{ number_format($stats['total_value'] ?? 0, 2) }}</span></div>
    </div>
</div>

<!-- Filter Form -->
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
                               placeholder="Name, SKU..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                
                <!-- Category Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Category</label>
                    <select name="category" id="filterCategory" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Stock Status Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Stock Status</label>
                    <select name="stock_status" id="filterStockStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <!-- Sort -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Sort By</label>
                    <select name="sort" id="filterSort" class="form-select form-select-sm">
                        <option value="updated_at" {{ request('sort') === 'updated_at' ? 'selected' : '' }}>Last Updated</option>
                        <option value="quantity" {{ request('sort') === 'quantity' ? 'selected' : '' }}>Stock Level</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name</option>
                        <option value="price" {{ request('sort') === 'price' ? 'selected' : '' }}>Price</option>
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div class="card border-0 shadow-sm mb-3" id="bulkActionsBar" style="display: none;">
    <div class="card-body py-2">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted"><span id="selectedCount">0</span> selected</span>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="clearSelection()">
                    Clear
                </button>
            </div>
            <div class="d-flex gap-2">
                @php $canAdjust = auth()->user()->hasPermission('inventory.adjust-stock'); @endphp
                <button type="button" class="btn btn-sm btn-success" onclick="showBulkAdjustModal('add')" {{ $canAdjust ? '' : 'disabled' }}>
                    <i class="bi bi-plus-lg me-1"></i> Add Stock
                </button>
                <button type="button" class="btn btn-sm btn-warning" onclick="showBulkAdjustModal('subtract')" {{ $canAdjust ? '' : 'disabled' }}>
                    <i class="bi bi-dash-lg me-1"></i> Remove Stock
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="showBulkAdjustModal('set')" {{ $canAdjust ? '' : 'disabled' }}>
                    <i class="bi bi-arrow-repeat me-1"></i> Set Stock
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>Product</th>
                        <th>Product Code</th>
                        <th>Category</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.inventory.partials.table-rows', ['products' => $products])
                </tbody>
            </table>
        </div>
        
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

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="adjustModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjustForm">
                <div class="modal-body">
                    <input type="hidden" id="adjustProductId" name="product_id">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" id="adjustProductName" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" id="adjustCurrentStock" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                        <select name="adjustment_type" id="adjustmentType" class="form-select" required>
                            <option value="add">Add Stock (+)</option>
                            <option value="subtract">Remove Stock (-)</option>
                            <option value="set">Set Stock (=)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="adjustQuantity" class="form-control" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="Optional note">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Adjustment Modal -->
<div class="modal fade" id="bulkAdjustModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Stock Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkAdjustForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Adjusting stock for <span id="bulkSelectedCount">0</span> selected product(s)
                    </div>
                    <input type="hidden" name="product_ids" id="bulkProductIds">
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                        <select name="adjustment_type" id="bulkAdjustmentType" class="form-select" required>
                            <option value="add">Add Stock (+)</option>
                            <option value="subtract">Remove Stock (-)</option>
                            <option value="set">Set Stock (=)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="bulkQuantity" class="form-control" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="Optional note">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Threshold Modal -->
<div class="modal fade" id="thresholdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Low Stock Threshold</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="thresholdForm">
                <div class="modal-body">
                    <input type="hidden" id="thresholdProductId" name="product_id">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" id="thresholdProductName" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Low Stock Threshold <span class="text-danger">*</span></label>
                        <input type="number" name="low_stock_threshold" id="thresholdValue" class="form-control" min="0" required>
                        <div class="form-text">Product will be marked as "Low Stock" when quantity falls at or below this value.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Threshold
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
</style>
@endpush

@push('scripts')
<script>
    let selectedItems = new Set();

    // Live Search
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        searchSpinner.style.display = 'block';
        searchTimeout = setTimeout(() => {
            performLiveSearch(searchTerm);
        }, 300);
    });

    // Filter dropdowns
    ['filterCategory', 'filterStockStatus', 'filterSort'].forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                performLiveSearch(searchInput.value.trim());
            });
        }
    });

    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();
        
        if (searchTerm) params.set('search', searchTerm);
        
        const category = document.getElementById('filterCategory').value;
        if (category) params.set('category', category);
        
        const stockStatus = document.getElementById('filterStockStatus').value;
        if (stockStatus) params.set('stock_status', stockStatus);

        const sort = document.getElementById('filterSort').value;
        if (sort) params.set('sort', sort);

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
        
        fetch(`{{ route('admin.inventory.index') }}?${params.toString()}&ajax=1`, {
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
            
            if (data.pagination) {
                const paginationContainer = document.querySelector('.card-footer');
                if (paginationContainer) {
                    paginationContainer.innerHTML = data.pagination;
                }
            }
        })
        .catch(() => {
            searchSpinner.style.display = 'none';
            document.getElementById('filterForm').submit();
        });
    }

    // Selection
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        
        if (selectAll.checked) {
            checkboxes.forEach(cb => {
                cb.checked = true;
                selectedItems.add(cb.value);
            });
        } else {
            checkboxes.forEach(cb => {
                cb.checked = false;
                selectedItems.delete(cb.value);
            });
        }
        updateBulkActions();
    }

    function toggleItem(checkbox) {
        if (checkbox.checked) {
            selectedItems.add(checkbox.value);
        } else {
            selectedItems.delete(checkbox.value);
        }
        updateBulkActions();
    }

    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
        document.getElementById('bulkSelectedCount').textContent = count;
    }

    function clearSelection() {
        selectedItems.clear();
        document.getElementById('selectAll').checked = false;
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
        updateBulkActions();
    }

    // Quick Adjust Modal
    function showAdjustModal(productId) {
        fetch(`{{ route('admin.inventory.product', ':id') }}`.replace(':id', productId))
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('adjustProductId').value = data.product.id;
                    document.getElementById('adjustProductName').value = data.product.name;
                    document.getElementById('adjustCurrentStock').value = data.product.quantity;
                    document.getElementById('adjustQuantity').value = '';
                    document.getElementById('adjustmentType').value = 'add';
                    new bootstrap.Modal(document.getElementById('adjustModal')).show();
                }
            })
            .catch(err => {
                console.error('Error:', err);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to load product data. Please refresh the page and try again.');
                } else {
                    alert('Failed to load product data. Please refresh the page and try again.');
                }
            });
    }

    // Single item adjustment
    document.getElementById('adjustForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const productId = document.getElementById('adjustProductId').value;

        fetch('{{ route('admin.inventory.adjust') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('adjustModal')).hide();
                // Redirect with success message as URL parameter
                setTimeout(() => {
                    window.location.href = '{{ route('admin.inventory.index') }}?success=Stock+adjusted+successfully&type=success';
                }, 300);
            } else {
                alert(data.message || 'Failed to adjust stock');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Failed to adjust stock');
        });
    });

    // Check for success message in URL on page load
    window.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');
        const type = urlParams.get('type') || 'success';

        if (success) {
            if (typeof adminToast === 'function') {
                adminToast(type, type === 'success' ? 'Success' : 'Error', decodeURIComponent(success));
            }
            // Remove the query parameter from URL without reload
            const newUrl = window.location.pathname;
            window.history.replaceState({}, '', newUrl);
        }
    });

    // Bulk Adjust Modal
    function showBulkAdjustModal(type) {
        if (selectedItems.size === 0) {
            if (typeof toastr !== 'undefined') {
                toastr.warning('Please select at least one product');
            } else {
                alert('Please select at least one product');
            }
            return;
        }
        
        document.getElementById('bulkProductIds').value = JSON.stringify(Array.from(selectedItems));
        document.getElementById('bulkAdjustmentType').value = type;
        document.getElementById('bulkQuantity').value = '';
        new bootstrap.Modal(document.getElementById('bulkAdjustModal')).show();
    }

    // Bulk adjustment
    document.getElementById('bulkAdjustForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('{{ route('admin.inventory.bulk-adjust') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('bulkAdjustModal')).hide();
                clearSelection();
                setTimeout(() => {
                    window.location.href = '{{ route('admin.inventory.index') }}?success=' + encodeURIComponent(data.message) + '&type=success';
                }, 300);
            } else {
                alert(data.message || 'Failed to adjust stock');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Failed to adjust stock');
        });
    });

    // Threshold Modal
    function showThresholdModal(productId, currentThreshold) {
        fetch(`{{ route('admin.inventory.product', ':id') }}`.replace(':id', productId))
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('thresholdProductId').value = data.product.id;
                    document.getElementById('thresholdProductName').value = data.product.name;
                    document.getElementById('thresholdValue').value = data.product.low_stock_threshold || 10;
                    new bootstrap.Modal(document.getElementById('thresholdModal')).show();
                }
            })
            .catch(err => {
                console.error('Error:', err);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to load product data. Please refresh the page and try again.');
                } else {
                    alert('Failed to load product data. Please refresh the page and try again.');
                }
            });
    }

    // Threshold form submission
    document.getElementById('thresholdForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('{{ route('admin.inventory.threshold') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('thresholdModal')).hide();
                setTimeout(() => {
                    window.location.href = '{{ route('admin.inventory.index') }}?success=' + encodeURIComponent(data.message) + '&type=success';
                }, 300);
            } else {
                alert(data.message || 'Failed to update threshold');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Failed to update threshold');
        });
    });
    }

    // Change per page
    function changePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }
</script>
@endpush
@endsection
