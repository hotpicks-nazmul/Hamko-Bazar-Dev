@extends('admin.layouts.app')

@section('title', 'Order Configuration')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="bi bi-bag-check text-primary me-2"></i> Order Configuration
                        </h4>
                        <p class="text-muted mb-0 small">Configure order processing, numbering, limits, and notifications</p>
                    </div>
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('admin.settings.order-configuration.update') }}" method="POST" id="order-config-form">
    @csrf

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Order Number Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-hash me-2"></i>Order Number Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Order Prefix</label>
                            <input type="text" name="order_prefix" class="form-control @error('order_prefix') is-invalid @enderror" value="{{ old('order_prefix', $settings['order_prefix'] ?? 'ORD') }}" placeholder="ORD">
                            @error('order_prefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Prefix added to order numbers (e.g., ORD-12345)</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Order Suffix</label>
                            <input type="text" name="order_suffix" class="form-control @error('order_suffix') is-invalid @enderror" value="{{ old('order_suffix', $settings['order_suffix'] ?? '') }}" placeholder="">
                            @error('order_suffix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Suffix added to order numbers</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Order Number Length</label>
                            <input type="number" name="order_number_length" class="form-control @error('order_number_length') is-invalid @enderror" value="{{ old('order_number_length', $settings['order_number_length'] ?? 8) }}" min="4" max="16">
                            @error('order_number_length')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimum length of random number part</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Order Number Format</label>
                            <select name="order_number_format" class="form-select @error('order_number_format') is-invalid @enderror">
                                <option value="random" {{ old('order_number_format', $settings['order_number_format'] ?? 'random') === 'random' ? 'selected' : '' }}>Random Number</option>
                                <option value="sequential" {{ old('order_number_format', $settings['order_number_format'] ?? '') === 'sequential' ? 'selected' : '' }}>Sequential Number</option>
                                <option value="date" {{ old('order_number_format', $settings['order_number_format'] ?? '') === 'date' ? 'selected' : '' }}>Date + Random (YMD-Random)</option>
                            </select>
                            @error('order_number_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Format for generating order numbers</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Limits -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-sliders me-2"></i>Order Limits</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Minimum Order Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ config('app.currency_symbol', '৳') }}</span>
                                <input type="number" name="min_order_amount" class="form-control @error('min_order_amount') is-invalid @enderror" value="{{ old('min_order_amount', $settings['min_order_amount'] ?? 0) }}" min="0" step="0.01">
                                @error('min_order_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Minimum cart total to place order (0 = no limit)</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maximum Order Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ config('app.currency_symbol', '৳') }}</span>
                                <input type="number" name="max_order_amount" class="form-control @error('max_order_amount') is-invalid @enderror" value="{{ old('max_order_amount', $settings['max_order_amount'] ?? 0) }}" min="0" step="0.01">
                                @error('max_order_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Maximum cart total per order (0 = no limit)</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Minimum Order Quantity</label>
                            <input type="number" name="min_order_quantity" class="form-control @error('min_order_quantity') is-invalid @enderror" value="{{ old('min_order_quantity', $settings['min_order_quantity'] ?? 1) }}" min="1">
                            @error('min_order_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimum quantity per order</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maximum Order Quantity</label>
                            <input type="number" name="max_order_quantity" class="form-control @error('max_order_quantity') is-invalid @enderror" value="{{ old('max_order_quantity', $settings['max_order_quantity'] ?? 99) }}" min="1">
                            @error('max_order_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maximum quantity per order item</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Processing -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-arrow-repeat me-2"></i>Order Processing</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Order Validity (Hours)</label>
                            <div class="input-group">
                                <input type="number" name="order_validity_hours" class="form-control @error('order_validity_hours') is-invalid @enderror" value="{{ old('order_validity_hours', $settings['order_validity_hours'] ?? 72) }}" min="1">
                                @error('order_validity_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <span class="input-group-text">hours</span>
                            </div>
                            <div class="form-text">Time before order expires if not paid</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auto Cancel Unpaid After</label>
                            <div class="input-group">
                                <input type="number" name="auto_cancel_unpaid_hours" class="form-control @error('auto_cancel_unpaid_hours') is-invalid @enderror" value="{{ old('auto_cancel_unpaid_hours', $settings['auto_cancel_unpaid_hours'] ?? 0) }}" min="0">
                                @error('auto_cancel_unpaid_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <span class="input-group-text">hours</span>
                            </div>
                            <div class="form-text">Auto-cancel unpaid orders (0 = disabled)</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auto Complete Delivered Orders</label>
                            <div class="input-group">
                                <input type="number" name="auto_complete_delivered_days" class="form-control @error('auto_complete_delivered_days') is-invalid @enderror" value="{{ old('auto_complete_delivered_days', $settings['auto_complete_delivered_days'] ?? 0) }}" min="0">
                                @error('auto_complete_delivered_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <span class="input-group-text">days</span>
                            </div>
                            <div class="form-text">Days after delivery to auto-complete (0 = disabled)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2"></i>Invoice Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Invoice Prefix</label>
                            <input type="text" name="invoice_prefix" class="form-control @error('invoice_prefix') is-invalid @enderror" value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? 'INV') }}" placeholder="INV">
                            @error('invoice_prefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Prefix for invoice numbers</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="show_invoice_logo" id="show_invoice_logo" value="1" {{ old('show_invoice_logo', $settings['show_invoice_logo'] ?? '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_invoice_logo">Show Logo on Invoice</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="show_invoice_barcode" id="show_invoice_barcode" value="1" {{ old('show_invoice_barcode', $settings['show_invoice_barcode'] ?? '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_invoice_barcode">Show Barcode on Invoice</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Invoice Terms & Conditions</label>
                        <textarea name="invoice_terms" class="form-control @error('invoice_terms') is-invalid @enderror" rows="3" placeholder="Enter any terms or conditions to show on invoice">{{ old('invoice_terms', $settings['invoice_terms'] ?? '') }}</textarea>
                        @error('invoice_terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Order Status Labels -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-tag me-2"></i>Order Status Labels</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Order Status</label>
                            <select name="new_order_status" class="form-select">
                                <option value="pending" {{ ($settings['new_order_status'] ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ ($settings['new_order_status'] ?? '') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="processing" {{ ($settings['new_order_status'] ?? '') === 'processing' ? 'selected' : '' }}>Processing</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmed Order Status</label>
                            <select name="confirm_order_status" class="form-select">
                                <option value="confirmed" {{ ($settings['confirm_order_status'] ?? 'confirmed') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="processing" {{ ($settings['confirm_order_status'] ?? '') === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="pending" {{ ($settings['confirm_order_status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Processing Order Status</label>
                            <select name="processing_order_status" class="form-select">
                                <option value="processing" {{ ($settings['processing_order_status'] ?? 'processing') === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ ($settings['processing_order_status'] ?? '') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="confirmed" {{ ($settings['processing_order_status'] ?? '') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Shipped Order Status</label>
                            <select name="shipped_order_status" class="form-select">
                                <option value="shipped" {{ ($settings['shipped_order_status'] ?? 'shipped') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ ($settings['shipped_order_status'] ?? '') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="processing" {{ ($settings['shipped_order_status'] ?? '') === 'processing' ? 'selected' : '' }}>Processing</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Delivered Order Status</label>
                            <select name="delivered_order_status" class="form-select">
                                <option value="delivered" {{ ($settings['delivered_order_status'] ?? 'delivered') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="completed" {{ ($settings['delivered_order_status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cancelled Order Status</label>
                            <select name="cancelled_order_status" class="form-select">
                                <option value="cancelled" {{ ($settings['cancelled_order_status'] ?? 'cancelled') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="refunded" {{ ($settings['cancelled_order_status'] ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Returned Order Status</label>
                            <select name="returned_order_status" class="form-select">
                                <option value="returned" {{ ($settings['returned_order_status'] ?? 'returned') === 'returned' ? 'selected' : '' }}>Returned</option>
                                <option value="refunded" {{ ($settings['returned_order_status'] ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="col-lg-4">
            <!-- Order Notifications -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-bell me-2"></i>Notifications</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="notify_admin_on_new_order" id="notify_admin_on_new_order" value="1" {{ ($settings['notify_admin_on_new_order'] ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="notify_admin_on_new_order">Notify Admin on New Order</label>
                        <div class="form-text small text-muted">Send email notification to admin when new order is placed</div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="notify_customer_on_status_change" id="notify_customer_on_status_change" value="1" {{ ($settings['notify_customer_on_status_change'] ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="notify_customer_on_status_change">Notify Customer on Status Change</label>
                        <div class="form-text small text-muted">Send email to customer when order status changes</div>
                    </div>
                </div>
            </div>

            <!-- Checkout Options -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-cart-check me-2"></i>Checkout Options</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="guest_checkout_enabled" id="guest_checkout_enabled" value="1" {{ ($settings['guest_checkout_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="guest_checkout_enabled">Allow Guest Checkout</label>
                        <div class="form-text small text-muted">Allow customers to checkout without creating an account</div>
                    </div>
                </div>
            </div>

            <!-- Order Reviews -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-star me-2"></i>Order Reviews</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="allow_order_review" id="allow_order_review" value="1" {{ ($settings['allow_order_review'] ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="allow_order_review">Allow Order Reviews</label>
                        <div class="form-text small text-muted">Allow customers to review products after order delivery</div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="review_required_for_completion" id="review_required_for_completion" value="1" {{ ($settings['review_required_for_completion'] ?? '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="review_required_for_completion">Require Review for Completion</label>
                        <div class="form-text small text-muted">Order must be reviewed before completion</div>
                    </div>
                </div>
            </div>

            <!-- Digital Products -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-binary me-2"></i>Digital Products</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="digital_product_auto_deliver" id="digital_product_auto_deliver" value="1" {{ old('digital_product_auto_deliver', $settings['digital_product_auto_deliver'] ?? '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="digital_product_auto_deliver">Auto Deliver Digital Products</label>
                        <div class="form-text small text-muted">Automatically deliver digital products after payment</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Download Validity</label>
                        <div class="input-group">
                            <input type="number" name="digital_product_validity_days" class="form-control @error('digital_product_validity_days') is-invalid @enderror" value="{{ old('digital_product_validity_days', $settings['digital_product_validity_days'] ?? 30) }}" min="1">
                            @error('digital_product_validity_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <span class="input-group-text">days</span>
                        </div>
                        <div class="form-text">Days before download links expire</div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.settings.vat-tax') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-receipt me-1"></i> VAT & Tax Settings
                        </a>
                        <a href="{{ route('admin.settings.email') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-envelope me-1"></i> Email Settings
                        </a>
                        <a href="{{ route('admin.settings.shipping') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-truck me-1"></i> Shipping Settings
                        </a>
                        <a href="{{ route('admin.payment.index') }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bi bi-credit-card me-1"></i> Payment Settings
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
    <button type="submit" form="order-config-form" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save Settings
    </button>
</div>

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
@endsection
