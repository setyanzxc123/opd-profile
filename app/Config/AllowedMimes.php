<?php

namespace Config;

/**
 * AllowedMimes Configuration
 * 
 * Centralized MIME type definitions for file upload validation.
 * This ensures consistency across all controllers and makes updates easier.
 */
class AllowedMimes
{
    /**
     * Standard image MIME types (no SVG)
     * Used by: Services, Galleries, News
     */
    public const IMAGES = [
        'image/jpeg',
        'image/jpg',
        'image/pjpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    /**
     * Image MIME types including SVG
     * Used by: AppLinks (logos that may be SVG)
     */
    public const IMAGES_WITH_SVG = [
        'image/jpeg',
        'image/jpg',
        'image/pjpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/svg+xml',
    ];

    /**
     * Document MIME types
     * Used by: Documents
     */
    public const DOCUMENTS = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/zip',
        'application/x-zip-compressed',
    ];

    /**
     * Get validation rule string for images (for CI validation rules)
     */
    public static function imageValidationRule(string $fieldName = 'image'): string
    {
        return 'mime_in[' . $fieldName . ',' . implode(',', self::IMAGES) . ']';
    }

    /**
     * Get validation rule string for images with SVG
     */
    public static function imageWithSvgValidationRule(string $fieldName = 'image'): string
    {
        return 'mime_in[' . $fieldName . ',' . implode(',', self::IMAGES_WITH_SVG) . ']';
    }

    /**
     * Get validation rule string for documents
     */
    public static function documentValidationRule(string $fieldName = 'file'): string
    {
        return 'mime_in[' . $fieldName . ',' . implode(',', self::DOCUMENTS) . ']';
    }
}
