@forelse($orders as $order)
<tr>
    <td>
        <div class="fw-medium">{{ $order->order_number }}</div>
        <small class="text-muted">{{ $order->created_at->format('M d, Y h:i A') }}</small>
    </td>
    <td>
        @if($order->user)
            <div class="fw-medium">{{ $order->user->name }}</div>
            <small class="text-muted">{{ $order->user->email }}</small>
        @else
            <div class="fw-medium">{{ trim($order->billing_first_name . ' ' . $order->billing_last_name) }}</div>
            <small class="text-muted">{{ $order->billing_phone }}</small>
        @endif
    </td>
    <td>
        {{ $order->shipping_city ?: ($order->billing_city ?: '-') }}
        @if($order->shipping_area_id || $order->shipping_postcode)
            <br><small class="text-muted">{{ $order->shipping_postcode }}</small>
        @endif
    </td>
    <td>
        <span class="badge {{ $order->status_badge_class ?? 'bg-secondary' }}">
            {{ ucfirst($order->status) }}
        </span>
    </td>
    <td>
        <span class="badge {{ $order->payment_status_badge_class ?? 'bg-secondary' }}">
            {{ ucfirst($order->payment_status) }}
        </span>
    </td>
    <td class="text-end">{{ config('app.currency_symbol', '৳') }}{{ number_format((float) $order->total, 2) }}</td>
    <td>
        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary" title="View Order">
            <i class="bi bi-eye"></i>
        </a>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center text-muted py-4">
        <i class="bi bi-inbox fs-4 d-block mb-2"></i>
        No orders match this filter.
    </td>
</tr>
@endforelse
