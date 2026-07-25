@extends('admin.layouts.app')

@section('title', 'Edit Delivery Schedule')

@section('content')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Edit Delivery Schedule</h4>
        <a href="{{ route('admin.delivery.schedules.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Schedules
        </a>
    </div>

    <form id="scheduleForm" method="POST" action="{{ route('admin.delivery.schedules.update', $schedule->id) }}">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Basic Info -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Schedule Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $schedule->name) }}" required placeholder="e.g., Morning Delivery, Evening Slot">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Enter a descriptive name for this delivery schedule</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Optional description...">{{ old('description', $schedule->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">Schedule Type <span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="same_day" {{ old('type', $schedule->type) === 'same_day' ? 'selected' : '' }}>Same Day Delivery</option>
                                <option value="next_day" {{ old('type', $schedule->type) === 'next_day' ? 'selected' : '' }}>Next Day Delivery</option>
                                <option value="express" {{ old('type', $schedule->type) === 'express' ? 'selected' : '' }}>Express Delivery</option>
                                <option value="scheduled" {{ old('type', $schedule->type) === 'scheduled' ? 'selected' : '' }}>Scheduled Delivery</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Choose the type of delivery schedule</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" id="sort_order" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                   value="{{ old('sort_order', $schedule->sort_order ?? 0) }}" min="0" placeholder="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Display order (higher values appear first)</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Schedule Settings -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Schedule Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="day_of_week" class="form-label">Day of Week</label>
                                <select id="day_of_week" name="day_of_week" class="form-select @error('day_of_week') is-invalid @enderror">
                                    <option value="">Select Day</option>
                                    <option value="0" {{ old('day_of_week', $schedule->day_of_week) === '0' ? 'selected' : '' }}>Sunday</option>
                                    <option value="1" {{ old('day_of_week', $schedule->day_of_week) == '1' ? 'selected' : '' }}>Monday</option>
                                    <option value="2" {{ old('day_of_week', $schedule->day_of_week) == '2' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="3" {{ old('day_of_week', $schedule->day_of_week) == '3' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="4" {{ old('day_of_week', $schedule->day_of_week) == '4' ? 'selected' : '' }}>Thursday</option>
                                    <option value="5" {{ old('day_of_week', $schedule->day_of_week) == '5' ? 'selected' : '' }}>Friday</option>
                                    <option value="6" {{ old('day_of_week', $schedule->day_of_week) == '6' ? 'selected' : '' }}>Saturday</option>
                                    <option value="7" {{ old('day_of_week', $schedule->day_of_week) == '7' ? 'selected' : '' }}>Everyday</option>
                                </select>
                                @error('day_of_week')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">Leave empty or select "Everyday" for all days</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="max_orders" class="form-label">Max Orders</label>
                                <input type="number" id="max_orders" name="max_orders" class="form-control @error('max_orders') is-invalid @enderror"
                                       value="{{ old('max_orders', $schedule->max_orders) }}" min="1" placeholder="Unlimited">
                                @error('max_orders')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">Maximum orders per slot (leave empty for unlimited)</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="time" id="start_time" name="start_time" class="form-control @error('start_time') is-invalid @enderror"
                                       value="{{ old('start_time', $schedule->start_time) }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">When delivery window starts</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                                <input type="time" id="end_time" name="end_time" class="form-control @error('end_time') is-invalid @enderror"
                                       value="{{ old('end_time', $schedule->end_time) }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">When delivery window ends</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="cutoff_time" class="form-label">Order Cutoff Time</label>
                                <input type="time" id="cutoff_time" name="cutoff_time" class="form-control @error('cutoff_time') is-invalid @enderror"
                                       value="{{ old('cutoff_time', $schedule->cutoff_time) }}">
                                @error('cutoff_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">Latest time to place order</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pricing -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Pricing</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="additional_fee" class="form-label">Additional Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('app.currency_symbol', '$') }}</span>
                                    <input type="number" id="additional_fee" name="additional_fee" class="form-control @error('additional_fee') is-invalid @enderror"
                                           value="{{ old('additional_fee', $schedule->additional_fee) }}" min="0" step="0.01">
                                </div>
                                @error('additional_fee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">Extra charge for this delivery slot</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="min_order_amount" class="form-label">Minimum Order Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('app.currency_symbol', '$') }}</span>
                                    <input type="number" id="min_order_amount" name="min_order_amount" class="form-control @error('min_order_amount') is-invalid @enderror"
                                           value="{{ old('min_order_amount', $schedule->min_order_amount) }}" min="0" step="0.01">
                                </div>
                                @error('min_order_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">Minimum order for this slot</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Availability -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-calendar-range me-2"></i>Availability Period</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="available_from" class="form-label">Available From</label>
                                <input type="datetime-local" id="available_from" name="available_from" class="form-control @error('available_from') is-invalid @enderror"
                                       value="{{ old('available_from', $schedule->available_from ? $schedule->available_from->format('Y-m-d\TH:i') : '') }}">
                                @error('available_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">When this schedule becomes active</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="available_to" class="form-label">Available To</label>
                                <input type="datetime-local" id="available_to" name="available_to" class="form-control @error('available_to') is-invalid @enderror"
                                       value="{{ old('available_to', $schedule->available_to ? $schedule->available_to->format('Y-m-d\TH:i') : '') }}">
                                @error('available_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">When this schedule expires</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Delivery Zones -->
                        @if($zones->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">Delivery Zones</label>
                            <div class="row">
                                @foreach($zones as $zone)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="delivery_zones[]" 
                                               value="{{ $zone->id }}" id="zone_{{ $zone->id }}"
                                               {{ in_array($zone->id, old('delivery_zones', $schedule->delivery_zones ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="zone_{{ $zone->id }}">
                                            {{ $zone->name }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="form-text">Select zones where this schedule applies (leave empty for all)</div>
                        </div>
                        @endif
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
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   value="1" {{ old('is_active', $schedule->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="bi bi-check-circle text-success me-1"></i> Active
                            </label>
                            <div class="form-text">Enable or disable this schedule</div>
                        </div>
                    </div>
                </div>
                
                <!-- Info -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Information</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><small class="text-muted">Created:</small></p>
                        <p class="mb-2">{{ $schedule->created_at->format('M d, Y h:i A') }}</p>
                        
                        <p class="mb-1"><small class="text-muted">Last Updated:</small></p>
                        <p class="mb-0">{{ $schedule->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <!-- Delete Form -->
    <form id="deleteForm" method="POST" action="{{ route('admin.delivery.schedules.destroy', $schedule->id) }}">
        @csrf
        @method('DELETE')
    </form>
    
    <!-- Floating Buttons -->
    <div class="floating-save-container">
        <a href="{{ route('admin.delivery.schedules.index') }}" class="btn btn-secondary floating-reset-btn">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button type="button" class="btn btn-outline-danger floating-reset-btn" 
                onclick="if(confirm('Are you sure you want to delete this schedule?')) { document.getElementById('deleteForm').submit(); }">
            <i class="bi bi-trash me-1"></i> Delete
        </button>
        <button type="submit" form="scheduleForm" class="btn btn-primary floating-save-btn">
            <i class="bi bi-check-lg me-1"></i> Update Schedule
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
