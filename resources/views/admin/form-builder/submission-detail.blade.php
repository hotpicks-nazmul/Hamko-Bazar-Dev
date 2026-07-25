@extends('admin.layouts.app')

@section('title', 'Submission Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Submission #{{ $submission->id }}</h4>
        <small class="text-muted">{{ $form->name }}</small>
    </div>
    <div>
        <a href="{{ route('admin.form-builder.submissions', $form->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Submissions
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Submission Data -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Submitted Data</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tbody>
                        @foreach($form->fields as $field)
                        <tr>
                            <td class="text-muted" style="width: 200px;">{{ $field->label }}</td>
                            <td>
                                @php
                                    $value = $submission->getFieldValue($field->name);
                                @endphp
                                @if($field->type === 'file' && $value)
                                    <a href="{{ asset('storage/' . $value) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark me-1"></i> View File
                                    </a>
                                @elseif(is_array($value))
                                    {{ implode(', ', $value) }}
                                @elseif(is_bool($value))
                                    {{ $value ? 'Yes' : 'No' }}
                                @else
                                    {{ $value ?? '-' }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Add Note -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-sticky me-2"></i>Admin Notes</h6>
            </div>
            <div class="card-body">
                <form id="noteForm">
                    @csrf
                    <div class="mb-3">
                        <textarea name="notes" id="notesTextarea" class="form-control" rows="3" 
                                  placeholder="Add a note about this submission...">{{ $submission->notes }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Note
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Submission Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Submission Info</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Status</small>
                    @if($submission->is_read)
                        <span class="badge bg-secondary">Read</span>
                    @else
                        <span class="badge bg-warning text-dark">Unread</span>
                    @endif
                </div>
                
                <div class="mb-3">
                    <small class="text-muted d-block">Submitted By</small>
                    @if($submission->user)
                        <div class="fw-medium">{{ $submission->user->name }}</div>
                        <small class="text-muted">{{ $submission->user->email }}</small>
                    @elseif($submission->guest_email)
                        <div class="fw-medium">{{ $submission->guest_email }}</div>
                        <small class="text-muted">Guest User</small>
                    @else
                        <span class="text-muted">Guest</span>
                    @endif
                </div>
                
                <div class="mb-3">
                    <small class="text-muted d-block">IP Address</small>
                    <code>{{ $submission->ip_address ?? 'N/A' }}</code>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted d-block">User Agent</small>
                    <small class="text-muted">{{ $submission->user_agent ?? 'N/A' }}</small>
                </div>
                
                <div class="mb-0">
                    <small class="text-muted d-block">Submitted At</small>
                    {{ $submission->created_at->format('d M, Y h:i A') }}
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.form-builder.submissions.toggle-read', [$form->id, $submission->id]) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $submission->is_read ? 'btn-warning' : 'btn-success' }} w-100">
                        <i class="bi bi-{{ $submission->is_read ? 'envelope' : 'envelope-open' }} me-1"></i>
                        {{ $submission->is_read ? 'Mark as Unread' : 'Mark as Read' }}
                    </button>
                </form>
                
                <form action="{{ route('admin.form-builder.submissions.destroy', [$form->id, $submission->id]) }}" 
                      method="POST" onsubmit="return confirm('Are you sure you want to delete this submission?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i> Delete Submission
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('noteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const notes = document.getElementById('notesTextarea').value;
        
        fetch('{{ route("admin.form-builder.submissions.note", [$form->id, $submission->id]) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ notes })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Note saved successfully!');
            }
        });
    });
});
</script>
@endpush
