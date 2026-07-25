@extends('admin.layouts.app')

@section('content')
<!-- Page Title with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Wallet Transactions</h4>
    <a href="{{ route('admin.customers.wallet.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Wallet
    </a>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-arrow-down-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Credit</span>
            <span class="stat-card-value">৳{{ number_format($stats['total_credit'], 2) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-arrow-up-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Debit</span>
            <span class="stat-card-value">৳{{ number_format($stats['total_debit'], 2) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-list-ul"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Transactions</span>
            <span class="stat-card-value">{{ number_format($stats['total_transactions']) }}</span>
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
                    <label class="form-label small text-muted">Search Customer</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Name, Email, Phone..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Type Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Type</label>
                    <select name="type" id="filterType" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit (+)</option>
                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit (-)</option>
                    </select>
                </div>

                <!-- Source Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Source</label>
                    <select name="source" id="filterSource" class="form-select form-select-sm">
                        <option value="">All Sources</option>
                        <option value="admin" {{ request('source') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="order" {{ request('source') === 'order' ? 'selected' : '' }}>Order</option>
                        <option value="refund" {{ request('source') === 'refund' ? 'selected' : '' }}>Refund</option>
                        <option value="payment" {{ request('source') === 'payment' ? 'selected' : '' }}>Payment</option>
                        <option value="other" {{ request('source') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- From Date -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" 
                           value="{{ request('from_date') }}">
                </div>

                <!-- To Date -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" 
                           value="{{ request('to_date') }}">
                </div>

                <!-- Reset Button -->
                <div class="col-lg-1 col-md-12 col-sm-12">
                    <a href="{{ route('admin.customers.wallet.transactions') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Customer</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Balance After</th>
                        <th>Source</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                    <tr>
                        <td>
                            <a href="{{ route('admin.customers.wallet.show', $transaction->user_id) }}" class="text-decoration-none">
                                <div class="fw-medium">{{ $transaction->user->name ?? 'Unknown' }}</div>
                                <div class="small text-muted">{{ $transaction->user->email ?? '' }}</div>
                            </a>
                        </td>
                        <td>
                            @if($transaction->type === 'credit')
                            <span class="badge bg-success">
                                <i class="bi bi-plus-circle me-1"></i> Credit
                            </span>
                            @else
                            <span class="badge bg-danger">
                                <i class="bi bi-dash-circle me-1"></i> Debit
                            </span>
                            @endif
                        </td>
                        <td class="text-end {{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }} fw-medium">
                            {{ $transaction->type === 'credit' ? '+' : '-' }}৳{{ number_format($transaction->amount, 2) }}
                        </td>
                        <td class="text-end">৳{{ number_format($transaction->balance_after, 2) }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($transaction->source) }}</span>
                        </td>
                        <td>{{ Str::limit($transaction->description, 40) ?? '-' }}</td>
                        <td>
                            <div>{{ $transaction->created_at->format('d M Y') }}</div>
                            <div class="small text-muted">{{ $transaction->created_at->format('h:i A') }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No transactions found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }} of {{ $transactions->total() }} transactions
            </div>
            <div>
                {{ $transactions->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

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
    const filterSelects = ['filterType', 'filterSource'];
    filterSelects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });

    // Date inputs trigger search on change
    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
</script>
@endpush
