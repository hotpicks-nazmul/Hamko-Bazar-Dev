@forelse($products as $product)
<tr>
    <td>
        <input type="checkbox" class="form-check-input product-checkbox" value="{{ $product->id }}" onchange="updateSelection({{ $product->id }}, this.checked)">
    </td>
    <td>
        @php
            $imageUrl = $product->featured_image;
            if($imageUrl) {
                $imageUrl = ltrim($imageUrl, '/');
                if (str_starts_with($imageUrl, 'http')) {
                    // Already full URL - use as is
                } elseif (str_starts_with($imageUrl, 'storage/')) {
                    // Path starts with storage/ - prepend just /
                    $imageUrl = '/' . $imageUrl;
                } elseif (str_starts_with($imageUrl, 'products/')) {
                    // Path starts with products/ - add /storage/
                    $imageUrl = '/storage/' . $imageUrl;
                } else {
                    // Other relative paths - prepend /storage/
                    $imageUrl = '/storage/' . $imageUrl;
                }
            }
        @endphp
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
        @else
            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                <i class="bi bi-file-earmark text-muted"></i>
            </div>
        @endif
    </td>
    <td>
        <div>
            <a href="{{ route('admin.products.digital.edit', $product->id) }}" class="fw-semibold text-decoration-none">
                {{ $product->name }}
            </a>
            @if($product->is_featured)
                <span class="badge bg-warning text-dark ms-1">Featured</span>
            @endif
            @if($product->requires_license_key)
                <span class="badge bg-info ms-1">License</span>
            @endif
        </div>
        <small class="text-muted">SKU: {{ $product->sku }}</small>
        @if($product->version)
            <span class="badge bg-secondary ms-1">v{{ $product->version }}</span>
        @endif
    </td>
    <td>
        @if($product->file_format)
            <div>
                <span class="badge bg-light text-dark">{{ $product->file_format }}</span>
                <span class="text-muted small">{{ $product->file_size_formatted }}</span>
            </div>
        @elseif($product->download_link)
            <div>
                <span class="badge bg-light text-dark">External Link</span>
            </div>
        @else
            <span class="text-muted">No file</span>
        @endif
    </td>
    <td>
        @if($product->digitalCategory)
            <span class="badge bg-light text-dark">{{ $product->digitalCategory->name }}</span>
        @elseif($product->category)
            <span class="badge bg-light text-dark">{{ $product->category->name }}</span>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        <div>
            @if($product->sale_price && $product->sale_price < $product->price)
                <del class="text-muted small">৳{{ number_format($product->price, 0) }}</del>
                <span class="text-danger fw-semibold">৳{{ number_format($product->sale_price, 0) }}</span>
            @else
                <span class="fw-semibold">৳{{ number_format($product->price, 0) }}</span>
            @endif
        </div>
    </td>
    <td>
        <div class="text-center">
            <div class="fw-semibold">{{ $product->digitalDownloads()->count() }}</div>
            <small class="text-muted">downloads</small>
        </div>
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
        <div class="btn-group">
            <a href="{{ route('admin.products.digital.edit', $product->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-info" onclick="showDownloadStats({{ $product->id }}); event.stopPropagation();" title="Download Stats">
                <i class="bi bi-bar-chart"></i>
            </button>
            @if($product->requires_license_key)
            <button type="button" class="btn btn-sm btn-outline-warning" onclick="showLicenseKeys({{ $product->id }}); event.stopPropagation();" title="License Keys">
                <i class="bi bi-key"></i>
            </button>
            @endif
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteProduct({{ $product->id }}); event.stopPropagation();" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <div class="text-muted">
            <i class="bi bi-file-earmark display-4"></i>
            <p class="mt-2">No digital products found.</p>
            <a href="{{ route('admin.products.digital.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Digital Product
            </a>
        </div>
    </td>
</tr>
@endforelse
