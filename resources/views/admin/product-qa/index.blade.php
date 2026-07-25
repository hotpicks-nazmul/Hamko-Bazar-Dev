@extends('admin.layouts.app')

@section('title', 'Product Q&A')

@push('styles')
<style>
    .qa-question {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .qa-answer {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }
    
    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .status-answered {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .status-published {
        background-color: #d1fae5;
        color: #065f46;
    }
    
    .featured-icon {
        color: #f59e0b;
    }
    
    .product-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .product-info img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-question-circle me-2"></i>Product Q&A</h4>
            <p class="text-muted mb-0">Manage customer questions and answers for products</p>
        </div>
    </div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-chat-left-text"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Total</span><span class="stat-card-value" id="statTotal">{{ $stats['total'] }}</span></div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Pending</span><span class="stat-card-value" id="statPending">{{ $stats['pending'] }}</span></div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-check-lg"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Answered</span><span class="stat-card-value" id="statAnswered">{{ $stats['answered'] }}</span></div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-send"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Published</span><span class="stat-card-value" id="statPublished">{{ $stats['published'] }}</span></div>
    </div>
</div>

<!-- Filters -->
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
                                   placeholder="Question, answer, product..." value="{{ request('search') }}">
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
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Answered</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>
                    
                    <!-- Product Filter -->
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <label class="form-label small text-muted">Product</label>
                        <select name="product_id" id="filterProduct" class="form-select form-select-sm">
                            <option value="">All Products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ Str::limit($product->name, 50) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Featured Filter -->
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label small text-muted">Featured</label>
                        <select name="featured" id="filterFeatured" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="yes" {{ request('featured') === 'yes' ? 'selected' : '' }}>Featured</option>
                            <option value="no" {{ request('featured') === 'no' ? 'selected' : '' }}>Not Featured</option>
                        </select>
                    </div>
                    
                    <!-- Reset Button -->
                    <div class="col-lg-2 col-md-4 col-sm-8">
                        <a href="{{ route('admin.product-qa.index') }}" class="btn btn-sm btn-outline-secondary">
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
                    <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('publish')">
                        <i class="bi bi-check-circle me-1"></i> Publish
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('feature')">
                        <i class="bi bi-star me-1"></i> Feature
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Action Form -->
    <form id="bulkActionForm" method="POST" action="{{ route('admin.product-qa.bulk-action') }}">
        @csrf
        <input type="hidden" name="action" id="bulkActionInput">
        <input type="hidden" name="ids" id="bulkIdsInput">
    </form>

    <!-- Q&A Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Product</th>
                            <th>Question</th>
                            <th>Answer</th>
                            <th>Status</th>
                            <th>Asked By</th>
                            <th>Date</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($qaEntries as $qa)
                        <tr data-id="{{ $qa->id }}">
                            <td>
                                <input type="checkbox" class="form-check-input item-checkbox" value="{{ $qa->id }}">
                            </td>
                            <td>
                                <div class="product-info">
                                    @php
                                        $imageUrl = $qa->product?->thumbnail;
                                        if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                            $imageUrl = '/storage/' . $imageUrl;
                                        }
                                    @endphp
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $qa->product?->name }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 4px;">
                                            <i class="bi bi-box text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-medium">{{ Str::limit($qa->product?->name, 30) }}</div>
                                        <small class="text-muted">ID: {{ $qa->product_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="qa-question" title="{{ $qa->question }}">
                                    {{ $qa->question }}
                                </div>
                                @if($qa->is_featured)
                                    <i class="bi bi-star-fill featured-icon" title="Featured"></i>
                                @endif
                            </td>
                            <td>
                                @if($qa->answer)
                                    <div class="qa-answer" title="{{ $qa->answer }}">
                                        {{ $qa->answer }}
                                    </div>
                                    <small class="text-muted">by {{ $qa->answerer?->name }} {{ $qa->answered_at?->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted fst-italic">Not answered yet</span>
                                @endif
                            </td>
                            <td>
                                @if($qa->status === 'pending')
                                    <span class="status-badge status-pending">Pending</span>
                                @elseif($qa->status === 'answered')
                                    <span class="status-badge status-answered">Answered</span>
                                @else
                                    <span class="status-badge status-published">Published</span>
                                @endif
                            </td>
                            <td>
                                {{ $qa->questioner_name ?? ($qa->user?->name ?? 'Guest') }}
                                @if($qa->is_anonymous)
                                    <span class="badge bg-secondary ms-1">Anonymous</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $qa->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $qa->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.product-qa.show', $qa->id) }}" class="btn btn-outline-primary" title="View & Answer">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-warning toggle-featured" data-id="{{ $qa->id }}" title="{{ $qa->is_featured ? 'Unfeature' : 'Feature' }}">
                                        <i class="bi {{ $qa->is_featured ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    </button>
                                    <form action="{{ route('admin.product-qa.destroy', $qa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Q&A?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-question-circle text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0">No Q&A entries found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        @if($qaEntries->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $qaEntries->firstItem() }} - {{ $qaEntries->lastItem() }} of {{ $qaEntries->total() }} entries
            </div>
            <div>
                {{ $qaEntries->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Selected items for bulk actions
    let selectedItems = new Set();
    
    // Update bulk actions bar
    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
    }
    
    // Clear selection
    function clearSelection() {
        selectedItems.clear();
        document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        updateBulkActions();
    }
    
    // Bulk action
    function bulkAction(action) {
        if (selectedItems.size === 0) {
            alert('Please select at least one item.');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${selectedItems.size} item(s)?`)) return;
        
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkIdsInput').value = JSON.stringify(Array.from(selectedItems));
        document.getElementById('bulkActionForm').submit();
    }
    
    // Select all checkbox
    document.getElementById('selectAll')?.addEventListener('change', function() {
        const checked = this.checked;
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            cb.checked = checked;
            if (checked) {
                selectedItems.add(parseInt(cb.value));
            } else {
                selectedItems.delete(parseInt(cb.value));
            }
        });
        updateBulkActions();
    });
    
    // Individual checkbox
    document.querySelectorAll('.item-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const id = parseInt(this.value);
            if (this.checked) {
                selectedItems.add(id);
            } else {
                selectedItems.delete(id);
            }
            updateBulkActions();
        });
    });
    
    // Toggle featured
    document.querySelectorAll('.toggle-featured').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`{{ route('admin.product-qa.index') }}/${id}/toggle-featured`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const icon = this.querySelector('i');
                    if (data.is_featured) {
                        icon.classList.remove('bi-star');
                        icon.classList.add('bi-star-fill');
                    } else {
                        icon.classList.remove('bi-star-fill');
                        icon.classList.add('bi-star');
                    }
                }
            });
        });
    });
    
    // Debounced live search
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    
    searchInput?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        
        searchSpinner.style.display = 'block';
        
        searchTimeout = setTimeout(() => {
            performLiveSearch(searchTerm);
        }, 300);
    });
    
    // Filter dropdowns trigger search on change
    ['filterStatus', 'filterProduct', 'filterFeatured'].forEach(id => {
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
        
        // Add filter values
        const status = document.getElementById('filterStatus').value;
        if (status) params.set('status', status);
        
        const productId = document.getElementById('filterProduct').value;
        if (productId) params.set('product_id', productId);
        
        const featured = document.getElementById('filterFeatured').value;
        if (featured) params.set('featured', featured);
        
        // Keep existing sort and per_page
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
        if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
        
        // AJAX request
        fetch(`{{ route('admin.product-qa.index') }}?${params.toString()}&ajax=1`, {
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
                
                // Update URL without reload
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
        });
    }
</script>
@endpush
