@extends('admin.layouts.app')

@section('title', 'Add Warehouse')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-building me-2"></i>Add Warehouse</h4>
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
                <form id="warehouseForm" method="POST" action="{{ route('admin.warehouses.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Warehouse Code</label>
                            <input type="text" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ $autoCode }}" disabled>
                            <input type="hidden" name="code" form="warehouseForm" value="{{ $autoCode }}">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Auto-generated unique code</div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Location Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Location Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                    <textarea id="address" name="address" form="warehouseForm" class="form-control @error('address') is-invalid @enderror" rows="2" required>{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="country" class="form-label">Country</label>
                        @if($checkoutMode === 'local' && $defaultCountry)
                            <input type="text" id="country" class="form-control" value="{{ $defaultCountry }}" disabled>
                            <input type="hidden" name="country" form="warehouseForm" value="{{ $defaultCountry }}">
                        @else
                            <select id="country" name="country" form="warehouseForm" class="form-select @error('country') is-invalid @enderror">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->name }}" {{ old('country') === $country->name ? 'selected' : '' }}>{{ $country->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                    </div>
                </div>

                <!-- Cities Served -->
                <div class="mb-3">
                    <label class="form-label">Cities Served <span class="text-danger">*</span></label>
                    <div id="city_help_text" class="form-text mb-2">Loading cities...</div>
                    <div id="cities_container" class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                        <div class="text-muted small text-center py-3" id="cities_placeholder">Loading cities...</div>
                    </div>
                    <div id="city_error_container">@error('city_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
                    <div id="area_summary" class="form-text mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        <span id="area_summary_text">All areas within the selected cities will be automatically assigned to this warehouse.</span>
                    </div>
                    <div id="areas_list_container" class="mt-2" style="display:none;">
                        <div class="border rounded p-2 bg-white" style="max-height: 150px; overflow-y: auto;">
                            <div id="areas_list" class="d-flex flex-wrap gap-1"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="postcode" class="form-label">Postal Code</label>
                        <input type="text" id="postcode" name="postcode" form="warehouseForm" class="form-control @error('postcode') is-invalid @enderror" value="{{ old('postcode') }}">
                        @error('postcode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" id="latitude" name="latitude" form="warehouseForm" class="form-control @error('latitude') is-invalid @enderror" placeholder="-90 to 90">
                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" id="longitude" name="longitude" form="warehouseForm" class="form-control @error('longitude') is-invalid @enderror" placeholder="-180 to 180">
                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
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
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" id="phone" name="phone" form="warehouseForm" class="form-control @error('phone') is-invalid @enderror">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" form="warehouseForm" class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-lg me-2"></i>Additional Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="opening_hours" class="form-label">Opening Hours</label>
                    <textarea id="opening_hours" name="opening_hours" form="warehouseForm" class="form-control @error('opening_hours') is-invalid @enderror" rows="2" placeholder="e.g., Mon-Fri: 9AM-6PM"></textarea>
                    @error('opening_hours')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" form="warehouseForm" class="form-control @error('notes') is-invalid @enderror" rows="2"></textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Status -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Status</h6>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" form="warehouseForm" value="1" checked>
                    <label class="form-check-label" for="is_active">
                        <i class="bi bi-check-circle text-success me-1"></i> Active
                    </label>
                    <div class="form-text">Enable or disable this warehouse</div>
                    @error('is_active')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_primary" name="is_primary" form="warehouseForm" value="1">
                    <label class="form-check-label" for="is_primary">
                        <i class="bi bi-star text-warning me-1"></i> Primary Warehouse
                    </label>
                    <div class="form-text">Set as the main warehouse</div>
                    @error('is_primary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Sort Order -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Display Order</h6>
            </div>
            <div class="card-body">
                <label for="sort_order" class="form-label">Sort Order</label>
                <input type="number" id="sort_order" name="sort_order" form="warehouseForm" class="form-control @error('sort_order') is-invalid @enderror" value="0" min="0">
                @error('sort_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Lower numbers appear first</div>
            </div>
        </div>


    </div>
</div>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-secondary floating-reset-btn text-white">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="warehouseForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Warehouse
    </button>
</div>
@endsection

@push('styles')
<style>
/* Add padding at bottom to prevent floating button overlap (Preference.md #2) */
.content-area.has-floating-save {
    padding-bottom: 100px;
}

/* Global Card Styles - matching reference page style */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.card-header.bg-white {
    background-color: #fff !important;
}
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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

        const citiesContainer = document.getElementById('cities_container');
        const cityHelpText = document.getElementById('city_help_text');
        const areaSummaryText = document.getElementById('area_summary_text');
        const areasListContainer = document.getElementById('areas_list_container');
        const areasList = document.getElementById('areas_list');

        // Load all cities with warehouse assignment info
        fetch('{{ route('admin.warehouses.get-cities') }}')
            .then(res => res.json())
            .then(data => {
                citiesContainer.innerHTML = '';
                if (data.success && data.cities && data.cities.length > 0) {
                    // Group cities by state
                    const grouped = {};
                    data.cities.forEach(c => {
                        const key = c.state_name || 'Unknown';
                        if (!grouped[key]) grouped[key] = [];
                        grouped[key].push(c);
                    });

                    Object.keys(grouped).sort().forEach(stateName => {
                        const stateHeader = document.createElement('div');
                        stateHeader.className = 'fw-semibold small text-uppercase text-muted mt-2 mb-1';
                        stateHeader.style.fontSize = '0.75rem';
                        stateHeader.textContent = stateName;
                        citiesContainer.appendChild(stateHeader);

                        grouped[stateName].forEach(c => {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'form-check mb-1';

                            const checkbox = document.createElement('input');
                            checkbox.className = 'form-check-input city-checkbox';
                            checkbox.type = 'checkbox';
                            checkbox.id = `city_${c.id}`;
                            checkbox.value = c.id;
                            checkbox.name = 'city_ids[]';
                            checkbox.setAttribute('form', 'warehouseForm');

                            const label = document.createElement('label');
                            label.className = 'form-check-label';
                            label.htmlFor = `city_${c.id}`;

                            if (c.assigned_to) {
                                checkbox.disabled = true;
                                label.innerHTML = `${c.name} <small class="text-muted">(${c.assigned_to.name})</small>`;
                                wrapper.style.opacity = '0.6';
                                wrapper.title = `Already assigned to ${c.assigned_to.name}`;
                            } else {
                                checkbox.addEventListener('change', updateAreaSummary);
                                label.textContent = c.name;
                            }

                            wrapper.appendChild(checkbox);
                            wrapper.appendChild(label);
                            citiesContainer.appendChild(wrapper);
                        });
                    });
                    cityHelpText.textContent = `${data.cities.length} cities. Already-assigned cities are disabled.`;
                } else {
                    citiesContainer.innerHTML = '<div class="text-muted small text-center py-3">No cities found</div>';
                    cityHelpText.textContent = 'No cities available';
                }
            })
            .catch(e => {
                console.error('Error loading cities:', e);
                citiesContainer.innerHTML = '<div class="text-danger small text-center py-3">Failed to load cities</div>';
                cityHelpText.textContent = 'Error loading cities';
            });

        // Update area summary based on selected cities
        function updateAreaSummary() {
            const checked = document.querySelectorAll('.city-checkbox:checked:not(:disabled)');
            const count = checked.length;
            if (count === 0) {
                areaSummaryText.textContent = 'No cities selected. Please select at least one city.';
                areasListContainer.style.display = 'none';
                return;
            }
            const cityNames = [];
            checked.forEach(cb => {
                const label = document.querySelector(`label[for="${cb.id}"]`);
                if (label) cityNames.push(label.textContent);
            });

            const cityIds = Array.from(checked).map(cb => cb.value);
            if (cityIds.length > 0) {
                fetch(`{{ route('admin.locations.get-areas') }}?${cityIds.map(id => 'city_id[]=' + id).join('&')}`)
                    .then(res => res.json())
                    .then(data => {
                        const areas = data.success && data.areas ? data.areas : [];
                        areaSummaryText.textContent = `${count} city(s) selected (${cityNames.join(', ')}). ${areas.length} area(s) will be served by this warehouse.`;
                        if (areas.length > 0) {
                            areasList.innerHTML = areas.map(a => `<span class="badge bg-light text-dark border px-2 py-1">${a.name}</span>`).join('');
                            areasListContainer.style.display = 'block';
                        } else {
                            areasListContainer.style.display = 'none';
                        }
                    })
                    .catch(() => {
                        areaSummaryText.textContent = `${count} city(s) selected (${cityNames.join(', ')}). Areas will be auto-assigned.`;
                        areasListContainer.style.display = 'none';
                    });
            } else {
                areaSummaryText.textContent = 'No cities selected.';
                areasListContainer.style.display = 'none';
            }
        }
    });
</script>
@endpush
