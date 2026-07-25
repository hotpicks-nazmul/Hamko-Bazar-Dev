@extends('admin.layouts.app')

@section('title', 'FAQs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">FAQs</h4>
    <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New FAQ
    </a>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-question-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total FAQs</span>
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
</div>

<!-- Filters Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Search Input -->
                        <div class="col-lg-4 col-md-4 col-sm-6">
                            <label class="form-label small text-muted">Search</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" id="liveSearch" class="form-control" 
                                       placeholder="Search FAQs..." value="{{ request('search') }}">
                                <span class="input-group-text" id="searchSpinner" style="display: none;">
                                    <div class="spinner-border spinner-border-sm"></div>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Status Filter -->
                        <div class="col-lg-3 col-md-3 col-sm-6">
                            <label class="form-label small text-muted">Status</label>
                            <select name="status" id="filterStatus" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        
                        <!-- Reset Button -->
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('admin.faqs.index') }}" class="btn btn-sm btn-outline-secondary">
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
                            <i class="bi bi-x-circle me-1"></i> Deactivate
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </div>
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
                                <th>Question</th>
                                <th>Answer</th>
                                <th>Status</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @include('admin.faqs.partials.table-rows', ['faqs' => $faqs])
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($faqs->hasPages())
                <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">
                        Showing {{ $faqs->firstItem() }} - {{ $faqs->lastItem() }} of {{ $faqs->total() }} FAQs
                    </div>
                    <div>
                        {{ $faqs->appends(request()->query())->links() }}
                    </div>
                </div>
                @endif
    </div>
</div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.faqs.bulk-action') }}">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ids" id="bulkIdsInput">
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
    const filterStatus = document.getElementById('filterStatus');
    if (filterStatus) {
        filterStatus.addEventListener('change', function() {
            performLiveSearch(searchInput.value.trim());
        });
    }

    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();
        
        if (searchTerm) params.set('search', searchTerm);
        
        const status = document.getElementById('filterStatus').value;
        if (status) params.set('status', status);
        
        fetch(`{{ route('admin.faqs.index') }}?${params.toString()}`, {
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
                
                // Update stats
                if (data.stats) {
                    const statCards = document.querySelectorAll('.stat-card-row .stat-card');
                    if (statCards.length >= 3) {
                        statCards[0].querySelector('.stat-card-value').textContent = data.stats.total.toLocaleString();
                        statCards[1].querySelector('.stat-card-value').textContent = data.stats.active.toLocaleString();
                        statCards[2].querySelector('.stat-card-value').textContent = data.stats.inactive.toLocaleString();
                    }
                }
                
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
        })
        .catch(error => {
            searchSpinner.style.display = 'none';
            console.error('Search error:', error);
            // Fallback to regular page load
            document.getElementById('filterForm').submit();
        });
    }

    // Select all checkbox
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
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

    // Individual checkbox
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-checkbox')) {
            if (e.target.checked) {
                selectedItems.add(e.target.value);
            } else {
                selectedItems.delete(e.target.value);
            }
            
            // Update select all checkbox
            const allCheckboxes = document.querySelectorAll('.item-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
            selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
            
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
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = false);
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        updateBulkActions();
    }

    function bulkAction(action) {
        if (selectedItems.size === 0) {
            alert('Please select at least one FAQ.');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${selectedItems.size} FAQ(s)?`)) return;
        
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
        document.getElementById('bulkActionForm').submit();
    }

    // Toggle status
    function toggleStatus(url, faqId) {
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update the status badge
                const row = document.querySelector(`tr[data-id="${faqId}"]`);
                if (row) {
                    const badge = row.querySelector('.status-badge');
                    if (badge) {
                        badge.outerHTML = data.badge;
                    }
                    // Update the toggle button icon and title
                    const toggleBtn = row.querySelector('[title="Deactivate"], [title="Activate"]');
                    if (toggleBtn) {
                        const isActive = data.status === 'active';
                        toggleBtn.title = isActive ? 'Deactivate' : 'Activate';
                        toggleBtn.className = `btn btn-sm btn-outline${isActive ? '-warning' : '-success'}`;
                        const icon = toggleBtn.querySelector('i');
                        if (icon) {
                            icon.className = `bi bi${isActive ? '-x-circle' : '-check-circle'}`;
                        }
                    }
                }
                
                // Show success message
                showToast(data.message, 'success');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }

    function showToast(message, type) {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : '#dc3545'};
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
</script>
<style>
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>
@endpush
