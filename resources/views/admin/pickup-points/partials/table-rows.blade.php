@forelse($pickupPoints as $index => $pickupPoint)
<tr>
    <td>{{ $pickupPoints->firstItem() + $index }}</td>
    <td>
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                <i class="bi bi-shop text-primary"></i>
            </div>
            <div>
                <div class="fw-semibold">{{ $pickupPoint->name }}</div>
                @if($pickupPoint->code)
                    <small class="text-muted">Code: {{ $pickupPoint->code }}</small>
                @endif
            </div>
        </div>
    </td>
    <td>
        <div><i class="bi bi-telephone me-1 text-muted"></i>{{ $pickupPoint->phone }}</div>
        @if($pickupPoint->email)
            <small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $pickupPoint->email }}</small>
        @endif
    </td>
    <td>
        <div class="text-truncate" style="max-width: 250px;" title="{{ $pickupPoint->formatted_address }}">
            {{ $pickupPoint->formatted_address }}
        </div>
    </td>
    <td>
        <form action="{{ route('admin.pickup-points.toggle-status', $pickupPoint->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm {{ $pickupPoint->is_active ? 'btn-success' : 'btn-secondary' }}">
                <i class="bi bi-{{ $pickupPoint->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>
                {{ $pickupPoint->status_text }}
            </button>
        </form>
    </td>
    <td>
        <div class="btn-group" role="group">
            <a href="{{ route('admin.pickup-points.show', $pickupPoint->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.pickup-points.edit', $pickupPoint->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.pickup-points.destroy', $pickupPoint->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this pick-up point?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center py-5">
        <i class="bi bi-shop fs-1 d-block mb-2 text-muted"></i>
        <p class="mb-0 text-muted">No pick-up points found.</p>
        <a href="{{ route('admin.pickup-points.create') }}" class="btn btn-primary btn-sm mt-2">
            <i class="bi bi-plus-lg me-1"></i> Add Pick-up Point
        </a>
    </td>
</tr>
@endforelse
