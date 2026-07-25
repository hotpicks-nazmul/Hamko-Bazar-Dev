@extends('admin.layouts.app')

@section('title', 'Seller Verification')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Pending Verification</span>
            <span class="stat-card-value" id="stat-pending">{{ $stats['pending'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon">
            <i class="bi bi-x-circle"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Rejected</span>
            <span class="stat-card-value" id="stat-rejected">{{ $stats['rejected'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Verified</span>
            <span class="stat-card-value" id="stat-verified">{{ $stats['verified'] ?? 0 }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Sellers</span>
            <span class="stat-card-value" id="stat-total">{{ $stats['total'] ?? 0 }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-patch-check me-2"></i>Seller Verification</h4>
    <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Sellers
    </a>
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
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Name, email, shop name..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </span>
                    </div>
                </div>

                <!-- Verification Status Filter -->
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Verification Status</label>
                    <select name="verification_status" id="filterVerification" class="form-select form-select-sm">
                        <option value="">All Pending/Rejected</option>
                        <option value="pending" {{ request('verification_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('verification_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Seller Type -->
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Seller Type</label>
                    <select name="seller_type" id="filterSellerType" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="individual" {{ request('seller_type') === 'individual' ? 'selected' : '' }}>Individual</option>
                        <option value="company" {{ request('seller_type') === 'company' ? 'selected' : '' }}>Company</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="col-lg-2 col-md-2 col-sm-6">
                    <a href="{{ route('admin.sellers.verification') }}" class="btn btn-outline-secondary btn-sm w-100">
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
                        <th>Shop Info</th>
                        <th>Seller Type</th>
                        <th>Verification Status</th>
                        <th>Submitted At</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($sellers as $seller)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @php
                                    $avatarUrl = $seller->shop_logo;
                                    if($avatarUrl && !str_starts_with($avatarUrl, '/storage/') && !str_starts_with($avatarUrl, 'http')) {
                                        $avatarUrl = '/storage/' . $avatarUrl;
                                    }
                                @endphp
                                @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $seller->name }}" class="shop-logo-thumb me-2">
                                @else
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                    <i class="bi bi-shop text-white"></i>
                                </div>
                                @endif
                                <div>
                                    <div class="fw-medium">{{ $seller->name }}</div>
                                    <div class="small text-muted">{{ $seller->email }}</div>
                                    <div class="small text-muted">{{ $seller->phone ?? 'No phone' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium">{{ $seller->shop_name ?? 'No shop name' }}</div>
                            @if($seller->company_name)
                            <div class="small text-muted">{{ $seller->company_name }}</div>
                            @endif
                        </td>
                        <td>
                            @if($seller->seller_type === 'company')
                            <span class="badge bg-primary">Company</span>
                            @else
                            <span class="badge bg-info text-dark">Individual</span>
                            @endif
                        </td>
                        <td>
                            @if($seller->verification_status === 'verified')
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i> Verified
                            </span>
                            @elseif($seller->verification_status === 'pending')
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-clock me-1"></i> Pending
                            </span>
                            @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle me-1"></i> Rejected
                            </span>
                            @endif
                            @if($seller->verification_notes)
                            <div class="small text-muted mt-1" data-bs-toggle="tooltip" title="{{ $seller->verification_notes }}">
                                <i class="bi bi-info-circle"></i> Has notes
                            </div>
                            @endif
                        </td>
                        <td>
                            <div class="small">{{ $seller->created_at->format('d M Y') }}</div>
                            <div class="small text-muted">{{ $seller->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                @if($seller->verification_status !== 'verified')
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $seller->id }}" title="Approve">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                @endif
                                
                                @if($seller->verification_status !== 'rejected')
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $seller->id }}" title="Reject">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Approve Modal -->
                    @if($seller->verification_status !== 'verified')
                    <div class="modal fade" id="approveModal{{ $seller->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Approve Seller Verification</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.sellers.verification.process', $seller->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <p>Are you sure you want to approve <strong>{{ $seller->name }}</strong> ({{ $seller->shop_name }})?</p>
                                        <p class="text-muted small">This will mark the seller as verified and activate their account.</p>
                                        
                                        <div class="mb-3">
                                            <label for="notes{{ $seller->id }}" class="form-label">Notes (Optional)</label>
                                            <textarea class="form-control" id="notes{{ $seller->id }}" name="verification_notes" rows="3" placeholder="Add any notes about this approval..."></textarea>
                                        </div>
                                        
                                        <input type="hidden" name="action" value="approve">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-lg me-1"></i> Approve Verification
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Reject Modal -->
                    @if($seller->verification_status !== 'rejected')
                    <div class="modal fade" id="rejectModal{{ $seller->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reject Seller Verification</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.sellers.verification.process', $seller->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <p>Are you sure you want to reject <strong>{{ $seller->name }}</strong> ({{ $seller->shop_name }})?</p>
                                        
                                        <div class="mb-3">
                                            <label for="rejectNotes{{ $seller->id }}" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="rejectNotes{{ $seller->id }}" name="verification_notes" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                                            <div class="form-text">This will be visible to the seller.</div>
                                        </div>
                                        
                                        <input type="hidden" name="action" value="reject">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-x-lg me-1"></i> Reject Verification
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif

                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-patch-check text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No verification requests found</p>
                            <a href="{{ route('admin.sellers.index') }}" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-arrow-left me-1"></i> Back to Sellers
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination inside card-body -->
        @if($sellers->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 pagination-container">
            <div class="text-muted small">
                Showing {{ $sellers->firstItem() }} - {{ $sellers->lastItem() }} of {{ $sellers->total() }} requests
            </div>
            <div class="pagination-links">
                {{ $sellers->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .shop-logo-thumb {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        object-fit: cover;
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script>
    // Live search with debounce
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const filterForm = document.getElementById('filterForm');

    function fetchResults() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchSpinner.style.display = 'block';

            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);

            fetch('{{ route("admin.sellers.verification") }}?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update table body
                document.querySelector('#tableBody').innerHTML = data.html;
                
                // Update pagination
                const paginationContainer = document.querySelector('.pagination-container');
                if (data.pagination) {
                    if (paginationContainer) {
                        paginationContainer.style.display = 'flex';
                        paginationContainer.innerHTML = data.pagination;
                    } else {
                        // Create pagination container if it doesn't exist
                        const cardBody = document.querySelector('.card-body');
                        const paginationDiv = document.createElement('div');
                        paginationDiv.className = 'card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 pagination-container';
                        paginationDiv.innerHTML = data.pagination;
                        cardBody.appendChild(paginationDiv);
                    }
                } else if (paginationContainer) {
                    // Hide pagination if no pages
                    paginationContainer.style.display = 'none';
                }

                // Update stats
                if (data.stats) {
                    document.getElementById('stat-pending').textContent = data.stats.pending;
                    document.getElementById('stat-rejected').textContent = data.stats.rejected;
                    document.getElementById('stat-verified').textContent = data.stats.verified;
                    document.getElementById('stat-total').textContent = data.stats.total;
                }

                // Update URL without reload
                const newUrl = '{{ route("admin.sellers.verification") }}?' + params.toString();
                window.history.pushState({}, '', newUrl);

                // Re-initialize tooltips
                initTooltips();
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                searchSpinner.style.display = 'none';
            });
        }, 500);
    }

    function initTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    }

    // Handle pagination clicks via AJAX
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            const url = link.getAttribute('href');
            if (url) {
                searchSpinner.style.display = 'block';
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.querySelector('#tableBody').innerHTML = data.html;
                    
                    const paginationContainer = document.querySelector('.pagination-container');
                    if (data.pagination) {
                        paginationContainer.style.display = 'flex';
                        paginationContainer.innerHTML = data.pagination;
                    } else {
                        paginationContainer.style.display = 'none';
                    }

                    if (data.stats) {
                        document.getElementById('stat-pending').textContent = data.stats.pending;
                        document.getElementById('stat-rejected').textContent = data.stats.rejected;
                        document.getElementById('stat-verified').textContent = data.stats.verified;
                        document.getElementById('stat-total').textContent = data.stats.total;
                    }

                    window.history.pushState({}, '', url);
                    initTooltips();
                })
                .catch(error => console.error('Error:', error))
                .finally(() => {
                    searchSpinner.style.display = 'none';
                });
            }
        }
    });

    // Search input event
    if (searchInput) {
        searchInput.addEventListener('input', fetchResults);
    }

    // Filter change events
    document.getElementById('filterVerification')?.addEventListener('change', fetchResults);
    document.getElementById('filterSellerType')?.addEventListener('change', fetchResults);

    // Initialize tooltips
    initTooltips();
</script>
@endpush
