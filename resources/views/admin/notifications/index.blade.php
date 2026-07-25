@extends('admin.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Notifications</h4>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="createTestNotification()">
            <i class="bi bi-plus-lg me-1"></i> Add Test Notification
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllNotifications()">
            <i class="bi bi-trash me-1"></i> Clear All
        </button>
    </div>
</div>

<!-- Filter Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Filter by Type</label>
                    <select name="filter" id="filterType" class="form-select form-select-sm">
                        <option value="">All Notifications</option>
                        <option value="unread" {{ request('filter') === 'unread' ? 'selected' : '' }}>Unread</option>
                        <option value="read" {{ request('filter') === 'read' ? 'selected' : '' }}>Read</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-bell"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total</span>
            <span class="stat-card-value">{{ $notifications->total() }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-envelope"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Unread</span>
            <span class="stat-card-value">{{ $notifications->where('is_read', false)->count() }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Read</span>
            <span class="stat-card-value">{{ $notifications->where('is_read', true)->count() }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-check-all"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Actions</span>
            <button class="btn btn-sm btn-outline-primary" onclick="markAllNotificationsAsRead()">
                Mark All Read
            </button>
        </div>
    </div>
</div>

<!-- Notifications Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="notificationsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                        </th>
                        <th style="width: 80px;">Type</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th style="width: 120px;">Time</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                    <tr class="{{ $notification->is_read ? '' : 'table-primary' }}">
                        <td>
                            <input type="checkbox" class="form-check-input notification-checkbox" value="{{ $notification->id }}">
                        </td>
                        <td>
                            @php
                                $typeColors = [
                                    'order' => 'primary',
                                    'review' => 'warning',
                                    'stock' => 'danger',
                                    'refund' => 'info',
                                    'customer' => 'success',
                                    'support' => 'secondary',
                                    'system' => 'dark',
                                    'product' => 'primary',
                                ];
                                $typeIcons = [
                                    'order' => 'bi-bag',
                                    'review' => 'bi-star',
                                    'stock' => 'bi-box',
                                    'refund' => 'bi-arrow-return-left',
                                    'customer' => 'bi-person-plus',
                                    'support' => 'bi-headset',
                                    'system' => 'bi-gear',
                                    'product' => 'bi-box-seam',
                                ];
                                $color = $typeColors[$notification->type] ?? 'primary';
                                $icon = $typeIcons[$notification->type] ?? 'bi-bell';
                            @endphp
                            <div class="bg-{{ $color }} bg-opacity-10 rounded p-2 text-center">
                                <i class="bi {{ $icon }} text-{{ $color }}"></i>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $notification->title }}</strong>
                        </td>
                        <td>
                            <span class="text-muted">{{ $notification->message }}</span>
                        </td>
                        <td>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            @if($notification->is_read)
                            <span class="badge bg-success">Read</span>
                            @else
                            <span class="badge bg-danger">Unread</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if(!$notification->is_read)
                                <button class="btn btn-sm btn-outline-success" onclick="markAsRead({{ $notification->id }})" title="Mark as read">
                                    <i class="bi bi-check"></i>
                                </button>
                                @endif
                                <a href="{{ $notification->link ?? '#' }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification({{ $notification->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No notifications found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
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

@push('scripts')
<script>
    // Filter form submit
    document.getElementById('filterType').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    
    // Mark as read
    function markAsRead(id) {
        fetch('{{ route('admin.notifications.mark-read') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ids: [id] })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    // Mark all as read
    function markAllNotificationsAsRead() {
        fetch('{{ route('admin.notifications.mark-all-read') }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    // Delete notification
    function deleteNotification(id) {
        if (confirm('Are you sure you want to delete this notification?')) {
            fetch(`/admin/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }
    
    // Clear all notifications
    function clearAllNotifications() {
        if (confirm('Are you sure you want to delete all notifications?')) {
            fetch('{{ route('admin.notifications.clear-all') }}', {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }
    
    // Create test notification
    function createTestNotification() {
        fetch('{{ route('admin.notifications.test') }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    // Select all checkbox
    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.notification-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
</script>
@endpush
@endsection
