@forelse($affiliates as $affiliate)
<tr>
    <td style="width: 50px;">{{ $affiliate->id }}</td>
    <td>
        <div class="d-flex align-items-center">
            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                <i class="bi bi-person text-muted"></i>
            </div>
            <div>
                <div class="fw-medium">{{ $affiliate->user->name ?? '-' }}</div>
                <div class="small text-muted">{{ $affiliate->user->email ?? '-' }}</div>
            </div>
        </div>
    </td>
    <td><code class="small">{{ $affiliate->affiliate_code }}</code></td>
    <td><span class="badge bg-info">{{ $affiliate->commission_rate }}%</span></td>
    <td>${{ number_format($affiliate->balance, 2) }}</td>
    <td>${{ number_format($affiliate->total_earnings, 2) }}</td>
    <td>
        @if($affiliate->status === 'approved')
        <span class="badge bg-success">Approved</span>
        @elseif($affiliate->status === 'pending')
        <span class="badge bg-warning">Pending</span>
        @elseif($affiliate->status === 'suspended')
        <span class="badge bg-danger">Suspended</span>
        @else
        <span class="badge bg-secondary">{{ ucfirst($affiliate->status) }}</span>
        @endif
    </td>
    <td style="width: 150px;">
        <a href="{{ route('admin.affiliate.users.show', $affiliate->id) }}" class="btn btn-sm btn-outline-info" title="View">
            <i class="bi bi-eye"></i>
        </a>
        @if($affiliate->status === 'pending')
        <form action="{{ route('admin.affiliate.users.approve', $affiliate->id) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                <i class="bi bi-check-circle"></i>
            </button>
        </form>
        @endif
        @if($affiliate->status === 'approved')
        <form action="{{ route('admin.affiliate.users.suspend', $affiliate->id) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-warning" title="Suspend" onclick="return confirm('Are you sure you want to suspend this affiliate?')">
                <i class="bi bi-pause-circle"></i>
            </button>
        </form>
        @endif
        <form action="{{ route('admin.affiliate.users.destroy', $affiliate->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this affiliate?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No affiliate users found</p>
    </td>
</tr>
@endforelse
