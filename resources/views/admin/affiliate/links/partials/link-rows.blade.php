@php
    $search = request('search');
@endphp
@forelse($links as $link)
@php
    $isMatch = $search && (
        stripos($link->name, $search) !== false ||
        stripos($link->affiliate_code, $search) !== false ||
        ($link->description && stripos($link->description, $search) !== false)
    );
@endphp
<tr class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>{{ $link->id }}</td>
    <td>
        <div class="fw-semibold">{{ $link->name }}</div>
        @if($link->description)
        <small class="text-muted">{{ Str::limit($link->description, 50) }}</small>
        @endif
    </td>
    <td>{{ $link->affiliate->user->name ?? '-' }}</td>
    <td>{{ $link->product->name ?? '-' }}</td>
    <td>
        <div class="input-group input-group-sm">
            <input type="text" class="form-control form-control-sm" value="{{ $link->full_url }}" readonly id="link{{ $link->id }}">
            <button class="btn btn-outline-secondary" type="button" onclick="copyLink('link{{ $link->id }}')">
                <i class="bi bi-clipboard"></i>
            </button>
        </div>
    </td>
    <td class="text-center">{{ number_format($link->clicks) }}</td>
    <td class="text-center">{{ number_format($link->conversions) }}</td>
    <td>
        @if($link->status === 'active')
        <span class="badge bg-success">Active</span>
        @else
        <span class="badge bg-danger">Inactive</span>
        @endif
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.affiliate.links.edit', $link->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.affiliate.links.destroy', $link->id) }}" method="POST" class="d-flex" onsubmit="return confirm('Are you sure you want to delete this link?')">
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
    <td colspan="9" class="text-center py-5">
        <i class="bi bi-link text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No affiliate links found</p>
        <a href="{{ route('admin.affiliate.links.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First Link
        </a>
    </td>
</tr>
@endforelse
