<?php

/**
 * Text Helper
 * 
 * Utility functions for text manipulation and formatting
 * Used across public controllers and views
 */

if (! function_exists('limit_text')) {
    /**
     * Limit plain text to a certain length with ellipsis
     * 
     * @param string|null $text Text to limit
     * @param int $limit Maximum length
     * @param string $ellipsis Suffix to add when truncated
     * @return string Truncated text
     */
    function limit_text(?string $text, int $limit = 200, string $ellipsis = '...'): string
    {
        $plain = trim(strip_tags((string) $text));
        
        if ($plain === '') {
            return '';
        }
        
        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($plain, 0, $limit, $ellipsis, 'UTF-8');
        }
        
        // Fallback for environments without mbstring
        if (strlen($plain) <= $limit) {
            return $plain;
        }
        
        return substr($plain, 0, $limit) . $ellipsis;
    }
}

if (! function_exists('format_date')) {
    /**
     * Format a date string to localized format
     * 
     * @param string|null $date Date string to format
     * @param string $format Localized format (default: 'd MMM yyyy')
     * @return string|null Formatted date or null on failure
     */
    function format_date($date, string $format = 'd MMM yyyy'): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }
        
        try {
            return \CodeIgniter\I18n\Time::parse($date)->toLocalizedString($format);
        } catch (\Throwable $e) {
            log_message('debug', 'Failed to format date: {error}', ['error' => $e->getMessage()]);
            return null;
        }
    }
}

if (! function_exists('format_datetime')) {
    /**
     * Format a datetime string to localized format with time
     * 
     * @param string|null $datetime Datetime string to format
     * @param string $format Localized format (default: 'd MMM yyyy HH:mm')
     * @return string|null Formatted datetime or null on failure
     */
    function format_datetime($datetime, string $format = 'd MMM yyyy HH:mm'): ?string
    {
        return format_date($datetime, $format);
    }
}

if (! function_exists('excerpt')) {
    /**
     * Generate an excerpt from HTML content
     * Strips tags and limits to specified length
     * 
     * @param string|null $html HTML content
     * @param int $limit Maximum length
     * @return string Plain text excerpt
     */
    function excerpt(?string $html, int $limit = 160): string
    {
        return limit_text($html, $limit);
    }
}

if (! function_exists('get_initial')) {
    /**
     * Get the first character (initial) of a string
     * 
     * @param string|null $text Text to get initial from
     * @param string $default Default character if text is empty
     * @return string Single uppercase character
     */
    function get_initial(?string $text, string $default = '?'): string
    {
        $text = trim((string) $text);
        
        if ($text === '') {
            return $default;
        }
        
        if (function_exists('mb_strtoupper') && function_exists('mb_substr')) {
            return mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8');
        }
        
        return strtoupper(substr($text, 0, 1));
    }
}

if (! function_exists('word_count')) {
    /**
     * Count the number of words in a text
     * 
     * @param string|null $text Text to count words from
     * @return int Word count
     */
    function word_count(?string $text): int
    {
        $plain = trim(strip_tags((string) $text));
        
        if ($plain === '') {
            return 0;
        }
        
        // Split by whitespace and count
        return count(preg_split('/\s+/', $plain, -1, PREG_SPLIT_NO_EMPTY));
    }
}

if (! function_exists('reading_time')) {
    /**
     * Estimate reading time for content
     * 
     * @param string|null $text Text content
     * @param int $wpm Words per minute (default: 200)
     * @return int Estimated minutes to read
     */
    function reading_time(?string $text, int $wpm = 200): int
    {
        $words = word_count($text);
        return (int) max(1, ceil($words / $wpm));
    }
}
