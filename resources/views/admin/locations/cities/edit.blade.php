@extends('admin.layouts.app')

@section('title', 'Edit City')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-building me-2"></i>Edit City</h4>
    <a href="{{ route('admin.locations.cities.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Cities
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>City Information</h6>
            </div>
            <div class="card-body">
                <form id="cityForm" method="POST" action="{{ route('admin.locations.cities.update', $city->id) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">City Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $city->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="country_id" class="form-label">Country <span class="text-danger">*</span></label>
                        <select id="country_id" name="country_id" class="form-select @error('country_id') is-invalid @enderror" required>
                            <option value="">Select Country</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('country_id', $city->country_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('country_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="state_id" class="form-label">State / Province</label>
                        <select id="state_id" name="state_id" class="form-select @error('state_id') is-invalid @enderror">
                            <option value="">Select State / Province</option>
                            @foreach($states as $s)
                                <option value="{{ $s->id }}" data-country="{{ $s->country_id }}" {{ old('state_id', $city->state_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('state_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', $city->sort_order) }}" min="0">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', $city->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="floating-save-container">
    <a href="{{ route('admin.locations.cities.index') }}" class="btn btn-secondary floating-reset-btn text-white">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <form action="{{ route('admin.locations.cities.destroy', $city->id) }}" method="POST" class="d-inline">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-outline-danger floating-reset-btn" onclick="return confirm('Delete this city and all its areas?')">
            <i class="bi bi-trash me-1"></i> Delete
        </button>
    </form>
    <button type="submit" form="cityForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update City
    </button>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('country_id')?.addEventListener('change', function() {
    const countryId = this.value;
    document.querySelectorAll('#state_id option').forEach(opt => {
        if (opt.value === '') return;
        opt.style.display = opt.dataset.country === countryId ? '' : 'none';
    });
});
const countrySelect = document.getElementById('country_id');
if (countrySelect) countrySelect.dispatchEvent(new Event('change'));
</script>
@endpush

@push('styles')
<style>
.content-area.has-floating-save { padding-bottom: 100px; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.card-header.bg-white { background-color: #fff !important; }
</style>
@endpush
