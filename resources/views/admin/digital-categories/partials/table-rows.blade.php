@foreach($categories as $index => $category)
@php
    $search = request('search');
    $isMatch = $search && (
        stripos($category->name, $search) !== false ||
        stripos($category->slug ?? '', $search) !== false ||
        stripos($category->description ?? '', $search) !== false
    );
@endphp
<tr class="{{ $isMatch ? 'table-warning' : '' }}">
    <td class="ps-3">{{ $categories->firstItem() + $index }}</td>
    <td>
        <div class="d-flex align-items-center">
            @if($category->image)
                @php
                    $imgPath = ltrim($category->image, '/');
                    if (!str_starts_with($imgPath, 'http') && !str_starts_with($imgPath, 'storage/')) {
                        $imgPath = '/storage/' . $imgPath;
                    } elseif (str_starts_with($imgPath, 'storage/')) {
                        $imgPath = '/' . $imgPath;
                    }
                @endphp
                <img src="{{ $imgPath }}" alt="{{ $category->name }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
            @elseif($category->icon)
                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="{{ $category->icon }} fs-5 text-muted"></i>
                </div>
            @else
                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-folder fs-5 text-muted"></i>
                </div>
            @endif
            <div>
                <div class="fw-semibold">{{ $category->name }}</div>
                @if($category->parent)
                    <small class="text-muted">
                        <i class="bi bi-arrow-up-right me-1"></i>{{ $category->parent->name }}
                    </small>
                @endif
            </div>
        </div>
    </td>
    <td>
        <span class="badge bg-light text-dark">{{ $category->product_count }}</span>
    </td>
    <td>
        <button type="button" class="btn btn-sm status-toggle {{ $category->status === 'active' ? 'btn-success' : 'btn-outline-secondary' }}" 
                data-id="{{ $category->id }}" 
                data-status="{{ $category->status }}"
                title="Click to toggle status">
            {{ $category->status === 'active' ? 'Active' : 'Inactive' }}
        </button>
    </td>
    <td>
        <input type="number" class="form-control form-control-sm order-input" 
               value="{{ $category->order }}" 
               data-id="{{ $category->id }}"
               min="0" style="width: 70px;">
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.digital-categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.digital-categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@endforeach