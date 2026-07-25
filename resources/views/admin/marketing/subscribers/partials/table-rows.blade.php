@forelse($subscribers as $subscriber)
<tr>
    <td>
        <div class="fw-medium">{{ $subscriber->email }}</div>
    </td>
    <td>{{ $subscriber->name ?? '-' }}</td>
    <td>
        @php
            $badgeClass = $subscriber->isActive() ? 'success' : 'danger';
        @endphp
        <span class="badge bg-{{ $badgeClass }}">{{ $subscriber->isActive() ? 'Active' : 'Unsubscribed' }}</span>
    </td>
    <td>{{ $subscriber->created_at->format('M d, Y') }}</td>
    <td>
        <div class="btn-group btn-group-sm">
            @if($subscriber->isActive())
                <form action="{{ route('admin.marketing.subscribers.unsubscribe', $subscriber->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning" title="Unsubscribe">
                        <i class="bi bi-bell-slash"></i>
                    </button>
                </form>
            @else
                <form action="{{ route('admin.marketing.subscribers.resubscribe', $subscriber->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-success" title="Resubscribe">
                        <i class="bi bi-bell"></i>
                    </button>
                </form>
            @endif
            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $subscriber->id }})" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        
        <!-- Delete Form (hidden) -->
        <form id="deleteForm{{ $subscriber->id }}" action="{{ route('admin.marketing.subscribers.destroy', $subscriber->id) }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="text-center py-5">
        <i class="bi bi-person-plus text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No subscribers found</p>
        <button type="button" class="btn btn-sm btn-primary mt-1" data-bs-toggle="modal" data-bs-target="#addSubscriberModal">
            <i class="bi bi-plus-lg me-1"></i> Add First Subscriber
        </button>
    </td>
</tr>
@endforelse
