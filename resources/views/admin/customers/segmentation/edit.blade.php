@extends('admin.layouts.app')

@section('title', 'Edit Customer Segment')

@section('content')
<!-- Header with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Customer Segment</h4>
    <a href="{{ route('admin.customers.segmentation.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Segments
    </a>
</div>

<form id="segmentForm" method="POST" action="{{ route('admin.customers.segmentation.update', $segment->id) }}">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Segment Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Segment Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $segment->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">A descriptive name for this customer segment</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Describe this segment...">{{ old('description', $segment->description) }}</textarea>
                        <div class="form-text">Optional description to help identify this segment</div>
                    </div>
                </div>
            </div>

            <!-- Conditions Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Segment Conditions</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Define the conditions that customers must meet to be included in this segment. At least one condition is required.</p>

                    <!-- Order Count Condition -->
                    <div class="condition-group mb-4 p-3 border rounded">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="order_count_enabled" name="order_count_enabled" 
                                onchange="toggleConditionFields('order_count')"
                                {{ isset($segment->conditions['order_count_min']) || isset($segment->conditions['order_count_max']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="order_count_enabled">
                                <strong>Order Count</strong>
                            </label>
                            <div class="form-text">Filter by number of orders placed</div>
                        </div>
                        <div id="order_count_fields" class="{{ isset($segment->conditions['order_count_min']) || isset($segment->conditions['order_count_max']) ? '' : 'd-none' }}">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Minimum Orders</label>
                                    <input type="number" name="order_count_min" class="form-control form-control-sm" 
                                        value="{{ old('order_count_min', $segment->conditions['order_count_min'] ?? '') }}" placeholder="e.g., 5" min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Maximum Orders</label>
                                    <input type="number" name="order_count_max" class="form-control form-control-sm" 
                                        value="{{ old('order_count_max', $segment->conditions['order_count_max'] ?? '') }}" placeholder="e.g., 20" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Spent Condition -->
                    <div class="condition-group mb-4 p-3 border rounded">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="total_spent_enabled" name="total_spent_enabled" 
                                onchange="toggleConditionFields('total_spent')"
                                {{ isset($segment->conditions['total_spent_min']) || isset($segment->conditions['total_spent_max']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="total_spent_enabled">
                                <strong>Total Spent</strong>
                            </label>
                            <div class="form-text">Filter by total amount spent</div>
                        </div>
                        <div id="total_spent_fields" class="{{ isset($segment->conditions['total_spent_min']) || isset($segment->conditions['total_spent_max']) ? '' : 'd-none' }}">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Minimum Amount</label>
                                    <input type="number" name="total_spent_min" class="form-control form-control-sm" 
                                        value="{{ old('total_spent_min', $segment->conditions['total_spent_min'] ?? '') }}" placeholder="e.g., 1000" min="0" step="0.01">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Maximum Amount</label>
                                    <input type="number" name="total_spent_max" class="form-control form-control-sm" 
                                        value="{{ old('total_spent_max', $segment->conditions['total_spent_max'] ?? '') }}" placeholder="e.g., 10000" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last Order Date Condition -->
                    <div class="condition-group mb-4 p-3 border rounded">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="last_order_days_enabled" name="last_order_days_enabled" 
                                onchange="toggleConditionFields('last_order_days')"
                                {{ isset($segment->conditions['last_order_days']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="last_order_days_enabled">
                                <strong>Last Order Date</strong>
                            </label>
                            <div class="form-text">Filter by how recent their last order was</div>
                        </div>
                        <div id="last_order_days_fields" class="{{ isset($segment->conditions['last_order_days']) ? '' : 'd-none' }}">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Ordered Within (Days)</label>
                                    <input type="number" name="last_order_days" class="form-control form-control-sm" 
                                        value="{{ old('last_order_days', $segment->conditions['last_order_days'] ?? '') }}" placeholder="e.g., 30" min="1">
                                    <div class="form-text">Customers who ordered in the last X days</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Group Condition -->
                    <div class="condition-group mb-4 p-3 border rounded">
                        <div class="mb-3">
                            <label class="form-label"><strong>Customer Group</strong></label>
                            <div class="form-text mb-2">Filter by customer group membership</div>
                            <select name="customer_group_id" class="form-select">
                                <option value="">All Customer Groups</option>
                                @foreach($customerGroups as $group)
                                <option value="{{ $group->id }}" {{ old('customer_group_id', $segment->conditions['customer_group_id'] ?? '') == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Registration Date Condition -->
                    <div class="condition-group mb-4 p-3 border rounded">
                        <div class="mb-3">
                            <label class="form-label"><strong>Registration Date</strong></label>
                            <div class="form-text mb-2">Filter by account creation date</div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small">From</label>
                                    <input type="date" name="registration_date_from" class="form-control form-control-sm" 
                                        value="{{ old('registration_date_from', $segment->conditions['registration_date_from'] ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">To</label>
                                    <input type="date" name="registration_date_to" class="form-control form-control-sm" 
                                        value="{{ old('registration_date_to', $segment->conditions['registration_date_to'] ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Status Condition -->
                    <div class="condition-group p-3 border rounded">
                        <div class="mb-3">
                            <label class="form-label"><strong>Customer Status</strong></label>
                            <div class="form-text mb-2">Filter by account status</div>
                            <select name="customer_status" class="form-select">
                                <option value="">All Customers</option>
                                <option value="active" {{ old('customer_status', $segment->conditions['is_active'] ?? '') === true || old('customer_status') === 'active' ? 'selected' : '' }}>Active Only</option>
                                <option value="inactive" {{ old('customer_status', $segment->conditions['is_active'] ?? '') === false || old('customer_status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                            </select>
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
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                            {{ $segment->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            <i class="bi bi-check-circle text-success me-1"></i> Active
                        </label>
                        <div class="form-text">Enable this segment for use</div>
                    </div>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Customers in Segment</span>
                        <span class="badge bg-primary">{{ number_format($segment->customer_count) }}</span>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="previewSegment()">
                        <i class="bi bi-arrow-repeat me-1"></i> Recalculate
                    </button>
                    <div id="preview_result" class="mt-3 d-none">
                        <div class="alert alert-info mb-0">
                            <strong id="preview_count">0</strong> customers match
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Danger Zone</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Once you delete a segment, there is no going back.</p>
                    <form action="{{ route('admin.customers.segmentation.destroy', $segment->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Are you sure you want to delete this segment?')">
                            <i class="bi bi-trash me-1"></i> Delete Segment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.customers.segmentation.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="segmentForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Segment
    </button>
</div>
@endsection

@push('styles')
<style>
    .condition-group {
        background-color: #f8f9fa;
    }
    .condition-group:hover {
        background-color: #f1f3f5;
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleConditionFields(field) {
        const checkbox = document.getElementById(field + '_enabled');
        const fields = document.getElementById(field + '_fields');
        
        if (checkbox.checked) {
            fields.classList.remove('d-none');
        } else {
            fields.classList.add('d-none');
        }
    }

    function previewSegment() {
        const form = document.getElementById('segmentForm');
        const formData = new FormData(form);
        
        // Build query string
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value && key !== '_token' && key !== 'name' && key !== 'description' && key !== 'is_active' && key !== '_method') {
                const checkbox = document.querySelector(`[name="${key}_enabled"]`);
                if (checkbox && checkbox.checked) {
                    params.append(key, value);
                } else if (!key.endsWith('_enabled')) {
                    params.append(key, value);
                }
            }
        }
        
        // Add enabled flags
        document.querySelectorAll('input[type="checkbox"][name$="_enabled"]').forEach(checkbox => {
            if (checkbox.checked) {
                params.append(checkbox.name, '1');
            }
        });

        fetch('{{ route("admin.customers.segmentation.preview") }}?' + params.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('preview_result');
            const countSpan = document.getElementById('preview_count');
            resultDiv.classList.remove('d-none');
            countSpan.textContent = data.count.toLocaleString();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to preview. Please check your conditions.');
        });
    }
</script>
@endpush
