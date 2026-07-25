@extends('admin.layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Customer Details</h4>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.customers.login-as', $customer->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary" onclick="return confirm('Login as this customer?')">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login as Customer
            </button>
        </form>
        @if(str_contains(url()->previous(), 'customers/loyalty'))
            <a href="{{ route('admin.customers.loyalty.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Loyalty Points
            </a>
        @else
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Customers
            </a>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                    <span class="text-primary fw-bold" style="font-size: 1.5rem;">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                </div>
                <h5 class="mb-1">{{ $customer->name }}</h5>
                <p class="text-muted small mb-0">{{ $customer->email }}</p>
                <span class="badge bg-{{ $customer->status === 'active' ? 'success' : 'secondary' }} mt-2">{{ $customer->status === 'active' ? 'Active' : 'Inactive' }}</span>
            </div>
        </div>
    </div>
    @if(auth()->user()->hasPermission('customers.view-financial'))
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Total Orders</p>
                        <h4 class="mb-0">{{ $customer->orders->count() }}</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded p-3">
                        <i class="bi bi-bag text-info" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Total Spent</p>
                        <h4 class="mb-0">৳{{ number_format($customer->orders->sum('total'), 2) }}</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded p-3">
                        <i class="bi bi-currency-exchange text-success" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Member Since</p>
                        <h6 class="mb-0">{{ $customer->created_at->format('M d, Y') }}</h6>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded p-3">
                        <i class="bi bi-calendar text-warning" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Personal Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.customers.update', $customer->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $customer->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $customer->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $customer->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="status" name="status" {{ $customer->status === 'active' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">
                                    <i class="bi bi-check-circle text-success me-1"></i> Active
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bag me-2"></i>Recent Orders</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Status</th>
                                @if(auth()->user()->hasPermission('customers.view-financial'))
                                <th>Amount</th>
                                @endif
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->orders->take(10) as $order)
                            <tr>
                                <td class="fw-medium">#{{ $order->order_number ?? $order->id }}</td>
                                <td>
                                    @php
                                        $statusClass = match($order->status) {
                                            'delivered' => 'success',
                                            'shipped' => 'info',
                                            'processing' => 'primary',
                                            'pending' => 'warning',
                                            'cancelled', 'refunded' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                                </td>
                                @if(auth()->user()->hasPermission('customers.view-financial'))
                                <td>৳{{ number_format($order->total, 2) }}</td>
                                @endif
                                <td>{{ $order->created_at->format('d M, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ auth()->user()->hasPermission('customers.view-financial') ? 5 : 4 }}" class="text-center py-4 text-muted">No orders found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Contact Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p class="text-muted small mb-1">Email</p>
                    <p class="mb-0"><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></p>
                </div>
                <div class="mb-3">
                    <p class="text-muted small mb-1">Phone</p>
                    <p class="mb-0">{{ $customer->phone ?? 'Not provided' }}</p>
                </div>
                <div>
                    <p class="text-muted small mb-1">Address</p>
                    <p class="mb-0">
                        @if($customer->addresses && $customer->addresses->count() > 0)
                            {{ $customer->addresses->first()->address }}<br>
                            {{ $customer->addresses->first()->city }} - {{ $customer->addresses->first()->postal_code }}
                        @else
                            No address on file
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if(auth()->user()->hasPermission('customers.view-financial'))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Purchase Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Orders</span>
                    <span class="fw-medium">{{ $customer->orders->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Spent</span>
                    <span class="fw-medium">৳{{ number_format($customer->orders->sum('total'), 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Average Order</span>
                    <span class="fw-medium">৳{{ number_format($customer->orders->count() > 0 ? $customer->orders->sum('total') / $customer->orders->count() : 0, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Wallet Balance</span>
                    <span class="fw-medium">৳{{ number_format($customer->wallet_balance ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Are you sure you want to delete this customer? This action cannot be undone.')">
                        <i class="bi bi-trash me-1"></i> Delete Customer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
