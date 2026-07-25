@php
    $search = request('search');
@endphp
@forelse($tickets as $ticket)
@php
    $isMatch = $search && (
        stripos($ticket->ticket_number, $search) !== false ||
        stripos($ticket->subject, $search) !== false ||
        stripos($ticket->user->name ?? '', $search) !== false ||
        stripos($ticket->user->email ?? '', $search) !== false
    );
@endphp
<tr class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input ticket-checkbox" value="{{ $ticket->id }}">
    </td>
    <td>
        <div class="fw-medium">{{ $ticket->ticket_number }}</div>
        <div class="small text-muted text-truncate" style="max-width: 200px;">
            {{ $ticket->subject }}
        </div>
    </td>
    <td>
        <div class="fw-medium">{{ $ticket->user->name ?? 'N/A' }}</div>
        <div class="small text-muted">{{ $ticket->user->email ?? '' }}</div>
    </td>
    <td>
        <span class="badge bg-light text-dark">{{ ucfirst($ticket->category) }}</span>
    </td>
    <td>
        <span class="badge {{ $ticket->getPriorityBadgeClass() }}">
            {{ ucfirst($ticket->priority) }}
        </span>
    </td>
    <td>
        <span class="badge {{ $ticket->getStatusBadgeClass() }}">
            {{ ucfirst($ticket->status) }}
        </span>
    </td>
    <td>
        @if($ticket->assignedTo)
            <div class="small">{{ $ticket->assignedTo->name }}</div>
        @else
            <span class="text-muted small">Unassigned</span>
        @endif
    </td>
    <td>
        <div class="small">{{ $ticket->created_at->format('d M Y') }}</div>
        <div class="small text-muted">{{ $ticket->created_at->format('h:i A') }}</div>
    </td>
    <td>
        <a href="{{ route('admin.support.tickets.show', $ticket->id) }}" 
           class="btn btn-sm btn-outline-primary" title="View">
            <i class="bi bi-eye"></i>
        </a>
        <button type="button" class="btn btn-sm btn-outline-danger" 
                onclick="deleteTicket({{ $ticket->id }})" title="Delete">
            <i class="bi bi-trash"></i>
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-5">
        <i class="bi bi-ticket-detailed text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No tickets found</p>
    </td>
</tr>
@endforelse
