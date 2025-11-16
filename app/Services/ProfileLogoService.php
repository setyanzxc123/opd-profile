<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Services;

class ProfileLogoService
{
    public const TYPE_PUBLIC = 'public';
    public const TYPE_ADMIN  = 'admin';

    private const UPLOAD_DIR = 'uploads/profile';

    private const MIN_DIMENSION = 48;

    private const MAX_DIMENSIONS = [
        self::TYPE_PUBLIC => 512,
        self::TYPE_ADMIN  => 320,
    ];

    public function store(UploadedFile $file, string $type = self::TYPE_PUBLIC, array $options = []): string
    {
        $type = $this->normalizeType($type);

        // Get image dimensions using CI4's getImageFile() helper
        $imageInfo = \Config\Services::image()->withFile($file->getTempName())->getFile();

        if (! $imageInfo) {
            throw new \RuntimeException('Logo tidak valid atau tidak dapat dibaca. Gunakan berkas JPG, PNG, WEBP, atau GIF.');
        }

        $width = $imageInfo->origWidth ?? 0;
        $height = $imageInfo->origHeight ?? 0;

        if ($width < self::MIN_DIMENSION || $height < self::MIN_DIMENSION) {
            throw new \RuntimeException('Logo terlalu kecil. Gunakan gambar dengan ukuran minimal ' . self::MIN_DIMENSION . 'x' . self::MIN_DIMENSION . ' piksel.');
        }

        $targetDir = $this->ensureUploadsDir();
        $extension = strtolower((string) $file->getClientExtension());

        // Use CI4's guessExtension() if extension is empty
        if ($extension === '') {
            $extension = $file->guessExtension() ?? 'png';
        }

        $token = $this->randomToken();

        $filename = sprintf(
            '%s-logo-%s-%s.%s',
            date('YmdHis'),
            $type,
            $token,
            $extension
        );

        try {
            $file->move($targetDir, $filename, true);
        } catch (\Throwable $throwable) {
            throw new \RuntimeException('Gagal menyimpan berkas logo. ' . $throwable->getMessage(), 0, $throwable);
        }

        $relativePath = self::UPLOAD_DIR . '/' . $filename;
        $fullPath     = $targetDir . DIRECTORY_SEPARATOR . $filename;

        $maxDimensionOverride = isset($options['maxDimension']) ? (int) $options['maxDimension'] : null;

        $this->optimizeImage($fullPath, $type, $width, $height, $maxDimensionOverride);

        return $relativePath;
    }

    public function delete(?string $relativePath, array $preserve = []): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        $normalizedPath = $this->normalizeRelativePath($relativePath);
        $preservedPaths = array_map([$this, 'normalizeRelativePath'], array_filter($preserve));

        if ($preservedPaths !== [] && in_array($normalizedPath, $preservedPaths, true)) {
            return;
        }

        $fullPath = $this->relativeToAbsolute($normalizedPath);
        if ($fullPath === null || ! is_file($fullPath)) {
            return;
        }

        @unlink($fullPath);
    }

    private function ensureUploadsDir(): string
    {
        $target = $this->relativeToAbsolute(self::UPLOAD_DIR);
        if ($target === null) {
            $base   = rtrim(FCPATH, DIRECTORY_SEPARATOR);
            $target = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, self::UPLOAD_DIR);
        }

        if (! is_dir($target)) {
            @mkdir($target, 0775, true);
        }

        return $target;
    }

    private function normalizeType(string $type): string
    {
        $type = strtolower($type);

        return in_array($type, [self::TYPE_PUBLIC, self::TYPE_ADMIN], true) ? $type : self::TYPE_PUBLIC;
    }


    private function randomToken(): string
    {
        try {
            return bin2hex(random_bytes(4));
        } catch (\Throwable $throwable) {
            return substr(sha1(uniqid((string) mt_rand(), true)), 0, 8);
        }
    }

    private function optimizeImage(string $path, string $type, int $originalWidth, int $originalHeight, ?int $maxDimensionOverride = null): void
    {
        $fallbackMax = self::MAX_DIMENSIONS[$type] ?? self::MAX_DIMENSIONS[self::TYPE_PUBLIC];
        $maxDimension = $maxDimensionOverride !== null && $maxDimensionOverride > 0 ? $maxDimensionOverride : $fallbackMax;

        $needsResize = $originalWidth > $maxDimension || $originalHeight > $maxDimension;

        try {
            $image  = Services::image();
            $editor = $image->withFile($path);

            if ($needsResize) {
                $editor->resize($maxDimension, $maxDimension, true, 'auto');
            }

            $editor->save($path, 90);
        } catch (\Throwable $throwable) {
            log_message('warning', 'Failed to optimize profile logo: {error}', ['error' => $throwable->getMessage()]);
        }
    }

    private function normalizeRelativePath(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/\\');

        return str_replace('\\', '/', $relativePath);
    }

    private function relativeToAbsolute(string $relativePath): ?string
    {
        $base = rtrim(FCPATH, DIRECTORY_SEPARATOR);
        if ($base === '') {
            return null;
        }

        $fullPath = $base . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        $uploadsRoot = realpath($base . DIRECTORY_SEPARATOR . 'uploads');

        $resolved = realpath($fullPath) ?: $fullPath;

        if ($uploadsRoot && strpos($resolved, $uploadsRoot) !== 0) {
            return null;
        }

        return $resolved;
    }
}
