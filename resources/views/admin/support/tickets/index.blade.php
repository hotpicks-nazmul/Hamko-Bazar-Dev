@extends('admin.layouts.app')

@section('title', 'Support Tickets')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-ticket-detailed"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total</span>
            <span class="stat-card-value">{{ number_format($stats['total'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-envelope-open"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Open</span>
            <span class="stat-card-value">{{ number_format($stats['open'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Pending</span>
            <span class="stat-card-value">{{ number_format($stats['pending'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Answered</span>
            <span class="stat-card-value">{{ number_format($stats['answered'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-check2-all"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Solved</span>
            <span class="stat-card-value">{{ number_format($stats['solved'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Closed</span>
            <span class="stat-card-value">{{ number_format($stats['closed'] ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Support Tickets</h4>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control"
                               placeholder="Ticket #, Subject, Customer..." value="{{ request('search') }}">
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
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Answered</option>
                        <option value="solved" {{ request('status') === 'solved' ? 'selected' : '' }}>Solved</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Priority</label>
                    <select name="priority" id="filterPriority" class="form-select form-select-sm">
                        <option value="">All Priority</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Category</label>
                    <select name="category" id="filterCategory" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <option value="general" {{ request('category') === 'general' ? 'selected' : '' }}>General</option>
                        <option value="order" {{ request('category') === 'order' ? 'selected' : '' }}>Order</option>
                        <option value="payment" {{ request('category') === 'payment' ? 'selected' : '' }}>Payment</option>
                        <option value="shipping" {{ request('category') === 'shipping' ? 'selected' : '' }}>Shipping</option>
                        <option value="return" {{ request('category') === 'return' ? 'selected' : '' }}>Return</option>
                        <option value="refund" {{ request('category') === 'refund' ? 'selected' : '' }}>Refund</option>
                        <option value="technical" {{ request('category') === 'technical' ? 'selected' : '' }}>Technical</option>
                        <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="col-lg-1 col-md-2 col-sm-6">
                    <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-sm btn-outline-secondary w-100">
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
                <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('close')">
                    <i class="bi bi-x-circle me-1"></i> Close
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
                        <th>Ticket</th>
                        <th>Customer</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @php
                        $search = request('search');
                    @endphp
                    @forelse($tickets as $ticket)
                    @php
                        $isMatch = $search && (
                            stripos($ticket->ticket_number, $search) !== false ||
                            stripos($ticket->subject, $search) !== false ||
                            stripos($ticket->user->name ?? '', $search) !== false ||
                            stripos($ticket->user->email ?? '', $search) !== false
                        );
                    @endphp
                    <tr class="{{ $isMatch ? 'table-warning' : '' }}">
                        <td>
                            <input type="checkbox" class="form-check-input ticket-checkbox" value="{{ $ticket->id }}">
                        </td>
                        <td>
                            <div class="fw-medium">{{ $ticket->ticket_number }}</div>
                            <div class="small text-muted text-truncate" style="max-width: 200px;">
                                {{ $ticket->subject }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium">{{ $ticket->user->name ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $ticket->user->email ?? '' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ ucfirst($ticket->category) }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $ticket->getPriorityBadgeClass() }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $ticket->getStatusBadgeClass() }}">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </td>
                        <td>
                            @if($ticket->assignedTo)
                                <div class="small">{{ $ticket->assignedTo->name }}</div>
                            @else
                                <span class="text-muted small">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">{{ $ticket->created_at->format('d M Y') }}</div>
                            <div class="small text-muted">{{ $ticket->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            <a href="{{ route('admin.support.tickets.show', $ticket->id) }}"
                               class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="deleteTicket({{ $ticket->id }})" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-ticket-detailed text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No tickets found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($tickets->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} of {{ $tickets->total() }} tickets
            </div>
            <div>
                {{ $tickets->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Hidden form for bulk actions -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.support.tickets.bulk-action') }}" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ticket_ids" id="bulkIdsInput">
</form>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this ticket? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Debounced live search
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

    // Filter dropdowns trigger search on change
    const filterSelects = ['filterStatus', 'filterPriority', 'filterCategory'];
    filterSelects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                performLiveSearch(searchInput.value.trim());
            });
        }
    });

    // Live search function
    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();
        
        if (searchTerm) params.set('search', searchTerm);
        
        const status = document.getElementById('filterStatus').value;
        if (status) params.set('status', status);
        
        const priority = document.getElementById('filterPriority').value;
        if (priority) params.set('priority', priority);
        
        const category = document.getElementById('filterCategory').value;
        if (category) params.set('category', category);
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
        
        fetch(`{{ route('admin.support.tickets.index') }}?${params.toString()}&ajax=1`, {
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
        .catch(error => {
            searchSpinner.style.display = 'none';
            console.error('Search error:', error);
        });
    }

    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const ticketCheckboxes = document.querySelectorAll('.ticket-checkbox');
    let selectedItems = new Set();

    selectAllCheckbox.addEventListener('change', function() {
        ticketCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            if (this.checked) {
                selectedItems.add(checkbox.value);
            } else {
                selectedItems.delete(checkbox.value);
            }
        });
        updateBulkActions();
    });

    ticketCheckboxes.forEach(checkbox => {
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
        
        // Update select all checkbox state
        const totalCheckboxes = ticketCheckboxes.length;
        const checkedCheckboxes = document.querySelectorAll('.ticket-checkbox:checked').length;
        selectAllCheckbox.checked = totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes;
        selectAllCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
    }

    function clearSelection() {
        selectedItems.clear();
        ticketCheckboxes.forEach(checkbox => checkbox.checked = false);
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        updateBulkActions();
    }

    function bulkAction(action) {
        if (selectedItems.size === 0) {
            alert('Please select at least one ticket.');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${selectedItems.size} ticket(s)?`)) return;
        
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
        document.getElementById('bulkActionForm').submit();
    }

    function deleteTicket(id) {
        if (!confirm('Are you sure you want to delete this ticket?')) return;
        
        const form = document.getElementById('deleteForm');
        form.action = `{{ route('admin.support.tickets.destroy', '') }}/` + id;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
</script>
@endpush
