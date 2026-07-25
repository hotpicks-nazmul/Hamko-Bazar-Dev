@forelse($banners as $banner)
<tr data-id="{{ $banner->id }}">
    <td>
        <input type="checkbox" data-id="{{ $banner->id }}" onchange="toggleBanner('{{ $banner->id }}')">
    </td>
    <td>
        @if($banner->image)
        <img src="{{ $banner->image_url }}" alt="{{ $banner->name }}" style="width: 80px; height: 50px; object-fit: cover;" class="rounded">
        @else
        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 80px; height: 50px;">
            <i class="bi bi-image text-white"></i>
        </div>
        @endif
    </td>
    <td>{{ $banner->name }}</td>
    <td>
        @if($banner->width && $banner->height)
        <span class="badge bg-info">{{ $banner->width }}x{{ $banner->height }}</span>
        @elseif($banner->size)
        <span class="badge bg-secondary">{{ $banner->size }}</span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        @if($banner->affiliate)
        <a href="{{ route('admin.affiliate.users.show', $banner->affiliate->id) }}">
            {{ $banner->affiliate->user->name ?? 'Unknown' }}
        </a>
        @else
        <span class="text-muted">General</span>
        @endif
    </td>
    <td>{{ number_format($banner->clicks) }}</td>
    <td>
        @if($banner->status === 'active')
        <span class="badge bg-success">Active</span>
        @else
        <span class="badge bg-secondary">Inactive</span>
        @endif
    </td>
    <td>{{ $banner->created_at->format('M d, Y') }}</td>
    <td>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.affiliate.banners.edit', $banner->id) }}" class="btn btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.affiliate.banners.destroy', $banner->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this banner?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-5">
        <i class="bi bi-images text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No banners found. Create your first banner to get started.</p>
        <a href="{{ route('admin.affiliate.banners.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i>Add First Banner
        </a>
    </td>
</tr>
@endforelse