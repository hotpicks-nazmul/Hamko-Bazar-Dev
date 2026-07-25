@extends('admin.layouts.app')

@section('title', 'Gift Cards')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-gift"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Cards</span>
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
        <div class="stat-card-icon"><i class="bi bi-pause-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Inactive</span>
            <span class="stat-card-value">{{ number_format($stats['inactive'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Expired</span>
            <span class="stat-card-value">{{ number_format($stats['expired'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-check2-all"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Used</span>
            <span class="stat-card-value">{{ number_format($stats['used'] ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Gift Cards</h4>
    <a href="{{ route('admin.marketing.gift-cards.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Gift Card
    </a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search -->
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" placeholder="Code, title, email..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Used</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <a href="{{ route('admin.marketing.gift-cards.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Gift Cards Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Balance</th>
                        <th>Discount</th>
                        <th>Recipient</th>
                        <th>Usage</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($giftCards as $card)
                    <tr>
                        <td>
                            <code class="bg-light px-2 py-1 rounded">{{ $card->code }}</code>
                            @if($card->is_featured)
                            <br><span class="badge bg-warning text-dark small mt-1"><i class="bi bi-star me-1"></i> Featured</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $card->title }}</strong>
                            @if($card->description)
                            <br><small class="text-muted">{{ Str::limit($card->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="text-primary fw-bold">{{ formatPrice($card->balance) }}</span>
                            <br><small class="text-muted">of {{ formatPrice($card->initial_amount) }}</small>
                        </td>
                        <td>
                            @if($card->discount_type === 'percent')
                            <span class="badge bg-info">{{ $card->discount_value }}%</span>
                            @else
                            <span class="badge bg-info">{{ formatPrice($card->discount_value) }}</span>
                            @endif
                            @if($card->min_order_amount > 0)
                            <br><small class="text-muted">Min: {{ formatPrice($card->min_order_amount) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($card->recipient_email)
                            <small class="d-block">{{ $card->recipient_email }}</small>
                            @endif
                            @if($card->recipient_name)
                            <small class="text-muted">{{ $card->recipient_name }}</small>
                            @endif
                            @if($card->sender_name)
                            <br><small class="text-muted">From: {{ $card->sender_name }}</small>
                            @endif
                            @if(!$card->recipient_email && !$card->recipient_name && !$card->sender_name)
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $card->usage_count }}</span>
                            @if($card->usage_limit)
                            <span class="text-muted">/ {{ $card->usage_limit }}</span>
                            @else
                            <span class="text-muted">/ ∞</span>
                            @endif
                        </td>
                        <td>
                            @if($card->expiry_date)
                            <small class="{{ $card->isExpired() ? 'text-danger' : 'text-muted' }}">
                                {{ $card->expiry_date->format('d M, Y') }}
                            </small>
                            @else
                            <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            @if($card->status === 'active')
                                @if($card->isExpired())
                                <span class="badge bg-danger">Expired</span>
                                @elseif($card->isFullyUsed())
                                <span class="badge bg-secondary">Used</span>
                                @else
                                <span class="badge bg-success">Active</span>
                                @endif
                            @elseif($card->status === 'expired')
                            <span class="badge bg-danger">Expired</span>
                            @elseif($card->status === 'used')
                            <span class="badge bg-secondary">Used</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <form action="{{ route('admin.marketing.gift-cards.toggle-status', $card->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $card->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $card->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $card->status === 'active' ? 'pause' : 'play' }}-circle"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.marketing.gift-cards.edit', $card->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.marketing.gift-cards.destroy', $card->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this gift card?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-gift text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No gift cards found</p>
                            <a href="{{ route('admin.marketing.gift-cards.create') }}" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-plus-lg me-1"></i> Add First Gift Card
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($giftCards->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Showing {{ $giftCards->firstItem() }} - {{ $giftCards->lastItem() }} of {{ $giftCards->total() }} gift cards
        </div>
        <div>
            {{ $giftCards->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Live search
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const filterStatus = document.getElementById('filterStatus');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 300);
    });

    filterStatus.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
</script>
@endpush
