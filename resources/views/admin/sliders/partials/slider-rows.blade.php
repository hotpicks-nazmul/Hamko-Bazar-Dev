@foreach($sliders as $slider)
<tr data-id="{{ $slider->id }}">
    <td>
        <i class="bi bi-grip-vertical text-muted cursor-move" style="cursor: move;"></i>
        <span class="ms-2 text-muted small">{{ $slider->order + 1 }}</span>
    </td>
    <td>
        @php
            $imageUrl = $slider->image;
            if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                $imageUrl = '/storage/' . $imageUrl;
            }
        @endphp
        @if($imageUrl)
        <img src="{{ $imageUrl }}" 
            alt="{{ $slider->title }}" 
            class="img-fluid rounded" 
            style="max-height: 60px; width: 100px; object-fit: cover;">
        @else
        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 100px; height: 60px;">
            <i class="bi bi-image text-white"></i>
        </div>
        @endif
    </td>
    <td>
        <span class="fw-medium">{{ $slider->title }}</span>
    </td>
    <td>
        <span class="text-muted">{{ $slider->subtitle ?? '-' }}</span>
    </td>
    <td>
        @if($slider->button_text)
        <span class="badge bg-info">{{ $slider->button_text }}</span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        @if($slider->is_active)
        <span class="badge bg-success">Active</span>
        @else
        <span class="badge bg-secondary">Inactive</span>
        @endif
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('admin.sliders.edit', $slider) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this slider?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@endforeach
