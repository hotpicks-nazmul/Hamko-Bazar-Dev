@php
    $hasChildren = $category->children->count() > 0;
    $indent = str_repeat('<span class="tree-indent"></span>', $depth);
    $search = request('search');
    $isMatch = $search && (stripos($category->name, $search) !== false || stripos($category->slug, $search) !== false);
@endphp

<tr data-id="{{ $category->id }}" data-parent="{{ $category->parent_id }}" class="{{ $depth > 0 ? 'children-row' : '' }} {{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input category-checkbox" value="{{ $category->id }}" onchange="updateBulkActions()">
    </td>
    <td>
        @if($category->image)
            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
        @else
            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-folder text-white"></i>
            </div>
        @endif
    </td>
    <td>
        {!! $indent !!}
        @if($hasChildren)
            <span class="tree-toggle" onclick="toggleChildren({{ $category->id }})">
                <i class="bi bi-chevron-down"></i>
            </span>
        @else
            <span class="tree-toggle"></span>
        @endif
        <a href="{{ route('admin.categories.edit', $category->id) }}" class="text-decoration-none ms-1">
            {{ $category->name }}
        </a>
    </td>
    <td>
        <span class="badge {{ $category->products_count > 0 ? 'bg-info' : 'bg-light text-dark' }}">
            {{ $category->products_count }}
        </span>
    </td>
    <td>
        <button type="button" class="btn btn-sm status-toggle {{ $category->status === 'active' ? 'btn-success' : 'btn-outline-secondary' }}" data-id="{{ $category->id }}">
            {{ ucfirst($category->status) }}
        </button>
    </td>
    <td>
        <button type="button" class="btn btn-sm menu-toggle {{ $category->show_in_menu ? 'btn-success' : 'btn-outline-secondary' }}" data-id="{{ $category->id }}">
            <i class="bi {{ $category->show_in_menu ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
        </button>
    </td>
    <td>
        <button type="button" class="btn btn-sm homepage-toggle {{ $category->show_in_homepage ? 'btn-success' : 'btn-outline-secondary' }}" data-id="{{ $category->id }}">
            <i class="bi {{ $category->show_in_homepage ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
        </button>
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-sm btn-outline-info" title="View">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            @if($category->canBeDeleted())
                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @else
                <button type="button" class="btn btn-sm btn-outline-secondary disabled" title="Cannot delete - has children or products" disabled>
                    <i class="bi bi-trash"></i>
                </button>
            @endif
        </div>
    </td>
</tr>

{{-- Render children recursively --}}
@if($hasChildren)
    @foreach($category->children as $child)
        @include('admin.categories.partials.category-row', ['category' => $child, 'depth' => $depth + 1])
    @endforeach
@endif
