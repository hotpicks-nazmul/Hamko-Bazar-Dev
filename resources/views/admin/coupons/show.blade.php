@extends('admin.layouts.app')

@section('title', 'Coupon ' . $coupon->code)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Coupon <span class="text-primary">{{ $coupon->code }}</span></h4>
        <small class="text-muted">Created {{ $coupon->created_at?->format('d M Y, H:i') ?? '—' }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Details</h6></div>
            <div class="card-body">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                        <tr><th style="width: 45%;">Code</th><td class="fw-semibold">{{ $coupon->code }}</td></tr>
                        <tr>
                            <th>Type</th>
                            <td>
                                @if($coupon->type === 'percentage')
                                    <span class="badge bg-info">Percentage</span>
                                @else
                                    <span class="badge bg-secondary">Fixed</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Value</th>
                            <td>{{ $coupon->type === 'percentage' ? number_format((float) $coupon->value, 2) . ' %' : format_price($coupon->value) }}</td>
                        </tr>
                        <tr><th>Minimum order</th><td>{{ $coupon->min_order_amount ? format_price($coupon->min_order_amount) : 'None' }}</td></tr>
                        <tr><th>Maximum discount</th><td>{{ $coupon->max_discount ? format_price($coupon->max_discount) : 'No cap' }}</td></tr>
                        <tr>
                            <th>Usage</th>
                            <td>
                                {{ (int) $coupon->used_count }}
                                @if($coupon->usage_limit)
                                    of {{ (int) $coupon->usage_limit }}
                                    @if($coupon->used_count >= $coupon->usage_limit)
                                        <span class="badge bg-danger ms-1">Limit reached</span>
                                    @endif
                                @else
                                    <span class="text-muted">(unlimited)</span>
                                @endif
                            </td>
                        </tr>
                        <tr><th>Starts</th><td>{{ $coupon->start_date?->format('d M Y, H:i') ?? 'Immediately' }}</td></tr>
                        <tr>
                            <th>Ends</th>
                            <td>
                                {{ $coupon->end_date?->format('d M Y, H:i') ?? 'Never' }}
                                @if($coupon->end_date && $coupon->end_date->isPast())
                                    <span class="badge bg-warning text-dark ms-1">Expired</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-{{ $coupon->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($coupon->status) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">What a customer gets</h6></div>
            <div class="card-body">
                @php($sample = 1000)
                @if($coupon->isValid($sample))
                    <p class="text-muted small mb-2">On a {{ format_price($sample) }} order:</p>
                    <h3 class="mb-1">{{ format_price($coupon->calculateDiscount($sample)) }} off</h3>
                    <p class="mb-0 small text-muted">Customer pays {{ format_price($sample - $coupon->calculateDiscount($sample)) }}</p>
                @else
                    <div class="alert alert-warning mb-0">
                        This coupon cannot be used right now
                        @if($coupon->status !== 'active') (inactive)@endif
                        @if($coupon->end_date && $coupon->end_date->isPast()) (past its end date)@endif
                        @if($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) (usage limit reached)@endif
                        @if($coupon->start_date && now()->lt($coupon->start_date)) (starts {{ $coupon->start_date->format('d M Y') }})@endif
                        .
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body d-flex gap-2">
                @php($showSpent = $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)
                @php($showEnded = $coupon->end_date && $coupon->end_date->isPast())
                @if($showEnded || $showSpent)
                    <span class="flex-fill" title="{{ $showEnded
                        ? 'Expired on ' . $coupon->end_date->format('d M, Y') . ' — extend the end date to use it again'
                        : 'Usage limit reached (' . (int) $coupon->used_count . ' of ' . (int) $coupon->usage_limit . ') — raise the limit' }}">
                        <button class="btn w-100 btn-outline-secondary" disabled>
                            {{ $showEnded ? 'Expired' : 'Limit reached' }}
                        </button>
                    </span>
                @else
                    <form method="POST" action="{{ route('admin.coupons.toggle', $coupon->id) }}" class="flex-fill">
                        @csrf
                        @method('PATCH')
                        <button class="btn w-100 btn-{{ $coupon->status === 'active' ? 'outline-warning' : 'outline-success' }}">
                            {{ $coupon->status === 'active' ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon->id) }}"
                      onsubmit="return confirm('Delete this coupon?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
