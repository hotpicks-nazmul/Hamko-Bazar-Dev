@extends('admin.layouts.app')

@section('title', 'Customer Loyalty Points')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Loyalty Points - {{ $customer->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.customers.loyalty.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Loyalty Points
        </a>
    </div>
</div>

<!-- Customer Info Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="avatar-circle bg-primary text-white me-3" style="width: 60px; height: 60px; font-size: 24px;">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <div>
                        <h5 class="mb-1">{{ $customer->name }}</h5>
                        <p class="text-muted mb-0">{{ $customer->email }}</p>
                        @if($customer->phone)
                            <p class="text-muted mb-0">{{ $customer->phone }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-flex gap-2 justify-content-end">
                    <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addPointsModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Points
                    </button>
                    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#deductPointsModal">
                        <i class="bi bi-dash-circle me-1"></i> Deduct Points
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-star"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Current Points Balance</span>
            <span class="stat-card-value">{{ number_format($customer->loyalty_points) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-arrow-down-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Points Spent</span>
            <span class="stat-card-value">{{ number_format($customer->loyalty_points_spent) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Spent</span>
            <span class="stat-card-value">${{ number_format($customer->total_spent ?? 0, 2) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-bag"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Orders</span>
            <span class="stat-card-value">{{ number_format($customer->orders_count ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Available Rewards -->
@if($rewards->count() > 0)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-gift me-2"></i>Available Rewards</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($rewards as $reward)
            <div class="col-md-4">
                <div class="card border {{ $customer->loyalty_points >= $reward->points_required ? 'border-success' : 'border-muted' }} h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">{{ $reward->name }}</h6>
                            <span class="badge {{ $customer->loyalty_points >= $reward->points_required ? 'bg-success' : 'bg-secondary' }}">
                                {{ $reward->points_required }} pts
                            </span>
                        </div>
                        <p class="text-muted small mb-0">{{ $reward->description ?? 'No description' }}</p>
                    </div>
                    @if($customer->loyalty_points >= $reward->points_required)
                    <div class="card-footer bg-transparent">
                        <button class="btn btn-sm btn-success w-100" disabled>
                            <i class="bi bi-check-circle me-1"></i> Can Redeem
                        </button>
                    </div>
                    @else
                    <div class="card-footer bg-transparent">
                        <button class="btn btn-sm btn-secondary w-100" disabled>
                            <i class="bi bi-x-circle me-1"></i> Not Enough Points
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Transaction History -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Transaction History</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Points</th>
                        <th>Balance After</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $index => $transaction)
                    <tr>
                        <td>{{ $transactions->firstItem() + $index }}</td>
                        <td>
                            @if($transaction->points > 0)
                                <span class="text-success fw-medium">+{{ number_format($transaction->points) }}</span>
                            @else
                                <span class="text-danger fw-medium">{{ number_format($transaction->points) }}</span>
                            @endif
                        </td>
                        <td>{{ number_format($transaction->points_balance) }}</td>
                        <td>
                            @switch($transaction->type)
                                @case('earned')
                                    <span class="badge bg-success">Earned</span>
                                    @break
                                @case('spent')
                                    <span class="badge bg-danger">Spent</span>
                                    @break
                                @case('bonus')
                                    <span class="badge bg-warning text-dark">Bonus</span>
                                    @break
                                @case('expired')
                                    <span class="badge bg-secondary">Expired</span>
                                    @break
                                @case('adjusted')
                                    <span class="badge bg-info">Adjusted</span>
                                    @break
                                @default
                                    <span class="badge bg-light text-dark">{{ ucfirst($transaction->type) }}</span>
                            @endswitch
                        </td>
                        <td class="text-muted">{{ $transaction->description ?? '-' }}</td>
                        <td class="text-muted small">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0 mt-2">No transactions found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
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

<!-- Add Points Modal -->
<div class="modal fade" id="addPointsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Points to {{ $customer->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.customers.loyalty.addPoints') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $customer->id }}">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Current Balance: <strong>{{ number_format($customer->loyalty_points) }} points</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Points <span class="text-danger">*</span></label>
                        <input type="number" name="points" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="bonus">Bonus</option>
                            <option value="earned">Earned</option>
                            <option value="adjusted">Adjusted</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Reason for adding points">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Points</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Deduct Points Modal -->
<div class="modal fade" id="deductPointsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Deduct Points from {{ $customer->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.customers.loyalty.deductPoints') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $customer->id }}">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Current Balance: <strong>{{ number_format($customer->loyalty_points) }} points</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Points to Deduct <span class="text-danger">*</span></label>
                        <input type="number" name="points" class="form-control" min="1" max="{{ $customer->loyalty_points }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <input type="text" name="description" class="form-control" placeholder="Reason for deduction">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Deduct Points</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-circle {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}
</style>
@endpush
