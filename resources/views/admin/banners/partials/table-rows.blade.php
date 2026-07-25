@php
    $search = request('search');
@endphp
@forelse($banners as $banner)
@php
    $isMatch = $search && (
        stripos($banner->title, $search) !== false ||
        stripos($banner->description ?? '', $search) !== false
    );
@endphp
<tr class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input banner-checkbox" value="{{ $banner->id }}">
    </td>
    <td>
        @php
            $imageUrl = $banner->thumbnail ?? $banner->image;
            if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                $imageUrl = '/storage/' . $imageUrl;
            }
        @endphp
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $banner->title }}" class="banner-thumbnail">
        @else
            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 40px;">
                <i class="bi bi-image text-white"></i>
            </div>
        @endif
    </td>
    <td>
        <div class="fw-medium">{{ $banner->title }}</div>
        @if($banner->description)
            <div class="small text-muted text-truncate" style="max-width: 200px;">{{ $banner->description }}</div>
        @endif
    </td>
    <td>
        <span class="badge bg-secondary">
            {{ \App\Models\Banner::getPositionOptions()[$banner->position] ?? $banner->position }}
        </span>
    </td>
    <td>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox"
                   {{ $banner->is_active ? 'checked' : '' }}
                   onchange="toggleBannerStatus(this, '{{ route('admin.banners.toggle', $banner->id) }}')">
        </div>
    </td>
    <td>
        <span class="badge bg-primary">{{ $banner->sort_order }}</span>
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                    onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this banner?')) { document.getElementById('delete-form-{{ $banner->id }}').submit(); }">
                <i class="bi bi-trash"></i>
            </button>
            <form id="delete-form-{{ $banner->id }}" method="POST" action="{{ route('admin.banners.destroy', $banner->id) }}" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-5">
        <i class="bi bi-card-image text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No banners found</p>
        <a href="{{ route('admin.banners.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First Banner
        </a>
    </td>
</tr>
@endforelse
