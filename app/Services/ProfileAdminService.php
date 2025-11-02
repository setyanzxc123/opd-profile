<?php

namespace App\Services;

class ProfileAdminService
{
    public function mergeThemeSettings($raw): array
    {
        return ThemeStyleService::mergeSettings($raw);
    }

    public function normalizeHexColor($value): ?string
    {
        if ($value === null || is_array($value)) {
            return null;
        }

        $candidate = trim((string) $value);
        if ($candidate === '') {
            return null;
        }

        if ($candidate[0] !== '#') {
            $candidate = '#' . $candidate;
        }

        if (! preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $candidate)) {
            return null;
        }

        if (strlen($candidate) === 4) {
            $candidate = sprintf(
                '#%s%s%s%s%s%s',
                $candidate[1],
                $candidate[1],
                $candidate[2],
                $candidate[2],
                $candidate[3],
                $candidate[3]
            );
        }

        return strtoupper($candidate);
    }

    public function isAffirmative($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            return in_array($normalized, ['1', 'true', 'on', 'yes'], true);
        }

        return false;
    }

    public function shouldRemove($value): bool
    {
        return $this->isAffirmative($value);
    }

    public function decodeLogoMeta($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return [];
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return [];
        }

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $throwable) {
            log_message('debug', 'Failed to decode logo meta: {error}', ['error' => $throwable->getMessage()]);

            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    public function normalizeCoordinate($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $filtered = trim((string) $value);
        if ($filtered === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $filtered);

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    public function normalizeZoom($value): ?int
    {
        if ($value === null) {
            return null;
        }

        $filtered = trim((string) $value);
        if ($filtered === '') {
            return null;
        }

        if (ctype_digit($filtered)) {
            return (int) $filtered;
        }

        $sanitized = filter_var($filtered, FILTER_SANITIZE_NUMBER_INT);

        return $sanitized === '' ? null : (int) $sanitized;
    }

    public function normalizeDisplayFlag($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (int) ((string) $value === '1' ? 1 : 0);
    }

    public function resolveTheme(array $currentTheme, array $incomingTheme, bool $reset, array $defaults): array
    {
        $finalTheme = $reset ? $defaults : $currentTheme;

        if (! $reset) {
            foreach ($incomingTheme as $key => $value) {
                if ($value !== null) {
                    $finalTheme[$key] = $value;
                }
            }
        }

        return $finalTheme;
    }
}

