@forelse($logs as $index => $log)
<tr>
    <td>
        <input type="checkbox" class="form-check-input log-checkbox" value="{{ $log->id }}" onchange="toggleItem(this)">
    </td>
    <td>
        @if($log->log_name === 'admin')
        <span class="badge bg-success">
            <i class="bi bi-person-badge me-1"></i>Admin
        </span>
        @elseif($log->log_name === 'customer')
        <span class="badge bg-info">
            <i class="bi bi-person me-1"></i>Customer
        </span>
        @elseif($log->log_name === 'system')
        <span class="badge bg-warning">
            <i class="bi bi-gear me-1"></i>System
        </span>
        @else
        <span class="badge bg-secondary">
            {{ $log->log_name }}
        </span>
        @endif
    </td>
    <td>
        <div class="fw-medium">{{ $log->description }}</div>
        @if($log->subject_type)
        <div class="text-muted small">
            <i class="bi bi-link-45deg me-1"></i>
            {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
        </div>
        @endif
        @if($log->properties && isset($log->properties['attributes']))
        <div class="text-muted small mt-1">
            <span class="text-info">{{ json_encode($log->properties['attributes']) }}</span>
        </div>
        @endif
    </td>
    <td>
        @if($log->causer)
        <div class="d-flex align-items-center">
            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <span class="text-primary small">{{ substr($log->causer->name ?? 'U', 0, 1) }}</span>
            </div>
            <div>
                <div class="small fw-medium">{{ $log->causer->name ?? 'Unknown' }}</div>
                <div class="text-muted small">{{ $log->causer->email ?? '' }}</div>
            </div>
        </div>
        @else
        <span class="text-muted">
            <i class="bi bi-gear me-1"></i>System
        </span>
        @endif
    </td>
    <td>
        <span class="text-muted small">{{ $log->ip_address ?? 'N/A' }}</span>
    </td>
    <td>
        <span class="text-muted small">
            {{ $log->created_at->format('d M Y, h:i A') }}
        </span>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center py-5">
        <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No activity logs found</p>
        <p class="text-muted small">Activity will be logged here when users perform actions</p>
    </td>
</tr>
@endforelse
