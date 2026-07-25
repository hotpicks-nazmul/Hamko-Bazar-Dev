@forelse($wishlists as $wishlist)
    <tr>
        <td>
            <a href="{{ route('admin.wishlists.user', $wishlist->user_id) }}" class="text-decoration-none fw-semibold">
                {{ $wishlist->user->name ?? 'Unknown customer' }}
            </a>
            <br>
            <small class="text-muted">{{ $wishlist->user->email ?? '' }}</small>
        </td>
        <td><small class="text-muted">{{ optional($wishlist->created_at)->format('d M Y, H:i') }}</small></td>
        <td>
            <form method="POST" action="{{ route('admin.wishlists.destroy-single', $wishlist->id) }}"
                  onsubmit="return confirm('Remove this item from the wishlist?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Remove
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr><td colspan="3" class="text-center text-muted py-4">Nobody has saved this product yet.</td></tr>
@endforelse
