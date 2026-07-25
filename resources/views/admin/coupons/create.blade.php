@extends('admin.layouts.app')

@section('title', 'Create Coupon')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create New Coupon</h4>
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="couponForm" action="{{ route('admin.coupons.store') }}" method="POST">
                    @csrf
                    
                    <!-- Coupon Code -->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Coupon Code <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="text" name="code" id="code" class="form-control form-control-lg text-uppercase" value="{{ old('code') }}" required placeholder="e.g., SUMMER2024">
                                <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">
                                    <i class="bi bi-shuffle me-1"></i> Generate
                                </button>
                            </div>
                            @error('code')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Discount Type -->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Discount Type <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type" id="typePercentage" value="percentage" {{ old('type', 'percentage') == 'percentage' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="typePercentage">
                                    <i class="bi bi-percent me-1"></i> Percentage
                                </label>
                                <input type="radio" class="btn-check" name="type" id="typeFixed" value="fixed" {{ old('type') == 'fixed' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="typeFixed">
                                    <i class="bi bi-currency-dollar me-1"></i> Fixed Amount
                                </label>
                            </div>
                            @error('type')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Discount Value -->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Discount Value <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <span class="input-group-text" id="valuePrefix">%</span>
                                <input type="number" name="value" id="value" class="form-control" value="{{ old('value') }}" step="0.01" min="0" required>
                            </div>
                            @error('value')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Max Discount (for percentage) -->
                    <div class="row mb-3" id="maxDiscountRow">
                        <label class="col-sm-3 col-form-label fw-semibold">Max Discount</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" name="max_discount" class="form-control" value="{{ old('max_discount') }}" step="0.01" min="0" placeholder="Optional - Maximum discount amount">
                            </div>
                            <small class="text-muted">Maximum discount amount for percentage coupons. Leave empty for no limit.</small>
                            @error('max_discount')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Minimum Order -->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Minimum Order</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" name="min_order_amount" class="form-control" value="{{ old('min_order_amount') }}" step="0.01" min="0" placeholder="Optional - Minimum order amount">
                            </div>
                            <small class="text-muted">Minimum order total required to use this coupon.</small>
                            @error('min_order_amount')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Usage Limit -->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Usage Limit</label>
                        <div class="col-sm-9">
                            <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit') }}" min="0" placeholder="Leave empty for unlimited uses">
                            <small class="text-muted">Maximum number of times this coupon can be used.</small>
                            @error('usage_limit')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Validity Period -->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Valid From</label>
                        <div class="col-sm-9">
                            <input type="datetime-local" name="start_date" class="form-control" value="{{ old('start_date') }}">
                            <small class="text-muted">Leave empty for immediate availability.</small>
                            @error('start_date')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Expires At</label>
                        <div class="col-sm-9">
                            <input type="datetime-local" name="end_date" class="form-control" value="{{ old('end_date') }}">
                            <small class="text-muted">Leave empty for no expiration.</small>
                            @error('end_date')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Active Status -->
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label fw-semibold">Status</label>
                        <div class="col-sm-9">
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <hr>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-1"></i> Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Use uppercase letters for coupon codes
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Percentage discounts are applied to the order total
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Fixed discounts are subtracted directly from the total
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Set a max discount for percentage coupons to limit the discount amount
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Usage limit helps create exclusive offers
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="couponForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Coupon
    </button>
</div>
@endsection

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
    // Auto-scroll to first error field
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->any())
            var firstErrorField = document.querySelector('.is-invalid');
            if (firstErrorField) {
                setTimeout(function() {
                    firstErrorField.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstErrorField.focus();
                }, 100);
            }
        @endif
    });

function generateCode() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < 8; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('code').value = code;
}

// Toggle max discount field visibility based on type
document.querySelectorAll('input[name="type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const maxDiscountRow = document.getElementById('maxDiscountRow');
        const valuePrefix = document.getElementById('valuePrefix');
        if (this.value === 'percentage') {
            maxDiscountRow.style.display = '';
            valuePrefix.textContent = '%';
        } else {
            maxDiscountRow.style.display = 'none';
            valuePrefix.textContent = '৳';
        }
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const checkedType = document.querySelector('input[name="type"]:checked');
    if (checkedType && checkedType.value === 'fixed') {
        document.getElementById('maxDiscountRow').style.display = 'none';
        document.getElementById('valuePrefix').textContent = '৳';
    }
});
</script>
@endpush
