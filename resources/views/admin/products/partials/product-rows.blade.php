@forelse($products ?? [] as $product)
@php
    $search = request('search');
    $isMatch = $search && (
        stripos($product->name, $search) !== false ||
        stripos($product->sku, $search) !== false ||
        stripos($product->product_code, $search) !== false ||
        stripos($product->short_description, $search) !== false
    );
    $canEdit   = auth()->user()->hasPermission('products.edit');
    $canDelete = auth()->user()->hasPermission('products.inhouse-delete');
    $canView   = auth()->user()->hasPermission('products.inhouse-view');
    $canCreate = auth()->user()->hasPermission('products.inhouse-duplicate');
@endphp
<tr data-id="{{ $product->id }}"
    data-name="{{ $product->name }}"
    data-product-code="{{ $product->product_code ?? $product->sku }}"
    data-short-description="{{ $product->short_description ?? '' }}"
    data-quantity="{{ $product->quantity }}"
    data-price="{{ $product->price }}"
    data-sale-price="{{ $product->sale_price ?? '' }}"
    data-category-id="{{ $product->category_id ?? '' }}"
    class="{{ $isMatch ? 'table-warning' : '' }}">
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
        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" width="50" height="50" class="rounded object-cover" style="object-fit: cover;">
        @else
        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
            <i class="bi bi-image text-white"></i>
        </div>
        @endif
    </td>
    <td>
        <div class="fw-semibold product-name">{{ $product->name }}</div>
        @if($product->short_description)
        <small class="text-muted d-none d-md-block">{{ Str::limit($product->short_description, 50) }}</small>
        @endif
    </td>
    <td>
        <code class="small product-code">{{ $product->product_code ?? $product->sku }}</code>
    </td>
    <td>
        <span class="badge bg-light text-dark">{{ $product->category->name ?? 'N/A' }}</span>
    </td>
    <td>
        @if($product->sale_price && $product->sale_price > 0)
            <del class="text-muted small">৳{{ number_format($product->price, 0) }}</del>
            <div class="text-danger fw-semibold">৳{{ number_format($product->sale_price, 0) }}</div>
        @else
            <span class="fw-semibold">৳{{ number_format($product->price, 0) }}</span>
        @endif
    </td>
    <td>
        @php
            $lowStockThreshold = $product->low_stock_threshold ?? 10;
            $isInStock = $product->quantity > $lowStockThreshold;
            $isLowStock = $product->quantity > 0 && $product->quantity <= $lowStockThreshold;
            $isOutOfStock = $product->quantity <= 0;
        @endphp
        <span class="badge {{ $isInStock ? 'bg-success' : ($isLowStock ? 'bg-warning text-dark' : 'bg-danger') }}">
            {{ $product->quantity }}
        </span>
        @if($isLowStock)
        <i class="bi bi-exclamation-triangle text-warning ms-1" title="Low stock (threshold: {{ $lowStockThreshold }})"></i>
        @endif
    </td>
    <td>
        <button type="button" class="btn btn-sm status-toggle {{ $product->is_active ? 'btn-success' : 'btn-outline-secondary' }}" 
                data-id="{{ $product->id }}" 
                data-status="{{ $product->is_active ? 'active' : 'inactive' }}"
                title="Click to toggle status">
            {{ $product->is_active ? 'Active' : 'Inactive' }}
        </button>
    </td>
    <td>
        <button type="button" class="btn btn-sm featured-toggle {{ $product->is_featured ? 'btn-info' : 'btn-outline-secondary' }}" 
                data-id="{{ $product->id }}" 
                data-featured="{{ $product->is_featured ? 'yes' : 'no' }}"
                title="Click to toggle featured">
            <i class="bi bi-star{{ $product->is_featured ? '-fill' : '' }}"></i>
        </button>
    </td>
    <td>
        <div class="btn-group">
            @if($canEdit)
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            @else
            <span class="btn btn-sm btn-outline-primary disabled" title="No permission" style="pointer-events:none;opacity:0.4">
                <i class="bi bi-pencil"></i>
            </span>
            @endif
            @if($canView)
            <button type="button" class="btn btn-sm btn-outline-info quick-edit-btn" data-id="{{ $product->id }}" title="Quick Edit">
                <i class="bi bi-lightning"></i>
            </button>
            @else
            <span class="btn btn-sm btn-outline-info disabled" title="No permission" style="pointer-events:none;opacity:0.4">
                <i class="bi bi-lightning"></i>
            </span>
            @endif
            @if($canCreate)
            <a href="{{ route('admin.products.duplicate', $product->id) }}" class="btn btn-sm btn-outline-secondary" title="Duplicate">
                <i class="bi bi-copy"></i>
            </a>
            @else
            <span class="btn btn-sm btn-outline-secondary disabled" title="No permission" style="pointer-events:none;opacity:0.4">
                <i class="bi bi-copy"></i>
            </span>
            @endif
            @if($canDelete)
            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-flex" onsubmit="return confirm('Are you sure you want to delete this product?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger delete-btn" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
            @else
            <span class="btn btn-sm btn-outline-danger disabled" title="No permission" style="pointer-events:none;opacity:0.4">
                <i class="bi bi-trash"></i>
            </span>
            @endif
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="10" class="text-center py-5">
        <div class="text-muted">
            <i class="bi bi-box-seam display-4"></i>
            <p class="mt-2 mb-1">No products found.</p>
            <p class="small">Try adjusting your filters or <a href="{{ route('admin.products.create') }}">add a new product</a>.</p>
        </div>
    </td>
</tr>
@endforelse
