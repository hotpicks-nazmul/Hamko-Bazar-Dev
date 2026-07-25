@forelse($requests as $request)
@php
    $search = request('search');
    $isMatch = $search && (
        stripos($request->user->name ?? '', $search) !== false ||
        stripos($request->user->email ?? '', $search) !== false ||
        stripos($request->website ?? '', $search) !== false
    );
@endphp
<tr class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $request->id }}">
    </td>
    <td>{{ $request->id }}</td>
    <td>{{ $request->user->name ?? '-' }}</td>
    <td>{{ $request->user->email ?? '-' }}</td>
    <td>
        @if($request->website)
        <a href="{{ $request->website }}" target="_blank" class="text-decoration-none">
            {{ Str::limit($request->website, 30) }}
        </a>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td>{{ Str::limit($request->promotion_methods ?? '-', 50) }}</td>
    <td>{{ $request->requested_at->format('M d, Y H:i') }}</td>
    <td>
        @if($request->status === 'pending')
        <span class="badge bg-warning">Pending</span>
        @elseif($request->status === 'approved')
        <span class="badge bg-success">Approved</span>
        @else
        <span class="badge bg-danger">Rejected</span>
        @endif
    </td>
    <td>
        @if($request->status === 'pending')
        <div class="btn-group">
            <form action="{{ route('admin.affiliate.requests.approve', $request->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-success" title="Approve" onclick="return confirm('Are you sure you want to approve this request?')">
                    <i class="bi bi-check-circle"></i>
                </button>
            </form>
            <form action="{{ route('admin.affiliate.requests.reject', $request->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject" onclick="return confirm('Are you sure you want to reject this request?')">
                    <i class="bi bi-x-circle"></i>
                </button>
            </form>
        </div>
        @else
        <span class="text-muted">Processed</span>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-0 mt-2">No requests found</p>
    </td>
</tr>
@endforelse
