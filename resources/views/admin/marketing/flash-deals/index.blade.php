@extends('admin.layouts.app')

@section('title', 'Flash Deals')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-lightning-charge"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Deals</span>
            <span class="stat-card-value">{{ number_format($stats['total']) }}</span>
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
        <div class="stat-card-icon"><i class="bi bi-star"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Featured</span>
            <span class="stat-card-value">{{ number_format($stats['featured'] ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Flash Deals</h4>
    <a href="{{ route('admin.marketing.flash-deals.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Flash Deal
    </a>
</div>

<!-- Flash Deals Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Duration</th>
                        <th>Products</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($flashDeals as $deal)
                    <tr>
                        <td>
                            <strong>{{ $deal->title }}</strong>
                            @if($deal->is_featured)
                            <br><span class="badge bg-warning text-dark small"><i class="bi bi-star me-1"></i> Featured</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted d-block">Start: {{ $deal->start_date->format('d M, Y h:i A') }}</small>
                            <small class="{{ $deal->isExpired() ? 'text-danger' : 'text-muted' }}">End: {{ $deal->end_date->format('d M, Y h:i A') }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $deal->products->count() }}</span>
                        </td>
                        <td>
                            @if($deal->is_featured)
                            <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Yes</span>
                            @else
                            <span class="text-muted">No</span>
                            @endif
                        </td>
                        <td>
                            @if($deal->status === 'active')
                                @if($deal->isExpired())
                                    <span class="badge bg-danger">Expired</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            @elseif($deal->status === 'expired')
                                <span class="badge bg-secondary">Expired</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <form action="{{ route('admin.marketing.flash-deals.toggle-status', $deal->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $deal->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $deal->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $deal->status === 'active' ? 'pause' : 'play' }}-circle"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.marketing.flash-deals.edit', $deal->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('admin.marketing.flash-deals.products', $deal->id) }}" class="btn btn-sm btn-outline-info" title="Manage Products">
                                    <i class="bi bi-box-seam"></i>
                                </a>
                                <form action="{{ route('admin.marketing.flash-deals.destroy', $deal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this flash deal?')">
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
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-lightning text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No flash deals found</p>
                            <a href="{{ route('admin.marketing.flash-deals.create') }}" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-plus-lg me-1"></i> Add First Flash Deal
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($flashDeals->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Showing {{ $flashDeals->firstItem() }} - {{ $flashDeals->lastItem() }} of {{ $flashDeals->total() }} deals
        </div>
        <div>
            {{ $flashDeals->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
