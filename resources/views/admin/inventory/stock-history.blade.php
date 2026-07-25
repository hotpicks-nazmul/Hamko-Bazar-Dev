@extends('admin.layouts.app')

@section('title', 'Stock History')

@section('content')

<!-- Stats Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-arrow-down-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Total Stock In</span><span class="stat-card-value" id="statTotalIn">+{{ number_format($stats['total_in'] ?? 0) }}</span></div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-arrow-up-circle"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Total Stock Out</span><span class="stat-card-value" id="statTotalOut">{{ number_format($stats['total_out'] ?? 0) }}</span></div>
    </div>
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-arrow-repeat"></i></div>
        <div class="stat-card-content"><span class="stat-card-label">Adjustments</span><span class="stat-card-value" id="statAdjustments">{{ number_format($stats['adjustments'] ?? 0) }}</span></div>
    </div>
</div>

<!-- Filter Form -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Product</label>
                    <select name="product_id" id="filterProduct" class="form-select form-select-sm">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Action Type</label>
                    <select name="action_type" id="filterActionType" class="form-select form-select-sm">
                        <option value="">All Actions</option>
                        <option value="stock_in" {{ request('action_type') === 'stock_in' ? 'selected' : '' }}>Stock In</option>
                        <option value="stock_out" {{ request('action_type') === 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                        <option value="adjustment" {{ request('action_type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        <option value="order" {{ request('action_type') === 'order' ? 'selected' : '' }}>Order</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Date From</label>
                    <input type="date" name="date_from" id="filterDateFrom" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Date To</label>
                    <input type="date" name="date_to" id="filterDateTo" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.inventory.stock-history') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- History Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Inventory History</h6>
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-boxes me-1"></i> Back to Inventory
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Action</th>
                        <th class="text-center">Before</th>
                        <th class="text-center">Change</th>
                        <th class="text-center">After</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.inventory.partials.history-table-rows', ['history' => $history])
                </tbody>
            </table>
        </div>
        
        @if($history->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $history->firstItem() ?? 0 }} - {{ $history->lastItem() ?? 0 }} of {{ $history->total() }} entries
            </div>
            <div>
                {{ $history->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-submit on filter change
    ['filterProduct', 'filterActionType', 'filterDateFrom', 'filterDateTo'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', () => document.getElementById('filterForm').submit());
        }
    });
</script>
@endpush
