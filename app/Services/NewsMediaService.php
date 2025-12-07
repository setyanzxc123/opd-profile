<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Services;

/**
 * Simplified NewsMediaService for basic OPD profile website
 * Handles thumbnail image uploads with responsive variants
 */
class NewsMediaService
{
    private const THUMB_UPLOAD_DIR = 'uploads/news';
    
    /**
     * Responsive image widths to generate
     * Format: width => aspectRatioHeight (based on 16:9)
     */
    private const RESPONSIVE_SIZES = [
        400 => 225,   // 400x225 (16:9)
        800 => 450,   // 800x450 (16:9)
        1200 => 675,  // 1200x675 (16:9)
    ];

    public function moveThumbnail(UploadedFile $file): ?string
    {
        $targetDir = $this->ensureDirectory(self::THUMB_UPLOAD_DIR);
        $newName   = $file->getRandomName();

        try {
            $file->move($targetDir, $newName, true);
        } catch (\Throwable $throwable) {
            log_message('error', 'Failed to store news thumbnail: {error}', ['error' => $throwable->getMessage()]);
            return null;
        }

        $relativePath = self::THUMB_UPLOAD_DIR . '/' . $newName;
        $fullPath     = $targetDir . DIRECTORY_SEPARATOR . $newName;

        try {
            $this->optimiseImage($fullPath);
            $this->generateResponsiveVariants($fullPath);
        } catch (\Throwable $throwable) {
            log_message('error', 'Failed to optimize news thumbnail: {error}', ['error' => $throwable->getMessage()]);
        }

        return $relativePath;
    }

    public function deleteFile(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        $relativePath = ltrim($relativePath, '/\\');
        $fullPath     = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        $uploadsRoot  = realpath(rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads');
        $realPath     = realpath($fullPath);

        if (! $uploadsRoot || ! $realPath || strpos($realPath, $uploadsRoot) !== 0) {
            return;
        }

        // Delete main file
        if (is_file($realPath)) {
            @unlink($realPath);
        }
        
        // Delete responsive variants
        $this->deleteResponsiveVariants($realPath);
    }

    private function ensureDirectory(string $relativePath): string
    {
        $base   = rtrim(FCPATH, DIRECTORY_SEPARATOR);
        $target = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (! is_dir($target)) {
            @mkdir($target, 0775, true);
        }

        return $target;
    }

    private function optimiseImage(string $path): void
    {
        $image = Services::image();
        $info  = @getimagesize($path);
        $width = $info[0] ?? null;

        $editor = $image->withFile($path);

        if ($width !== null && $width <= 1200) {
            $editor->save($path, 85);
            return;
        }

        $editor
            ->resize(1200, 675, true, 'width')
            ->save($path, 85);
    }
    
    /**
     * Generate responsive image variants at different sizes
     */
    private function generateResponsiveVariants(string $sourcePath): void
    {
        helper('image');
        generate_image_variants($sourcePath, self::RESPONSIVE_SIZES);
    }
    
    /**
     * Delete all responsive variants of an image
     */
    private function deleteResponsiveVariants(string $sourcePath): void
    {
        helper('image');
        delete_image_variants($sourcePath, array_keys(self::RESPONSIVE_SIZES));
    }
}


