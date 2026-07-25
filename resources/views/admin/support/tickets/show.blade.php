@extends('admin.layouts.app')

@section('title', 'Ticket: ' . $ticket->ticket_number)

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Ticket: {{ $ticket->ticket_number }}</h4>
        <p class="text-muted mb-0">{{ $ticket->subject }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Tickets
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Content - Conversation -->
    <div class="col-lg-8">
        <!-- Ticket Description -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Conversation</h6>
                    <div>
                        @if($ticket->status !== 'closed')
                        <button type="button" class="btn btn-sm btn-outline-warning"
                                onclick="document.getElementById('replyForm').scrollIntoView({behavior: 'smooth'})">
                            <i class="bi bi-reply me-1"></i> Reply
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="p-3 bg-light border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex gap-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white"
                                 style="width: 40px; height: 40px;">
                                {{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-medium">{{ $ticket->user->name ?? 'Unknown User' }}</div>
                                <div class="small text-muted">{{ $ticket->user->email ?? '' }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">{{ $ticket->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge {{ $ticket->getPriorityBadgeClass() }} me-1">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                        <span class="badge {{ $ticket->getStatusBadgeClass() }}">
                            {{ ucfirst($ticket->status) }}
                        </span>
                    </div>
                    <div class="mt-3">
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $ticket->description }}</p>
                    </div>
                </div>

                <!-- Replies -->
                @forelse($ticket->replies as $reply)
                <div class="p-3 {{ $reply->is_admin_reply ? 'bg-white' : 'bg-light' }} border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex gap-3">
                            @if($reply->is_admin_reply)
                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white"
                                 style="width: 40px; height: 40px;">
                                {{ strtoupper(substr($reply->admin->name ?? 'A', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-medium">{{ $reply->admin->name ?? 'Admin' }}</div>
                                <div class="small text-muted">Support Team</div>
                            </div>
                            @else
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white"
                                 style="width: 40px; height: 40px;">
                                {{ strtoupper(substr($reply->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-medium">{{ $reply->user->name ?? 'Unknown User' }}</div>
                                <div class="small text-muted">{{ $reply->user->email ?? '' }}</div>
                            </div>
                            @endif
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">{{ $reply->created_at->format('d M Y, h:i A') }}</div>
                            @if($reply->is_admin_reply)
                            <span class="badge bg-success mt-1">Admin Reply</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $reply->message }}</p>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-chat-square-text" style="font-size: 2rem;"></i>
                    <p class="mt-2">No replies yet</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Reply Form -->
        @if($ticket->status !== 'closed')
        <div class="card border-0 shadow-sm" id="replyForm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-reply me-2"></i>Send Reply</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.support.tickets.reply', $ticket->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="message" class="form-label">Reply Message <span class="text-danger">*</span></label>
                        <textarea name="message" id="message" rows="5"
                                  class="form-control @error('message') is-invalid @enderror"
                                  placeholder="Type your reply here..." required>{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="form-text">
                            The customer will be notified via email about your reply.
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i> Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3">This Ticket is Closed</h5>
                <p class="text-muted">This ticket has been closed. Would you like to reopen it?</p>
                <a href="{{ route('admin.support.tickets.reopen', $ticket->id) }}"
                   class="btn btn-primary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reopen Ticket
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar - Ticket Details -->
    <div class="col-lg-4">
        <!-- Ticket Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Ticket Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small text-muted">Ticket Number</label>
                    <div class="fw-medium">{{ $ticket->ticket_number }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Subject</label>
                    <div class="fw-medium">{{ $ticket->subject }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Category</label>
                    <div>
                        <span class="badge bg-light text-dark">{{ ucfirst($ticket->category) }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Priority</label>
                    <div>
                        <select class="form-select form-select-sm" id="prioritySelect"
                                onchange="updateTicketField('priority', this.value)">
                            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Status</label>
                    <div>
                        <select class="form-select form-select-sm" id="statusSelect"
                                onchange="updateTicketField('status', this.value)">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="pending" {{ $ticket->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="answered" {{ $ticket->status === 'answered' ? 'selected' : '' }}>Answered</option>
                            <option value="solved" {{ $ticket->status === 'solved' ? 'selected' : '' }}>Solved</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Assigned To</label>
                    <div>
                        <select class="form-select form-select-sm" id="assignSelect"
                                onchange="assignTicket(this.value)">
                            <option value="">-- Unassigned --</option>
                            @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ $ticket->assigned_to === $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Created</label>
                    <div class="small">{{ $ticket->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Last Updated</label>
                    <div class="small">{{ $ticket->updated_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Info</h6>
            </div>
            <div class="card-body">
                <div class="d-flex gap-3 align-items-center mb-3">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white"
                         style="width: 48px; height: 48px; font-size: 1.2rem;">
                        {{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-medium">{{ $ticket->user->name ?? 'Unknown User' }}</div>
                        <div class="small text-muted">{{ $ticket->user->email ?? '' }}</div>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Member Since</label>
                    <div class="small">{{ $ticket->user->created_at->format('d M Y') ?? 'N/A' }}</div>
                </div>
                @if($ticket->user->phone)
                <div class="mb-2">
                    <label class="form-label small text-muted">Phone</label>
                    <div class="small">{{ $ticket->user->phone }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
                @if($ticket->status !== 'closed')
                <a href="{{ route('admin.support.tickets.close', $ticket->id) }}"
                   class="btn btn-outline-warning w-100 mb-2"
                   onclick="event.preventDefault(); if(confirm('Are you sure you want to close this ticket?')) { document.getElementById('closeForm').submit(); }">
                    <i class="bi bi-x-circle me-1"></i> Close Ticket
                </a>
                <form id="closeForm" method="POST" action="{{ route('admin.support.tickets.close', $ticket->id) }}" style="display: none;">
                    @csrf
                </form>
                @endif
                <button type="button" class="btn btn-outline-danger w-100"
                        onclick="if(confirm('Are you sure you want to delete this ticket?')) { document.getElementById('deleteTicketForm').submit(); }">
                    <i class="bi bi-trash me-1"></i> Delete Ticket
                </button>
                <form id="deleteTicketForm" method="POST" action="{{ route('admin.support.tickets.destroy', $ticket->id) }}" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateTicketField(field, value) {
        fetch(`{{ route('admin.support.tickets.update-status', $ticket->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ [field]: value })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Updated successfully');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Failed to update');
        });
    }

    function assignTicket(adminId) {
        fetch(`{{ route('admin.support.tickets.assign', $ticket->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ assigned_to: adminId || null })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Ticket assigned successfully');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Failed to assign ticket');
        });
    }

    function showToast(type, message) {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 position-fixed bottom-0 end-0 m-3`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        setTimeout(() => toast.remove(), 3000);
    }
</script>
@endpush
