@extends('admin.layouts.app')

@section('content')
<!-- Page Title with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Customer Wallet Details</h4>
    <a href="{{ route('admin.customers.wallet.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Wallet
    </a>
</div>

<!-- Customer Info Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="avatar-circle avatar-circle-lg bg-primary text-white me-3">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <div>
                        <h5 class="mb-1">{{ $customer->name }}</h5>
                        <p class="text-muted mb-0">
                            <i class="bi bi-envelope me-1"></i>{{ $customer->email }}
                            @if($customer->phone)
                            <span class="mx-2">|</span>
                            <i class="bi bi-phone me-1"></i>{{ $customer->phone }}
                            @endif
                        </p>
                        <div class="mt-2">
                            <span class="badge bg-primary">{{ ucfirst($customer->role) }}</span>
                            <span class="badge bg-{{ $customer->status === 'active' ? 'success' : 'danger' }}">
                                {{ ucfirst($customer->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                @if(auth()->user()->hasPermission('view-revenue'))
                <div class="mb-2">
                    <div class="text-muted small">Wallet Balance</div>
                    <div class="h3 mb-0 text-success">৳{{ number_format($customer->wallet_balance, 2) }}</div>
                </div>
                @endif
                <div>
                    <div class="text-muted small">Points</div>
                    <div class="h4 mb-0 text-warning">{{ number_format($customer->wallet_points ?? 0, 2) }} PTS</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-6">
        <!-- Add Balance Modal -->
        <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addBalanceModal">
            <i class="bi bi-plus-lg me-1"></i> Add Balance
        </button>
    </div>
    <div class="col-md-6">
        <!-- Deduct Balance Modal -->
        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#deductBalanceModal">
            <i class="bi bi-dash-lg me-1"></i> Deduct Balance
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Type Filter -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Transaction Type</label>
                    <select name="type" id="filterType" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit (+)</option>
                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit (-)</option>
                    </select>
                </div>

                <!-- Source Filter -->
                <div class="col-lg-3 col-md-4 col-sm-6">
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

                <!-- Reset Button -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('admin.customers.wallet.show', $customer->id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Transaction History</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                        <th>Source</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                    <tr>
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
                        <td class="{{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }} fw-medium">
                            {{ $transaction->type === 'credit' ? '+' : '-' }}৳{{ number_format($transaction->amount, 2) }}
                        </td>
                        <td>৳{{ number_format($transaction->balance_after, 2) }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($transaction->source) }}</span>
                        </td>
                        <td>{{ $transaction->description ?? '-' }}</td>
                        <td>{{ $transaction->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
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

<!-- Add Balance Modal -->
<div class="modal fade" id="addBalanceModal" tabindex="-1" aria-labelledby="addBalanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBalanceModalLabel">Add Wallet Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.customers.wallet.add-balance') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <input type="text" class="form-control" value="{{ $customer->name }} ({{ $customer->email }})" readonly>
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Balance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Deduct Balance Modal -->
<div class="modal fade" id="deductBalanceModal" tabindex="-1" aria-labelledby="deductBalanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deductBalanceModalLabel">Deduct Wallet Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.customers.wallet.deduct-balance') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Current Balance: <strong>৳{{ number_format($customer->wallet_balance, 2) }}</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <input type="text" class="form-control" value="{{ $customer->name }} ({{ $customer->email }})" readonly>
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="{{ $customer->wallet_balance }}" required placeholder="0.00">
                        <div class="form-text">Maximum: ৳{{ number_format($customer->wallet_balance, 2) }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Reason for deduction..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Deduct Balance</button>
                </div>
            </form>
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
    .avatar-circle-lg {
        width: 60px;
        height: 60px;
        font-size: 20px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Filter dropdowns trigger search on change
    const filterSelects = ['filterType', 'filterSource'];
    filterSelects.forEach(function(id) {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });

    // Deduct modal validation
    const deductForm = document.querySelector('#deductBalanceModal form');
    if (deductForm) {
        deductForm.addEventListener('submit', function(e) {
            const amountInput = this.querySelector('input[name="amount"]');
            const maxBalance = parseFloat('{{ $customer->wallet_balance }}');
            if (parseFloat(amountInput.value) > maxBalance) {
                e.preventDefault();
                alert('Amount cannot exceed current balance of ৳' + maxBalance.toFixed(2));
            }
        });
    }
</script>
@endpush
