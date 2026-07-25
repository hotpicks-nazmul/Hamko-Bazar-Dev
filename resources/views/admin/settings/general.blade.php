@extends('admin.layouts.app')

@section('title', 'General Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">General Settings</h4>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>
</div>

<!-- Success/Error Alerts -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>Please fix the following errors:
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<form action="{{ route('admin.settings.general.update') }}" method="POST" id="settings-form" enctype="multipart/form-data">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="site_name" class="form-label">Site Name <span class="text-danger">*</span></label>
                            <input type="text" id="site_name" name="site_name" class="form-control @error('site_name') is-invalid @enderror" value="{{ old('site_name', $settings['site_name'] ?? '') }}" required>
                            @error('site_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Your website name</div>
                        </div>
                        <div class="col-md-6">
                            <label for="site_tagline" class="form-label">Site Tagline</label>
                            <input type="text" id="site_tagline" name="site_tagline" class="form-control @error('site_tagline') is-invalid @enderror" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}">
                            @error('site_tagline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Short description of your store</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="site_favicon" class="form-label">Favicon</label>
                            <input type="file" id="site_favicon" name="site_favicon" class="form-control @error('site_favicon') is-invalid @enderror" accept="image/png,image/x-icon,image/jpeg">
                            @error('site_favicon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Upload a favicon (PNG, ICO, or JPG, recommended size: 32x32 or 64x64)</div>
                            @if(!empty($settings['site_favicon']))
                                <div class="mt-2">
                                    <img src="{{ asset($settings['site_favicon']) }}" alt="Favicon" style="width:32px;height:32px;border:1px solid #ddd;border-radius:4px;padding:2px;">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Localization -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-globe me-2"></i>Localization</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="currency" class="form-label">Currency</label>
                            <select id="currency" name="currency" class="form-select @error('currency') is-invalid @enderror">
                                <option value="BDT" {{ old('currency', $settings['currency'] ?? 'BDT') === 'BDT' ? 'selected' : '' }}>BDT (৳) - Bangladesh</option>
                                <option value="USD" {{ old('currency', $settings['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                                <option value="EUR" {{ old('currency', $settings['currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                                <option value="GBP" {{ old('currency', $settings['currency'] ?? '') === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                                <option value="INR" {{ old('currency', $settings['currency'] ?? '') === 'INR' ? 'selected' : '' }}>INR (₹) - Indian Rupee</option>
                            </select>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select id="timezone" name="timezone" class="form-select @error('timezone') is-invalid @enderror">
                                <option value="Asia/Dhaka" {{ old('timezone', $settings['timezone'] ?? 'Asia/Dhaka') === 'Asia/Dhaka' ? 'selected' : '' }}>Asia/Dhaka (UTC+6)</option>
                                <option value="Asia/Kolkata" {{ old('timezone', $settings['timezone'] ?? '') === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (UTC+5:30)</option>
                                <option value="UTC" {{ old('timezone', $settings['timezone'] ?? '') === 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="Europe/London" {{ old('timezone', $settings['timezone'] ?? '') === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                <option value="America/New_York" {{ old('timezone', $settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                            </select>
                            @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Quick Info -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Quick Info</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">General settings for your website.</p>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Currency affects all pricing</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> For top bar info, go to Home Page Settings</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> For logo/theme, go to Home Page Settings</li>
                        <li class="mb-0"><i class="bi bi-check-circle text-success me-1"></i> For footer, go to Footer Settings</li>
                    </ul>
                </div>
            </div>
            
            <!-- Other Settings Links -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Other Settings</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.homepage.index') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-house me-1"></i> Home Page Settings
                        </a>
                        <a href="{{ route('admin.settings.seo') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-search me-1"></i> SEO Settings
                        </a>
                        <a href="{{ route('admin.settings.email') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-envelope me-1"></i> Email Settings
                        </a>
                        <a href="{{ route('admin.settings.footer') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-layout-text-window-reverse me-1"></i> Footer Settings
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Last Updated -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Last Updated</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-0">
                        @php
                            $lastUpdated = \App\Models\Setting::where('key', 'site_name')->first();
                        @endphp
                        @if($lastUpdated && $lastUpdated->updated_at)
                            {{ $lastUpdated->updated_at->format('M d, Y h:i A') }}
                        @else
                            Not yet updated
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.settings.general') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="settings-form" class="btn btn-primary floating-save-btn">
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
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-scroll to first error field
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
</script>
@endpush
