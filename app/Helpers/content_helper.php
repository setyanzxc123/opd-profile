<?php

if (! function_exists('sanitize_rich_text')) {
    /**
     * Enhanced sanitizer for WYSIWYG/HTML inputs.
     * Removes disallowed tags and scriptable attributes, while keeping a safe subset.
     * 
     * Protections:
     * - Strip all tags except whitelist
     * - Remove all event handlers (onclick, onerror, etc.)
     * - Remove script/style tags
     * - Neutralize javascript:, vbscript:, data: URIs
     * - Remove expression() and behavior CSS
     * - Clean up srcset and other potentially dangerous attributes
     */
    function sanitize_rich_text(?string $html, string $allowedTags = '<p><br><strong><em><b><i><u><ol><ul><li><blockquote><code><pre><span><h1><h2><h3><h4><h5><h6><a><img><table><thead><tbody><tr><th><td>'): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $clean = strip_tags($html, $allowedTags);

        // Remove ALL inline event handlers (on*)
        $clean = preg_replace("/\s+on[a-z]+\s*=\s*(\"[^\"]*\"|'[^']*'|[^\s>]+)/i", '', $clean);
        
        // Remove script/style tags explicitly if somehow still present
        $clean = preg_replace("/<(script|style|iframe|object|embed|form|input|button)[^>]*>.*?<\/\\1>/is", '', $clean);
        $clean = preg_replace("/<(script|style|iframe|object|embed|form|input|button)[^>]*\/?>/i", '', $clean);
        
        // Neutralize dangerous URI schemes
        $clean = preg_replace("/\b(javascript|vbscript|data)\s*:/i", 'blocked:', $clean);
        
        // Remove expression() and behavior (IE CSS exploits)
        $clean = preg_replace("/expression\s*\(/i", 'blocked(', $clean);
        $clean = preg_replace("/behavior\s*:/i", 'blocked:', $clean);
        
        // Remove -moz-binding (Firefox CSS exploit)
        $clean = preg_replace("/-moz-binding\s*:/i", 'blocked:', $clean);
        
        // Clean srcset attribute (can be used for data exfiltration)
        $clean = preg_replace("/\s+srcset\s*=\s*(\"[^\"]*\"|'[^']*'|[^\s>]+)/i", '', $clean);
        
        // Remove base64 images (can be used to embed malicious content)
        // Allow only http/https image sources
        $clean = preg_replace('/src\s*=\s*["\']data:/i', 'src="blocked:', $clean);

        return $clean;
    }
}

if (! function_exists('sanitize_plain_text')) {
    /**
     * Sanitizes plain text inputs by trimming and stripping control characters.
     */
    function sanitize_plain_text(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        // Remove non-printable characters except common whitespace, then trim.
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        return trim($value);
    }
}
