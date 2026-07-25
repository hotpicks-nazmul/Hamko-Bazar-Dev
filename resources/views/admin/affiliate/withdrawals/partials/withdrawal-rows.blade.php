@foreach($withdrawals as $withdrawal)
@php
    $search = request('search');
    $isMatch = $search && (
        stripos($withdrawal->affiliate->user->name ?? '', $search) !== false ||
        stripos($withdrawal->affiliate->user->email ?? '', $search) !== false
    );
@endphp
<tr class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $withdrawal->id }}">
    </td>
    <td>{{ $withdrawal->id }}</td>
    <td>{{ $withdrawal->affiliate->user->name ?? '-' }}</td>
    <td>${{ number_format($withdrawal->amount, 2) }}</td>
    <td>{{ ucfirst($withdrawal->payment_method ?? '-') }}</td>
    <td>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $withdrawal->id }}">
            <i class="bi bi-eye"></i> View
        </button>
    </td>
    <td>
        @if($withdrawal->status === 'pending')
        <span class="badge bg-warning">Pending</span>
        @elseif($withdrawal->status === 'approved')
        <span class="badge bg-success">Approved</span>
        @elseif($withdrawal->status === 'rejected')
        <span class="badge bg-danger">Rejected</span>
        @else
        <span class="badge bg-info">Paid</span>
        @endif
    </td>
    <td>{{ $withdrawal->requested_at ? $withdrawal->requested_at->format('M d, Y H:i') : '-' }}</td>
    <td>
        <a href="{{ route('admin.affiliate.withdrawals.show', $withdrawal->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
            <i class="bi bi-eye"></i>
        </a>
        @if($withdrawal->status === 'pending')
        <form action="{{ route('admin.affiliate.withdrawals.approve', $withdrawal->id) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-success" title="Approve" onclick="return confirm('Are you sure you want to approve this withdrawal?')">
                <i class="bi bi-check-circle"></i>
            </button>
        </form>
        <form action="{{ route('admin.affiliate.withdrawals.reject', $withdrawal->id) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject" onclick="return confirm('Are you sure you want to reject this withdrawal?')">
                <i class="bi bi-x-circle"></i>
            </button>
        </form>
        @endif
    </td>
</tr>

{{-- Payment Details Modal --}}
<div class="modal fade" id="detailsModal{{ $withdrawal->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre class="bg-light p-3 rounded">{{ $withdrawal->payment_details ?? 'No payment details provided' }}</pre>
            </div>
        </div>
    </div>
</div>
@endforeach

@if($withdrawals->isEmpty())
<tr>
    <td colspan="9" class="text-center py-5">
        <i class="bi bi-wallet2 text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No withdrawals found</p>
        <p class="text-muted small">Withdrawal requests will appear here once affiliates request payouts.</p>
    </td>
</tr>
@endif
