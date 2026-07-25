@extends('admin.layouts.app')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Loyalty Points</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.customers.loyalty.settings') }}" class="btn btn-outline-primary">
                    <i class="bi bi-gear me-1"></i> Settings
                </a>
                <a href="{{ route('admin.customers.loyalty.export') }}" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i> Export
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-people"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Customers</span>
            <span class="stat-card-value">{{ number_format($stats['total_customers']) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active Members</span>
            <span class="stat-card-value">{{ number_format($stats['active_customers']) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-star"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Points</span>
            <span class="stat-card-value">{{ number_format($stats['total_points']) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-arrow-down-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Points Spent</span>
            <span class="stat-card-value">{{ number_format($stats['total_points_spent']) }}</span>
        </div>
    </div>
</div>
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Spent</span>
            <span class="stat-card-value">${{ number_format($stats['total_spent'], 2) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-calculator"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Avg. Points/Customer</span>
            <span class="stat-card-value">
                @if($stats['active_customers'] > 0)
                    {{ number_format(round($stats['total_points'] / $stats['active_customers'])) }}
                @else
                    0
                @endif
            </span>
        </div>
    </div>
</div>

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
                                       placeholder="Name, Email, Phone..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Min Points Filter -->
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label small text-muted">Min Points</label>
                            <input type="number" name="min_points" class="form-control form-control-sm" 
                                   placeholder="Min" value="{{ request('min_points') }}">
                        </div>

                        <!-- Max Points Filter -->
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label small text-muted">Max Points</label>
                            <input type="number" name="max_points" class="form-control form-control-sm" 
                                   placeholder="Max" value="{{ request('max_points') }}">
                        </div>

                        <!-- Sort By -->
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label small text-muted">Sort By</label>
                            <select name="sort" class="form-select form-select-sm">
                                <option value="loyalty_points" {{ request('sort') == 'loyalty_points' ? 'selected' : '' }}>Points</option>
                                <option value="total_spent" {{ request('sort') == 'total_spent' ? 'selected' : '' }}>Total Spent</option>
                                <option value="loyalty_points_spent" {{ request('sort') == 'loyalty_points_spent' ? 'selected' : '' }}>Points Spent</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Joined Date</option>
                            </select>
                        </div>

                        <!-- Direction -->
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label small text-muted">Order</label>
                            <select name="direction" class="form-select form-select-sm">
                                <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Descending</option>
                                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
                            </select>
                        </div>

                        <!-- Reset Button -->
                        <div class="col-lg-1 col-md-12">
                            <a href="{{ route('admin.customers.loyalty.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                                <i class="bi bi-x-lg me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Customer</th>
                                <th class="text-center">Points Balance</th>
                                <th class="text-center">Points Spent</th>
                                <th class="text-center">Total Spent</th>
                                <th class="text-center">Join Date</th>
                                 <th style="width: 120px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($customers as $index => $customer)
                                <tr>
                                    <td>{{ $customers->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white me-2">
                                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $customer->name }}</div>
                                                <div class="small text-muted">{{ $customer->email }}</div>
                                                @if($customer->phone)
                                                    <div class="small text-muted">{{ $customer->phone }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark fs-6">
                                            <i class="bi bi-star-fill me-1"></i>{{ number_format($customer->loyalty_points) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-muted">{{ number_format($customer->loyalty_points_spent) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-medium">${{ number_format($customer->total_spent, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="small text-muted">{{ $customer->created_at->format('M d, Y') }}</div>
                                    </td>
                                     <td class="text-center">
                                         <div class="btn-group">
                                             <a href="{{ route('admin.customers.loyalty.show', $customer->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                 <i class="bi bi-eye"></i>
                                             </a>
                                             <button class="btn btn-sm btn-outline-success" title="Add Points" data-bs-toggle="modal" data-bs-target="#addPointsModal{{ $customer->id }}">
                                                 <i class="bi bi-plus-circle"></i>
                                             </button>
                                             <button class="btn btn-sm btn-outline-warning" title="Deduct Points" data-bs-toggle="modal" data-bs-target="#deductPointsModal{{ $customer->id }}">
                                                 <i class="bi bi-dash-circle"></i>
                                             </button>
                                             <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-outline-secondary" title="View Profile">
                                                 <i class="bi bi-person"></i>
                                             </a>
                                         </div>
                                     </td>
                                </tr>

                                <!-- Add Points Modal -->
                                <div class="modal fade" id="addPointsModal{{ $customer->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Add Points to {{ $customer->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.customers.loyalty.addPoints') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $customer->id }}">
                                                <div class="modal-body">
                                                    <div class="alert alert-info">
                                                        <i class="bi bi-info-circle me-2"></i>
                                                        Current Balance: <strong>{{ number_format($customer->loyalty_points) }} points</strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Points <span class="text-danger">*</span></label>
                                                        <input type="number" name="points" class="form-control" min="1" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                                        <select name="type" class="form-select" required>
                                                            <option value="bonus">Bonus</option>
                                                            <option value="earned">Earned</option>
                                                            <option value="adjusted">Adjusted</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <input type="text" name="description" class="form-control" placeholder="Reason for adding points">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Add Points</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Deduct Points Modal -->
                                <div class="modal fade" id="deductPointsModal{{ $customer->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Deduct Points from {{ $customer->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.customers.loyalty.deductPoints') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $customer->id }}">
                                                <div class="modal-body">
                                                    <div class="alert alert-warning">
                                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                                        Current Balance: <strong>{{ number_format($customer->loyalty_points) }} points</strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Points to Deduct <span class="text-danger">*</span></label>
                                                        <input type="number" name="points" class="form-control" min="1" max="{{ $customer->loyalty_points }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason</label>
                                                        <input type="text" name="description" class="form-control" placeholder="Reason for deduction">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Deduct Points</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="bi bi-star text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mb-2 mt-2">No customers with loyalty points found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($customers->hasPages())
                <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">
                        Showing {{ $customers->firstItem() }} - {{ $customers->lastItem() }} of {{ $customers->total() }} customers
                    </div>
                    <div>
                        {{ $customers->appends(request()->query())->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Transactions -->
        @if($recentTransactions->count() > 0)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Points</th>
                                <th>Balance</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $transaction->user->name ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $transaction->user->email ?? '' }}</div>
                                    </td>
                                    <td>
                                        @if($transaction->points > 0)
                                            <span class="text-success">+{{ number_format($transaction->points) }}</span>
                                        @else
                                            <span class="text-danger">{{ number_format($transaction->points) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($transaction->points_balance) }}</td>
                                    <td>
                                        @switch($transaction->type)
                                            @case('earned')
                                                <span class="badge bg-success">Earned</span>
                                                @break
                                            @case('spent')
                                                <span class="badge bg-danger">Spent</span>
                                                @break
                                            @case('bonus')
                                                <span class="badge bg-warning text-dark">Bonus</span>
                                                @break
                                            @case('expired')
                                                <span class="badge bg-secondary">Expired</span>
                                                @break
                                            @case('adjusted')
                                                <span class="badge bg-info">Adjusted</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="text-muted">{{ $transaction->description ?? '-' }}</td>
                                    <td class="text-muted small">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
@endsection

@push('styles')
<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}
</style>
@endpush

@push('scripts')
<script>
// Live search with debounce and AJAX updates
let searchTimeout;
const searchInput = document.getElementById('liveSearch');
const filterForm = document.getElementById('filterForm');

function fetchResults() {
    const formData = new FormData(filterForm);
    const params = new URLSearchParams(formData);

    fetch(`{{ route('admin.customers.loyalty.index') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update table body
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = data.html;

        // Update pagination
        const footer = document.querySelector('.card.border-0.shadow-sm .card-footer');
        if (footer && data.pagination) {
            footer.innerHTML = `
                <div class="text-muted small">
                    Showing ${data.first_item} - ${data.last_item} of ${data.total} customers
                </div>
                <div>${data.pagination}</div>
            `;
        }

        // Update URL without reload
        const newUrl = window.location.pathname + '?' + params.toString();
        window.history.pushState({}, '', newUrl);
    })
    .catch(error => console.error('Error:', error));
}

// Search input event with debounce
if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(fetchResults, 300);
    });
}

// Filter change events
document.querySelectorAll('#filterForm select, #filterForm input[type="number"]').forEach(element => {
    element.addEventListener('change', function() {
        fetchResults();
    });
});

// Handle pagination clicks via AJAX
document.addEventListener('click', function(e) {
    const paginationLink = e.target.closest('.pagination a');
    if (paginationLink && paginationLink.href) {
        e.preventDefault();
        const url = new URL(paginationLink.href);
        const params = url.searchParams.toString();

        fetch(`${window.location.pathname}?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = data.html;

            const footer = document.querySelector('.card.border-0.shadow-sm .card-footer');
            if (footer && data.pagination) {
                footer.innerHTML = `
                    <div class="text-muted small">
                        Showing ${data.first_item} - ${data.last_item} of ${data.total} customers
                    </div>
                    <div>${data.pagination}</div>
                `;
            }

            window.history.pushState({}, '', url.toString());
        })
        .catch(error => console.error('Error:', error));
    }
});
</script>
@endpush
