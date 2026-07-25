@extends('admin.layouts.app')

@section('title', $store->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-shop me-2"></i>{{ $store->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.multi-store.edit', $store->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Store
        </a>
        <a href="{{ route('admin.multi-store.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Stores
        </a>
    </div>
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
                        <label class="form-label text-muted small">Store Name</label>
                        <div class="fw-semibold">{{ $store->name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Slug</label>
                        <div class="">{{ $store->slug }}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Store Code</label>
                        <div class="">{{ $store->code ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Type</label>
                        <div class="">
                            @if($store->is_physical)
                                <span class="badge bg-info">Physical Store</span>
                            @else
                                <span class="badge bg-secondary">Online Store</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($store->description)
                <div class="mb-3">
                    <label class="form-label text-muted small">Description</label>
                    <div class="">{{ $store->description }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Location Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Location Information</h6>
            </div>
            <div class="card-body">
                @if($store->address)
                <div class="mb-3">
                    <label class="form-label text-muted small">Address</label>
                    <div class="">{!! $store->full_address_html !!}</div>
                </div>
                @endif
                <div class="row">
                    @if($store->latitude && $store->longitude)
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Coordinates</label>
                        <div class="">
                            <a href="https://www.google.com/maps?q={{ $store->latitude }},{{ $store->longitude }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-map me-1"></i> View on Map
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Contact Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($store->email)
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Email</label>
                        <div class="">
                            <a href="mailto:{{ $store->email }}">{{ $store->email }}</a>
                        </div>
                    </div>
                    @endif
                    @if($store->phone)
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Phone</label>
                        <div class="">
                            <a href="tel:{{ $store->phone }}">{{ $store->phone }}</a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contact Person -->
        @if($store->contact_person_name)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Contact Person</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($store->contact_person_name)
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small">Name</label>
                        <div class="">{{ $store->contact_person_name }}</div>
                    </div>
                    @endif
                    @if($store->contact_person_phone)
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small">Phone</label>
                        <div class="">{{ $store->contact_person_phone }}</div>
                    </div>
                    @endif
                    @if($store->contact_person_email)
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small">Email</label>
                        <div class="">{{ $store->contact_person_email }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Additional Information -->
        @if($store->opening_hours)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Opening Hours</h6>
            </div>
            <div class="card-body">
                <div class="mb-0">{!! nl2br(e($store->opening_hours)) !!}</div>
            </div>
        </div>
        @endif

        <!-- SEO -->
        @if($store->meta_title || $store->meta_description || $store->meta_keywords)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-search me-2"></i>SEO</h6>
            </div>
            <div class="card-body">
                @if($store->meta_title)
                <div class="mb-3">
                    <label class="form-label text-muted small">Meta Title</label>
                    <div class="">{{ $store->meta_title }}</div>
                </div>
                @endif
                @if($store->meta_description)
                <div class="mb-3">
                    <label class="form-label text-muted small">Meta Description</label>
                    <div class="">{{ $store->meta_description }}</div>
                </div>
                @endif
                @if($store->meta_keywords)
                <div class="mb-0">
                    <label class="form-label text-muted small">Meta Keywords</label>
                    <div class="">{{ $store->meta_keywords }}</div>
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
                    <label class="form-label text-muted small">Current Status</label>
                    <div class="">
                        <span class="badge {{ $store->status_badge_class }}">{{ $store->status_text }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Default Store</label>
                    <div class="">
                        @if($store->is_default)
                            <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i> Yes</span>
                        @else
                            <span class="text-muted">No</span>
                        @endif
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label text-muted small">Sort Order</label>
                    <div class="">{{ $store->sort_order }}</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(!$store->is_default)
                    <form action="{{ route('admin.multi-store.setDefault', $store->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning w-100">
                            <i class="bi bi-star me-1"></i> Set as Default
                        </button>
                    </form>
                    @endif
                    
                    <form action="{{ route('admin.multi-store.toggleStatus', $store->id) }}" method="POST" class="d-inline">
                        @csrf
                        @if($store->is_active)
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-pause-circle me-1"></i> Deactivate
                        </button>
                        @else
                        <button type="submit" class="btn btn-outline-success w-100">
                            <i class="bi bi-play-circle me-1"></i> Activate
                        </button>
                        @endif
                    </form>
                    
                    @if(!$store->is_default && $store->products_count == 0)
                    <form action="{{ route('admin.multi-store.destroy', $store->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this store?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete Store
                        </button>
                    </form>
                    @elseif($store->products_count > 0)
                    <button type="button" class="btn btn-outline-secondary w-100" disabled>
                        <i class="bi bi-trash me-1"></i> Has Products
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Store Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Store Info</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label text-muted small">Products</label>
                    <div class="">
                        <span class="badge bg-primary">{{ $store->products_count }}</span>
                        <a href="{{ route('admin.products.index', ['store' => $store->id]) }}" class="ms-2">View Products</a>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label text-muted small">Created</label>
                    <div class="">{{ $store->created_at->format('M d, Y h:i A') }}</div>
                </div>
                <div class="mb-0">
                    <label class="form-label text-muted small">Last Updated</label>
                    <div class="">{{ $store->updated_at->format('M d, Y h:i A') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
