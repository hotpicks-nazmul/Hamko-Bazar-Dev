@forelse($schedules as $schedule)
<tr>
    <td>
        <input type="checkbox" class="form-check-input item-checkbox" value="{{ $schedule->id }}" 
               onchange="toggleItem(this.value)" {{ in_array($schedule->id, old('selected', [])) ? 'checked' : '' }}>
    </td>
    <td>
        <div class="fw-medium">{{ $schedule->name }}</div>
        @if($schedule->description)
        <small class="text-muted">{{ Str::limit($schedule->description, 50) }}</small>
        @endif
    </td>
    <td>
        {!! $schedule->type_badge !!}
    </td>
    <td>
        @if($schedule->day_of_week === null)
        <span class="text-muted">Not set</span>
        @elseif($schedule->day_of_week == 7)
        <span>Everyday</span>
        @else
        <span>{{ $schedule->day_name }}</span>
        @endif
    </td>
    <td>
        <div>{{ $schedule->time_range }}</div>
        @if($schedule->cutoff_time)
        <small class="text-muted">Cutoff: {{ date('h:i A', strtotime($schedule->cutoff_time)) }}</small>
        @endif
    </td>
    <td>
        @if($schedule->additional_fee > 0)
        <span class="text-success">+{{ number_format($schedule->additional_fee, 2) }}</span>
        @else
        <span class="text-muted">Free</span>
        @endif
    </td>
    <td>
        {!! $schedule->status_badge !!}
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('admin.delivery.schedules.edit', $schedule->id) }}" 
               class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.delivery.schedules.toggle', $schedule->id) }}" method="POST" class="d-inline">
                @csrf
                @method('POST')
                <button type="submit" class="btn btn-sm {{ $schedule->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                        title="{{ $schedule->is_active ? 'Deactivate' : 'Activate' }}">
                    <i class="bi {{ $schedule->is_active ? 'bi-x-circle' : 'bi-check-circle' }}"></i>
                </button>
            </form>
            <form action="{{ route('admin.delivery.schedules.destroy', $schedule->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" 
                        title="Delete"
                        onclick="return confirm('Are you sure you want to delete &quot;{{ $schedule->name }}&quot;?')">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <i class="bi bi-calendar-week text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No delivery schedules found</p>
        <a href="{{ route('admin.delivery.schedules.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First Schedule
        </a>
    </td>
</tr>
@endforelse
