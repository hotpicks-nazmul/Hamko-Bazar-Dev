<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Select Media</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
    html, body { height: 100%; }
    body { background: #f6f7f9; font-size: 14px; margin: 0; display: flex; flex-direction: column; }
    .picker-toolbar { background: #fff; border-bottom: 1px solid #e5e7eb; padding: .6rem .75rem; }
    .picker-body { flex: 1 1 auto; overflow-y: auto; padding: .75rem; }
    .picker-footer { background: #fff; border-top: 1px solid #e5e7eb; padding: .6rem .75rem; }
    .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: .6rem; }
    /* Cards carry the same stacked info block as the Media Library page: image, then the file name on its
       own line, then format / size / dimensions on a second line. Squeezing all of it into one overlay
       line made the picker unreadable. */
    .media-card {
        position: relative; display: flex; flex-direction: column; background: #fff;
        border: 2px solid #e5e7eb; border-radius: 8px; overflow: hidden; cursor: pointer;
        transition: border-color .12s, box-shadow .12s;
    }
    .media-card:hover { border-color: #94a3b8; }
    .media-card.selected { border-color: #2D5A27; box-shadow: 0 0 0 3px rgba(45,90,39,.15); }
    .media-card .thumb { position: relative; aspect-ratio: 4 / 3; background: #f3f4f6; overflow: hidden; }
    .media-card .thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .media-card .info { padding: .35rem .45rem .4rem; border-top: 1px solid #eef0f2; }
    .media-card .info .name {
        font-size: 11px; font-weight: 600; color: #111827; line-height: 1.25;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .media-card .info .meta {
        display: flex; align-items: center; gap: .3rem; margin-top: 3px;
        font-size: 10px; color: #6b7280; white-space: nowrap; overflow: hidden;
    }
    .media-card .info .meta .fmt {
        flex: 0 0 auto; background: #111827; color: #fff; font-size: 9px; font-weight: 600;
        letter-spacing: .3px; padding: 0 4px; border-radius: 3px;
    }
    .media-check {
        position: absolute; top: 5px; right: 5px; width: 20px; height: 20px; border-radius: 50%;
        background: #fff; border: 2px solid #cbd5e1; display: flex; align-items: center; justify-content: center;
        font-size: 12px; color: #fff; line-height: 1; z-index: 2;
    }
    .media-card.selected .media-check { background: #2D5A27; border-color: #2D5A27; }
    .empty-state { text-align: center; color: #6b7280; padding: 2.5rem 1rem; }
</style>
</head>
<body>

<div class="picker-toolbar">
    <div class="d-flex gap-2 align-items-center">
        <div class="input-group input-group-sm flex-grow-1">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" id="mediaSearch" class="form-control" placeholder="Search file name…" value="{{ $search }}">
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="uploadBtn">
            <i class="bi bi-cloud-upload"></i> Upload
        </button>
        <input type="file" id="uploadInput" accept="image/*" multiple class="d-none">
    </div>
    <div class="mt-2 d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            {{ $paginator->total() }} image(s)
            @if($multiple) — select one or more @else — single selection @endif
        </span>
        <span class="text-muted small" id="uploadStatus"></span>
    </div>
</div>

<div class="picker-body">
    <div class="media-grid" id="mediaGrid">
        @forelse($paginator as $file)
            <div class="media-card"
                 data-path="{{ $file['path'] }}"
                 data-url="{{ $file['url'] }}"
                 data-name="{{ $file['name'] }}"
                 title="{{ $file['name'] }} — {{ $file['format'] }}{{ $file['dimensions'] ? ' · ' . $file['dimensions'] : '' }} — {{ $file['size_formatted'] }}">
                <div class="thumb">
                    <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" loading="lazy"
                         onerror="this.style.display='none'; this.parentElement.parentElement.querySelector('.info .name').textContent='⚠ broken';">
                    <span class="media-check"><i class="bi bi-check-lg"></i></span>
                </div>
                <div class="info">
                    <div class="name">{{ $file['name'] }}</div>
                    <div class="meta">
                        <span class="fmt">{{ $file['format'] }}</span>
                        <span>{{ $file['size_formatted'] }}</span>
                        @if($file['dimensions'])
                            <span>· {{ $file['dimensions'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="bi bi-images" style="font-size: 2rem;"></i>
                <p class="mb-1 mt-2">No images found.</p>
                <p class="small mb-0">Use the Upload button to add a new image.</p>
            </div>
        @endforelse
    </div>

    @if($paginator->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $paginator->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<div class="picker-footer d-flex justify-content-between align-items-center">
    <span class="text-muted small"><span id="selCount">0</span> selected</span>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="cancelBtn">Cancel</button>
        <button type="button" class="btn btn-sm btn-success" id="insertBtn" disabled>
            <i class="bi bi-check2-circle"></i> Use selected
        </button>
    </div>
</div>

<script>
(function () {
    'use strict';

    var MULTIPLE = {{ $multiple ? 'true' : 'false' }};
    var FIELD = @json($field);
    var PARENT_ORIGIN = window.location.origin;
    var selected = []; // [{path, url, name}]

    var grid = document.getElementById('mediaGrid');
    var selCount = document.getElementById('selCount');
    var insertBtn = document.getElementById('insertBtn');
    var searchInput = document.getElementById('mediaSearch');

    function find(path) {
        return selected.findIndex(function (s) { return s.path === path; });
    }

    function refreshFooter() {
        selCount.textContent = selected.length;
        insertBtn.disabled = selected.length === 0;
    }

    function toggle(card) {
        var path = card.dataset.path;
        var idx = find(path);
        if (idx > -1) {
            selected.splice(idx, 1);
            card.classList.remove('selected');
        } else {
            if (!MULTIPLE) {
                selected = [];
                grid.querySelectorAll('.media-card.selected').forEach(function (c) {
                    c.classList.remove('selected');
                });
            }
            selected.push({ path: path, url: card.dataset.url, name: card.dataset.name });
            card.classList.add('selected');
        }
        refreshFooter();
    }

    grid.addEventListener('click', function (e) {
        var card = e.target.closest('.media-card');
        if (!card) return;
        toggle(card);
        if (!MULTIPLE && selected.length === 1) {
            // single-select: act immediately, like a native file dialog
            send();
        }
    });

    searchInput.addEventListener('input', function () {
        var needle = this.value.trim().toLowerCase();
        grid.querySelectorAll('.media-card').forEach(function (card) {
            var hit = !needle || card.dataset.name.toLowerCase().indexOf(needle) > -1
                   || card.dataset.path.toLowerCase().indexOf(needle) > -1;
            card.style.display = hit ? '' : 'none';
        });
    });

    function send() {
        if (!selected.length) return;
        var payload = {
            source: 'hil-media-picker',
            action: 'select',
            field: FIELD,
            multiple: MULTIPLE,
            files: selected.slice()
        };
        window.parent.postMessage(payload, PARENT_ORIGIN);
    }

    insertBtn.addEventListener('click', send);

    document.getElementById('cancelBtn').addEventListener('click', function () {
        window.parent.postMessage({ source: 'hil-media-picker', action: 'cancel', field: FIELD }, PARENT_ORIGIN);
    });

    // ---- upload ----
    var uploadBtn = document.getElementById('uploadBtn');
    var uploadInput = document.getElementById('uploadInput');
    var uploadStatus = document.getElementById('uploadStatus');
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    uploadBtn.addEventListener('click', function () { uploadInput.click(); });

    uploadInput.addEventListener('change', function () {
        if (!this.files.length) return;
        var data = new FormData();
        for (var i = 0; i < this.files.length; i++) {
            data.append('files[]', this.files[i]);
        }
        uploadStatus.textContent = 'Uploading…';
        uploadBtn.disabled = true;

        fetch('{{ route('admin.media.picker.upload') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: data
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
        .then(function (res) {
            uploadBtn.disabled = false;
            if (!res.ok || !res.body.success) {
                uploadStatus.textContent = res.body.message || 'Upload failed';
                return;
            }
            uploadStatus.textContent = res.body.message || 'Uploaded';
            (res.body.uploaded || []).forEach(function (f) { prependCard(f); });
            var last = (res.body.uploaded || [])[0];
            if (last && !MULTIPLE) {
                selected = [{ path: last.path, url: last.url, name: last.name }];
                send();
            }
        })
        .catch(function () {
            uploadBtn.disabled = false;
            uploadStatus.textContent = 'Upload failed';
        });

        this.value = '';
    });

    function prependCard(file) {
        var card = document.createElement('div');
        card.className = 'media-card selected';
        card.dataset.path = file.path;
        card.dataset.url = file.url;
        card.dataset.name = file.name;
        card.title = file.name;
        card.innerHTML = '<div class="thumb"><img alt="">'
            + '<span class="media-check"><i class="bi bi-check-lg"></i></span></div>'
            + '<div class="info"><div class="name"></div><div class="meta">'
            + '<span class="fmt"></span><span class="sz"></span><span class="dim"></span></div></div>';

        // built node by node: the file name comes from an upload, so it is never interpolated as HTML
        card.querySelector('.thumb img').src = file.url;
        card.querySelector('.info .name').textContent = file.name;
        card.querySelector('.info .fmt').textContent = file.format || '';
        card.querySelector('.info .sz').textContent = file.size_formatted || '';
        card.querySelector('.info .dim').textContent = file.dimensions ? '· ' + file.dimensions : '';

        var empty = grid.querySelector('.empty-state');
        if (empty) { empty.remove(); }

        if (!MULTIPLE) {
            selected = [];
            grid.querySelectorAll('.media-card.selected').forEach(function (c) { c.classList.remove('selected'); });
        }
        grid.prepend(card);
        selected.push({ path: file.path, url: file.url, name: file.name });
        refreshFooter();
    }
})();
</script>
</body>
</html>
