@forelse($deliveryBoys as $boy)
    @php
        $searchTerm = request('search');
        $isMatch = $searchTerm && (
            stripos($boy->name, $searchTerm) !== false || 
            stripos($boy->phone, $searchTerm) !== false ||
            stripos($boy->email ?? '', $searchTerm) !== false
        );
    @endphp
    <tr class="{{ $isMatch ? 'table-warning' : '' }}">
        <td>
            <input type="checkbox" name="delivery_boys[]" value="{{ $boy->id }}" class="form-check-input row-checkbox">
        </td>
        <td>
            <div class="d-flex align-items-center">
                <img src="{{ $boy->photo_url }}" alt="{{ $boy->name }}" 
                     class="rounded-circle me-3" width="45" height="45" 
                     onerror="this.src='{{ asset('images/default-delivery-boy.png') }}'">
                <div>
                    <h6 class="mb-0">{{ $boy->name }}</h6>
                    <small class="text-muted">ID: {{ $boy->id }}</small>
                </div>
            </div>
        </td>
        <td>
            <div>
                <div><i class="bi bi-phone me-1 text-muted"></i> {{ $boy->phone }}</div>
                @if($boy->email)
                    <div><i class="bi bi-envelope me-1 text-muted"></i> {{ $boy->email }}</div>
                @endif
            </div>
        </td>
        <td>
            @if($boy->zone)
                <span class="badge bg-info">{{ $boy->zone->name }}</span>
            @else
                <span class="text-muted">Not Assigned</span>
            @endif
        </td>
        <td>
            @if($boy->vehicle_type)
                <div>
                    <span class="badge bg-secondary">{{ $boy->vehicle_type_label }}</span>
                    @if($boy->vehicle_number)
                        <small class="d-block text-muted">{{ $boy->vehicle_number }}</small>
                    @endif
                </div>
            @else
                <span class="text-muted">Not Set</span>
            @endif
        </td>
        <td>
            <div class="small">
                <div class="d-flex align-items-center mb-1">
                    <span class="text-muted me-2">Success Rate:</span>
                    <span class="fw-bold {{ $boy->success_rate >= 90 ? 'text-success' : ($boy->success_rate >= 70 ? 'text-warning' : 'text-danger') }}">
                        {{ $boy->success_rate }}%
                    </span>
                </div>
                <div class="text-muted">
                    {{ $boy->successful_deliveries }}/{{ $boy->total_deliveries }} deliveries
                </div>
            </div>
        </td>
        <td>
            <div>
                {!! $boy->status_label !!}
                @if($boy->is_active)
                    @if($boy->is_available)
                        <span class="badge bg-success ms-1">Available</span>
                    @else
                        <span class="badge bg-secondary ms-1">Busy</span>
                    @endif
                @endif
            </div>
        </td>
        <td>
            <div class="d-flex gap-1">
                <a href="{{ route('admin.delivery.delivery-boys.edit', $boy->id) }}" 
                   class="btn btn-sm btn-light" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
                <button type="button" class="btn btn-sm btn-light toggle-availability" 
                        data-id="{{ $boy->id }}" 
                        data-available="{{ $boy->is_available ? 0 : 1 }}"
                        title="{{ $boy->is_available ? 'Mark Unavailable' : 'Mark Available' }}">
                    <i class="bi {{ $boy->is_available ? 'bi-bicycle' : 'bi-pause-circle' }}"></i>
                </button>
                <button type="button" class="btn btn-sm btn-light toggle-status" 
                        data-id="{{ $boy->id }}" 
                        data-active="{{ $boy->is_active ? 0 : 1 }}"
                        title="{{ $boy->is_active ? 'Deactivate' : 'Activate' }}">
                    <i class="bi {{ $boy->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                </button>
                <button type="button" class="btn btn-sm btn-light text-danger" 
                        onclick="deleteDeliveryBoy({{ $boy->id }})" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-5">
            <i class="bi bi-person-badge text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mb-2 mt-2">No delivery boys found</p>
            <a href="{{ route('admin.delivery.delivery-boys.create') }}" class="btn btn-sm btn-primary mt-1">
                <i class="bi bi-plus-lg me-1"></i> Add First Delivery Boy
            </a>
        </td>
    </tr>
@endforelse
