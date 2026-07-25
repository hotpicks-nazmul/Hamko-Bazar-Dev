@extends('admin.layouts.app')

@section('title', 'Create Membership Plan')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create Membership Plan</h4>
    <a href="{{ route('admin.customers.membership.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Plans
    </a>
</div>

<form id="itemForm" method="POST" action="{{ route('admin.customers.membership.store') }}">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <!-- Plan Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Plan Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Enter a unique name for this membership plan (e.g., Gold, Platinum, VIP)</div>
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
                            <div class="form-text">Optional description for this membership plan</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Pricing & Duration Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Pricing & Duration</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Price -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', 0) }}" min="0" step="0.01" required>
                                </div>
                                @error('price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="form-text">Set to 0 for free membership</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="duration_days" class="form-label">Duration (Days) <span class="text-danger">*</span></label>
                                <input type="number" id="duration_days" name="duration_days" class="form-control @error('duration_days') is-invalid @enderror" value="{{ old('duration_days', 365) }}" min="1" required>
                                @error('duration_days')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="form-text">Plan validity period in days</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Discount Percentage -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discount_percentage" class="form-label">Discount Percentage</label>
                                <div class="input-group">
                                    <input type="number" id="discount_percentage" name="discount_percentage" class="form-control @error('discount_percentage') is-invalid @enderror" value="{{ old('discount_percentage', 0) }}" min="0" max="100" step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('discount_percentage')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="form-text">Discount on orders for members</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Minimum Spent -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="minimum_spent" class="form-label">Minimum Spend Required</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="minimum_spent" name="minimum_spent" class="form-control @error('minimum_spent') is-invalid @enderror" value="{{ old('minimum_spent', 0) }}" min="0" step="0.01">
                                </div>
                                @error('minimum_spent')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="form-text">Minimum order amount to avail discount</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Benefits Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-gift me-2"></i>Benefits</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="benefits" class="form-label">Plan Benefits</label>
                        <textarea id="benefits" name="benefits" rows="5" class="form-control @error('benefits') is-invalid @enderror" placeholder="Enter each benefit on a new line">{{ old('benefits') }}</textarea>
                        @error('benefits')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">List the benefits of this membership plan (one per line)</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Validity Period Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-calendar me-2"></i>Validity Period (Optional)</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_from" class="form-label">Valid From</label>
                                <input type="date" id="valid_from" name="valid_from" class="form-control @error('valid_from') is-invalid @enderror" value="{{ old('valid_from') }}">
                                @error('valid_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">Leave empty for always valid</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_until" class="form-label">Valid Until</label>
                                <input type="date" id="valid_until" name="valid_until" class="form-control @error('valid_until') is-invalid @enderror" value="{{ old('valid_until') }}">
                                @error('valid_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">Leave empty for no expiration</div>
                                @enderror
                            </div>
                        </div>
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
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            <i class="bi bi-check-circle text-success me-1"></i> Active
                        </label>
                        <div class="form-text">Inactive plans will not be visible to customers</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" {{ old('is_featured') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">
                            <i class="bi bi-star-fill text-warning me-1"></i> Featured
                        </label>
                        <div class="form-text">Featured plans appear prominently</div>
                    </div>
                </div>
            </div>

            <!-- Appearance Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Appearance</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon Class</label>
                        <input type="text" id="icon" name="icon" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon') }}" placeholder="bi bi-star">
                        @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Bootstrap Icons class (e.g., bi-star, bi-gem)</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <div class="d-flex flex-wrap gap-2">
                            @php
                                $colors = ['#6c757d', '#0d6efd', '#198754', '#dc3545', '#ffc107', '#0dcaf0', '#6610f2', '#e83e8c', '#fd7e14', '#20c997'];
                            @endphp
                            @foreach($colors as $color)
                                <input type="radio" class="btn-check" name="color" id="color_{{ str_replace('#', '', $color) }}" value="{{ $color }}" {{ old('color', '#6c757d') == $color ? 'checked' : '' }}>
                                <label class="color-option" style="background-color: {{ $color }};" for="color_{{ str_replace('#', '', $color) }}"></label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Limits Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-people me-2"></i>Membership Limits</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="max_members" class="form-label">Maximum Members</label>
                        <input type="number" id="max_members" name="max_members" class="form-control @error('max_members') is-invalid @enderror" value="{{ old('max_members') }}" min="1" placeholder="Unlimited">
                        @error('max_members')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Leave empty for unlimited members</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}" min="0">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Display order (lower numbers appear first)</div>
                        @enderror
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
                        <li class="mb-2"><i class="bi bi-check2 me-1"></i> Create plans like "Silver", "Gold", "Platinum"</li>
                        <li class="mb-2"><i class="bi bi-check2 me-1"></i> Set higher discounts for premium plans</li>
                        <li class="mb-2"><i class="bi bi-check2 me-1"></i> Add benefits to entice customers</li>
                        <li><i class="bi bi-check2 me-1"></i> Feature your best plans for visibility</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.customers.membership.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="itemForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Plan
    </button>
</div>

@push('scripts')
<script>
    // Auto-generate slug from name
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    nameInput.addEventListener('blur', function() {
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

    // Color selection visual feedback
    document.querySelectorAll('.color-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.color-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
</script>
@endpush

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
    .color-option {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        display: inline-block;
    }
    .color-option.selected {
        border-color: #000;
        transform: scale(1.1);
    }
    .icon-option {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: #f8f9fa;
    }
    .icon-option.selected {
        border-color: #0d6efd;
        background: #e7f1ff;
    }
</style>
@endpush
@endsection
