@extends('admin.layouts.app')

@section('title', 'Edit Pick-up Point - ' . $pickupPoint->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="bi bi-shop me-2"></i>Edit Pick-up Point</h4>
            <a href="{{ route('admin.pickup-points.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <form id="pickupPointForm" action="{{ route('admin.pickup-points.update', $pickupPoint->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Location Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $pickupPoint->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="code" class="form-label">Code <span class="text-muted">(Optional)</span></label>
                            <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $pickupPoint->code) }}">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Unique identifier for this location</div>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $pickupPoint->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-muted">(Optional)</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $pickupPoint->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Address Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="2" required>{{ old('address', $pickupPoint->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" id="city" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $pickupPoint->city) }}" required>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="state" class="form-label">State/Province <span class="text-muted">(Optional)</span></label>
                            <input type="text" id="state" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $pickupPoint->state) }}">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="postcode" class="form-label">Postcode <span class="text-muted">(Optional)</span></label>
                            <input type="text" id="postcode" name="postcode" class="form-control @error('postcode') is-invalid @enderror" value="{{ old('postcode', $pickupPoint->postcode) }}">
                            @error('postcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" id="country" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country', $pickupPoint->country) }}" required>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Additional Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="opening_hours" class="form-label">Opening Hours <span class="text-muted">(Optional)</span></label>
                            <textarea id="opening_hours" name="opening_hours" class="form-control @error('opening_hours') is-invalid @enderror" rows="3" placeholder="e.g.&#10;Mon-Fri: 9:00 AM - 6:00 PM&#10;Sat: 10:00 AM - 4:00 PM&#10;Sun: Closed">{{ old('opening_hours', $pickupPoint->opening_hours) }}</textarea>
                            @error('opening_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes <span class="text-muted">(Optional)</span></label>
                            <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $pickupPoint->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <!-- Settings Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Settings</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" form="pickupPointForm" value="1" {{ old('is_active', $pickupPoint->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">
                            <i class="bi bi-check-circle text-success me-1"></i> Active
                        </label>
                    </div>
                    <div class="form-text">Inactive locations won't be available for selection</div>
                </div>
                <div class="mb-0">
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" class="form-control" form="pickupPointForm" value="{{ old('sort_order', $pickupPoint->sort_order) }}" min="0">
                    <div class="form-text">Lower numbers appear first</div>
                </div>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total Orders</span>
                    <span class="badge bg-primary">{{ $pickupPoint->orders()->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Pending</span>
                    <span class="badge bg-warning">{{ $pickupPoint->orders()->where('status', 'pending')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Ready</span>
                    <span class="badge bg-info">{{ $pickupPoint->orders()->where('status', 'confirmed')->whereNull('picked_up_at')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Picked Up</span>
                    <span class="badge bg-success">{{ $pickupPoint->orders()->whereNotNull('picked_up_at')->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Delete Action -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Danger Zone</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Deleting this pick-up point is permanent and cannot be undone. Make sure there are no pending orders assigned to this location.</p>
                <button type="button" class="btn btn-outline-danger w-100" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i> Delete Pick-up Point
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (outside main form to avoid nesting) -->
<form id="deleteForm" action="{{ route('admin.pickup-points.destroy', $pickupPoint->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Floating Save Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.pickup-points.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="pickupPointForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Pick-up Point
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
    // Delete confirmation function
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this pick-up point? This action cannot be undone.')) {
            document.getElementById('deleteForm').submit();
        }
    }

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
