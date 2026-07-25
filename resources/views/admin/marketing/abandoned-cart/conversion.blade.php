@extends('admin.layouts.app')

@section('title', 'Conversion Analytics')

@section('content')
<!-- Header with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Abandoned Cart Analytics</h4>
    <a href="{{ route('admin.marketing.abandoned-cart.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-cart-x"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Abandoned Carts</span>
            <span class="stat-card-value">{{ $totalAbandoned }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Recovered Carts</span>
            <span class="stat-card-value">{{ $recovered }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-percent"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Recovery Rate</span>
            <span class="stat-card-value">{{ $recoveryRate }}%</span>
        </div>
    </div>
    @if(auth()->user()->hasPermission('view-revenue'))
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Revenue Recovered</span>
            <span class="stat-card-value">{{ '৳' . number_format($recoveredRevenue, 2) }}</span>
        </div>
    </div>
    @endif
</div>

<div class="row">
    <!-- Monthly Breakdown -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Monthly Breakdown</h6>
            </div>
            <div class="card-body">
                @if($monthlyData->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th class="text-center">Abandoned</th>
                                <th class="text-center">Recovered</th>
                                <th class="text-center">Recovery Rate</th>
                                @if(auth()->user()->hasPermission('view-revenue'))
                                <th class="text-end">Revenue Recovered</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyData as $data)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($data->month)->format('F Y') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ $data->total }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $data->recovered }}</span>
                                </td>
                                <td class="text-center">
                                    @if($data->total > 0)
                                        <span class="badge bg-info">{{ round(($data->recovered / $data->total) * 100, 1) }}%</span>
                                    @else
                                        <span class="badge bg-secondary">0%</span>
                                    @endif
                                </td>
                                @if(auth()->user()->hasPermission('view-revenue'))
                                <td class="text-end">
                                    <strong>{{ '৳' . number_format($data->revenue, 2) }}</strong>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-graph-up text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0 mt-2">No data available yet</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Performance Tips -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips to Improve Recovery</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">Send the first recovery email within <strong>1 hour</strong> of cart abandonment</li>
                    <li class="mb-2">Include a <strong>small discount</strong> (5-10%) to incentivize the purchase</li>
                    <li class="mb-2">Use <strong>personalized subject lines</strong> with the customer's name</li>
                    <li class="mb-2">Show <strong>product images</strong> in the recovery email</li>
                    <li class="mb-2">Add <strong>urgency</strong> by mentioning limited stock or expiration of discount</li>
                    <li class="mb-0">Make the <strong>checkout process easy</strong> with a direct link</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Summary Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Quick Summary</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total Emails Sent:</span>
                    <span class="fw-medium">{{ $emailsSent }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Avg. Emails per Cart:</span>
                    <span class="fw-medium">
                        @if($totalAbandoned > 0)
                            {{ round($emailsSent / $totalAbandoned, 1) }}
                        @else
                            0
                        @endif
                    </span>
                </div>
                @if(auth()->user()->hasPermission('view-revenue'))
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Avg. Order Value:</span>
                    <span class="fw-medium">
                        @if($recovered > 0)
                            {{ '৳' . number_format($recoveredRevenue / $recovered, 2) }}
                        @else
                            {{ '৳' . number_format(0, 2) }}
                        @endif
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Email Performance -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-envelope me-2"></i>Email Performance</h6>
            </div>
            <div class="card-body">
                @if($totalAbandoned > 0 && $emailsSent > 0)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Open Rate (Est.)</span>
                        <span class="small fw-medium">20-30%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: 25%;"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Click Rate (Est.)</span>
                        <span class="small fw-medium">5-10%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: 7.5%;"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Conversion Rate (Est.)</span>
                        <span class="small fw-medium">2-5%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 3.5%;"></div>
                    </div>
                </div>
                @else
                <div class="text-center py-3">
                    <i class="bi bi-envelope text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0 mt-2 small">No email data yet</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.marketing.abandoned-cart.settings') }}" class="btn btn-outline-primary">
                        <i class="bi bi-gear me-1"></i> Configure Settings
                    </a>
                    <a href="{{ route('admin.marketing.abandoned-cart.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-list me-1"></i> View All Carts
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
