@extends('admin.layouts.app')

@section('title', 'Price Rules')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-calculator"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Rules</span>
            <span class="stat-card-value">{{ number_format($stats['total'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value">{{ number_format($stats['active'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Upcoming</span>
            <span class="stat-card-value">{{ number_format($stats['upcoming'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Expired</span>
            <span class="stat-card-value">{{ number_format($stats['expired'] ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Price Rules</h4>
    <a href="{{ route('admin.marketing.price-rules.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Price Rule
    </a>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search Input -->
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Search by name..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <!-- Discount Type Filter -->
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <label class="form-label small text-muted">Discount Type</label>
                    <select name="discount_type" id="filterDiscountType" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="percent" {{ request('discount_type') === 'percent' ? 'selected' : '' }}>Percentage</option>
                        <option value="fixed" {{ request('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-2 col-sm-4">
                    <a href="{{ route('admin.marketing.price-rules.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Price Rules Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                        </th>
                        <th>Name</th>
                        <th>Discount</th>
                        <th>Duration</th>
                        <th>Products</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($priceRules as $rule)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input" name="selected[]" value="{{ $rule->id }}">
                        </td>
                        <td>
                            <strong>{{ $rule->name }}</strong>
                            @if($rule->description)
                            <br><small class="text-muted">{{ Str::limit($rule->description, 50) }}</small>
                            @endif
                            @if($rule->is_featured)
                            <br><span class="badge bg-warning text-dark small"><i class="bi bi-star me-1"></i> Featured</span>
                            @endif
                        </td>
                        <td>
                            @if($rule->discount_type === 'percent')
                                <span class="text-primary fw-bold">{{ $rule->discount_value }}%</span>
                            @else
                                <span class="text-success fw-bold">${{ number_format($rule->discount_value, 2) }}</span>
                            @endif
                            @if($rule->max_discount_amount)
                            <br><small class="text-muted">Max: ${{ number_format($rule->max_discount_amount, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($rule->start_date || $rule->end_date)
                                <small class="text-muted d-block">
                                    @if($rule->start_date)
                                    Start: {{ $rule->start_date->format('d M, Y') }}
                                    @endif
                                </small>
                                <small class="{{ $rule->isExpired() ? 'text-danger' : 'text-muted' }}">
                                    @if($rule->end_date)
                                    End: {{ $rule->end_date->format('d M, Y') }}
                                    @else
                                    No end date
                                    @endif
                                </small>
                            @else
                            <span class="text-muted">No date range</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $rule->products->count() }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $rule->priority }}</span>
                        </td>
                        <td>
                            @if($rule->isExpired())
                                <span class="badge bg-danger">Expired</span>
                            @elseif($rule->isUpcoming())
                                <span class="badge bg-info">Upcoming</span>
                            @elseif($rule->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <form action="{{ route('admin.marketing.price-rules.toggle-status', $rule->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $rule->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $rule->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $rule->status === 'active' ? 'pause' : 'play' }}-circle"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.marketing.price-rules.edit', $rule->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('admin.marketing.price-rules.products', $rule->id) }}" class="btn btn-sm btn-outline-info" title="Manage Products">
                                    <i class="bi bi-box-seam"></i>
                                </a>
                                <form action="{{ route('admin.marketing.price-rules.destroy', $rule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this price rule?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-percent text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No price rules found</p>
                            <a href="{{ route('admin.marketing.price-rules.create') }}" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-plus-lg me-1"></i> Add First Price Rule
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($priceRules->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Showing {{ $priceRules->firstItem() }} - {{ $priceRules->lastItem() }} of {{ $priceRules->total() }} rules
        </div>
        <div>
            {{ $priceRules->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Filter form handling
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('liveSearch');
    const filterStatus = document.getElementById('filterStatus');
    const filterDiscountType = document.getElementById('filterDiscountType');
    
    let searchTimeout;
    
    // Debounced live search
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterForm.submit();
        }, 300);
    });
    
    // Filter dropdowns trigger form submit on change
    filterStatus.addEventListener('change', function() {
        filterForm.submit();
    });
    
    filterDiscountType.addEventListener('change', function() {
        filterForm.submit();
    });
    
    // Select all checkbox
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="selected[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    });
</script>
@endpush
