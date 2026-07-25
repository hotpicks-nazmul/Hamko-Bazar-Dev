@forelse($carriers as $carrier)
    @include('admin.delivery.carriers.partials.table-row', ['carrier' => $carrier])
@empty
    <tr>
        <td colspan="11" class="text-center py-5">
            <i class="bi bi-truck text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">No carriers found.</p>
            <a href="{{ route('admin.delivery.carriers.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Your First Carrier
            </a>
        </td>
    </tr>
@endforelse
