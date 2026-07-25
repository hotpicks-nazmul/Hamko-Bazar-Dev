@forelse($history as $item)
<tr>
    <td>
        <small>{{ \Carbon\Carbon::parse($item->created_at)->format('M d, Y') }}</small>
        <small class="text-muted d-block">{{ \Carbon\Carbon::parse($item->created_at)->format('h:i A') }}</small>
    </td>
    <td>
        @if($item->product_name)
        <div class="fw-medium">{{ $item->product_name }}</div>
        <small class="text-muted">{{ $item->product_sku ?? '' }}</small>
        @else
        <span class="text-muted">Product #{{ $item->product_id }}</span>
        @endif
    </td>
    <td>
        @switch($item->action_type)
            @case('stock_in')
                <span class="badge bg-success"><i class="bi bi-plus-lg me-1"></i>Stock In</span>
                @break
            @case('stock_out')
                <span class="badge bg-danger"><i class="bi bi-dash-lg me-1"></i>Stock Out</span>
                @break
            @case('adjustment')
                <span class="badge bg-primary"><i class="bi bi-arrow-repeat me-1"></i>Adjustment</span>
                @break
            @case('order')
                <span class="badge bg-info"><i class="bi bi-cart me-1"></i>Order</span>
                @break
            @default
                <span class="badge bg-secondary">{{ $item->action_type }}</span>
        @endswitch
    </td>
    <td class="text-center">
        <span class="text-muted">{{ $item->quantity_before }}</span>
    </td>
    <td class="text-center">
        @if($item->quantity_change > 0)
        <span class="text-success fw-bold">+{{ $item->quantity_change }}</span>
        @elseif($item->quantity_change < 0)
        <span class="text-danger fw-bold">{{ $item->quantity_change }}</span>
        @else
        <span class="text-muted">0</span>
        @endif
    </td>
    <td class="text-center">
        <span class="fw-medium">{{ $item->quantity_after }}</span>
    </td>
    <td>
        <small class="text-muted">{{ $item->reason ?? '-' }}</small>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-5">
        <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-0 mt-2">No inventory history found</p>
    </td>
</tr>
@endforelse
