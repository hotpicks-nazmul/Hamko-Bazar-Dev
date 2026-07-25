@forelse($products as $product)
@php
    $search = request('search');
    $isMatch = $search && (
        stripos($product->name, $search) !== false || 
        stripos($product->description ?? '', $search) !== false
    );
@endphp
<tr class="{{ $isMatch ? 'table-warning' : '' }}">
    <td>
        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $product->id }}">
    </td>
    <td>{{ $product->id }}</td>
    <td>
        @php
            $imageUrl = $product->image;
            if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                $imageUrl = '/storage/' . $imageUrl;
            }
        @endphp
        @if($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
        @else
        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="bi bi-image text-white"></i>
        </div>
        @endif
    </td>
    <td>{{ $product->name }}</td>
    <td>
        @if($product->category)
        <span class="badge bg-secondary">{{ $product->category->name }}</span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td>${{ number_format($product->price, 2) }}</td>
    <td><span class="badge bg-info">{{ $product->commission_rate }}%</span></td>
    <td>{{ number_format($product->clicks) }}</td>
    <td>{{ number_format($product->conversions) }}</td>
    <td>
        @if($product->status === 'active')
        <span class="badge bg-success">Active</span>
        @else
        <span class="badge bg-danger">Inactive</span>
        @endif
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('admin.affiliate.products.show', $product->id) }}" class="btn btn-sm btn-outline-info" title="View">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('admin.affiliate.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.affiliate.products.destroy', $product->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
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
    <td colspan="11" class="text-center py-5">
        <i class="bi bi-folder text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No products found</p>
        <a href="{{ route('admin.affiliate.products.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First Product
        </a>
    </td>
</tr>
@endforelse
