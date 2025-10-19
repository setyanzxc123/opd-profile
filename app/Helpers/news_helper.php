<?php

if (! function_exists('news_trim_excerpt')) {
    /**
     * Returns a sanitized excerpt limited to the desired length.
     */
    function news_trim_excerpt(?string $excerpt, string $content = '', int $limit = 160): string
    {
        $limit = max(40, $limit);
        $candidate = $excerpt ?? '';

        if ($candidate === '' && $content !== '') {
            $candidate = strip_tags($content);
        }

        if (function_exists('sanitize_plain_text')) {
            $candidate = sanitize_plain_text($candidate);
        } else {
            $candidate = trim($candidate);
        }

        if ($candidate === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($candidate) > $limit) {
                $candidate = mb_substr($candidate, 0, $limit);
            }
        } else {
            if (strlen($candidate) > $limit) {
                $candidate = substr($candidate, 0, $limit);
            }
        }

        return rtrim($candidate);
    }
}

if (! function_exists('news_resolve_meta_title')) {
    function news_resolve_meta_title(?string $metaTitle, string $title, int $limit = 70): string
    {
        $limit = max(20, $limit);
        $candidate = $metaTitle ?? '';

        if ($candidate === '') {
            $candidate = $title;
        }

        if (function_exists('sanitize_plain_text')) {
            $candidate = sanitize_plain_text($candidate);
        } else {
            $candidate = trim($candidate);
        }

        if ($candidate === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($candidate) > $limit) {
                $candidate = mb_substr($candidate, 0, $limit);
            }
        } else {
            if (strlen($candidate) > $limit) {
                $candidate = substr($candidate, 0, $limit);
            }
        }

        return rtrim($candidate);
    }
}

if (! function_exists('news_resolve_meta_description')) {
    function news_resolve_meta_description(?string $metaDescription, string $excerpt = '', string $content = '', int $limit = 160): string
    {
        $limit = max(40, $limit);
        $candidate = $metaDescription ?? '';

        if ($candidate === '') {
            $candidate = $excerpt !== '' ? $excerpt : strip_tags($content);
        }

        if (function_exists('sanitize_plain_text')) {
            $candidate = sanitize_plain_text($candidate);
        } else {
            $candidate = trim($candidate);
        }

        if ($candidate === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($candidate) > $limit) {
                $candidate = mb_substr($candidate, 0, $limit);
            }
        } else {
            if (strlen($candidate) > $limit) {
                $candidate = substr($candidate, 0, $limit);
            }
        }

        return rtrim($candidate);
    }
}

