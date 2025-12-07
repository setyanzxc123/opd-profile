<?php

/**
 * Image Helper
 * 
 * Fungsi-fungsi untuk optimasi gambar di sisi publik
 * termasuk responsive images dengan srcset dan variant generation
 */

if (! function_exists('responsive_image')) {
    /**
     * Generate atribut responsive image (src, srcset, sizes)
     * 
     * @param string $imagePath Path relatif gambar (e.g., 'uploads/news/image.jpg')
     * @param string $alt Alt text untuk gambar
     * @param array $options Opsi tambahan:
     *   - 'widths' => array ukuran width yang diinginkan (default: [400, 800, 1200])
     *   - 'sizes' => nilai sizes attribute (default: '100vw')
     *   - 'class' => CSS classes
     *   - 'loading' => 'lazy' atau 'eager' (default: 'lazy')
     *   - 'fetchpriority' => 'high', 'low', atau 'auto' (default: 'auto')
     *   - 'decoding' => 'async', 'sync', atau 'auto' (default: 'async')
     *   - 'default_width' => width untuk src fallback (default: 800)
     *   - 'default_height' => height untuk dimensi (default: null, auto-calculated)
     *   - 'aspect_ratio' => rasio aspek (e.g., 16/9) untuk height calculation
     * @return string HTML img tag dengan srcset
     */
    function responsive_image(string $imagePath, string $alt = '', array $options = []): string
    {
        // Default options
        $widths = $options['widths'] ?? [400, 800, 1200];
        $sizes = $options['sizes'] ?? '100vw';
        $class = $options['class'] ?? '';
        $loading = $options['loading'] ?? 'lazy';
        $fetchpriority = $options['fetchpriority'] ?? 'auto';
        $decoding = $options['decoding'] ?? 'async';
        $defaultWidth = $options['default_width'] ?? 800;
        $defaultHeight = $options['default_height'] ?? null;
        $aspectRatio = $options['aspect_ratio'] ?? (16 / 9);
        
        // Normalize slashes for consistency
        $imagePath = str_replace('\\', '/', $imagePath);
        $imagePath = ltrim($imagePath, '/');
        
        // Get full path for checking file existence
        $fullPath = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $imagePath);
        
        // Base URL (always forward slash via helper logic)
        $baseUrl = base_url($imagePath);
        
        // Generate srcset entries
        $srcsetParts = [];
        $actualWidths = [];
        
        // Check if original image exists and get its dimensions
        $originalWidth = $defaultWidth;
        $originalHeight = $defaultHeight;
        
        if (file_exists($fullPath)) {
            $imageInfo = @getimagesize($fullPath);
            if ($imageInfo) {
                $originalWidth = $imageInfo[0];
                $originalHeight = $imageInfo[1];
            }
        }
        
        // Calculate default height from aspect ratio if not provided
        if ($defaultHeight === null && $aspectRatio) {
            $defaultHeight = (int) round($defaultWidth / $aspectRatio);
        }
        
        // For srcset, we use the same image with width descriptors
        foreach ($widths as $width) {
            // Only include widths smaller or equal to original
            if ($width <= $originalWidth) {
                // Check if resized variant exists
                $variantPath = get_image_variant_path($imagePath, $width);
                $variantSystemPath = str_replace('/', DIRECTORY_SEPARATOR, $variantPath);
                $variantFullPath = FCPATH . $variantSystemPath;
                
                if (file_exists($variantFullPath)) {
                    $srcsetParts[] = base_url($variantPath) . ' ' . $width . 'w';
                    $actualWidths[] = $width;
                }
            }
        }
        
        // Always include original as the largest option
        // Ensure base URL doesn't look weird if $imagePath has leading slash (already trimmed)
        $srcsetParts[] = $baseUrl . ' ' . $originalWidth . 'w';
        $actualWidths[] = $originalWidth;
        
        // Build srcset attribute
        $srcset = implode(', ', array_unique($srcsetParts));
        
        // Build img tag attributes
        $attrs = [];
        $attrs[] = 'src="' . esc($baseUrl, 'attr') . '"';
        
        // Only add srcset if we have multiple sources
        if (count(array_unique($srcsetParts)) > 1) {
            $attrs[] = 'srcset="' . esc($srcset, 'attr') . '"';
            $attrs[] = 'sizes="' . esc($sizes, 'attr') . '"';
        }
        
        $attrs[] = 'alt="' . esc($alt, 'attr') . '"';
        
        // Add dimensions for CLS prevention
        if ($defaultWidth) {
            $attrs[] = 'width="' . (int) $defaultWidth . '"';
        }
        if ($defaultHeight) {
            $attrs[] = 'height="' . (int) $defaultHeight . '"';
        }
        
        // Loading strategy
        $attrs[] = 'loading="' . esc($loading, 'attr') . '"';
        $attrs[] = 'decoding="' . esc($decoding, 'attr') . '"';
        
        if ($fetchpriority !== 'auto') {
            $attrs[] = 'fetchpriority="' . esc($fetchpriority, 'attr') . '"';
        }
        
        if ($class) {
            $attrs[] = 'class="' . esc($class, 'attr') . '"';
        }
        
        return '<img ' . implode(' ', $attrs) . '>';
    }
}

if (! function_exists('get_image_variant_path')) {
    /**
     * Get the path for a resized variant of an image
     * 
     * @param string $originalPath Path asli (e.g., 'uploads/news/image.jpg')
     * @param int $width Width variant
     * @return string Path variant (e.g., 'uploads/news/image-400.jpg')
     */
    function get_image_variant_path(string $originalPath, int $width): string
    {
        $pathInfo = pathinfo($originalPath);
        $dir = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'] ?? '';
        $ext = $pathInfo['extension'] ?? 'jpg';
        
        $variantFilename = $filename . '-' . $width . '.' . $ext;
        $variantPath = $dir !== '.' ? ($dir . '/' . $variantFilename) : $variantFilename;
        
        // Ensure forward slashes for URL usage
        return str_replace('\\', '/', $variantPath);
    }
}

if (! function_exists('responsive_srcset')) {
    /**
     * Generate hanya atribut srcset dan sizes untuk digunakan inline
     * 
     * @param string $imagePath Path relatif gambar ATAU full URL
     * @param array $widths Array ukuran width
     * @param string $sizes Nilai sizes attribute
     * @return array ['srcset' => string, 'sizes' => string]
     */
    function responsive_srcset(string $imagePath, array $widths = [400, 800, 1200], string $sizes = '100vw'): array
    {
        // Normalize slashes for consistency
        $imagePath = str_replace('\\', '/', $imagePath);
        
        // Check if input is already a full URL
        $isUrl = preg_match('#^https?://#i', $imagePath);
        
        if ($isUrl) {
            // Extract relative path from URL for file checking
            $baseUrlValue = rtrim(base_url(), '/');
            $relativePath = str_replace($baseUrlValue . '/', '', $imagePath);
            $relativePath = ltrim($relativePath, '/');
            $baseUrl = $imagePath; // Use as-is for src
        } else {
            $relativePath = ltrim($imagePath, '/');
            $baseUrl = base_url($relativePath);
        }
        
        // Full System Path for file checking
        $fullPath = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        
        $srcsetParts = [];
        $originalWidth = 1200; // Default
        
        if (file_exists($fullPath)) {
            $imageInfo = @getimagesize($fullPath);
            if ($imageInfo) {
                $originalWidth = $imageInfo[0];
            }
        }
        
        foreach ($widths as $width) {
            if ($width <= $originalWidth) {
                $variantPath = get_image_variant_path($relativePath, $width);
                // System Path check
                $variantSystemPath = str_replace('/', DIRECTORY_SEPARATOR, $variantPath);
                $variantFullPath = FCPATH . $variantSystemPath;
                
                if (file_exists($variantFullPath)) {
                    $srcsetParts[] = base_url($variantPath) . ' ' . $width . 'w';
                }
            }
        }
        
        $srcsetParts[] = $baseUrl . ' ' . $originalWidth . 'w';
        
        return [
            'srcset' => implode(', ', array_unique($srcsetParts)),
            'sizes' => $sizes,
        ];
    }
}

if (! function_exists('image_dimensions')) {
    function image_dimensions(string $imagePath): ?array
    {
        $imagePath = ltrim($imagePath, '/');
        $fullPath = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $imagePath);
        
        if (!file_exists($fullPath)) {
            return null;
        }
        
        $info = @getimagesize($fullPath);
        if (!$info) {
            return null;
        }
        
        return [
            'width' => $info[0],
            'height' => $info[1],
        ];
    }
}

if (! function_exists('generate_image_variants')) {
    /**
     * Generate responsive image variants at different sizes
     * 
     * @param string $sourcePath Full path to source image
     * @param array $sizes Array of width => height pairs
     */
    function generate_image_variants(string $sourcePath, array $sizes = [400 => 225, 800 => 450, 1200 => 675]): void
    {
        $info = @getimagesize($sourcePath);
        if (!$info) {
            return;
        }
        
        $sourceWidth = $info[0];
        $pathInfo = pathinfo($sourcePath);
        $dir = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $ext = $pathInfo['extension'] ?? 'jpg';
        
        $imageService = \Config\Services::image();
        
        foreach ($sizes as $targetWidth => $targetHeight) {
            // Skip if target is larger than source
            if ($targetWidth >= $sourceWidth) {
                continue;
            }
            
            $variantPath = $dir . DIRECTORY_SEPARATOR . $filename . '-' . $targetWidth . '.' . $ext;
            
            try {
                $imageService->withFile($sourcePath)
                    ->resize($targetWidth, $targetHeight, true, 'width')
                    ->save($variantPath, 85);
            } catch (\Throwable $e) {
                log_message('warning', 'Failed to create variant {width}w: {error}', [
                    'width' => $targetWidth,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}

if (! function_exists('delete_image_variants')) {
    /**
     * Delete all responsive variants of an image
     * 
     * @param string $sourcePath Full path to source image
     * @param array $widths Array of widths to check
     */
    function delete_image_variants(string $sourcePath, array $widths = [400, 800, 1200]): void
    {
        $pathInfo = pathinfo($sourcePath);
        $dir = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $ext = $pathInfo['extension'] ?? 'jpg';
        
        foreach ($widths as $width) {
            $variantPath = $dir . DIRECTORY_SEPARATOR . $filename . '-' . $width . '.' . $ext;
            if (is_file($variantPath)) {
                @unlink($variantPath);
            }
        }
    }
}
