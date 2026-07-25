<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageHelper
{
    /**
     * Process and store uploaded image
     * - Converts to WebP
     * - Resizes to max width
     * - Generates thumbnail
     * 
     * @param $file UploadedFile
     * @param string $directory Storage directory
     * @param int $maxWidth Max width for main image (0 = no resize)
     * @param int $thumbWidth Thumbnail width (0 = no thumbnail)
     * @param int $quality WebP quality (0-100)
     * @return array ['path' => main image path, 'thumbnail' => thumbnail path]
     */
    public static function processImage($file, $directory = 'products', $maxWidth = 1920, $thumbWidth = 300, $quality = 85)
    {
        // Get original extension
        $originalExtension = strtolower($file->getClientOriginalExtension());
        
        // Readable, collision-safe stem: "pressure-cooker-a1b2c3". The old scheme
        // (uniqid_time + ".jpg.webp") could not be read in the library, in search or in a file
        // manager, and it hid the original name of the picture completely.
        $stem = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
        $stem = rtrim(Str::limit($stem, 48, ''), '-') ?: 'image';
        $filename = $stem . '-' . Str::lower(Str::random(6));

        // Keep the master. WebP quality is decided at upload and the upload itself is otherwise
        // thrown away, so no future format or larger rendition could ever be generated again.
        // Only for pictures worth re-rendering; documents and tiny images are not stored twice.
        try {
            $sourcePath = $file->getRealPath();
            $sourceDim = @getimagesize($sourcePath);
            if ($sourceDim && max($sourceDim[0], $sourceDim[1]) > 1200) {
                Storage::disk('public')->putFileAs(
                    'originals',
                    $file,
                    $filename . '.' . $originalExtension
                );
            }
        } catch (\Throwable $e) {
            // A failed master copy must never fail the upload the admin is doing.
        }
        
        // Create image resource based on extension
        $imageResource = self::createImageFromFile($file->getRealPath(), $originalExtension);
        
        if (!$imageResource) {
            throw new \Exception('Failed to create image resource from file');
        }

        // A phone writes the rotation into EXIF and leaves the pixels sideways. GD does not read
        // EXIF, so without this every portrait photo from a camera phone is stored on its side.
        $imageResource = self::applyExifOrientation($imageResource, $file->getRealPath(), $originalExtension);

        // WebP cannot encode palette (indexed-colour) images — normalise to truecolour first.
        // Without this, GIFs and indexed PNGs fail with "imagewebp(): Palette image not supported by webp".
        if (!imageistruecolor($imageResource)) {
            imagepalettetotruecolor($imageResource);
        }

        // Get original dimensions
        $originalWidth = imagesx($imageResource);
        $originalHeight = imagesy($imageResource);
        
        // Resize main image if needed
        $mainImage = $imageResource;
        $mainWidth = $originalWidth;
        $mainHeight = $originalHeight;
        
        if ($maxWidth > 0 && $originalWidth > $maxWidth) {
            $mainHeight = (int) round(($maxWidth / $originalWidth) * $originalHeight);
            $mainWidth = $maxWidth;
            
            $mainImage = imagecreatetruecolor($mainWidth, $mainHeight);
            
            // Preserve transparency for PNG/GIF
            if ($originalExtension === 'png') {
                imagealphablending($mainImage, false);
                imagesavealpha($mainImage, true);
                $transparent = imagecolorallocatealpha($mainImage, 255, 255, 255, 127);
                imagefilledrectangle($mainImage, 0, 0, $mainWidth, $mainHeight, $transparent);
            }
            
            imagecopyresampled(
                $mainImage, $imageResource,
                0, 0, 0, 0,
                $mainWidth, $mainHeight,
                $originalWidth, $originalHeight
            );
        }
        
        // Save main image as WebP
        $mainFilename = $filename . '.webp';
        $mainPath = $directory . '/' . $mainFilename;
        $fullMainPath = Storage::disk('public')->path($mainPath);
        
        // Ensure directory exists
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        
        imagewebp($mainImage, $fullMainPath, $quality);
        
        $result = [
            'path' => Storage::url($mainPath),
            'filename' => $mainFilename,
            'original_width' => $mainWidth,
            'original_height' => $mainHeight,
        ];
        
        // Free the resized copy only; the original is still needed for the thumbnail below.
        if ($mainImage !== $imageResource) {
            imagedestroy($mainImage);
        }
        
        // Derivative widths for listing cards, tablets and retina screens. 1920 px is the
        // detail-page picture; a phone card is ~180-260 CSS px (360-520 device px at DPR 2), so a
        // single 800 px step was too coarse — the browser fell back to the full file and nothing was
        // actually saved. Measured on the homepage at 390 CSS px / DPR 2.
        foreach ([400, 800] as $midWidth) {
            if ($mainWidth <= $midWidth) {
                continue;
            }
            $midHeight = (int) round(($midWidth / $mainWidth) * $mainHeight);
            $medium = imagecreatetruecolor($midWidth, $midHeight);
            if ($originalExtension === 'png') {
                imagealphablending($medium, false);
                imagesavealpha($medium, true);
                $t = imagecolorallocatealpha($medium, 255, 255, 255, 127);
                imagefilledrectangle($medium, 0, 0, $midWidth, $midHeight, $t);
            }
            imagecopyresampled($medium, $mainImage, 0, 0, 0, 0, $midWidth, $midHeight, $mainWidth, $mainHeight);
            $midPath = $directory . '/' . $filename . '-' . $midWidth . '.webp';
            imagewebp($medium, Storage::disk('public')->path($midPath), 82);
            // AVIF next to the WebP: ~28% smaller on this catalogue (measured), served through
            // <picture> so every browser that cannot read it still gets the WebP.
            if (function_exists('imageavif')) {
                imageavif($medium, Storage::disk('public')->path($directory . '/' . $filename . '-' . $midWidth . '.avif'), 60);
            }
            imagedestroy($medium);
            $key = $midWidth === 800 ? 'medium' : 'small';
            $result[$key] = Storage::url($midPath);
            $result[$key . '_filename'] = $filename . '-' . $midWidth . '.webp';
        }

        // Generate thumbnail if requested
        if ($thumbWidth > 0) {
            // Calculate thumbnail dimensions
            $aspectRatio = $originalHeight / $originalWidth;
            $thumbHeight = (int) round($thumbWidth * $aspectRatio);
            
            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
            
            // Preserve transparency
            if ($originalExtension === 'png') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
                imagefilledrectangle($thumbnail, 0, 0, $thumbWidth, $thumbHeight, $transparent);
            }
            
            imagecopyresampled(
                $thumbnail, $imageResource,
                0, 0, 0, 0,
                $thumbWidth, $thumbHeight,
                $originalWidth, $originalHeight
            );
            
            // Save thumbnail as WebP
            $thumbFilename = $filename . '_thumb.webp';
            $thumbPath = $directory . '/' . $thumbFilename;
            $fullThumbPath = Storage::disk('public')->path($thumbPath);
            
            imagewebp($thumbnail, $fullThumbPath, 80);
            
            $result['thumbnail'] = Storage::url($thumbPath);
            $result['thumbnail_filename'] = $thumbFilename;
            
            // Free thumbnail memory
            imagedestroy($thumbnail);
        }
        
        // Free original resource if still in memory (GD returns GdImage objects on PHP 8+)
        if (isset($imageResource) && ($imageResource instanceof \GdImage || is_resource($imageResource))) {
            imagedestroy($imageResource);
        }
        
        $result['width'] = $mainWidth;
        $result['height'] = $mainHeight;

        return $result;
    }
    
    /**
     * Create GD image resource from file path
     * 
     * @param string $filePath
     * @param string $extension
     * @return resource|\GdImage|false
     */
    private static function createImageFromFile($filePath, $extension)
    {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($filePath);
            case 'png':
                $img = imagecreatefrompng($filePath);
                // Fix transparency issue
                if ($img) {
                    imagealphablending($img, true);
                }
                return $img;
            case 'gif':
                return imagecreatefromgif($filePath);
            case 'webp':
                return imagecreatefromwebp($filePath);
            case 'bmp':
                return imagecreatefrombmp($filePath);
            case 'tiff':
            case 'tif':
                return imagecreatefromtiff($filePath);
            default:
                return false;
        }
    }
    
    /**
     * Process multiple gallery images
     * 
     * @param array $files Array of uploaded files
     * @param string $directory Storage directory
     * @param int $maxWidth Max width
     * @param int $quality WebP quality
     * @return array Array of processed image paths
     */
    public static function processGalleryImages($files, $directory = 'products/gallery', $maxWidth = 1200, $quality = 85)
    {
        $images = [];
        
        foreach ($files as $file) {
            $result = self::processImage($file, $directory, $maxWidth, 0, $quality);
            $images[] = $result['path'];
        }
        
        return $images;
    }
    
    /**
     * Delete image and its thumbnail
     * 
     * @param string $imagePath Full path or URL to image
     * @param string|null $thumbnailPath Thumbnail path
     */
    public static function deleteImage($imagePath, $thumbnailPath = null)
    {
        $disk = Storage::disk('public');
        // Remove /storage/ prefix if present
        $path = ltrim(str_replace('/storage/', '', (string) $imagePath), '/');

        if ($path !== '' && $disk->exists($path)) {
            $disk->delete($path);
        }

        // Delete thumbnail if provided
        if ($thumbnailPath) {
            $thumbPath = ltrim(str_replace('/storage/', '', (string) $thumbnailPath), '/');
            if ($thumbPath !== '' && $disk->exists($thumbPath)) {
                $disk->delete($thumbPath);
            }
        }

        // Every file an image generates belongs to it: the 300 px thumb and the 800 px middle
        // derivative. Deleting only the path the caller passed is how a library ends up half-deleted
        // (main gone, thumbnail left behind) — 3,104 orphaned files were removed for that reason.
        if ($path === '') {
            return;
        }

        $dir = dirname($path);
        $dir = $dir === '.' ? '' : $dir . '/';
        $stem = preg_replace('/\.(webp|jpe?g|png|gif|bmp|avif)$/i', '', basename($path));
        $stem = preg_replace('/(_thumb|-400|-800)$/i', '', $stem);

        foreach ([$dir . $stem . '_thumb.webp', $dir . $stem . '-400.webp', $dir . $stem . '-800.webp'] as $sibling) {
            if ($disk->exists($sibling)) {
                $disk->delete($sibling);
            }
        }
    }
    
    /**
     * Check if file is a valid image
     * 
     * @param $file
     * @return bool
     */
    public static function isValidImage($file)
    {
        $validExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tiff', 'tif'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        return in_array($extension, $validExtensions) && $file->isValid();
    }
    
    /**
     * Get allowed image extensions
     * 
     * @return array
     */
    public static function allowedExtensions()
    {
        return ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tiff', 'tif'];
    }

    /**
     * Apply the EXIF Orientation tag to a decoded GD image.
     * Returns the (possibly new) resource; the caller must use the return value.
     */
    private static function applyExifOrientation($image, string $filePath, string $extension)
    {
        if (! in_array($extension, ['jpg', 'jpeg'], true) || ! function_exists('exif_read_data')) {
            return $image;
        }

        try {
            $exif = @exif_read_data($filePath);
        } catch (\Throwable $e) {
            return $image;
        }

        $orientation = (int) ($exif['Orientation'] ?? 1);
        if ($orientation <= 1 || $orientation > 8) {
            return $image;
        }

        $rotate = static function ($im, float $deg) {
            $out = imagerotate($im, $deg, 0);
            if ($out !== false) {
                imagedestroy($im);
                return $out;
            }
            return $im;
        };

        switch ($orientation) {
            case 2: // mirrored horizontally
                imageflip($image, IMG_FLIP_HORIZONTAL);
                break;
            case 3:
                $image = $rotate($image, 180);
                break;
            case 4:
                imageflip($image, IMG_FLIP_VERTICAL);
                break;
            case 5: // mirrored + rotated 270
                imageflip($image, IMG_FLIP_HORIZONTAL);
                $image = $rotate($image, 270);
                break;
            case 6: // 90 clockwise
                $image = $rotate($image, -90);
                break;
            case 7:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                $image = $rotate($image, 90);
                break;
            case 8: // 90 counter-clockwise
                $image = $rotate($image, 90);
                break;
        }

        return $image;
    }

    /**
     * Widths a stored image can be served at, as [diskPath => width].
     * Only files that actually exist are returned — the templates fall back to the plain src.
     */
    public static function availableWidths(string $storedPath): array
    {
        // Paths arrive in three shapes: '/storage/a/b.webp', 'a/b.webp' and a full URL.
        $rel = preg_replace('~^https?://[^/]+/~', '', trim($storedPath));
        $rel = preg_replace('~^/?storage/~', '', ltrim((string) $rel, '/'));
        if ($rel === '' || str_starts_with($rel, 'http')) {
            return [];
        }

        $disk = Storage::disk('public');
        $dir = dirname($rel);
        $dir = $dir === '.' ? '' : $dir . '/';
        $stem = preg_replace('/\.(webp|jpe?g|png|gif|bmp|avif)$/i', '', basename($rel));
        $stem = preg_replace('/(_thumb|-400|-800)$/i', '', $stem);

        $out = [];
        // The requested file is a candidate too: a PNG logo whose only derived size is 400 px still
        // needs to offer itself as the larger option, otherwise the helper returns '' and the browser
        // falls back to the full PNG.
        $candidates = [
            $dir . $stem . '_thumb.webp' => null,
            $dir . $stem . '-400.webp' => 400,
            $dir . $stem . '-800.webp' => 800,
            $dir . $stem . '.webp' => null,
            $rel => null,
        ];
        foreach ($candidates as $path => $known) {
            if (! $disk->exists($path)) {
                continue;
            }
            $width = $known;
            if ($width === null) {
                $dim = @getimagesize(storage_path('app/public/' . $path));
                $width = $dim[0] ?? null;
            }
            if ($width) {
                $out[$path] = (int) $width;
            }
        }

        return $out;
    }

    /**
     * A `srcset` value for a stored image, or '' when there is no usable alternative.
     * An empty string means the template renders exactly what it rendered before.
     */
    public static function srcset(string $storedPath): string
    {
        $widths = self::availableWidths($storedPath);
        if (count($widths) < 2) {
            return '';
        }

        $parts = [];
        foreach ($widths as $path => $width) {
            $parts[] = asset('storage/' . $path) . ' ' . $width . 'w';
        }

        return implode(', ', $parts);
    }


    /**
     * AVIF candidates for a stored image, or '' when it has none. Same contract as srcset():
     * an empty string means the template renders exactly what it rendered before.
     */
    public static function avifSrcset(string $storedPath): string
    {
        $rel = preg_replace('~^https?://[^/]+/~', '', trim($storedPath));
        $rel = preg_replace('~^/?storage/~', '', ltrim((string) $rel, '/'));
        if ($rel === '') {
            return '';
        }

        $disk = Storage::disk('public');
        $dir = dirname($rel);
        $dir = $dir === '.' ? '' : $dir . '/';
        $stem = preg_replace('/\.(webp|jpe?g|png|gif|bmp|avif)$/i', '', basename($rel));
        $stem = preg_replace('/(_thumb|-400|-800)$/i', '', $stem);

        $parts = [];
        foreach ([400, 800] as $width) {
            $candidate = $dir . $stem . '-' . $width . '.avif';
            if ($disk->exists($candidate)) {
                // ?v=1 because the edge cache latched onto these URLs while Apache still served
                // .avif as text/plain; a versioned URL is a new object, so it is fetched with the
                // correct type. Bump it if an AVIF is ever regenerated.
                $parts[] = asset('storage/' . $candidate) . '?v=1 ' . $width . 'w';
            }
        }

        return count($parts) >= 1 ? implode(', ', $parts) : '';
    }

}
