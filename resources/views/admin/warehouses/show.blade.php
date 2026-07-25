@extends('admin.layouts.app')

@section('title', 'Warehouse Details')

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-building me-2"></i>Warehouse Details</h4>
    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Warehouses
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
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Warehouse Name</label>
                        <div class="fw-semibold">{{ $warehouse->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Warehouse Code</label>
                        <div class="fw-semibold">{{ $warehouse->code ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Location Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Address</label>
                    <div class="fw-semibold">{{ $warehouse->address ?? 'N/A' }}</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Cities Served</label>
                        <div class="fw-semibold">
                            @if($warehouse->cities && $warehouse->cities->count() > 0)
                                @foreach($warehouse->cities as $city)
                                    <span class="badge bg-info text-dark me-1 mb-1">{{ $city->name }}</span>
                                @endforeach
                                <small class="d-block text-muted mt-1">{{ $warehouse->cities->sum(fn($c) => $c->activeAreas->count()) }} area(s) across {{ $warehouse->cities->count() }} city(s)</small>
                            @else
                                <span class="text-muted">No cities assigned</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label text-muted small">State/Province</label>
                        <div class="fw-semibold">{{ $warehouse->state ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label text-muted small">Postal Code</label>
                        <div class="fw-semibold">{{ $warehouse->postcode ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label text-muted small">Country</label>
                        <div class="fw-semibold">{{ $warehouse->country ?? 'N/A' }}</div>
                    </div>
                </div>
                @if($warehouse->latitude || $warehouse->longitude)
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Latitude</label>
                        <div class="fw-semibold">{{ $warehouse->latitude ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Longitude</label>
                        <div class="fw-semibold">{{ $warehouse->longitude ?? 'N/A' }}</div>
                    </div>
                </div>
                @else
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Latitude</label>
                        <div class="fw-semibold">N/A</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Longitude</label>
                        <div class="fw-semibold">N/A</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Contact Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Phone Number</label>
                        <div class="fw-semibold">{{ $warehouse->phone ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Email Address</label>
                        <div class="fw-semibold">{{ $warehouse->email ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        @if($warehouse->opening_hours || $warehouse->notes)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-lg me-2"></i>Additional Information</h6>
            </div>
            <div class="card-body">
                @if($warehouse->opening_hours)
                <div class="mb-3">
                    <label class="form-label text-muted small">Opening Hours</label>
                    <div class="fw-semibold">{{ $warehouse->opening_hours }}</div>
                </div>
                @endif
                @if($warehouse->notes)
                <div class="mb-3">
                    <label class="form-label text-muted small">Notes</label>
                    <div class="fw-semibold">{{ $warehouse->notes }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Status -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Status</label>
                    <div>
                        <span class="badge {{ $warehouse->status_badge_class }}">
                            {{ $warehouse->status_text }}
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Primary Warehouse</label>
                    <div>
                        @if($warehouse->is_primary)
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-star me-1"></i> Primary
                            </span>
                        @else
                            <span class="text-muted">No</span>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Sort Order</label>
                    <div class="fw-semibold">{{ $warehouse->sort_order }}</div>
                </div>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Timestamps</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Created At</label>
                    <div class="fw-semibold">{{ $warehouse->created_at ? $warehouse->created_at->format('M d, Y h:i A') : 'N/A' }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Updated At</label>
                    <div class="fw-semibold">{{ $warehouse->updated_at ? $warehouse->updated_at->format('M d, Y h:i A') : 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Actions</h6>
            </div>
            @if(auth()->user()->hasPermission('warehouse.create'))
<div class="card-body">
                <a href="{{ route('admin.warehouses.picking', $warehouse->id ?? 0) }}" class="btn btn-success w-100 mb-2">
                    <i class="bi bi-box-seam me-1"></i> Picking Dashboard
                </a>
                <a href="{{ route('admin.warehouses.orders', $warehouse->id ?? 0) }}" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-list-ul me-1"></i> View Orders
                </a>
                <a href="{{ route('admin.warehouses.edit', $warehouse->id ?? 0) }}" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-pencil me-1"></i> Edit Warehouse
                </a>
                <form action="{{ route('admin.warehouses.destroy', $warehouse->id ?? 0) }}" method="POST" class="d-inline w-100">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Are you sure you want to delete this warehouse?')">
                        <i class="bi bi-trash me-1"></i> Delete Warehouse
                    </button>
                </form>
            </div>
@endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Global Card Styles */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.card-header.bg-white {
    background-color: var(--color-white) !important;
}
</style>
@endpush
