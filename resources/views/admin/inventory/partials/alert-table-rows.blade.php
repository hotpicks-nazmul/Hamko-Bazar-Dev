@forelse($products as $product)
@php
    $threshold = $product->low_stock_threshold ?? 10;
    $criticalThreshold = floor($threshold * 0.5);
    $isOutOfStock = $product->quantity <= 0;
    $isCriticalLow = !$isOutOfStock && $product->quantity <= $criticalThreshold;
    $isLowStock = !$isOutOfStock && !$isCriticalLow;
@endphp
<tr class="{{ $isOutOfStock ? 'table-danger' : ($isCriticalLow ? 'table-warning' : '') }}">
    <td>
        @php
            $imageUrl = $product->featured_image;
            if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                $imageUrl = '/storage/' . $imageUrl;
            }
        @endphp
        @if($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
        @else
        <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
            <i class="bi bi-image text-white"></i>
        </div>
        @endif
        <div class="d-inline-block">
            <div class="fw-medium">{{ $product->name }}</div>
            <small class="text-muted">{{ $product->category?->name ?? 'Uncategorized' }}</small>
        </div>
    </td>
    <td style="white-space: nowrap;">
    <div class="small text-truncate" style="max-width: 120px;">
        <span class="badge bg-primary">{{ $product->product_code ?? $product->sku ?? 'N/A' }}</span>
    </div>
</td>
    <td class="text-center">
        <span class="fw-bold {{ $isOutOfStock ? 'text-danger' : 'text-warning' }}">
            {{ $product->quantity }}
        </span>
    </td>
    <td class="text-center">
        {{ $product->low_stock_threshold ?? 10 }}
    </td>
    <td class="text-center">
        @if($isOutOfStock)
        <span class="badge bg-danger alert-badge">Out of Stock</span>
        @elseif($isCriticalLow)
        <span class="badge bg-warning text-dark alert-badge">Critical Low</span>
        @else
        <span class="badge bg-info text-dark alert-badge">Low Stock</span>
        @endif
    </td>
    <td class="text-center">
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-success" onclick="showRestockModal({{ $product->id }})" title="Restock">
                <i class="bi bi-plus-lg"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-info" onclick="showThresholdModal({{ $product->id }}, {{ $product->low_stock_threshold ?? 10 }})" title="Set Threshold">
                <i class="bi bi-bell"></i>
            </button>
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center py-5">
        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
        <p class="text-muted mb-0 mt-2">No low stock products! All items are well-stocked.</p>
    </td>
</tr>
@endforelse
