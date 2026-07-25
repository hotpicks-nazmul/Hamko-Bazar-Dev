<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

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
        
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.jpg';
        
        // Create image resource based on extension
        $imageResource = self::createImageFromFile($file->getRealPath(), $originalExtension);
        
        if (!$imageResource) {
            throw new \Exception('Failed to create image resource from file');
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
            
            // Free original if resized
            if ($mainImage !== $imageResource) {
                imagedestroy($imageResource);
            }
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
        
        // Free main image memory
        imagedestroy($mainImage);
        
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
        
        // Free original resource if still in memory
        if (isset($imageResource) && is_resource($imageResource)) {
            imagedestroy($imageResource);
        }
        
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
        // Remove /storage/ prefix if present
        $path = str_replace('/storage/', '', $imagePath);
        
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        
        // Delete thumbnail if provided
        if ($thumbnailPath) {
            $thumbPath = str_replace('/storage/', '', $thumbnailPath);
            if (Storage::disk('public')->exists($thumbPath)) {
                Storage::disk('public')->delete($thumbPath);
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
}
