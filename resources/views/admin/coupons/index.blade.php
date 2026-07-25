@extends('admin.layouts.app')

@section('title', 'Coupons')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-ticket-perforated"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Coupons</span>
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
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-pause-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value">{{ number_format($stats['inactive'] ?? 0) }}</span>
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
    <h4 class="mb-0">Coupons</h4>
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Coupon
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
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Search by code..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <!-- Type Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Type</label>
                    <select name="type" id="filterType" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="percentage" {{ request('type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                        <option value="fixed" {{ request('type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-2 col-sm-6">
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Coupons Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Min Order</th>
                        <th>Usage</th>
                        <th>Valid Period</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                    <tr>
                        <td>
                            <strong class="text-primary">{{ $coupon->code }}</strong>
                        </td>
                        <td>
                            <span class="badge {{ $coupon->type === 'percentage' ? 'bg-info' : 'bg-success' }}">
                                {{ ucfirst($coupon->type) }}
                            </span>
                        </td>
                        <td>
                            @if($coupon->type === 'percentage')
                                <strong>{{ $coupon->value }}%</strong>
                                @if($coupon->max_discount)
                                    <br><small class="text-muted">Max: ৳{{ number_format($coupon->max_discount, 2) }}</small>
                                @endif
                            @else
                                <strong>৳{{ number_format($coupon->value, 2) }}</strong>
                            @endif
                        </td>
                        <td>
                            @if($coupon->min_order_amount)
                                ৳{{ number_format($coupon->min_order_amount, 2) }}
                            @else
                                <span class="text-muted">No minimum</span>
                            @endif
                        </td>
                        <td>
                            <span class="{{ ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) ? 'text-danger' : '' }}">
                                {{ $coupon->used_count ?? 0 }}
                            </span>
                            @if($coupon->usage_limit)
                                / {{ $coupon->usage_limit }}
                            @else
                                <span class="text-muted">/ ∞</span>
                            @endif
                        </td>
                        <td>
                            @if($coupon->start_date || $coupon->end_date)
                                @if($coupon->start_date)
                                    <small class="text-muted">From: {{ $coupon->start_date->format('d M, Y') }}</small><br>
                                @endif
                                @if($coupon->end_date)
                                    <small class="{{ $coupon->end_date->isPast() ? 'text-danger' : 'text-muted' }}">
                                        Until: {{ $coupon->end_date->format('d M, Y') }}
                                    </small>
                                @endif
                            @else
                                <span class="text-muted">No limit</span>
                            @endif
                        </td>
                        <td>
                            @if($coupon->status === 'active')
                                @if($coupon->end_date && $coupon->end_date->isPast())
                                    <span class="badge bg-secondary">Expired</span>
                                @elseif($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)
                                    <span class="badge bg-warning">Limit Reached</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.coupons.toggle', $coupon->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $coupon->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $coupon->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi bi-{{ $coupon->status === 'active' ? 'pause' : 'play' }}-circle"></i>
                                </button>
                            </form>
                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this coupon?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-ticket-perforated text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No coupons found.</p>
                            <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm mt-1">
                                <i class="bi bi-plus-lg me-1"></i> Add your first coupon
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($coupons->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Showing {{ $coupons->firstItem() }} - {{ $coupons->lastItem() }} of {{ $coupons->total() }} coupons
        </div>
        <div>
            {{ $coupons->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Live search with debounce
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const filterForm = document.getElementById('filterForm');

    // Search input handler
    searchInput?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        
        searchTimeout = setTimeout(() => {
            filterForm.submit();
        }, 300);
    });

    // Filter dropdown handlers
    document.getElementById('filterType')?.addEventListener('change', function() {
        filterForm.submit();
    });

    document.getElementById('filterStatus')?.addEventListener('change', function() {
        filterForm.submit();
    });
</script>
@endpush
