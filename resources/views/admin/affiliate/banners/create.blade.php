@extends('admin.layouts.app')

@section('title', 'Create Affiliate Banner')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create Affiliate Banner</h4>
    <a href="{{ route('admin.affiliate.banners.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Banners
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

<form id="bannerForm" method="POST" action="{{ route('admin.affiliate.banners.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-image me-2"></i>Banner Details</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Banner Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="affiliate_id" class="form-label">Affiliate</label>
                            <select class="form-select @error('affiliate_id') is-invalid @enderror" id="affiliate_id" name="affiliate_id">
                                <option value="">General Banner (No specific affiliate)</option>
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
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="image" class="form-label">Banner Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*" required onchange="previewImage(this)">
                            @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Supported formats: JPG, PNG, GIF, WebP. Max size: 2MB</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Preview</label>
                            <div id="imagePreview" class="border rounded p-2 text-center" style="min-height: 100px;">
                                <span class="text-muted">No image selected</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="width" class="form-label">Width (px)</label>
                            <input type="number" class="form-control @error('width') is-invalid @enderror" id="width" name="width" value="{{ old('width', 728) }}" min="1">
                            @error('width')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="height" class="form-label">Height (px)</label>
                            <input type="number" class="form-control @error('height') is-invalid @enderror" id="height" name="height" value="{{ old('height', 90) }}" min="1">
                            @error('height')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="presetSizes" class="form-label">Preset Sizes</label>
                            <select class="form-select" id="presetSizes" onchange="applyPreset()">
                                <option value="">Custom Size</option>
                                <option value="728,90,Leaderboard">Leaderboard (728x90)</option>
                                <option value="468,60,Banner">Banner (468x60)</option>
                                <option value="300,250,Medium Rectangle">Medium Rectangle (300x250)</option>
                                <option value="336,280,Large Rectangle">Large Rectangle (336x280)</option>
                                <option value="160,600,Wide Skyscraper">Wide Skyscraper (160x600)</option>
                                <option value="120,600,Skyscraper">Skyscraper (120x600)</option>
                                <option value="320,50,Mobile Banner">Mobile Banner (320x50)</option>
                                <option value="320,100,Large Mobile Banner">Large Mobile Banner (320x100)</option>
                            </select>
                            <input type="hidden" name="size" id="sizeField" value="{{ old('size') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="target_url" class="form-label">Target URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                <input type="url" class="form-control @error('target_url') is-invalid @enderror" id="target_url" name="target_url" value="{{ old('target_url') }}" placeholder="https://example.com">
                            </div>
                            @error('target_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">URL where users will be redirected when clicking the banner</div>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Help</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Upload a banner image in common web formats (JPG, PNG, GIF, or WebP).</p>
                    <p class="small text-muted mb-0">Choose a preset size or enter custom dimensions.</p>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="floating-save-container">
    <a href="{{ route('admin.affiliate.banners.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i>Cancel
    </a>
    <button type="submit" form="bannerForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i>Create Banner
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
    function previewImage(input) {
        var preview = document.getElementById('imagePreview');
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 100%; max-height: 200px;">';
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function applyPreset() {
        var preset = document.getElementById('presetSizes').value;
        if (preset) {
            var parts = preset.split(',');
            document.getElementById('width').value = parts[0];
            document.getElementById('height').value = parts[1];
            document.getElementById('sizeField').value = parts[2] || '';
        } else {
            document.getElementById('sizeField').value = '';
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->any())
        var firstErrorField = document.querySelector('.is-invalid');
        if (firstErrorField) {
            setTimeout(function() {
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstErrorField.focus();
            }, 100);
        }
        @endif
    });
</script>
@endpush
