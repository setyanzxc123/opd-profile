<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Services;

/**
 * Simplified NewsMediaService for basic OPD profile website
 * Handles only thumbnail image uploads - no video embedding or complex media gallery
 */
class NewsMediaService
{
    private const THUMB_UPLOAD_DIR = 'uploads/news';

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

        if (is_file($realPath)) {
            @unlink($realPath);
        }
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
            ->resize(1200, 630, true, 'width')
            ->save($path, 85);
    }
}

