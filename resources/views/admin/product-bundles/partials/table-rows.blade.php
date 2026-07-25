@foreach($bundles as $bundle)
    @include('admin.product-bundles.partials.table-row', ['bundle' => $bundle])
@endforeach
@if($bundles->isEmpty())
    <tr>
        <td colspan="9" class="text-center py-5">
            <i class="bi bi-boxes text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">No product bundles found.</p>
            <a href="{{ route('admin.product-bundles.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Create Your First Bundle
            </a>
        </td>
    </tr>
@endif
