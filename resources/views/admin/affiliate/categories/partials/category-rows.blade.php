@forelse($categories as $category)
@php
    $search = request('search');
    $isMatch = $search && (
        stripos($category->name, $search) !== false || 
        stripos($category->slug, $search) !== false
    );
@endphp
<tr class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input category-checkbox" 
               value="{{ $category->id }}" onchange="updateBulkActions()">
    </td>
    <td>{{ $category->id }}</td>
    <td>
        @if($category->image)
        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
        @else
        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="bi bi-folder text-muted"></i>
        </div>
        @endif
    </td>
    <td class="fw-medium">{{ $category->name }}</td>
    <td><code class="small">{{ $category->slug }}</code></td>
    <td><span class="badge bg-info">{{ $category->commission_rate }}%</span></td>
    <td><span class="badge bg-secondary">{{ $category->products_count }}</span></td>
    <td>
        @if($category->status === 'active')
        <span class="badge bg-success">Active</span>
        @else
        <span class="badge bg-danger">Inactive</span>
        @endif
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.affiliate.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                    onclick="confirmDelete({{ $category->id }}, '{{ $category->name }}')">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-5">
        <i class="bi bi-folder text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No categories found</p>
        <a href="{{ route('admin.affiliate.categories.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First Category
        </a>
    </td>
</tr>
@endforelse
