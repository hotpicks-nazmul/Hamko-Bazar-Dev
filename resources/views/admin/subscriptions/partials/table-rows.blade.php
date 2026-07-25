@forelse($subscriptions as $subscription)
@php
    $search = request('search');
    $isMatch = $search && (
        stripos($subscription->subscription_number, $search) !== false ||
        stripos($subscription->shipping_first_name, $search) !== false ||
        stripos($subscription->shipping_last_name, $search) !== false ||
        stripos($subscription->shipping_email, $search) !== false ||
        stripos($subscription->plan_name, $search) !== false
    );
@endphp
<tr class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input row-checkbox" 
               value="{{ $subscription->id }}" onclick="updateBulkActions()">
    </td>
    <td>
        <div class="fw-medium">{{ $subscription->subscription_number }}</div>
        <small class="text-muted">{{ $subscription->plan_name }}</small>
    </td>
    <td>
        <div class="fw-medium">{{ $subscription->shipping_full_name }}</div>
        <small class="text-muted">{{ $subscription->shipping_email }}</small>
    </td>
    <td>
        <div class="d-flex align-items-center">
            @php
                $imageUrl = $subscription->product->featured_image ?? null;
                if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                    $imageUrl = '/storage/' . $imageUrl;
                }
            @endphp
            @if($imageUrl)
                <img src="{{ $imageUrl }}" alt="" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
            @else
                <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                    <i class="bi bi-image text-white"></i>
                </div>
            @endif
            <div>
                <div class="fw-medium small">{{ Str::limit($subscription->product->name ?? 'N/A', 30) }}</div>
                <small class="text-muted">Qty: {{ $subscription->quantity }}</small>
            </div>
        </div>
    </td>
    <td>
        <span class="badge bg-light text-dark">
            {{ $subscription->billing_frequency_label }}
        </span>
    </td>
    <td>
        @if($subscription->next_billing_date)
            <div class="fw-medium">{{ $subscription->next_billing_date->format('M d, Y') }}</div>
            @if($subscription->next_billing_date->isPast())
                <small class="text-danger">Overdue</small>
            @else
                <small class="text-muted">{{ $subscription->next_billing_date->diffForHumans() }}</small>
            @endif
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        <span class="badge {{ $subscription->status_badge_class }}">
            {{ ucfirst($subscription->status) }}
        </span>
    </td>
    <td>
        <span class="badge {{ $subscription->payment_status_badge_class }}">
            {{ ucfirst($subscription->payment_status) }}
        </span>
    </td>
    <td>
        <div class="fw-medium">৳{{ number_format($subscription->total_price, 2) }}</div>
        @if(!$subscription->hasUnlimitedCycles())
            <small class="text-muted">{{ $subscription->completed_billing_cycles }}/{{ $subscription->total_billing_cycles }} cycles</small>
        @endif
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-sm btn-outline-primary" title="View">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="10" class="text-center py-5">
        <i class="bi bi-inbox display-4 text-muted"></i>
        <p class="text-muted mt-2">No subscriptions found</p>
    </td>
</tr>
@endforelse