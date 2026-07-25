@extends('admin.layouts.app')

@section('title', 'Abandoned Cart Details')

@section('content')
<!-- Header with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Abandoned Cart Details</h4>
    <a href="{{ route('admin.marketing.abandoned-cart.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Customer Info Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Name</label>
                        <div class="fw-medium">{{ $record->customer_name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Email</label>
                        <div class="fw-medium">{{ $record->customer_email ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">User ID</label>
                        <div class="fw-medium">{{ $record->user_id ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Cart ID</label>
                        <div class="fw-medium">#{{ $record->cart_id }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Items Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-cart me-2"></i>Cart Items</h6>
            </div>
            <div class="card-body p-0">
                @if($record->cart && $record->cart->items && count($record->cart->items) > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($record->cart->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(isset($item['image']))
                                        @php
                                            $imageUrl = $item['image'];
                                            if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                                $imageUrl = '/storage/' . $imageUrl;
                                            }
                                        @endphp
                                        <img src="{{ $imageUrl }}" alt="{{ $item['name'] }}" 
                                             class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2" 
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-image text-white"></i>
                                        </div>
                                        @endif
                                        <div>
                                            <div class="fw-medium">{{ $item['name'] }}</div>
                                            @if(isset($item['variant_data']['color_name']))
                                            <small class="text-muted">Color: {{ $item['variant_data']['color_name'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ '৳' . number_format($item['price'], 2) }}</td>
                                <td>{{ $item['quantity'] }}</td>
                                <td class="text-end">
                                    <strong>{{ '৳' . number_format($item['price'] * $item['quantity'], 2) }}</strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th class="text-end">{{ '৳' . number_format($record->cart_total, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-cart-x text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0 mt-2">No items found in cart</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Activity Log Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Activity Log</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <!-- Created -->
                    <div class="timeline-item mb-3">
                        <div class="d-flex">
                            <div class="timeline-icon bg-primary rounded-circle p-1 me-3">
                                <i class="bi bi-cart text-white small"></i>
                            </div>
                            <div>
                                <div class="fw-medium">Cart Created</div>
                                <small class="text-muted">{{ $record->created_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Abandoned -->
                    @if($record->abandoned_at)
                    <div class="timeline-item mb-3">
                        <div class="d-flex">
                            <div class="timeline-icon bg-danger rounded-circle p-1 me-3">
                                <i class="bi bi-cart-x text-white small"></i>
                            </div>
                            <div>
                                <div class="fw-medium">Cart Marked as Abandoned</div>
                                <small class="text-muted">{{ $record->abandoned_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Recovery Emails -->
                    @for($i = 0; $i < $record->email_sent_count; $i++)
                    <div class="timeline-item mb-3">
                        <div class="d-flex">
                            <div class="timeline-icon bg-warning rounded-circle p-1 me-3">
                                <i class="bi bi-envelope text-white small"></i>
                            </div>
                            <div>
                                <div class="fw-medium">Recovery Email Sent (#{{ $i + 1 }})</div>
                                @if($i == 0 && $record->last_email_sent_at)
                                <small class="text-muted">{{ $record->last_email_sent_at->format('M d, Y h:i A') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endfor

                    <!-- Recovered -->
                    @if($record->status == 'recovered' && $record->recovered_at)
                    <div class="timeline-item mb-3">
                        <div class="d-flex">
                            <div class="timeline-icon bg-success rounded-circle p-1 me-3">
                                <i class="bi bi-check-circle text-white small"></i>
                            </div>
                            <div>
                                <div class="fw-medium">Cart Recovered</div>
                                <small class="text-muted">{{ $record->recovered_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Status Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Status</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <span class="badge bg-{{ $record->status_badge }} fs-6">
                        @switch($record->status)
                            @case('pending')
                                Pending
                                @break
                            @case('abandoned')
                                Abandoned
                                @break
                            @case('email_sent')
                                Email Sent
                                @break
                            @case('recovered')
                                Recovered
                                @break
                            @case('failed')
                                Failed
                                @break
                            @default
                                {{ ucfirst($record->status) }}
                        @endswitch
                    </span>
                </div>

                <div class="d-grid gap-2">
                    @if($record->status != 'recovered' && $record->customer_email)
                    <form action="{{ route('admin.marketing.abandoned-cart.send-reminder', $record->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100" 
                            {{ $record->email_sent_count >= 3 ? 'disabled' : '' }}>
                            <i class="bi bi-envelope me-1"></i> 
                            Send Reminder ({{ $record->email_sent_count }}/3)
                        </button>
                    </form>
                    @endif

                    @if($record->status != 'recovered')
                    <form action="{{ route('admin.marketing.abandoned-cart.mark-recovered', $record->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i> Mark as Recovered
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Summary</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Items:</span>
                    <span class="fw-medium">{{ $record->item_count }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Cart Total:</span>
                    <span class="fw-medium">{{ '৳' . number_format($record->cart_total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Emails Sent:</span>
                    <span class="fw-medium">{{ $record->email_sent_count }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Abandoned:</span>
                    <span class="fw-medium">
                        {{ $record->abandoned_at ? $record->abandoned_at->format('M d, Y') : 'N/A' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Recovery Link Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Recovery Link</h6>
            </div>
            <div class="card-body">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" 
                           value="{{ url('/cart/recover?email=' . urlencode($record->customer_email ?? '') . '&cart_id=' . $record->cart_id) }}" 
                           readonly id="recoveryLink">
                    <button class="btn btn-outline-secondary btn-sm" type="button" 
                            onclick="copyRecoveryLink()">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <small class="text-muted mt-1 d-block">Share this link with the customer</small>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.marketing.abandoned-cart.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Back
    </a>
    @if($record->status != 'recovered')
    <form action="{{ route('admin.marketing.abandoned-cart.mark-recovered', $record->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-success floating-save-btn" style="background: linear-gradient(135deg, #198754 0%, #157347 100%); border: none;">
            <i class="bi bi-check-lg me-1"></i> Mark Recovered
        </button>
    </form>
    @endif
    <form action="{{ route('admin.marketing.abandoned-cart.destroy', $record->id) }}" method="POST" class="d-inline"
          onsubmit="return confirm('Are you sure you want to delete this record?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger floating-reset-btn">
            <i class="bi bi-trash me-1"></i> Delete
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function copyRecoveryLink() {
        const copyText = document.getElementById("recoveryLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        alert("Recovery link copied to clipboard!");
    }
</script>
@endpush
