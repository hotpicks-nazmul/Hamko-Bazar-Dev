@extends('admin.layouts.app')

@section('title', 'Customers Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Customers Report</h4>
        <p class="text-muted mb-0 small">Top 20 customers by paid spend. Orders counts every order they placed.</p>
    </div>
    <a href="{{ route('admin.reports.export', 'customers') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-download me-1"></i> Export CSV
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">New Customers (30 days)</h6>
                <h3 class="mb-0">{{ number_format($newCustomers) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Paid Spend (top 20)</h6>
                <h3 class="mb-0">৳{{ number_format((float) $topCustomers->sum('total_spent'), 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Orders by Those Customers</h6>
                <h3 class="mb-0">{{ number_format((int) $topCustomers->sum('orders_count')) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0">Top Customers</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th class="text-end">Orders</th>
                    <th class="text-end">Paid Spend</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topCustomers as $i => $customer)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="fw-medium">{{ $customer->name }}</td>
                    <td class="text-muted small">{{ $customer->email ?: '—' }}</td>
                    <td class="text-muted small">{{ $customer->phone ?: '—' }}</td>
                    <td class="text-end">{{ number_format((int) $customer->orders_count) }}</td>
                    <td class="text-end fw-semibold">৳{{ number_format((float) $customer->total_spent, 2) }}</td>
                    <td class="text-muted small">{{ $customer->created_at?->format('d M Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No customers yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
