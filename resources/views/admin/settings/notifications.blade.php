@extends('admin.layouts.app')

@section('title', 'Notification Settings')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Notification Settings</h4>
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
    <i class="bi bi-exclamation-triangle me-2"></i>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<form action="{{ route('admin.settings.notifications.update') }}" method="POST" id="settings-form">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Email Notifications - Admin -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-envelope me-2"></i>Email Notifications - Admin</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_admin_new_order" name="notify_admin_new_order" form="settings-form" {{ ($settings['notify_admin_new_order'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_admin_new_order">
                                    New Order Notification
                                </label>
                                <div class="form-text">Receive email when a new order is placed</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_admin_new_refund" name="notify_admin_new_refund" form="settings-form" {{ ($settings['notify_admin_new_refund'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_admin_new_refund">
                                    New Refund Request
                                </label>
                                <div class="form-text">Receive email when a refund is requested</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_admin_new_customer" name="notify_admin_new_customer" form="settings-form" {{ ($settings['notify_admin_new_customer'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_admin_new_customer">
                                    New Customer Registration
                                </label>
                                <div class="form-text">Receive email when a new customer registers</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_admin_new_seller" name="notify_admin_new_seller" form="settings-form" {{ ($settings['notify_admin_new_seller'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_admin_new_seller">
                                    New Seller Registration
                                </label>
                                <div class="form-text">Receive email when a new seller applies</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_admin_low_stock" name="notify_admin_low_stock" form="settings-form" {{ ($settings['notify_admin_low_stock'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_admin_low_stock">
                                    Low Stock Alert
                                </label>
                                <div class="form-text">Receive email when products are running low</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_admin_out_of_stock" name="notify_admin_out_of_stock" form="settings-form" {{ ($settings['notify_admin_out_of_stock'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_admin_out_of_stock">
                                    Out of Stock Alert
                                </label>
                                <div class="form-text">Receive email when products are out of stock</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_admin_new_review" name="notify_admin_new_review" form="settings-form" {{ ($settings['notify_admin_new_review'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_admin_new_review">
                                    New Review
                                </label>
                                <div class="form-text">Receive email when a new product review is submitted</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_admin_new_support_ticket" name="notify_admin_new_support_ticket" form="settings-form" {{ ($settings['notify_admin_new_support_ticket'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_admin_new_support_ticket">
                                    New Support Ticket
                                </label>
                                <div class="form-text">Receive email when a new support ticket is created</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Notifications - Customer -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-envelope me-2"></i>Email Notifications - Customer</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_customer_order_placed" name="notify_customer_order_placed" form="settings-form" {{ ($settings['notify_customer_order_placed'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_customer_order_placed">
                                    Order Placed
                                </label>
                                <div class="form-text">Send email when order is placed</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_customer_order_confirmed" name="notify_customer_order_confirmed" form="settings-form" {{ ($settings['notify_customer_order_confirmed'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_customer_order_confirmed">
                                    Order Confirmed
                                </label>
                                <div class="form-text">Send email when order is confirmed</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_customer_order_processing" name="notify_customer_order_processing" form="settings-form" {{ ($settings['notify_customer_order_processing'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_customer_order_processing">
                                    Order Processing
                                </label>
                                <div class="form-text">Send email when order is being processed</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_customer_order_shipped" name="notify_customer_order_shipped" form="settings-form" {{ ($settings['notify_customer_order_shipped'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_customer_order_shipped">
                                    Order Shipped
                                </label>
                                <div class="form-text">Send email when order is shipped</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_customer_order_delivered" name="notify_customer_order_delivered" form="settings-form" {{ ($settings['notify_customer_order_delivered'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_customer_order_delivered">
                                    Order Delivered
                                </label>
                                <div class="form-text">Send email when order is delivered</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_customer_order_cancelled" name="notify_customer_order_cancelled" form="settings-form" {{ ($settings['notify_customer_order_cancelled'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_customer_order_cancelled">
                                    Order Cancelled
                                </label>
                                <div class="form-text">Send email when order is cancelled</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_customer_refund_approved" name="notify_customer_refund_approved" form="settings-form" {{ ($settings['notify_customer_refund_approved'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_customer_refund_approved">
                                    Refund Approved
                                </label>
                                <div class="form-text">Send email when refund is approved</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_customer_refund_rejected" name="notify_customer_refund_rejected" form="settings-form" {{ ($settings['notify_customer_refund_rejected'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_customer_refund_rejected">
                                    Refund Rejected
                                </label>
                                <div class="form-text">Send email when refund is rejected</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_customer_new_message" name="notify_customer_new_message" form="settings-form" {{ ($settings['notify_customer_new_message'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_customer_new_message">
                                    New Message
                                </label>
                                <div class="form-text">Send email when new message is received</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_customer_promo" name="notify_customer_promo" form="settings-form" {{ ($settings['notify_customer_promo'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_customer_promo">
                                    Promotional Emails
                                </label>
                                <div class="form-text">Send promotional and newsletter emails</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SMS Notifications -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>SMS Notifications</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="admin_phone_for_sms" class="form-label">Admin Phone Number</label>
                            <input type="text" id="admin_phone_for_sms" name="admin_phone_for_sms" form="settings-form" class="form-control @error('admin_phone_for_sms') is-invalid @enderror" value="{{ old('admin_phone_for_sms', $settings['admin_phone_for_sms'] ?? '') }}" placeholder="+8801XXXXXXXXX">
                            @error('admin_phone_for_sms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Phone number for receiving SMS notifications</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sms_notify_admin_new_order" name="sms_notify_admin_new_order" form="settings-form" {{ ($settings['sms_notify_admin_new_order'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sms_notify_admin_new_order">
                                    Admin: New Order
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sms_notify_admin_new_refund" name="sms_notify_admin_new_refund" form="settings-form" {{ ($settings['sms_notify_admin_new_refund'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sms_notify_admin_new_refund">
                                    Admin: New Refund
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sms_notify_admin_low_stock" name="sms_notify_admin_low_stock" form="settings-form" {{ ($settings['sms_notify_admin_low_stock'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sms_notify_admin_low_stock">
                                    Admin: Low Stock
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sms_notify_customer_order_status" name="sms_notify_customer_order_status" form="settings-form" {{ ($settings['sms_notify_customer_order_status'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sms_notify_customer_order_status">
                                    Customer: Order Status
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sms_notify_customer_delivery" name="sms_notify_customer_delivery" form="settings-form" {{ ($settings['sms_notify_customer_delivery'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sms_notify_customer_delivery">
                                    Customer: Delivery Update
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sms_notify_customer_otp" name="sms_notify_customer_otp" form="settings-form" {{ ($settings['sms_notify_customer_otp'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sms_notify_customer_otp">
                                    Customer: OTP Verification
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Push Notifications -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-bell me-2"></i>Push Notifications</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="push_notify_customer_order" name="push_notify_customer_order" form="settings-form" {{ ($settings['push_notify_customer_order'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="push_notify_customer_order">
                                    Order Updates
                                </label>
                                <div class="form-text">Send push notifications for order updates</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="push_notify_customer_promo" name="push_notify_customer_promo" form="settings-form" {{ ($settings['push_notify_customer_promo'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="push_notify_customer_promo">
                                    Promotional
                                </label>
                                <div class="form-text">Send promotional push notifications</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="push_notify_customer_new_product" name="push_notify_customer_new_product" form="settings-form" {{ ($settings['push_notify_customer_new_product'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="push_notify_customer_new_product">
                                    New Products
                                </label>
                                <div class="form-text">Notify when new products are added</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notification_sound_enabled" name="notification_sound_enabled" form="settings-form" {{ ($settings['notification_sound_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="notification_sound_enabled">
                                    Notification Sound
                                </label>
                                <div class="form-text">Play sound for web push notifications</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Alert Settings -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Stock Alert Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="low_stock_threshold" class="form-label">Low Stock Threshold</label>
                            <input type="number" id="low_stock_threshold" name="low_stock_threshold" form="settings-form" class="form-control @error('low_stock_threshold') is-invalid @enderror" value="{{ old('low_stock_threshold', $settings['low_stock_threshold'] ?? 10) }}" min="1">
                            @error('low_stock_threshold')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Alert when product quantity falls below this number</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Quick Info -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Quick Info</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Configure how notifications are sent to admins and customers.</p>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Email notifications require SMTP configuration</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> SMS requires SMS gateway setup</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Push requires browser/mobile app integration</li>
                        <li class="mb-0"><i class="bi bi-check-circle text-success me-1"></i> Customers can manage their preferences in account settings</li>
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
                        <a href="{{ route('admin.settings.email') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-envelope me-1"></i> Email Settings
                        </a>
                        <a href="{{ route('admin.marketing.bulk-sms.index') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-chat-dots me-1"></i> SMS Gateway
                        </a>
                        <a href="{{ route('admin.marketing.push-notifications.index') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-bell me-1"></i> Push Notifications
                        </a>
                        <a href="{{ route('admin.settings.order-configuration') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-bag me-1"></i> Order Settings
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
                            $lastUpdated = \App\Models\Setting::where('key', 'notify_admin_new_order')->first();
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
