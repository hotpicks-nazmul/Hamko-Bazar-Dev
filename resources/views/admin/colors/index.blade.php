@extends('admin.layouts.app')

@section('title', 'Colors')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-palette me-2"></i>Colors</h4>
        <p class="text-muted mb-0">Manage product colors for variations</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.colors.export') }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Export
        </a>
        <a href="{{ route('admin.colors.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Color
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-palette"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total</span>
            <span class="stat-card-value" id="statTotal" data-stat="total">{{ $stats['total'] }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value" id="statActive" data-stat="active">{{ $stats['active'] }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value" id="statInactive" data-stat="inactive">{{ $stats['inactive'] }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-box"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">In Use</span>
            <span class="stat-card-value" id="statProducts" data-stat="products">{{ $stats['products'] }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-funnel"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Filterable</span>
            <span class="stat-card-value" id="statFilterable" data-stat="filterable">{{ $stats['filterable'] }}</span>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Name, Code..." value="{{ request('search') }}">
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
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <label class="form-label small text-muted">Per Page</label>
                    <select name="per_page" id="filterPerPage" class="form-select form-select-sm">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <a href="{{ route('admin.colors.index') }}" class="btn btn-sm btn-outline-secondary">
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
                    Select All {{ $colors->total() }} Items
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
<div class="modal fade" id="colorProductsModal" tabindex="-1" aria-labelledby="colorProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="colorProductsModalLabel">Products with Color</h5>
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
                        <tbody id="colorProductsTableBody">
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

<!-- Colors Table -->
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
                            <a href="{{ route('admin.colors.index', array_merge(request()->all(), ['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'per_page' => request('per_page')])) }}" class="text-decoration-none text-dark">
                                Name <i class="bi bi-arrow-down-up"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.colors.index', array_merge(request()->all(), ['sort' => 'display_order', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'per_page' => request('per_page')])) }}" class="text-decoration-none text-dark">
                                Order <i class="bi bi-arrow-down-up"></i>
                            </a>
                        </th>
                        <th>Values</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Filterable</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($colors as $color)
                    @php
                        $search = request('search');
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
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-palette text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No colors found</p>
                            <a href="{{ route('admin.colors.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Add First Color
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($colors->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Showing {{ $colors->firstItem() }} to {{ $colors->lastItem() }} of {{ $colors->total() }} colors
        </div>
        <div id="pagination">
            {{ $colors->links() }}
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

@push('styles')
<style>
    .color-swatch {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        display: inline-block;
        vertical-align: middle;
        border: 1px solid #ddd;
        flex-shrink: 0;
    }
    
    /* Force Bootstrap Icons to display - SAME AS REFERENCE PAGE */
    .stat-card-icon i,
    .stat-card-icon i::before,
    .bi::before {
        display: inline-block !important;
    }
    
    /* Override icon colors for stat cards */
    .stat-card-primary .stat-card-icon i::before { color: #0d6efd !important; }
    .stat-card-success .stat-card-icon i::before { color: #198754 !important; }
    .stat-card-info .stat-card-icon i::before { color: #0dcaf0 !important; }
    .stat-card-warning .stat-card-icon i::before { color: #ffc107 !important; }
    .stat-card-danger .stat-card-icon i::before { color: #dc3545 !important; }
    .stat-card-secondary .stat-card-icon i::before { color: #6c757d !important; }
    
    /* Make the whole icon colored */
    .stat-card-icon i { color: inherit !important; }
</style>
@endpush

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
    ['filterStatus', 'filterFilterable', 'filterPerPage'].forEach(id => {
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

        const perPage = document.getElementById('filterPerPage').value;
        if (perPage) params.set('per_page', perPage);
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
        if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
        
        fetch(`{{ route('admin.colors.index') }}?${params.toString()}&ajax=1`, {
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
                
                if (data.stats) {
                    updateStats(data.stats);
                }
                
                if (data.pagination) {
                    document.querySelector('#pagination').innerHTML = data.pagination;
                }
                
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
        });
    }

    function updateStats(stats) {
        document.querySelector('[data-stat="total"]').textContent = stats.total;
        document.querySelector('[data-stat="active"]').textContent = stats.active;
        document.querySelector('[data-stat="inactive"]').textContent = stats.inactive;
        document.querySelector('[data-stat="filterable"]').textContent = stats.filterable;
        document.querySelector('[data-stat="products"]').textContent = stats.products;
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
        document.getElementById('selectedCount').textContent = '{{ $colors->total() }} (all pages)';
        document.getElementById('bulkActionsBar').style.display = 'block';
    }

    function bulkAction(action) {
        if (selectedItems.size === 0) {
            alert('Please select at least one color.');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${selectedItems.size} color(s)?`)) return;
        
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
        document.getElementById('bulkActionForm').action = '{{ route('admin.colors.bulk-action') }}';
        document.getElementById('bulkActionForm').submit();
    }

    function toggleStatus(id) {
        fetch(`{{ route('admin.colors.toggle-status', ['color' => 'ID']) }}`.replace('ID', id), {
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
        fetch(`{{ route('admin.colors.toggle-filterable', ['color' => 'ID']) }}`.replace('ID', id), {
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
        if (!confirm('Are you sure you want to delete this color?')) return;
        
        const form = document.getElementById('deleteForm');
        form.action = `{{ route('admin.colors.destroy', ['color' => 'ID']) }}`.replace('ID', id);
        form.submit();
    }

    let colorProductsModal = null;
    
    function showColorProducts(id, name) {
        document.getElementById('colorProductsModalLabel').textContent = 'Products with "' + name + '" Color';
        document.getElementById('colorProductsTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>';
        
        if (!colorProductsModal) {
            colorProductsModal = new bootstrap.Modal(document.getElementById('colorProductsModal'));
        }
        colorProductsModal.show();
        
        fetch(`{{ route('admin.colors.products.list', ['color' => 'ID']) }}`.replace('ID', id), {
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
                document.getElementById('colorProductsTableBody').innerHTML = html;
            } else {
                document.getElementById('colorProductsTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No products found</td></tr>';
            }
        })
        .catch(error => {
            document.getElementById('colorProductsTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error loading products</td></tr>';
        });
    }
</script>
@endpush
