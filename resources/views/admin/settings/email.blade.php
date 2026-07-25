@extends('admin.layouts.app')

@section('title', 'Email Settings')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Email Settings</h4>
    <p class="text-muted">Configure your SMTP settings to send emails from your application</p>
</div>

<form action="{{ route('admin.settings.email.update') }}" method="POST" id="email-form">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- SMTP Configuration -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-envelope me-2"></i>SMTP Configuration</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mail_mailer" class="form-label">Mail Driver <span class="text-danger">*</span></label>
                            <select id="mail_mailer" name="mail_mailer" class="form-select @error('mail_mailer') is-invalid @enderror" required>
                                <option value="smtp" {{ old('mail_mailer', $settings['mail_mailer'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="sendmail" {{ old('mail_mailer', $settings['mail_mailer'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="log" {{ old('mail_mailer', $settings['mail_mailer'] ?? '') === 'log' ? 'selected' : '' }}>Log (Development)</option>
                                <option value="array" {{ old('mail_mailer', $settings['mail_mailer'] ?? '') === 'array' ? 'selected' : '' }}>Array (Testing)</option>
                            </select>
                            @error('mail_mailer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Choose how emails should be sent</div>
                        </div>
                        <div class="col-md-6">
                            <label for="mail_host" class="form-label">SMTP Host <span class="text-danger">*</span></label>
                            <input type="text" id="mail_host" name="mail_host" class="form-control @error('mail_host') is-invalid @enderror" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" placeholder="smtp.example.com" required>
                            @error('mail_host')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Your SMTP server address</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mail_port" class="form-label">SMTP Port <span class="text-danger">*</span></label>
                            <input type="number" id="mail_port" name="mail_port" class="form-control @error('mail_port') is-invalid @enderror" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}" placeholder="587" required>
                            @error('mail_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Common ports: 587 (TLS), 465 (SSL), 25</div>
                        </div>
                        <div class="col-md-6">
                            <label for="mail_encryption" class="form-label">Encryption</label>
                            <select id="mail_encryption" name="mail_encryption" class="form-select @error('mail_encryption') is-invalid @enderror">
                                <option value="tls" {{ old('mail_encryption', $settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Recommended)</option>
                                <option value="ssl" {{ old('mail_encryption', $settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ old('mail_encryption', $settings['mail_encryption'] ?? '') === '' ? 'selected' : '' }}>None</option>
                            </select>
                            @error('mail_encryption')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Secure connection type</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mail_username" class="form-label">SMTP Username</label>
                            <input type="text" id="mail_username" name="mail_username" class="form-control @error('mail_username') is-invalid @enderror" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" placeholder="your-username">
                            @error('mail_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Your SMTP authentication username</div>
                        </div>
                        <div class="col-md-6">
                            <label for="mail_password" class="form-label">SMTP Password</label>
                            <div class="input-group">
                                <input type="password" id="mail_password" name="mail_password" class="form-control @error('mail_password') is-invalid @enderror" value="{{ old('mail_password', $settings['mail_password'] ?? '') }}" placeholder="••••••••">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </button>
                            </div>
                            @error('mail_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Your SMTP authentication password</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- From Address -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-send me-2"></i>From Address</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mail_from_address" class="form-label">From Email <span class="text-danger">*</span></label>
                            <input type="email" id="mail_from_address" name="mail_from_address" class="form-control @error('mail_from_address') is-invalid @enderror" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? 'noreply@example.com') }}" placeholder="noreply@example.com" required>
                            @error('mail_from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Email address used as sender</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mail_from_name" class="form-label">From Name <span class="text-danger">*</span></label>
                            <input type="text" id="mail_from_name" name="mail_from_name" class="form-control @error('mail_from_name') is-invalid @enderror" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? config('app.name')) }}" placeholder="{{ config('app.name') }}" required>
                            @error('mail_from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Name displayed as sender</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form Email -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Contact Form Email</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="contact_email" class="form-label">Contact Form Recipient</label>
                            <input type="email" id="contact_email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}" placeholder="admin@example.com">
                            @error('contact_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Email address where contact form submissions will be sent</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Test Email -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-paper-plane me-2"></i>Send Test Email</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Send a test email to verify your SMTP configuration is working correctly.</p>
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label for="test_email" class="form-label">Test Email Address</label>
                            <input type="email" id="test_email" name="test_email" class="form-control" placeholder="test@example.com">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-primary" onclick="sendTestEmail()">
                                <i class="bi bi-send me-1"></i> Send Test Email
                            </button>
                        </div>
                    </div>
                    <div id="test-email-result" class="mt-3"></div>
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
                    <p class="text-muted small mb-2">SMTP configuration for sending emails.</p>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Use TLS on port 587 for most providers</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Use SSL on port 465 for secure connections</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Gmail requires App Password</li>
                        <li class="mb-0"><i class="bi bi-check-circle text-success me-1"></i> Test before saving</li>
                    </ul>
                </div>
            </div>
            
            <!-- Common SMTP Providers -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-cloud me-2"></i>Common Providers</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="applySmtpPreset('gmail')">
                            <i class="bi bi-google me-1"></i> Gmail
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="applySmtpPreset('mailgun')">
                            <i class="bi bi-envelope me-1"></i> Mailgun
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="applySmtpPreset('mailtrap')">
                            <i class="bi bi-bug me-1"></i> Mailtrap (Testing)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="applySmtpPreset('smtp2go')">
                            <i class="bi bi-send me-1"></i> SMTP2GO
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Other Settings Links -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Other Settings</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.settings.general') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-gear me-1"></i> General Settings
                        </a>
                        <a href="{{ route('admin.settings.seo') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-search me-1"></i> SEO Settings
                        </a>
                        <a href="{{ route('admin.settings.social-login') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Social Login
                        </a>
                        <a href="{{ route('admin.settings.whatsapp') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-whatsapp me-1"></i> WhatsApp Chat
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
                            $lastUpdated = \App\Models\Setting::where('key', 'mail_mailer')->first();
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
    <a href="{{ route('admin.settings.email') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="email-form" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save Settings
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

    // Toggle password visibility
    function togglePassword() {
        const passwordInput = document.getElementById('mail_password');
        const icon = document.getElementById('password-icon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
    
    // SMTP Presets for common providers
    function applySmtpPreset(provider) {
        const presets = {
            gmail: {
                mail_host: 'smtp.gmail.com',
                mail_port: '587',
                mail_encryption: 'tls',
                mail_username: '',
                mail_password: '',
                mail_from_address: '',
                mail_from_name: '{{ config("app.name") }}'
            },
            mailgun: {
                mail_host: 'smtp.mailgun.org',
                mail_port: '587',
                mail_encryption: 'tls',
                mail_username: '',
                mail_password: '',
                mail_from_address: '',
                mail_from_name: '{{ config("app.name") }}'
            },
            mailtrap: {
                mail_host: 'smtp.mailtrap.io',
                mail_port: '2525',
                mail_encryption: '',
                mail_username: '',
                mail_password: '',
                mail_from_address: '',
                mail_from_name: '{{ config("app.name") }}'
            },
            smtp2go: {
                mail_host: 'mail.smtp2go.com',
                mail_port: '587',
                mail_encryption: 'tls',
                mail_username: '',
                mail_password: '',
                mail_from_address: '',
                mail_from_name: '{{ config("app.name") }}'
            }
        };
        
        if (presets[provider]) {
            document.getElementById('mail_host').value = presets[provider].mail_host;
            document.getElementById('mail_port').value = presets[provider].mail_port;
            document.getElementById('mail_encryption').value = presets[provider].mail_encryption;
            document.getElementById('mail_username').value = presets[provider].mail_username;
            document.getElementById('mail_password').value = presets[provider].mail_password;
            document.getElementById('mail_from_name').value = '{{ config("app.name") }}';
        }
    }
    
    // Send test email
    function sendTestEmail() {
        const testEmail = document.getElementById('test_email').value;
        const resultDiv = document.getElementById('test-email-result');
        
        if (!testEmail) {
            resultDiv.innerHTML = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i> Please enter a test email address</div>';
            return;
        }
        
        // Show loading
        resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-arrow-repeat me-1"></i> Sending test email...</div>';
        
        // Get current SMTP settings
        const formData = new FormData();
        formData.append('test_email', testEmail);
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch('{{ route("admin.settings.email.test") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> ' + data.message + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-1"></i> ' + (data.message || 'Failed to send test email') + '</div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-1"></i> Error: ' + error.message + '</div>';
        });
    }
</script>
@endpush
