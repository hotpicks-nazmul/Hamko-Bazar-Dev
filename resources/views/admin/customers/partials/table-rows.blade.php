@forelse($customers as $customer)
<tr>
    <td style="width: 40px;">
        <input type="checkbox" class="form-check-input customer-checkbox" value="{{ $customer->id }}">
    </td>
    <td>
        <div class="d-flex align-items-center">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                <span class="text-white fw-medium">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
            </div>
            <div>
                <div class="fw-medium">{{ $customer->name }}</div>
                <small class="text-muted">{!! $customer->phone ? phone_display($customer->phone) : 'No phone' !!}</small>
            </div>
        </div>
    </td>
    <td>{{ $customer->email }}</td>
    @if(auth()->user()->hasPermission('customers.view-financial'))
    <td><span class="badge bg-info">{{ $customer->orders_count ?? 0 }}</span></td>
    <td>৳{{ number_format($customer->total_spent ?? 0, 2) }}</td>
    @endif
    <td>
        <span class="badge bg-{{ $customer->status === 'active' ? 'success' : 'secondary' }}">
            {{ $customer->status === 'active' ? 'Active' : 'Inactive' }}
        </span>
    </td>
    <td>{{ $customer->created_at->format('d M, Y') }}</td>
    <td style="width: 140px;">
        <div class="btn-group">
            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                <i class="bi bi-eye"></i>
            </a>
            <button type="button" class="btn btn-sm {{ $customer->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }} status-toggle" data-id="{{ $customer->id }}" title="{{ $customer->status === 'active' ? 'Deactivate' : 'Activate' }}">
                <i class="bi bi-{{ $customer->status === 'active' ? 'pause-circle' : 'check-circle' }}"></i>
            </button>
            <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="d-flex" onsubmit="return confirm('Are you sure you want to delete this customer?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger delete-btn" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="{{ auth()->user()->hasPermission('customers.view-financial') ? 8 : 6 }}" class="text-center py-5">
        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No customers found</p>
    </td>
</tr>
@endforelse
