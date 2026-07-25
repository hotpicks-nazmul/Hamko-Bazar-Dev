@extends('admin.layouts.app')

@section('title', 'Newsletters')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-envelope"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total</span>
            <span class="stat-card-value">{{ number_format($stats['total'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-file-earmark"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Draft</span>
            <span class="stat-card-value">{{ number_format($stats['draft'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Sent</span>
            <span class="stat-card-value">{{ number_format($stats['sent'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Scheduled</span>
            <span class="stat-card-value">{{ number_format($stats['scheduled'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Failed</span>
            <span class="stat-card-value">{{ number_format($stats['failed'] ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Newsletters</h4>
    <a href="{{ route('admin.marketing.newsletters.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Create Newsletter
    </a>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search Input -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Search by subject..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('admin.marketing.newsletters.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Newsletters Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Subject</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 150px;">Sent Date</th>
                        <th style="width: 100px;">Recipients</th>
                        <th style="width: 150px;">Created</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($newsletters as $newsletter)
                    <tr>
                        <td>
                            <div class="fw-medium">{{ $newsletter->subject }}</div>
                            <div class="small text-muted">{{ $newsletter->content_snippet }}</div>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($newsletter->status) {
                                    'sent' => 'success',
                                    'scheduled' => 'warning',
                                    'failed' => 'danger',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($newsletter->status) }}</span>
                        </td>
                        <td>
                            @if($newsletter->sent_at)
                                {{ $newsletter->sent_at->format('M d, Y h:i A') }}
                            @elseif($newsletter->scheduled_at)
                                <span class="text-warning">{{ $newsletter->scheduled_at->format('M d, Y h:i A') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $newsletter->recipients_count ?? 0 }}</td>
                        <td>{{ $newsletter->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if($newsletter->isDraft())
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sendModal{{ $newsletter->id }}" title="Send">
                                        <i class="bi bi-send"></i>
                                    </button>
                                @else
                                    <a href="{{ route('admin.marketing.newsletters.preview', $newsletter->id) }}" class="btn btn-outline-secondary" title="Preview">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.marketing.newsletters.edit', $newsletter->id) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.marketing.newsletters.duplicate', $newsletter->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary" title="Duplicate">
                                        <i class="bi bi-copy"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $newsletter->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            
                            <!-- Delete Form (hidden) -->
                            <form id="deleteForm{{ $newsletter->id }}" action="{{ route('admin.marketing.newsletters.destroy', $newsletter->id) }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                            
                            <!-- Send Modal -->
                            @if($newsletter->isDraft())
                            <div class="modal fade" id="sendModal{{ $newsletter->id }}" tabindex="-1" aria-hidden="true">
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
                                                    <label for="recipients_type{{ $newsletter->id }}" class="form-label">Recipients</label>
                                                    <select name="recipients_type" id="recipients_type{{ $newsletter->id }}" class="form-select" required onchange="updateRecipientCount({{ $newsletter->id }}, this.value)">
                                                        <option value="all">All (Subscribers + Customers)</option>
                                                        <option value="subscribers">Newsletter Subscribers Only</option>
                                                        <option value="users">Registered Customers Only</option>
                                                    </select>
                                                    <div class="form-text">This will send to: <span id="recipientCount{{ $newsletter->id }}">-</span> recipients</div>
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
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-envelope text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No newsletters found</p>
                            <a href="{{ route('admin.marketing.newsletters.create') }}" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-plus-lg me-1"></i> Create First Newsletter
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($newsletters->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $newsletters->firstItem() }} - {{ $newsletters->lastItem() }} of {{ $newsletters->total() }} newsletters
            </div>
            <div>
                {{ $newsletters->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Filter form submission on change
    document.getElementById('filterStatus').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    // Delete confirmation
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this newsletter?')) {
            document.getElementById('deleteForm' + id).submit();
        }
    }

    // Update recipient count on modal
    function updateRecipientCount(newsletterId, recipientsType) {
        fetch(`{{ route('admin.marketing.newsletters.recipient-count') }}?recipients_type=${recipientsType}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('recipientCount' + newsletterId).textContent = data.count;
        });
    }

    // Initialize recipient count on page load for draft newsletters
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($newsletters->where('status', 'draft') as $newsletter)
        updateRecipientCount({{ $newsletter->id }}, 'all');
        @endforeach
    });
</script>
@endpush
