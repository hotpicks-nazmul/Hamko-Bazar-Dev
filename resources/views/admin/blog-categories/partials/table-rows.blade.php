@php
    $search = request('search');
@endphp
@forelse($categories as $category)
@php
    $isMatch = $search && (
        stripos($category->name, $search) !== false || 
        stripos($category->slug, $search) !== false
    );
@endphp
<tr data-id="{{ $category->id }}" class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input item-checkbox" value="{{ $category->id }}" onchange="toggleItem(this)">
    </td>
    <td>
        <div class="d-flex align-items-center">
            @if($category->image)
            @php
                $imageUrl = $category->image;
                if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                    $imageUrl = '/storage/' . $imageUrl;
                }
            @endphp
            <img src="{{ $imageUrl }}" alt="{{ $category->name }}" 
                 class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
            @else
            <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                <i class="bi bi-folder text-white"></i>
            </div>
            @endif
            <div>
                <div class="fw-medium">{{ $category->name }}</div>
                <small class="text-muted">{{ $category->slug }}</small>
            </div>
        </div>
    </td>
    <td>
        <span class="badge bg-info">{{ $category->blogs_count }}</span>
    </td>
    <td class="status-badge" data-id="{{ $category->id }}">
        {!! $category->status_badge !!}
    </td>
    <td>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.blog-categories.edit', $category->id) }}" 
               class="btn btn-sm btn-outline-primary" 
               title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" 
                    class="btn btn-sm btn-outline-{{ $category->status === 'active' ? 'warning' : 'success' }}" 
                    onclick="toggleStatus('{{ route('admin.blog-categories.toggle-status', $category->id) }}', {{ $category->id }})"
                    title="{{ $category->status === 'active' ? 'Deactivate' : 'Activate' }}">
                <i class="bi bi-{{ $category->status === 'active' ? 'eye-slash' : 'eye' }}"></i>
            </button>
            <button type="button" 
                    class="btn btn-sm btn-outline-danger" 
                    onclick="deleteCategory('{{ route('admin.blog-categories.destroy', $category->id) }}')"
                    title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="text-center py-5">
        <i class="bi bi-folder text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No blog categories found</p>
        <a href="{{ route('admin.blog-categories.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First Category
        </a>
    </td>
</tr>
@endforelse
