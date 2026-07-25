@extends('admin.layouts.app')

@section('title', 'Abandoned Cart Recovery')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-cart-x"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Abandoned</span>
            <span class="stat-card-value">{{ number_format($stats['total'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-cart-dash"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Abandoned</span>
            <span class="stat-card-value">{{ number_format($stats['abandoned'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-cart-check"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Recovered</span>
            <span class="stat-card-value">{{ number_format($stats['recovered'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-percent"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Recovery Rate</span>
            <span class="stat-card-value">
                @if($stats['total'] > 0)
                    {{ round(($stats['recovered'] / $stats['total']) * 100, 1) }}%
                @else
                    0%
                @endif
            </span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-envelope"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Emails Sent</span>
            <span class="stat-card-value">{{ number_format($stats['email_sent'] ?? 0) }}</span>
        </div>
    </div>
    @if(auth()->user()->hasPermission('view-revenue'))
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Revenue Recovered</span>
            <span class="stat-card-value">{{ '৳' . number_format($stats['total_revenue_recovered'] ?? 0, 2) }}</span>
        </div>
    </div>
    @endif
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Abandoned Cart Recovery</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.marketing.abandoned-cart.settings') }}" class="btn btn-outline-primary">
            <i class="bi bi-gear me-1"></i> Settings
        </a>
        <a href="{{ route('admin.marketing.abandoned-cart.conversion-tracking') }}" class="btn btn-outline-info">
            <i class="bi bi-graph-up me-1"></i> Analytics
        </a>
    </div>
</div>

<!-- Filters Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Search -->
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <label class="form-label small text-muted">Search</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" id="liveSearch" class="form-control" 
                                    placeholder="Email or name..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label small text-muted">Status</label>
                            <select name="status" id="filterStatus" class="form-select form-select-sm">
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="abandoned" {{ request('status') == 'abandoned' ? 'selected' : '' }}>Abandoned</option>
                                <option value="email_sent" {{ request('status') == 'email_sent' ? 'selected' : '' }}>Email Sent</option>
                                <option value="recovered" {{ request('status') == 'recovered' ? 'selected' : '' }}>Recovered</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>

                        <!-- Date From -->
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label small text-muted">From Date</label>
                            <input type="date" name="date_from" class="form-control form-select-sm" value="{{ request('date_from') }}">
                        </div>

                        <!-- Date To -->
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label small text-muted">To Date</label>
                            <input type="date" name="date_to" class="form-control form-select-sm" value="{{ request('date_to') }}">
                        </div>

                        <!-- Reset Button -->
                        <div class="col-lg-1 col-md-2 col-sm-6">
                            <a href="{{ route('admin.marketing.abandoned-cart.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                                <i class="bi bi-x-lg me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Items</th>
                                @if(auth()->user()->hasPermission('view-revenue'))
                                <th>Total</th>
                                @endif
                                <th>Abandoned Date</th>
                                <th>Emails Sent</th>
                                <th>Status</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($records as $record)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $record->customer_name ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ $record->customer_email ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $record->item_count }} items</span>
                                </td>
                                @if(auth()->user()->hasPermission('view-revenue'))
                                <td>
                                    <strong>{{ '৳' . number_format($record->cart_total, 2) }}</strong>
                                </td>
                                @endif
                                <td>
                                    {{ $record->abandoned_at ? $record->abandoned_at->format('M d, Y h:i A') : 'N/A' }}
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $record->email_sent_count }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $record->status_badge }}">
                                        @switch($record->status)
                                            @case('pending')
                                                Pending
                                                @break
                                            @case('abandoned')
                                                Abandoned
                                                @break
                                            @case('email_sent')
                                                Email Sent
                                                @break
                                            @case('recovered')
                                                Recovered
                                                @break
                                            @case('failed')
                                                Failed
                                                @break
                                            @default
                                                {{ ucfirst($record->status) }}
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.marketing.abandoned-cart.show', $record->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($record->status != 'recovered' && $record->customer_email)
                                        <form action="{{ route('admin.marketing.abandoned-cart.send-reminder', $record->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                title="Send Reminder" {{ $record->email_sent_count >= 3 ? 'disabled' : '' }}>
                                                <i class="bi bi-envelope"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @if($record->status != 'recovered')
                                        <form action="{{ route('admin.marketing.abandoned-cart.mark-recovered', $record->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as Recovered">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('admin.marketing.abandoned-cart.destroy', $record->id) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this record?')">
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
                                <td colspan="{{ auth()->user()->hasPermission('view-revenue') ? 7 : 6 }}" class="text-center py-5">
                                    <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mb-2 mt-2">No abandoned cart records found</p>
                                    <a href="{{ route('admin.marketing.abandoned-cart.settings') }}" class="btn btn-sm btn-primary mt-1">
                                        <i class="bi bi-gear me-1"></i> Configure Settings
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($records->hasPages())
                <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">
                        Showing {{ $records->firstItem() }} - {{ $records->lastItem() }} of {{ $records->total() }} records
                    </div>
                    <div>
                        {{ $records->appends(request()->query())->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
@endsection

@push('scripts')
<script>
    // Auto-submit form on filter change
    const filterStatus = document.getElementById('filterStatus');
    const filterForm = document.getElementById('filterForm');
    const liveSearch = document.getElementById('liveSearch');

    filterStatus.addEventListener('change', function() {
        filterForm.submit();
    });

    // Debounced search
    let searchTimeout;
    liveSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            filterForm.submit();
        }, 500);
    });

    // Handle date filters
    document.querySelectorAll('input[type="date"]').forEach(function(input) {
        input.addEventListener('change', function() {
            filterForm.submit();
        });
    });
</script>
@endpush
