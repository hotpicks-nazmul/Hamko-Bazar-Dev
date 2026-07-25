{{--
    One media card. Shared by the first render (index.blade.php) and the auto-scroll batches that
    MediaController@index returns as a fragment, so a card appended while scrolling is byte-identical
    to the ones the page printed — no second copy to drift.
--}}
@foreach($files as $file)
<div class="media-item" data-path="{{ $file['path'] }}" onclick='viewFile(@json($file["path"]))'>
    <input type="checkbox" class="form-check-input media-checkbox"
           value="{{ $file['path'] }}"
           onclick="event.stopPropagation(); toggleSelection(this);">

    <div class="media-menu-wrap" onclick="event.stopPropagation()">
        <button type="button" class="media-menu-toggle" data-bs-toggle="dropdown"
                data-bs-display="static" aria-expanded="false" aria-label="More actions" title="More actions">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
                <button type="button" class="dropdown-item" onclick='viewFile(@json($file["path"]))'>
                    <i class="bi bi-eye me-2"></i>View
                </button>
            </li>
            <li>
                <button type="button" class="dropdown-item" onclick='copyUrl(@json($file["url"]))'>
                    <i class="bi bi-clipboard me-2"></i>Copy URL
                </button>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <button type="button" class="dropdown-item text-danger" onclick='deleteFile(@json($file["path"]))'>
                    <i class="bi bi-trash me-2"></i>Delete
                </button>
            </li>
        </ul>
    </div>

    <div class="preview">
        @if($file['is_image'])
        <img src="{{ $file['thumb_url'] ?? $file['url'] }}" alt="{{ $file['name'] }}" loading="lazy">
        @else
        <div class="file-icon">
            <i class="bi bi-file-earmark"></i>
        </div>
        @endif
    </div>

    <div class="info">
        <div class="name" title="{{ $file['name'] }}">{{ $file['name'] }}</div>
        <div class="size d-flex align-items-center gap-1 flex-wrap">
            <span class="badge bg-dark" title="{{ $file['mime'] }}">{{ $file['format'] }}</span>
            <span>{{ $file['size_formatted'] }}</span>
            @if($file['dimensions'])
                <span class="text-muted" title="Dimensions">&middot; {{ $file['dimensions'] }}</span>
            @endif
        </div>
    </div>
</div>
@endforeach
