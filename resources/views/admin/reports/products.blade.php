@extends('admin.layouts.app')

@section('title', 'Products Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Products Report</h4>
        <p class="text-muted mb-0 small">Top 20 products by units sold (paid orders, cancellations excluded) and the shelf that needs attention.</p>
    </div>
    <a href="{{ route('admin.reports.export', 'products') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-download me-1"></i> Export CSV
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Products Listed</h6>
                <h3 class="mb-0">{{ number_format($topProducts->count()) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Units Sold (top 20)</h6>
                <h3 class="mb-0">{{ number_format($topProducts->sum('total_sold')) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Low Stock</h6>
                <h3 class="mb-0">{{ number_format($lowStockProducts->count()) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0">Best Sellers</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th class="text-end">Units Sold</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">In Stock</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProducts as $i => $product)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($product->featured_image)
                                <img src="{{ $product->featured_image }}" alt="" width="34" height="34" style="object-fit: cover; border-radius: 4px;">
                            @endif
                            <span class="fw-medium">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="text-muted small">{{ $product->sku ?: '—' }}</td>
                    <td class="text-end fw-semibold">{{ number_format((int) $product->total_sold) }}</td>
                    <td class="text-end">৳{{ number_format((float) $product->price, 2) }}</td>
                    <td class="text-end">
                        @if((int) $product->quantity <= 0)
                            <span class="badge bg-danger">Out</span>
                        @elseif((int) $product->quantity <= (int) $product->low_stock_threshold)
                            <span class="badge bg-warning text-dark">{{ (int) $product->quantity }}</span>
                        @else
                            <span class="text-muted">{{ (int) $product->quantity }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No products yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0">Low Stock (quantity at or below its threshold, still above zero)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th class="text-end">Quantity</th>
                    <th class="text-end">Threshold</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lowStockProducts as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td class="text-muted small">{{ $product->sku ?: '—' }}</td>
                    <td class="text-end"><span class="badge bg-warning text-dark">{{ (int) $product->quantity }}</span></td>
                    <td class="text-end text-muted">{{ (int) $product->low_stock_threshold }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">Nothing is running low.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
