@extends('admin.layouts.app')

@section('title', 'Social Login Settings')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="bi bi-shield-lock text-primary me-2"></i> Social Login Settings
                        </h4>
                        <p class="text-muted mb-0 small">Configure Google and Facebook social login</p>
                    </div>
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('admin.settings.social-login.update') }}" method="POST" id="social-login-form">
    @csrf
    @method('PUT')

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Google Settings -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center">
                    <i class="bi bi-google text-primary me-2" style="font-size: 1.25rem;"></i>
                    <h5 class="mb-0 fw-semibold">Google Login</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="google_enabled" id="google_enabled" value="1" {{ ($settings['google_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="google_enabled">
                            <i class="bi bi-check-circle text-success me-1"></i> Enable Google Login
                        </label>
                        <div class="form-text">Allow users to sign in with their Google account</div>
                    </div>

                    <div class="mb-3">
                        <label for="google_client_id" class="form-label fw-medium">Google Client ID</label>
                        <input type="text" id="google_client_id" name="google_client_id" class="form-control @error('google_client_id') is-invalid @enderror" value="{{ $settings['google_client_id'] ?? '' }}" placeholder="Enter Google Client ID">
                        @error('google_client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Get your Client ID from <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a></div>
                    </div>

                    <div class="mb-3">
                        <label for="google_client_secret" class="form-label fw-medium">Google Client Secret</label>
                        <input type="password" id="google_client_secret" name="google_client_secret" class="form-control @error('google_client_secret') is-invalid @enderror" value="{{ $settings['google_client_secret'] ?? '' }}" placeholder="Enter Google Client Secret">
                        @error('google_client_secret')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text"><i class="bi bi-shield-lock me-1"></i>Keep this secret! Never share it publicly.</div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <h6 class="alert-heading"><i class="bi bi-info-circle me-1"></i> Setup Instructions</h6>
                        <ol class="mb-0 small">
                            <li>Go to <a href="https://console.cloud.google.com" target="_blank">Google Cloud Console</a></li>
                            <li>Create a new project or select existing</li>
                            <li>Enable Google+ API</li>
                            <li>Create OAuth 2.0 credentials</li>
                            <li>Add redirect URI: <code>{{ url('/login/google/callback') }}</code></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Facebook Settings -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center">
                    <i class="bi bi-facebook text-primary me-2" style="font-size: 1.25rem;"></i>
                    <h5 class="mb-0 fw-semibold">Facebook Login</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="facebook_enabled" id="facebook_enabled" value="1" {{ ($settings['facebook_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="facebook_enabled">
                            <i class="bi bi-check-circle text-success me-1"></i> Enable Facebook Login
                        </label>
                        <div class="form-text">Allow users to sign in with their Facebook account</div>
                    </div>

                    <div class="mb-3">
                        <label for="facebook_client_id" class="form-label fw-medium">Facebook App ID</label>
                        <input type="text" id="facebook_client_id" name="facebook_client_id" class="form-control @error('facebook_client_id') is-invalid @enderror" value="{{ $settings['facebook_client_id'] ?? '' }}" placeholder="Enter Facebook App ID">
                        @error('facebook_client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Get your App ID from <a href="https://developers.facebook.com/apps" target="_blank">Facebook Developers</a></div>
                    </div>

                    <div class="mb-3">
                        <label for="facebook_client_secret" class="form-label fw-medium">Facebook App Secret</label>
                        <input type="password" id="facebook_client_secret" name="facebook_client_secret" class="form-control @error('facebook_client_secret') is-invalid @enderror" value="{{ $settings['facebook_client_secret'] ?? '' }}" placeholder="Enter Facebook App Secret">
                        @error('facebook_client_secret')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text"><i class="bi bi-shield-lock me-1"></i>Keep this secret! Never share it publicly.</div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <h6 class="alert-heading"><i class="bi bi-info-circle me-1"></i> Setup Instructions</h6>
                        <ol class="mb-0 small">
                            <li>Go to <a href="https://developers.facebook.com/apps" target="_blank">Facebook Developers</a></li>
                            <li>Create a new app</li>
                            <li>Add Facebook Login product</li>
                            <li>Configure OAuth settings</li>
                            <li>Add redirect URI: <code>{{ url('/login/facebook/callback') }}</code></li>
                        </ol>
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
    <button type="submit" form="social-login-form" class="btn btn-primary floating-save-btn">
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
    
    /* Force Bootstrap Icons to display */
    .bi::before,
    [class*="bi bi-"]::before {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-family: 'bootstrap-icons' !important;
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
