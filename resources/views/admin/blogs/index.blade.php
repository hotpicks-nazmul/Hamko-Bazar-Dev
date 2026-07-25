@extends('admin.layouts.app')

@section('title', 'Blog Posts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Blog Posts</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.blog-settings.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-gear me-1"></i> Settings
        </a>
        <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add New Post
        </a>
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
                               placeholder="Search posts..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                
                <!-- Category Filter -->
                <div class="col-lg-3 col-md-3 col-sm-4">
                    <label class="form-label small text-muted">Category</label>
                    <select name="category" id="filterCategory" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @php
                        $blogCategories = \App\Models\BlogCategory::where('status', 'active')->get();
                        @endphp
                        @foreach($blogCategories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-2 col-sm-4">
                    <a href="{{ route('admin.blogs.index') }}" class="btn btn-sm btn-outline-secondary">
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
                        <th style="width: 60px;">Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 120px;">Published</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($blogs as $blog)
                    <tr>
                        <td>
                            @php
                                $imageUrl = $blog->featured_image;
                                if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                                    $imageUrl = '/storage/' . $imageUrl;
                                }
                            @endphp
                            @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $blog->title }}" class="rounded" style="width: 50px; height: 40px; object-fit: cover;">
                            @else
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 40px;">
                                <i class="bi bi-image text-white"></i>
                            </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-medium">{{ $blog->title }}</div>
                            @if($blog->slug)
                            <small class="text-muted">/{{ $blog->slug }}</small>
                            @endif
                        </td>
                        <td>{{ $blog->category->name ?? 'Uncategorized' }}</td>
                        <td>{{ $blog->author->name ?? 'N/A' }}</td>
                        <td>
                            @php $isPublished = $blog->status === 'published' && $blog->published_at && $blog->published_at->isPast(); @endphp
                            <span class="badge {{ $isPublished ? 'bg-success' : 'bg-secondary' }}">
                                {{ $isPublished ? 'Published' : 'Draft' }}
                            </span>
                        </td>
                        <td>{{ $blog->published_at ? $blog->published_at->format('d M, Y') : 'Not published' }}</td>
                        <td>
                            <a href="{{ route('admin.blogs.edit', $blog->slug) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.blogs.toggle', $blog->slug) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-{{ $isPublished ? 'warning' : 'success' }}" title="{{ $isPublished ? 'Mark as Draft' : 'Mark as Published' }}">
                                    <i class="bi bi-{{ $isPublished ? 'eye-slash' : 'eye' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.blogs.destroy', $blog->slug) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this post?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-newspaper text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No blog posts found</p>
                            <a href="{{ route('admin.blogs.create') }}" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-plus-lg me-1"></i> Add First Post
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($blogs->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $blogs->firstItem() }} - {{ $blogs->lastItem() }} of {{ $blogs->total() }} posts
            </div>
            <div>
                {{ $blogs->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
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
        
        // Show spinner
        searchSpinner.style.display = 'block';
        
        // Debounce - wait 300ms after user stops typing
        searchTimeout = setTimeout(() => {
            performLiveSearch(searchTerm);
        }, 300);
    });

    // Filter dropdowns trigger search on change
    const filterStatus = document.getElementById('filterStatus');
    const filterCategory = document.getElementById('filterCategory');
    
    if (filterStatus) {
        filterStatus.addEventListener('change', function() {
            performLiveSearch(searchInput.value.trim());
        });
    }
    
    if (filterCategory) {
        filterCategory.addEventListener('change', function() {
            performLiveSearch(searchInput.value.trim());
        });
    }

    // Live search function
    function performLiveSearch(searchTerm) {
        const params = new URLSearchParams();
        
        if (searchTerm) params.set('search', searchTerm);
        
        // Add filter values
        if (filterStatus && filterStatus.value) params.set('status', filterStatus.value);
        if (filterCategory && filterCategory.value) params.set('category', filterCategory.value);
        
        // Keep existing per_page
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));
        
        // AJAX request
        fetch(`{{ route('admin.blogs.index') }}?${params.toString()}`, {
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
                
                // Update pagination if exists
                if (data.pagination) {
                    const paginationContainer = document.querySelector('.card-footer');
                    if (paginationContainer) {
                        paginationContainer.innerHTML = data.pagination;
                    }
                }
                
                // Update URL without reload
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            } else {
                // If no AJAX response, do regular page load
                window.location.search = params.toString();
            }
        })
        .catch(error => {
            searchSpinner.style.display = 'none';
            // Fallback to regular page load on error
            window.location.search = params.toString();
        });
    }
</script>
@endpush
