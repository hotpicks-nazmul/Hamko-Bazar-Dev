{{--
    Shared media picker: modal + JS API, available on every admin page
    (included once from admin/layouts/app.blade.php).

    Two ways to use it:

    1. Automatic — every <input type="file"> whose accept attribute mentions an image
       (or that carries data-media-field) opens the Media Library instead of the OS dialog.
       The chosen file is fetched and attached to the input as a File, so the form posts
       exactly what it posted before and no controller has to change:
         - single input  -> replaces the current selection
         - multiple input -> appends to it
       After attaching, a bubbling "change" event fires, so existing preview handlers run.

    2. Manual — for pages that want to store a library path instead of a copy:
         MediaPicker.attach(input, { multiple: false, field: 'image_media_path',
             onSelect: function (files) { /* files = [{path, url, name}] */ } });
       or MediaPicker.open({ multiple: true, onSelect: ... });
--}}
@once
<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-labelledby="mediaPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="height: 84vh;">
            <div class="modal-header py-2 bg-white">
                <h6 class="modal-title mb-0" id="mediaPickerModalLabel">
                    <i class="bi bi-images me-2"></i>Media Library
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="overflow: hidden;">
                <iframe id="mediaPickerFrame" src="about:blank" title="Media picker"
                        style="width: 100%; height: 100%; border: 0; display: block;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
window.MediaPicker = (function () {
    'use strict';

    var PICKER_URL = @json(route('admin.media.picker'));
    var modalEl = null;
    var modal = null;
    var frame = null;
    var current = null;

    function ensure() {
        if (modal) return true;
        modalEl = document.getElementById('mediaPickerModal');
        if (!modalEl || typeof bootstrap === 'undefined') return false;
        frame = document.getElementById('mediaPickerFrame');
        modal = new bootstrap.Modal(modalEl);
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (frame) frame.src = 'about:blank';
            current = null;
        });
        window.addEventListener('message', onMessage);
        return true;
    }

    function onMessage(e) {
        if (e.origin !== window.location.origin) return;
        var d = e.data;
        if (!d || d.source !== 'hil-media-picker') return;

        if (d.action === 'select') {
            var cb = current && current.onSelect;
            var files = d.files || [];
            current = null;
            if (modal) modal.hide();
            if (frame) frame.src = 'about:blank';
            if (typeof cb === 'function') cb(files, d);
        } else if (d.action === 'cancel') {
            current = null;
            if (modal) modal.hide();
        }
    }

    function open(opts) {
        if (!ensure()) {
            console.error('MediaPicker: modal markup or Bootstrap missing');
            return;
        }
        opts = opts || {};
        var params = new URLSearchParams();
        params.set('multiple', opts.multiple ? 1 : 0);
        params.set('type', opts.type || 'images');
        params.set('field', opts.field || '');
        if (opts.search) params.set('search', opts.search);

        current = opts;
        frame.src = PICKER_URL + '?' + params.toString();
        modal.show();
    }

    /** Intercept a native file input so clicking it opens the library instead. */
    function attach(input, opts) {
        if (!input) return;
        input.dataset.mediaPickerWired = '1';   // autoWire must leave this one alone
        input.addEventListener('click', function (e) {
            e.preventDefault();
            open(opts);
        });
        // keyboard users: stop the OS dialog on Enter/Space too
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                open(opts);
            }
        });
    }

    /* ------------------------------------------------------------------ *
     * Automatic wiring: the picked library file is fetched and attached
     * to the file input as a File object, so every existing controller
     * keeps working unchanged (same upload, same processing, same column).
     * ------------------------------------------------------------------ */

    function isLibraryImageInput(input) {
        if (!input || input.type !== 'file') return false;
        if (input.dataset.mediaSkip === '1') return false;
        var accept = (input.getAttribute('accept') || '').toLowerCase();
        return input.hasAttribute('data-media-field') || accept.indexOf('image') !== -1;
    }

    function fetchAsFile(file, expectImage) {
        return fetch(file.url, { credentials: 'same-origin' })
            .then(function (res) {
                if (!res.ok) throw new Error((file.name || file.url) + ' could not be read (HTTP ' + res.status + ')');
                return res.blob();
            })
            .then(function (blob) {
                if (!blob.size) {
                    throw new Error((file.name || file.url) + ' is empty on disk');
                }
                if (expectImage && blob.type && blob.type.indexOf('image/') !== 0) {
                    throw new Error((file.name || file.url) + ' is not an image (' + blob.type + ')');
                }
                var name = file.name || (file.url || '').split('/').pop() || 'media-file';
                return new File([blob], name, { type: blob.type || file.type || 'application/octet-stream' });
            });
    }

    function attachFilesToInput(input, files) {
        var dt = new DataTransfer();
        var expectImage = isLibraryImageInput(input);

        if (input.multiple) {
            Array.prototype.forEach.call(input.files || [], function (f) { dt.items.add(f); });
        }

        return Promise.all(files.map(function (f) { return fetchAsFile(f, expectImage); })).then(function (made) {
            made.forEach(function (f) { dt.items.add(f); });
            try {
                input.files = dt.files;
            } catch (err) {
                console.error('MediaPicker: cannot attach files to input', err);
                return 0;
            }
            input.dispatchEvent(new Event('change', { bubbles: true }));
            return input.files.length;
        });
    }

    function wire(input) {
        if (!isLibraryImageInput(input) || input.dataset.mediaPickerWired === '1') return;
        input.dataset.mediaPickerWired = '1';

        input.addEventListener('click', function (e) {
            e.preventDefault();
            open({
                multiple: !!input.multiple,
                field: input.name || '',
                onSelect: function (files) {
                    if (!files || !files.length) return;
                    attachFilesToInput(input, files).catch(function (err) {
                        console.error('MediaPicker:', err);
                        alert('Could not load that file from the media library.');
                    });
                }
            });
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                input.click();
            }
        });
    }

    function autoWire(root) {
        var scope = root && root.querySelectorAll ? root : document;
        Array.prototype.forEach.call(scope.querySelectorAll('input[type="file"]'), wire);
    }

    document.addEventListener('DOMContentLoaded', function () {
        // page-level scripts that call attach() register their listener first, so they are
        // already marked by the time this runs
        autoWire(document);
    });

    return { open: open, attach: attach, autoWire: autoWire };
})();
</script>
@endonce
