@forelse($orders as $order)
<tr>
    <td>
        <div class="fw-semibold">{{ $order->order_number }}</div>
        <small class="text-muted">{{ $order->items->count() }} item(s)</small>
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
        @php
            $sellers = [];
            foreach($order->items as $item) {
                if($item->product && $item->product->seller) {
                    $sellers[$item->product->seller->id] = $item->product->seller;
                }
            }
        @endphp
        @if(count($sellers) > 0)
            <div class="d-flex flex-column gap-1">
                @foreach($sellers as $seller)
                    <span class="badge bg-info text-dark">
                        <i class="bi bi-shop me-1"></i>{{ $seller->name }}
                    </span>
                @endforeach
            </div>
        @else
            <span class="badge bg-secondary">No Seller</span>
        @endif
    </td>
    @if(auth()->user()->hasPermission('orders.view-pricing'))
    <td>
        <div class="fw-semibold">৳{{ number_format($order->total, 2) }}</div>
        <small class="text-muted">{{ ucfirst($order->payment_method ?? 'N/A') }}</small>
    </td>
    @endif
    <td>
        <div class="d-flex flex-column gap-1">
            <span class="badge align-self-start {{ $order->payment_status_badge_class }}">
                {{ ucfirst($order->payment_status) }}
            </span>
        </div>
    </td>
    <td>
        <span class="badge {{ $order->status_badge_class }}">
            {{ ucfirst($order->status) }}
        </span>
    </td>
    <td>
        <div>{{ $order->created_at->format('d M, Y') }}</div>
        <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.orders.seller.show', $order->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn btn-sm btn-outline-secondary" title="Invoice" target="_blank">
                <i class="bi bi-receipt"></i>
            </a>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-gear"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.orders.seller.show', $order->id) }}">
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
    <td colspan="{{ 8 - (auth()->user()->hasPermission('orders.view-customer') ? 0 : 1) - (auth()->user()->hasPermission('orders.view-pricing') ? 0 : 1) }}" class="text-center py-5">
        <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
        <p class="mb-0 text-muted">No seller orders found.</p>
        @if(request('search') || request('status') || request('payment_status') || request('seller_id'))
            <a href="{{ route('admin.orders.seller') }}" class="btn btn-outline-secondary btn-sm mt-2">
                <i class="bi bi-x-lg me-1"></i> Clear Filters
            </a>
        @endif
    </td>
</tr>
@endforelse
