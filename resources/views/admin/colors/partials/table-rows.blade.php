@forelse($colors as $color)
@php
    $search = isset($search) ? $search : request('search');
    $isMatch = $search && (
        stripos($color->name, $search) !== false || 
        stripos($color->code, $search) !== false
    );
@endphp
<tr data-id="{{ $color->id }}" class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input item-checkbox" value="{{ $color->id }}">
    </td>
    <td>
        <a href="{{ route('admin.colors.edit', $color->id) }}" class="text-decoration-none fw-medium">
            {{ $color->name }}
        </a>
        @if($color->description)
        <br><small class="text-muted">{{ Str::limit($color->description, 50) }}</small>
        @endif
    </td>
    <td>{{ $color->display_order }}</td>
    <td>
        <span class="badge bg-light text-dark">{{ $color->values_count }} values</span>
        @if($color->active_values_count > 0)
        <span class="badge bg-success">{{ $color->active_values_count }} active</span>
        @endif
    </td>
    <td>
        @if($color->products_count > 0)
        <button type="button" class="badge bg-primary text-white text-decoration-none border-0" 
                onclick="showColorProducts({{ $color->id }}, '{{ $color->name }}')">
            {{ $color->products_count }} products
        </button>
        @else
        <span class="badge bg-light text-dark">{{ $color->products_count }} products</span>
        @endif
    </td>
    <td>
        <button type="button" class="btn btn-sm {{ $color->is_active ? 'btn-success' : 'btn-outline-secondary' }}"
                onclick="toggleStatus({{ $color->id }})" title="Toggle Status">
            <i class="bi {{ $color->is_active ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
            {{ $color->is_active ? 'Active' : 'Inactive' }}
        </button>
    </td>
    <td>
        <button type="button" class="btn btn-sm {{ $color->is_filterable ? 'btn-info' : 'btn-outline-secondary' }}"
                onclick="toggleFilterable({{ $color->id }})" title="Toggle Filterable">
            <i class="bi {{ $color->is_filterable ? 'bi-funnel' : 'bi-funnel-fill' }}"></i>
            {{ $color->is_filterable ? 'Yes' : 'No' }}
        </button>
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.colors.edit', $color->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteItem({{ $color->id }})" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-5">
        <i class="bi bi-palette text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-2">No colors found</p>
        <a href="{{ route('admin.colors.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add First Color
        </a>
    </td>
</tr>
@endforelse
