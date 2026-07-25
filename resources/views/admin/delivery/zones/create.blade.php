@extends('admin.layouts.app')

@section('title', 'Create Delivery Zone')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create Delivery Zone</h4>
    <a href="{{ route('admin.delivery.zones.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Zones
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.delivery.zones.store') }}" method="POST" id="zoneForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Zone Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., Dhaka Metro">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Internal name for this zone</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="auto-generated if empty">
                        </div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Leave empty to auto-generate from name</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2" placeholder="Optional description for this zone">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="region" class="form-label">Region Name</label>
                            <input type="text" class="form-control" id="region" name="region" value="{{ old('region') }}" placeholder="e.g., Dhaka Metro">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="area_type" class="form-label">Area Type</label>
                            <select class="form-select" id="area_type" name="area_type">
                                <option value="zone" {{ old('area_type') == 'zone' ? 'selected' : '' }}>Custom Zone</option>
                                <option value="nationwide" {{ old('area_type') == 'nationwide' ? 'selected' : '' }}>Nationwide</option>
                                <option value="regional" {{ old('area_type') == 'regional' ? 'selected' : '' }}>Regional</option>
                                <option value="city" {{ old('area_type') == 'city' ? 'selected' : '' }}>City</option>
                                <option value="district" {{ old('area_type') == 'district' ? 'selected' : '' }}>District</option>
                                <option value="thana" {{ old('area_type') == 'thana' ? 'selected' : '' }}>Thana/Upazila</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Location -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Location</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" class="form-control" id="country" name="country" value="{{ old('country', 'Bangladesh') }}" form="zoneForm">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="state" class="form-label">State/Division</label>
                        <input type="text" class="form-control" id="state" name="state" value="{{ old('state') }}" placeholder="e.g., Dhaka, Chittagong" form="zoneForm">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}" placeholder="e.g., Dhaka, Sylhet" form="zoneForm">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="postal_code" class="form-label">Postal Code</label>
                        <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" placeholder="e.g., 1200" form="zoneForm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping Rates -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-currency-exchange me-2"></i>Shipping Rates</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="shipping_cost_type" class="form-label">Shipping Cost Type</label>
                        <select class="form-select" id="shipping_cost_type" name="shipping_cost_type" form="zoneForm">
                            <option value="flat" {{ old('shipping_cost_type', 'flat') == 'flat' ? 'selected' : '' }}>Flat Rate</option>
                            <option value="weight" {{ old('shipping_cost_type') == 'weight' ? 'selected' : '' }}>Based on Weight</option>
                            <option value="free" {{ old('shipping_cost_type') == 'free' ? 'selected' : '' }}>Free Shipping</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3" id="shippingCostField">
                        <label for="shipping_cost" class="form-label">Shipping Cost ({{ config('app.currency_symbol', '৳') }})</label>
                        <input type="number" class="form-control" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost', 0) }}" min="0" step="0.01" form="zoneForm">
                    </div>
                    
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="free_shipping_enabled" name="free_shipping_enabled" {{ old('free_shipping_enabled') ? 'checked' : '' }} form="zoneForm">
                            <label class="form-check-label" for="free_shipping_enabled">
                                Enable free shipping above order amount
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3" id="freeShippingThresholdField" style="{{ old('free_shipping_enabled') ? '' : 'display:none;' }}">
                        <label for="free_shipping_threshold" class="form-label">Free Shipping Threshold ({{ config('app.currency_symbol', '৳') }})</label>
                        <input type="number" class="form-control" id="free_shipping_threshold" name="free_shipping_threshold" value="{{ old('free_shipping_threshold', 0) }}" min="0" step="0.01" form="zoneForm">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="min_order_amount" class="form-label">Minimum Order Amount ({{ config('app.currency_symbol', '৳') }})</label>
                        <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" value="{{ old('min_order_amount', 0) }}" min="0" step="0.01" form="zoneForm">
                        <div class="form-text">Minimum order amount (0 = no minimum)</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="max_order_weight" class="form-label">Maximum Order Weight (kg)</label>
                        <input type="number" class="form-control" id="max_order_weight" name="max_order_weight" value="{{ old('max_order_weight') }}" min="0" step="0.1" form="zoneForm">
                    </div>
                </div>
            </div>
        </div>

        <!-- COD Settings -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-cash me-2"></i>Cash on Delivery (COD)</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cod_enabled" name="cod_enabled" {{ old('cod_enabled', true) ? 'checked' : '' }} form="zoneForm">
                            <label class="form-check-label" for="cod_enabled">
                                Enable Cash on Delivery for this zone
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3" id="codChargeField" style="{{ old('cod_enabled', true) ? '' : 'display:none;' }}">
                        <label for="cod_charge_type" class="form-label">COD Charge Type</label>
                        <select class="form-select" id="cod_charge_type" name="cod_charge_type" form="zoneForm">
                            <option value="flat" {{ old('cod_charge_type', 'flat') == 'flat' ? 'selected' : '' }}>Flat Rate</option>
                            <option value="percentage" {{ old('cod_charge_type') == 'percentage' ? 'selected' : '' }}>Percentage of Order</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3" id="codChargeValueField" style="{{ old('cod_enabled', true) ? '' : 'display:none;' }}">
                        <label for="cod_charge" class="form-label">COD Charge Value</label>
                        <input type="number" class="form-control" id="cod_charge" name="cod_charge" value="{{ old('cod_charge', 0) }}" min="0" step="0.01" form="zoneForm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Time -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Delivery Time</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="estimated_days" class="form-label">Estimated Delivery Days</label>
                        <input type="number" class="form-control" id="estimated_days" name="estimated_days" value="{{ old('estimated_days', 3) }}" min="1" max="30" form="zoneForm">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="delivery_time_start" class="form-label">Delivery Time Start</label>
                        <input type="text" class="form-control" id="delivery_time_start" name="delivery_time_start" value="{{ old('delivery_time_start') }}" placeholder="e.g., 9 AM" form="zoneForm">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="delivery_time_end" class="form-label">Delivery Time End</label>
                        <input type="text" class="form-control" id="delivery_time_end" name="delivery_time_end" value="{{ old('delivery_time_end') }}" placeholder="e.g., 6 PM" form="zoneForm">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Status -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-toggle-on me-2"></i>Status</h6>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} form="zoneForm">
                    <label class="form-check-label" for="is_active">
                        <i class="bi bi-check-circle text-success me-1"></i> Active
                    </label>
                    <div class="form-text">Only active zones will be available for delivery</div>
                </div>
                
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }} form="zoneForm">
                    <label class="form-check-label" for="is_default">
                        <i class="bi bi-star text-warning me-1"></i> Default Zone
                    </label>
                    <div class="form-text">The default zone is used when no other zone matches</div>
                </div>
            </div>
        </div>
        
        <!-- Sorting -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-sort-down me-2"></i>Display Order</h6>
            </div>
            <div class="card-body">
                <label for="sort_order" class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" form="zoneForm">
                <div class="form-text">Higher values appear first</div>
            </div>
        </div>
        
        <!-- Tips -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small text-muted">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Create zones for different areas with specific rates</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Set up free shipping for minimum order amounts</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Configure COD charges per zone</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i> Use the default zone for areas not covered</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.delivery.zones.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="zoneForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Zone
    </button>
</div>
@endsection

@push('styles')
<style>
.content-area {
    padding-bottom: 100px !important;
}
</style>
@endpush

@push('scripts')
<script>
    // Auto-generate slug from name
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    nameInput.addEventListener('input', function() {
        if (!slugInput.dataset.manual) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    });
    
    slugInput.addEventListener('input', function() {
        this.dataset.manual = 'true';
    });
    
    // Toggle shipping cost field based on type
    document.getElementById('shipping_cost_type').addEventListener('change', function() {
        const costField = document.getElementById('shippingCostField');
        if (this.value === 'free') {
            costField.style.display = 'none';
            costField.querySelector('input').value = 0;
        } else {
            costField.style.display = 'block';
        }
    });
    
    // Toggle free shipping threshold field
    document.getElementById('free_shipping_enabled').addEventListener('change', function() {
        const thresholdField = document.getElementById('freeShippingThresholdField');
        thresholdField.style.display = this.checked ? 'block' : 'none';
    });
    
    // Toggle COD fields
    document.getElementById('cod_enabled').addEventListener('change', function() {
        const codFields = ['codChargeField', 'codChargeValueField'];
        codFields.forEach(id => {
            document.getElementById(id).style.display = this.checked ? 'block' : 'none';
        });
    });
</script>
@endpush
