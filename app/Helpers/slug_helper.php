<?php

use CodeIgniter\Database\BaseBuilder;

if (! function_exists('unique_slug')) {
    /**
     * Generate a unique slug from title
     *
     * @param string $title The title to convert to slug
     * @param BaseBuilder|object $queryBuilder Query builder instance for checking uniqueness
     * @param string $column The column name to check for slug uniqueness (default: 'slug')
     * @param string|null $customSlug Optional custom slug to use instead of auto-generating from title
     * @param int|null $ignoreId Optional ID to ignore when checking uniqueness (for updates)
     * @param string $fallback Fallback slug if title is empty (default: 'item')
     * @return string The unique slug
     */
    function unique_slug(
        string $title,
        object $queryBuilder,
        string $column = 'slug',
        ?string $customSlug = null,
        ?int $ignoreId = null,
        string $fallback = 'item'
    ): string {
        helper('text');

        $base = $customSlug !== null && $customSlug !== '' ? $customSlug : $title;
        $base = url_title($base, '-', true);

        if ($base === '') {
            $base = $fallback;
        }

        $slug = $base;
        $i    = 2;

        while (true) {
            $query = $queryBuilder->where($column, $slug);

            if ($ignoreId !== null) {
                $query = $query->where('id !=', $ignoreId);
            }

            if (! $query->first()) {
                break;
            }

            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
