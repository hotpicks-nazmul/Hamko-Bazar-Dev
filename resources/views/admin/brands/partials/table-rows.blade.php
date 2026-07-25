@foreach($brands as $brand)
    @include('admin.brands.partials.table-row', ['brand' => $brand])
@endforeach
@if($brands->isEmpty())
    <tr>
        <td colspan="8" class="text-center py-5">
            <i class="bi bi-award text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">No brands found.</p>
            <a href="{{ route('admin.brands.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Your First Brand
            </a>
        </td>
    </tr>
@endif
