@forelse($partners as $partner)
    @include('admin.delivery.partners.partials.table-row', ['partner' => $partner])
@empty
    <tr>
        <td colspan="9" class="text-center py-5">
            <i class="bi bi-truck text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">No delivery partners found.</p>
            <a href="{{ route('admin.delivery.partners.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Your First Partner
            </a>
        </td>
    </tr>
@endforelse
