@extends('admin.layouts.app')

@section('title', 'Payout Requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Payout Requests</h4>
    <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Sellers
    </a>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Pending & Approved</span>
            <span class="stat-card-value">{{ $stats['pending'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Pending Amount</span>
            <span class="stat-card-value">৳{{ number_format($stats['pending_amount'] ?? 0, 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Completed</span>
            <span class="stat-card-value">{{ $stats['completed'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Rejected</span>
            <span class="stat-card-value">{{ $stats['rejected'] ?? 0 }}</span>
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
                               placeholder="Seller name, email, shop..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="pending_approved" {{ request('status') === 'pending_approved' ? 'selected' : '' }}>Pending & Approved</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Date From -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Date From</label>
                    <input type="date" name="date_from" id="filterDateFrom" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>

                <!-- Date To -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Date To</label>
                    <input type="date" name="date_to" id="filterDateTo" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>

                <!-- Reset Button -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('admin.sellers.payout-requests') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
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
                        <th>Seller</th>
                        <th>Amount</th>
                        <th>Commission</th>
                        <th>Net Amount</th>
                        <th>Payment Method</th>
                        <th>Bank Details</th>
                        <th>Status</th>
                        <th>Requested Date</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($payouts as $payout)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    @if($payout->seller->shop_logo)
                                        @php
                                            $logoUrl = $payout->seller->shop_logo;
                                            if($logoUrl && !str_starts_with($logoUrl, '/storage/') && !str_starts_with($logoUrl, 'http')) {
                                                $logoUrl = '/storage/' . $logoUrl;
                                            }
                                        @endphp
                                        <img src="{{ $logoUrl }}" alt="{{ $payout->seller->shop_name }}" class="rounded" style="width: 36px; height: 36px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                            <i class="bi bi-shop text-white"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $payout->seller->shop_name ?? $payout->seller->name }}</div>
                                    <small class="text-muted">{{ $payout->seller->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-medium">৳{{ number_format($payout->amount, 2) }}</span>
                        </td>
                        <td>
                            <span class="text-muted">৳{{ number_format($payout->commission, 2) }}</span>
                        </td>
                        <td>
                            <span class="fw-medium text-success">৳{{ number_format($payout->net_amount, 2) }}</span>
                        </td>
                        <td>
                            @if($payout->payment_method)
                                @switch($payout->payment_method)
                                    @case('bank_transfer')
                                        <span class="badge bg-primary"><i class="bi bi-bank me-1"></i> Bank Transfer</span>
                                        @break
                                    @case('cash')
                                        <span class="badge bg-info"><i class="bi bi-cash me-1"></i> Cash</span>
                                        @break
                                    @case('mobile_banking')
                                        <span class="badge bg-success"><i class="bi bi-phone me-1"></i> Mobile Banking</span>
                                        @break
                                    @case('cheque')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-file-earmark-text me-1"></i> Cheque</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $payout->payment_method }}</span>
                                @endswitch
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($payout->bank_name)
                                <small>
                                    <div>{{ $payout->bank_name }}</div>
                                    <div class="text-muted">{{ $payout->account_number }}</div>
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @switch($payout->status)
                                @case('pending')
                                    <span class="badge bg-warning">Pending</span>
                                    @break
                                @case('approved')
                                    <span class="badge bg-info">Approved</span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-success">Completed</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $payout->status }}</span>
                            @endswitch
                        </td>
                        <td>
                            <div>{{ $payout->created_at->format('d M Y') }}</div>
                            <small class="text-muted">{{ $payout->created_at->format('h:i A') }}</small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('admin.sellers.payouts.show', $payout->id) }}" class="btn btn-sm btn-outline-secondary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($payout->status === 'pending' || $payout->status === 'approved')
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $payout->id }}" title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $payout->id }}" title="Reject">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                @endif
                            </div>
                            
                            <!-- Approve Modal -->
                            <div class="modal fade" id="approveModal{{ $payout->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Approve Payout</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.sellers.payout-requests.approve', $payout->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <p>Approve payout of <strong>৳{{ number_format($payout->amount, 2) }}</strong> for <strong>{{ $payout->seller->shop_name ?? $payout->seller->name }}</strong>?</p>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Transaction ID (Optional)</label>
                                                    <input type="text" name="transaction_id" class="form-control" placeholder="Enter transaction ID">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Admin Notes (Optional)</label>
                                                    <textarea name="admin_notes" class="form-control" rows="2" placeholder="Any notes..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Approve & Complete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $payout->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Payout</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.sellers.payout-requests.reject', $payout->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <p>Reject payout of <strong>৳{{ number_format($payout->amount, 2) }}</strong> for <strong>{{ $payout->seller->shop_name ?? $payout->seller->name }}</strong>?</p>
                                                <p class="text-muted small">The seller's pending balance will be restored.</p>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                                    <textarea name="admin_notes" class="form-control" rows="3" required placeholder="Enter reason for rejection..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject Payout</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-cash-stack text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No payout requests found</p>
                            <a href="{{ route('admin.sellers.index') }}" class="btn btn-sm btn-outline-primary mt-1">
                                <i class="bi bi-arrow-left me-1"></i> Back to Sellers
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination inside card-body -->
        @if($payouts->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2" id="paginationFooter">
            <div class="text-muted small" id="paginationInfo">
                Showing {{ $payouts->firstItem() }} - {{ $payouts->lastItem() }} of {{ $payouts->total() }} requests
            </div>
            <div id="paginationLinks">
                {{ $payouts->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Stat card icon colors - only overrides not covered by global styles */
    .stat-card-warning .stat-card-icon i::before { color: #ffc107 !important; }
    .stat-card-warning .stat-card-icon i { color: #ffc107 !important; }
</style>
@endpush

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
        }, 400);
    });

    document.getElementById('filterStatus').addEventListener('change', function() {
        performLiveSearch(searchInput.value.trim());
    });

    // Date filters trigger search on change
    document.getElementById('filterDateFrom')?.addEventListener('change', function() {
        performLiveSearch(searchInput.value.trim());
    });

    document.getElementById('filterDateTo')?.addEventListener('change', function() {
        performLiveSearch(searchInput.value.trim());
    });

    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();

        if (searchTerm) params.set('search', searchTerm);

        const status = document.getElementById('filterStatus').value;
        if (status) params.set('status', status);

        const dateFrom = document.getElementById('filterDateFrom').value;
        if (dateFrom) params.set('date_from', dateFrom);

        const dateTo = document.getElementById('filterDateTo').value;
        if (dateTo) params.set('date_to', dateTo);

        fetch(`{{ route('admin.sellers.payout-requests') }}?${params.toString()}`, {
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
                
                // Update pagination info
                if (data.pagination_info) {
                    document.getElementById('paginationInfo').innerHTML = data.pagination_info;
                }
                
                // Update pagination links
                if (data.pagination) {
                    document.getElementById('paginationLinks').innerHTML = data.pagination;
                }
                
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
        })
        .catch(error => {
            searchSpinner.style.display = 'none';
            console.error('Search error:', error);
        });
    }

    // Handle pagination clicks via AJAX
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('.pagination a');
        if (paginationLink && document.querySelector('#tableBody')) {
            e.preventDefault();
            const url = paginationLink.getAttribute('href');
            if (url) {
                fetch(url + (url.includes('?') ? '&' : '?') + 'ajax=1', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.html) {
                        document.querySelector('#tableBody').innerHTML = data.html;
                        if (data.pagination_info) {
                            document.getElementById('paginationInfo').innerHTML = data.pagination_info;
                        }
                        if (data.pagination) {
                            document.getElementById('paginationLinks').innerHTML = data.pagination;
                        }
                        window.history.pushState({}, '', url);
                    }
                });
            }
        }
    });
</script>
@endpush
