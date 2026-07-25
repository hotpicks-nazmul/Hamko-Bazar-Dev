@extends('admin.layouts.app')

@section('title', 'Edit Affiliate Link')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Edit Affiliate Link</h4>
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
        <!-- Link URL Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Link URL</h6>
            </div>
            <div class="card-body">
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ $link->full_url }}" readonly id="linkUrl">
                    <button class="btn btn-outline-primary" type="button" onclick="copyLink()">
                        <i class="bi bi-clipboard me-1"></i>Copy
                    </button>
                </div>
                <div class="form-text">This is the URL affiliates should share to track clicks and conversions.</div>
            </div>
        </div>

        <!-- Link Details Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Link Details</h6>
            </div>
            <div class="card-body">
                <form id="itemForm" method="POST" action="{{ route('admin.affiliate.links.update', $link->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Link Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $link->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="affiliate_id" class="form-label">Affiliate <span class="text-danger">*</span></label>
                        <select class="form-select @error('affiliate_id') is-invalid @enderror" id="affiliate_id" name="affiliate_id" required>
                            <option value="">Select Affiliate</option>
                            @foreach($affiliates as $affiliate)
                            <option value="{{ $affiliate->id }}" {{ old('affiliate_id', $link->affiliate_id) == $affiliate->id ? 'selected' : '' }}>
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
                                    <option value="{{ $product->id }}" {{ old('product_id', $link->product_id) == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="affiliate_code" class="form-label">Affiliate Code</label>
                                <input type="text" class="form-control" id="affiliate_code" name="affiliate_code" value="{{ old('affiliate_code', $link->affiliate_code) }}" readonly>
                                <div class="form-text">Code cannot be changed after creation</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="target_url" class="form-label">Target URL</label>
                                <input type="url" class="form-control @error('target_url') is-invalid @enderror" id="target_url" name="target_url" value="{{ old('target_url', $link->target_url) }}">
                                @error('target_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', $link->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $link->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $link->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Statistics Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="mb-1 text-primary">{{ number_format($link->clicks) }}</h4>
                            <small class="text-muted">Clicks</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="mb-1 text-success">{{ number_format($link->conversions) }}</h4>
                        <small class="text-muted">Conversions</small>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <span class="badge bg-{{ $link->clicks > 0 ? ($link->conversions / $link->clicks * 100 >= 5 ? 'success' : 'warning') : 'secondary' }} fs-6">
                        {{ $link->clicks > 0 ? number_format(($link->conversions / $link->clicks) * 100, 2) : 0 }}% Conversion Rate
                    </span>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Help</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2"><strong>Tracking:</strong></p>
                <p class="small text-muted">Each click on this link is tracked and recorded. Conversions are recorded when a referred user makes a purchase.</p>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.affiliate.links.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <form action="{{ route('admin.affiliate.links.destroy', $link->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this link?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger floating-reset-btn">
            <i class="bi bi-trash me-1"></i> Delete
        </button>
    </form>
    <button type="submit" form="itemForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Link
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
    function copyLink() {
        var copyText = document.getElementById('linkUrl');
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        
        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = '<div class="toast show" role="alert"><div class="toast-body"><i class="bi bi-check-circle text-success me-2"></i>Link copied to clipboard!</div></div>';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
</script>
@endpush
