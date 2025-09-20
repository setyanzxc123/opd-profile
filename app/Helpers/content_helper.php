<?php

if (! function_exists('sanitize_rich_text')) {
    /**
     * Basic sanitizer for WYSIWYG/HTML inputs.
     * Removes disallowed tags and scriptable attributes, while keeping a safe subset.
     */
    function sanitize_rich_text(?string $html, string $allowedTags = '<p><br><strong><em><b><i><u><ol><ul><li><blockquote><code><pre><span><h1><h2><h3><h4><h5><h6><a>'): string
    {
        if ($html === null) {
            return '';
        }

        $clean = strip_tags($html, $allowedTags);

        // Drop inline event handlers and styles that could embed scripts.
        $clean = preg_replace("/\s+on[a-z]+\s*=\s*(\"[^\"]*\"|'[^']*'|[^\s>]+)/i", '', $clean);
        // Remove script/style tags explicitly if still present.
        $clean = preg_replace("/<(script|style)[^>]*>.*?<\/\\1>/is", '', $clean);
        // Neutralize javascript: URIs.
        $clean = preg_replace("/javascript\s*:/i", '', $clean);

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
