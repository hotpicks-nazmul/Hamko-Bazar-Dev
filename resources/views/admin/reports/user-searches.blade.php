@extends('admin.layouts.app')

@section('title', 'User Searches Report')

@section('content')
<div class="mb-4">
    <h4 class="mb-0"><i class="bi bi-search me-2"></i>User Searches Report</h4>
    <p class="text-muted mb-0">Track what users are searching for on your store</p>
</div>

<!-- Summary Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon">
            <i class="bi bi-search"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Searches</span>
            <span class="stat-card-value">{{ number_format($totalSearches) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon">
            <i class="bi bi-chat-left-text"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Unique Queries</span>
            <span class="stat-card-value">{{ number_format($uniqueQueries) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Unique Users</span>
            <span class="stat-card-value">{{ number_format($uniqueUsers) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon">
            <i class="bi bi-emoji-frown"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">No Results</span>
            <span class="stat-card-value">{{ number_format($noResultsCount) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon">
            <i class="bi bi-bar-chart"></i>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-label">Avg Results</span>
            <span class="stat-card-value">{{ number_format($avgResultsPerSearch, 1) }}</span>
        </div>
    </div>
</div>

<!-- Top Search Terms Alert -->
@if($topSearches->isNotEmpty())
<div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-trophy-fill text-warning fs-4 me-3 mt-1"></i>
        <div>
            <strong>Top Search Terms:</strong>
            <div class="mt-2">
                @foreach($topSearches->take(5) as $index => $topSearch)
                <span class="badge bg-{{ $index == 0 ? 'warning text-dark' : 'primary' }} me-2 mb-1">
                    "{{ $topSearch->query }}" ({{ number_format($topSearch->search_count) }})
                </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search Input -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search Query</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Search by query..." value="{{ $search }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </span>
                    </div>
                </div>
                
                <!-- Date Range -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Date Range</label>
                    <input type="text" name="date_range" id="dateRange" class="form-control form-control-sm" 
                           placeholder="Select date range" value="{{ $dateRange }}">
                </div>
                
                <!-- Search Type -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Search Type</label>
                    <select name="search_type" class="form-select form-select-sm">
                        <option value="" {{ $searchType == '' ? 'selected' : '' }}>All Types</option>
                        <option value="autocomplete" {{ $searchType == 'autocomplete' ? 'selected' : '' }}>Autocomplete</option>
                        <option value="manual" {{ $searchType == 'manual' ? 'selected' : '' }}>Manual Search</option>
                    </select>
                </div>
                
                <!-- Sort By -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Sort By</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="recent" {{ $sortBy == 'recent' ? 'selected' : '' }}>Most Recent</option>
                        <option value="oldest" {{ $sortBy == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="popular" {{ $sortBy == 'popular' ? 'selected' : '' }}>Most Popular</option>
                        <option value="results_desc" {{ $sortBy == 'results_desc' ? 'selected' : '' }}>Most Results</option>
                        <option value="results_asc" {{ $sortBy == 'results_asc' ? 'selected' : '' }}>Least Results</option>
                    </select>
                </div>
                
                <!-- Filter Buttons -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.user-searches') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                        <a href="{{ route('admin.reports.user-searches.export', request()->query()) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download me-1"></i> Export
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0"><i class="bi bi-table me-2"></i>Search History</h6>
        <span class="text-muted small">Showing {{ $searches->firstItem() ?? 0 }} - {{ $searches->lastItem() ?? 0 }} of {{ $searches->total() }} searches</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Search Query</th>
                        <th style="width: 150px;">User</th>
                        <th class="text-center" style="width: 120px;">Results</th>
                        <th class="text-center" style="width: 130px;">Type</th>
                        <th style="width: 140px;">IP Address</th>
                        <th style="width: 150px;">Date</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($searches as $index => $item)
                    <tr>
                        <td>{{ $searches->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-medium">{{ $item->query }}</div>
                        </td>
                        <td>
                            @if($item->user)
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <span class="text-primary small">{{ substr($item->user->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="small fw-medium">{{ $item->user->name }}</div>
                                    <div class="text-muted small">{{ $item->user->email }}</div>
                                </div>
                            </div>
                            @else
                            <span class="text-muted">
                                <i class="bi bi-person me-1"></i>Guest
                            </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->results_count > 0)
                            <span class="badge bg-success">{{ number_format($item->results_count) }}</span>
                            @else
                            <span class="badge bg-danger">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->is_autocomplete)
                            <span class="badge bg-info">
                                <i class="bi bi-lightning me-1"></i>Autocomplete
                            </span>
                            @else
                            <span class="badge bg-secondary">
                                <i class="bi bi-search me-1"></i>Manual
                            </span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted small">{{ $item->ip_address ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="text-muted small">
                                {{ $item->created_at->format('d M Y, h:i A') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No search data found</p>
                            <p class="text-muted small">Start tracking user searches by using the search functionality on your store</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($searches->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <div class="text-muted small">
                Page {{ $searches->currentPage() }} of {{ $searches->lastPage() }}
            </div>
            <div>
                {{ $searches->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table td {
        font-size: 0.9rem;
    }
    .badge {
        font-weight: 500;
    }
    .avatar-sm {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date range picker
    const dateRangeInput = document.getElementById('dateRange');
    if (dateRangeInput) {
        flatpickr(dateRangeInput, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            allowInput: true,
            placeholder: 'Select date range'
        });
    }
    
    // Live search with debounce - auto submit after typing stops
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const filterForm = document.getElementById('filterForm');
    
    if (searchInput && filterForm) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            // Show spinner while typing
            if (searchSpinner) {
                searchSpinner.style.display = 'block';
            }
            
            // Debounce - submit form 500ms after user stops typing
            searchTimeout = setTimeout(() => {
                if (searchSpinner) {
                    searchSpinner.style.display = 'none';
                }
                // Actually submit the form to filter results
                filterForm.submit();
            }, 500);
        });
    }
    
    // Date range presets - work with the flatpickr date range input
    document.querySelectorAll('.date-preset').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const days = parseInt(this.dataset.days);
            const dateRangeInput = document.getElementById('dateRange');
            
            if (dateRangeInput) {
                const end = new Date();
                const start = new Date();
                start.setDate(start.getDate() - days);
                
                // Format: YYYY-MM-DD to YYYY-MM-DD (flatpickr range format)
                const startStr = start.toISOString().split('T')[0];
                const endStr = end.toISOString().split('T')[0];
                
                dateRangeInput.value = startStr + ' to ' + endStr;
                filterForm.submit();
            }
        });
    });
});
</script>
@endpush
