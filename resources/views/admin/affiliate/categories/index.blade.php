@extends('admin.layouts.app')

@section('title', 'Affiliate Categories')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-folder"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Categories</span>
            <span class="stat-card-value">{{ number_format($stats['total'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value">{{ number_format($stats['active'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value">{{ number_format($stats['inactive'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-box"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Products</span>
            <span class="stat-card-value">{{ number_format($stats['total_products'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-percent"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Avg Commission</span>
            <span class="stat-card-value">{{ number_format($stats['avg_commission'] ?? 0, 1) }}%</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Affiliate Categories</h4>
    <a href="{{ route('admin.affiliate.categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Category
    </a>
</div>

<!-- Alert Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search Input -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Name, slug..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <!-- Sort -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Sort By</label>
                    <select name="sort" id="filterSort" class="form-select form-select-sm">
                        <option value="created_at" {{ request('sort') === 'created_at' || !request('sort') ? 'selected' : '' }}>Date Created</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name</option>
                        <option value="commission_rate" {{ request('sort') === 'commission_rate' ? 'selected' : '' }}>Commission Rate</option>
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('admin.affiliate.categories.index') }}" class="btn btn-sm btn-outline-secondary">
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

<!-- Categories Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="affiliateCategoriesTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 80px;">Image</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th style="width: 120px;">Commission</th>
                        <th style="width: 80px;">Products</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
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
                </tbody>
            </table>
        </div>
        
        <!-- Pagination inside card-body -->
        @if($categories->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $categories->firstItem() }} - {{ $categories->lastItem() }} of {{ $categories->total() }} entries
            </div>
            <div>
                {{ $categories->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.affiliate.categories.bulk-action') }}">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ids" id="bulkIdsInput">
</form>
@endsection

@push('styles')
<style>
    /* Fix delete button border-radius to match btn-group siblings */
    .btn-group .btn-outline-danger {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
    .btn-group .btn-outline-primary {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let selectedItems = new Set();

    // Toggle select all on current page
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
            if (checkbox.checked) {
                selectedItems.add(cb.value);
            } else {
                selectedItems.delete(cb.value);
            }
        });
        updateBulkActions();
    }

    // Clear selection
    function clearSelection() {
        selectedItems.clear();
        document.querySelectorAll('.category-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAllCheckbox').checked = false;
        updateBulkActions();
    }

    // Update bulk actions bar visibility
    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
        
        // Update select all checkbox state
        const checkboxes = document.querySelectorAll('.category-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        document.getElementById('selectAllCheckbox').checked = allChecked && checkboxes.length > 0;
    }

    // Perform bulk action
    function bulkAction(action) {
        if (selectedItems.size === 0) {
            alert('Please select at least one category.');
            return;
        }
        
        let confirmMsg = '';
        switch(action) {
            case 'delete':
                confirmMsg = `Are you sure you want to delete ${selectedItems.size} category(ies)? This action cannot be undone.`;
                break;
            case 'activate':
                confirmMsg = `Activate ${selectedItems.size} category(ies)?`;
                break;
            case 'deactivate':
                confirmMsg = `Deactivate ${selectedItems.size} category(ies)?`;
                break;
        }
        
        if (!confirm(confirmMsg)) return;
        
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
        document.getElementById('bulkActionForm').submit();
    }

    // Confirm delete for single category
    function confirmDelete(id, name) {
        if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('admin.affiliate.categories.destroy', ':id') }}`.replace(':id', id);
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Live Search
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
    ['filterStatus', 'filterSort'].forEach(id => {
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
        
        const sort = document.getElementById('filterSort').value;
        if (sort) params.set('sort', sort);
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
        
        fetch(`{{ route('admin.affiliate.categories.index') }}?${params.toString()}&ajax=1`, {
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
        })
        .catch(() => {
            searchSpinner.style.display = 'none';
            document.getElementById('filterForm').submit();
        });
    }
</script>
@endpush
