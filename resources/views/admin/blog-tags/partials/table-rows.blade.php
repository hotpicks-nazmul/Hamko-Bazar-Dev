@php
    $search = request('search');
@endphp
@forelse($tags as $tag)
@php
    $isMatch = $search && (
        stripos($tag->name, $search) !== false || 
        stripos($tag->slug, $search) !== false
    );
@endphp
<tr data-id="{{ $tag->id }}" class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input item-checkbox" value="{{ $tag->id }}">
    </td>
    <td>
        <div class="d-flex align-items-center">
            <div class="bg-primary rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                <i class="bi bi-tag text-white"></i>
            </div>
            <div>
                <div class="fw-medium">{{ $tag->name }}</div>
                <small class="text-muted">{{ $tag->slug }}</small>
            </div>
        </div>
    </td>
    <td>
        <span class="badge bg-info">{{ $tag->blogs_count }}</span>
    </td>
    <td class="status-badge" data-id="{{ $tag->id }}">
        {!! $tag->status_badge !!}
    </td>
    <td>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.blog-tags.edit', $tag->id) }}" 
               class="btn btn-sm btn-outline-primary" 
               title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" 
                    class="btn btn-sm btn-outline-{{ $tag->status === 'active' ? 'warning' : 'success' }}" 
                    onclick="toggleStatus('{{ route('admin.blog-tags.toggle-status', $tag->id) }}', {{ $tag->id }})"
                    title="{{ $tag->status === 'active' ? 'Deactivate' : 'Activate' }}">
                <i class="bi bi-{{ $tag->status === 'active' ? 'eye-slash' : 'eye' }}"></i>
            </button>
            <button type="button" 
                    class="btn btn-sm btn-outline-danger" 
                    onclick="deleteTag('{{ route('admin.blog-tags.destroy', $tag->id) }}')"
                    title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="text-center py-5">
        <i class="bi bi-tags text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No blog tags found</p>
        <a href="{{ route('admin.blog-tags.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First Tag
        </a>
    </td>
</tr>
@endforelse
