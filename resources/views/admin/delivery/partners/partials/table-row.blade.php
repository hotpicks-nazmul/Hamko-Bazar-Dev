<tr @if(request('search'))
    @php
        $search = request('search');
        $isMatch = stripos($partner->name, $search) !== false || 
                   stripos($partner->slug, $search) !== false ||
                   stripos($partner->contact_person ?? '', $search) !== false ||
                   stripos($partner->email ?? '', $search) !== false;
    @endphp
    class="{{ $isMatch ? 'table-warning' : '' }}"
@endif>
    <td>
        <input type="checkbox" class="form-check-input partner-checkbox" value="{{ $partner->id }}" onclick="event.stopPropagation();">
    </td>
    <td>
        @php
            $logoUrl = $partner->logo;
            if ($logoUrl && !str_starts_with($logoUrl, '/storage/') && !str_starts_with($logoUrl, 'http')) {
                $logoUrl = '/storage/' . $logoUrl;
            }
        @endphp
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $partner->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
        @else
            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-truck text-white"></i>
            </div>
        @endif
    </td>
    <td>
        <div class="fw-medium">{{ $partner->name }}</div>
        <small class="text-muted">{{ $partner->slug }}</small>
    </td>
    <td>
        @switch($partner->service_type)
            @case('express')
                <span class="badge bg-primary">Express</span>
                @break
            @case('standard')
                <span class="badge bg-info">Standard</span>
                @break
            @case('overnight')
                <span class="badge bg-warning text-dark">Overnight</span>
                @break
            @case('international')
                <span class="badge bg-dark">International</span>
                @break
            @case('all')
                <span class="badge bg-success">All Services</span>
                @break
            @default
                <span class="badge bg-secondary">{{ $partner->service_type }}</span>
        @endswitch
    </td>
    <td>
        <div>{{ $partner->contact_person ?? '-' }}</div>
        <small class="text-muted">{{ $partner->phone ?? '' }}</small>
    </td>
    <td>
        <button type="button" 
                class="btn btn-sm status-toggle {{ $partner->is_active ? 'btn-success' : 'btn-outline-secondary' }}" 
                data-id="{{ $partner->id }}"
                onclick="event.stopPropagation();">
            {{ $partner->is_active ? 'Active' : 'Inactive' }}
        </button>
    </td>
    <td>
        <button type="button" 
                class="btn btn-sm featured-toggle {{ $partner->is_featured ? 'btn-info' : 'btn-outline-secondary' }}" 
                data-id="{{ $partner->id }}"
                onclick="event.stopPropagation();">
            <i class="bi bi-{{ $partner->is_featured ? 'star-fill' : 'star' }}"></i>
        </button>
    </td>
    <td>{{ $partner->sort_order }}</td>
    <td>
        <div class="btn-group">
            <a href="{{ route('admin.delivery.partners.edit', $partner->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.delivery.partners.destroy', $partner->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this partner?')">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
