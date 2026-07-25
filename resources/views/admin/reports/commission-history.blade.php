@extends('admin.layouts.app')

@section('title', 'Commission History')

@section('content')
<div class="mb-4">
    <h4 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Commission History</h4>
    <p class="text-muted mb-0">Track commission earnings from seller payouts</p>
</div>

<!-- Summary Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon">
            <i class="bi bi-receipt"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Payouts</span>
            <span class="stat-card-value">{{ number_format($totalPayouts) }}</span>
        </div>
    </div>
    @if(auth()->user()->hasPermission('view-revenue'))
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon">
            <i class="bi bi-currency-dollar"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Amount</span>
            <span class="stat-card-value">৳{{ number_format($totalAmount, 2) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon">
            <i class="bi bi-percent"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Commission</span>
            <span class="stat-card-value">৳{{ number_format($totalCommission, 2) }}</span>
        </div>
    </div>
    @endif
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon">
            <i class="bi bi-graph-up"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Avg. Commission Rate</span>
            <span class="stat-card-value">{{ number_format($avgCommissionRate, 2) }}%</span>
        </div>
    </div>
</div>

<!-- Status Summary Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon">
            <i class="bi bi-clock"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Pending</span>
            <span class="stat-card-value">{{ number_format($pendingCount) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Completed</span>
            <span class="stat-card-value">{{ number_format($completedCount) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon">
            <i class="bi bi-x-circle"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Rejected</span>
            <span class="stat-card-value">{{ number_format($rejectedCount) }}</span>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search Input -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search Seller</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Search by name, shop, email..." value="{{ $search }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                
                <!-- Date Range -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Date Range</label>
                    <input type="text" name="date_range" id="dateRange" class="form-control form-control-sm" 
                           placeholder="Select date range" value="{{ $dateRange }}">
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                
                <!-- Seller Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Seller</label>
                    <select name="seller" class="form-select form-select-sm">
                        <option value="">All Sellers</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ $sellerId == $seller->id ? 'selected' : '' }}>
                                {{ $seller->name }}{{ $seller->shop_name ? ' - ' . $seller->shop_name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Sort By -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Sort By</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="date_desc" {{ $sortBy === 'date_desc' ? 'selected' : '' }}>Date (Newest)</option>
                        <option value="date_asc" {{ $sortBy === 'date_asc' ? 'selected' : '' }}>Date (Oldest)</option>
                        <option value="amount_desc" {{ $sortBy === 'amount_desc' ? 'selected' : '' }}>Amount (High to Low)</option>
                        <option value="amount_asc" {{ $sortBy === 'amount_asc' ? 'selected' : '' }}>Amount (Low to High)</option>
                        <option value="commission_desc" {{ $sortBy === 'commission_desc' ? 'selected' : '' }}>Commission (High to Low)</option>
                        <option value="commission_asc" {{ $sortBy === 'commission_asc' ? 'selected' : '' }}>Commission (Low to High)</option>
                    </select>
                </div>
                
                <!-- Filter Buttons -->
                <div class="col-lg-12 col-md-12 col-sm-12 mt-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.commission-history') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                        <a href="{{ route('admin.reports.commission-history.export', request()->query()) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download me-1"></i> Export
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0"><i class="bi bi-table me-2"></i>Commission Details</h6>
        <span class="text-muted small">Showing {{ $payouts->firstItem() ?? 0 }} - {{ $payouts->lastItem() ?? 0 }} of {{ $payouts->total() }} records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Seller</th>
                        @if(auth()->user()->hasPermission('view-revenue'))
                        <th class="text-end" style="width: 120px;">Amount</th>
                        @endif
                        <th class="text-center" style="width: 100px;">Rate</th>
                        @if(auth()->user()->hasPermission('view-revenue'))
                        <th class="text-end" style="width: 120px;">Commission</th>
                        <th class="text-end" style="width: 120px;">Net Amount</th>
                        @endif
                        <th class="text-center" style="width: 100px;">Status</th>
                        <th class="text-center" style="width: 120px;">Payment Method</th>
                        <th style="width: 150px;">Date</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($payouts as $index => $payout)
                    <tr>
                        <td>{{ $payouts->firstItem() + $index }}</td>
                        <td>
                            @if($payout->seller)
                            <div class="fw-medium">{{ $payout->seller->name }}</div>
                            @if($payout->seller->shop_name)
                            <small class="text-muted">{{ $payout->seller->shop_name }}</small>
                            @endif
                            @else
                            <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        @if(auth()->user()->hasPermission('view-revenue'))
                        <td class="text-end">
                            <span class="fw-medium">৳{{ number_format($payout->amount, 2) }}</span>
                        </td>
                        @endif
                        <td class="text-center">
                            @php
                                $commissionRate = $payout->amount > 0 ? ($payout->commission / $payout->amount) * 100 : 0;
                            @endphp
                            <span class="badge bg-info">{{ number_format($commissionRate, 1) }}%</span>
                        </td>
                        @if(auth()->user()->hasPermission('view-revenue'))
                        <td class="text-end">
                            <span class="text-success fw-medium">৳{{ number_format($payout->commission, 2) }}</span>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold">৳{{ number_format($payout->net_amount, 2) }}</span>
                        </td>
                        @endif
                        <td class="text-center">
                            <span class="badge bg-{{ $payout->getStatusBadgeClass() }}">
                                {{ ucfirst($payout->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="text-muted small">{{ $payout->getPaymentMethodName() }}</span>
                        </td>
                        <td>
                            <small class="text-muted">{{ $payout->created_at->format('d M Y, h:i A') }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No commission history found</p>
                            <p class="text-muted small">Try adjusting your filter criteria</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($payouts->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $payouts->firstItem() ?? 0 }} - {{ $payouts->lastItem() ?? 0 }} of {{ $payouts->total() }} records
            </div>
            <div>
                {{ $payouts->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize flatpickr date range picker
    const dateRangeInput = document.getElementById('dateRange');
    if (dateRangeInput) {
        flatpickr(dateRangeInput, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            allowInput: true,
            placeholder: 'Select date range'
        });
    }

    // Debounced live search
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();

            if (searchTerm.length >= 3 || searchTerm.length === 0) {
                searchSpinner.style.display = 'block';

                searchTimeout = setTimeout(() => {
                    // Auto-submit form after 500ms delay
                    document.getElementById('filterForm').submit();
                }, 500);
            }
        });
    }

    // Auto-submit on dropdown changes
    const statusSelect = document.querySelector('select[name="status"]');
    const sellerSelect = document.querySelector('select[name="seller"]');
    const sortSelect = document.querySelector('select[name="sort"]');

    [statusSelect, sellerSelect, sortSelect].forEach(function(select) {
        if (select) {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });
});
</script>
@endpush
