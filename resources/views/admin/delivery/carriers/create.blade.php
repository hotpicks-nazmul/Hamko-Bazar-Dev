@extends('admin.layouts.app')

@section('title', 'Add Carrier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-truck me-2"></i>Add New Carrier</h4>
    <a href="{{ route('admin.delivery.carriers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Carriers
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
                <form action="{{ route('admin.delivery.carriers.store') }}" method="POST" enctype="multipart/form-data" id="carrierForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Carrier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., FedEx, UPS, DHL">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">The slug will be auto-generated from the name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="auto-generated-if-empty">
                        </div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Leave empty to auto-generate from name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Enter carrier description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="carrier_type" class="form-label">Carrier Type</label>
                            <select class="form-select @error('carrier_type') is-invalid @enderror" id="carrier_type" name="carrier_type">
                                <option value="all" {{ old('carrier_type') === 'all' ? 'selected' : '' }}>All Types</option>
                                <option value="international" {{ old('carrier_type') === 'international' ? 'selected' : '' }}>International</option>
                                <option value="regional" {{ old('carrier_type') === 'regional' ? 'selected' : '' }}>Regional</option>
                                <option value="local" {{ old('carrier_type') === 'local' ? 'selected' : '' }}>Local</option>
                                <option value="express" {{ old('carrier_type') === 'express' ? 'selected' : '' }}>Express</option>
                                <option value="freight" {{ old('carrier_type') === 'freight' ? 'selected' : '' }}>Freight</option>
                            </select>
                            @error('carrier_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="service_type" class="form-label">Service Type</label>
                            <select class="form-select @error('service_type') is-invalid @enderror" id="service_type" name="service_type">
                                <option value="all" {{ old('service_type') === 'all' ? 'selected' : '' }}>All Services</option>
                                <option value="express" {{ old('service_type') === 'express' ? 'selected' : '' }}>Express Delivery</option>
                                <option value="standard" {{ old('service_type') === 'standard' ? 'selected' : '' }}>Standard Delivery</option>
                                <option value="economy" {{ old('service_type') === 'economy' ? 'selected' : '' }}>Economy Delivery</option>
                                <option value="overnight" {{ old('service_type') === 'overnight' ? 'selected' : '' }}>Overnight Delivery</option>
                                <option value="international" {{ old('service_type') === 'international' ? 'selected' : '' }}>International Shipping</option>
                                <option value="freight" {{ old('service_type') === 'freight' ? 'selected' : '' }}>Freight</option>
                            </select>
                            @error('service_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="estimated_delivery_days" class="form-label">Estimated Delivery Days</label>
                        <input type="text" class="form-control @error('estimated_delivery_days') is-invalid @enderror" id="estimated_delivery_days" name="estimated_delivery_days" value="{{ old('estimated_delivery_days') }}" placeholder="e.g., 3-5 days, 24 hours">
                        @error('estimated_delivery_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>
        
        <!-- API Configuration -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-key me-2"></i>API Configuration</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="api_mode" class="form-label">API Mode</label>
                        <select class="form-select @error('api_mode') is-invalid @enderror" id="api_mode" name="api_mode" form="carrierForm">
                            <option value="sandbox" {{ old('api_mode', 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                            <option value="production" {{ old('api_mode') === 'production' ? 'selected' : '' }}>Production (Live)</option>
                        </select>
                        @error('api_mode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="account_number" class="form-label">Account Number</label>
                        <input type="text" class="form-control @error('account_number') is-invalid @enderror" id="account_number" name="account_number" value="{{ old('account_number') }}" placeholder="Carrier account number" form="carrierForm">
                        @error('account_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="api_key" class="form-label">API Key</label>
                    <input type="text" class="form-control @error('api_key') is-invalid @enderror" id="api_key" name="api_key" value="{{ old('api_key') }}" placeholder="Enter API key" form="carrierForm">
                    @error('api_key')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="api_secret" class="form-label">API Secret</label>
                    <input type="password" class="form-control @error('api_secret') is-invalid @enderror" id="api_secret" name="api_secret" value="{{ old('api_secret') }}" placeholder="Enter API secret" form="carrierForm">
                    @error('api_secret')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="api_token" class="form-label">API Token</label>
                    <input type="text" class="form-control @error('api_token') is-invalid @enderror" id="api_token" name="api_token" value="{{ old('api_token') }}" placeholder="Enter API token" form="carrierForm">
                    @error('api_token')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Tracking Settings -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Tracking Settings</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tracking_prefix" class="form-label">Tracking Number Prefix</label>
                        <input type="text" class="form-control @error('tracking_prefix') is-invalid @enderror" id="tracking_prefix" name="tracking_prefix" value="{{ old('tracking_prefix') }}" placeholder="e.g., FX, 1Z" form="carrierForm">
                        @error('tracking_prefix')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Prefix added to tracking numbers</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="supports_tracking" name="supports_tracking" value="1" {{ old('supports_tracking', true) ? 'checked' : '' }} form="carrierForm">
                            <label class="form-check-label" for="supports_tracking">
                                Supports Tracking
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="tracking_url_pattern" class="form-label">Tracking URL Pattern</label>
                    <input type="url" class="form-control @error('tracking_url_pattern') is-invalid @enderror" id="tracking_url_pattern" name="tracking_url_pattern" value="{{ old('tracking_url_pattern') }}" placeholder="https://track.carrier.com?tracking={tracking_number}" form="carrierForm">
                    @error('tracking_url_pattern')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Use {tracking_number} as placeholder</div>
                </div>
            </div>
        </div>
        
        <!-- Contact Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-contact me-2"></i>Contact Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control @error('contact_person') is-invalid @enderror" id="contact_person" name="contact_person" value="{{ old('contact_person') }}" placeholder="Contact person name" form="carrierForm">
                        </div>
                        @error('contact_person')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Phone number" form="carrierForm">
                        </div>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Email address" form="carrierForm">
                        </div>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="website" class="form-label">Website</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-globe"></i></span>
                            <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website') }}" placeholder="https://example.com" form="carrierForm">
                        </div>
                        @error('website')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" placeholder="Full address" form="carrierForm">{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Pricing -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Pricing</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="base_rate" class="form-label">Base Rate (৳)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-tag"></i></span>
                            <input type="number" step="0.01" min="0" class="form-control @error('base_rate') is-invalid @enderror" id="base_rate" name="base_rate" value="{{ old('base_rate', 0) }}" placeholder="0.00" form="carrierForm">
                        </div>
                        @error('base_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Starting delivery charge</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="per_kg_rate" class="form-label">Per KG Rate (৳)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-speedometer2"></i></span>
                            <input type="number" step="0.01" min="0" class="form-control @error('per_kg_rate') is-invalid @enderror" id="per_kg_rate" name="per_kg_rate" value="{{ old('per_kg_rate', 0) }}" placeholder="0.00" form="carrierForm">
                        </div>
                        @error('per_kg_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Charge per kilogram</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fuel_surcharge_percent" class="form-label">Fuel Surcharge (%)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-percent"></i></span>
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('fuel_surcharge_percent') is-invalid @enderror" id="fuel_surcharge_percent" name="fuel_surcharge_percent" value="{{ old('fuel_surcharge_percent', 0) }}" placeholder="0.00" form="carrierForm">
                        </div>
                        @error('fuel_surcharge_percent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="free_shipping_threshold" class="form-label">Free Shipping Threshold (৳)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-gift"></i></span>
                            <input type="number" step="0.01" min="0" class="form-control @error('free_shipping_threshold') is-invalid @enderror" id="free_shipping_threshold" name="free_shipping_threshold" value="{{ old('free_shipping_threshold', 0) }}" placeholder="0.00" form="carrierForm">
                        </div>
                        @error('free_shipping_threshold')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cod_charge" class="form-label">COD Charge (৳)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                            <input type="number" step="0.01" min="0" class="form-control @error('cod_charge') is-invalid @enderror" id="cod_charge" name="cod_charge" value="{{ old('cod_charge', 0) }}" placeholder="0.00" form="carrierForm">
                        </div>
                        @error('cod_charge')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="supports_cod" name="supports_cod" value="1" {{ old('supports_cod') ? 'checked' : '' }} form="carrierForm">
                            <label class="form-check-label" for="supports_cod">
                                Supports COD
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="supports_insurance" name="supports_insurance" value="1" {{ old('supports_insurance') ? 'checked' : '' }} form="carrierForm">
                            <label class="form-check-label" for="supports_insurance">
                                Supports Insurance
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Coverage -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-globe me-2"></i>Coverage</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="coverage_countries" class="form-label">Coverage Countries</label>
                    <textarea class="form-control @error('coverage_countries') is-invalid @enderror" id="coverage_countries" name="coverage_countries" rows="2" placeholder="Bangladesh, India, USA, UK (comma separated)" form="carrierForm">{{ old('coverage_countries') }}</textarea>
                    @error('coverage_countries')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Leave empty for worldwide coverage</div>
                </div>
                
                <div class="mb-3">
                    <label for="excluded_countries" class="form-label">Excluded Countries</label>
                    <textarea class="form-control @error('excluded_countries') is-invalid @enderror" id="excluded_countries" name="excluded_countries" rows="2" placeholder="Russia, China (comma separated)" form="carrierForm">{{ old('excluded_countries') }}</textarea>
                    @error('excluded_countries')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Logo Upload -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-image me-2"></i>Carrier Logo</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="image-upload-preview mb-2 text-center" id="logoPreview" style="display: none;">
                        <img src="" alt="Preview" class="img-thumbnail" style="max-width: 100%; max-height: 200px;">
                        <button type="button" class="btn btn-sm btn-danger remove-image" onclick="removeLogo()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*" onchange="previewLogo(event)" form="carrierForm">
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Recommended size: 200x60px (PNG with transparent background)</div>
                </div>
            </div>
        </div>
        
        <!-- Status -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-toggle-on me-2"></i>Status</h6>
            </div>
            <div class="card-body">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} form="carrierForm">
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
                
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} form="carrierForm">
                    <label class="form-check-label" for="is_featured">
                        Featured
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Sort Order -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-sort-numeric-up me-2"></i>Sort Order</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="sort_order" class="form-label">Display Order</label>
                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" form="carrierForm">
                    @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Lower numbers appear first</div>
                </div>
            </div>
        </div>
        
        <!-- Floating Save Buttons -->
        <div class="floating-save-container">
            <a href="{{ route('admin.delivery.carriers.index') }}" class="btn btn-secondary floating-reset-btn">
                <i class="bi bi-x-lg me-1"></i> Cancel
            </a>
            <button type="submit" form="carrierForm" class="btn btn-primary floating-save-btn">
                <i class="bi bi-check-lg me-1"></i> Create Carrier
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewLogo(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('logoPreview');
    const img = preview.querySelector('img');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

function removeLogo() {
    document.getElementById('logo').value = '';
    document.getElementById('logoPreview').style.display = 'none';
}

// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const slugField = document.getElementById('slug');
    if (!slugField.value || slugField.dataset.auto === 'true') {
        slugField.value = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        slugField.dataset.auto = 'true';
    }
});

document.getElementById('slug').addEventListener('input', function() {
    this.dataset.auto = 'false';
});
</script>
@endpush

@push('styles')
<style>
    .image-upload-preview {
        position: relative;
        padding: 10px;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
    }
    .image-upload-preview img {
        max-width: 100%;
        max-height: 200px;
        border-radius: 4px;
    }
    .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
    }
    /* Add padding at bottom to prevent floating button overlap */
    .content-area.has-floating-save {
        padding-bottom: 100px !important;
    }
</style>
@endpush
@endsection
