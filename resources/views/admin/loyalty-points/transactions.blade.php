@extends('admin.layouts.app')

@section('title', 'Points History — ' . $customer->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Loyalty Points History</h4>
        <p class="text-muted small mb-0">
            {{ $customer->name }} · balance {{ number_format($customer->loyalty_points) }} pts
            · spent {{ number_format($customer->loyalty_points_spent) }} pts
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.customers.loyalty.show', $customer->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Loyalty
        </a>
        <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-person me-1"></i> Customer
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <span class="text-muted small me-1">Filter:</span>
        @foreach (['' => 'All', 'earned' => 'Earned', 'bonus' => 'Bonus', 'adjusted' => 'Adjusted', 'spent' => 'Spent'] as $value => $label)
            <a href="{{ route('admin.customers.loyalty.transactions', ['customerId' => $customer->id, 'type' => $value ?: null]) }}"
               class="btn btn-sm {{ request('type') === ($value ?: null) || (request('type') === null && $value === '') ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

<div class="card border-0 shadow-sm">
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
                        <td class="text-muted">{{ $transactions->firstItem() + $index }}</td>
                        <td>
                            @if($transaction->points > 0)
                                <span class="text-success fw-medium">+{{ number_format($transaction->points) }}</span>
                            @else
                                <span class="text-danger fw-medium">{{ number_format($transaction->points) }}</span>
                            @endif
                        </td>
                        <td>{{ number_format($transaction->points_balance) }}</td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ ucfirst($transaction->type) }}</span>
                        </td>
                        <td class="text-muted small">{{ $transaction->description ?? '—' }}</td>
                        <td class="text-muted small">{{ $transaction->created_at?->format('d M Y, h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-0 mt-2">No points transactions for this customer.</p>
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
            <div>{{ $transactions->links() }}</div>
        </div>
        @endif
    </div>
</div>
@endsection
