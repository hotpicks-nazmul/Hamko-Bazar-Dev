@extends('admin.layouts.app')

@section('title', 'Bulk Discount')

@section('content')
@if(auth()->user()->hasPermission('products') && auth()->user()->hasPermission('products.view'))
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-percent me-2"></i>Bulk Discount</h4>
        <p class="text-muted mb-0">Apply and manage product discounts</p>
    </div>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" id="discountTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="apply-tab" data-bs-toggle="tab" data-bs-target="#applyDiscount" type="button" role="tab">
            <i class="bi bi-plus-circle me-1"></i> Apply Discount
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="manage-tab" data-bs-toggle="tab" data-bs-target="#manageDiscounts" type="button" role="tab">
            <i class="bi bi-list-ul me-1"></i> Manage Discounts
            <span class="badge bg-primary ms-1">{{ $productsWithDiscounts->total() }}</span>
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="discountTabsContent">
    <!-- Apply Discount Tab -->
    <div class="tab-pane fade show active" id="applyDiscount" role="tabpanel">
        <div class="row">
            <div class="col-lg-8">
                <!-- Discount Settings Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Discount Settings</h6>
                    </div>
                    <div class="card-body">
                        <form id="discountForm" method="POST" action="{{ route('admin.products.bulk-discount.apply') }}">
                            @csrf
                            
                            <!-- Discount Type & Value -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                                    <select name="discount_type" id="discountType" class="form-select @error('discount_type') is-invalid @enderror" required>
                                        <option value="percentage">Percentage (%)</option>
                                        <option value="fixed">Fixed Amount</option>
                                    </select>
                                    @error('discount_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="discount_value" id="discountValue" class="form-control @error('discount_value') is-invalid @enderror" 
                                               placeholder="Enter value" step="0.01" min="0" required>
                                        <span class="input-group-text" id="discountTypeLabel">%</span>
                                    </div>
                                    <div class="form-text" id="discountHelp">Enter a percentage value (e.g., 10 for 10% off)</div>
                                    @error('discount_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Apply To Selection -->
                            <div class="mb-4">
                                <label class="form-label">Apply To <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="form-check card p-3 border">
                                    <input class="form-check-input" type="radio" name="apply_to" id="applyAll" value="all" checked>
                                    <label class="form-check-label w-100" for="applyAll">
                                        <i class="bi bi-box-seam text-primary me-2"></i>
                                        <strong>All Products</strong>
                                        <p class="text-muted small mb-0">Apply to all products</p>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check card p-3 border">
                                    <input class="form-check-input" type="radio" name="apply_to" id="applyCategory" value="category">
                                    <label class="form-check-label w-100" for="applyCategory">
                                        <i class="bi bi-folder text-success me-2"></i>
                                        <strong>By Category</strong>
                                        <p class="text-muted small mb-0">Select a category</p>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check card p-3 border">
                                    <input class="form-check-input" type="radio" name="apply_to" id="applySelected" value="selected">
                                    <label class="form-check-label w-100" for="applySelected">
                                        <i class="bi bi-check2-square text-info me-2"></i>
                                        <strong>Selected</strong>
                                        <p class="text-muted small mb-0">Choose specific products</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Selection (conditional) -->
                    <div class="mb-4" id="categorySelection" style="display: none;">
                        <label class="form-label">Select Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="categoryId" class="form-select">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Product Selection (conditional) -->
                    <div class="mb-4" id="productSelection" style="display: none;">
                        <label class="form-label">Select Products <span class="text-danger">*</span></label>
                        
                        <!-- Product Search -->
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" id="productSearch" class="form-control" placeholder="Search products by name or SKU..." oninput="debouncedSearch()">
                                <select id="productCategoryFilter" class="form-select" style="max-width: 200px;" onchange="debouncedSearch()">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Selected Products Count -->
                        <div class="alert alert-info py-2" id="selectedCount" style="display: none;">
                            <i class="bi bi-check-circle me-1"></i>
                            <span id="selectedCountText">0</span> product(s) selected
                            <button type="button" class="btn btn-sm btn-link p-0 ms-2" onclick="clearProductSelection()">Clear</button>
                        </div>

                        <!-- Product List -->
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAllProducts" onchange="toggleSelectAll()">
                                        </th>
                                        <th>
    <a href="javascript:void(0);" class="text-decoration-none text-dark">
        Product
        <i class="bi bi-caret-down-fill"></i>
    </a>
</th>
                                        <th>Product Code</th>
                                        <th>Price</th>
                                        <th>Sale Price</th>
                                        <th>% Applied</th>
                                    </tr>
                                </thead>
                                <tbody id="productList">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            Search for products to select
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Hidden input for selected product IDs -->
                        <div id="selectedProductIds"></div>
                    </div>

                    <!-- Discount Date Range -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Discount Start Date</label>
                            <input type="datetime-local" name="start_date" id="startDate" class="form-control">
                            <small class="text-muted">When the discount becomes active (optional)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount End Date</label>
                            <input type="datetime-local" name="end_date" id="endDate" class="form-control">
                            <small class="text-muted">When the discount expires (optional)</small>
                        </div>
                    </div>

                    <!-- Additional Options -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Price Range Filter</label>
                            <div class="input-group">
                                <input type="number" name="price_min" class="form-control" placeholder="Min Price" step="0.01">
                                <span class="input-group-text">to</span>
                                <input type="number" name="price_max" class="form-control" placeholder="Max Price" step="0.01">
                            </div>
                            <small class="text-muted">Only apply to products within this price range</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Options</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="active_only" id="activeOnly" value="1" checked>
                                <label class="form-check-label" for="activeOnly">
                                    Apply to active products only
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="clear_existing_sale_price" id="clearExisting" value="1">
                                <label class="form-check-label" for="clearExisting">
                                    Clear existing sale prices first
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="mb-4" id="previewSection" style="display: none;">
                        <label class="form-label">Discount Preview</label>
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Original Price:</small>
                                        <div class="h5 mb-0" id="previewOriginal">৳100.00</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">After Discount:</small>
                                        <div class="h5 mb-0 text-success" id="previewDiscounted">৳90.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                     <!-- Submit Button -->
                     <div class="d-flex gap-2">
                         <button type="button" class="btn btn-outline-danger" onclick="showRemoveDiscountModal()">
                             <i class="bi bi-x-circle me-1"></i> Remove Discounts
                         </button>
                     </div>
                </form>
            </div>
        </div>

        <!-- Results -->
        @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        </div>
        @endif

        @if(session('warning'))
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
            @if(session('discount_errors'))
                <ul class="mb-0 mt-2">
                    @foreach(session('discount_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Statistics Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Product Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Products:</span>
                    <strong>{{ $totalProducts }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Products on Sale:</span>
                    <strong id="productsOnSale">-</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Regular Price Only:</span>
                    <strong id="regularPriceOnly">-</strong>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Discounts</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Apply preset discounts quickly:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickDiscount(10)">
                        <i class="bi bi-percent me-1"></i> 10% Off All Products
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickDiscount(20)">
                        <i class="bi bi-percent me-1"></i> 20% Off All Products
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickDiscount(50)">
                        <i class="bi bi-percent me-1"></i> 50% Off All Products
                    </button>
                </div>
            </div>
        </div>

        <!-- Tips Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li class="mb-2">Percentage discounts are calculated from the regular price</li>
                    <li class="mb-2">Fixed amounts are subtracted from the regular price</li>
                    <li class="mb-2">Sale prices must be less than regular prices</li>
                    <li class="mb-2">Use "Clear existing sale prices" to reset before applying new discounts</li>
                    <li class="mb-2">Set date range to schedule discounts for specific periods</li>
                    <li class="mb-2">Discounts without dates are always active until removed</li>
                    <li>Products with existing sale prices will be updated</li>
                </ul>
            </div>
        </div>
    </div>
</div>
    </div>
    
    <!-- Manage Discounts Tab -->
    <div class="tab-pane fade" id="manageDiscounts" role="tabpanel">
        <!-- Discount Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="display-6 text-success">{{ $activeDiscounts }}</div>
                        <div class="text-muted">Active Discounts</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="display-6 text-warning">{{ $scheduledDiscounts }}</div>
                        <div class="text-muted">Scheduled</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="display-6 text-secondary">{{ $expiredDiscounts }}</div>
                        <div class="text-muted">Expired</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Discount List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Products with Discounts</h6>
                <div class="d-flex gap-2">
                    <select id="discountStatusFilter" class="form-select form-select-sm" style="width: auto;" onchange="filterDiscounts()">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>
    <a href="javascript:void(0);" class="text-decoration-none text-dark">
        Product
        <i class="bi bi-caret-down-fill"></i>
    </a>
</th>
                                <th>Product Code</th>
                                <th>Regular Price</th>
                                <th>Sale Price</th>
                                <th>Discount</th>
                                <th>Validity Period</th>
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="discountListBody">
                            @forelse($productsWithDiscounts as $product)
                             <tr data-id="{{ $product->id }}" 
                                 data-status="{{ $product->isOnSale() ? 'active' : ($product->isDiscountScheduled() ? 'scheduled' : 'expired') }}"
                                 data-starts-at="{{ $product->discount_starts_at ? $product->discount_starts_at->format('Y-m-d H:i:s') : '' }}"
                                 data-ends-at="{{ $product->discount_ends_at ? $product->discount_ends_at->format('Y-m-d H:i:s') : '' }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->featured_image)
                                        <img src="{{ asset('storage/' . $product->featured_image) }}" alt="{{ $product->name }}" 
                                             class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @endif
                                        <div>
                                            <div class="fw-medium">{{ $product->name }}</div>
                                            <small class="text-muted">{{ $product->category->name ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                 <td><code>{{ $product->product_code }}</code></td>
                                <td>৳{{ number_format($product->price, 2) }}</td>
                                <td class="text-success">৳{{ number_format($product->sale_price, 2) }}</td>
                                <td>
                                    @if($product->price > 0)
                                    <span class="badge bg-danger">{{ round((($product->price - $product->sale_price) / $product->price) * 100) }}%</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->discount_starts_at || $product->discount_ends_at)
                                        <small>
                                            @if($product->discount_starts_at)
                                                <span title="Starts"><i class="bi bi-play-circle text-success"></i> {{ $product->discount_starts_at->format('M d, Y H:i') }}</span><br>
                                            @endif
                                            @if($product->discount_ends_at)
                                                <span title="Ends"><i class="bi bi-stop-circle text-danger"></i> {{ $product->discount_ends_at->format('M d, Y H:i') }}</span>
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">No limit</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->isOnSale())
                                        <span class="badge bg-success">Active</span>
                                    @elseif($product->isDiscountScheduled())
                                        <span class="badge bg-warning text-dark">Scheduled</span>
                                    @else
                                        <span class="badge bg-secondary">Expired</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editDiscount({{ $product->id }})" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSingleDiscount({{ $product->id }})" title="Remove">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No products with discounts found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                 <!-- Pagination -->
                 @if($productsWithDiscounts->hasPages())
                 <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                     <div class="text-muted small">
                         Showing {{ $productsWithDiscounts->firstItem() }} - {{ $productsWithDiscounts->lastItem() }} of {{ $productsWithDiscounts->total() }} items
                     </div>
                     <div>
                         {{ $productsWithDiscounts->appends(request()->query())->links() }}
                     </div>
                 </div>
                 @endif
            </div>
        </div>
    </div>
</div>

<!-- Remove Discount Modal -->
<div class="modal fade" id="removeDiscountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Remove Discounts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.products.bulk-discount.remove') }}">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to remove sale prices from products?</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Remove From</label>
                        <select name="apply_to" class="form-select" required>
                            <option value="all">All Products</option>
                            <option value="category">Specific Category</option>
                            <option value="selected">Selected Products</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="removeCategorySelect" style="display: none;">
                        <label class="form-label">Select Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Remove Discounts</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Discount Modal -->
<div class="modal fade" id="editDiscountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDiscountForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="editProductId" name="product_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" id="editProductName" class="form-control" readonly>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Regular Price</label>
                            <input type="text" id="editRegularPrice" class="form-control" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Sale Price <span class="text-danger">*</span></label>
                            <input type="number" id="editSalePrice" name="sale_price" class="form-control" 
                                   step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Discount Start Date</label>
                        <input type="datetime-local" id="editStartDate" name="discount_starts_at" class="form-control">
                        <small class="text-muted">Leave empty for immediate effect</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Discount End Date</label>
                        <input type="datetime-local" id="editEndDate" name="discount_ends_at" class="form-control">
                        <small class="text-muted">Leave empty for no expiration</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Floating Action Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="discountForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-percent me-1"></i> Apply Discount
    </button>
</div>

@push('styles')
<style>
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let selectedProducts = new Set();
    let allProducts = [];
    let searchTimeout;

    // Debounced search
    function debouncedSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(searchProducts, 300);
    }

    // Discount type change
    document.getElementById('discountType').addEventListener('change', function() {
        const label = document.getElementById('discountTypeLabel');
        const help = document.getElementById('discountHelp');
        
        if (this.value === 'percentage') {
            label.textContent = '%';
            help.textContent = 'Enter a percentage value (e.g., 10 for 10% off)';
        } else {
            label.textContent = '$';
            help.textContent = 'Enter a fixed amount to subtract (e.g., 5 for $5 off)';
        }
        updatePreview();
    });

    // Discount value change
    document.getElementById('discountValue').addEventListener('input', updatePreview);

    function updatePreview() {
        const type = document.getElementById('discountType').value;
        const value = parseFloat(document.getElementById('discountValue').value) || 0;
        // Use first product price as sample, or fallback to 100
        let originalPrice = 100;
        if (window.allProducts && window.allProducts.length > 0) {
            originalPrice = parseFloat(window.allProducts[0].price) || 100;
        }
        let discountedPrice;

        if (type === 'percentage') {
            discountedPrice = originalPrice - (originalPrice * value / 100);
        } else {
            discountedPrice = originalPrice - value;
        }

        document.getElementById('previewOriginal').textContent = '৳' + originalPrice.toFixed(2);
        document.getElementById('previewDiscounted').textContent = '৳' + Math.max(0, discountedPrice).toFixed(2);
        document.getElementById('previewSection').style.display = value > 0 ? 'block' : 'none';
    }

    // Apply to selection change
    document.querySelectorAll('input[name="apply_to"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('categorySelection').style.display = this.value === 'category' ? 'block' : 'none';
            document.getElementById('productSelection').style.display = this.value === 'selected' ? 'block' : 'none';
            
            // Update required attributes
            document.getElementById('categoryId').required = this.value === 'category';
            
            // Auto-search when product selection becomes visible
            if (this.value === 'selected' && allProducts.length === 0) {
                searchProducts();
            }
        });
    });

     // Search products
     function searchProducts() {
         const search = document.getElementById('productSearch').value;
         const category = document.getElementById('productCategoryFilter').value;
         
         fetch(`{{ route('admin.products.bulk-discount.products') }}?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`)
             .then(res => res.json())
             .then(data => {
                 allProducts = data.products;
                 renderProductList(data.products);
                 updatePreview(); // Update preview when products change
             });
     }

    function renderProductList(products) {
        const tbody = document.getElementById('productList');
        
        if (products.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-3">
                        No products found
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = products.map(p => `
            <tr>
                <td>
                    <input type="checkbox" class="product-checkbox" value="${p.id}" 
                           ${selectedProducts.has(p.id) ? 'checked' : ''}
                           onchange="toggleProduct(${p.id})">
                </td>
                <td>
                    <div class="fw-medium">${p.name}</div>
                    <small class="text-muted">${p.category}</small>
                </td>
                <td><code>${p.sku}</code></td>
                <td>৳${parseFloat(p.price).toFixed(2)}</td>
                <td>
                    ${p.sale_price ? '<span class="text-success">৳' + parseFloat(p.sale_price).toFixed(2) + '</span>' : '<span class="text-muted">-</span>'}
                    ${p.discount_starts_at || p.discount_ends_at ? '<br><small class="text-info">' + formatDiscountDates(p.discount_starts_at, p.discount_ends_at, p.is_on_sale) + '</small>' : ''}
                </td>
                <td>
                    ${p.discount_percentage > 0 ? '<span class="badge bg-success">' + p.discount_percentage + '%</span>' : '<span class="text-muted">-</span>'}
                </td>
            </tr>
        `).join('');
    }

    function toggleProduct(id) {
        if (selectedProducts.has(id)) {
            selectedProducts.delete(id);
        } else {
            selectedProducts.add(id);
        }
        updateSelectedCount();
    }

    function toggleSelectAll() {
        const checkbox = document.getElementById('selectAllProducts');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        
        checkboxes.forEach(cb => {
            const id = parseInt(cb.value);
            if (checkbox.checked) {
                selectedProducts.add(id);
                cb.checked = true;
            } else {
                selectedProducts.delete(id);
                cb.checked = false;
            }
        });
        updateSelectedCount();
    }

    function clearProductSelection() {
        selectedProducts.clear();
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAllProducts').checked = false;
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const count = selectedProducts.size;
        const countDiv = document.getElementById('selectedCount');
        const countText = document.getElementById('selectedCountText');
        const idsContainer = document.getElementById('selectedProductIds');
        
        countDiv.style.display = count > 0 ? 'block' : 'none';
        countText.textContent = count;
        
        // Update hidden inputs
        idsContainer.innerHTML = Array.from(selectedProducts).map(id => 
            `<input type="hidden" name="product_ids[]" value="${id}">`
        ).join('');
    }

    // Quick discount
    function quickDiscount(percent) {
        document.getElementById('discountType').value = 'percentage';
        document.getElementById('discountValue').value = percent;
        document.querySelector('input[name="apply_to"][value="all"]').checked = true;
        document.getElementById('discountTypeLabel').textContent = '%';
        updatePreview();
    }

    // Remove discount modal
    function showRemoveDiscountModal() {
        new bootstrap.Modal(document.getElementById('removeDiscountModal')).show();
    }

    // Remove discount category toggle
    document.querySelector('#removeDiscountModal select[name="apply_to"]').addEventListener('change', function() {
        document.getElementById('removeCategorySelect').style.display = this.value === 'category' ? 'block' : 'none';
    });

    // Format discount dates for display
    function formatDiscountDates(startsAt, endsAt, isOnSale) {
        if (!startsAt && !endsAt) return '';
        
        const startDate = startsAt ? new Date(startsAt) : null;
        const endDate = endsAt ? new Date(endsAt) : null;
        
        let status = '';
        if (!isOnSale && startDate && new Date() < startDate) {
            status = '<span class="badge bg-warning text-dark">Scheduled</span> ';
        } else if (!isOnSale && endDate && new Date() > endDate) {
            status = '<span class="badge bg-secondary">Expired</span> ';
        } else if (isOnSale) {
            status = '<span class="badge bg-success">Active</span> ';
        }
        
        if (startDate && endDate) {
            return status + formatDate(startDate) + ' - ' + formatDate(endDate);
        } else if (startDate) {
            return status + 'From ' + formatDate(startDate);
        } else if (endDate) {
            return status + 'Until ' + formatDate(endDate);
        }
        return status;
    }
    
    function formatDate(date) {
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Filter discounts by status
    function filterDiscounts() {
        const filter = document.getElementById('discountStatusFilter').value;
        const rows = document.querySelectorAll('#discountListBody tr[data-status]');
        
        rows.forEach(row => {
            if (filter === 'all' || row.dataset.status === filter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Edit discount
    function editDiscount(productId) {
        // Find product data from the table
        const row = document.querySelector(`tr[data-id="${productId}"]`) || 
                    document.querySelector(`button[onclick="editDiscount(${productId})"]`).closest('tr');
        
        if (!row) {
            // Fetch product data via AJAX if not found in table
            fetch(`{{ route('admin.products.bulk-discount.products') }}?search=${productId}`)
                .then(res => res.json())
                .then(data => {
                    const product = data.products.find(p => p.id === productId);
                    if (product) {
                        populateEditModal(product);
                    }
                });
        } else {
            // Get data from row
            const cells = row.cells;
            document.getElementById('editProductId').value = productId;
            document.getElementById('editProductName').value = cells[0].querySelector('.fw-medium')?.textContent || '';
            document.getElementById('editRegularPrice').value = cells[2].textContent.replace(/[^\d.]/g, '');
            document.getElementById('editSalePrice').value = cells[3].textContent.replace(/[^\d.]/g, '');
            
            // Parse dates from row data attributes
            const startDateInput = document.getElementById('editStartDate');
            const endDateInput = document.getElementById('editEndDate');
            
            // Set dates from data attributes
            const startsAt = row.dataset.startsAt;
            const endsAt = row.dataset.endsAt;
            
            if (startsAt) {
                // Format: Y-m-d H:i:s -> Y-m-dTH:i for datetime-local input
                const [datePart, timePart] = startsAt.split(' ');
                startDateInput.value = `${datePart}T${timePart}`;
            } else {
                startDateInput.value = '';
            }
            
            if (endsAt) {
                // Format: Y-m-d H:i:s -> Y-m-dTH:i for datetime-local input
                const [datePart, timePart] = endsAt.split(' ');
                endDateInput.value = `${datePart}T${timePart}`;
            } else {
                endDateInput.value = '';
            }
        }
        
        new bootstrap.Modal(document.getElementById('editDiscountModal')).show();
    }

    function populateEditModal(product) {
        document.getElementById('editProductId').value = product.id;
        document.getElementById('editProductName').value = product.name;
        document.getElementById('editRegularPrice').value = product.price;
        document.getElementById('editSalePrice').value = product.sale_price || '';
        
        // Format dates for datetime-local input
        if (product.discount_starts_at) {
            document.getElementById('editStartDate').value = product.discount_starts_at.slice(0, 16);
        }
        if (product.discount_ends_at) {
            document.getElementById('editEndDate').value = product.discount_ends_at.slice(0, 16);
        }
        
        new bootstrap.Modal(document.getElementById('editDiscountModal')).show();
    }

    // Handle edit discount form submission
    document.getElementById('editDiscountForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const productId = document.getElementById('editProductId').value;
        const salePrice = document.getElementById('editSalePrice').value;
        const startDate = document.getElementById('editStartDate').value;
        const endDate = document.getElementById('editEndDate').value;
        
        fetch(`{{ url('admin/products') }}/${productId}/discount`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                sale_price: salePrice,
                discount_starts_at: startDate || null,
                discount_ends_at: endDate || null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('editDiscountModal')).hide();
                location.reload(); // Reload to show updated data
            } else {
                alert('Error: ' + (data.message || 'Failed to update discount'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('An error occurred while updating the discount');
        });
    });

    // Remove single product discount
    function removeSingleDiscount(productId) {
        if (!confirm('Are you sure you want to remove the discount from this product?')) {
            return;
        }
        
        fetch(`{{ url('admin/products') }}/${productId}/discount`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show updated data
            } else {
                alert('Error: ' + (data.message || 'Failed to remove discount'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('An error occurred while removing the discount');
        });
    }

    // Load initial statistics
    fetch('{{ route('admin.products.bulk-discount.products') }}')
        .then(res => res.json())
        .then(data => {
            const products = data.products || [];
            const onSale = products.filter(p => p.sale_price).length;
            document.getElementById('productsOnSale').textContent = onSale;
            document.getElementById('regularPriceOnly').textContent = products.length - onSale;
        })
        .catch(() => {
            document.getElementById('productsOnSale').textContent = '0';
            document.getElementById('regularPriceOnly').textContent = '0';
        });

    // Initial preview update
    updatePreview();

    // Tab state persistence
    document.addEventListener('DOMContentLoaded', function() {
      // Restore tab
      const activeTab = localStorage.getItem('activeDiscountTab');
      if (activeTab) {
        const tabEl = document.querySelector(`button[data-bs-target="${activeTab}"]`);
        if (tabEl) {
          new bootstrap.Tab(tabEl).show();
        }
      }

      // Save tab when shown
      const tabButtons = document.querySelectorAll('#discountTabs button[data-bs-toggle="tab"]');
      tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', event => {
          localStorage.setItem('activeDiscountTab', event.target.getAttribute('data-bs-target'));
        });
      });
    });
</script>
@endpush
@else
<div class="alert alert-warning border-0 shadow-sm mb-4">
    <i class="bi bi-shield-exclamation me-2"></i>
    <strong>Access Restricted:</strong> You don't have permission to access products.
    Please contact an administrator if you need access.
</div>
@endif
@endsection