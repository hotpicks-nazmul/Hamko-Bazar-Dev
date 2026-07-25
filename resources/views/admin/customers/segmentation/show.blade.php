@extends('admin.layouts.app')

@section('title', $segment->name . ' - Customer Segmentation')

@section('content')
<!-- Header with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $segment->name }}</h4>
    <div>
        <a href="{{ route('admin.customers.segmentation.export', $segment->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Export
        </a>
        <a href="{{ route('admin.customers.segmentation.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Segments
        </a>
    </div>
</div>

<!-- Segment Info -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Total Customers</p>
                        <h4 class="mb-0">{{ number_format($segment->customer_count) }}</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded p-3">
                        <i class="bi bi-people text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Status</p>
                        @if($segment->is_active)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                    <div class="bg-success bg-opacity-10 rounded p-3">
                        <i class="bi bi-check-circle text-success" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Created</p>
                        <h6 class="mb-0">{{ $segment->created_at->format('M d, Y') }}</h6>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded p-3">
                        <i class="bi bi-calendar text-info" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Created By</p>
                        <h6 class="mb-0">{{ $segment->creator->name ?? 'System' }}</h6>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded p-3">
                        <i class="bi bi-person text-warning" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Description -->
@if($segment->description)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="mb-2">Description</h6>
        <p class="text-muted mb-0">{{ $segment->description }}</p>
    </div>
</div>
@endif

<!-- Conditions -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Segment Conditions</h6>
    </div>
    <div class="card-body">
        @if($segment->conditions && count($segment->conditions) > 0)
        <div class="d-flex flex-wrap gap-2">
            @if(isset($segment->conditions['order_count_min']))
            <span class="badge bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-bag me-1"></i> Min Orders: {{ $segment->conditions['order_count_min'] }}
            </span>
            @endif
            @if(isset($segment->conditions['order_count_max']))
            <span class="badge bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-bag me-1"></i> Max Orders: {{ $segment->conditions['order_count_max'] }}
            </span>
            @endif
            @if(isset($segment->conditions['total_spent_min']))
            <span class="badge bg-success bg-opacity-10 text-success">
                <i class="bi bi-currency-exchange me-1"></i> Min Spent: {{ number_format($segment->conditions['total_spent_min'], 2) }}
            </span>
            @endif
            @if(isset($segment->conditions['total_spent_max']))
            <span class="badge bg-success bg-opacity-10 text-success">
                <i class="bi bi-currency-exchange me-1"></i> Max Spent: {{ number_format($segment->conditions['total_spent_max'], 2) }}
            </span>
            @endif
            @if(isset($segment->conditions['last_order_days']))
            <span class="badge bg-info bg-opacity-10 text-info">
                <i class="bi bi-clock me-1"></i> Last {{ $segment->conditions['last_order_days'] }} days
            </span>
            @endif
            @if(isset($segment->conditions['customer_group_id']))
            <span class="badge bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-people me-1"></i> Customer Group ID: {{ $segment->conditions['customer_group_id'] }}
            </span>
            @endif
            @if(isset($segment->conditions['registration_date_from']))
            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                <i class="bi bi-calendar me-1"></i> From: {{ $segment->conditions['registration_date_from'] }}
            </span>
            @endif
            @if(isset($segment->conditions['registration_date_to']))
            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                <i class="bi bi-calendar me-1"></i> To: {{ $segment->conditions['registration_date_to'] }}
            </span>
            @endif
            @if(isset($segment->conditions['is_active']))
            <span class="badge bg-{{ $segment->conditions['is_active'] ? 'success' : 'danger' }} bg-opacity-10 text-{{ $segment->conditions['is_active'] ? 'success' : 'danger' }}">
                <i class="bi bi-{{ $segment->conditions['is_active'] ? 'check' : 'x' }}-circle me-1"></i>
                {{ $segment->conditions['is_active'] ? 'Active' : 'Inactive' }} Only
            </span>
            @endif
        </div>
        @else
        <p class="text-muted mb-0">No conditions defined</p>
        @endif
    </div>
</div>

<!-- Customers List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-people me-2"></i>Customers in this Segment</h6>
            <a href="{{ route('admin.customers.segmentation.edit', $segment->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Edit Segment
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        @if(auth()->user()->hasPermission('view-revenue'))
                        <th>Total Spent</th>
                        @endif
                        <th>Joined</th>
                        <th style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                    <i class="bi bi-person text-primary"></i>
                                </div>
                                <div>
                                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="text-decoration-none fw-semibold">
                                        {{ $customer->name }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted">{{ $customer->email }}</span>
                        </td>
                        <td>
                            <span class="text-muted">{{ $customer->phone ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                {{ $customer->orders_count }}
                            </span>
                        </td>
                        @if(auth()->user()->hasPermission('view-revenue'))
                        <td>
                            <span class="fw-semibold">{{ number_format($customer->orders->sum('grand_total'), 2) }}</span>
                        </td>
                        @endif
                        <td>
                            <span class="text-muted small">
                                {{ $customer->created_at->format('M d, Y') }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->hasPermission('view-revenue') ? 7 : 6 }}" class="text-center py-5">
                            <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No customers in this segment</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $customers->firstItem() }} - {{ $customers->lastItem() }} of {{ $customers->total() }} customers
            </div>
            <div>
                {{ $customers->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
