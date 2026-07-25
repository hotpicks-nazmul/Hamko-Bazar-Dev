@extends('admin.layouts.app')

@section('title', 'Add Area')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Add Area</h4>
    <a href="{{ route('admin.locations.areas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Areas
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Area Information</h6>
            </div>
            <div class="card-body">
                <form id="areaForm" method="POST" action="{{ route('admin.locations.areas.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="city_id" class="form-label">City <span class="text-danger">*</span></label>
                        <select id="city_id" name="city_id" class="form-select @error('city_id') is-invalid @enderror" required>
                            <option value="">Select City</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }} ({{ $city->country }})
                                </option>
                            @endforeach
                        </select>
                        @error('city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Area Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="floating-save-container">
    <a href="{{ route('admin.locations.areas.index') }}" class="btn btn-secondary floating-reset-btn text-white">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="areaForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save Area
    </button>
</div>
@endsection

@push('styles')
<style>
.content-area.has-floating-save { padding-bottom: 100px; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.card-header.bg-white { background-color: #fff !important; }
</style>
@endpush
