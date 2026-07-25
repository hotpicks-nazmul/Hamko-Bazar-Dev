@extends('admin.layouts.app')

@section('title', 'Shipping Settings')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="bi bi-truck text-primary me-2"></i> Shipping Settings
                        </h4>
                        <p class="text-muted mb-0 small">Configure shipping methods and options for your store</p>
                    </div>
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.settings.shipping.update') }}" method="POST" id="shipping-form">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            <!-- Free Shipping -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-gift me-2"></i>Free Shipping</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="free_shipping_enabled" id="free_shipping_enabled" value="1" {{ ($settings['free_shipping_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="free_shipping_enabled">Enable Free Shipping</label>
                        <div class="form-text small text-muted">Offer free shipping on orders above a certain amount</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Minimum Order Amount for Free Shipping</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ config('app.currency_symbol', '৳') }}</span>
                            <input type="number" name="free_shipping_min_amount" class="form-control @error('free_shipping_min_amount') is-invalid @enderror" value="{{ old('free_shipping_min_amount', $settings['free_shipping_min_amount'] ?? 0) }}" min="0" step="0.01">
                        </div>
                        @error('free_shipping_min_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Orders above this amount will get free shipping (0 = disabled)</div>
                    </div>
                </div>
            </div>

            <!-- Shipping Calculation -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-calculator me-2"></i>Shipping Calculation</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Shipping Calculation Type</label>
                        <select name="shipping_calculation_type" class="form-select @error('shipping_calculation_type') is-invalid @enderror">
                            <option value="flat" {{ old('shipping_calculation_type', $settings['shipping_calculation_type'] ?? 'flat') === 'flat' ? 'selected' : '' }}>Flat Rate</option>
                            <option value="weight" {{ old('shipping_calculation_type', $settings['shipping_calculation_type'] ?? '') === 'weight' ? 'selected' : '' }}>Based on Weight</option>
                            <option value="price" {{ old('shipping_calculation_type', $settings['shipping_calculation_type'] ?? '') === 'price' ? 'selected' : '' }}>Based on Price</option>
                            <option value="location" {{ old('shipping_calculation_type', $settings['shipping_calculation_type'] ?? '') === 'location' ? 'selected' : '' }}>Based on Location</option>
                        </select>
                        @error('shipping_calculation_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Default Shipping Cost</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ config('app.currency_symbol', '৳') }}</span>
                            <input type="number" name="default_shipping_cost" class="form-control @error('default_shipping_cost') is-invalid @enderror" value="{{ old('default_shipping_cost', $settings['default_shipping_cost'] ?? 0) }}" min="0" step="0.01">
                        </div>
                        @error('default_shipping_cost')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Default cost when no specific rule applies</div>
                    </div>
                </div>
            </div>

            <!-- Local Pickup -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-shop me-2"></i>Local Pickup</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="local_pickup_enabled" id="local_pickup_enabled" value="1" {{ ($settings['local_pickup_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="local_pickup_enabled">Enable Local Pickup</label>
                        <div class="form-text small text-muted">Allow customers to pick up their orders from your store</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Local Pickup Cost</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ config('app.currency_symbol', '৳') }}</span>
                            <input type="number" name="local_pickup_cost" class="form-control @error('local_pickup_cost') is-invalid @enderror" value="{{ old('local_pickup_cost', $settings['local_pickup_cost'] ?? 0) }}" min="0" step="0.01">
                        </div>
                        @error('local_pickup_cost')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Cost for local pickup (0 = free)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Links -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.settings.order-configuration') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-bag-check me-1"></i> Order Configuration
                        </a>
                        <a href="{{ route('admin.settings.vat-tax') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-receipt me-1"></i> VAT & Tax Settings
                        </a>
                        <a href="{{ route('admin.payment.index') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-credit-card me-1"></i> Payment Settings
                        </a>
                        <a href="{{ route('admin.delivery.zones.index') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-geo-alt me-1"></i> Delivery Zones
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="shipping-form" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save Settings
    </button>
</div>
@endsection

@push('styles')
<style>
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
    
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
</style>
@endpush
