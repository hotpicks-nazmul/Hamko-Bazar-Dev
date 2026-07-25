@extends('admin.layouts.app')

@section('title', 'Delivery Boys')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-person-badge me-2"></i>Delivery Boys</h4>
        <p class="text-muted mb-0 d-none d-md-block">Manage delivery personnel and assignments</p>
    </div>
    <a href="{{ route('admin.delivery.delivery-boys.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i><span class="d-none d-sm-inline">Add Delivery Boy</span>
    </a>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4" id="statsCards">
    <div class="col">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Total</span>
                <span class="stat-card-value" id="statTotal">{{ $stats['total'] }}</span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Active</span>
                <span class="stat-card-value" id="statActive">{{ $stats['active'] }}</span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon"><i class="bi bi-bicycle"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">Available</span>
                <span class="stat-card-value" id="statAvailable">{{ $stats['available'] }}</span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">On Delivery</span>
                <span class="stat-card-value" id="statOnDelivery">{{ $stats['on_delivery'] }}</span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card stat-card-secondary">
            <div class="stat-card-icon"><i class="bi bi-calendar-minus-fill"></i></div>
            <div class="stat-card-content">
                <span class="stat-card-label">On Leave</span>
                <span class="stat-card-value" id="statOnLeave">{{ $stats['on_leave'] }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search -->
                <div class="col-6 col-lg-3 col-md-4">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Search..." value="{{ $search }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-6 col-lg-2 col-md-3">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="on_leave" {{ $status == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="suspended" {{ $status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

                <!-- Zone Filter -->
                <div class="col-6 col-lg-2 col-md-3">
                    <label class="form-label small text-muted">Zone</label>
                    <select name="zone" id="filterZone" class="form-select form-select-sm">
                        <option value="">All Zones</option>
                        @foreach($zones as $z)
                            <option value="{{ $z->id }}" {{ $z->id == $zone ? 'selected' : '' }}>{{ $z->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Availability Filter -->
                <div class="col-6 col-lg-2 col-md-3">
                    <label class="form-label small text-muted">Availability</label>
                    <select name="availability" id="filterAvailability" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" {{ $availability === '1' ? 'selected' : '' }}>Available</option>
                        <option value="0" {{ $availability === '0' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="col-12 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-funnel me-1"></i><span class="d-none d-sm-inline">Filter</span>
                    </button>
                    <a href="{{ route('admin.delivery.delivery-boys.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i><span class="d-none d-sm-inline">Clear</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <!-- Bulk Actions Bar -->
        <div id="bulkActionsBar" style="display: none;" class="border-bottom">
            <div class="p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <span class="text-muted"><span id="selectedCount">0</span> selected</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="clearSelection()">
                            Clear Selection
                        </button>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('activate')">
                            <i class="bi bi-check-circle me-1"></i> Activate
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('deactivate')">
                            <i class="bi bi-x-circle me-1"></i> Deactivate
                        </button>
                        <button type="button" class="btn btn-sm btn-info" onclick="bulkAction('available')">
                            <i class="bi bi-bicycle me-1"></i> Available
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="bulkAction('unavailable')">
                            <i class="bi bi-pause-circle me-1"></i> Unavailable
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <form id="bulkActionForm" method="POST" action="{{ route('admin.delivery.delivery-boys.bulk-action') }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                            </th>
                            <th>Delivery Boy</th>
                            <th>Contact</th>
                            <th>Zone</th>
                            <th>Vehicle</th>
                            <th>Performance</th>
                            <th>Status</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($deliveryBoys as $boy)
                            @php
                                $searchTerm = request('search');
                                $isMatch = $searchTerm && (
                                    stripos($boy->name, $searchTerm) !== false || 
                                    stripos($boy->phone, $searchTerm) !== false ||
                                    stripos($boy->email ?? '', $searchTerm) !== false
                                );
                            @endphp
                            <tr class="{{ $isMatch ? 'table-warning' : '' }}">
                                <td>
                                    <input type="checkbox" name="delivery_boys[]" value="{{ $boy->id }}" class="form-check-input row-checkbox">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $boy->photo_url }}" alt="{{ $boy->name }}" 
                                             class="rounded-circle me-3" width="45" height="45" 
                                             onerror="this.src='{{ asset('images/default-delivery-boy.png') }}'">
                                        <div>
                                            <h6 class="mb-0">{{ $boy->name }}</h6>
                                            <small class="text-muted">ID: {{ $boy->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div><i class="bi bi-phone me-1 text-muted"></i> {{ $boy->phone }}</div>
                                        @if($boy->email)
                                            <div><i class="bi bi-envelope me-1 text-muted"></i> {{ $boy->email }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($boy->zone)
                                        <span class="badge bg-info">{{ $boy->zone->name }}</span>
                                    @else
                                        <span class="text-muted">Not Assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($boy->vehicle_type)
                                        <div>
                                            <span class="badge bg-secondary">{{ $boy->vehicle_type_label }}</span>
                                            @if($boy->vehicle_number)
                                                <small class="d-block text-muted">{{ $boy->vehicle_number }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Not Set</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="text-muted me-2">Success Rate:</span>
                                            <span class="fw-bold {{ $boy->success_rate >= 90 ? 'text-success' : ($boy->success_rate >= 70 ? 'text-warning' : 'text-danger') }}">
                                                {{ $boy->success_rate }}%
                                            </span>
                                        </div>
                                        <div class="text-muted">
                                            {{ $boy->successful_deliveries }}/{{ $boy->total_deliveries }} deliveries
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        {!! $boy->status_label !!}
                                        @if($boy->is_active)
                                            @if($boy->is_available)
                                                <span class="badge bg-success ms-1">Available</span>
                                            @else
                                                <span class="badge bg-secondary ms-1">Busy</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.delivery.delivery-boys.edit', $boy->id) }}" 
                                           class="btn btn-sm btn-light" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-light toggle-availability" 
                                                data-id="{{ $boy->id }}" 
                                                data-available="{{ $boy->is_available ? 0 : 1 }}"
                                                title="{{ $boy->is_available ? 'Mark Unavailable' : 'Mark Available' }}">
                                            <i class="bi {{ $boy->is_available ? 'bi-bicycle' : 'bi-pause-circle' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light toggle-status" 
                                                data-id="{{ $boy->id }}" 
                                                data-active="{{ $boy->is_active ? 0 : 1 }}"
                                                title="{{ $boy->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi {{ $boy->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light text-danger" 
                                                onclick="deleteDeliveryBoy({{ $boy->id }})" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-person-badge text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mb-2 mt-2">No delivery boys found</p>
                                    <a href="{{ route('admin.delivery.delivery-boys.create') }}" class="btn btn-sm btn-primary mt-1">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Delivery Boy
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions - Hidden Inputs -->
            <input type="hidden" name="action" id="bulkActionInput" value="">
            <input type="hidden" name="delivery_boys" id="bulkIdsInput" value="">
        </form>

        <!-- Pagination -->
        @if($deliveryBoys->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <div class="text-muted small">
                Showing {{ $deliveryBoys->firstItem() }} - {{ $deliveryBoys->lastItem() }} of {{ $deliveryBoys->total() }} items
            </div>
            <div id="pagination">
                {{ $deliveryBoys->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this delivery boy? This action cannot be undone.</p>
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

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Select All Checkbox
    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });

    // Update selected count
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        const count = checkboxes.length;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    }

    // Add event listeners to individual checkboxes
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Clear Selection
    function clearSelection() {
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('selectAllCheckbox').checked = false;
        updateSelectedCount();
    }

    // Bulk Action
    function bulkAction(action) {
        const selectedItems = document.querySelectorAll('.row-checkbox:checked');
        
        if (selectedItems.length === 0) {
            alert('Please select at least one delivery boy.');
            return;
        }
        
        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete the selected delivery boys?')) {
                return;
            }
        }
        
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(
            Array.from(selectedItems).map(cb => cb.value)
        );
        document.getElementById('bulkActionForm').submit();
    }

    // Delete Delivery Boy
    function deleteDeliveryBoy(id) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route('admin.delivery.delivery-boys.destroy', '') }}/' + id;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    // Toggle Availability (AJAX)
    document.querySelectorAll('.toggle-availability').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const available = this.dataset.available;
            const icon = this.querySelector('i');
            
            fetch(`{{ route('admin.delivery.delivery-boys.toggle-availability', '') }}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.dataset.available = available;
                    icon.className = available == 1 ? 'bi bi-bicycle' : 'bi bi-pause-circle';
                    this.title = available == 1 ? 'Mark Unavailable' : 'Mark Available';
                    
                    // Update badge in status column
                    const row = this.closest('tr');
                    const statusCell = row.cells[6];
                    const busyBadge = statusCell.querySelector('.badge.bg-success, .badge.bg-secondary');
                    if (busyBadge) {
                        if (available == 1) {
                            busyBadge.className = 'badge bg-success ms-1';
                            busyBadge.textContent = 'Available';
                        } else {
                            busyBadge.className = 'badge bg-secondary ms-1';
                            busyBadge.textContent = 'Busy';
                        }
                    }
                }
            });
        });
    });

    // Toggle Status (AJAX)
    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const active = this.dataset.active;
            const icon = this.querySelector('i');
            
            fetch(`{{ route('admin.delivery.delivery-boys.toggle-status', '') }}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.dataset.active = active;
                    icon.className = active == 1 ? 'bi bi-toggle-on' : 'bi bi-toggle-off';
                    this.title = active == 1 ? 'Deactivate' : 'Activate';
                }
            });
        });
    });

    // Live Search with AJAX
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchSpinner.style.display = 'block';
        
        searchTimeout = setTimeout(() => {
            performLiveSearch();
        }, 300);
    });

    // Filter dropdowns trigger search on change
    ['filterStatus', 'filterZone', 'filterAvailability'].forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                performLiveSearch();
            });
        }
    });

    function performLiveSearch() {
        const params = new URLSearchParams();
        
        const searchTerm = searchInput.value.trim();
        if (searchTerm) params.set('search', searchTerm);
        
        const status = document.getElementById('filterStatus').value;
        if (status) params.set('status', status);
        
        const zone = document.getElementById('filterZone').value;
        if (zone) params.set('zone', zone);
        
        const availability = document.getElementById('filterAvailability').value;
        if (availability) params.set('availability', availability);
        
        // Keep existing sort and per_page
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
        if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
        
        // AJAX request
        fetch(`{{ route('admin.delivery.delivery-boys.index') }}?${params.toString()}&ajax=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            searchSpinner.style.display = 'none';
            
            if (data.html) {
                // Update table body
                document.querySelector('#tableBody').innerHTML = data.html;
                
                // Update stats
                if (data.stats) {
                    document.getElementById('statTotal').textContent = data.stats.total || 0;
                    document.getElementById('statActive').textContent = data.stats.active || 0;
                    document.getElementById('statAvailable').textContent = data.stats.available || 0;
                    document.getElementById('statOnDelivery').textContent = data.stats.on_delivery || 0;
                    document.getElementById('statOnLeave').textContent = data.stats.on_leave || 0;
                }
                
                // Update pagination
                if (data.pagination) {
                    document.getElementById('pagination').innerHTML = data.pagination;
                }
                
                // Update URL without reload
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
                
                // Re-attach event listeners to new elements
                attachRowEventListeners();
            }
        })
        .catch(err => {
            searchSpinner.style.display = 'none';
            console.error('Search error:', err);
        });
    }

    function attachRowEventListeners() {
        // Re-attach checkbox listeners
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });
        
        // Re-attach toggle availability
        document.querySelectorAll('.toggle-availability').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const available = this.dataset.available;
                const icon = this.querySelector('i');
                
                fetch(`{{ route('admin.delivery.delivery-boys.toggle-availability', '') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.dataset.available = available;
                        icon.className = available == 1 ? 'bi bi-bicycle' : 'bi bi-pause-circle';
                        this.title = available == 1 ? 'Mark Unavailable' : 'Mark Available';
                    }
                });
            });
        });
        
        // Re-attach toggle status
        document.querySelectorAll('.toggle-status').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const active = this.dataset.active;
                const icon = this.querySelector('i');
                
                fetch(`{{ route('admin.delivery.delivery-boys.toggle-status', '') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.dataset.active = active;
                        icon.className = active == 1 ? 'bi bi-toggle-on' : 'bi bi-toggle-off';
                        this.title = active == 1 ? 'Deactivate' : 'Activate';
                    }
                });
            });
        });
    }
</script>
@endpush
