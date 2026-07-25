@forelse($shipments as $order)
<tr>
    <td>
        <div class="form-check">
            <input type="checkbox" class="form-check-input shipment-checkbox" value="{{ $order->id }}">
        </div>
    </td>
    <td>
        <div class="fw-medium">{{ $order->order_number }}</div>
        <small class="text-muted">{{ $order->created_at->format('M d, Y h:i A') }}</small>
    </td>
    <td>
        @if($order->user)
            <div class="fw-medium">{{ $order->user->name }}</div>
            <small class="text-muted">{{ $order->user->email }}</small>
        @else
            <div class="fw-medium">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</div>
            <small class="text-muted">{{ $order->billing_email }}</small>
        @endif
    </td>
    <td>
        @if($order->tracking_number)
            <span class="badge bg-primary">{{ $order->tracking_number }}</span>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        @if($order->shipping_company)
            {{ $order->shipping_company }}
        @else
            <span class="text-muted">In House</span>
        @endif
    </td>
    <td>
        <span class="badge {{ $order->status_badge_class }}">
            {{ ucfirst($order->status) }}
        </span>
    </td>
    <td>
        <span class="badge {{ $order->payment_status_badge_class }}">
            {{ ucfirst($order->payment_status) }}
        </span>
    </td>
    <td>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-primary" 
                data-bs-toggle="modal" 
                data-bs-target="#trackingModal{{ $order->id }}"
                title="Track Shipment">
                <i class="bi bi-geo-alt"></i>
            </button>
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary" title="View Order">
                <i class="bi bi-eye"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-success"
                onclick="generateTrackingNumber({{ $order->id }})"
                title="Generate Tracking Number"
                {{ $order->tracking_number ? 'disabled' : '' }}>
                <i class="bi bi-upc-scan"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No shipments found</p>
    </td>
</tr>
@endforelse
