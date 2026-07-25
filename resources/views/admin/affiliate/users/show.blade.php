@extends('admin.layouts.app')

@section('title', 'Affiliate User Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Affiliate User Details</h4>
    <a href="{{ route('admin.affiliate.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Profile</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($affiliate->user && $affiliate->user->image)
                    <img src="{{ $affiliate->user->image }}" alt="{{ $affiliate->user->name }}" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                    @else
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="bi bi-person" style="font-size: 3rem;"></i>
                    </div>
                    @endif
                </div>
                <h5>{{ $affiliate->user->name ?? '-' }}</h5>
                <p class="text-muted mb-0">{{ $affiliate->user->email ?? '-' }}</p>
                @if($affiliate->status === 'approved')
                <span class="badge bg-success mt-2">Approved</span>
                @elseif($affiliate->status === 'pending')
                <span class="badge bg-warning mt-2">Pending</span>
                @elseif($affiliate->status === 'suspended')
                <span class="badge bg-danger mt-2">Suspended</span>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Statistics</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th>Total Clicks</th>
                        <td class="text-end">{{ number_format($affiliate->clicks_count ?? 0) }}</td>
                    </tr>
                    <tr>
                        <th>Total Conversions</th>
                        <td class="text-end">{{ number_format($affiliate->sales_count ?? 0) }}</td>
                    </tr>
                    @if(auth()->user()->hasPermission('view-revenue'))
                    <tr>
                        <th>Total Sales</th>
                        <td class="text-end">${{ number_format($affiliate->total_earnings ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total Earnings</th>
                        <td class="text-end">${{ number_format($affiliate->total_earnings ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Current Balance</th>
                        <td class="text-end fw-bold">${{ number_format($affiliate->balance ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Pending Balance</th>
                        <td class="text-end">${{ number_format($affiliate->pending_balance ?? 0, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Account Details</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-0">
                    <tr>
                        <th width="30%">Affiliate Code</th>
                        <td><code>{{ $affiliate->affiliate_code ?? '-' }}</code></td>
                    </tr>
                    <tr>
                        <th>Commission Rate</th>
                        <td>{{ $affiliate->commission_rate ?? '-' }}%</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $affiliate->user->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Payment Method</th>
                        <td>{{ $affiliate->payment_method ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Payment Details</th>
                        <td>{{ $affiliate->payment_details ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Website</th>
                        <td>{{ $affiliate->website ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Approved At</th>
                        <td>{{ $affiliate->approved_at ? $affiliate->approved_at->format('M d, Y H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Joined At</th>
                        <td>{{ $affiliate->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Sales</h6>
            </div>
            <div class="card-body">
                @if($recentSales && $recentSales->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                @if(auth()->user()->hasPermission('view-revenue'))
                                <th>Amount</th>
                                <th>Commission</th>
                                @endif
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSales as $sale)
                            <tr>
                                <td>#{{ $sale->order_id }}</td>
                                <td>{{ $sale->customer_name ?? '-' }}</td>
                                @if(auth()->user()->hasPermission('view-revenue'))
                                <td>${{ number_format($sale->sale_amount, 2) }}</td>
                                <td>${{ number_format($sale->commission_amount, 2) }}</td>
                                @endif
                                <td>{{ $sale->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-receipt text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0 mt-2">No recent sales</p>
                </div>
                @endif
            </div>
        </div>

        @if($affiliate->status === 'approved')
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.affiliate.users.suspend', $affiliate->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning" onclick="return confirm('Are you sure you want to suspend this affiliate?')">
                        <i class="bi bi-pause-circle me-1"></i> Suspend
                    </button>
                </form>
                <form action="{{ route('admin.affiliate.users.destroy', $affiliate->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this affiliate? This action cannot be undone.')">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
