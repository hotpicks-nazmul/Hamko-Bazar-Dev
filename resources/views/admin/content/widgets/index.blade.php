@extends('admin.layouts.app')

@section('title', 'Widget Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Widget Manager</h4>
    <a href="{{ route('admin.content.widgets.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Widget
    </a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control"
                               placeholder="Name, title..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Widget Type</label>
                    <select name="widget_type" id="filterWidgetType" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach($widgetTypes as $key => $type)
                            <option value="{{ $key }}" {{ request('widget_type') == $key ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-8">
                    <a href="{{ route('admin.content.widgets.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card border-0 shadow-sm mb-3" id="bulkActionsBar" style="display: none;">
    <div class="card-body py-2">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted"><span id="selectedCount">0</span> selected</span>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="clearSelection()">
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
                <button type="button" class="btn btn-sm btn-info" onclick="bulkAction('feature')">
                    <i class="bi bi-star me-1"></i> Feature
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="bulkAction('unfeature')">
                    <i class="bi bi-star-fill me-1"></i> Unfeature
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Widgets Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                        </th>
                        <th>Widget</th>
                        <th>Type</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 100px;">Featured</th>
                        <th style="width: 100px;">Order</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($widgets as $widget)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input widget-checkbox" value="{{ $widget->id }}">
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi {{ $widget->widget_icon }} text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $widget->name }}</div>
                                    <div class="text-muted small">{{ $widget->title ?? 'No title' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $widgetTypes[$widget->widget_type] ?? $widget->widget_type }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.content.widgets.toggle-status', $widget->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-{{ $widget->status === 'active' ? 'success' : 'secondary' }} w-100">
                                    {{ $widget->status === 'active' ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('admin.content.widgets.toggle-featured', $widget->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-{{ $widget->is_featured ? 'warning' : 'outline-secondary' }}">
                                    <i class="bi {{ $widget->is_featured ? 'bi-star-fill' : 'bi-star' }}"></i>
                                </button>
                            </form>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $widget->sort_order }}</span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.content.widgets.edit', $widget->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.content.widgets.destroy', $widget->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this widget?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-grid-3x3-gap text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No widgets found</p>
                            <a href="{{ route('admin.content.widgets.create') }}" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-plus-lg me-1"></i> Add First Widget
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($widgets->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $widgets->firstItem() }} - {{ $widgets->lastItem() }} of {{ $widgets->total() }} widgets
            </div>
            <div>
                {{ $widgets->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.content.widgets.bulk-action') }}">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="widgets" id="bulkIdsInput">
</form>

@push('scripts')
<script>
    // Live search and filters
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const filterForm = document.getElementById('filterForm');
    const searchSpinner = document.getElementById('searchSpinner');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchSpinner.style.display = 'block';
        searchTimeout = setTimeout(() => {
            filterForm.submit();
        }, 300);
    });

    document.getElementById('filterWidgetType').addEventListener('change', function() {
        filterForm.submit();
    });

    document.getElementById('filterStatus').addEventListener('change', function() {
        filterForm.submit();
    });

    // Bulk actions
    let selectedItems = new Set();

    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.widget-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            if (this.checked) {
                selectedItems.add(checkbox.value);
            } else {
                selectedItems.delete(checkbox.value);
            }
        });
        updateBulkActions();
    });

    document.querySelectorAll('.widget-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedItems.add(this.value);
            } else {
                selectedItems.delete(this.value);
            }
            updateBulkActions();
        });
    });

    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    }

    function clearSelection() {
        selectedItems.clear();
        document.getElementById('selectAllCheckbox').checked = false;
        document.querySelectorAll('.widget-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateBulkActions();
    }

    function bulkAction(action) {
        if (selectedItems.size === 0) {
            alert('Please select at least one widget.');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${selectedItems.size} widget(s)?`)) return;
        
        document.getElementById('bulkActionInput').value = action;
        // Send as comma-separated values instead of JSON for proper form submission
        document.getElementById('bulkIdsInput').value = Array.from(selectedItems).join(',');
        document.getElementById('bulkActionForm').submit();
    }
</script>
@endpush
@endsection
