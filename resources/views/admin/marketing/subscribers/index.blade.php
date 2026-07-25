@extends('admin.layouts.app')

@section('title', 'Subscribers')

@section('content')

{{-- Reopen modal if there are validation errors --}}
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('addSubscriberModal'));
        modal.show();
    });
</script>
@endif
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-people"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total</span>
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
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Unsubscribed</span>
            <span class="stat-card-value">{{ number_format($stats['unsubscribed'] ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Subscribers</h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubscriberModal">
        <i class="bi bi-plus-lg me-1"></i> Add Subscriber
    </button>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search Input -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Search by email or name..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="unsubscribed" {{ request('status') === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                    </select>
                </div>
                
                <!-- Export Button -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('admin.marketing.subscribers.export', ['status' => request('status')]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-download me-1"></i> Export
                    </a>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('admin.marketing.subscribers.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Subscribers Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Email</th>
                        <th>Name</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 150px;">Subscribed Date</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($subscribers as $subscriber)
                    <tr>
                        <td>
                            <div class="fw-medium">{{ $subscriber->email }}</div>
                        </td>
                        <td>{{ $subscriber->name ?? '-' }}</td>
                        <td>
                            @php
                                $badgeClass = $subscriber->isActive() ? 'success' : 'danger';
                            @endphp
                            <span class="badge bg-{{ $badgeClass }}">{{ $subscriber->isActive() ? 'Active' : 'Unsubscribed' }}</span>
                        </td>
                        <td>{{ $subscriber->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if($subscriber->isActive())
                                    <form action="{{ route('admin.marketing.subscribers.unsubscribe', $subscriber->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning" title="Unsubscribe">
                                            <i class="bi bi-bell-slash"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.marketing.subscribers.resubscribe', $subscriber->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Resubscribe">
                                            <i class="bi bi-bell"></i>
                                        </button>
                                    </form>
                                @endif
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $subscriber->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            
                            <!-- Delete Form (hidden) -->
                            <form id="deleteForm{{ $subscriber->id }}" action="{{ route('admin.marketing.subscribers.destroy', $subscriber->id) }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="bi bi-person-plus text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No subscribers found</p>
                            <button type="button" class="btn btn-sm btn-primary mt-1" data-bs-toggle="modal" data-bs-target="#addSubscriberModal">
                                <i class="bi bi-plus-lg me-1"></i> Add First Subscriber
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($subscribers->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $subscribers->firstItem() }} - {{ $subscribers->lastItem() }} of {{ $subscribers->total() }} subscribers
            </div>
            <div>
                {{ $subscribers->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Add Subscriber Modal -->
<div class="modal fade" id="addSubscriberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Subscriber</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.marketing.subscribers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               placeholder="Enter email address" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               placeholder="Enter name (optional)">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add Subscriber
                    </button>
                </div>
            </form>
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
    const filterStatus = document.getElementById('filterStatus');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        
        // Show spinner
        if (searchSpinner) {
            searchSpinner.style.display = 'block';
        }
        
        // Debounce - wait 300ms after user stops typing
        searchTimeout = setTimeout(() => {
            performLiveSearch(searchTerm);
        }, 300);
    });

    // Filter dropdown triggers search on change
    if (filterStatus) {
        filterStatus.addEventListener('change', function() {
            performLiveSearch(searchInput.value.trim());
        });
    }

    // Live search function
    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();
        
        if (searchTerm) params.set('search', searchTerm);
        
        // Add filter value
        if (filterStatus && filterStatus.value) {
            params.set('status', filterStatus.value);
        }
        
        // Keep existing per_page
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
        
        // AJAX request
        fetch(`{{ route('admin.marketing.subscribers.index') }}?${params.toString()}&ajax=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (searchSpinner) {
                searchSpinner.style.display = 'none';
            }
            
            if (data.html) {
                // Update table body
                document.querySelector('#tableBody').innerHTML = data.html;
                
                // Update pagination
                const paginationContainer = document.querySelector('.card-footer');
                if (data.pagination && paginationContainer) {
                    paginationContainer.innerHTML = data.pagination;
                }
                
                // Update stats
                if (data.stats) {
                    updateStats(data.stats);
                }
                
                // Update URL without reload
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
        })
        .catch(error => {
            if (searchSpinner) {
                searchSpinner.style.display = 'none';
            }
            // Fallback to regular form submission
            document.getElementById('filterForm').submit();
        });
    }

    // Update stats cards
    function updateStats(stats) {
        const statCards = document.querySelectorAll('.stat-card-value');
        if (statCards.length >= 3) {
            statCards[0].textContent = stats.total ? number_format(stats.total) : '0';
            statCards[1].textContent = stats.active ? number_format(stats.active) : '0';
            statCards[2].textContent = stats.unsubscribed ? number_format(stats.unsubscribed) : '0';
        }
    }
    
    // Number format helper
    function number_format(num) {
        return parseInt(num).toLocaleString();
    }

    // Delete confirmation
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this subscriber?')) {
            document.getElementById('deleteForm' + id).submit();
        }
    }
</script>
@endpush
