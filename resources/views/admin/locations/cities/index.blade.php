@extends('admin.layouts.app')

@section('title', 'Cities')

@section('content')
<div class="stat-card-row mb-4" id="statsCards">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-building"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Cities</span>
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
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value">{{ number_format($stats['inactive'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-globe"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Countries</span>
            <span class="stat-card-value">{{ number_format($stats['countries'] ?? 0) }}</span>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0"><i class="bi bi-building me-2"></i>Cities</h4>
        <small class="text-muted">Manage cities for checkout location selection</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.locations.cities.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add City
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="City name, country..." value="{{ request('search') }}">
                        <span class="input-group-text" id="searchSpinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>
                        </span>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Country</label>
                    <select name="country_id" id="filterCountry" class="form-select form-select-sm">
                        <option value="">All Countries</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}" {{ request('country_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">State / Province</label>
                    <select name="state_id" id="filterState" class="form-select form-select-sm">
                        <option value="">All States</option>
                        @foreach($states as $s)
                            <option value="{{ $s->id }}" {{ request('state_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <a href="{{ route('admin.locations.cities.index') }}" class="btn btn-sm btn-outline-secondary w-100" id="resetFilters">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>
                            <a href="{{ route('admin.locations.cities.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Name @if(request('sort') == 'name')<i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill"></i>@endif
                            </a>
                        </th>
                        <th>State / Province</th>
                        <th>Country</th>
                        <th>Areas</th>
                        <th>Status</th>
                        <th>Sort</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.locations.cities.partials.table-rows', ['cities' => $cities])
                </tbody>
            </table>
        </div>
        @if(isset($cities) && method_exists($cities, 'hasPages') && $cities->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show:</span>
                <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 || !request('per_page') ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="text-muted small">per page</span>
            </div>
            <div id="paginationLinks">{{ $cities->appends(request()->query())->links() }}</div>
            <div class="text-muted small">Showing {{ $cities->firstItem() }} - {{ $cities->lastItem() }} of {{ $cities->total() }} cities</div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
let searchTimeout;

document.getElementById('liveSearch')?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    document.getElementById('searchSpinner').style.display = 'block';
    searchTimeout = setTimeout(() => performLiveSearch(this.value.trim()), 300);
});

['filterCountry', 'filterState', 'filterStatus'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', function() {
        performLiveSearch(document.getElementById('liveSearch')?.value?.trim() || '');
    });
});

function performLiveSearch(search) {
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    const countryId = document.getElementById('filterCountry')?.value;
    if (countryId) params.set('country_id', countryId);
    const stateId = document.getElementById('filterState')?.value;
    if (stateId) params.set('state_id', stateId);
    const status = document.getElementById('filterStatus')?.value;
    if (status) params.set('status', status);
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
    if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));
    if (urlParams.get('per_page')) params.set('per_page', urlParams.get('per_page'));

    const url = `{{ route('admin.locations.cities.index') }}?${params.toString()}`;
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('searchSpinner').style.display = 'none';
        if (data.html) {
            document.querySelector('#tableBody').innerHTML = data.html;
            if (data.pagination) document.getElementById('paginationLinks').innerHTML = data.pagination;
            if (data.stats) updateStats(data.stats);
        }
        window.history.pushState({}, '', url);
    })
    .catch(() => { window.location.search = params.toString(); });
}

function changePerPage(val) {
    const params = new URLSearchParams(window.location.search);
    params.set('per_page', val);
    params.delete('page');
    const url = `${window.location.pathname}?${params.toString()}`;
    fetch(`{{ route('admin.locations.cities.index') }}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.html) document.querySelector('#tableBody').innerHTML = data.html;
        if (data.pagination) document.getElementById('paginationLinks').innerHTML = data.pagination;
        if (data.stats) updateStats(data.stats);
        window.history.pushState({}, '', url);
    });
}

function updateStats(stats) {
    const cards = document.querySelectorAll('#statsCards .stat-card');
    cards.forEach(card => {
        const label = card.querySelector('.stat-card-label')?.textContent.trim();
        const valueEl = card.querySelector('.stat-card-value');
        if (!valueEl) return;
        switch (label) {
            case 'Total Cities': valueEl.textContent = Number(stats.total ?? 0).toLocaleString(); break;
            case 'Active': valueEl.textContent = Number(stats.active ?? 0).toLocaleString(); break;
            case 'Inactive': valueEl.textContent = Number(stats.inactive ?? 0).toLocaleString(); break;
            case 'Countries': valueEl.textContent = Number(stats.countries ?? 0).toLocaleString(); break;
        }
    });
}

// Status toggle with event delegation
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('toggle-status')) {
        const checkbox = e.target;
        const url = checkbox.dataset.url;
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                checkbox.checked = !checkbox.checked;
            }
        });
    }
});
</script>
@endpush
