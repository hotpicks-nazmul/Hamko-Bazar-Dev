@extends('admin.layouts.app')

@section('title', 'Edit Coupon')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Coupon: <span class="text-primary">{{ $coupon->code }}</span></h4>
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="couponForm" action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Coupon Code -->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Coupon Code <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" name="code" id="code" class="form-control form-control-lg text-uppercase" value="{{ old('code', $coupon->code) }}" required placeholder="e.g., SUMMER2024">
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
                                <input type="radio" class="btn-check" name="type" id="typePercentage" value="percentage" {{ old('type', $coupon->type) == 'percentage' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="typePercentage">
                                    <i class="bi bi-percent me-1"></i> Percentage
                                </label>
                                <input type="radio" class="btn-check" name="type" id="typeFixed" value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'checked' : '' }}>
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
                                <span class="input-group-text" id="valuePrefix">{{ $coupon->type === 'percentage' ? '%' : '৳' }}</span>
                                <input type="number" name="value" id="value" class="form-control" value="{{ old('value', $coupon->value) }}" step="0.01" min="0" required>
                            </div>
                            @error('value')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Max Discount (for percentage) -->
                    <div class="row mb-3" id="maxDiscountRow" style="{{ $coupon->type === 'fixed' ? 'display:none' : '' }}">
                        <label class="col-sm-3 col-form-label fw-semibold">Max Discount</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" name="max_discount" class="form-control" value="{{ old('max_discount', $coupon->max_discount) }}" step="0.01" min="0" placeholder="Optional - Maximum discount amount">
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
                                <input type="number" name="min_order_amount" class="form-control" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" step="0.01" min="0" placeholder="Optional - Minimum order amount">
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
                            <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit', $coupon->usage_limit) }}" min="0" placeholder="Leave empty for unlimited uses">
                            <small class="text-muted">Maximum number of times this coupon can be used. Already used: {{ $coupon->used_count ?? 0 }} times.</small>
                            @error('usage_limit')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Validity Period -->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Valid From</label>
                        <div class="col-sm-9">
                            <input type="datetime-local" name="start_date" class="form-control" value="{{ old('start_date', $coupon->start_date?->format('Y-m-d\TH:i')) }}">
                            <small class="text-muted">Leave empty for immediate availability.</small>
                            @error('start_date')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Expires At</label>
                        <div class="col-sm-9">
                            <input type="datetime-local" name="end_date" class="form-control" value="{{ old('end_date', $coupon->end_date?->format('Y-m-d\TH:i')) }}">
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
                                <option value="active" {{ old('status', $coupon->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $coupon->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Usage Stats -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bar-chart me-1"></i> Usage Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Times Used:</span>
                    <strong>{{ $coupon->used_count ?? 0 }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Usage Limit:</span>
                    <strong>{{ $coupon->usage_limit ?? 'Unlimited' }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Remaining:</span>
                    <strong class="{{ ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) ? 'text-danger' : 'text-success' }}">
                        @if($coupon->usage_limit)
                            {{ max(0, $coupon->usage_limit - ($coupon->used_count ?? 0)) }}
                        @else
                            Unlimited
                        @endif
                    </strong>
                </div>
            </div>
        </div>
        
        <!-- Status Info -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-1"></i> Status</h6>
            </div>
            <div class="card-body">
                @if($coupon->status !== 'active')
                    <div class="alert alert-warning mb-0 py-2">
                        <i class="bi bi-pause-circle me-1"></i> This coupon is currently inactive.
                    </div>
                @elseif($coupon->end_date && $coupon->end_date->isPast())
                    <div class="alert alert-danger mb-0 py-2">
                        <i class="bi bi-x-circle me-1"></i> This coupon has expired.
                    </div>
                @elseif($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)
                    <div class="alert alert-warning mb-0 py-2">
                        <i class="bi bi-exclamation-triangle me-1"></i> Usage limit reached.
                    </div>
                @else
                    <div class="alert alert-success mb-0 py-2">
                        <i class="bi bi-check-circle me-1"></i> This coupon is active and available.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

<!-- Floating Save Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <a href="#" class="btn btn-outline-danger floating-reset-btn" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this coupon?')) { document.getElementById('deleteForm').submit(); }">
        <i class="bi bi-trash me-1"></i> Delete
    </a>
    <button type="submit" form="couponForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Coupon
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
</script>
@endpush
