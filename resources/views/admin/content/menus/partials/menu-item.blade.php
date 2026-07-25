<li class="menu-item-wrapper" data-item-id="{{ $item->id }}">
    <div class="menu-item {{ !$item->is_active ? 'inactive' : '' }}">
        <i class="bi bi-grip-vertical drag-handle"></i>
        
        @if($item->icon)
        <i class="bi {{ $item->icon }} item-icon"></i>
        @else
        <i class="bi bi-link-45deg item-icon"></i>
        @endif
        
        <span class="item-title">
            {{ $item->title }}
            @if($item->type !== 'custom')
            <span class="badge bg-light text-dark ms-1">{{ $item->type }}</span>
            @endif
        </span>
        
        <span class="item-url">{{ $item->url ?? '/' }}</span>
        
        @if(!$item->is_active)
        <span class="badge bg-secondary">Inactive</span>
        @endif
        
        <div class="item-actions">
            <button type="button" class="btn btn-sm btn-outline-primary"
                data-bs-toggle="modal" 
                data-bs-target="#editItemModal"
                data-item-id="{{ $item->id }}"
                data-item-title="{{ $item->title }}"
                data-item-type="{{ $item->type }}"
                data-item-url="{{ $item->url }}"
                data-item-target="{{ $item->target }}"
                data-item-icon="{{ $item->icon }}"
                data-item-css-class="{{ $item->css_class }}"
                data-item-parent-id="{{ $item->parent_id }}"
                data-item-active="{{ $item->is_active ? '1' : '0' }}"
                data-item-reference-id="{{ $item->reference_id }}"
                title="Edit">
                <i class="bi bi-pencil"></i>
            </button>
            
            <form action="{{ route('admin.menus.items.toggle', [$menu->id, $item->id]) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm {{ $item->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}">
                    <i class="bi bi-{{ $item->is_active ? 'pause' : 'play' }}-circle"></i>
                </button>
            </form>
            
            <form action="{{ route('admin.menus.items.destroy', [$menu->id, $item->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </div>
    
    @if($item->children && $item->children->count() > 0)
    <ul class="menu-builder-list">
        @foreach($item->children as $child)
        @include('admin.content.menus.partials.menu-item', ['item' => $child, 'menu' => $menu])
        @endforeach
    </ul>
    @endif
</li>
