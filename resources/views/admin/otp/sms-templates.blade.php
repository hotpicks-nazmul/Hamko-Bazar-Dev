@extends('admin.layouts.app')

@section('title', 'SMS Templates')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-chat-dots"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">SMS Templates</span>
            <span class="stat-card-value">10</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active Templates</span>
            <span class="stat-card-value">
                @php
                    $activeCount = 0;
                    if(!empty($templates['otp_verification_template'])) $activeCount++;
                    if(!empty($templates['registration_template'])) $activeCount++;
                    if(!empty($templates['password_reset_template'])) $activeCount++;
                    if(!empty($templates['payment_template'])) $activeCount++;
                    if(!empty($templates['order_confirmation_template'])) $activeCount++;
                    if(!empty($templates['login_notification_template'])) $activeCount++;
                    if(!empty($templates['order_placed_template'])) $activeCount++;
                    if(!empty($templates['delivery_status_template'])) $activeCount++;
                    if(!empty($templates['payment_status_template'])) $activeCount++;
                    if(!empty($templates['delivery_assignment_template'])) $activeCount++;
                    echo $activeCount;
                @endphp
            </span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-send"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">OTPs Sent Today</span>
            <span class="stat-card-value">{{ number_format($stats['total_sent_today'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Failed Attempts</span>
            <span class="stat-card-value">{{ number_format($stats['failed_attempts'] ?? 0) }}</span>
        </div>
    </div>
</div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">SMS Templates</h4>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="smsTemplateForm" method="POST" action="{{ route('admin.otp.sms-templates.update') }}">
                @csrf
                @if(auth()->user()->hasPermission('otp.templates-edit') || auth()->user()->hasPermission('otp.templates-create'))
                
                <!-- OTP Verification Template -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>OTP Verification Message</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="otp_verification_template" class="form-label">Verification Code Message <span class="text-danger">*</span></label>
                            <textarea id="otp_verification_template" name="otp_verification_template" class="form-control template-input @error('otp_verification_template') is-invalid @enderror" rows="3" placeholder="Enter your OTP verification message">{{ old('otp_verification_template', $templates['otp_verification_template'] ?? 'Your verification code is: {otp}. Valid for {expiry} minutes.') }}</textarea>
                            @error('otp_verification_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Use <code>{otp}</code> for the OTP code, <code>{expiry}</code> for expiry time in minutes, <code>{site_name}</code> for your website name
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded">
                            <span class="text-muted small">Preview:</span>
                            <p class="mb-0 mt-2 small" id="verificationPreview">
                                Your <strong>{{ config('app.name') }}</strong> verification code is: <strong>123456</strong>. Valid for <strong>5</strong> minutes.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Welcome/Registration Template -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-person-plus me-2"></i>Registration Welcome Message</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="registration_template" class="form-label">Welcome Message (Optional)</label>
                            <textarea id="registration_template" name="registration_template" class="form-control template-input @error('registration_template') is-invalid @enderror" rows="3" placeholder="Enter welcome message after registration">{{ old('registration_template', $templates['registration_template'] ?? 'Welcome to {site_name}! Your account has been created successfully.') }}</textarea>
                            @error('registration_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                This message is sent after successful phone verification during registration. Use <code>{site_name}</code>, <code>{user_name}</code>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded">
                            <span class="text-muted small">Preview:</span>
                            <p class="mb-0 mt-2 small" id="registrationPreview">
                                Welcome to <strong>{{ config('app.name') }}</strong>! Your account has been created successfully.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Password Reset Template -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-key me-2"></i>Password Reset Message</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="password_reset_template" class="form-label">Password Reset OTP Message</label>
                            <textarea id="password_reset_template" name="password_reset_template" class="form-control template-input @error('password_reset_template') is-invalid @enderror" rows="3" placeholder="Enter password reset message">{{ old('password_reset_template', $templates['password_reset_template'] ?? 'Your password reset OTP is: {otp}. Do not share this code with anyone.') }}</textarea>
                            @error('password_reset_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Use <code>{otp}</code> for the OTP code, <code>{site_name}</code>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded">
                            <span class="text-muted small">Preview:</span>
                            <p class="mb-0 mt-2 small" id="passwordResetPreview">
                                Your password reset OTP is: <strong>123456</strong>. Do not share this code with anyone.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Verification Template -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Verification Message</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="payment_template" class="form-label">Payment OTP Message</label>
                            <textarea id="payment_template" name="payment_template" class="form-control template-input @error('payment_template') is-invalid @enderror" rows="3" placeholder="Enter payment verification message">{{ old('payment_template', $templates['payment_template'] ?? 'Your payment verification code is: {otp}. Amount: {amount}.') }}</textarea>
                            @error('payment_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Use <code>{otp}</code> for the OTP code, <code>{amount}</code> for payment amount, <code>{site_name}</code>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded">
                            <span class="text-muted small">Preview:</span>
                            <p class="mb-0 mt-2 small" id="paymentPreview">
                                Your payment verification code is: <strong>123456</strong>. Amount: <strong>1000</strong>.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Order Confirmation Template -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-bag-check me-2"></i>Order Confirmation Message</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="order_confirmation_template" class="form-label">Order OTP Message</label>
                            <textarea id="order_confirmation_template" name="order_confirmation_template" class="form-control template-input @error('order_confirmation_template') is-invalid @enderror" rows="3" placeholder="Enter order confirmation message">{{ old('order_confirmation_template', $templates['order_confirmation_template'] ?? 'Your order OTP is: {otp}. Order ID: {order_id}.') }}</textarea>
                            @error('order_confirmation_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Use <code>{otp}</code> for the OTP code, <code>{order_id}</code> for order ID, <code>{site_name}</code>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded">
                            <span class="text-muted small">Preview:</span>
                            <p class="mb-0 mt-2 small" id="orderConfirmationPreview">
                                Your order OTP is: <strong>123456</strong>. Order ID: <strong>ORD-12345</strong>.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Login Notification Template -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-box-arrow-in-right me-2"></i>Login Notification Message</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="login_notification_template" class="form-label">Login OTP Message</label>
                            <textarea id="login_notification_template" name="login_notification_template" class="form-control template-input @error('login_notification_template') is-invalid @enderror" rows="3" placeholder="Enter login notification message">{{ old('login_notification_template', $templates['login_notification_template'] ?? 'Your login OTP is: {otp}. If you did not request this, please ignore.') }}</textarea>
                            @error('login_notification_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Use <code>{otp}</code> for the OTP code, <code>{site_name}</code>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded">
                            <span class="text-muted small">Preview:</span>
                            <p class="mb-0 mt-2 small" id="loginNotificationPreview">
                                Your login OTP is: <strong>123456</strong>. If you did not request this, please ignore.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Order Placed Template -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-bag-check me-2"></i>Order Placed Message</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="order_placed_template" class="form-label">Order Placed SMS</label>
                            <textarea id="order_placed_template" name="order_placed_template" class="form-control template-input" rows="3" placeholder="Enter order placed message">{{ old('order_placed_template', $templates['order_placed_template'] ?? 'Your order has been placed and Order Code is {order_code}.') }}</textarea>
                            <div class="form-text">
                                Use <code>{order_code}</code> for order code, <code>{site_name}</code>, <code>{customer_name}</code>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded">
                            <span class="text-muted small">Preview:</span>
                            <p class="mb-0 mt-2 small" id="orderPlacedPreview">
                                Your order has been placed and Order Code is <strong>ORD-12345</strong>.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Delivery Status Template -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-truck me-2"></i>Delivery Status Update Message</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="delivery_status_template" class="form-label">Delivery Status SMS</label>
                            <textarea id="delivery_status_template" name="delivery_status_template" class="form-control template-input" rows="3" placeholder="Enter delivery status message">{{ old('delivery_status_template', $templates['delivery_status_template'] ?? 'Your delivery status has been updated to {delivery_status} for Order code : {order_code}.') }}</textarea>
                            <div class="form-text">
                                Use <code>{delivery_status}</code> for status, <code>{order_code}</code> for order code, <code>{site_name}</code>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded">
                            <span class="text-muted small">Preview:</span>
                            <p class="mb-0 mt-2 small" id="deliveryStatusPreview">
                                Your delivery status has been updated to <strong>Shipped</strong> for Order code : <strong>ORD-12345</strong>.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Status Template -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Status Update Message</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="payment_status_template" class="form-label">Payment Status SMS</label>
                            <textarea id="payment_status_template" name="payment_status_template" class="form-control template-input" rows="3" placeholder="Enter payment status message">{{ old('payment_status_template', $templates['payment_status_template'] ?? 'Your payment status has been updated to {payment_status} for Order code : {order_code}.') }}</textarea>
                            <div class="form-text">
                                Use <code>{payment_status}</code> for status, <code>{order_code}</code> for order code, <code>{site_name}</code>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded">
                            <span class="text-muted small">Preview:</span>
                            <p class="mb-0 mt-2 small" id="paymentStatusPreview">
                                Your payment status has been updated to <strong>Paid</strong> for Order code : <strong>ORD-12345</strong>.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Delivery Assignment Template -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-person-check me-2"></i>Delivery Assignment Message</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="delivery_assignment_template" class="form-label">Delivery Assignment SMS</label>
                            <textarea id="delivery_assignment_template" name="delivery_assignment_template" class="form-control template-input" rows="3" placeholder="Enter delivery assignment message">{{ old('delivery_assignment_template', $templates['delivery_assignment_template'] ?? 'You are assigned to deliver an order. Order code : {order_code}.') }}</textarea>
                            <div class="form-text">
                                Use <code>{order_code}</code> for order code, <code>{delivery_person}</code> for assignee name, <code>{site_name}</code>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded">
                            <span class="text-muted small">Preview:</span>
                            <p class="mb-0 mt-2 small" id="deliveryAssignmentPreview">
                                You are assigned to deliver an order. Order code : <strong>ORD-12345</strong>.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </form>
        </div>
        
        <div class="col-lg-4">
            <!-- Template Variables Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-code-slash text-info me-2"></i>Available Variables</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Use these variables in your SMS templates:</p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <code class="bg-light px-2 py-1 rounded small">{otp}</code>
                        <code class="bg-light px-2 py-1 rounded small">{expiry}</code>
                        <code class="bg-light px-2 py-1 rounded small">{site_name}</code>
                        <code class="bg-light px-2 py-1 rounded small">{user_name}</code>
                        <code class="bg-light px-2 py-1 rounded small">{amount}</code>
                        <code class="bg-light px-2 py-1 rounded small">{order_id}</code>
                        <code class="bg-light px-2 py-1 rounded small">{email}</code>
                        <code class="bg-light px-2 py-1 rounded small">{phone}</code>
                        <code class="bg-light px-2 py-1 rounded small">{order_code}</code>
                        <code class="bg-light px-2 py-1 rounded small">{delivery_status}</code>
                        <code class="bg-light px-2 py-1 rounded small">{payment_status}</code>
                        <code class="bg-light px-2 py-1 rounded small">{delivery_person}</code>
                        <code class="bg-light px-2 py-1 rounded small">{customer_name}</code>
                    </div>
                    <hr>
                    <p class="small text-muted mb-1"><strong>Character Count:</strong></p>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">SMS Segments:</span>
                        <span class="badge bg-primary" id="charCount">0/160</span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle text-info me-2"></i>SMS Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">SMS length is limited to 160 characters per segment</li>
                        <li class="mb-2">Longer messages will be split into multiple segments</li>
                        <li class="mb-2">Keep messages concise for cost efficiency</li>
                        <li>Always include your brand name for recognition</li>
                    </ul>
                </div>
            </div>
            
            <!-- Related Settings Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Related Settings</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.otp.configuration') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-gear me-2"></i>OTP Configuration</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('admin.otp.credentials') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-key me-2"></i>OTP Credentials</span>
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
        <button type="submit" form="smsTemplateForm" class="btn btn-primary floating-save-btn">
            <i class="bi bi-check-lg me-1"></i> Save Templates
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
        const templateInputs = document.querySelectorAll('.template-input');
        const charCountEl = document.getElementById('charCount');

        const previewMap = {
            'otp_verification_template': 'verificationPreview',
            'registration_template': 'registrationPreview',
            'password_reset_template': 'passwordResetPreview',
            'payment_template': 'paymentPreview',
            'order_confirmation_template': 'orderConfirmationPreview',
            'login_notification_template': 'loginNotificationPreview',
            'order_placed_template': 'orderPlacedPreview',
            'delivery_status_template': 'deliveryStatusPreview',
            'payment_status_template': 'paymentStatusPreview',
            'delivery_assignment_template': 'deliveryAssignmentPreview',
        };

        const defaultValues = {
            'otp_verification_template': 'Your verification code is: <strong>123456</strong>. Valid for <strong>5</strong> minutes.',
            'registration_template': 'Welcome to <strong>{{ config('app.name') }}</strong>! Your account has been created successfully.',
            'password_reset_template': 'Your password reset OTP is: <strong>123456</strong>. Do not share this code with anyone.',
            'payment_template': 'Your payment verification code is: <strong>123456</strong>. Amount: <strong>1000</strong>.',
            'order_confirmation_template': 'Your order OTP is: <strong>123456</strong>. Order ID: <strong>ORD-12345</strong>.',
            'login_notification_template': 'Your login OTP is: <strong>123456</strong>. If you did not request this, please ignore.',
            'order_placed_template': 'Your order has been placed and Order Code is <strong>ORD-12345</strong>.',
            'delivery_status_template': 'Your delivery status has been updated to <strong>Shipped</strong> for Order code : <strong>ORD-12345</strong>.',
            'payment_status_template': 'Your payment status has been updated to <strong>Paid</strong> for Order code : <strong>ORD-12345</strong>.',
            'delivery_assignment_template': 'You are assigned to deliver an order. Order code : <strong>ORD-12345</strong>.',
        };

        function replacePlaceholders(text) {
            return text
                .replace(/{otp}/g, '<strong>123456</strong>')
                .replace(/{expiry}/g, '<strong>5</strong>')
                .replace(/{site_name}/g, '<strong>{{ config('app.name') }}</strong>')
                .replace(/{user_name}/g, '<strong>John Doe</strong>')
                .replace(/{amount}/g, '<strong>1000</strong>')
                .replace(/{order_id}/g, '<strong>ORD-12345</strong>')
                .replace(/{email}/g, '<strong>user@example.com</strong>')
                .replace(/{phone}/g, '<strong>01712345678</strong>')
                .replace(/{order_code}/g, '<strong>ORD-12345</strong>')
                .replace(/{delivery_status}/g, '<strong>Shipped</strong>')
                .replace(/{payment_status}/g, '<strong>Paid</strong>')
                .replace(/{delivery_person}/g, '<strong>Rahim</strong>')
                .replace(/{customer_name}/g, '<strong>John Doe</strong>');
        }

        function updatePreview(input) {
            const previewId = previewMap[input.id];
            const previewEl = document.getElementById(previewId);
            if (previewEl) {
                const text = input.value || defaultValues[input.id] || '';
                previewEl.innerHTML = replacePlaceholders(text);
            }
        }

        function updateCharCount(input) {
            if (input && charCountEl) {
                const len = input.value.length;
                const segments = Math.ceil(len / 160) || 1;
                charCountEl.textContent = `${len}/160 (${segments} segment${segments > 1 ? 's' : ''})`;

                if (len > 160) {
                    charCountEl.classList.remove('bg-primary');
                    charCountEl.classList.add('bg-warning');
                } else {
                    charCountEl.classList.remove('bg-warning');
                    charCountEl.classList.add('bg-primary');
                }
            }
        }

        templateInputs.forEach(input => {
            updatePreview(input);
            updateCharCount(input);

            input.addEventListener('input', function() {
                updatePreview(this);
                updateCharCount(this);
            });

            input.addEventListener('focus', function() {
                updateCharCount(this);
            });
        });
    });
</script>
@endpush
