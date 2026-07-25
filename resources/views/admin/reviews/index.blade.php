@extends('admin.layouts.app')

@section('title', 'Reviews Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Reviews Management</h4>
        <p class="text-muted mb-0 small">Manage customer reviews and ratings</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-star"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Total</span><span class="stat-card-value" id="statTotal">{{ $stats['total'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Pending</span><span class="stat-card-value" id="statPending">{{ $stats['pending'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Approved</span><span class="stat-card-value" id="statApproved">{{ $stats['approved'] ?? 0 }}</span></div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Rejected</span><span class="stat-card-value" id="statRejected">{{ $stats['total'] - $stats['pending'] - $stats['approved'] }}</span></div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-star-half"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Avg Rating</span><span class="stat-card-value" id="statAvg">{{ number_format($stats['avg_rating'] ?? 0, 1) }}</span></div>
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
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Reviews</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Rating</label>
                    <select name="rating" id="filterRating" class="form-select form-select-sm">
                        <option value="">All Ratings</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} Star</option>
                        @endfor
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-8">
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-outline-secondary">
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
                <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('approve')">
                    <i class="bi bi-check-circle me-1"></i> Approve
                </button>
                <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('reject')">
                    <i class="bi bi-x-circle me-1"></i> Reject
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Form -->
<form id="bulkActionForm" action="{{ route('admin.reviews.bulk-action') }}" method="POST">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput" value="">
    <input type="hidden" name="ids" id="bulkIdsInput" value="">

    <!-- Reviews Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                            </th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($reviews as $review)
                        <tr>
                            <td>
                                <input class="form-check-input review-checkbox" type="checkbox" value="{{ $review->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($review->product)
                                        @php
                                            $imageUrl = $review->product->image;
                                            if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                                $imageUrl = '/storage/' . $imageUrl;
                                            }
                                        @endphp
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $review->product->name }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                <i class="bi bi-image text-white"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('products.show', $review->product->slug) }}" target="_blank" class="text-decoration-none">
                                                <span class="fw-medium">{{ Str::limit($review->product->name, 30) }}</span>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">Product deleted</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                        <i class="bi bi-person text-primary"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium">{{ $review->user->name ?? 'Guest' }}</span>
                                        <br>
                                        <small class="text-muted">{{ $review->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                        @endfor
                                    </div>
                                    <small class="text-muted">{{ $review->rating }}/5</small>
                                </div>
                            </td>
                            <td>
                                @if($review->title)
                                    <strong>{{ $review->title }}</strong><br>
                                @endif
                                <span class="text-muted">{{ Str::limit($review->comment, 80) }}</span>
                                @if(strlen($review->comment) > 80)
                                    <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $review->id }}">
                                        Read more
                                    </button>
                                @endif
                            </td>
                            <td>
                                @if($review->status === 'approved')
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="bi bi-check-circle me-1"></i>Approved
                                    </span>
                                @elseif($review->status === 'rejected')
                                    <span class="badge bg-danger-subtle text-danger">
                                        <i class="bi bi-x-circle me-1"></i>Rejected
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="bi bi-clock me-1"></i>Pending
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $review->created_at->format('d M, Y') }}</small>
                                <br>
                                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($review->status !== 'approved')
                                        <button type="button" class="btn btn-sm btn-outline-success" title="Approve" onclick="approveReview({{ $review->id }})">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="View" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $review->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteReview({{ $review->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Review Detail Modal -->
                        <div class="modal fade" id="reviewModal{{ $review->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Review Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                                <i class="bi bi-person text-primary fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $review->user->name ?? 'Guest' }}</h6>
                                                <small class="text-muted">{{ $review->created_at->format('F d, Y \a\t h:i A') }}</small>
                                            </div>
                                            <div class="ms-auto text-warning">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        @if($review->product)
                                            <div class="bg-light p-3 rounded mb-3">
                                                <small class="text-muted">Product:</small>
                                                <p class="mb-0 fw-medium">{{ $review->product->name }}</p>
                                            </div>
                                        @endif
                                        @if($review->title)
                                            <h6 class="fw-bold">{{ $review->title }}</h6>
                                        @endif
                                        <p class="text-muted mb-0">{{ $review->comment }}</p>
                                    </div>
                                    <div class="modal-footer">
                                        @if($review->status !== 'approved')
                                            <button type="button" class="btn btn-success" onclick="approveReview({{ $review->id }})">
                                                <i class="bi bi-check-lg me-1"></i> Approve
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-chat-square-text text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0">No reviews found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($reviews->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $reviews->firstItem() }} - {{ $reviews->lastItem() }} of {{ $reviews->total() }} reviews
            </div>
            <div>
                {{ $reviews->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</form>

@push('scripts')
<script>
let selectedItems = new Set();
let searchTimeout;

// Select All Checkbox
document.getElementById('selectAllCheckbox').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.review-checkbox');
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

// Individual checkbox change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('review-checkbox')) {
        if (e.target.checked) {
            selectedItems.add(e.target.value);
        } else {
            selectedItems.delete(e.target.value);
        }
        updateBulkActions();
    }
});

function updateBulkActions() {
    const count = selectedItems.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    
    // Update select all checkbox state
    const selectAll = document.getElementById('selectAllCheckbox');
    const totalCheckboxes = document.querySelectorAll('.review-checkbox').length;
    const checkedCheckboxes = document.querySelectorAll('.review-checkbox:checked').length;
    selectAll.checked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
    selectAll.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
}

function clearSelection() {
    selectedItems.clear();
    document.querySelectorAll('.review-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateBulkActions();
}

// Bulk Action Submit
function bulkAction(action) {
    if (selectedItems.size === 0) {
        alert('Please select at least one review.');
        return;
    }
    
    if (action === 'delete') {
        if (!confirm(`Are you sure you want to delete ${selectedItems.size} review(s)?`)) {
            return;
        }
    }
    
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
    document.getElementById('bulkActionForm').submit();
}

// Approve single review
function approveReview(reviewId) {
    document.getElementById('bulkActionInput').value = 'approve';
    document.getElementById('bulkIdsInput').value = JSON.stringify([reviewId]);
    document.getElementById('bulkActionForm').submit();
}

// Delete single review
function deleteReview(reviewId) {
    if (!confirm('Are you sure you want to delete this review?')) {
        return;
    }
    document.getElementById('bulkActionInput').value = 'delete';
    document.getElementById('bulkIdsInput').value = JSON.stringify([reviewId]);
    document.getElementById('bulkActionForm').submit();
}

// Live Search
const searchInput = document.getElementById('liveSearch');
const searchSpinner = document.getElementById('searchSpinner');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const searchTerm = this.value.trim();
    
    searchTimeout = setTimeout(() => {
        performLiveSearch(searchTerm);
    }, 300);
});

// Filter dropdowns trigger search on change
const filterSelects = ['filterStatus', 'filterRating'];
filterSelects.forEach(id => {
    const select = document.getElementById(id);
    if (select) {
        select.addEventListener('change', function() {
            performLiveSearch(searchInput.value.trim());
        });
    }
});

function performLiveSearch(searchTerm) {
    searchSpinner.style.display = 'block';
    
    const params = new URLSearchParams();
    
    if (searchTerm) params.set('search', searchTerm);
    
    const status = document.getElementById('filterStatus').value;
    if (status) params.set('status', status);
    
    const rating = document.getElementById('filterRating').value;
    if (rating) params.set('rating', rating);
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
    if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
    
    fetch(`{{ route('admin.reviews.index') }}?${params.toString()}`, {
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
            
            // Update pagination if provided
            if (data.pagination) {
                const paginationContainer = document.querySelector('.card-footer div:last-child');
                if (paginationContainer) {
                    paginationContainer.innerHTML = data.pagination;
                }
            }
            
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({}, '', newUrl);
            
            // Re-initialize checkboxes
            selectedItems.clear();
            document.getElementById('selectAllCheckbox').checked = false;
            updateBulkActions();
        } else {
            // Regular page load fallback
            window.location.search = params.toString();
        }
    })
    .catch(() => {
        searchSpinner.style.display = 'none';
        // Fallback to regular form submission
        document.getElementById('filterForm').submit();
    });
}
</script>
@endpush
@endsection
