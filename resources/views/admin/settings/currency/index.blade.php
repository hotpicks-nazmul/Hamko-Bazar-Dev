@extends('admin.layouts.app')

@section('title', 'Currency')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-currency-exchange me-2"></i>Currency Management</h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
        <i class="bi bi-plus-lg me-1"></i> Add New Currency
    </button>
</div>

<!-- Frontend Currency Switcher Toggle -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1"><i class="bi bi-globe me-1"></i>Frontend Currency Switcher</h6>
                <p class="text-muted small mb-0">Show currency switcher dropdown on the frontend header</p>
            </div>
            <form action="{{ route('admin.settings.currency.toggleFrontend') }}" method="POST" id="frontendSwitchForm">
                @csrf
                <input type="hidden" name="status" value="{{ $frontendCurrencySwitcher ? 0 : 1 }}">
                <button type="submit" class="btn btn-sm {{ $frontendCurrencySwitcher ? 'btn-success' : 'btn-secondary' }}">
                    {{ $frontendCurrencySwitcher ? 'ON' : 'OFF' }}
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Total Currencies</span>
                <span class="stat-card-value">{{ $currencies->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Active</span>
                <span class="stat-card-value">{{ $currencies->where('is_active', true)->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card stat-card-secondary">
            <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Inactive</span>
                <span class="stat-card-value">{{ $currencies->where('is_active', false)->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon"><i class="bi bi-star"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Default</span>
                <span class="stat-card-value">{{ $defaultCurrency->symbol ?? 'N/A' }} ({{ $defaultCurrency->code ?? 'N/A' }})</span>
            </div>
        </div>
    </div>
</div>

<!-- Currencies Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Currency</th>
                        <th>Code</th>
                        <th>Symbol</th>
                        <th>Exchange Rate</th>
                        <th style="width: 100px;">Default</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($currencies as $currency)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <span class="fw-medium">{{ $currency->name }}</span>
                        </td>
                        <td><code>{{ $currency->code }}</code></td>
                        <td><span class="fs-5">{{ $currency->symbol }}</span></td>
                        <td>{{ number_format($currency->exchange_rate, 6) }}</td>
                        <td>
                            @if($currency->is_default)
                            <span class="badge bg-success"><i class="bi bi-check me-1"></i>Default</span>
                            @else
                            <form action="{{ route('admin.settings.currency.setDefault', $currency->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">Set Default</button>
                            </form>
                            @endif
                        </td>
                        <td>
                            @if($currency->is_active)
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
                                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editCurrencyModal{{ $currency->id }}">
                                            <i class="bi bi-pencil me-2"></i>Edit
                                        </button>
                                    </li>
                                    @if(!$currency->is_default)
                                    <li>
                                        <form action="{{ route('admin.settings.currency.destroy', $currency->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this currency?')">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Edit Currency Modal -->
                    <div class="modal fade" id="editCurrencyModal{{ $currency->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Currency</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.settings.currency.update', $currency->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="name{{ $currency->id }}" class="form-label">Currency Name <span class="text-danger">*</span></label>
                                            <input type="text" id="name{{ $currency->id }}" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $currency->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="code{{ $currency->id }}" class="form-label">Code <span class="text-danger">*</span></label>
                                            <input type="text" id="code{{ $currency->id }}" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $currency->code) }}" required>
                                            <div class="form-text">ISO 4217 code (e.g., USD, EUR, GBP)</div>
                                            @error('code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="symbol{{ $currency->id }}" class="form-label">Symbol <span class="text-danger">*</span></label>
                                            <input type="text" id="symbol{{ $currency->id }}" name="symbol" class="form-control @error('symbol') is-invalid @enderror" value="{{ old('symbol', $currency->symbol) }}" required>
                                            <div class="form-text">Currency symbol (e.g., $, €, £, ¥)</div>
                                            @error('symbol')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="exchange_rate{{ $currency->id }}" class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                                            <input type="number" id="exchange_rate{{ $currency->id }}" name="exchange_rate" class="form-control @error('exchange_rate') is-invalid @enderror" value="{{ old('exchange_rate', $currency->exchange_rate) }}" min="0.000001" step="0.000001" required>
                                            <div class="form-text">Exchange rate relative to default currency (1 = default)</div>
                                            @error('exchange_rate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="sort_order{{ $currency->id }}" class="form-label">Sort Order</label>
                                            <input type="number" id="sort_order{{ $currency->id }}" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $currency->sort_order) }}" min="0">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_default{{ $currency->id }}" name="is_default" value="1" {{ old('is_default', $currency->is_default) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_default{{ $currency->id }}">
                                                    <i class="bi bi-star me-1"></i>Set as Default
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_active{{ $currency->id }}" name="is_active" value="1" {{ old('is_active', $currency->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active{{ $currency->id }}">
                                                    <i class="bi bi-check-circle text-success me-1"></i>Active
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="bi bi-x-lg me-1"></i>Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-1"></i>Update Currency
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-currency-exchange text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No currencies found</p>
                            <button type="button" class="btn btn-sm btn-primary mt-1" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
                                <i class="bi bi-plus-lg me-1"></i> Add First Currency
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Currency Modal -->
<div class="modal fade" id="addCurrencyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Currency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.settings.currency.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Currency Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g., US Dollar" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g., USD" required>
                        <div class="form-text">ISO 4217 code (e.g., USD, EUR, GBP, BDT)</div>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="symbol" class="form-label">Symbol <span class="text-danger">*</span></label>
                        <input type="text" id="symbol" name="symbol" class="form-control @error('symbol') is-invalid @enderror" value="{{ old('symbol') }}" placeholder="e.g., $" required>
                        <div class="form-text">Currency symbol (e.g., $, €, £, ¥, ৳)</div>
                        @error('symbol')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="exchange_rate" class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                        <input type="number" id="exchange_rate" name="exchange_rate" class="form-control @error('exchange_rate') is-invalid @enderror" value="{{ old('exchange_rate', '1.000000') }}" min="0.000001" step="0.000001" required>
                        <div class="form-text">Exchange rate relative to default currency. Set to 1 for default currency.</div>
                        @error('exchange_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">
                                <i class="bi bi-star me-1"></i>Set as Default
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="bi bi-check-circle text-success me-1"></i>Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Add Currency
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-dismiss alerts using vanilla JavaScript (no jQuery dependency)
    document.addEventListener('DOMContentLoaded', function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 3000);
        });

        // Reopen add modal if there are validation errors for add currency
        @if($errors->any() && session('open_add_modal'))
            var addModal = new bootstrap.Modal(document.getElementById('addCurrencyModal'));
            addModal.show();
        @endif

        // Reopen specific edit modal if there are validation errors
        @if($errors->any() && session('edit_currency_id'))
            var editId = {{ session('edit_currency_id') }};
            var editModal = new bootstrap.Modal(document.getElementById('editCurrencyModal' + editId));
            editModal.show();
        @endif
    });
</script>
@endpush
@endsection
