<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Media library.
 *
 * There is no table behind it: the library is a scan of the `public` disk. Three things this class
 * is careful about, each of which was broken in the 2026-10-25 audit:
 *
 * 1. **Cost.** A scan stats every file (size + modified) and reads every image header; with 5,140
 *    files that was ~1.8 s per page view. The metadata list is cached for a minute and dropped the
 *    moment this controller writes a file.
 * 2. **Format.** The owner asked to see the image format everywhere. `getimagesize()` already
 *    reports the real mime; it is kept as `mime` + `format` (and shown on the card, in the modal and
 *    on the picker tile) instead of being thrown away in favour of the extension.
 * 3. **Safety.** Uploads are classified by detected mime (not extension) and an extension that
 *    disagrees with the content is refused; SVG is sanitised; a delete refuses while a record still
 *    points at the file; a bad path answers 404 instead of throwing.
 */
class MediaController extends Controller
{
    // originals/ holds the untouched masters — they are storage, not media to be browsed or deleted.
    private const EXCLUDED_TOP_FOLDERS = ['cache', 'tmp', 'logs', 'originals'];

    private const IMAGE_MIMES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/avif',
    ];

    /** mime => extensions that may legitimately carry it. A mismatch is refused. */
    private const ALLOWED = [
        'image/jpeg' => ['jpg', 'jpeg', 'jpe'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'image/bmp' => ['bmp'],
        'image/avif' => ['avif'],
        'image/svg+xml' => ['svg'],
        'video/mp4' => ['mp4'],
        'video/webm' => ['webm'],
        'audio/mpeg' => ['mp3'],
        'audio/wav' => ['wav', 'wave'],
        'audio/ogg' => ['ogg', 'oga'],
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'text/plain' => ['txt', 'csv', 'md'],
    ];

    private const MAX_BYTES = 10 * 1024 * 1024;

    private const CACHE_KEY = 'media.library.v1';

    private const CACHE_TTL = 60;

    public function index(Request $request)
    {
        $files = $this->mediaFiles($request, 'all');
        $perPage = $this->perPage($request, 25);
        $total = count($files);
        $page = $this->page($request, $total, $perPage);

        $items = array_slice($files, ($page - 1) * $perPage, $perPage);

        // Auto-load on scroll asks for one page at a time and gets the cards, not a whole document.
        // The grid comes from the same partial the first render uses, so appended cards cannot drift
        // from the printed ones (the trap that made an AJAX list here swap to the wrong row partial).
        if ($request->boolean('fragment')) {
            return response()->json([
                'html' => view('admin.media.partials.items', ['files' => $items])->render(),
                'page' => $page,
                'per_page' => $perPage,
                'loaded' => min($total, $page * $perPage),
                'total' => $total,
                'has_more' => $page * $perPage < $total,
            ]);
        }

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => route('admin.media.index', [], false)]
        );

        return view('admin.media.index', compact('paginator'));
    }

    /**
     * Media picker — compact, iframe-friendly media browser used by admin forms.
     */
    public function picker(Request $request)
    {
        $files = $this->mediaFiles($request, 'images');
        $perPage = $this->perPage($request, 48, 12);
        $page = $this->page($request, count($files), $perPage);

        $paginator = new LengthAwarePaginator(
            array_slice($files, ($page - 1) * $perPage, $perPage),
            count($files),
            $perPage,
            $page,
            ['path' => $request->url()]
        );
        $paginator->appends($request->query());

        return view('admin.media.picker', [
            'paginator' => $paginator,
            'multiple' => (bool) $request->get('multiple', 0),
            'field' => (string) $request->get('field', ''),
            'type' => (string) $request->get('type', 'images'),
            'search' => (string) $request->get('search', ''),
        ]);
    }

    /**
     * File details as JSON — now including the format.
     */
    public function show(Request $request): JsonResponse
    {
        $path = $this->cleanPath((string) $request->get('path'));

        if ($path === null || ! $this->exists($path)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }

        return response()->json(['success' => true, 'file' => $this->fileMeta($path)]);
    }

    /**
     * Upload one or more files.
     */
    public function upload(Request $request): JsonResponse
    {
        $files = $this->normaliseUploads($request->file('files'), $request->file('file'));

        if (empty($files)) {
            return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
        }

        $uploaded = [];
        $errors = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $original = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            $mime = (string) $file->getMimeType();

            if ($file->getSize() > self::MAX_BYTES) {
                $errors[] = "{$original}: larger than the 10 MB limit";
                continue;
            }

            if (! isset(self::ALLOWED[$mime])) {
                $errors[] = "{$original}: file type not allowed ({$mime})";
                continue;
            }

            // The extension has to agree with the detected content. Without this check a text file
            // named ".jpg" was stored, listed as an image and left a broken card behind.
            if ($extension !== '' && ! in_array($extension, self::ALLOWED[$mime], true)) {
                $errors[] = "{$original}: .{$extension} does not match its content ({$mime})";
                continue;
            }

            $stored = $mime === 'image/svg+xml'
                ? $this->storeSvg($file, $original, $errors)
                : (in_array($mime, self::IMAGE_MIMES, true)
                    ? $this->storeImage($file, $original, $errors)
                    : $this->storeAs($file, $original, $errors));

            if ($stored) {
                $uploaded[] = $stored;
            }
        }

        // A 400 px photo uploads happily and then looks soft on the product page. Warn, never block:
        // the shop is run by an operator, who should not have to know image specs to notice.
        $warnings = [];
        foreach ($uploaded as $item) {
            $width = (int) ($item['width'] ?? 0);
            if ($width > 0 && $width < 1000) {
                $warnings[] = ($item['name'] ?? $item['path'] ?? 'image') . ": {$width} px wide — soft above ~" . (int) round($width / 2) . ' px on screen. Aim for 1000 px or more.';
            }
        }

        $this->forgetLibrary();

        if (empty($uploaded)) {
            return response()->json([
                'success' => false,
                'message' => implode(', ', $errors) ?: 'Nothing was uploaded',
                'errors' => $errors,
            ], 422);
        }

        ActivityLog::adminLog('Media uploaded', null, auth()->user(), [
            'count' => count($uploaded),
            'files' => array_map(fn ($f) => $f['path'], $uploaded),
            'rejected' => $errors,
        ]);

        return response()->json([
            'success' => true,
            'message' => count($uploaded) . ' file(s) uploaded successfully'
                . ($errors ? '; ' . count($errors) . ' rejected' : '')
                . ($warnings ? ' — ' . count($warnings) . ' small image(s) noted below' : ''),
            'uploaded' => $uploaded,
            'errors' => $errors,
            'warnings' => $warnings,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $raw = (string) $request->get('path');
        $path = $this->cleanPath($raw);

        if ($path === null) {
            return response()->json(['success' => false, 'message' => 'File path is required'], 400);
        }

        if (! $this->exists($path)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }

        $usedBy = $this->referencesTo($path);
        if ($usedBy) {
            return response()->json([
                'success' => false,
                'message' => 'Still in use by ' . implode(', ', $usedBy) . '. Replace it there first.',
                'used_by' => $usedBy,
            ], 409);
        }

        $meta = $this->fileMeta($path);
        // One call removes the image and everything generated from it (thumb, 800 px derivative).
        ImageHelper::deleteImage($path, $meta['thumb_url'] !== $meta['url'] ? $meta['thumb_url'] : null);
        $this->forgetLibrary();

        ActivityLog::adminLog('Media deleted', null, auth()->user(), [
            'path' => $path,
            'format' => $meta['format'],
            'bytes' => $meta['size'],
        ]);

        return response()->json(['success' => true, 'message' => 'File deleted successfully']);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $paths = $this->normalisePaths($request->input('paths'));

        if (empty($paths)) {
            return response()->json(['success' => false, 'message' => 'No files selected for deletion'], 400);
        }

        $deleted = [];
        $blocked = [];
        $missing = [];

        foreach ($paths as $raw) {
            $path = $this->cleanPath($raw);

            if ($path === null || ! $this->exists($path)) {
                $missing[] = basename($raw);
                continue;
            }

            $usedBy = $this->referencesTo($path);
            if ($usedBy) {
                $blocked[] = basename($path) . ' (' . implode(', ', $usedBy) . ')';
                continue;
            }

            ImageHelper::deleteImage($path);
            $deleted[] = $path;
        }

        if ($deleted) {
            $this->forgetLibrary();
            ActivityLog::adminLog('Media bulk deleted', null, auth()->user(), [
                'deleted' => $deleted,
                'blocked' => $blocked,
                'missing' => $missing,
            ]);
        }

        $message = count($deleted) . ' file(s) deleted';
        if ($blocked) {
            $message .= '; ' . count($blocked) . ' still in use: ' . implode(', ', $blocked);
        }
        if ($missing) {
            $message .= '; ' . count($missing) . ' not found';
        }

        return response()->json([
            'success' => $deleted !== [],
            'message' => $message,
            'deleted' => count($deleted),
            'blocked' => $blocked,
            'missing' => $missing,
        ], $deleted === [] ? 409 : 200);
    }

    // ------------------------------------------------------------------ scanning

    /**
     * The library metadata (cached), filtered by type and search.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function mediaFiles(Request $request, string $defaultType = 'all'): array
    {
        $files = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $out = [];

            foreach (Storage::disk('public')->allFiles() as $path) {
                if (in_array(explode('/', $path)[0], self::EXCLUDED_TOP_FOLDERS, true)) {
                    continue;
                }

                // Derived files belong to their parent image, they are not media of their own.
                // Listing them made every upload appear twice and let a thumbnail be deleted alone,
                // leaving the picture that uses it pointing at nothing.
                if (preg_match('/_thumb\.webp$|-400\.webp$|-800\.webp$|-400\.avif$|-800\.avif$/i', $path)) {
                    continue;
                }

                try {
                    if (Storage::disk('public')->size($path) <= 0) {
                        continue; // 0-byte or truncated: cannot be used, would fail image validation
                    }
                } catch (\Throwable $e) {
                    continue;
                }

                $out[] = $this->fileMeta($path);
            }

            return $out;
        });

        $type = (string) $request->get('type', $defaultType);
        if ($type !== '' && $type !== 'all') {
            $files = array_filter($files, fn (array $f) => match ($type) {
                'images' => $f['is_image'],
                'videos' => in_array($f['mime'], ['video/mp4', 'video/webm'], true),
                'documents' => in_array($f['mime'], [
                    'application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/plain',
                ], true),
                default => true,
            });
        }

        $search = mb_strtolower(trim((string) $request->get('search', '')));
        if ($search !== '') {
            $files = array_filter($files, fn (array $f) => str_contains(mb_strtolower($f['name']), $search)
                || str_contains(mb_strtolower($f['path']), $search));
        }

        $files = array_values($files);
        usort($files, fn ($a, $b) => $b['modified'] <=> $a['modified']);

        // "Unused only": files no record anywhere mentions. Same detector the junk audit used, so the
        // answer to "I don't want any junk image" becomes an ongoing tool instead of a one-off purge.
        if ($request->boolean('unused')) {
            $referenced = $this->referencedNames();
            $files = array_values(array_filter(
                $files,
                fn (array $f) => ! isset($referenced[basename($f['path'] ?? '')])
            ));
        }

        return $files;
    }

    /**
     * Every filename the database mentions, as a lookup set. One pass over the text/JSON columns,
     * skipping logs and sessions (their contents are not references), then cached — the scan takes a
     * second or two and an unused list that costs that on every page view would be unusable.
     */
    protected function referencedNames(): array
    {
        return Cache::remember('media.referenced.names', 300, function () {
            $skip = ['activity_logs', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches',
                'failed_jobs', 'migrations', 'password_reset_tokens', 'personal_access_tokens',
                'notifications', 'login_codes', 'otp_verifications', 'media', 'telescope_entries'];

            $pattern = '~[A-Za-z0-9._\-]+\.(?:webp|jpg|jpeg|png|gif|svg|avif|bmp|mp4|webm|pdf|docx?|xlsx?)~i';
            $names = [];

            try {
                $columns = DB::select("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND DATA_TYPE IN ('varchar','text','mediumtext','longtext','json')");
            } catch (\Throwable $e) {
                return $names;
            }

            foreach ($columns as $column) {
                $table = $column->TABLE_NAME;
                $col = $column->COLUMN_NAME;
                if (in_array($table, $skip, true)) {
                    continue;
                }

                try {
                    // chunk() refuses to run without an explicit order; without it the whole scan
                    // threw and was swallowed by the catch below, which made every file look unused.
                    DB::table($table)->select($col)->whereNotNull($col)->orderBy($col)
                        ->chunk(500, function ($rows) use ($col, $pattern, &$names) {
                            foreach ($rows as $row) {
                                $value = $row->$col;
                                if (! is_string($value) || $value === '') {
                                    continue;
                                }
                                if (preg_match_all($pattern, $value, $m)) {
                                    foreach ($m[0] as $name) {
                                        $names[basename($name)] = true;
                                    }
                                }
                            }
                        });
                } catch (\Throwable $e) {
                    // A table that cannot be scanned would silently mark its images as unused, and the
                    // only safe reading of that is to log it, not to pretend the file is disposable.
                    \Log::warning('media unused-scan skipped ' . $table . '.' . $col . ': ' . $e->getMessage());
                    continue;
                }
            }

            // Files the code itself depends on are referenced whatever the data says.
            $names['placeholder.webp'] = true;

            return $names;
        });
    }

    /**
     * One file's metadata, with the real format.
     *
     * @return array<string, mixed>
     */
    protected function fileMeta(string $path): array
    {
        $disk = Storage::disk('public');
        $full = storage_path('app/public/' . $path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $mime = null;
        $width = null;
        $height = null;

        if (is_file($full)) {
            $info = @getimagesize($full);
            if ($info) {
                // For images this is the authoritative format, whatever the file is called.
                $mime = $info['mime'] ?? null;
                $width = $info[0] ?? null;
                $height = $info[1] ?? null;
            } else {
                $mime = @mime_content_type($full) ?: null;
            }
        }

        $mime = $mime ?: 'application/octet-stream';
        $size = (int) @$disk->size($path);
        $modified = (int) @$disk->lastModified($path);

        return [
            'path' => $path,
            'name' => basename($path),
            'url' => $disk->url($path),
            'size' => $size,
            'size_formatted' => $this->formatFileSize($size),
            'modified' => $modified,
            'modified_formatted' => date('Y-m-d H:i:s', $modified),
            'extension' => $extension,
            'mime' => $mime,
            'format' => $this->formatLabel($mime, $extension),
            'is_image' => str_starts_with($mime, 'image/'),
            'width' => $width,
            'height' => $height,
            'dimensions' => ($width && $height) ? $width . ' x ' . $height . ' px' : null,
            // Small preview for the grid (300 px derivative when it exists, else the file itself).
            'thumb_url' => $this->thumbUrl($path, $disk->url($path)),
        ];
    }

    /** The 300 px derivative of an image, as a URL — or the file's own URL when there is none. */
    protected function thumbUrl(string $path, string $fallback): string
    {
        $widths = ImageHelper::availableWidths($path);
        foreach ($widths as $candidate => $width) {
            if ($width <= 300) {
                return Storage::disk('public')->url($candidate);
            }
        }

        return $fallback;
    }

    /** "image/webp" → "WEBP"; anything unsniffable falls back to its extension. */
    protected function formatLabel(?string $mime, string $extension): string
    {
        $map = [
            'image/jpeg' => 'JPEG', 'image/png' => 'PNG', 'image/gif' => 'GIF', 'image/webp' => 'WEBP',
            'image/bmp' => 'BMP', 'image/avif' => 'AVIF', 'image/svg+xml' => 'SVG',
            'application/pdf' => 'PDF', 'video/mp4' => 'MP4', 'video/webm' => 'WEBM',
            'audio/mpeg' => 'MP3', 'audio/wav' => 'WAV', 'audio/ogg' => 'OGG', 'text/plain' => 'TXT',
            'application/msword' => 'DOC',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
            'application/vnd.ms-excel' => 'XLS',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'XLSX',
        ];

        return $map[$mime] ?? ($extension !== '' ? strtoupper($extension) : 'FILE');
    }

    // ------------------------------------------------------------------ writing

    /**
     * @return array<string, mixed>|null
     */
    private function storeImage(UploadedFile $file, string $original, array &$errors): ?array
    {
        if (! ImageHelper::isValidImage($file)) {
            $errors[] = "{$original}: not a readable image";

            return null;
        }

        $result = ImageHelper::processImage($file, 'uploads', 1920, 300, 85);

        // processImage() returns a public URL ("/storage/uploads/x.webp"), not a disk path.
        $path = ltrim((string) ($result['path'] ?? ''), '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if ($path === '' || ! $this->exists($path)) {
            $errors[] = "{$original}: could not be stored";

            return null;
        }

        return $this->uploadPayload($path);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function storeAs(UploadedFile $file, string $original, array &$errors): ?array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $base = Str::slug(pathinfo($original, PATHINFO_FILENAME)) ?: 'file';
        $name = $base . ($extension !== '' ? '.' . $extension : '');
        $counter = 1;

        while (Storage::disk('public')->exists('uploads/' . $name)) {
            $name = $base . '-' . $counter++ . ($extension !== '' ? '.' . $extension : '');
        }

        $path = $file->storeAs('uploads', $name, 'public');

        if (! $path || ! $this->exists($path)) {
            $errors[] = "{$original}: could not be stored";

            return null;
        }

        return $this->uploadPayload($path);
    }

    /**
     * SVG is markup, not pixels: it can carry <script> and event handlers, and it is served from the
     * shop's own origin. Sanitise before writing; refuse what cannot be made safe.
     *
     * @return array<string, mixed>|null
     */
    private function storeSvg(UploadedFile $file, string $original, array &$errors): ?array
    {
        $clean = $this->sanitiseSvg((string) file_get_contents($file->getRealPath()));

        if ($clean === null) {
            $errors[] = "{$original}: SVG contains active content, so it was refused. Upload a PNG instead.";

            return null;
        }

        $base = Str::slug(pathinfo($original, PATHINFO_FILENAME)) ?: 'image';
        $name = $base . '.svg';
        $counter = 1;
        while (Storage::disk('public')->exists('uploads/' . $name)) {
            $name = $base . '-' . $counter++ . '.svg';
        }

        Storage::disk('public')->put('uploads/' . $name, $clean);

        return $this->uploadPayload('uploads/' . $name);
    }

    private function sanitiseSvg(string $svg): ?string
    {
        if (! class_exists(\DOMDocument::class)) {
            return null; // no DOM: refuse markup we cannot inspect
        }

        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $doc->loadXML($svg, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return null;
        }

        $xpath = new \DOMXPath($doc);

        foreach (['script', 'foreignobject', 'iframe', 'embed', 'object', 'audio', 'video', 'handler', 'set', 'animate'] as $tag) {
            foreach (iterator_to_array($xpath->query('//*[local-name()="' . $tag . '"]') ?: []) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        foreach (iterator_to_array($xpath->query('//@*') ?: []) as $attr) {
            $name = strtolower($attr->nodeName);
            $value = strtolower(trim((string) $attr->nodeValue));

            $handler = str_starts_with($name, 'on');
            $script = str_starts_with($value, 'javascript:') || str_starts_with($value, 'data:text/html');
            $external = in_array($name, ['href', 'xlink:href', 'src'], true)
                && (str_starts_with($value, 'http') || str_starts_with($value, '//') || str_starts_with($value, 'data:'));

            if ($handler || $script || $external) {
                $attr->ownerElement?->removeAttributeNode($attr);
            }
        }

        $out = (string) $doc->saveXML();

        if (preg_match('/<script|javascript:|onload\s*=|onerror\s*=/i', $out)) {
            return null;
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function uploadPayload(string $path): array
    {
        $meta = $this->fileMeta($path);

        return [
            'name' => $meta['name'],
            'path' => $meta['path'],
            'url' => $meta['url'],
            'size' => $meta['size'],
            'size_formatted' => $meta['size_formatted'],
            'extension' => $meta['extension'],
            'mime' => $meta['mime'],
            'format' => $meta['format'],
            'width' => $meta['width'],
            'height' => $meta['height'],
            'dimensions' => $meta['dimensions'],
        ];
    }

    // ------------------------------------------------------------------ references

    /**
     * Which records still point at this file.
     *
     * The library used to delete whatever it was told to, including a product's featured image,
     * which left a broken <img> on the storefront with no warning.
     *
     * @return array<int, string>
     */
    protected function referencesTo(string $path): array
    {
        $needles = array_values(array_unique(['%' . basename($path), '%' . ltrim($path, '/')]));
        $hits = [];

        foreach ($this->referenceColumns() as $column) {
            foreach ($needles as $needle) {
                try {
                    $count = DB::table($column['TABLE_NAME'])->where($column['COLUMN_NAME'], 'like', $needle)->count();
                } catch (\Throwable $e) {
                    continue;
                }

                if ($count > 0) {
                    $hits[] = $column['TABLE_NAME'] . '.' . $column['COLUMN_NAME'] . ' (' . $count . ')';
                    break;
                }
            }
        }

        foreach ($needles as $needle) {
            try {
                $count = DB::table('settings')->where('value', 'like', $needle)->count();
            } catch (\Throwable $e) {
                $count = 0;
            }

            if ($count > 0) {
                $hits[] = 'settings.value (' . $count . ')';
                break;
            }
        }

        return array_values(array_unique($hits));
    }

    /**
     * Text columns that look like they hold a path, discovered once and cached.
     *
     * @return array<int, array{TABLE_NAME: string, COLUMN_NAME: string}>
     */
    private function referenceColumns(): array
    {
        return Cache::rememberForever('media.reference_columns.v1', function () {
            $skip = ['activity_logs', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches',
                     'failed_jobs', 'migrations', 'password_reset_tokens', 'personal_access_tokens',
                     'notifications', 'settings'];

            return DB::table('information_schema.COLUMNS')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->whereIn('DATA_TYPE', ['varchar', 'text', 'mediumtext', 'longtext'])
                ->whereNotIn('TABLE_NAME', $skip)
                ->where(function ($q) {
                    $q->where('COLUMN_NAME', 'like', '%image%')
                        ->orWhere('COLUMN_NAME', 'like', '%logo%')
                        ->orWhere('COLUMN_NAME', 'like', '%photo%')
                        ->orWhere('COLUMN_NAME', 'like', '%thumbnail%')
                        ->orWhere('COLUMN_NAME', 'like', '%banner%')
                        ->orWhere('COLUMN_NAME', 'like', '%favicon%');
                })
                ->get(['TABLE_NAME', 'COLUMN_NAME'])
                ->map(fn ($c) => (array) $c)
                ->all();
        });
    }

    // ------------------------------------------------------------------ helpers

    /**
     * @return array<int, UploadedFile>
     */
    private function normaliseUploads($files, $single): array
    {
        if ($files instanceof UploadedFile) {
            return [$files];
        }

        if (is_array($files)) {
            return array_values(array_filter($files, fn ($f) => $f instanceof UploadedFile));
        }

        if ($single instanceof UploadedFile) {
            return [$single];
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    private function normalisePaths($raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : explode(',', $raw);
        }

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter(array_map(fn ($p) => is_string($p) ? trim($p) : '', $raw)));
    }

    /** A path inside the public disk, or null. No traversal, no absolute paths. */
    private function cleanPath(string $path): ?string
    {
        $path = trim(str_replace('\\', '/', $path));

        if ($path === '' || str_starts_with($path, '/') || str_contains($path, '..')) {
            return null;
        }

        return $path;
    }

    private function exists(string $path): bool
    {
        try {
            return Storage::disk('public')->exists($path);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function page(Request $request, int $total, int $perPage): int
    {
        $page = (int) $request->get('page', 1);
        $last = max(1, (int) ceil($total / max(1, $perPage)));

        return max(1, min($page, $last));
    }

    private function perPage(Request $request, int $default, int $min = 1): int
    {
        $raw = $request->get('per_page');

        if (! is_numeric($raw)) {
            return $default;
        }

        $value = (int) $raw;

        // 0, a negative number or a nonsense value falls back to the screen's own page size;
        // anything above the ceiling is capped. Before this, "?per_page=0" was a 500 and
        // "?per_page=-5" answered with the whole 10 MB library.
        if ($value < max(1, $min)) {
            return $default;
        }

        return min($value, 100);
    }

    private function forgetLibrary(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('media.referenced.names');
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = min((int) floor(($bytes ? log($bytes) : 0) / log(1024)), count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
