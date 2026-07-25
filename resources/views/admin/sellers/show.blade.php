@extends('admin.layouts.app')

@section('title', 'Seller Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Seller Details</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.sellers.edit', $seller->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Seller
        </a>
        <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Sellers
        </a>
    </div>
</div>

<!-- Seller Info Cards -->
<div class="row mb-4">
    <!-- Seller Basic Info -->
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                @if($seller->shop_logo && file_exists(public_path('uploads/shop_logos/' . $seller->shop_logo)))
                    <img src="{{ asset('uploads/shop_logos/' . $seller->shop_logo) }}" alt="{{ $seller->shop_name }}" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="bi bi-person text-muted" style="font-size: 3rem;"></i>
                    </div>
                @endif
                <h5 class="mb-1">{{ $seller->shop_name ?? $seller->name }}</h5>
                <p class="text-muted mb-2">{{ $seller->email }}</p>
                <p class="mb-2">{{ $seller->phone ?? 'No phone' }}</p>
                <span class="badge bg-{{ $seller->status === 'active' ? 'success' : 'secondary' }}">
                    {{ ucfirst($seller->status) }}
                </span>
                <span class="badge bg-{{ $seller->verification_status === 'verified' ? 'success' : ($seller->verification_status === 'pending' ? 'warning' : 'danger') }}">
                    {{ ucfirst($seller->verification_status) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total Products</span>
                    <span class="fw-bold">{{ $seller->products_count ?? 0 }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total Orders</span>
                    <span class="fw-bold">{{ $totalOrders }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Pending Orders</span>
                    <span class="fw-bold text-warning">{{ $pendingOrders }}</span>
                </div>
                @if(auth()->user()->hasPermission('view-revenue'))
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total Sales</span>
                    <span class="fw-bold text-success">৳{{ number_format($totalSales, 2) }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Wallet & Commission -->
    <div class="col-lg-4 col-md-12 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Wallet & Commission</h6>
            </div>
            <div class="card-body">
                @if(auth()->user()->hasPermission('view-revenue'))
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Wallet Balance</span>
                    <span class="fw-bold text-success">৳{{ number_format($seller->wallet_balance ?? 0, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Pending Balance</span>
                    <span class="fw-bold text-warning">৳{{ number_format($seller->pending_balance ?? 0, 2) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Commission Rate</span>
                    <span class="fw-bold">{{ $seller->commission_rate ?? 10 }}%</span>
                </div>
                @if($seller->verified_at)
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Verified On</span>
                    <span class="small">{{ $seller->verified_at->format('d M Y') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Detailed Information -->
<div class="row">
    <div class="col-lg-8">
        <!-- Shop Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-shop me-2"></i>Shop Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Shop Name</label>
                        <p class="mb-0 fw-medium">{{ $seller->shop_name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Seller Type</label>
                        <p class="mb-0 fw-medium">{{ ucfirst($seller->seller_type ?? 'individual') }}</p>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Shop Description</label>
                    <p class="mb-0">{{ $seller->shop_description ?? '-' }}</p>
                </div>
                @if($seller->shop_banner && file_exists(public_path('uploads/shop_banners/' . $seller->shop_banner)))
                <div class="mb-3">
                    <label class="text-muted small">Shop Banner</label>
                    <div>
                        <img src="{{ asset('uploads/shop_banners/' . $seller->shop_banner) }}" alt="Shop Banner" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Business Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-briefcase me-2"></i>Business Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Company Name</label>
                        <p class="mb-0 fw-medium">{{ $seller->company_name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Business Registration Number</label>
                        <p class="mb-0 fw-medium">{{ $seller->business_registration_number ?? '-' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Tax ID / TIN</label>
                        <p class="mb-0 fw-medium">{{ $seller->tax_id ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Member Since</label>
                        <p class="mb-0 fw-medium">{{ $seller->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Person Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Contact Person Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Name</label>
                        <p class="mb-0 fw-medium">{{ $seller->contact_person_name ?? '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Phone</label>
                        <p class="mb-0 fw-medium">{{ $seller->contact_person_phone ?? '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Email</label>
                        <p class="mb-0 fw-medium">{{ $seller->contact_person_email ?? '-' }}</p>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Return Address</label>
                    <p class="mb-0">{{ $seller->return_address ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Bank Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bank me-2"></i>Bank Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Bank Name</label>
                        <p class="mb-0 fw-medium">{{ $seller->bank_name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Account Number</label>
                        <p class="mb-0 fw-medium">{{ $seller->bank_account_number ? '****' . substr($seller->bank_account_number, -4) : '-' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Account Name</label>
                        <p class="mb-0 fw-medium">{{ $seller->bank_account_name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Routing Code</label>
                        <p class="mb-0 fw-medium">{{ $seller->bank_routing_code ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-cart me-2"></i>Recent Orders</h6>
            </div>
            <div class="card-body p-0">
                @if($orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                @if(auth()->user()->hasPermission('view-sales'))
                                <th>Amount</th>
                                @endif
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}">{{ $order->order_number }}</a>
                                </td>
                                <td>{{ $order->user->name ?? 'N/A' }}</td>
                                @if(auth()->user()->hasPermission('view-sales'))
                                <td>৳{{ number_format($order->grand_total, 2) }}</td>
                                @endif
                                <td>
                                    <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at->format('d M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-cart text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">No orders found</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                @if($seller->verification_status === 'pending')
                    <form action="{{ route('admin.sellers.verification.process', $seller->id) }}" method="POST" class="mb-2">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i> Approve Verification
                        </button>
                    </form>
                @endif

                @if($seller->status === 'active')
                    <form action="{{ route('admin.sellers.suspend', $seller->id) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Are you sure you want to suspend this seller?')">
                            <i class="bi bi-pause-circle me-1"></i> Suspend Seller
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.sellers.activate', $seller->id) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-play-circle me-1"></i> Activate Seller
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.sellers.edit', $seller->id) }}" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-pencil me-1"></i> Edit Seller
                </a>
            </div>
        </div>

        <!-- Verification History -->
        @if($seller->verification_notes)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Verification Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $seller->verification_notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
