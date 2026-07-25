@forelse($staffs as $staff)
    <tr>
        <td>
            <input type="checkbox" class="form-check-input staff-checkbox" value="{{ $staff->id }}">
        </td>
        <td>
            <div class="d-flex align-items-center">
                @php
                    $avatarUrl = $staff->avatar;
                    if ($avatarUrl && !str_starts_with($avatarUrl, '/storage/') && !str_starts_with($avatarUrl, 'http')) {
                        $avatarUrl = '/storage/' . $avatarUrl;
                    }
                @endphp
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="{{ $staff->name }}" class="staff-avatar me-2">
                @else
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                        <i class="bi bi-person text-white"></i>
                    </div>
                @endif
                <div>
                    <div class="fw-medium">{{ $staff->name }}</div>
                    <div class="small text-muted">{{ $staff->email }}</div>
                    @if($staff->phone)
                        <div class="small text-muted">{{ $staff->phone }}</div>
                    @endif
                </div>
            </div>
        </td>
        <td>
            {{ $staff->designation ?? 'N/A' }}
            @if($staff->is_super_admin)
                <span class="badge bg-warning text-dark ms-1">Super Admin</span>
            @endif
        </td>
        <td>
            @if($staff->warehouse)
                <span class="badge bg-info">{{ $staff->warehouse->name }}</span>
            @else
                <span class="text-muted">Not Assigned</span>
            @endif
        </td>
        <td>
            @if($staff->status === 'active')
                <span class="badge bg-success">Active</span>
            @elseif($staff->status === 'inactive')
                <span class="badge bg-secondary">Inactive</span>
            @elseif($staff->status === 'banned')
                <span class="badge bg-danger">Banned</span>
            @endif
        </td>
        <td>
            {{ $staff->created_at->format('M d, Y') }}
        </td>
        <td>
            <div class="d-flex gap-1">
                <a href="{{ route('admin.staffs.edit', $staff->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
                <a href="#" class="btn btn-sm btn-outline-danger" title="Delete" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this staff member?')) { document.getElementById('delete-form-{{ $staff->id }}').submit(); }">
                    <i class="bi bi-trash"></i>
                </a>
            </div>
            <form id="delete-form-{{ $staff->id }}" action="{{ route('admin.staffs.destroy', $staff->id) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5">
            <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mb-2 mt-2">No staff members found</p>
            <a href="{{ route('admin.staffs.create') }}" class="btn btn-sm btn-primary mt-1">
                <i class="bi bi-plus-lg me-1"></i> Add First Staff
            </a>
        </td>
    </tr>
@endforelse
