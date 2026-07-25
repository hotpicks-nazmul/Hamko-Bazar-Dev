@extends('admin.layouts.app')

@section('title', 'Manage Menu Items - ' . $menu->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Manage Menu Items</h4>
        <small class="text-muted">{{ $menu->name }} ({{ $menu->location ?? 'No location' }})</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Menus
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="bi bi-plus-lg me-1"></i> Add Menu Item
        </button>
    </div>
</div>

<!-- Success/Error Alerts -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Menu Items -->
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-list-nested me-2"></i>Menu Structure</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="saveOrderBtn">
                    <i class="bi bi-save me-1"></i> Save Order
                </button>
            </div>
            <div class="card-body p-0">
                @if($menu->items->count() > 0)
                <div class="menu-builder-container p-3" id="menuItemsContainer">
                    <ul class="menu-builder-list list-unstyled" id="sortableMenu">
                        @foreach($rootItems as $item)
                            @include('admin.content.menus.partials.menu-item', ['item' => $item, 'menu' => $menu])
                        @endforeach
                    </ul>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mb-2 mt-2">No menu items yet</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="bi bi-plus-lg me-1"></i> Add First Menu Item
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Tips -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Quick Tips</h6>
            </div>
            <div class="card-body">
                <ul class="small text-muted mb-0">
                    <li class="mb-2">Drag and drop items to reorder them.</li>
                    <li class="mb-2">Items can be nested up to 3 levels deep.</li>
                    <li class="mb-2">Use categories, pages, or custom links.</li>
                    <li>Click "Save Order" after reordering.</li>
                </ul>
            </div>
        </div>

        <!-- Menu Stats -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Menu Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Items</span>
                    <strong>{{ $menu->items->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Active Items</span>
                    <strong>{{ $menu->items->where('is_active', true)->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Inactive Items</span>
                    <strong>{{ $menu->items->where('is_active', false)->count() }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Add Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addItemForm" method="POST" action="{{ route('admin.menus.items.store', $menu->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="itemTitle" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="itemTitle" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="itemType" class="form-label">Type <span class="text-danger">*</span></label>
                        <select id="itemType" name="type" class="form-select" required>
                            <option value="custom">Custom Link</option>
                            <option value="category">Category</option>
                            <option value="page">Page</option>
                            <option value="product">Product</option>
                            <option value="link">External Link</option>
                        </select>
                    </div>

                    <!-- Reference Selection (Category/Page/Product) -->
                    <div class="mb-3 reference-field" id="categoryField" style="display: none;">
                        <label for="categorySelect" class="form-label">Select Category</label>
                        <select id="categorySelect" name="reference_id" class="form-select">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @foreach($category->children as $child)
                            <option value="{{ $child->id }}">-- {{ $child->name }}</option>
                            @endforeach
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 reference-field" id="pageField" style="display: none;">
                        <label for="pageSelect" class="form-label">Select Page</label>
                        <select id="pageSelect" name="reference_id" class="form-select">
                            <option value="">Select Page</option>
                            @foreach($pages as $page)
                            <option value="{{ $page->id }}">{{ $page->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 reference-field" id="productField" style="display: none;">
                        <label for="productSelect" class="form-label">Select Product</label>
                        <select id="productSelect" name="reference_id" class="form-select">
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Custom URL -->
                    <div class="mb-3 reference-field" id="urlField">
                        <label for="itemUrl" class="form-label">URL</label>
                        <input type="text" id="itemUrl" name="url" class="form-control" placeholder="/about-us">
                        <div class="form-text">For custom links, start with / for internal pages.</div>
                    </div>

                    <div class="mb-3">
                        <label for="itemTarget" class="form-label">Open In</label>
                        <select id="itemTarget" name="target" class="form-select">
                            <option value="_self">Same Window</option>
                            <option value="_blank">New Tab</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="itemIcon" class="form-label">Icon Class</label>
                        <input type="text" id="itemIcon" name="icon" class="form-control" placeholder="bi bi-home">
                        <div class="form-text">Bootstrap Icons class (e.g., bi bi-home)</div>
                    </div>

                    <div class="mb-3">
                        <label for="itemParent" class="form-label">Parent Item</label>
                        <select id="itemParent" name="parent_id" class="form-select">
                            <option value="">No Parent (Root Level)</option>
                            @foreach($menu->items as $item)
                            @if($item->parent_id == null && $item->id)
                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="itemCssClass" class="form-label">CSS Class</label>
                        <input type="text" id="itemCssClass" name="css_class" class="form-control" placeholder="custom-class">
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="itemActive" name="is_active" checked>
                        <label class="form-check-label" for="itemActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editItemModalLabel">Edit Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editItemForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editItemTitle" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="editItemTitle" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="editItemType" class="form-label">Type <span class="text-danger">*</span></label>
                        <select id="editItemType" name="type" class="form-select" required>
                            <option value="custom">Custom Link</option>
                            <option value="category">Category</option>
                            <option value="page">Page</option>
                            <option value="product">Product</option>
                            <option value="link">External Link</option>
                        </select>
                    </div>

                    <!-- Reference Selection -->
                    <div class="mb-3 edit-reference-field" id="editCategoryField" style="display: none;">
                        <label for="editCategorySelect" class="form-label">Select Category</label>
                        <select id="editCategorySelect" name="reference_id" class="form-select">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @foreach($category->children as $child)
                            <option value="{{ $child->id }}">-- {{ $child->name }}</option>
                            @endforeach
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 edit-reference-field" id="editPageField" style="display: none;">
                        <label for="editPageSelect" class="form-label">Select Page</label>
                        <select id="editPageSelect" name="reference_id" class="form-select">
                            <option value="">Select Page</option>
                            @foreach($pages as $page)
                            <option value="{{ $page->id }}">{{ $page->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 edit-reference-field" id="editProductField" style="display: none;">
                        <label for="editProductSelect" class="form-label">Select Product</label>
                        <select id="editProductSelect" name="reference_id" class="form-select">
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 edit-reference-field" id="editUrlField">
                        <label for="editItemUrl" class="form-label">URL</label>
                        <input type="text" id="editItemUrl" name="url" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="editItemTarget" class="form-label">Open In</label>
                        <select id="editItemTarget" name="target" class="form-select">
                            <option value="_self">Same Window</option>
                            <option value="_blank">New Tab</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="editItemIcon" class="form-label">Icon Class</label>
                        <input type="text" id="editItemIcon" name="icon" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="editItemParent" class="form-label">Parent Item</label>
                        <select id="editItemParent" name="parent_id" class="form-select">
                            <option value="">No Parent (Root Level)</option>
                            @foreach($menu->items as $item)
                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="editItemCssClass" class="form-label">CSS Class</label>
                        <input type="text" id="editItemCssClass" name="css_class" class="form-control">
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="editItemActive" name="is_active" checked>
                        <label class="form-check-label" for="editItemActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.menu-builder-container {
    min-height: 200px;
}

.menu-builder-list {
    margin: 0;
    padding: 0;
}

.menu-builder-list ul {
    margin-left: 20px;
    list-style: none;
    padding-left: 15px;
    border-left: 1px dashed #dee2e6;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    margin-bottom: 5px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    cursor: move;
    transition: all 0.2s ease;
}

.menu-item:hover {
    background: #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.menu-item.sortable-ghost {
    opacity: 0.4;
    background: #e2e3e5;
}

.menu-item.sortable-chosen {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    background: #fff;
}

.menu-item .drag-handle {
    color: #adb5bd;
    margin-right: 10px;
    cursor: move;
}

.menu-item .item-icon {
    margin-right: 10px;
    color: #6c757d;
}

.menu-item .item-title {
    flex: 1;
    font-weight: 500;
}

.menu-item .item-url {
    flex: 1;
    font-size: 0.85em;
    color: #6c757d;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 200px;
}

.menu-item .item-actions {
    display: flex;
    gap: 5px;
    flex-shrink: 0;
}

.menu-item.inactive {
    opacity: 0.6;
}

.nested-indicator {
    font-size: 0.75em;
    color: #adb5bd;
    margin-left: 10px;
}
</style>
@endpush

@push('scripts')
<!-- SortableJS Library for drag-and-drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize SortableJS on the menu list
    const sortableList = document.getElementById('sortableMenu');
    if (sortableList) {
        Sortable.create(sortableList, {
            group: 'menu-items',
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            fallbackClass: 'sortable-fallback',
            onEnd: function(evt) {
                // Visual feedback on reorder
                console.log('Item moved from index ' + evt.oldIndex + ' to ' + evt.newIndex);
            }
        });
    }

    // Toggle reference fields based on type selection (Add Modal)
    const itemType = document.getElementById('itemType');
    const referenceFields = document.querySelectorAll('.reference-field');
    
    function toggleAddFields(type) {
        referenceFields.forEach(field => field.style.display = 'none');
        
        switch(type) {
            case 'category':
                document.getElementById('categoryField').style.display = 'block';
                break;
            case 'page':
                document.getElementById('pageField').style.display = 'block';
                break;
            case 'product':
                document.getElementById('productField').style.display = 'block';
                break;
            case 'link':
                document.getElementById('urlField').style.display = 'block';
                document.getElementById('itemUrl').placeholder = 'https://example.com';
                break;
            default:
                document.getElementById('urlField').style.display = 'block';
                document.getElementById('itemUrl').placeholder = '/about-us';
        }
    }
    
    if (itemType) {
        itemType.addEventListener('change', function() {
            toggleAddFields(this.value);
        });
        // Initialize on page load
        toggleAddFields(itemType.value);
    }

    // Edit item modal handling
    const editItemModal = document.getElementById('editItemModal');
    if (editItemModal) {
        editItemModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const itemId = button.getAttribute('data-item-id');
            const itemTitle = button.getAttribute('data-item-title');
            const itemTypeVal = button.getAttribute('data-item-type');
            const itemUrl = button.getAttribute('data-item-url');
            const itemTarget = button.getAttribute('data-item-target');
            const itemIcon = button.getAttribute('data-item-icon');
            const itemCssClass = button.getAttribute('data-item-css-class');
            const itemParentId = button.getAttribute('data-item-parent-id');
            const itemActive = button.getAttribute('data-item-active');
            const itemReferenceId = button.getAttribute('data-item-reference-id');

            document.getElementById('editItemTitle').value = itemTitle || '';
            document.getElementById('editItemType').value = itemTypeVal || 'custom';
            document.getElementById('editItemUrl').value = itemUrl || '';
            document.getElementById('editItemTarget').value = itemTarget || '_self';
            document.getElementById('editItemIcon').value = itemIcon || '';
            document.getElementById('editItemCssClass').value = itemCssClass || '';
            document.getElementById('editItemParent').value = itemParentId || '';
            document.getElementById('editItemActive').checked = itemActive === '1';

            // Update form action
            const form = document.getElementById('editItemForm');
            form.action = `{{ route('admin.menus.items.update', [$menu->id, 'ITEM_ID']) }}`.replace('ITEM_ID', itemId);

            // Show/hide reference fields
            const editReferenceFields = document.querySelectorAll('.edit-reference-field');
            editReferenceFields.forEach(field => field.style.display = 'none');

            switch(itemTypeVal) {
                case 'category':
                    document.getElementById('editCategoryField').style.display = 'block';
                    document.getElementById('editCategorySelect').value = itemReferenceId || '';
                    break;
                case 'page':
                    document.getElementById('editPageField').style.display = 'block';
                    document.getElementById('editPageSelect').value = itemReferenceId || '';
                    break;
                case 'product':
                    document.getElementById('editProductField').style.display = 'block';
                    document.getElementById('editProductSelect').value = itemReferenceId || '';
                    break;
                case 'link':
                    document.getElementById('editUrlField').style.display = 'block';
                    document.getElementById('editItemUrl').placeholder = 'https://example.com';
                    break;
                default:
                    document.getElementById('editUrlField').style.display = 'block';
                    document.getElementById('editItemUrl').placeholder = '/about-us';
            }
        });
    }

    // Edit modal type change handler
    const editItemType = document.getElementById('editItemType');
    if (editItemType) {
        editItemType.addEventListener('change', function() {
            const editReferenceFields = document.querySelectorAll('.edit-reference-field');
            editReferenceFields.forEach(field => field.style.display = 'none');
            
            switch(this.value) {
                case 'category':
                    document.getElementById('editCategoryField').style.display = 'block';
                    break;
                case 'page':
                    document.getElementById('editPageField').style.display = 'block';
                    break;
                case 'product':
                    document.getElementById('editProductField').style.display = 'block';
                    break;
                case 'link':
                    document.getElementById('editUrlField').style.display = 'block';
                    document.getElementById('editItemUrl').placeholder = 'https://example.com';
                    break;
                default:
                    document.getElementById('editUrlField').style.display = 'block';
                    document.getElementById('editItemUrl').placeholder = '/about-us';
            }
        });
    }

    // Save order functionality
    const saveOrderBtn = document.getElementById('saveOrderBtn');
    if (saveOrderBtn) {
        saveOrderBtn.addEventListener('click', function() {
            const items = [];
            
            function processItems(ul, parentId = null) {
                const lis = ul.querySelectorAll(':scope > li.menu-item-wrapper');
                lis.forEach((li, index) => {
                    const itemId = li.getAttribute('data-item-id');
                    const parentEl = li.closest('ul');
                    const parentItemId = parentEl && parentEl.closest('li.menu-item-wrapper') 
                        ? parentEl.closest('li.menu-item-wrapper').getAttribute('data-item-id') 
                        : null;
                    
                    items.push({
                        id: parseInt(itemId),
                        parent_id: parentItemId ? parseInt(parentItemId) : null,
                        sort_order: index
                    });

                    const childUl = li.querySelector('ul');
                    if (childUl) {
                        processItems(childUl, itemId);
                    }
                });
            }

            processItems(sortableList);

            if (items.length > 0) {
                // Show loading state
                saveOrderBtn.disabled = true;
                saveOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
                
                fetch(`{{ route('admin.menus.items.reorder', $menu->id) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ items: items })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Show success alert
                        showAlert('success', data.message || 'Menu order saved successfully!');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert('danger', 'Error saving order: ' + (data.message || 'Unknown error'));
                        resetSaveButton();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'An error occurred while saving the order.');
                    resetSaveButton();
                });
            } else {
                showAlert('warning', 'No items to reorder.');
            }
        });
    }
    
    function resetSaveButton() {
        saveOrderBtn.disabled = false;
        saveOrderBtn.innerHTML = '<i class="bi bi-save me-1"></i> Save Order';
    }
    
    // Helper function to show alerts using Bootstrap alerts
    function showAlert(type, message) {
        // Remove existing alerts
        document.querySelectorAll('.menu-alert').forEach(el => el.remove());
        
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show menu-alert" role="alert">
                <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Insert before the first card
        const firstCard = document.querySelector('.card');
        if (firstCard) {
            firstCard.insertAdjacentHTML('beforebegin', alertHtml);
        }
    }
});
</script>
@endpush
