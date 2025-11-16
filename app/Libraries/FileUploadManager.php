<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * FileUploadManager
 *
 * Centralized file upload utility to eliminate code duplication across controllers.
 * Handles directory creation, file deletion, and common upload operations.
 */
class FileUploadManager
{
    /**
     * Ensure upload directory exists, create if not
     *
     * @param string $relativePath Relative path from FCPATH (e.g., 'uploads/documents')
     * @return string Absolute path to directory
     */
    public static function ensureDirectory(string $relativePath): string
    {
        $target = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
                . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (! is_dir($target)) {
            @mkdir($target, 0775, true);
        }

        return $target;
    }

    /**
     * Safely delete file within uploads directory
     *
     * @param string|null $relativePath Relative path from FCPATH
     * @return bool True if deleted or not exists, false on error
     */
    public static function deleteFile(?string $relativePath): bool
    {
        if (! $relativePath || $relativePath === '') {
            return true;
        }

        $relativePath = ltrim($relativePath, '/\\');
        $fullPath     = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
                      . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        $uploadsRoot  = realpath(rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads');
        $realPath     = realpath($fullPath);

        // Security check: ensure file is within uploads directory
        if (! $uploadsRoot || ! $realPath || strpos($realPath, $uploadsRoot) !== 0) {
            return false;
        }

        if (is_file($realPath)) {
            return @unlink($realPath);
        }

        return true;
    }

    /**
     * Check if uploaded file has allowed MIME type
     *
     * @param UploadedFile $file
     * @param array $allowedMimes Array of allowed MIME types
     * @return bool
     */
    public static function hasAllowedMime(UploadedFile $file, array $allowedMimes): bool
    {
        $mime = strtolower((string) $file->getMimeType());

        return in_array($mime, array_map('strtolower', $allowedMimes), true);
    }

    /**
     * Move uploaded file to target directory
     *
     * @param UploadedFile $file
     * @param string $uploadDir Relative directory path (e.g., 'uploads/documents')
     * @param string|null $originalPath Original file path to delete after successful move
     * @return string|null Relative path of moved file, or null on failure
     */
    public static function moveFile(UploadedFile $file, string $uploadDir, ?string $originalPath = null): ?string
    {
        $targetDir = self::ensureDirectory($uploadDir);
        $newName   = $file->getRandomName();

        try {
            $file->move($targetDir, $newName, true);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to move uploaded file: {error}', ['error' => $e->getMessage()]);
            return null;
        }

        $relativePath = $uploadDir . '/' . $newName;

        // Delete original file if replacement was successful
        if ($originalPath && $originalPath !== $relativePath) {
            self::deleteFile($originalPath);
        }

        return $relativePath;
    }

    /**
     * Get file extension from MIME type
     *
     * @param string $mime
     * @return string|null
     */
    public static function extensionFromMime(string $mime): ?string
    {
        $map = [
            'application/pdf'  => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/zip' => 'zip',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        return $map[strtolower($mime)] ?? null;
    }
}
