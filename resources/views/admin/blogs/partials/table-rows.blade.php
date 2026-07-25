@forelse($blogs as $blog)
<tr>
    <td>
        @php
            $imageUrl = $blog->featured_image;
            if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                $imageUrl = '/storage/' . $imageUrl;
            }
        @endphp
        @if($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $blog->title }}" class="rounded" style="width: 50px; height: 40px; object-fit: cover;">
        @else
        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 40px;">
            <i class="bi bi-image text-white"></i>
        </div>
        @endif
    </td>
    <td>
        <div class="fw-medium">{{ $blog->title }}</div>
        @if($blog->slug)
        <small class="text-muted">/{{ $blog->slug }}</small>
        @endif
    </td>
    <td>{{ $blog->category->name ?? 'Uncategorized' }}</td>
    <td>{{ $blog->author->name ?? 'N/A' }}</td>
    <td>
        @php $isPublished = $blog->status === 'published' && $blog->published_at && $blog->published_at->isPast(); @endphp
        <span class="badge {{ $isPublished ? 'bg-success' : 'bg-secondary' }}">
            {{ $isPublished ? 'Published' : 'Draft' }}
        </span>
    </td>
    <td>{{ $blog->published_at ? $blog->published_at->format('d M, Y') : 'Not published' }}</td>
    <td>
        <a href="{{ route('admin.blogs.edit', $blog->slug) }}" class="btn btn-sm btn-outline-primary" title="Edit">
            <i class="bi bi-pencil"></i>
        </a>
        <form action="{{ route('admin.blogs.destroy', $blog->slug) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this post?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-5">
        <i class="bi bi-newspaper text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No blog posts found</p>
        <a href="{{ route('admin.blogs.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First Post
        </a>
    </td>
</tr>
@endforelse
