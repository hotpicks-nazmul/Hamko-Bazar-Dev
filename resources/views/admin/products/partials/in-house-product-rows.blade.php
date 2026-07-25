@forelse($products ?? [] as $product)
@php
    $search = request('search');
    $isMatch = $search && (
        stripos($product->name, $search) !== false || 
        stripos($product->product_code, $search) !== false ||
        stripos($product->barcode ?? '', $search) !== false
    );
    $canEdit = auth()->user()->hasPermission('products.edit');
    $canDelete = auth()->user()->hasPermission('products.inhouse-delete');
@endphp
<tr data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-sku="{{ $product->sku }}" data-quantity="{{ $product->quantity }}" data-low-threshold="{{ $product->low_stock_threshold }}" class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input product-checkbox" value="{{ $product->id }}" onchange="updateBulkActions()">
    </td>
    <td>
        @php
            $imageUrl = $product->featured_image;
            if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                $imageUrl = '/storage/' . $imageUrl;
            }
        @endphp
        @if($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
        @else
        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
            <i class="bi bi-image text-muted"></i>
        </div>
        @endif
    </td>
    <td>
        <div class="fw-semibold">{{ $product->name }}</div>
        @if($product->brand)
            <small class="text-muted">{{ $product->brand }}</small>
        @endif
        @if($product->is_featured)
            <span class="badge bg-info ms-1">Featured</span>
        @endif
    </td>
    <td style="white-space: nowrap;">
        <div class="small text-truncate" style="max-width: 120px;">
            <span class="badge bg-primary">{{ $product->product_code }}</span>
            @if($product->barcode)
                <span class="badge bg-secondary">{{ $product->barcode }}</span>
            @endif
        </div>
    </td>
    <td>
        <span class="badge bg-light text-dark">{{ $product->category->name ?? 'Uncategorized' }}</span>
    </td>
    @if(auth()->user()->hasPermission('products.view-cost'))
    <td>
        @if($product->purchase_price || $product->cost_price)
            <span class="text-muted">৳{{ number_format($product->purchase_price ?? $product->cost_price, 0) }}</span>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    @endif
    <td>
        <div>
            @if($product->sale_price)
                <span class="text-decoration-line-through text-muted small">৳{{ number_format($product->price, 0) }}</span>
                <span class="text-danger fw-semibold">৳{{ number_format($product->sale_price, 0) }}</span>
            @else
                <span class="fw-semibold">৳{{ number_format($product->price, 0) }}</span>
            @endif
        </div>
        @if($product->profit_margin > 0)
            <small class="text-success">{{ $product->profit_margin }}% margin</small>
        @endif
    </td>
    <td>
        @if($product->quantity <= 0)
            <span class="badge bg-danger">{{ $product->quantity }}</span>
            <i class="bi bi-exclamation-circle text-danger" title="Out of Stock"></i>
        @elseif($product->quantity <= $product->low_stock_threshold)
            <span class="badge bg-warning text-dark">{{ $product->quantity }}</span>
            <i class="bi bi-exclamation-triangle text-warning" title="Low Stock (Threshold: {{ $product->low_stock_threshold }})"></i>
        @else
            <span class="badge bg-success">{{ $product->quantity }}</span>
        @endif
        <small class="text-muted d-block"><span class="badge bg-danger">Min: {{ $product->low_stock_threshold }}</span></small>
    </td>
    <td>
        <span class="fw-semibold">৳{{ number_format($product->stock_value, 0) }}</span>
    </td>
    <td>
        <button type="button" class="btn btn-sm {{ $product->is_active ? 'btn-success' : 'btn-outline-secondary' }} status-toggle" data-id="{{ $product->id }}">
            {{ $product->is_active ? 'Active' : 'Inactive' }}
        </button>
    </td>
    <td>
        <div class="btn-group">
            <a href="{{ $canEdit ? route('admin.products.edit', $product->id) : '#' }}" 
               class="btn btn-sm btn-outline-primary {{ $canEdit ? '' : 'disabled' }}" 
               title="{{ $canEdit ? 'Edit' : 'No permission' }}"
               {{ $canEdit ? '' : 'onclick="return false" style="pointer-events:none;opacity:0.4"' }}>
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" {{ $canDelete ? '' : 'disabled' }}>
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="{{ auth()->user()->hasPermission('products.view-cost') ? 11 : 10 }}" class="text-center py-5">
        <div class="text-muted">
            <i class="bi bi-box-seam display-4"></i>
            <p class="mt-2 mb-1">No in-house products found.</p>
            <p class="small">Try adjusting your filters or <a href="{{ route('admin.products.create') }}">add a new product</a>.</p>
        </div>
    </td>
</tr>
@endforelse
