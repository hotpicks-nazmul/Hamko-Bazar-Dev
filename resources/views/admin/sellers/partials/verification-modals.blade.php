{{--
    Verification modals, rendered OUTSIDE the table and refreshed with the AJAX rows.
    A modal inside <tbody> is invalid table content: the parser (and innerHTML in a table context)
    hoists it out of the cell, so Bootstrap's target/backdrop pairing breaks and the buttons stop
    opening anything. The rows partial carries only <tr> elements; this file carries only modals.
--}}
@foreach($sellers as $seller)
    @if($seller->verification_status !== 'verified')
    <div class="modal fade" id="approveModal{{ $seller->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Seller Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.sellers.verification.process', $seller->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to approve <strong>{{ $seller->name }}</strong> ({{ $seller->shop_name }})?</p>
                        <p class="text-muted small">This will mark the seller as verified and activate their account.</p>

                        <div class="mb-3">
                            <label for="notes{{ $seller->id }}" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes{{ $seller->id }}" name="verification_notes" rows="3" placeholder="Add any notes about this approval..."></textarea>
                        </div>

                        <input type="hidden" name="action" value="approve">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Approve Verification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($seller->verification_status !== 'rejected')
    <div class="modal fade" id="rejectModal{{ $seller->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Seller Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.sellers.verification.process', $seller->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reject <strong>{{ $seller->name }}</strong> ({{ $seller->shop_name }})?</p>

                        <div class="mb-3">
                            <label for="rejectNotes{{ $seller->id }}" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectNotes{{ $seller->id }}" name="verification_notes" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                            <div class="form-text">This will be visible to the seller.</div>
                        </div>

                        <input type="hidden" name="action" value="reject">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-lg me-1"></i> Reject Verification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach
