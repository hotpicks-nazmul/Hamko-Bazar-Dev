@extends('admin.layouts.app')

@section('title', 'Withdrawal Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Withdrawal Details</h4>
    <a href="{{ route('admin.affiliate.withdrawals.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Withdrawal Information Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Withdrawal Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Withdrawal ID</label>
                            <p class="mb-0 fw-bold">#{{ $withdrawal->id }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Amount</label>
                            <p class="mb-0 fw-bold text-success">${{ number_format($withdrawal->amount, 2) }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Payment Method</label>
                            <p class="mb-0">{{ ucfirst($withdrawal->payment_method ?? '-') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Status</label>
                            <p class="mb-0">
                                @if($withdrawal->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                                @elseif($withdrawal->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                                @elseif($withdrawal->status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                                @else
                                <span class="badge bg-info">Paid</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Requested At</label>
                            <p class="mb-0">{{ $withdrawal->requested_at ? $withdrawal->requested_at->format('M d, Y H:i') : '-' }}</p>
                        </div>
                    </div>
                    @if($withdrawal->processed_at)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Requested At</label>
                            <p class="mb-0">{{ $withdrawal->requested_at ? $withdrawal->requested_at->format('M d, Y H:i') : '-' }}</p>
                        </div>
                    </div>
                    </div>
                    @endif
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <label class="form-label text-muted small">Payment Details</label>
                    <div class="bg-light p-3 rounded">
                        <pre class="mb-0" style="white-space: pre-wrap;">{{ $withdrawal->payment_details ?? 'No payment details provided' }}</pre>
                    </div>
                </div>
                
                @if($withdrawal->admin_note)
                <div class="mb-3">
                    <label class="form-label text-muted small">Admin Note</label>
                    <div class="bg-light p-3 rounded">
                        <pre class="mb-0" style="white-space: pre-wrap;">{{ $withdrawal->admin_note }}</pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Affiliate Information Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Affiliate Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Name</label>
                    <p class="mb-0">{{ $withdrawal->affiliate->user->name ?? '-' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Email</label>
                    <p class="mb-0">{{ $withdrawal->affiliate->user->email ?? '-' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Affiliate Code</label>
                    <p class="mb-0"><code>{{ $withdrawal->affiliate->affiliate_code ?? '-' }}</code></p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Current Balance</label>
                    <p class="mb-0 fw-bold text-success">${{ number_format($withdrawal->affiliate->balance ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
        
        <!-- Actions Card (only for pending) -->
        @if($withdrawal->status === 'pending')
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    <form action="{{ route('admin.affiliate.withdrawals.approve', $withdrawal->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to approve this withdrawal?')">
                            <i class="bi bi-check-circle me-2"></i>Approve Withdrawal
                        </button>
                    </form>
                    <form action="{{ route('admin.affiliate.withdrawals.reject', $withdrawal->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Are you sure you want to reject this withdrawal?')">
                            <i class="bi bi-x-circle me-2"></i>Reject Withdrawal
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
