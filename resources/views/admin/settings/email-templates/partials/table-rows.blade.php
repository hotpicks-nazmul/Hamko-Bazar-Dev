@forelse($templates as $template)
<tr>
    <td>
        <div class="fw-medium">{{ $template->subject }}</div>
        <small class="text-muted">{{ $template->slug }}</small>
    </td>
    <td>
        <span class="badge bg-light text-dark">{{ $template->event_label }}</span>
    </td>
    <td>
        @switch($template->recipient_type)
            @case('customer')
                <span class="badge bg-primary"><i class="bi bi-person me-1"></i>Customer</span>
                @break
            @case('seller')
                <span class="badge bg-info"><i class="bi bi-shop me-1"></i>Seller</span>
                @break
            @case('admin')
                <span class="badge bg-warning text-dark"><i class="bi bi-shield me-1"></i>Admin</span>
                @break
            @default
                <span class="badge bg-secondary">{{ ucfirst($template->recipient_type) }}</span>
        @endswitch
    </td>
    <td>
        @if($template->is_active)
            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
        @else
            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Inactive</span>
        @endif
    </td>
    <td>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.settings.email-templates.edit', $template->id) }}" 
               class="btn btn-sm btn-outline-primary" 
               title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.settings.email-templates.toggle-status', $template->id) }}" 
                  method="POST" 
                  class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" 
                        class="btn btn-sm {{ $template->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                        title="{{ $template->is_active ? 'Deactivate' : 'Activate' }}">
                    <i class="bi {{ $template->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="text-center py-5">
        <i class="bi bi-envelope-paper text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No email templates found</p>
    </td>
</tr>
@endforelse
