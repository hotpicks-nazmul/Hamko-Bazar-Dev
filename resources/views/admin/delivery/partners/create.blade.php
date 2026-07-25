@extends('admin.layouts.app')

@section('title', 'Add Delivery Partner')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Add Delivery Partner</h4>
    <a href="{{ route('admin.delivery.partners.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Partners
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.delivery.partners.store') }}" method="POST" enctype="multipart/form-data" id="partnerForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Partner Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Enter partner name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">The slug will be auto-generated from the name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="auto-generated-if-empty">
                        </div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Leave empty to auto-generate from name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Enter partner description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="service_type" class="form-label">Service Type</label>
                            <select class="form-select @error('service_type') is-invalid @enderror" id="service_type" name="service_type">
                                <option value="all" {{ old('service_type') === 'all' ? 'selected' : '' }}>All Services</option>
                                <option value="express" {{ old('service_type') === 'express' ? 'selected' : '' }}>Express Delivery</option>
                                <option value="standard" {{ old('service_type') === 'standard' ? 'selected' : '' }}>Standard Delivery</option>
                                <option value="overnight" {{ old('service_type') === 'overnight' ? 'selected' : '' }}>Overnight Delivery</option>
                                <option value="international" {{ old('service_type') === 'international' ? 'selected' : '' }}>International Shipping</option>
                            </select>
                            @error('service_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="coverage_area" class="form-label">Coverage Area</label>
                            <input type="text" class="form-control @error('coverage_area') is-invalid @enderror" id="coverage_area" name="coverage_area" value="{{ old('coverage_area') }}" placeholder="e.g., Dhaka, Chattogram, All Bangladesh">
                            @error('coverage_area')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Contact Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-contact me-2"></i>Contact Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control @error('contact_person') is-invalid @enderror" id="contact_person" name="contact_person" value="{{ old('contact_person') }}" placeholder="Contact person name" form="partnerForm">
                        </div>
                        @error('contact_person')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Phone number" form="partnerForm">
                        </div>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Email address" form="partnerForm">
                        </div>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="website" class="form-label">Website</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-globe"></i></span>
                            <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website') }}" placeholder="https://example.com" form="partnerForm">
                        </div>
                        @error('website')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" placeholder="Full address" form="partnerForm">{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Pricing -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Pricing</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="base_rate" class="form-label">Base Rate (৳)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-tag"></i></span>
                            <input type="number" step="0.01" min="0" class="form-control @error('base_rate') is-invalid @enderror" id="base_rate" name="base_rate" value="{{ old('base_rate', 0) }}" placeholder="0.00" form="partnerForm">
                        </div>
                        @error('base_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Starting delivery charge</div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="cod_charge" class="form-label">COD Charge (৳)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                            <input type="number" step="0.01" min="0" class="form-control @error('cod_charge') is-invalid @enderror" id="cod_charge" name="cod_charge" value="{{ old('cod_charge', 0) }}" placeholder="0.00" form="partnerForm">
                        </div>
                        @error('cod_charge')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Cash on delivery fee</div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="free_shipping_threshold" class="form-label">Free Shipping Threshold (৳)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-gift"></i></span>
                            <input type="number" step="0.01" min="0" class="form-control @error('free_shipping_threshold') is-invalid @enderror" id="free_shipping_threshold" name="free_shipping_threshold" value="{{ old('free_shipping_threshold', 0) }}" placeholder="0.00" form="partnerForm">
                        </div>
                        @error('free_shipping_threshold')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Order value for free shipping</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Logo Upload -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-image me-2"></i>Partner Logo</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="image-upload-preview mb-2 text-center" id="logoPreview" style="display: none;">
                        <img src="" alt="Preview" class="img-thumbnail" style="max-width: 100%; max-height: 200px;">
                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeLogo()">
                            <i class="bi bi-trash me-1"></i> Remove
                        </button>
                    </div>
                    <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*" form="partnerForm" onchange="previewLogo(this)">
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Recommended size: 300x300px. Max 5MB. PNG or JPG.</div>
                </div>
            </div>
        </div>
        
        <!-- Status & Visibility -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Status & Visibility</h6>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} form="partnerForm">
                    <label class="form-check-label" for="is_active">
                        <i class="bi bi-check-circle text-success me-1"></i> Active
                    </label>
                    <div class="form-text">Only active partners will be available for delivery</div>
                </div>
                
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} form="partnerForm">
                    <label class="form-check-label" for="is_featured">
                        <i class="bi bi-star text-warning me-1"></i> Featured
                    </label>
                    <div class="form-text">Featured partners appear first in listings</div>
                </div>
            </div>
        </div>
        
        <!-- Sort Order -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-sort-numeric-up me-2"></i>Display Order</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" placeholder="0" form="partnerForm">
                    @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Lower numbers appear first.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.delivery.partners.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="partnerForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Partner
    </button>
</div>
@endsection

@push('styles')
<style>
    .image-upload-preview img {
        border: 2px solid #dee2e6;
    }
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
    
    // Logo preview
    function previewLogo(input) {
        const preview = document.getElementById('logoPreview');
        const previewImg = preview.querySelector('img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function removeLogo() {
        const input = document.getElementById('logo');
        const preview = document.getElementById('logoPreview');
        
        input.value = '';
        preview.style.display = 'none';
        preview.querySelector('img').src = '';
    }
    
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('blur', function() {
        const name = this.value;
        const slugInput = document.getElementById('slug');
        
        if (name && !slugInput.value) {
            slugInput.value = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    });
</script>
@endpush
