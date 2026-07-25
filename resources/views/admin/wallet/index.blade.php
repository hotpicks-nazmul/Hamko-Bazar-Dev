@extends('admin.layouts.app')

@section('content')
<!-- Page Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Customer Wallet</h4>
    <a href="{{ route('admin.customers.wallet.transactions') }}" class="btn btn-outline-primary">
        <i class="bi bi-clock-history me-1"></i> All Transactions
    </a>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row stat-card-row-5 mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-people"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Customers</span>
            <span class="stat-card-value">{{ number_format($stats['total_customers']) }}</span>
        </div>
    </div>
    @if(auth()->user()->hasPermission('view-revenue'))
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-wallet2"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Balance</span>
            <span class="stat-card-value">৳{{ number_format($stats['total_wallet_balance'], 2) }}</span>
        </div>
    </div>
    @endif
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-star"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Points</span>
            <span class="stat-card-value">{{ number_format($stats['total_wallet_points'], 2) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">With Balance</span>
            <span class="stat-card-value">{{ number_format($stats['customers_with_balance']) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-person-check"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">With Points</span>
            <span class="stat-card-value">{{ number_format($stats['customers_with_points']) }}</span>
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
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Name, Email, Phone..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Min Balance Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Min Balance</label>
                    <input type="number" name="min_balance" class="form-control form-control-sm" 
                           placeholder="0.00" value="{{ request('min_balance') }}" step="0.01">
                </div>

                <!-- Max Balance Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Max Balance</label>
                    <input type="number" name="max_balance" class="form-control form-control-sm" 
                           placeholder="0.00" value="{{ request('max_balance') }}" step="0.01">
                </div>

                <!-- Has Balance Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Has Balance</label>
                    <select name="has_balance" id="filterHasBalance" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="yes" {{ request('has_balance') === 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ request('has_balance') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('admin.customers.wallet.index') }}" class="btn btn-sm btn-outline-secondary">
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
                        <th>Phone</th>
                        @if(auth()->user()->hasPermission('view-revenue'))
                        <th class="text-end">Wallet Balance</th>
                        @endif
                        <th class="text-end">Points</th>
                        <th>Joined</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($customers as $customer)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $customer->name }}</div>
                                    <div class="small text-muted">{{ $customer->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $customer->phone ?? '-' }}</td>
                        @if(auth()->user()->hasPermission('view-revenue'))
                        <td class="text-end">
                            <span class="text-success fw-medium">
                                ৳{{ number_format($customer->wallet_balance, 2) }}
                            </span>
                        </td>
                        @endif
                        <td class="text-end">
                            <span class="text-warning fw-medium">
                                {{ number_format($customer->wallet_points ?? 0, 2) }} PTS
                            </span>
                        </td>
                        <td>{{ $customer->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.customers.wallet.show', $customer->id) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-wallet text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No customers found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($customers->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $customers->firstItem() }} - {{ $customers->lastItem() }} of {{ $customers->total() }} customers
            </div>
            <div>
                {{ $customers->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Recent Transactions -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Source</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $transaction)
                    <tr>
                        <td>
                            <a href="{{ route('admin.customers.wallet.show', $transaction->user_id) }}" class="text-decoration-none">
                                <div class="fw-medium">{{ $transaction->user->name ?? 'Unknown' }}</div>
                                <div class="small text-muted">{{ $transaction->user->email ?? '' }}</div>
                            </a>
                        </td>
                        <td>
                            @if($transaction->type === 'credit')
                            <span class="badge bg-success">Credit</span>
                            @else
                            <span class="badge bg-danger">Debit</span>
                            @endif
                        </td>
                        <td class="{{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                            {{ $transaction->type === 'credit' ? '+' : '-' }}৳{{ number_format($transaction->amount, 2) }}
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($transaction->source) }}</span>
                        </td>
                        <td>{{ Str::limit($transaction->description, 30) ?? '-' }}</td>
                        <td>{{ $transaction->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            No recent transactions
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Debounced live search
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 500);
        });
    }

    // Filter dropdowns trigger search on change
    const filterSelects = ['filterHasBalance'];
    filterSelects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });

    // Handle Enter key on filter inputs
    document.querySelectorAll('#filterForm input').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('filterForm').submit();
            }
        });
    });
</script>
@endpush
