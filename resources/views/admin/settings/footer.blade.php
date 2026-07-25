@extends('admin.layouts.app')

@section('title', 'Footer Settings')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="bi bi-layout-text-window-reverse text-primary me-2"></i> Footer Settings
                        </h4>
                        <p class="text-muted mb-0 small">Configure your website footer section</p>
                    </div>
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('admin.settings.footer.update') }}" method="POST" id="footerSettingsForm">
    @csrf

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>Please fix the errors below.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- About Section -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2"></i>About Section</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">About Text</label>
                        <textarea name="footer_about_text" class="form-control @error('footer_about_text') is-invalid @enderror" rows="4" placeholder="Your trusted source for premium quality halal meat...">{{ old('footer_about_text', $settings['footer_about_text'] ?? '') }}</textarea>
                        @error('footer_about_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Brief description of your business shown in footer</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media Links -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-share me-2"></i>Social Media Links</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium"><i class="bi bi-facebook text-primary me-2"></i>Facebook URL</label>
                        <input type="url" name="footer_facebook_url" class="form-control @error('footer_facebook_url') is-invalid @enderror" value="{{ old('footer_facebook_url', $settings['footer_facebook_url'] ?? '') }}" placeholder="https://facebook.com/yourpage">
                        @error('footer_facebook_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter full URL including https://</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium"><i class="bi bi-instagram text-danger me-2"></i>Instagram URL</label>
                        <input type="url" name="footer_instagram_url" class="form-control @error('footer_instagram_url') is-invalid @enderror" value="{{ old('footer_instagram_url', $settings['footer_instagram_url'] ?? '') }}" placeholder="https://instagram.com/yourpage">
                        @error('footer_instagram_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter full URL including https://</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium"><i class="bi bi-youtube text-danger me-2"></i>YouTube URL</label>
                        <input type="url" name="footer_youtube_url" class="form-control @error('footer_youtube_url') is-invalid @enderror" value="{{ old('footer_youtube_url', $settings['footer_youtube_url'] ?? '') }}" placeholder="https://youtube.com/yourchannel">
                        @error('footer_youtube_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter full URL including https://</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium"><i class="bi bi-twitter text-info me-2"></i>Twitter URL</label>
                        <input type="url" name="footer_twitter_url" class="form-control @error('footer_twitter_url') is-invalid @enderror" value="{{ old('footer_twitter_url', $settings['footer_twitter_url'] ?? '') }}" placeholder="https://twitter.com/yourpage">
                        @error('footer_twitter_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter full URL including https://</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium"><i class="bi bi-linkedin text-primary me-2"></i>LinkedIn URL</label>
                        <input type="url" name="footer_linkedin_url" class="form-control @error('footer_linkedin_url') is-invalid @enderror" value="{{ old('footer_linkedin_url', $settings['footer_linkedin_url'] ?? '') }}" placeholder="https://linkedin.com/company/yourcompany">
                        @error('footer_linkedin_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter full URL including https://</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Contact Information -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-telephone me-2"></i>Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Address</label>
                        <textarea name="footer_address" class="form-control @error('footer_address') is-invalid @enderror" rows="2" placeholder="123 Green Market Road, Dhaka-1205, Bangladesh">{{ old('footer_address', $settings['footer_address'] ?? '') }}</textarea>
                        @error('footer_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Phone Number</label>
                        <input type="text" name="footer_phone" class="form-control @error('footer_phone') is-invalid @enderror" value="{{ old('footer_phone', $settings['footer_phone'] ?? '') }}" placeholder="+880 1700-000000">
                        @error('footer_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Email Address</label>
                        <input type="email" name="footer_email" class="form-control @error('footer_email') is-invalid @enderror" value="{{ old('footer_email', $settings['footer_email'] ?? '') }}" placeholder="info@example.com">
                        @error('footer_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Business Hours</label>
                        <input type="text" name="footer_business_hours" class="form-control @error('footer_business_hours') is-invalid @enderror" value="{{ old('footer_business_hours', $settings['footer_business_hours'] ?? '') }}" placeholder="Sat - Thu: 8AM - 10PM">
                        @error('footer_business_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Newsletter Section -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-envelope me-2"></i>Newsletter Section</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="footer_newsletter_enabled" id="footer_newsletter_enabled" value="1" {{ (old('footer_newsletter_enabled', $settings['footer_newsletter_enabled'] ?? '1')) === '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="footer_newsletter_enabled">Show Newsletter Section</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Newsletter Title</label>
                        <input type="text" name="footer_newsletter_title" class="form-control @error('footer_newsletter_title') is-invalid @enderror" value="{{ old('footer_newsletter_title', $settings['footer_newsletter_title'] ?? 'Subscribe to Our Newsletter') }}" placeholder="Subscribe to Our Newsletter">
                        @error('footer_newsletter_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Newsletter Subtitle</label>
                        <input type="text" name="footer_newsletter_subtitle" class="form-control @error('footer_newsletter_subtitle') is-invalid @enderror" value="{{ old('footer_newsletter_subtitle', $settings['footer_newsletter_subtitle'] ?? 'Get updates on new products and special offers!') }}" placeholder="Get updates on new products...">
                        @error('footer_newsletter_subtitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Column Titles -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-columns me-2"></i>Column Titles</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Column 1 Title (Quick Links)</label>
                        <input type="text" name="footer_column1_title" class="form-control @error('footer_column1_title') is-invalid @enderror" value="{{ old('footer_column1_title', $settings['footer_column1_title'] ?? 'Quick Links') }}">
                        @error('footer_column1_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Column 2 Title (Customer Service)</label>
                        <input type="text" name="footer_column2_title" class="form-control @error('footer_column2_title') is-invalid @enderror" value="{{ old('footer_column2_title', $settings['footer_column2_title'] ?? 'Customer Service') }}">
                        @error('footer_column2_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Column 3 Title (Contact)</label>
                        <input type="text" name="footer_column3_title" class="form-control @error('footer_column3_title') is-invalid @enderror" value="{{ old('footer_column3_title', $settings['footer_column3_title'] ?? 'Contact Us') }}">
                        @error('footer_column3_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment & Copyright -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-credit-card me-2"></i>Payment & Copyright</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="footer_show_payment_icons" id="footer_show_payment_icons" value="1" {{ (old('footer_show_payment_icons', $settings['footer_show_payment_icons'] ?? '1')) === '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="footer_show_payment_icons">Show Payment Methods</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Payment Methods (comma separated)</label>
                        <input type="text" name="footer_payment_methods" class="form-control @error('footer_payment_methods') is-invalid @enderror" value="{{ old('footer_payment_methods', $settings['footer_payment_methods'] ?? 'bkash,nagad,rocket,visa,mastercard') }}" placeholder="bkash,nagad,rocket,visa,mastercard">
                        @error('footer_payment_methods')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Available: bkash, nagad, rocket, visa, mastercard, amex, paypal</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Copyright Text</label>
                        <input type="text" name="footer_copyright_text" class="form-control @error('footer_copyright_text') is-invalid @enderror" value="{{ old('footer_copyright_text', $settings['footer_copyright_text'] ?? '') }}" placeholder="© 2024 Your Store. All rights reserved.">
                        @error('footer_copyright_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Leave empty to use default: © Year Site Name. All rights reserved.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Save Button -->
    <div class="floating-save-container">
        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary floating-reset-btn">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary floating-save-btn">
            <i class="bi bi-check-lg me-1"></i> Save Footer Settings
        </button>
    </div>
</form>
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
