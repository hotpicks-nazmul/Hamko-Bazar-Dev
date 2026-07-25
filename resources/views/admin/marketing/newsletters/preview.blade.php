@extends('admin.layouts.app')

@section('title', 'Preview Newsletter')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Preview Newsletter</h4>
    <a href="{{ route('admin.marketing.newsletters.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Newsletters
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Email Preview -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-envelope me-2"></i>Email Preview</h6>
            </div>
            <div class="card-body p-0">
                <!-- Email Header -->
                <div class="p-3 border-bottom bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <span class="text-muted small">From:</span>
                            <strong>{{ config('app.name') }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small">Subject:</span>
                            <strong>{{ $newsletter->subject }}</strong>
                        </div>
                    </div>
                </div>
                
                <!-- Email Content -->
                <div class="p-4">
                    {!! $newsletter->content !!}
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Newsletter Details -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Newsletter Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small d-block">Status</span>
                    @php
                        $badgeClass = match($newsletter->status) {
                            'sent' => 'success',
                            'scheduled' => 'warning',
                            'failed' => 'danger',
                            default => 'secondary',
                        };
                    @endphp
                    <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($newsletter->status) }}</span>
                </div>
                
                <div class="mb-3">
                    <span class="text-muted small d-block">Recipients Type</span>
                    <span>{{ ucfirst($newsletter->recipients_type) }}</span>
                </div>
                
                @if($newsletter->sent_at)
                <div class="mb-3">
                    <span class="text-muted small d-block">Sent At</span>
                    <span>{{ $newsletter->sent_at->format('M d, Y h:i A') }}</span>
                </div>
                @endif
                
                @if($newsletter->recipients_count > 0)
                <div class="mb-3">
                    <span class="text-muted small d-block">Recipients Count</span>
                    <span>{{ $newsletter->recipients_count }}</span>
                </div>
                @endif
                
                <div class="mb-0">
                    <span class="text-muted small d-block">Created At</span>
                    <span>{{ $newsletter->created_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
                @if($newsletter->isDraft())
                <a href="{{ route('admin.marketing.newsletters.edit', $newsletter->id) }}" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-pencil me-1"></i> Edit Newsletter
                </a>
                <button type="button" class="btn btn-outline-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#sendModal">
                    <i class="bi bi-send me-1"></i> Send Newsletter
                </button>
                @endif
                
                <form action="{{ route('admin.marketing.newsletters.duplicate', $newsletter->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-copy me-1"></i> Duplicate Newsletter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Send Modal -->
@if($newsletter->isDraft())
<div class="modal fade" id="sendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Newsletter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.marketing.newsletters.send', $newsletter->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to send this newsletter?</p>
                    <div class="mb-3">
                        <label for="recipients_type" class="form-label">Recipients</label>
                        <select name="recipients_type" id="recipients_type" class="form-select" required>
                            <option value="all" {{ $newsletter->recipients_type == 'all' ? 'selected' : '' }}>All (Subscribers + Customers)</option>
                            <option value="subscribers" {{ $newsletter->recipients_type == 'subscribers' ? 'selected' : '' }}>Newsletter Subscribers Only</option>
                            <option value="users" {{ $newsletter->recipients_type == 'users' ? 'selected' : '' }}>Registered Customers Only</option>
                        </select>
                        <div class="form-text">This will send to: <span id="recipientCount">-</span> recipients</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Send Newsletter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    // Update recipient count
    function updateRecipientCount() {
        const recipientsType = document.getElementById('recipients_type').value;
        fetch(`{{ route('admin.marketing.newsletters.recipient-count') }}?recipients_type=${recipientsType}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('recipientCount').textContent = data.count;
        });
    }

    // Initialize on modal open
    document.getElementById('sendModal')?.addEventListener('shown.bs.modal', function() {
        updateRecipientCount();
    });

    document.getElementById('recipients_type')?.addEventListener('change', updateRecipientCount);
</script>
@endpush
