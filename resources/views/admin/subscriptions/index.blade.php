@extends('admin.layouts.app')

@section('title', 'Subscriptions')

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Subscriptions</h4>
            <p class="text-muted mb-0">Manage recurring subscriptions and billing cycles</p>
        </div>
        <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Create Subscription
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="stat-card-row mb-4">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="bi bi-arrow-repeat"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Total</span><span class="stat-card-value" id="statTotal">{{ $stats['total'] ?? 0 }}</span></div>
        </div>
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Active</span><span class="stat-card-value" id="statActive">{{ $stats['active'] ?? 0 }}</span></div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Pending</span><span class="stat-card-value" id="statPending">{{ $stats['pending'] ?? 0 }}</span></div>
        </div>
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon"><i class="bi bi-pause-circle"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Paused</span><span class="stat-card-value" id="statPaused">{{ $stats['paused'] ?? 0 }}</span></div>
        </div>
        <div class="stat-card stat-card-danger">
            <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Cancelled</span><span class="stat-card-value" id="statCancelled">{{ $stats['cancelled'] ?? 0 }}</span></div>
        </div>
        <div class="stat-card stat-card-secondary">
            <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-card-content"><span class="stat-card-label">Due for Billing</span><span class="stat-card-value" id="statDue">{{ $stats['due_for_billing'] ?? 0 }}</span></div>
        </div>
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
                                   placeholder="Subscription #, Customer..." value="{{ request('search') }}">
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
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Billing Frequency</label>
                        <select name="billing_frequency" id="filterFrequency" class="form-select form-select-sm">
                            <option value="">All Frequencies</option>
                            <option value="weekly" {{ request('billing_frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="bi_weekly" {{ request('billing_frequency') === 'bi_weekly' ? 'selected' : '' }}>Bi-Weekly</option>
                            <option value="monthly" {{ request('billing_frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ request('billing_frequency') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="semi_annually" {{ request('billing_frequency') === 'semi_annually' ? 'selected' : '' }}>Semi-Annually</option>
                            <option value="annually" {{ request('billing_frequency') === 'annually' ? 'selected' : '' }}>Annually</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Payment Status</label>
                        <select name="payment_status" id="filterPaymentStatus" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Date From</label>
                        <input type="date" name="date_from" id="filterDateFrom" class="form-control form-select-sm" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Date To</label>
                        <input type="date" name="date_to" id="filterDateTo" class="form-control form-select-sm" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-lg-1 col-md-2 col-sm-6">
                        <label class="form-label small text-muted">Per Page</label>
                        <select name="per_page" id="filterPerPage" class="form-select form-select-sm">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-2 col-sm-6">
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-outline-secondary w-100 mt-4">
                            <i class="bi bi-x-lg"></i> Reset
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
                        <i class="bi bi-play-circle me-1"></i> Activate
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('pause')">
                        <i class="bi bi-pause-circle me-1"></i> Pause
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('cancel')">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="bulkAction('delete')">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscriptions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleSelectAll()">
                            </th>
                            <th>
                                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['sort' => 'subscription_number', 'direction' => request('sort') == 'subscription_number' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Subscription
                                    @if(request('sort') == 'subscription_number')
                                        <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>
                                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['sort' => 'billing_frequency', 'direction' => request('sort') == 'billing_frequency' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Frequency
                                    @if(request('sort') == 'billing_frequency')
                                        <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['sort' => 'next_billing_date', 'direction' => request('sort') == 'next_billing_date' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Next Billing
                                    @if(request('sort') == 'next_billing_date')
                                        <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Status
                                    @if(request('sort') == 'status')
                                        <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Payment</th>
                            <th>
                                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['sort' => 'total_price', 'direction' => request('sort') == 'total_price' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Total
                                    @if(request('sort') == 'total_price')
                                        <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($subscriptions as $subscription)
                        @php
                            $search = request('search');
                            $isMatch = $search && (
                                stripos($subscription->subscription_number, $search) !== false ||
                                stripos($subscription->shipping_first_name, $search) !== false ||
                                stripos($subscription->shipping_last_name, $search) !== false ||
                                stripos($subscription->shipping_email, $search) !== false ||
                                stripos($subscription->plan_name, $search) !== false
                            );
                        @endphp
                        <tr class="{{ $isMatch ? 'table-warning' : '' }}">
                            <td>
                                <input type="checkbox" class="form-check-input row-checkbox" 
                                       value="{{ $subscription->id }}" onclick="updateBulkActions()">
                            </td>
                            <td>
                                <div class="fw-medium">{{ $subscription->subscription_number }}</div>
                                <small class="text-muted">{{ $subscription->plan_name }}</small>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $subscription->shipping_full_name }}</div>
                                <small class="text-muted">{{ $subscription->shipping_email }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @php
                                        $imageUrl = $subscription->product->featured_image ?? null;
                                        if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                            $imageUrl = '/storage/' . $imageUrl;
                                        }
                                    @endphp
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                            <i class="bi bi-image text-white"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-medium small">{{ Str::limit($subscription->product->name ?? 'N/A', 30) }}</div>
                                        <small class="text-muted">Qty: {{ $subscription->quantity }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $subscription->billing_frequency_label }}
                                </span>
                            </td>
                            <td>
                                @if($subscription->next_billing_date)
                                    <div class="fw-medium">{{ $subscription->next_billing_date->format('M d, Y') }}</div>
                                    @if($subscription->next_billing_date->isPast())
                                        <small class="text-danger">Overdue</small>
                                    @else
                                        <small class="text-muted">{{ $subscription->next_billing_date->diffForHumans() }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $subscription->status_badge_class }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $subscription->payment_status_badge_class }}">
                                    {{ ucfirst($subscription->payment_status) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-medium">৳{{ number_format($subscription->total_price, 2) }}</div>
                                @if(!$subscription->hasUnlimitedCycles())
                                    <small class="text-muted">{{ $subscription->completed_billing_cycles }}/{{ $subscription->total_billing_cycles }} cycles</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">No subscriptions found</p>
                                <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-sm btn-primary mt-1">
                                    <i class="bi bi-plus-lg me-1"></i> Create First Subscription
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($subscriptions->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $subscriptions->firstItem() }} - {{ $subscriptions->lastItem() }} of {{ $subscriptions->total() }} subscriptions
            </div>
            <div>
                {{ $subscriptions->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.subscriptions.bulk-action') }}" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <input type="hidden" name="ids" id="bulkIdsInput">
</form>
@endsection

@push('styles')
<style>
    /* Table action button styling */
    .btn-group .btn {
        border-radius: 0.375rem;
    }
    .btn-group .btn + .btn {
        margin-left: -1px;
    }
</style>
@endpush

@push('scripts')
<script>
let selectedItems = new Set();
let searchTimeout;

// Live search with debounce
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
const filterSelects = ['filterStatus', 'filterFrequency', 'filterPaymentStatus', 'filterPerPage'];
filterSelects.forEach(id => {
    const select = document.getElementById(id);
    if (select) {
        select.addEventListener('change', function() {
            performLiveSearch(searchInput.value.trim());
        });
    }
});

// Date filters trigger search on change
const filterDateFrom = document.getElementById('filterDateFrom');
if (filterDateFrom) {
    filterDateFrom.addEventListener('change', function() {
        performLiveSearch(searchInput.value.trim());
    });
}

const filterDateTo = document.getElementById('filterDateTo');
if (filterDateTo) {
    filterDateTo.addEventListener('change', function() {
        performLiveSearch(searchInput.value.trim());
    });
}

// Live search function
function performLiveSearch(searchTerm) {
    const params = new URLSearchParams();
    
    if (searchTerm) params.set('search', searchTerm);
    
    const status = document.getElementById('filterStatus').value;
    if (status) params.set('status', status);
    
    const frequency = document.getElementById('filterFrequency').value;
    if (frequency) params.set('billing_frequency', frequency);
    
    const paymentStatus = document.getElementById('filterPaymentStatus').value;
    if (paymentStatus) params.set('payment_status', paymentStatus);
    
    const dateFrom = document.getElementById('filterDateFrom').value;
    if (dateFrom) params.set('date_from', dateFrom);
    
    const dateTo = document.getElementById('filterDateTo').value;
    if (dateTo) params.set('date_to', dateTo);
    
    const perPage = document.getElementById('filterPerPage').value;
    if (perPage) params.set('per_page', perPage);
    
    // Keep existing sort
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
    
    fetch(`{{ route('admin.subscriptions.index') }}?${params.toString()}&ajax=1`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        searchSpinner.style.display = 'none';
        
        if (data.html) {
            document.getElementById('tableBody').innerHTML = data.html;
            
            // Re-attach checkbox listeners
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.addEventListener('change', updateBulkActions);
                if (selectedItems.has(cb.value)) {
                    cb.checked = true;
                }
            });
            
            // Update stats
            if (data.stats) {
                document.getElementById('statTotal').textContent = data.stats.total ?? 0;
                document.getElementById('statActive').textContent = data.stats.active ?? 0;
                document.getElementById('statPending').textContent = data.stats.pending ?? 0;
                document.getElementById('statPaused').textContent = data.stats.paused ?? 0;
                document.getElementById('statCancelled').textContent = data.stats.cancelled ?? 0;
                document.getElementById('statDue').textContent = data.stats.due_for_billing ?? 0;
            }
            
            // Update URL without reload
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({}, '', newUrl);
        }
    })
    .catch(() => {
        searchSpinner.style.display = 'none';
    });
}

// Bulk actions
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
        if (selectAll.checked) {
            selectedItems.add(cb.value);
        } else {
            selectedItems.delete(cb.value);
        }
    });
    
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    selectedItems.clear();
    
    checkboxes.forEach(cb => {
        if (cb.checked) {
            selectedItems.add(cb.value);
        }
    });
    
    const count = selectedItems.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    
    // Update select all checkbox
    const selectAll = document.getElementById('selectAll');
    selectAll.checked = count > 0 && count === checkboxes.length;
}

function clearSelection() {
    selectedItems.clear();
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

function bulkAction(action) {
    if (selectedItems.size === 0) {
        alert('Please select at least one subscription.');
        return;
    }
    
    const actionNames = {
        'activate': 'activate',
        'pause': 'pause',
        'cancel': 'cancel',
        'delete': 'delete'
    };
    
    if (!confirm(`Are you sure you want to ${actionNames[action]} ${selectedItems.size} subscription(s)?`)) return;
    
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
    document.getElementById('bulkActionForm').submit();
}
</script>
@endpush