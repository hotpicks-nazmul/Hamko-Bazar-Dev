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
        <strong>{{ $order->order_number }}</strong>
    </td>
    @if(auth()->user()->hasPermission('orders.view-customer'))
    <td>
        <div>
            <strong>{{ $order->billing_full_name }}</strong>
            @if($order->user)
                <br><small class="text-muted">{{ $order->user->email }}</small>
            @else
                <br><small class="text-muted">{{ $order->billing_email }}</small>
            @endif
            @if($order->billing_phone)
                <br><small class="text-muted"><i class="bi bi-phone me-1"></i>{{ $order->billing_phone }}</small>
            @endif
        </div>
    </td>
    @endif
    @if(auth()->user()->hasPermission('orders.view-pricing'))
    <td>
        <div>৳{{ number_format($order->total, 2) }}</div>
        <small class="text-muted">
            <i class="bi bi-cart3 me-1"></i>{{ $order->items->count() }} items
        </small>
    </td>
    @endif
    <td>
        <span class="badge align-self-start {{ $order->payment_status_badge_class }}">
            {{ ucfirst($order->payment_status) }}
        </span>
        <br><span class="badge bg-secondary mt-1">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
    </td>
    <td>
        <span class="badge {{ $order->status_badge_class }}">
            {{ ucfirst($order->status) }}
        </span>
    </td>
    <td style="min-width:160px;">
        @if($order->warehouse)
            <div class="fw-medium small">{{ $order->warehouse->name }}</div>
            @if($order->status === 'picking' && $order->pickedBy)
                <small class="text-primary d-block mt-1">
                    <i class="bi bi-person-check me-1"></i>Picked by: {{ $order->pickedBy->name }}
                </small>
                @if($order->picking_started_at)
                    <small class="text-muted">{{ $order->picking_started_at->format('d M, H:i') }}</small>
                @endif
            @elseif($order->status === 'packed' && $order->packedBy)
                <small class="text-success d-block mt-1">
                    <i class="bi bi-box-seam me-1"></i>Packed by: {{ $order->packedBy->name }}
                </small>
                @if($order->packed_at)
                    <small class="text-muted">{{ $order->packed_at->format('d M, H:i') }}</small>
                @endif
            @elseif(in_array($order->status, ['pending','confirmed','processing']))
                <small class="text-muted"><i class="bi bi-hourglass me-1"></i>Waiting for picking</small>
            @elseif(in_array($order->status, ['shipped','delivered']))
                <small class="text-success"><i class="bi bi-check-circle me-1"></i>Completed</small>
            @endif
        @else
            <small class="text-muted">N/A</small>
        @endif
    </td>
    <td>
        {{ $order->created_at->format('d M, Y') }}
        <br><small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn btn-sm btn-outline-secondary" title="Invoice" target="_blank">
                <i class="bi bi-receipt"></i>
            </a>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="More Actions">
                    <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.orders.show', $order->id) }}">
                            <i class="bi bi-eye me-2"></i> View Details
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank">
                            <i class="bi bi-receipt me-2"></i> View Invoice
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-clock me-2"></i> Mark Pending
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-check-circle me-2"></i> Mark Confirmed
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="processing">
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-arrow-repeat me-2"></i> Mark Processing
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="shipped">
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-truck me-2"></i> Mark Shipped
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="delivered">
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-check2-all me-2"></i> Mark Delivered
                            </button>
                        </form>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                                <i class="bi bi-x-circle me-2"></i> Cancel Order
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="{{ 7 - (auth()->user()->hasPermission('orders.view-customer') ? 0 : 1) - (auth()->user()->hasPermission('orders.view-pricing') ? 0 : 1) }}" class="text-center py-5">
        <i class="bi bi-folder text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No orders found</p>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </a>
    </td>
</tr>
@endforelse
