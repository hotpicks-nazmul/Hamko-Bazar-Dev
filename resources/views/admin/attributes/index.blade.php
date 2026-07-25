@extends('admin.layouts.app')

@section('title', 'Attributes')

@push('styles')
<style>
    .color-preview {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        display: inline-block;
        vertical-align: middle;
        border: 1px solid #ddd;
    }
</style>
@endpush

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-sliders me-2"></i>Attributes</h4>
            <p class="text-muted mb-0">Manage product attributes and their values</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.attributes.export') }}" class="btn btn-outline-secondary">
                <i class="bi bi-download me-1"></i> Export
            </a>
            <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Attribute
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-list-ul"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Total</span><span class="stat-card-value" id="statTotal">{{ $stats['total'] }}</span></div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Active</span><span class="stat-card-value" id="statActive">{{ $stats['active'] }}</span></div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Inactive</span><span class="stat-card-value" id="statInactive">{{ $stats['inactive'] }}</span></div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-funnel"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Filterable</span><span class="stat-card-value" id="statFilterable">{{ $stats['filterable'] }}</span></div>
    </div>
</div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <label class="form-label small text-muted">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="liveSearch" class="form-control" 
                                   placeholder="Name, Slug..." value="{{ request('search') }}">
                            <span class="input-group-text" id="searchSpinner" style="display: none;">
                                <div class="spinner-border spinner-border-sm"></div>
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" id="filterStatus" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Filterable</label>
                        <select name="filterable" id="filterFilterable" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="yes" {{ request('filterable') === 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('filterable') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-8">
                        <a href="{{ route('admin.attributes.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div class="card border-0 shadow-sm mb-3" id="bulkActionsBar" style="display: none;">
        <div class="card-body py-2">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted"><span id="selectedCount">0</span> selected</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllItems()">
                        Select All {{ $attributes->total() }} Items
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                        Clear Selection
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('activate')">
                        <i class="bi bi-check-circle me-1"></i> Activate
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('deactivate')">
                        <i class="bi bi-x-circle me-1"></i> Deactivate
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Modal -->
<div class="modal fade" id="attributeProductsModal" tabindex="-1" aria-labelledby="attributeProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attributeProductsModalLabel">Products with Attribute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Edited</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="attributeProductsTableBody">
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <!-- Attributes Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>
                                <a href="{{ route('admin.attributes.index', array_merge(request()->all(), ['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Name <i class="bi bi-arrow-down-up"></i>
                                </a>
                            </th>
                            <th>Values</th>
                            <th>Products</th>
                            <th>
                            <th>Status</th>
                            <th>Filterable</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($attributes as $attribute)
                        <tr data-id="{{ $attribute->id }}">
                            <td>
                                <input type="checkbox" class="form-check-input item-checkbox" value="{{ $attribute->id }}">
                            </td>
                            <td>
                                <a href="{{ route('admin.attributes.edit', $attribute->id) }}" class="text-decoration-none fw-medium">
                                    {{ $attribute->name }}
                                </a>
                                @if($attribute->description)
                                <br><small class="text-muted">{{ Str::limit($attribute->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $attribute->values_count }} values</span>
                                @if($attribute->active_values_count > 0)
                                <span class="badge bg-success">{{ $attribute->active_values_count }} active</span>
                                @endif
                            </td>
                            <td>
                                @if($attribute->products_count > 0)
                                <button type="button" class="badge bg-primary text-white text-decoration-none border-0" 
                                        onclick="showAttributeProducts({{ $attribute->id }}, '{{ $attribute->name }}')">
                                    {{ $attribute->products_count }} products
                                </button>
                                @else
                                <span class="badge bg-light text-dark">{{ $attribute->products_count }} products</span>
                                @endif
                            </td>
                            <td>{{ $attribute->display_order }}</td>
                            <td>
                                <button type="button" class="btn btn-sm {{ $attribute->is_active ? 'btn-success' : 'btn-outline-secondary' }}" 
                                        onclick="toggleStatus({{ $attribute->id }})" title="Toggle Status">
                                    <i class="bi {{ $attribute->is_active ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                    {{ $attribute->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm {{ $attribute->is_filterable ? 'btn-info' : 'btn-outline-secondary' }}" 
                                        onclick="toggleFilterable({{ $attribute->id }})" title="Toggle Filterable">
                                    <i class="bi {{ $attribute->is_filterable ? 'bi-funnel' : 'bi-funnel-fill' }}"></i>
                                    {{ $attribute->is_filterable ? 'Yes' : 'No' }}
                                </button>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.attributes.edit', $attribute->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteItem({{ $attribute->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">No attributes found</p>
                                <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i> Add First Attribute
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($attributes->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $attributes->firstItem() }} to {{ $attributes->lastItem() }} of {{ $attributes->total() }} attributes
                </div>
                <div id="pagination">
                    {{ $attributes->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ids" id="bulkIdsInput">
</form>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    let selectedItems = new Set();

    // Live search
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        
        searchSpinner.style.display = 'block';
        
        searchTimeout = setTimeout(() => {
            performLiveSearch(searchTerm);
        }, 300);
    });

    // Filter dropdowns
    ['filterStatus', 'filterFilterable'].forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                performLiveSearch(searchInput.value.trim());
            });
        }
    });

    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();
        
        if (searchTerm) params.set('search', searchTerm);
        
        const status = document.getElementById('filterStatus').value;
        if (status) params.set('status', status);
        
        const filterable = document.getElementById('filterFilterable').value;
        if (filterable) params.set('filterable', filterable);
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
        if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
        
        fetch(`{{ route('admin.attributes.index') }}?${params.toString()}&ajax=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            searchSpinner.style.display = 'none';
            
            if (data.html) {
                document.querySelector('#tableBody').innerHTML = data.html;
                
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
            
            if (data.stats) {
                updateStats(data.stats);
            }
        });
    }
    
    function updateStats(stats) {
        document.querySelector('#statTotal').textContent = stats.total;
        document.querySelector('#statActive').textContent = stats.active;
        document.querySelector('#statInactive').textContent = stats.inactive;
        document.querySelector('#statFilterable').textContent = stats.filterable;
    }

    // Select all
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
            if (this.checked) {
                selectedItems.add(parseInt(cb.value));
            } else {
                selectedItems.delete(parseInt(cb.value));
            }
        });
        updateBulkActions();
    });

    // Individual checkbox
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-checkbox')) {
            const id = parseInt(e.target.value);
            if (e.target.checked) {
                selectedItems.add(id);
            } else {
                selectedItems.delete(id);
            }
            updateBulkActions();
        }
    });

    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    }

    function clearSelection() {
        selectedItems.clear();
        document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        updateBulkActions();
    }

    function selectAllItems() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = true;
            selectedItems.add(parseInt(cb.value));
        });
        document.getElementById('selectedCount').textContent = '{{ $attributes->total() }} (all pages)';
        document.getElementById('bulkActionsBar').style.display = 'block';
    }

    function bulkAction(action) {
        if (selectedItems.size === 0) {
            alert('Please select at least one attribute.');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${selectedItems.size} attribute(s)?`)) return;
        
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
        document.getElementById('bulkActionForm').action = '{{ route('admin.attributes.bulk-action') }}';
        document.getElementById('bulkActionForm').submit();
    }

    function toggleStatus(id) {
        fetch(`{{ route('admin.attributes.toggle-status', ['attribute' => 'ID']) }}`.replace('ID', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function toggleFilterable(id) {
        fetch(`{{ route('admin.attributes.toggle-filterable', ['attribute' => 'ID']) }}`.replace('ID', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function deleteItem(id) {
        if (!confirm('Are you sure you want to delete this attribute?')) return;
        
        const form = document.getElementById('deleteForm');
        form.action = `{{ route('admin.attributes.destroy', ['attribute' => 'ID']) }}`.replace('ID', id);
        form.submit();
    }

    let attributeProductsModal = null;
    
    function showAttributeProducts(id, name) {
        document.getElementById('attributeProductsModalLabel').textContent = 'Products with "' + name + '" Attribute';
        document.getElementById('attributeProductsTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>';
        
        if (!attributeProductsModal) {
            attributeProductsModal = new bootstrap.Modal(document.getElementById('attributeProductsModal'));
        }
        attributeProductsModal.show();
        
        fetch(`{{ route('admin.attributes.products.list', ['attribute' => 'ID']) }}`.replace('ID', id), {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.products && data.products.length > 0) {
                let html = '';
                data.products.forEach(function(product) {
                    var editUrl = '{{ url("/admin/products") }}/' + product.id + '/edit';
                    var isEdited = product.is_complete === 1 || product.is_complete === true ? '<span class="badge bg-success">Done</span>' : '<span class="badge bg-warning text-dark">Pending</span>';
                    html += '<tr>';
                    html += '<td><a href="' + editUrl + '" class="text-decoration-none fw-medium">' + product.name + '</a></td>';
                    html += '<td>৳' + Number(product.price).toLocaleString() + '</td>';
                    html += '<td><span class="badge ' + (product.status === 'Active' ? 'bg-success' : 'bg-secondary') + '">' + product.status + '</span></td>';
                    html += '<td>' + isEdited + '</td>';
                    html += '<td><a href="' + editUrl + '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a></td>';
                    html += '</tr>';
                });
                document.getElementById('attributeProductsTableBody').innerHTML = html;
            } else {
                document.getElementById('attributeProductsTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No products found</td></tr>';
            }
        })
        .catch(error => {
            document.getElementById('attributeProductsTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error loading products</td></tr>';
        });
    }
</script>
@endpush
