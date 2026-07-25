@forelse($affiliates as $affiliate)
<tr>
    <td>
        <input type="checkbox" class="form-check-input affiliate-checkbox" value="{{ $affiliate->id }}">
    </td>
    <td>{{ $affiliate->id }}</td>
    <td>{{ $affiliate->user->name ?? '-' }}</td>
    <td><code>{{ $affiliate->affiliate_code }}</code></td>
    <td>{{ number_format($affiliate->clicks_count) }}</td>
    <td>{{ number_format($affiliate->sales_count) }}</td>
    @if(auth()->user()->hasPermission('view-revenue'))
    <td>${{ number_format($affiliate->total_sales ?? 0, 2) }}</td>
    <td>${{ number_format($affiliate->total_commission ?? 0, 2) }}</td>
    @endif
    <td>
        @if($affiliate->status === 'approved')
        <span class="badge bg-success">Active</span>
        @elseif($affiliate->status === 'pending')
        <span class="badge bg-warning">Pending</span>
        @else
        <span class="badge bg-danger">Suspended</span>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-5">
        <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3 text-muted">No data available</h5>
        <p class="text-muted">Affiliate performance data will appear here.</p>
    </td>
</tr>
@endforelse
