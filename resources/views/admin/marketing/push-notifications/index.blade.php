@extends('admin.layouts.app')

@section('title', 'Push Notifications')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-bell"></i></div>
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
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Scheduled</span>
            <span class="stat-card-value">{{ number_format($stats['scheduled'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Sent</span>
            <span class="stat-card-value">{{ number_format($stats['sent'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Failed</span>
            <span class="stat-card-value">{{ number_format($stats['failed'] ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Push Notifications</h4>
    <a href="{{ route('admin.marketing.push-notifications.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Create Notification
    </a>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
                <form method="GET" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Search Input -->
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <label class="form-label small text-muted">Search</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" id="liveSearch" class="form-control" 
                                       placeholder="Search title..." value="{{ request('search') }}">
                            </div>
                        </div>
                        
                        <!-- Status Filter -->
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label small text-muted">Status</label>
                            <select name="status" id="filterStatus" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>

                        <!-- Date From -->
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label small text-muted">Date From</label>
                            <input type="date" name="date_from" class="form-control form-select-sm" value="{{ request('date_from') }}">
                        </div>

                        <!-- Date To -->
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label small text-muted">Date To</label>
                            <input type="date" name="date_to" class="form-control form-select-sm" value="{{ request('date_to') }}">
                        </div>
                        
                        <!-- Reset Button -->
                        <div class="col-lg-1 col-md-2 col-sm-6">
                            <a href="{{ route('admin.marketing.push-notifications.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                                <i class="bi bi-x-lg me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Notification</th>
                                <th style="width: 120px;">Target</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 100px;">Recipients</th>
                                <th style="width: 100px;">Delivered</th>
                                <th style="width: 100px;">Clicked</th>
                                <th style="width: 150px;">Created</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($notifications as $notification)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($notification->image)
                                        @php
                                            $imageUrl = $notification->image;
                                            if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                                $imageUrl = '/storage/' . $imageUrl;
                                            }
                                        @endphp
                                        <img src="{{ $imageUrl }}" alt="" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                            <i class="bi bi-bell text-muted"></i>
                                        </div>
                                        @endif
                                        <div>
                                            <div class="fw-medium">{{ $notification->title }}</div>
                                            <div class="text-muted small text-truncate" style="max-width: 250px;">{{ Str::limit($notification->message, 50) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $notification->target_type_label }}</span>
                                </td>
                                <td>
                                    {!! $notification->status_badge !!}
                                </td>
                                <td>{{ $notification->recipients_count }}</td>
                                <td>{{ $notification->delivered_count }}</td>
                                <td>{{ $notification->clicked_count }}</td>
                                <td>
                                    <div class="text-muted small">{{ $notification->created_at->format('M d, Y') }}</div>
                                    <div class="text-muted small">{{ $notification->creator->name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if(in_array($notification->status, ['draft', 'failed']))
                                        <a href="{{ route('admin.marketing.push-notifications.edit', $notification->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.marketing.push-notifications.send', $notification->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Send Now" 
                                                    onclick="return confirm('Send this notification now?')">
                                                <i class="bi bi-send"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('admin.marketing.push-notifications.duplicate', $notification->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Duplicate">
                                                <i class="bi bi-copy"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.marketing.push-notifications.destroy', $notification->id) }}" method="POST" class="d-inline" id="deleteForm{{ $notification->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                    onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this notification?')) { document.getElementById('deleteForm{{ $notification->id }}').submit(); }">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-bell text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mb-2 mt-2">No push notifications found</p>
                                    <a href="{{ route('admin.marketing.push-notifications.create') }}" class="btn btn-sm btn-primary mt-1">
                                        <i class="bi bi-plus-lg me-1"></i> Create First Notification
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($notifications->hasPages())
                <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">
                        Showing {{ $notifications->firstItem() }} - {{ $notifications->lastItem() }} of {{ $notifications->total() }} notifications
                    </div>
                    <div>
                        {{ $notifications->appends(request()->query())->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
@endsection

@push('scripts')
<script>
    // Filter form auto-submit
    const filterForm = document.getElementById('filterForm');
    const liveSearch = document.getElementById('liveSearch');
    const filterStatus = document.getElementById('filterStatus');

    // Live search with debounce
    let searchTimeout;
    liveSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterForm.submit();
        }, 300);
    });

    // Filter dropdowns trigger submit on change
    filterStatus.addEventListener('change', function() {
        filterForm.submit();
    });

    // Date inputs trigger submit on change
    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.addEventListener('change', function() {
            filterForm.submit();
        });
    });
</script>
@endpush
