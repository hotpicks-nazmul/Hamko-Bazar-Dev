@forelse($wishlists as $wishlist)
    <tr>
        <td>
            <input type="checkbox" class="form-check-input wishlist-checkbox" value="{{ $wishlist->id }}" onchange="toggleCheckbox({{ $wishlist->id }})">
        </td>
        <td>
            <a href="{{ route('admin.wishlists.user', $wishlist->user->id) }}" class="text-decoration-none fw-semibold">
                {{ $wishlist->user->name }}
            </a>
            <br>
            <small class="text-muted">{{ $wishlist->user->email }}</small>
        </td>
        <td>
            <div class="d-flex align-items-center">
                @php
                    $productImage = null;
                    $product = $wishlist->product;
                    
                    // Check featured_image first (which already contains /storage/ or http URL)
                    if ($product->featured_image) {
                        $productImage = $product->featured_image;
                    } elseif ($product->image) {
                        $productImage = $product->image;
                    } elseif ($product->images && is_array($product->images) && count($product->images) > 0) {
                        $productImage = $product->images[0];
                    }
                    
                    $imageUrl = null;
                    if ($productImage) {
                        if (str_starts_with($productImage, 'http')) {
                            $imageUrl = $productImage;
                        } elseif (str_starts_with($productImage, '/storage/')) {
                            $imageUrl = asset($productImage);
                        } else {
                            $imageUrl = asset('storage/' . $productImage);
                        }
                    }
                @endphp
                @if($imageUrl)
                    <img src="{{ $imageUrl }}" 
                         alt="{{ $wishlist->product->name }}" 
                         class="rounded me-2" 
                         style="width: 40px; height: 40px; object-fit: cover;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="rounded bg-light me-2 d-flex align-items-center justify-content-center" 
                         style="width: 40px; height: 40px; display: none;">
                        <i class="bi bi-box text-muted"></i>
                    </div>
                @else
                    <div class="rounded bg-light me-2 d-flex align-items-center justify-content-center" 
                         style="width: 40px; height: 40px;">
                        <i class="bi bi-box text-muted"></i>
                    </div>
                @endif
                <div>
                    <a href="{{ route('admin.wishlists.product', $wishlist->product->id) }}" class="text-decoration-none">
                        {{ Str::limit($wishlist->product->name, 40) }}
                    </a>
                    @if($wishlist->product->sku)
                        <br>
                        <small class="text-muted">SKU: {{ $wishlist->product->sku }}</small>
                    @endif
                </div>
            </div>
        </td>
        <td>
            @if($wishlist->product->category)
                <span class="badge bg-light text-dark">
                    {{ $wishlist->product->category->name }}
                </span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            <small>{{ $wishlist->created_at->format('M d, Y') }}</small>
            <br>
            <small class="text-muted">{{ $wishlist->created_at->format('h:i A') }}</small>
        </td>
        <td>
            <div class="btn-group">
                <a href="{{ route('admin.products.edit', $wishlist->product->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="View Product">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('admin.customers.show', $wishlist->user->id) }}" target="_blank" class="btn btn-sm btn-outline-info" title="View Customer">
                    <i class="bi bi-person"></i>
                </a>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteWishlist({{ $wishlist->id }})" title="Remove">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-5">
            <i class="bi bi-heart text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mb-2 mt-2">No wishlist items found</p>
            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-primary mt-1">
                <i class="bi bi-box-seam me-1"></i> Browse Products
            </a>
        </td>
    </tr>
@endforelse
