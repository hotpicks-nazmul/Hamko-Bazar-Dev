@extends('admin.layouts.app')

@section('title', 'OTP Configuration')

@section('content')
<!-- Statistics Cards -->
<div class="row g-3 mb-4" id="statsCards">
    <div class="col">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-send"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Total OTPs Sent Today</span>
                <span class="stat-card-value">{{ number_format($stats['total_sent_today'] ?? 0) }}</span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Successful Verifications</span>
                <span class="stat-card-value">{{ number_format($stats['successful_verifications'] ?? 0) }}</span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card stat-card-danger">
            <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Failed Attempts</span>
                <span class="stat-card-value">{{ number_format($stats['failed_attempts'] ?? 0) }}</span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon"><i class="bi bi-shield-lock"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">System Status</span>
                <span class="stat-card-value">
                    @if(($config['otp_for_login'] ?? 1) || ($config['otp_for_registration'] ?? 1))
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">OTP Configuration</h4>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="otpConfigForm" method="POST" action="{{ route('admin.otp.configuration.update') }}">
                @csrf
                
                <!-- General Settings -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>General OTP Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if(auth()->user()->hasPermission('otp.config-length'))
                            <div class="col-md-6">
                                <label for="otp_length" class="form-label">OTP Length <span class="text-danger">*</span></label>
                                <select id="otp_length" name="otp_length" class="form-select @error('otp_length') is-invalid @enderror" required>
                                    <option value="4" {{ old('otp_length', $config['otp_length'] ?? '6') == '4' ? 'selected' : '' }}>4 Digits</option>
                                    <option value="5" {{ old('otp_length', $config['otp_length'] ?? '6') == '5' ? 'selected' : '' }}>5 Digits</option>
                                    <option value="6" {{ old('otp_length', $config['otp_length'] ?? '6') == '6' ? 'selected' : '' }}>6 Digits</option>
                                </select>
                                @error('otp_length')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Number of digits in the OTP code</div>
                            </div>
                            @endif
                            
                            @if(auth()->user()->hasPermission('otp.config-expiry'))
                            <div class="col-md-6">
                                <label for="otp_expiry" class="form-label">OTP Validity (minutes) <span class="text-danger">*</span></label>
                                <input type="number" id="otp_expiry" name="otp_expiry" class="form-control @error('otp_expiry') is-invalid @enderror" value="{{ old('otp_expiry', $config['otp_expiry'] ?? 5) }}" min="1" max="60" required>
                                @error('otp_expiry')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Time before OTP expires</div>
                            </div>
                            @endif
                            
                            <div class="col-md-6">
                                <label for="otp_max_attempts" class="form-label">Max Verification Attempts <span class="text-danger">*</span></label>
                                <input type="number" id="otp_max_attempts" name="otp_max_attempts" class="form-control @error('otp_max_attempts') is-invalid @enderror" value="{{ old('otp_max_attempts', $config['otp_max_attempts'] ?? 3) }}" min="1" max="10" required>
                                @error('otp_max_attempts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Maximum wrong attempts before OTP is blocked</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="resend_cooldown" class="form-label">Resend Cooldown (seconds) <span class="text-danger">*</span></label>
                                <input type="number" id="resend_cooldown" name="resend_cooldown" class="form-control @error('resend_cooldown') is-invalid @enderror" value="{{ old('resend_cooldown', $config['resend_cooldown'] ?? 60) }}" min="30" max="300" required>
                                @error('resend_cooldown')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimum time before requesting new OTP</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- OTP for Different Actions -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-toggle-on me-2"></i>OTP Verification for Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="otp_for_login" name="otp_for_login" value="1" {{ (old('otp_for_login', $config['otp_for_login'] ?? 1)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="otp_for_login">
                                        <i class="bi bi-box-arrow-in-right text-primary me-1"></i> OTP for Login
                                    </label>
                                    <div class="form-text">Require OTP when user logs in</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="otp_for_registration" name="otp_for_registration" value="1" {{ (old('otp_for_registration', $config['otp_for_registration'] ?? 1)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="otp_for_registration">
                                        <i class="bi bi-person-plus text-success me-1"></i> OTP for Registration
                                    </label>
                                    <div class="form-text">Verify phone number during registration</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="otp_for_password_reset" name="otp_for_password_reset" value="1" {{ (old('otp_for_password_reset', $config['otp_for_password_reset'] ?? 1)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="otp_for_password_reset">
                                        <i class="bi bi-key text-warning me-1"></i> OTP for Password Reset
                                    </label>
                                    <div class="form-text">Verify phone when resetting password</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="otp_for_payment" name="otp_for_payment" value="1" {{ (old('otp_for_payment', $config['otp_for_payment'] ?? 0)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="otp_for_payment">
                                        <i class="bi bi-credit-card text-info me-1"></i> OTP for Payment
                                    </label>
                                    <div class="form-text">Verify payment with OTP</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="otp_for_profile_change" name="otp_for_profile_change" value="1" {{ (old('otp_for_profile_change', $config['otp_for_profile_change'] ?? 0)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="otp_for_profile_change">
                                        <i class="bi bi-person-gear text-secondary me-1"></i> OTP for Profile Changes
                                    </label>
                                    <div class="form-text">Verify when changing profile information</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="otp_for_order_confirmation" name="otp_for_order_confirmation" value="1" {{ (old('otp_for_order_confirmation', $config['otp_for_order_confirmation'] ?? 0)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="otp_for_order_confirmation">
                                        <i class="bi bi-bag-check text-success me-1"></i> OTP for Order Confirmation
                                    </label>
                                    <div class="form-text">Verify phone for order placement</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Security Settings -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Security Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="otp_case_sensitive" name="otp_case_sensitive" value="1" {{ (old('otp_case_sensitive', $config['otp_case_sensitive'] ?? 0)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="otp_case_sensitive">
                                        <i class="bi bi-type text-primary me-1"></i> Case Sensitive OTP
                                    </label>
                                    <div class="form-text">Make OTP case-sensitive (if using alphanumeric)</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="otp_alphanumeric" name="otp_alphanumeric" value="1" {{ (old('otp_alphanumeric', $config['otp_alphanumeric'] ?? 0)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="otp_alphanumeric">
                                        <i class="bi bi-chat-text text-info me-1"></i> Alphanumeric OTP
                                    </label>
                                    <div class="form-text">Use letters and numbers instead of only digits</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="max_otp_per_day" class="form-label">Max OTP Requests per Day <span class="text-danger">*</span></label>
                                <input type="number" id="max_otp_per_day" name="max_otp_per_day" class="form-control @error('max_otp_per_day') is-invalid @enderror" value="{{ old('max_otp_per_day', $config['max_otp_per_day'] ?? 10) }}" min="1" max="100" required>
                                @error('max_otp_per_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Limit OTP requests per phone number per day</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="temp_block_duration" class="form-label">Temporary Block Duration (minutes) <span class="text-danger">*</span></label>
                                <input type="number" id="temp_block_duration" name="temp_block_duration" class="form-control @error('temp_block_duration') is-invalid @enderror" value="{{ old('temp_block_duration', $config['temp_block_duration'] ?? 30) }}" min="5" max="1440" required>
                                @error('temp_block_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Block duration after max attempts exceeded</div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-lg-4">
            <!-- Quick Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle text-info me-2"></i>Quick Info</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Configure OTP settings for your e-commerce platform. The system will send verification codes to users' phone numbers for secure authentication.</p>
                    <ul class="small text-muted ps-3 mb-0">
                        <li>6-digit OTP is recommended for balance of security and usability</li>
                        <li>5-minute expiry is standard for most applications</li>
                        <li>Enable OTP for password reset to prevent unauthorized access</li>
                    </ul>
                </div>
            </div>
            
            <!-- Other Settings Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Related Settings</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.otp.credentials') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-key me-2"></i>OTP Credentials</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('admin.otp.sms-templates') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-file-text me-2"></i>SMS Templates</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Floating Buttons -->
    <div class="floating-save-container">
        <button type="button" class="btn btn-secondary floating-reset-btn" onclick="history.back()">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </button>
        <button type="submit" form="otpConfigForm" class="btn btn-primary floating-save-btn">
            <i class="bi bi-check-lg me-1"></i> Save Configuration
        </button>
    </div>
@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
    .avatar-sm {
        width: 36px;
        height: 36px;
    }
</style>
@endpush
