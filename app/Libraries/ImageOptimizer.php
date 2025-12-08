<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * ImageOptimizer
 *
 * Centralized image upload, resize, and optimization utility.
 * Consolidates duplicated logic from Services and Galleries controllers.
 * 
 * Uses CodeIgniter's built-in Image Manipulation class for maximum compatibility.
 */
class ImageOptimizer
{
    /**
     * Default resize dimensions for different contexts
     */
    public const PRESET_SERVICE = [
        'maxWidth'  => 1280,
        'maxHeight' => 720,
        'quality'   => 80,
    ];

    public const PRESET_GALLERY = [
        'maxWidth'  => 1920,
        'maxHeight' => 1080,
        'quality'   => 80,
    ];

    public const PRESET_NEWS = [
        'maxWidth'  => 1200,
        'maxHeight' => 800,
        'quality'   => 85,
    ];

    public const PRESET_HERO = [
        'maxWidth'  => 1920,
        'maxHeight' => 1080,
        'quality'   => 85,
    ];

    /**
     * Move uploaded image, resize it, and generate responsive variants.
     *
     * @param UploadedFile $file         The uploaded file
     * @param string       $uploadDir    Relative directory path (e.g., 'uploads/services')
     * @param string|null  $originalPath Original file path to delete after successful move
     * @param array        $options      Resize options: maxWidth, maxHeight, quality, generateVariants
     * @return string|null Relative path of the saved file, or null on failure
     */
    public static function moveAndOptimize(
        UploadedFile $file,
        string $uploadDir,
        ?string $originalPath = null,
        array $options = []
    ): ?string {
        // Merge with defaults
        $maxWidth  = $options['maxWidth'] ?? 1920;
        $maxHeight = $options['maxHeight'] ?? 1080;
        $quality   = $options['quality'] ?? 80;
        $generateVariants = $options['generateVariants'] ?? true;

        // Use FileUploadManager for safe file move
        $newPath = FileUploadManager::moveFile($file, $uploadDir, $originalPath);
        if ($newPath === null) {
            return null;
        }

        // Convert relative path to absolute
        $fullPath = self::toAbsolutePath($newPath);

        // Resize and optimize
        try {
            $imageService = \Config\Services::image();
            $imageService
                ->withFile($fullPath)
                ->resize($maxWidth, $maxHeight, true, 'width')
                ->save($fullPath, $quality);

            // Generate responsive variants if enabled
            if ($generateVariants) {
                helper('image');
                if (function_exists('generate_image_variants')) {
                    generate_image_variants($fullPath);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'ImageOptimizer: Failed to optimize image: {error}', [
                'error' => $e->getMessage(),
                'file'  => $fullPath,
            ]);
            // Don't fail the upload if optimization fails - file is already saved
        }

        return $newPath;
    }

    /**
     * Move and optimize using a preset configuration.
     *
     * @param UploadedFile $file
     * @param string       $uploadDir
     * @param string       $preset     One of: 'service', 'gallery', 'news', 'hero'
     * @param string|null  $originalPath
     * @return string|null
     */
    public static function moveWithPreset(
        UploadedFile $file,
        string $uploadDir,
        string $preset = 'gallery',
        ?string $originalPath = null
    ): ?string {
        $presetConfig = match (strtolower($preset)) {
            'service'  => self::PRESET_SERVICE,
            'gallery'  => self::PRESET_GALLERY,
            'news'     => self::PRESET_NEWS,
            'hero'     => self::PRESET_HERO,
            default    => self::PRESET_GALLERY,
        };

        return self::moveAndOptimize($file, $uploadDir, $originalPath, $presetConfig);
    }

    /**
     * Delete an image file along with all its responsive variants.
     *
     * @param string|null $relativePath Relative path from FCPATH
     * @return bool True if deleted successfully
     */
    public static function deleteWithVariants(?string $relativePath): bool
    {
        if (empty($relativePath)) {
            return true;
        }

        $fullPath = self::toAbsolutePath($relativePath);

        // Delete responsive variants first
        helper('image');
        if (function_exists('delete_image_variants')) {
            delete_image_variants($fullPath);
        }

        // Then delete the main file
        return FileUploadManager::deleteFile($relativePath);
    }

    /**
     * Convert relative path to absolute path.
     *
     * @param string $relativePath
     * @return string
     */
    public static function toAbsolutePath(string $relativePath): string
    {
        return rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
             . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));
    }

    /**
     * Convert absolute path to relative path (from FCPATH).
     *
     * @param string $absolutePath
     * @return string
     */
    public static function toRelativePath(string $absolutePath): string
    {
        $fcpath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        
        if (strpos($absolutePath, $fcpath) === 0) {
            return str_replace(DIRECTORY_SEPARATOR, '/', substr($absolutePath, strlen($fcpath)));
        }

        return $absolutePath;
    }
}
