@extends('admin.layouts.app')

@section('title', 'SEO Settings')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="bi bi-search text-primary me-2"></i> SEO Settings
                        </h4>
                        <p class="text-muted mb-0 small">Configure search engine optimization and social sharing settings</p>
                    </div>
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Success/Error Alerts --}}
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

<div class="row">
    <div class="col-lg-8">
        {{-- Meta Settings Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-gear me-2"></i>Meta Settings</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.seo.meta.update') }}" method="POST" id="seo-form">
                    @csrf

                    <div class="mb-3">
                        <label for="site_meta_title" class="form-label fw-medium">Site Meta Title <span class="text-danger">*</span></label>
                        <input type="text" id="site_meta_title" name="site_meta_title" class="form-control @error('site_meta_title') is-invalid @enderror" value="{{ old('site_meta_title', $settings['site_meta_title'] ?? '') }}" maxlength="100">
                        <div class="d-flex justify-content-between mt-1">
                            <div class="form-text mb-0">Default title for SEO (50-60 characters recommended)</div>
                            <div class="form-text mb-0"><span id="metaTitleCount">0</span>/100</div>
                        </div>
                        @error('site_meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="site_meta_description" class="form-label fw-medium">Site Meta Description</label>
                        <textarea id="site_meta_description" name="site_meta_description" class="form-control @error('site_meta_description') is-invalid @enderror" rows="3" maxlength="300">{{ old('site_meta_description', $settings['site_meta_description'] ?? '') }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <div class="form-text mb-0">Default description for SEO (150-160 characters recommended)</div>
                            <div class="form-text mb-0"><span id="metaDescCount">0</span>/300</div>
                        </div>
                        @error('site_meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="site_meta_keywords" class="form-label fw-medium">Site Meta Keywords</label>
                        <input type="text" id="site_meta_keywords" name="site_meta_keywords" class="form-control @error('site_meta_keywords') is-invalid @enderror" value="{{ old('site_meta_keywords', $settings['site_meta_keywords'] ?? '') }}" maxlength="500" placeholder="keyword1, keyword2, keyword3">
                        <div class="form-text">Comma separated keywords</div>
                        @error('site_meta_keywords')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>

        {{-- Analytics & Verification Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-graph-up me-2"></i>Analytics & Verification</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="google_analytics_id" class="form-label fw-medium">Google Analytics ID</label>
                            <input type="text" id="google_analytics_id" name="google_analytics_id" class="form-control @error('google_analytics_id') is-invalid @enderror" value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}" placeholder="G-XXXXXXXXXX" maxlength="50">
                            @error('google_analytics_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Get ID from <a href="https://analytics.google.com" target="_blank">Google Analytics</a></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="facebook_pixel_id" class="form-label fw-medium">Facebook Pixel ID</label>
                            <input type="text" id="facebook_pixel_id" name="facebook_pixel_id" class="form-control @error('facebook_pixel_id') is-invalid @enderror" value="{{ old('facebook_pixel_id', $settings['facebook_pixel_id'] ?? '') }}" placeholder="123456789012345" maxlength="50">
                            @error('facebook_pixel_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Get ID from <a href="https://business.facebook.com" target="_blank">Facebook Business</a></div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="google_search_console_code" class="form-label fw-medium">Google Search Console Verification</label>
                    <textarea id="google_search_console_code" name="google_search_console_code" class="form-control @error('google_search_console_code') is-invalid @enderror" rows="2" maxlength="500">{{ old('google_search_console_code', $settings['google_search_console_code'] ?? '') }}</textarea>
                    <div class="form-text">Paste your Google Search Console verification meta tag content here</div>
                    @error('google_search_console_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- URL Redirects Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-arrow-left-right me-2"></i>URL Redirects</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.seo.redirects.store') }}" method="POST" class="row g-2 mb-3">
                    @csrf
                    <div class="col-md-4">
                        <label for="redirect_from" class="form-label small text-muted">From URL</label>
                        <input type="text" id="redirect_from" name="from" class="form-control" placeholder="/old-page" required>
                        @error('from')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="redirect_to" class="form-label small text-muted">To URL</label>
                        <input type="text" id="redirect_to" name="to" class="form-control" placeholder="/new-page" required>
                        @error('to')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label for="redirect_type" class="form-label small text-muted">Type</label>
                        <select id="redirect_type" name="type" class="form-select">
                            <option value="301">301 (Permanent)</option>
                            <option value="302">302 (Temporary)</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg me-1"></i> Add
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>From</th>
                                <th>To</th>
                                <th>Type</th>
                                <th style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($redirects ?? [] as $index => $redirect)
                            <tr>
                                <td><code>{{ $redirect['from'] ?? '' }}</code></td>
                                <td><code>{{ $redirect['to'] ?? '' }}</code></td>
                                <td><span class="badge bg-{{ ($redirect['type'] ?? '') == 301 ? 'success' : 'warning' }}">{{ $redirect['type'] ?? '' }}</span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteRedirect({{ $index }}, '{{ addslashes($redirect['from'] ?? '') }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="bi bi-arrow-left-right text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-0 mt-2">No redirects configured</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Sitemap Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-diagram-3 me-2"></i>Sitemap</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Generate a sitemap.xml file for search engines.</p>
                <form action="{{ route('admin.seo.sitemap.generate') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i> Generate Sitemap
                    </button>
                </form>
                <hr>
                <p class="mb-1 small"><strong>Sitemap URL:</strong></p>
                <code class="small">{{ url('sitemap.xml') }}</code>
            </div>
        </div>

        {{-- Open Graph Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-share me-2"></i>Open Graph</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Default Open Graph settings for social sharing.</p>
                <form action="{{ route('admin.seo.meta.update') }}" method="POST" id="og-form">
                    @csrf
                    <div class="mb-3">
                        <label for="og_title" class="form-label fw-medium">OG Title</label>
                        <input type="text" id="og_title" name="og_title" class="form-control @error('og_title') is-invalid @enderror" value="{{ old('og_title', $settings['og_title'] ?? '') }}" maxlength="100">
                        @error('og_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="og_description" class="form-label fw-medium">OG Description</label>
                        <textarea id="og_description" name="og_description" class="form-control @error('og_description') is-invalid @enderror" rows="2" maxlength="300">{{ old('og_description', $settings['og_description'] ?? '') }}</textarea>
                        @error('og_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="og_image" class="form-label fw-medium">OG Image URL</label>
                        <input type="text" id="og_image" name="og_image" class="form-control @error('og_image') is-invalid @enderror" value="{{ old('og_image', $settings['og_image'] ?? '') }}" placeholder="/storage/path/to/image.webp" maxlength="500">
                        <div class="form-text">Path to the default OG image for social sharing</div>
                        @error('og_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>

        {{-- Twitter Card Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-twitter me-2"></i>Twitter Card</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Default Twitter Card settings.</p>
                <form action="{{ route('admin.seo.meta.update') }}" method="POST" id="twitter-form">
                    @csrf
                    <div class="mb-3">
                        <label for="twitter_card_type" class="form-label fw-medium">Card Type</label>
                        <select id="twitter_card_type" name="twitter_card_type" class="form-select @error('twitter_card_type') is-invalid @enderror">
                            <option value="summary" {{ ($settings['twitter_card_type'] ?? '') == 'summary' ? 'selected' : '' }}>Summary</option>
                            <option value="summary_large_image" {{ ($settings['twitter_card_type'] ?? '') == 'summary_large_image' ? 'selected' : '' }}>Summary with Large Image</option>
                        </select>
                        @error('twitter_card_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Floating Save Buttons for Meta Settings --}}
<div class="floating-save-container">
    <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="seo-form" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save Meta Settings
    </button>
</div>

{{-- Delete Redirect Confirmation Modal --}}
<div class="modal fade" id="deleteRedirectModal" tabindex="-1" aria-labelledby="deleteRedirectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteRedirectModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this redirect?</p>
                <p class="mb-0"><strong>From:</strong> <code id="deleteRedirectFrom"></code></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteRedirectForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }

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
        const metaTitle = document.getElementById('site_meta_title');
        const metaDesc = document.getElementById('site_meta_description');
        const metaTitleCount = document.getElementById('metaTitleCount');
        const metaDescCount = document.getElementById('metaDescCount');

        function updateCounters() {
            if (metaTitle) metaTitleCount.textContent = metaTitle.value.length;
            if (metaDesc) metaDescCount.textContent = metaDesc.value.length;
        }

        if (metaTitle) {
            metaTitle.addEventListener('input', updateCounters);
        }
        if (metaDesc) {
            metaDesc.addEventListener('input', updateCounters);
        }

        updateCounters();

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

    function confirmDeleteRedirect(index, fromUrl) {
        document.getElementById('deleteRedirectFrom').textContent = fromUrl;
        document.getElementById('deleteRedirectForm').action = '{{ route('admin.seo.redirects.destroy', ':id') }}'.replace(':id', index);
        var modal = new bootstrap.Modal(document.getElementById('deleteRedirectModal'));
        modal.show();
    }
</script>
@endpush
