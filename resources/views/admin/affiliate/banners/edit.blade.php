@extends('admin.layouts.app')

@section('title', 'Edit Affiliate Banner')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Affiliate Banner</h4>
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

<form id="bannerForm" method="POST" action="{{ route('admin.affiliate.banners.update', $banner->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-image me-2"></i>Current Banner</h6>
                </div>
                <div class="card-body text-center">
                    @php
                        $imageUrl = $banner->image;
                        if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                            $imageUrl = '/storage/' . $imageUrl;
                        }
                    @endphp
                    @if($imageUrl)
                    <img src="{{ asset($imageUrl) }}" alt="{{ $banner->name }}" style="max-width: 100%; max-height: 200px;" class="rounded">
                    <p class="mt-2 text-muted">Size: {{ $banner->width }}x{{ $banner->height }} pixels</p>
                    @else
                    <span class="text-muted">No image uploaded</span>
                    @endif
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Banner Details</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Banner Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $banner->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="affiliate_id" class="form-label">Affiliate</label>
                            <select class="form-select @error('affiliate_id') is-invalid @enderror" id="affiliate_id" name="affiliate_id">
                                <option value="">General Banner (No specific affiliate)</option>
                                @foreach($affiliates as $affiliate)
                                <option value="{{ $affiliate->id }}" {{ old('affiliate_id', $banner->affiliate_id) == $affiliate->id ? 'selected' : '' }}>
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
                            <label for="image" class="form-label">Banner Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                            @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave empty to keep current image. Supported formats: JPG, PNG, GIF, WebP. Max size: 2MB</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Image Preview</label>
                            <div id="imagePreview" class="border rounded p-2 text-center" style="min-height: 100px;">
                                <span class="text-muted">No new image selected</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="width" class="form-label">Width (px)</label>
                            <input type="number" class="form-control @error('width') is-invalid @enderror" id="width" name="width" value="{{ old('width', $banner->width) }}" min="1">
                            @error('width')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="height" class="form-label">Height (px)</label>
                            <input type="number" class="form-control @error('height') is-invalid @enderror" id="height" name="height" value="{{ old('height', $banner->height) }}" min="1">
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
                            <input type="hidden" name="size" id="sizeField" value="{{ old('size', $banner->size) }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="target_url" class="form-label">Target URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                <input type="url" class="form-control @error('target_url') is-invalid @enderror" id="target_url" name="target_url" value="{{ old('target_url', $banner->target_url) }}">
                            </div>
                            @error('target_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active" {{ old('status', $banner->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $banner->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $banner->description) }}</textarea>
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
                    <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Statistics</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Clicks:</strong> {{ number_format($banner->clicks) }}</p>
                    <p class="mb-0"><strong>Created:</strong> {{ $banner->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Help</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Upload a new banner image to replace the current one.</p>
                    <p class="small text-muted mb-0">Choose a preset size or enter custom dimensions.</p>
                </div>
            </div>
        </div>
    </div>
</form>

<form id="deleteForm" action="{{ route('admin.affiliate.banners.destroy', $banner->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<div class="floating-save-container">
    <a href="{{ route('admin.affiliate.banners.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i>Cancel
    </a>
    <a href="#" class="btn btn-outline-danger floating-reset-btn" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this banner?')) { document.getElementById('deleteForm').submit(); }">
        <i class="bi bi-trash me-1"></i>Delete
    </a>
    <button type="submit" form="bannerForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i>Update Banner
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
