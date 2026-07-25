@extends('admin.layouts.app')

@section('title', 'Edit Newsletter')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Newsletter</h4>
    <a href="{{ route('admin.marketing.newsletters.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Newsletters
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        @if($newsletter->isSent())
        <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            This newsletter has already been sent. Editing it will not affect the sent copy.
        </div>
        @endif
        
        <form action="{{ route('admin.marketing.newsletters.update', $newsletter->id) }}" method="POST" id="newsletterForm">
            @csrf
            @method('PUT')
            
            <!-- Subject Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-card-text me-2"></i>Newsletter Content</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                               value="{{ old('subject', $newsletter->subject) }}" placeholder="Enter newsletter subject" required {{ $newsletter->isSent() ? 'readonly' : '' }}>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">The subject line that recipients will see in their inbox</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" 
                                  rows="15" placeholder="Enter newsletter content (HTML supported)" required {{ $newsletter->isSent() ? 'readonly' : '' }}>{{ old('content', $newsletter->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">HTML is supported. You can use standard HTML tags for formatting.</div>
                        @enderror
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="col-lg-4">
        <!-- Status Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Status</h6>
            </div>
            <div class="card-body">
                @php
                    $badgeClass = match($newsletter->status) {
                        'sent' => 'success',
                        'scheduled' => 'warning',
                        'failed' => 'danger',
                        default => 'secondary',
                    };
                @endphp
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted">Current Status:</span>
                    <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($newsletter->status) }}</span>
                </div>
                
                @if($newsletter->sent_at)
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted">Sent At:</span>
                    <span>{{ $newsletter->sent_at->format('M d, Y h:i A') }}</span>
                </div>
                @endif
                
                @if($newsletter->recipients_count > 0)
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted">Recipients:</span>
                    <span>{{ $newsletter->recipients_count }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Recipients Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-people me-2"></i>Recipients</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="recipients_type" class="form-label">Send To</label>
                    <select name="recipients_type" id="recipients_type" class="form-select" form="newsletterForm" {{ $newsletter->isSent() ? 'disabled' : '' }}>
                        <option value="all" {{ $newsletter->recipients_type == 'all' ? 'selected' : '' }}>All (Subscribers + Customers)</option>
                        <option value="subscribers" {{ $newsletter->recipients_type == 'subscribers' ? 'selected' : '' }}>Newsletter Subscribers Only</option>
                        <option value="users" {{ $newsletter->recipients_type == 'users' ? 'selected' : '' }}>Registered Customers Only</option>
                    </select>
                </div>
                
                @if($newsletter->isDraft())
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#sendModal">
                    <i class="bi bi-send me-1"></i> Send Newsletter
                </button>
                @endif
            </div>
        </div>
        
        <!-- Actions Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
                @if($newsletter->isDraft())
                <form action="{{ route('admin.marketing.newsletters.duplicate', $newsletter->id) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-copy me-1"></i> Duplicate Newsletter
                    </button>
                </form>
                @endif
                
                @if(!$newsletter->isSent())
                <button type="button" class="btn btn-outline-danger w-100" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i> Delete Newsletter
                </button>
                
                <form id="deleteForm" action="{{ route('admin.marketing.newsletters.destroy', $newsletter->id) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
                @endif
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
                        <label for="recipients_type_modal" class="form-label">Recipients</label>
                        <select name="recipients_type" id="recipients_type_modal" class="form-select" required>
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

<!-- Floating Buttons -->
@if(!$newsletter->isSent())
<div class="floating-save-container">
    <a href="{{ route('admin.marketing.newsletters.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="newsletterForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Newsletter
    </button>
</div>
@endif

@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this newsletter?')) {
            document.getElementById('deleteForm').submit();
        }
    }

    // Update recipient count
    function updateRecipientCount() {
        const recipientsType = document.getElementById('recipients_type_modal').value;
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

    document.getElementById('recipients_type_modal')?.addEventListener('change', updateRecipientCount);
</script>
@endpush
