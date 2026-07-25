@extends('admin.layouts.app')

@section('title', 'Create Affiliate Link')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Create Affiliate Link</h4>
    <a href="{{ route('admin.affiliate.links.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Links
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Link Details</h6>
            </div>
            <div class="card-body">
                <form id="itemForm" method="POST" action="{{ route('admin.affiliate.links.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Link Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="affiliate_id" class="form-label">Affiliate <span class="text-danger">*</span></label>
                        <select class="form-select @error('affiliate_id') is-invalid @enderror" id="affiliate_id" name="affiliate_id" required>
                            <option value="">Select Affiliate</option>
                            @foreach($affiliates as $affiliate)
                            <option value="{{ $affiliate->id }}" {{ old('affiliate_id') == $affiliate->id ? 'selected' : '' }}>
                                {{ $affiliate->user->name ?? 'Unknown' }} ({{ $affiliate->affiliate_code }})
                            </option>
                            @endforeach
                        </select>
                        @error('affiliate_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_id" class="form-label">Product</label>
                                <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id">
                                    <option value="">Select Product (Optional)</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Associate this link with a specific affiliate product (optional)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="affiliate_code" class="form-label">Affiliate Code</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('affiliate_code') is-invalid @enderror" id="affiliate_code" name="affiliate_code" value="{{ old('affiliate_code') }}">
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">
                                        <i class="bi bi-arrow-repeat me-1"></i> Generate
                                    </button>
                                </div>
                                @error('affiliate_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave empty to auto-generate a unique code</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="target_url" class="form-label">Target URL</label>
                                <input type="url" class="form-control @error('target_url') is-invalid @enderror" id="target_url" name="target_url" value="{{ old('target_url') }}" placeholder="https://example.com">
                                @error('target_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Custom redirect URL (optional)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Help</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2"><strong>Affiliate Code:</strong></p>
                <p class="small text-muted">Each link has a unique affiliate code. If you don't provide one, it will be auto-generated.</p>
                <p class="small text-muted mb-2"><strong>Target URL:</strong></p>
                <p class="small text-muted">The page users will be redirected to when they click the affiliate link.</p>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.affiliate.links.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="itemForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Link
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
    function generateCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        for (let i = 0; i < 10; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('affiliate_code').value = code;
    }
</script>
@endpush
