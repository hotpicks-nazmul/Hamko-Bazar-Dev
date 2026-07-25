@extends('admin.layouts.app')

@section('title', 'Invoice - ' . $order->order_number)

@section('content')
<div class="container" id="invoice-content">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="mb-0">Invoice #{{ $order->order_number }}</h4>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h3 class="text-primary mb-1">{{ config('app.name', 'Store') }}</h3>
                    <p class="text-muted mb-0">Online Store</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h4 class="mb-1">INVOICE</h4>
                    <p class="mb-0"><strong>#{{ $order->order_number }}</strong></p>
                    <p class="text-muted mb-0">{{ $order->created_at->format('d M, Y') }}</p>
                </div>
            </div>

            <hr>

            <!-- Addresses -->
            @if(auth()->user()->hasPermission('orders.view-customer'))
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase mb-2">Bill To</h6>
                    <p class="mb-1"><strong>{{ $order->billing_full_name }}</strong></p>
                    <p class="mb-0">{{ $order->billing_email }}</p>
                    <p class="mb-0">{{ $order->billing_phone }}</p>
                    <p class="mb-0">{{ $order->billing_address }}</p>
                    <p class="mb-0">{{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_postcode }}</p>
                    <p class="mb-0">{{ $order->billing_country }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-muted text-uppercase mb-2">Ship To</h6>
                    <p class="mb-1"><strong>{{ $order->shipping_full_name }}</strong></p>
                    <p class="mb-0">{{ $order->shipping_address ?? $order->billing_address }}</p>
                    <p class="mb-0">{{ $order->shipping_city ?? $order->billing_city }}, {{ $order->shipping_state ?? $order->billing_state }} {{ $order->shipping_postcode ?? $order->billing_postcode }}</p>
                    <p class="mb-0">{{ $order->shipping_country ?? $order->billing_country }}</p>
                </div>
            </div>
            @endif

            <!-- Status -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase mb-2">Payment Status</h6>
                    <span class="badge {{ $order->payment_status_badge_class }} fs-6">{{ ucfirst($order->payment_status) }}</span>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-muted text-uppercase mb-2">Order Status</h6>
                    <span class="badge {{ $order->status_badge_class }} fs-6">{{ ucfirst($order->status) }}</span>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                @php
                                    $imgUrl = $item->product && $item->product->featured_image ? $item->product->featured_image : null;
                                    if($imgUrl && !str_starts_with($imgUrl, '/storage/') && !str_starts_with($imgUrl, 'http')) {
                                        $imgUrl = '/storage/' . $imgUrl;
                                    }
                                @endphp
                                <div class="d-flex align-items-center">
                                    @if($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="{{ $item->product_name }}" class="me-2" style="width:50px;height:50px;object-fit:cover;border-radius:4px;" onerror="this.style.display='none'">
                                    @endif
                                    <div>
                                        <strong>{{ $item->product_name }}</strong>
                                        @if($item->variation)
                                        <br><small class="text-muted">{{ is_array($item->variation) ? json_encode($item->variation) : $item->variation }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">৳{{ number_format($item->price, 2) }}</td>
                            <td class="text-end">৳{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="row">
                <div class="col-md-6">
                    @if($order->notes)
                    <div class="bg-light p-3 rounded">
                        <h6 class="mb-2">Notes</h6>
                        <p class="mb-0 text-muted">{{ $order->notes }}</p>
                    </div>
                    @endif
                </div>
                @if(auth()->user()->hasPermission('orders.view-pricing'))
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-end">Subtotal:</td>
                            <td class="text-end" style="width: 150px;">৳{{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        @if($order->discount > 0)
                        <tr>
                            <td class="text-end">Discount:</td>
                            <td class="text-end text-danger">-৳{{ number_format($order->discount, 2) }}</td>
                        </tr>
                        @endif
                        @if($order->shipping_cost > 0)
                        <tr>
                            <td class="text-end">Shipping:</td>
                            <td class="text-end">৳{{ number_format($order->shipping_cost, 2) }}</td>
                        </tr>
                        @endif
                        @if($order->tax > 0)
                        <tr>
                            <td class="text-end">Tax:</td>
                            <td class="text-end">৳{{ number_format($order->tax, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="table-light">
                            <td class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong class="text-primary fs-5">৳{{ number_format($order->total, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
                @endif
            </div>

            <!-- Payment Info -->
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase mb-2">Payment Method</h6>
                    <p class="mb-0">{{ ucfirst($order->payment_method ?? 'N/A') }}</p>
                </div>
                @if($order->transaction_id)
                <div class="col-md-6 text-md-end">
                    <h6 class="text-muted text-uppercase mb-2">Transaction ID</h6>
                    <p class="mb-0">{{ $order->transaction_id }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
}
</style>
@endsection
