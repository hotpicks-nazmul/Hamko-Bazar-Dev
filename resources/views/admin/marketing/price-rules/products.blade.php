@extends('admin.layouts.app')

@section('title', 'Manage Products - ' . $priceRule->name)

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Manage Products</h4>
        <small class="text-muted">{{ $priceRule->name }}</small>
    </div>
    <a href="{{ route('admin.marketing.price-rules.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Price Rules
    </a>
</div>

<form method="POST" action="{{ route('admin.marketing.price-rules.products.update', $priceRule->id) }}" id="itemForm">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Products Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Select Products</h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" onclick="selectAll()">Select All</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="deselectAll()">Deselect All</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleAll(this)">
                                        </th>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Discount Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        @php
                                            $pivotData = $product->pivot ?? null;
                                            $isSelected = $pivotData !== null;
                                            $discount = $pivotData ? $pivotData->discount : $priceRule->discount_value;
                                            $discountType = $pivotData ? $pivotData->discount_type : $priceRule->discount_type;
                                        @endphp
                                        <tr class="{{ $isSelected ? 'table-primary' : '' }}">
                                            <td>
                                                <input type="checkbox" 
                                                       class="form-check-input product-checkbox product-select" 
                                                       name="products[{{ $product->id }}][selected]" 
                                                       value="1"
                                                       {{ $isSelected ? 'checked' : '' }}
                                                       data-product-id="{{ $product->id }}"
                                                       onchange="toggleProductRow(this)">
                                                <input type="hidden" name="products[{{ $product->id }}][product_id]" value="{{ $product->id }}">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($product->thumbnail_image)
                                                        <img src="{{ $product->thumbnail_image }}" alt="{{ $product->name }}" class="me-2 rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light me-2 rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <i class="bi bi-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $product->name }}</strong>
                                                        @if($product->sku)
                                                            <br><small class="text-muted">SKU: {{ $product->sku }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-primary">${{ number_format($product->unit_price, 2) }}</span>
                                            </td>
                                            <td style="min-width: 100px;">
                                                <input type="number" 
                                                       class="form-control form-control-sm" 
                                                       name="products[{{ $product->id }}][discount]" 
                                                       value="{{ $discount }}"
                                                       step="0.01"
                                                       min="0"
                                                       placeholder="0"
                                                       {{ !$isSelected ? 'disabled' : '' }}>
                                            </td>
                                            <td style="min-width: 120px;">
                                                <select class="form-select form-select-sm" 
                                                        name="products[{{ $product->id }}][discount_type]"
                                                        {{ !$isSelected ? 'disabled' : '' }}>
                                                    <option value="percent" {{ $discountType === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                                                    <option value="fixed" {{ $discountType === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="mt-2 text-muted">No products available</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Add Product
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="d-flex justify-content-center mb-3">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Rule Information</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-{{ $priceRule->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($priceRule->status) }}
                        </span>
                    </div>
                    @if($priceRule->start_date)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Start:</span>
                        <strong>{{ $priceRule->start_date->format('M d, Y h:i A') }}</strong>
                    </div>
                    @endif
                    @if($priceRule->end_date)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">End:</span>
                        <strong>{{ $priceRule->end_date->format('M d, Y h:i A') }}</strong>
                    </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Selected Products:</span>
                        <strong id="selectedCount">{{ $priceRule->products->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-0">
                        <span class="text-muted">Discount:</span>
                        <strong>
                            @if($priceRule->discount_type === 'percent')
                                {{ $priceRule->discount_value }}%
                            @else
                                ${{ number_format($priceRule->discount_value, 2) }}
                            @endif
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Instructions Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Instructions</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0 small text-muted">
                        <li class="mb-2">Check the products you want to add to this price rule</li>
                        <li class="mb-2">Enter discount value (percentage or fixed amount)</li>
                        <li class="mb-2">Click "Update Products" to save changes</li>
                        <li class="mb-0">Leave empty to use the rule's default discount</li>
                    </ol>
                </div>
            </div>

            <!-- Discount Examples -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Discount Examples</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2">
                            <strong>10% off:</strong> Enter "10" as discount, select "Percentage"
                        </div>
                        <div class="mb-2">
                            <strong>$5 off:</strong> Enter "5" as discount, select "Fixed Amount"
                        </div>
                        <div class="mb-0">
                            <strong>25% off:</strong> Enter "25" as discount, select "Percentage"
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.marketing.price-rules.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="itemForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Products
    </button>
</div>
@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
    .product-checkbox {
        width: 18px;
        height: 18px;
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleAll(checkbox) {
        const checkboxes = document.querySelectorAll('.product-select');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
            toggleProductRow(cb);
        });
        updateSelectedCount();
    }

    function selectAll() {
        const checkboxes = document.querySelectorAll('.product-select');
        checkboxes.forEach(cb => {
            cb.checked = true;
            toggleProductRow(cb);
        });
        document.getElementById('selectAllCheckbox').checked = true;
        updateSelectedCount();
    }

    function deselectAll() {
        const checkboxes = document.querySelectorAll('.product-select');
        checkboxes.forEach(cb => {
            cb.checked = false;
            toggleProductRow(cb);
        });
        document.getElementById('selectAllCheckbox').checked = false;
        updateSelectedCount();
    }

    function toggleProductRow(checkbox) {
        const row = checkbox.closest('tr');
        const inputs = row.querySelectorAll('input:not(.product-select), select');
        
        if (checkbox.checked) {
            row.classList.add('table-primary');
            inputs.forEach(input => {
                if (input.type !== 'checkbox' || input.classList.contains('product-select')) {
                    input.disabled = false;
                }
            });
        } else {
            row.classList.remove('table-primary');
            inputs.forEach(input => {
                if (!input.classList.contains('product-select')) {
                    input.disabled = true;
                }
            });
        }
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.product-select:checked').length;
        document.getElementById('selectedCount').textContent = checked;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.product-select');
        checkboxes.forEach(cb => {
            toggleProductRow(cb);
        });
        
        // Update "Select All" checkbox state
        const allCheckboxes = document.querySelectorAll('.product-select');
        const checkedCheckboxes = document.querySelectorAll('.product-select:checked');
        document.getElementById('selectAllCheckbox').checked = allCheckboxes.length === checkedCheckboxes.length && allCheckboxes.length > 0;
    });
</script>
@endpush
