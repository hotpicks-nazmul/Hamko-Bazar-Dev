@extends('admin.layouts.app')

@section('title', 'Country Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="bi bi-globe me-2"></i>Country Settings</h4>
        <small class="text-muted">Configure checkout localization and country preferences</small>
    </div>
    <a href="{{ route('admin.locations.cities.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Locations
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.locations.country-settings.update') }}" method="POST">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-globe me-2"></i>Checkout Localization</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Checkout Mode</label>
                        <select name="checkout_mode" class="form-select @error('checkout_mode') is-invalid @enderror">
                            <option value="local" {{ old('checkout_mode', $settings['checkout_mode'] ?? 'local') === 'local' ? 'selected' : '' }}>Local (Single Country)</option>
                            <option value="international" {{ old('checkout_mode', $settings['checkout_mode'] ?? '') === 'international' ? 'selected' : '' }}>International (Multi-Country)</option>
                        </select>
                        @error('checkout_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text small text-muted">
                            <strong>Local:</strong> Hides country selection at checkout. Only cities from the default country are shown.<br>
                            <strong>International:</strong> Shows a country dropdown at checkout. Customers select their country first.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Default Country <span class="text-danger">*</span></label>
                        <select name="default_country" class="form-select @error('default_country') is-invalid @enderror">
                            <option value="">Select Country</option>
                            @foreach(\App\Models\Country::ordered()->get() as $country)
                                <option value="{{ $country->id }}" {{ old('default_country', $settings['default_country'] ?? '') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('default_country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text small text-muted">In Local mode, only cities from this country will be available for customers to select during checkout.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">Quick Info</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="small text-muted mb-1">Total Countries</p>
                        <h4 class="mb-0">{{ \App\Models\Country::count() }}</h4>
                    </div>
                    <div class="mb-3">
                        <p class="small text-muted mb-1">Cities in Database</p>
                        <h4 class="mb-0">{{ \App\Models\City::count() }}</h4>
                    </div>
                    <div>
                        <p class="small text-muted mb-1">Areas in Database</p>
                        <h4 class="mb-0">{{ \App\Models\Area::count() }}</h4>
                    </div>
                    <hr>
                    <a href="{{ route('admin.locations.cities.index') }}" class="btn btn-sm btn-outline-primary w-100 mb-2">
                        <i class="bi bi-building me-1"></i> Manage Cities
                    </a>
                    <a href="{{ route('admin.locations.areas.index') }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-geo me-1"></i> Manage Areas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="floating-save-container">
        <a href="{{ route('admin.locations.cities.index') }}" class="btn btn-secondary floating-reset-btn text-white">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary floating-save-btn">
            <i class="bi bi-check-lg me-1"></i> Save Settings
        </button>
    </div>
</form>
@endsection

@push('styles')
<style>
.content-area.has-floating-save { padding-bottom: 100px; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.card-header.bg-white { background-color: #fff !important; }
</style>
@endpush
