@extends('admin.layouts.app')

@section('title', 'Addon Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Addon Manager</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.addons.install') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Install Addon
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-puzzle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total</span>
            <span class="stat-card-value">{{ $stats['total'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value">{{ $stats['active'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-pause-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value">{{ $stats['inactive'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-download"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Not Installed</span>
            <span class="stat-card-value">{{ $stats['uninstalled'] ?? 0 }}</span>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search -->
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Name, description..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="uninstalled" {{ request('status') === 'uninstalled' ? 'selected' : '' }}>Not Installed</option>
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <a href="{{ route('admin.addons.index') }}" class="btn btn-sm btn-outline-secondary">
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
                    <i class="bi bi-pause-circle me-1"></i> Deactivate
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Forms -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.addons.bulk-action') }}">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput" value="">
    <input type="hidden" name="ids" id="bulkIdsInput" value="">
</form>

<!-- Addons Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                        </th>
                        <th>
                            @php
                                $sort = request('sort');
                                $direction = request('direction');
                                $isNameSort = $sort === 'name';
                                $newDirection = ($isNameSort && $direction === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('admin.addons.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $newDirection])) }}"
                               class="text-decoration-none text-dark">
                                Addon
                                @if($isNameSort)
                                    <i class="bi bi-chevron-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Version</th>
                        <th>
                            @php
                                $isAuthorSort = $sort === 'author';
                                $newDirection = ($isAuthorSort && $direction === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('admin.addons.index', array_merge(request()->query(), ['sort' => 'author', 'direction' => $newDirection])) }}"
                               class="text-decoration-none text-dark">
                                Author
                                @if($isAuthorSort)
                                    <i class="bi bi-chevron-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php
                                $isStatusSort = $sort === 'status';
                                $newDirection = ($isStatusSort && $direction === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('admin.addons.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $newDirection])) }}"
                               class="text-decoration-none text-dark">
                                Status
                                @if($isStatusSort)
                                    <i class="bi bi-chevron-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Type</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($addons as $addon)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input item-checkbox" value="{{ $addon->id }}">
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="addon-icon me-3">
                                    <i class="{{ $addon->icon }}" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <strong>{{ $addon->name }}</strong>
                                    @if($addon->description)
                                    <br><small class="text-muted">{{ Str::limit($addon->description, 60) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $addon->version }}</span>
                        </td>
                        <td>{{ $addon->author ?? 'N/A' }}</td>
                        <td>
                            @if($addon->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($addon->status === 'inactive')
                                <span class="badge bg-warning text-dark">Inactive</span>
                            @else
                                <span class="badge bg-secondary">Not Installed</span>
                            @endif
                        </td>
                        <td>
                            @if($addon->is_core)
                                <span class="badge bg-info text-dark">Core</span>
                            @else
                                <span class="badge bg-light text-dark">Custom</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                @if($addon->status !== 'uninstalled')
                                    @if(!$addon->is_core)
                                    <form action="{{ route('admin.addons.toggle', $addon->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $addon->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $addon->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $addon->status === 'active' ? 'pause' : 'play' }}-circle"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <a href="{{ route('admin.addons.edit', $addon->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                @if(!$addon->is_core && $addon->status !== 'uninstalled')
                                <form action="{{ route('admin.addons.destroy', $addon->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to uninstall this addon?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Uninstall">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-puzzle text-muted" style="font-size: 3rem;"></i>
                            @if(request('search') || request('status'))
                                <p class="text-muted mb-2 mt-2">No addons match your filters</p>
                                <a href="{{ route('admin.addons.index') }}" class="btn btn-sm btn-outline-secondary mt-1">
                                    <i class="bi bi-x-lg me-1"></i> Clear Filters
                                </a>
                            @else
                                <p class="text-muted mb-2 mt-2">No addons found</p>
                                <a href="{{ route('admin.addons.install') }}" class="btn btn-sm btn-primary mt-1">
                                    <i class="bi bi-plus-lg me-1"></i> Install Your First Addon
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($addons->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Showing {{ $addons->firstItem() ?? 0 }} - {{ $addons->lastItem() ?? 0 }} of {{ $addons->total() }} addons
            @if(request('search'))<span class="text-primary"> (filtered)</span>@endif
        </div>
        <div>
            {{ $addons->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Filter form handling
    const filterStatus = document.getElementById('filterStatus');
    const liveSearch = document.getElementById('liveSearch');
    const filterForm = document.getElementById('filterForm');

    // Live search with debounce
    let searchTimeout;
    liveSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterForm.submit();
        }, 300);
    });

    // Filter dropdown triggers form submit
    filterStatus.addEventListener('change', function() {
        filterForm.submit();
    });

    // Bulk selection
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');

    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
        selectedCount.textContent = checkedBoxes.length;
        bulkActionsBar.style.display = checkedBoxes.length > 0 ? 'block' : 'none';
    }

    function clearSelection() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAllCheckbox.checked = false;
        updateBulkActions();
    }

    function bulkAction(action) {
        const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one addon.');
            return;
        }

        if (!confirm(`Are you sure you want to ${action} ${checkedBoxes.length} addon(s)?`)) {
            return;
        }

        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(
            Array.from(checkedBoxes).map(cb => cb.value)
        );
        document.getElementById('bulkActionForm').submit();
    }
</script>
@endpush
