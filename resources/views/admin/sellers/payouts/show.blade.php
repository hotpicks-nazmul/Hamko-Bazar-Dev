@extends('admin.layouts.app')

@section('title', 'Payout Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Payout Details</h4>
    <a href="{{ route('admin.sellers.payouts') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Payouts
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Payout Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Payout Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Payout ID</label>
                        <div class="fw-medium">#{{ $payout->id }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Status</label>
                        <div>
                            @switch($payout->status)
                                @case('pending')
                                    <span class="badge bg-warning">Pending</span>
                                    @break
                                @case('approved')
                                    <span class="badge bg-info">Approved</span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-success">Completed</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $payout->status }}</span>
                            @endswitch
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Amount</label>
                        <div class="h5 text-primary">৳{{ number_format($payout->amount, 2) }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Commission ({{ $payout->seller->commission_rate ?? 10 }}%)</label>
                        <div class="text-muted">৳{{ number_format($payout->commission, 2) }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Net Amount</label>
                        <div class="h5 text-success">৳{{ number_format($payout->net_amount, 2) }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Payment Method</label>
                        <div>{{ $payout->getPaymentMethodName() }}</div>
                    </div>
                    @if($payout->transaction_id)
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Transaction ID</label>
                        <div><code>{{ $payout->transaction_id }}</code></div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bank Details -->
        @if($payout->bank_name || $payout->account_number)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bank me-2"></i>Bank Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Bank Name</label>
                        <div>{{ $payout->bank_name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Account Number</label>
                        <div>{{ $payout->account_number ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Account Name</label>
                        <div>{{ $payout->account_name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Notes -->
        @if($payout->notes || $payout->admin_notes)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Notes</h6>
            </div>
            <div class="card-body">
                @if($payout->notes)
                <div class="mb-3">
                    <label class="form-label text-muted small">Seller Notes</label>
                    <div>{{ $payout->notes }}</div>
                </div>
                @endif
                @if($payout->admin_notes)
                <div>
                    <label class="form-label text-muted small">Admin Notes</label>
                    <div>{{ $payout->admin_notes }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Seller Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-shop me-2"></i>Seller Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($payout->seller->shop_logo)
                        @php
                            $logoUrl = $payout->seller->shop_logo;
                            if($logoUrl && !str_starts_with($logoUrl, '/storage/') && !str_starts_with($logoUrl, 'http')) {
                                $logoUrl = '/storage/' . $logoUrl;
                            }
                        @endphp
                        <img src="{{ $logoUrl }}" alt="{{ $payout->seller->shop_name }}" class="rounded mb-2" style="width: 64px; height: 64px; object-fit: cover;">
                    @else
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center mb-2" style="width: 64px; height: 64px; margin: 0 auto;">
                            <i class="bi bi-shop text-white" style="font-size: 2rem;"></i>
                        </div>
                    @endif
                    <h6 class="mb-0">{{ $payout->seller->shop_name ?? $payout->seller->name }}</h6>
                    <small class="text-muted">{{ $payout->seller->email }}</small>
                </div>
                
                <hr>
                
                <div class="mb-2">
                    <label class="form-label text-muted small">Wallet Balance</label>
                    <div class="fw-medium">৳{{ number_format($payout->seller->wallet_balance ?? 0, 2) }}</div>
                </div>
                <div class="mb-2">
                    <label class="form-label text-muted small">Pending Balance</label>
                    <div class="fw-medium">৳{{ number_format($payout->seller->pending_balance ?? 0, 2) }}</div>
                </div>
                <div>
                    <label class="form-label text-muted small">Commission Rate</label>
                    <div>{{ $payout->seller->commission_rate ?? 10 }}%</div>
                </div>
                
                <hr>
                
                <a href="{{ route('admin.sellers.show', $payout->seller->id) }}" class="btn btn-sm btn-outline-primary w-100">
                    <i class="bi bi-eye me-1"></i> View Seller Profile
                </a>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Timeline</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                    <div class="me-2">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-plus text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="fw-medium small">Created</div>
                        <div class="text-muted small">{{ $payout->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
                
                @if($payout->approved_at)
                <div class="d-flex align-items-start mb-3">
                    <div class="me-2">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-check text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="fw-medium small">Approved</div>
                        <div class="text-muted small">{{ $payout->approved_at->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
                @endif
                
                @if($payout->processed_at)
                <div class="d-flex align-items-start mb-3">
                    <div class="me-2">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-check2-all text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="fw-medium small">Processed</div>
                        <div class="text-muted small">{{ $payout->processed_at->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
                @endif
                
                @if($payout->rejected_at)
                <div class="d-flex align-items-start">
                    <div class="me-2">
                        <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-x text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="fw-medium small">Rejected</div>
                        <div class="text-muted small">{{ $payout->rejected_at->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Processed By -->
        @if($payout->processedBy)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Processed By</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-person text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="fw-medium">{{ $payout->processedBy->name }}</div>
                        <small class="text-muted">{{ $payout->processedBy->email }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
