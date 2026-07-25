@extends('admin.layouts.app')

@section('title', 'Add Customer Group')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Add Customer Group</h4>
    <a href="{{ route('admin.customers.groups.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Groups
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Basic Info Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
            </div>
            <div class="card-body">
                <form id="itemForm" method="POST" action="{{ route('admin.customers.groups.store') }}">
                    @csrf
                    
                    <!-- Group Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Group Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Enter a unique name for this customer group (e.g., VIP, Wholesale, Retail)</div>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                            <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="auto-generated">
                        </div>
                        @error('slug')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @else
                            <div class="form-text">URL-friendly version of the name. Leave empty for auto-generation.</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Optional description for this customer group</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>

        <!-- Discount Settings Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-percent me-2"></i>Discount Settings</h6>
            </div>
            <div class="card-body">
                <!-- Discount Percentage -->
                <div class="mb-3">
                    <label for="discount_percentage" class="form-label">Discount Percentage</label>
                    <div class="input-group">
                        <input type="number" id="discount_percentage" name="discount_percentage" form="itemForm" class="form-control @error('discount_percentage') is-invalid @enderror" value="{{ old('discount_percentage', 0) }}" min="0" max="100" step="0.01">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('discount_percentage')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @else
                        <div class="form-text">Optional discount percentage for members of this group</div>
                    @enderror
                </div>

                <!-- Sort Order -->
                <div class="mb-3">
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" form="itemForm" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}" min="0">
                    @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="form-text">Display order (lower numbers appear first)</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Status Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Status</h6>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" form="itemForm" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        <i class="bi bi-check-circle text-success me-1"></i> Active
                    </label>
                    <div class="form-text">Inactive groups will not be visible to customers</div>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small text-muted">
                    <li class="mb-2"><i class="bi bi-check2 me-1"></i> Create groups like "VIP", "Wholesale", "Retail" etc.</li>
                    <li class="mb-2"><i class="bi bi-check2 me-1"></i> Assign discount percentages for special pricing</li>
                    <li class="mb-2"><i class="bi bi-check2 me-1"></i> Customers can be assigned to groups from their profile</li>
                    <li><i class="bi bi-check2 me-1"></i> Inactive groups are hidden from customers</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.customers.groups.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="itemForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Group
    </button>
</div>

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-generate slug from name on input
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    nameInput.addEventListener('input', function() {
        if (!slugInput.dataset.modified && slugInput.value === '') {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    });

    slugInput.addEventListener('input', function() {
        this.dataset.modified = 'true';
    });
</script>
@endpush
@endsection
