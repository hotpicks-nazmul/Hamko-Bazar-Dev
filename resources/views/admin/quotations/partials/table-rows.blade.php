@forelse($quotations as $quotation)
@php
    $search = $search ?? '';
    $isMatch = $search && (
        stripos($quotation->quotation_number, $search) !== false || 
        stripos($quotation->customer_name, $search) !== false ||
        stripos($quotation->customer_email, $search) !== false
    );
@endphp
<tr class="{{ $isMatch ? 'table-warning' : '' }}">
    <td class="ps-3">
        <input type="checkbox" class="form-check-input quotation-checkbox" value="{{ $quotation->id }}">
    </td>
    <td>
        <a href="{{ route('admin.quotations.show', $quotation) }}" class="text-decoration-none fw-medium">
            @if($search && stripos($quotation->quotation_number, $search) !== false)
                {!! preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', $quotation->quotation_number) !!}
            @else
                {{ $quotation->quotation_number }}
            @endif
        </a>
        @if($quotation->is_expired && $quotation->status !== 'converted')
            <span class="badge bg-secondary ms-1">Expired</span>
        @endif
    </td>
    <td>
        <div class="fw-medium">
            @if($search && stripos($quotation->customer_name, $search) !== false)
                {!! preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', $quotation->customer_name) !!}
            @else
                {{ $quotation->customer_name }}
            @endif
        </div>
        @if($quotation->customer_email)
            <small class="text-muted">
                @if($search && stripos($quotation->customer_email, $search) !== false)
                    {!! preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', $quotation->customer_email) !!}
                @else
                    {{ $quotation->customer_email }}
                @endif
            </small>
        @endif
    </td>
    <td>
        <span class="fw-medium">{{ number_format($quotation->total, 2) }}</span>
    </td>
    <td>
        <span class="badge {{ $quotation->status_badge_class }}">
            {{ ucfirst($quotation->status) }}
        </span>
    </td>
    <td>
        <span class="{{ $quotation->is_expired && $quotation->status !== 'converted' ? 'text-danger' : '' }}">
            {{ $quotation->valid_until->format('M d, Y') }}
        </span>
    </td>
    <td>
        <small class="text-muted">{{ $quotation->created_at->format('M d, Y') }}</small>
    </td>
    <td class="text-end pe-3">
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('admin.quotations.show', $quotation) }}">
                        <i class="bi bi-eye me-2"></i> View
                    </a>
                </li>
                @if($quotation->can_edit)
                <li>
                    <a class="dropdown-item" href="{{ route('admin.quotations.edit', $quotation) }}">
                        <i class="bi bi-pencil me-2"></i> Edit
                    </a>
                </li>
                @endif
                <li>
                    <a class="dropdown-item" href="{{ route('admin.quotations.print', $quotation) }}" target="_blank">
                        <i class="bi bi-printer me-2"></i> Print
                    </a>
                </li>
                @if($quotation->can_convert)
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button class="dropdown-item text-success" type="button" 
                            onclick="document.getElementById('convertForm{{ $quotation->id }}').submit()">
                        <i class="bi bi-cart-check me-2"></i> Convert to Order
                    </button>
                    <form id="convertForm{{ $quotation->id }}" action="{{ route('admin.quotations.convert-to-order', $quotation) }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
                @endif
                @if($quotation->status !== 'converted')
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button class="dropdown-item text-danger" type="button" 
                            onclick="if(confirm('Are you sure you want to delete this quotation?')) document.getElementById('deleteForm{{ $quotation->id }}').submit()">
                        <i class="bi bi-trash me-2"></i> Delete
                    </button>
                    <form id="deleteForm{{ $quotation->id }}" action="{{ route('admin.quotations.destroy', $quotation) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </li>
                @endif
            </ul>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <i class="bi bi-folder text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No quotations found</p>
        <a href="{{ route('admin.quotations.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Create First Quotation
        </a>
    </td>
</tr>
@endforelse
