@extends('admin.layouts.app')

@section('title', 'VAT & Tax')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">VAT & Tax Settings</h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaxModal">
        <i class="bi bi-plus-lg me-1"></i> Add New Tax
    </button>
</div>

<!-- Tax Settings Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-sliders me-2"></i>Tax Configuration</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.settings.vat-tax.updateSettings') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="tax_enabled" name="tax_enabled" value="1" {{ ($taxSettings['tax_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="tax_enabled">
                                <i class="bi bi-check-circle text-success me-1"></i> Enable Tax Calculation
                            </label>
                        </div>
                        <div class="form-text">Enable or disable tax calculation across the store</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="tax_per_product" name="tax_per_product" value="1" {{ ($taxSettings['tax_per_product'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="tax_per_product">
                                <i class="bi bi-box-seam me-1"></i> Tax Per Product
                            </label>
                        </div>
                        <div class="form-text">Enable to set tax rates per product instead of global</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label for="tax_type" class="form-label">Tax Type</label>
                        <select id="tax_type" name="tax_type" class="form-select @error('tax_type') is-invalid @enderror">
                            <option value="global" {{ ($taxSettings['tax_type'] ?? 'global') == 'global' ? 'selected' : '' }}>Global Tax</option>
                            <option value="location" {{ ($taxSettings['tax_type'] ?? 'global') == 'location' ? 'selected' : '' }}>Location Based Tax</option>
                        </select>
                        @error('tax_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Global: Single tax rate for all locations. Location: Different rates based on customer location.</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label for="tax_calculation_address" class="form-label">Tax Calculation Address</label>
                        <select id="tax_calculation_address" name="tax_calculation_address" class="form-select @error('tax_calculation_address') is-invalid @enderror">
                            <option value="shipping" {{ ($taxSettings['tax_calculation_address'] ?? 'shipping') == 'shipping' ? 'selected' : '' }}>Shipping Address</option>
                            <option value="billing" {{ ($taxSettings['tax_calculation_address'] ?? 'shipping') == 'billing' ? 'selected' : '' }}>Billing Address</option>
                        </select>
                        @error('tax_calculation_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Which address to use for calculating tax</div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> Save Settings
            </button>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-receipt"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Taxes</span>
            <span class="stat-card-value">{{ $taxes->count() }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value">{{ $taxes->where('is_active', true)->count() }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value">{{ $taxes->where('is_active', false)->count() }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-star"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Default Rate</span>
            <span class="stat-card-value">{{ $defaultTax ? $defaultTax->rate . '%' : 'None' }}</span>
        </div>
    </div>
</div>

<!-- Taxes Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Tax Name</th>
                        <th>Location</th>
                        <th>Rate</th>
                        <th style="width: 100px;">Type</th>
                        <th style="width: 80px;">Default</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxes as $tax)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <span class="fw-medium">{{ $tax->name }}</span>
                        </td>
                        <td>
                            @if($tax->country)
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-globe me-1"></i>{{ $tax->country }}
                                @if($tax->state)
                                , {{ $tax->state }}
                                @endif
                                @if($tax->zip_code)
                                - {{ $tax->zip_code }}
                                @endif
                            </span>
                            @else
                            <span class="badge bg-secondary">Global</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold text-primary">
                                @if($tax->type === 'percentage')
                                {{ number_format($tax->rate, 2) }}%
                                @else
                                {{ config('app.currency_symbol', '$') }}{{ number_format($tax->rate, 2) }}
                                @endif
                            </span>
                        </td>
                        <td>
                            @if($tax->type === 'percentage')
                            <span class="badge bg-info">Percentage</span>
                            @else
                            <span class="badge bg-warning text-dark">Fixed</span>
                            @endif
                        </td>
                        <td>
                            @if($tax->is_default)
                            <span class="badge bg-success"><i class="bi bi-check me-1"></i>Default</span>
                            @else
                            <form action="{{ route('admin.settings.vat-tax.setDefault', $tax->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">Set Default</button>
                            </form>
                            @endif
                        </td>
                        <td>
                            @if($tax->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editTaxModal{{ $tax->id }}">
                                            <i class="bi bi-pencil me-2"></i>Edit
                                        </button>
                                    </li>
                                    @if(!$tax->is_default)
                                    <li>
                                        <form action="{{ route('admin.settings.vat-tax.destroy', $tax->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this tax?')">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Edit Tax Modal -->
                    <div class="modal fade" id="editTaxModal{{ $tax->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Tax: {{ $tax->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.settings.vat-tax.update', $tax->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="name{{ $tax->id }}" class="form-label">Tax Name <span class="text-danger">*</span></label>
                                            <input type="text" id="name{{ $tax->id }}" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $tax->name }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="country{{ $tax->id }}" class="form-label">Country</label>
                                                    <input type="text" id="country{{ $tax->id }}" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ $tax->country }}" placeholder="e.g., Bangladesh">
                                                    @error('country')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="state{{ $tax->id }}" class="form-label">State/Region</label>
                                                    <input type="text" id="state{{ $tax->id }}" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ $tax->state }}" placeholder="e.g., Dhaka">
                                                    @error('state')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="zip_code{{ $tax->id }}" class="form-label">Zip/Postal Code</label>
                                            <input type="text" id="zip_code{{ $tax->id }}" name="zip_code" class="form-control @error('zip_code') is-invalid @enderror" value="{{ $tax->zip_code }}" placeholder="e.g., 1200">
                                            @error('zip_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="rate{{ $tax->id }}" class="form-label">Tax Rate <span class="text-danger">*</span></label>
                                                    <input type="number" id="rate{{ $tax->id }}" name="rate" class="form-control @error('rate') is-invalid @enderror" value="{{ $tax->rate }}" min="0" max="100" step="0.01" required>
                                                    @error('rate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="type{{ $tax->id }}" class="form-label">Type <span class="text-danger">*</span></label>
                                                    <select id="type{{ $tax->id }}" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                                        <option value="percentage" {{ $tax->type === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                                        <option value="fixed" {{ $tax->type === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                                    </select>
                                                    @error('type')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="sort_order{{ $tax->id }}" class="form-label">Sort Order</label>
                                            <input type="number" id="sort_order{{ $tax->id }}" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ $tax->sort_order }}" min="0">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_default{{ $tax->id }}" name="is_default" value="1" {{ $tax->is_default ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_default{{ $tax->id }}">Set as Default</label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_active{{ $tax->id }}" name="is_active" value="1" {{ $tax->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active{{ $tax->id }}">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update Tax</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No taxes found</p>
                            <button type="button" class="btn btn-sm btn-primary mt-1" data-bs-toggle="modal" data-bs-target="#addTaxModal">
                                <i class="bi bi-plus-lg me-1"></i> Add First Tax
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Tax Modal -->
<div class="modal fade" id="addTaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Tax</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.settings.vat-tax.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tax Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., VAT" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" id="country" name="country" class="form-control @error('country') is-invalid @enderror" placeholder="e.g., Bangladesh" value="{{ old('country') }}">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave empty for global tax</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="state" class="form-label">State/Region</label>
                                <input type="text" id="state" name="state" class="form-control @error('state') is-invalid @enderror" placeholder="e.g., Dhaka" value="{{ old('state') }}">
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="zip_code" class="form-label">Zip/Postal Code</label>
                        <input type="text" id="zip_code" name="zip_code" class="form-control @error('zip_code') is-invalid @enderror" placeholder="e.g., 1200" value="{{ old('zip_code') }}">
                        @error('zip_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Leave empty to apply to all zip codes in the state</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rate" class="form-label">Tax Rate <span class="text-danger">*</span></label>
                                <input type="number" id="rate" name="rate" class="form-control @error('rate') is-invalid @enderror" placeholder="e.g., 15" value="{{ old('rate') }}" min="0" max="100" step="0.01" required>
                                @error('rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}" min="0">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1">
                            <label class="form-check-label" for="is_default">Set as Default</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Tax</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
    
    /* Force Bootstrap Icons to display - SAME AS REFERENCE PAGE */
    .stat-card-icon i,
    .stat-card-icon i::before,
    .bi::before,
    [class*="bi bi-"]::before {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-family: 'bootstrap-icons' !important;
    }
    
    /* Override icon colors for stat cards */
    .stat-card-primary .stat-card-icon i::before { color: #0d6efd !important; }
    .stat-card-success .stat-card-icon i::before { color: #198754 !important; }
    .stat-card-info .stat-card-icon i::before { color: #0dcaf0 !important; }
    .stat-card-warning .stat-card-icon i::before { color: #ffc107 !important; }
    .stat-card-danger .stat-card-icon i::before { color: #dc3545 !important; }
    .stat-card-secondary .stat-card-icon i::before { color: #6c757d !important; }
    
    /* Make the whole icon colored */
    .stat-card-icon i { color: inherit !important; }
</style>
@endpush

@push('scripts')
<script>
    // Auto-dismiss alerts using vanilla JavaScript (no jQuery dependency)
    document.addEventListener('DOMContentLoaded', function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) {
                    bsAlert.close();
                } else {
                    alert.style.display = 'none';
                }
            }, 3000);
        });
    });
</script>
@endpush
