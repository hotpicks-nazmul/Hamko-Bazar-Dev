@extends('admin.layouts.app')

@section('title', 'Pages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Pages</h4>
    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add </a>
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
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Search pages..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-sm btn-outline-secondary">
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
                        <th>Title</th>
                        <th>Slug</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 150px;">Updated</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($pages as $page)
                    <tr>
                        <td>{{ $loop->iteration + ($pages->currentPage() - 1) * $pages->perPage() }}</td>
                        <td>
                            <div class="fw-medium">{{ $page->title }}</div>
                            @if($page->meta_title)
                            <small class="text-muted">{{ Str::limit($page->meta_title, 50) }}</small>
                            @endif
                        </td>
                        <td><code class="small">{{ $page->slug }}</code></td>
                        <td>
                            <span class="badge {{ $page->status === 'published' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $page->status === 'published' ? 'Published' : 'Draft' }}
                            </span>
                        </td>
                        <td>{{ $page->updated_at->format('d M, Y') }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.pages.toggle', $page->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $page->status === 'published' ? 'warning' : 'success' }}" title="{{ $page->status === 'published' ? 'Mark as Draft' : 'Mark as Published' }}">
                                        <i class="bi bi-{{ $page->status === 'published' ? 'eye-slash' : 'eye' }}"></i>
                                    </button>
                                </form>
                                @if($page->status === 'published')
                                <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="btn btn-sm btn-outline-success" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @endif
                                <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this page?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-file-earmark-text text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No pages found</p>
                            <a href="{{ route('admin.pages.create') }}" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-plus-lg me-1"></i> Add First Page
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination inside card-body -->
        @if($pages->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $pages->firstItem() }} - {{ $pages->lastItem() }} of {{ $pages->total() }} pages
            </div>
            <div>
                {{ $pages->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('liveSearch');
    const filterStatus = document.getElementById('filterStatus');
    const filterForm = document.getElementById('filterForm');
    
    let searchTimeout;
    
    // Debounced live search
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterForm.submit();
        }, 300);
    });
    
    // Filter dropdown triggers search on change
    filterStatus.addEventListener('change', function() {
        filterForm.submit();
    });
});
</script>
@endpush
