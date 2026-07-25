@forelse($wishlists as $wishlist)
    <tr>
        <td>
            <a href="{{ route('admin.wishlists.product', $wishlist->product_id) }}" class="text-decoration-none fw-semibold">
                {{ $wishlist->product->name ?? 'Deleted product' }}
            </a>
            <br>
            <small class="text-muted">{{ $wishlist->product->sku ?? '' }}</small>
        </td>
        <td class="text-end">
            @if($wishlist->product)
                ৳{{ number_format((float) $wishlist->product->final_price, 2) }}
            @else
                —
            @endif
        </td>
        <td><small class="text-muted">{{ optional($wishlist->created_at)->format('d M Y, H:i') }}</small></td>
        <td>
            <form method="POST" action="{{ route('admin.wishlists.destroy-single', $wishlist->id) }}"
                  onsubmit="return confirm('Remove this product from the wishlist?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Remove
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr><td colspan="4" class="text-center text-muted py-4">This customer has no saved products.</td></tr>
@endforelse
