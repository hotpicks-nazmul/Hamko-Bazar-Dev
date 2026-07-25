@extends('admin.layouts.app')

@section('title', 'Abandoned Cart Settings')

@section('content')
<!-- Header with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Abandoned Cart Settings</h4>
    <a href="{{ route('admin.marketing.abandoned-cart.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
</div>

<form method="POST" id="settingsForm" action="{{ route('admin.marketing.abandoned-cart.settings.update') }}">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            <!-- General Settings Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>General Settings</h6>
                </div>
                <div class="card-body">
                    <!-- Enable/Disable -->
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled"
                            {{ old('is_enabled', $settings->is_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_enabled">
                            <i class="bi bi-power text-success me-1"></i> Enable Abandoned Cart Recovery
                        </label>
                        <div class="form-text">When enabled, carts will be automatically tracked and recovery emails will be sent.</div>
                    </div>

                    <!-- Abandonment Time -->
                    <div class="mb-3">
                        <label for="abandonment_time" class="form-label">
                            Cart Abandonment Time <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" id="abandonment_time" name="abandonment_time" 
                                class="form-control @error('abandonment_time') is-invalid @enderror"
                                value="{{ old('abandonment_time', $settings->abandonment_time) }}" min="1" required>
                            <span class="input-group-text">minutes</span>
                        </div>
                        <div class="form-text">Cart will be considered abandoned after this time of inactivity</div>
                        @error('abandonment_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Enable Email -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="send_recovery_email" name="send_recovery_email"
                            {{ old('send_recovery_email', $settings->send_recovery_email) ? 'checked' : '' }}>
                        <label class="form-check-label" for="send_recovery_email">
                            <i class="bi bi-envelope text-primary me-1"></i> Send Recovery Emails
                        </label>
                    </div>
                </div>
            </div>

            <!-- Email Timing Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Email Timing</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_email_delay" class="form-label">
                                First Email Delay <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" id="first_email_delay" name="first_email_delay" 
                                    class="form-control @error('first_email_delay') is-invalid @enderror"
                                    value="{{ old('first_email_delay', $settings->first_email_delay) }}" min="0">
                                <span class="input-group-text">minutes</span>
                            </div>
                            <div class="form-text">Wait time after cart is abandoned before sending first email</div>
                            @error('first_email_delay')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="second_email_delay" class="form-label">
                                Second Email Delay <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" id="second_email_delay" name="second_email_delay" 
                                    class="form-control @error('second_email_delay') is-invalid @enderror"
                                    value="{{ old('second_email_delay', $settings->second_email_delay) }}" min="0">
                                <span class="input-group-text">minutes</span>
                            </div>
                            <div class="form-text">Wait time after first email before sending second email</div>
                            @error('second_email_delay')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="max_emails_per_cart" class="form-label">
                            Maximum Emails Per Cart <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="max_emails_per_cart" name="max_emails_per_cart" 
                            class="form-control @error('max_emails_per_cart') is-invalid @enderror"
                            value="{{ old('max_emails_per_cart', $settings->max_emails_per_cart) }}" min="1" max="10" required>
                        <div class="form-text">Maximum number of recovery emails to send for each abandoned cart</div>
                        @error('max_emails_per_cart')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Email Template Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-envelope-paper me-2"></i>Email Template</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="email_subject" class="form-label">Email Subject</label>
                        <input type="text" id="email_subject" name="email_subject" 
                            class="form-control @error('email_subject') is-invalid @enderror"
                            value="{{ old('email_subject', $settings->email_subject) }}">
                        <div class="form-text">Subject line for the recovery email</div>
                        @error('email_subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email_template" class="form-label">Email Template</label>
                        <textarea id="email_template" name="email_template" 
                            class="form-control @error('email_template') is-invalid @enderror"
                            rows="12">{{ old('email_template', $settings->email_template) }}</textarea>
                        <div class="form-text">
                            Available placeholders: 
                            <code>@{{customer_name}}</code>, 
                            <code>@{{cart_items}}</code>, 
                            <code>@{{cart_total}}</code>, 
                            <code>@{{recovery_link}}</code>, 
                            <code>@{{shop_name}}</code>, 
                            <code>@{{discount_offer}}</code>
                        </div>
                        @error('email_template')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Discount Settings Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-percent me-2"></i>Discount Offer</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="include_discount" name="include_discount"
                            {{ old('include_discount', $settings->include_discount) ? 'checked' : '' }}>
                        <label class="form-check-label" for="include_discount">
                            Include Discount in Email
                        </label>
                    </div>
                    <div class="mb-3">
                        <label for="discount_code" class="form-label">Discount Code</label>
                        <input type="text" id="discount_code" name="discount_code" 
                            class="form-control @error('discount_code') is-invalid @enderror"
                            value="{{ old('discount_code', $settings->discount_code) }}"
                            placeholder="e.g., COMEBACK10">
                        @error('discount_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="discount_percentage" class="form-label">Discount Percentage</label>
                        <div class="input-group">
                            <input type="number" id="discount_percentage" name="discount_percentage" 
                                class="form-control @error('discount_percentage') is-invalid @enderror"
                                value="{{ old('discount_percentage', $settings->discount_percentage) }}"
                                min="1" max="100" step="0.01">
                            <span class="input-group-text">%</span>
                        </div>
                        @error('discount_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Preview</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Click to preview how the email will look:</p>
                    <button type="button" class="btn btn-outline-primary w-100" onclick="previewEmail()">
                        <i class="bi bi-envelope me-1"></i> Preview Email
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Save Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.marketing.abandoned-cart.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="settingsForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save Settings
    </button>
</div>
@endsection

@push('scripts')
<script>
    function previewEmail() {
        const template = document.getElementById('email_template').value;
        const subject = document.getElementById('email_subject').value;
        
        let preview = template
            .replace('@{{customer_name}}', 'John Doe')
            .replace('@{{cart_items}}', '<div style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Sample Product</strong><br>Qty: 1 | Price: $99.00</div>')
            .replace('@{{cart_total}}', '$99.00')
            .replace('@{{recovery_link}}', '<a href="#">Click here to complete your purchase</a>')
            .replace('@{{shop_name}}', 'Site Name')
            .replace('@{{discount_offer}}', '<p style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;"><strong>Special Offer!</strong> Use code <strong>COMEBACK10</strong> to get 10% off!</p>');
        
        // Open preview in modal
        const modalHtml = `
            <div class="modal fade" id="emailPreviewModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Email Preview: ${subject}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${preview}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        document.getElementById('emailPreviewModal')?.remove();
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('emailPreviewModal'));
        modal.show();
    }
</script>
@endpush
