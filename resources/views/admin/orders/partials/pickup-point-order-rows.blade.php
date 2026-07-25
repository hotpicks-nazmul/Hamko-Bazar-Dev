@php
    $search = request('search');
@endphp
@forelse($orders as $order)
@php
    $isMatch = $search && (
        stripos($order->order_number, $search) !== false ||
        stripos($order->billing_full_name, $search) !== false ||
        stripos($order->billing_email, $search) !== false ||
        stripos($order->billing_phone, $search) !== false
    );
@endphp
<tr class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input order-checkbox" value="{{ $order->id }}">
    </td>
    <td>
        <div class="fw-semibold">{{ $order->order_number }}</div>
        @if($order->pickupPointLocation)
            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $order->pickupPointLocation->city }}</small>
        @endif
    </td>
    @if(auth()->user()->hasPermission('orders.view-customer'))
    <td>
        @if($order->user)
            <div class="d-flex align-items-center">
                <div class="bg-light rounded-circle p-2 me-2">
                    <i class="bi bi-person text-muted"></i>
                </div>
                <div>
                    <div class="fw-medium">{{ $order->billing_full_name }}</div>
                    <small class="text-muted">{{ $order->user->email }}</small>
                </div>
            </div>
        @else
            <div class="fw-medium">{{ $order->billing_full_name }}</div>
            <small class="text-muted">{{ $order->billing_email }}</small>
        @endif
    </td>
    @endif
    <td>
        @if($order->pickupPointLocation)
            <div class="fw-medium">{{ $order->pickupPointLocation->name }}</div>
            <small class="text-muted">{{ $order->pickupPointLocation->city }}</small>
        @else
            <span class="text-muted">Not assigned</span>
        @endif
    </td>
    @if(auth()->user()->hasPermission('orders.view-pricing'))
    <td>
        <div class="fw-semibold">৳{{ number_format($order->total, 2) }}</div>
        <small class="text-muted">{{ $order->items->count() }} item(s)</small>
    </td>
    @endif
    <td>
        <div class="d-flex flex-column gap-1">
            <span class="badge {{ $order->payment_status_badge_class }}">
                {{ ucfirst($order->payment_status) }}
            </span>
            <small class="text-muted">{{ ucfirst($order->payment_method ?? 'N/A') }}</small>
        </div>
    </td>
    <td>
        @if($order->picked_up_at)
            <span class="badge bg-success">
                <i class="bi bi-check-circle me-1"></i>Picked Up
            </span>
            <small class="text-muted d-block">{{ $order->picked_up_at->format('d M, H:i') }}</small>
        @else
            <span class="badge {{ $order->status_badge_class }}">
                {{ ucfirst($order->status) }}
            </span>
        @endif
    </td>
    <td>
        <div>{{ $order->created_at->format('d M, Y') }}</div>
        <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.orders.pickup-point.show', $order->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn btn-sm btn-outline-secondary" title="Invoice" target="_blank">
                <i class="bi bi-receipt"></i>
            </a>
            @if(!$order->picked_up_at && $order->status !== 'cancelled')
                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#markPickedUpModal{{ $order->id }}" title="Mark Picked Up">
                    <i class="bi bi-box-seam"></i>
                </button>
                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="d-flex" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel Order">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @endif
        </div>

        <!-- Mark as Picked Up Modal -->
        @if(!$order->picked_up_at && $order->status !== 'cancelled')
        <div class="modal fade" id="markPickedUpModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.orders.pickup-point.picked-up', $order->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Mark Order as Picked Up</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Order <span class="text-primary">{{ $order->order_number }}</span></label>
                                @if(auth()->user()->hasPermission('orders.view-customer'))
                                <p class="text-muted mb-0">Customer: {{ $order->billing_full_name }}</p>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Picked Up By <span class="text-danger">*</span></label>
                                <input type="text" name="picked_up_by" class="form-control" placeholder="Name of person picking up" required>
                                <small class="text-muted">Enter the name of the person who picked up the order</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i> Confirm Pickup
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="{{ 9 - (auth()->user()->hasPermission('orders.view-customer') ? 0 : 1) - (auth()->user()->hasPermission('orders.view-pricing') ? 0 : 1) }}" class="text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No pick-up point orders found.</p>
        <a href="{{ route('admin.orders.pickup-point') }}" class="btn btn-sm btn-outline-secondary mt-1">
            <i class="bi bi-arrow-repeat me-1"></i> Refresh
        </a>
    </td>
</tr>
@endforelse
