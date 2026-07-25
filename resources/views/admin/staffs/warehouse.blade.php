@extends('admin.layouts.app')

@section('title', 'Warehouse Staffs')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-building"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Warehouse Staff</span>
            <span class="stat-card-value">{{ $staffs->total() ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value">{{ $stats['active'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value">{{ $stats['inactive'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-building-gear"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Warehouses</span>
            <span class="stat-card-value">{{ $warehouses->count() ?? 0 }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Warehouse Staffs</h4>
    @if(auth()->user()->role !== 'staff')
    <a href="{{ route('admin.staffs.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Staff
    </a>
    @else
    <button type="button" class="btn btn-primary" onclick="showAccessDenied()">
        <i class="bi bi-plus-lg me-1"></i> Add New Staff
    </button>
    @endif
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search -->
                <div class="col-lg-4 col-md-4">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Name, email..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </span>
                    </div>
                </div>

                <!-- Status -->
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                    </select>
                </div>

                <!-- Warehouse -->
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small text-muted">Warehouse</label>
                    <select name="warehouse_id" id="filterWarehouse" class="form-select form-select-sm">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Per Page -->
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small text-muted">Per Page</label>
                    <select name="per_page" id="perPage" class="form-select form-select-sm">
                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <!-- Reset -->
                <div class="col-lg-2 col-md-4">
                    <a href="{{ route('admin.staffs.warehouse') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card border-0 shadow-sm mb-3" id="bulkActionsCard" style="display: none;">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small" id="selectedCount">0 selected</span>
            <select id="bulkActionSelect" class="form-select form-select-sm" style="width: auto;">
                <option value="">Bulk Actions</option>
                <option value="activate">Set Active</option>
                <option value="deactivate">Set Inactive</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button type="button" id="applyBulkAction" class="btn btn-sm btn-primary">
                Apply
            </button>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                        </th>
                        <th>Staff Member</th>
                        <th>Designation</th>
                        <th>Warehouse</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($staffs as $staff)
                        @php
                            $search = request('search');
                            $isMatch = $search && (
                                stripos($staff->name, $search) !== false || 
                                stripos($staff->email, $search) !== false ||
                                stripos($staff->phone, $search) !== false
                            );
                        @endphp
                        <tr class="{{ $isMatch ? 'table-warning' : '' }}">
                            <td>
                                <input type="checkbox" class="form-check-input staff-checkbox" value="{{ $staff->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @php
                                        $avatarUrl = $staff->avatar;
                                        if ($avatarUrl && !str_starts_with($avatarUrl, '/storage/') && !str_starts_with($avatarUrl, 'http')) {
                                            $avatarUrl = '/storage/' . $avatarUrl;
                                        }
                                    @endphp
                                    @if($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $staff->name }}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                            <i class="bi bi-person text-white"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-medium">{{ $staff->name }}</div>
                                        <div class="small text-muted">{{ $staff->email }}</div>
                                        @if($staff->phone)
                                            <div class="small text-muted">{{ $staff->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $staff->designation ?? 'N/A' }}</td>
                            <td>
                                @if($staff->warehouse)
                                    <span class="badge bg-info">{{ $staff->warehouse->name }}</span>
                                @else
                                    <span class="text-muted">Not Assigned</span>
                                @endif
                            </td>
                            <td>
                                @if($staff->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($staff->status === 'inactive')
                                    <span class="badge bg-secondary">Inactive</span>
                                @else
                                    <span class="badge bg-danger">Banned</span>
                                @endif
                            </td>
                            <td>{{ $staff->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.staffs.edit', $staff->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger" title="Delete" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this staff member?')) { document.getElementById('delete-form-{{ $staff->id }}').submit(); }">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                                <form id="delete-form-{{ $staff->id }}" action="{{ route('admin.staffs.destroy', $staff->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mb-2 mt-2">No warehouse staff found</p>
                                <a href="{{ route('admin.staffs.create') }}" class="btn btn-sm btn-primary mt-1">
                                    <i class="bi bi-plus-lg me-1"></i> Add First Staff
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($staffs->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $staffs->firstItem() }} - {{ $staffs->lastItem() }} of {{ $staffs->total() }} staffs
            </div>
            <div>
                {{ $staffs->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.staffs.bulk-action') }}" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="staff_ids" id="bulkIdsInput">
</form>

<!-- Access Denied Modal -->
<div class="modal fade" id="accessDeniedModal" tabindex="-1" aria-labelledby="accessDeniedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accessDeniedModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Access Denied
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-shield-lock text-muted" style="font-size: 3rem;"></i>
                <p class="mt-3 mb-0">Staff members cannot create other staff members.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Show access denied modal for staff users
    function showAccessDenied() {
        var modal = new bootstrap.Modal(document.getElementById('accessDeniedModal'));
        modal.show();
    }

    // Live search with debounce
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const filterForm = document.getElementById('filterForm');
    const tableBody = document.getElementById('tableBody');

    function fetchResults() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchSpinner.style.display = 'block';

            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);

            fetch('{{ route("admin.staffs.warehouse") }}?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = data.html;

                // Update stats
                if (data.stats) {
                    document.querySelectorAll('.stat-card-value')[0].textContent = data.stats.total;
                    document.querySelectorAll('.stat-card-value')[1].textContent = data.stats.active;
                    document.querySelectorAll('.stat-card-value')[2].textContent = data.stats.inactive;
                }

                // Update pagination
                const paginationContainer = document.querySelector('.card-footer');
                if (paginationContainer && data.pagination) {
                    const showingText = data.showing_text || '';
                    paginationContainer.innerHTML = `
                        <div class="text-muted small">${showingText}</div>
                        <div>${data.pagination}</div>
                    `;
                }

                // Reinitialize checkbox listeners
                initCheckboxListeners();

                // Update URL
                const newUrl = '{{ route("admin.staffs.warehouse") }}?' + params.toString();
                window.history.pushState({}, '', newUrl);

                searchSpinner.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                searchSpinner.style.display = 'none';
            });
        }, 300);
    }

    // Search input listener
    searchInput.addEventListener('input', fetchResults);

    // Filter listeners
    document.getElementById('filterStatus').addEventListener('change', fetchResults);
    document.getElementById('filterWarehouse').addEventListener('change', fetchResults);
    document.getElementById('perPage').addEventListener('change', fetchResults);

    // Bulk Actions
    const selectedItems = new Set();

    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selectedCount').textContent = count + ' selected';
        document.getElementById('bulkActionsCard').style.display = count > 0 ? 'block' : 'none';
    }

    function initCheckboxListeners() {
        // Select all
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = tableBody.querySelectorAll('.staff-checkbox');
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
        }

        // Individual checkboxes
        tableBody.querySelectorAll('.staff-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    selectedItems.add(this.value);
                } else {
                    selectedItems.delete(this.value);
                }
                
                // Update select all checkbox
                const allCheckboxes = tableBody.querySelectorAll('.staff-checkbox');
                const checkedCount = tableBody.querySelectorAll('.staff-checkbox:checked').length;
                const selectAll = document.getElementById('selectAllCheckbox');
                if (selectAll) {
                    selectAll.checked = checkedCount === allCheckboxes.length;
                }
                
                updateBulkActions();
            });
        });
    }

    // Initialize checkboxes on page load
    document.addEventListener('DOMContentLoaded', initCheckboxListeners);

    // Bulk action apply
    document.getElementById('applyBulkAction').addEventListener('click', function() {
        const action = document.getElementById('bulkActionSelect').value;
        if (!action) {
            alert('Please select an action');
            return;
        }
        if (selectedItems.size === 0) {
            alert('Please select at least one staff member');
            return;
        }

        const confirmMessages = {
            'activate': 'Are you sure you want to activate the selected staff members?',
            'deactivate': 'Are you sure you want to deactivate the selected staff members?',
            'delete': 'Are you sure you want to delete the selected staff members? This action cannot be undone.'
        };

        if (!confirm(confirmMessages[action])) return;

        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
        document.getElementById('bulkActionForm').submit();
    });
</script>
@endpush
