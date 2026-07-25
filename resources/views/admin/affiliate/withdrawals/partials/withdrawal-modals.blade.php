{{--
    Payment-details modals, rendered OUTSIDE the table and refreshed with the AJAX rows.
    A modal inside <tbody> is invalid table content: the parser hoists it out of the cell and
    Bootstrap's target/backdrop pairing breaks, so the "View" button stops opening anything.
--}}
@foreach($withdrawals as $withdrawal)
<div class="modal fade" id="detailsModal{{ $withdrawal->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details — #{{ $withdrawal->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    <strong>৳{{ number_format($withdrawal->amount, 2) }}</strong>
                    · {{ ucfirst($withdrawal->payment_method ?? '-') }}
                    · {{ $withdrawal->affiliate?->user?->name ?? '-' }}
                </p>
                <pre class="bg-light p-3 rounded mb-0">{{ $withdrawal->payment_details ?? 'No payment details provided' }}</pre>
                @if($withdrawal->admin_note)
                    <div class="alert alert-secondary mt-3 mb-0">{{ $withdrawal->admin_note }}</div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach
