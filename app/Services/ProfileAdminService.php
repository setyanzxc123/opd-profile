<?php

namespace App\Services;

class ProfileAdminService
{
    public const THEME_MODE_PRESET = 'preset';
    public const THEME_MODE_CUSTOM = 'custom';

    public function mergeThemeSettings($raw): array
    {
        return ThemeStyleService::mergeSettings($raw);
    }

    public function getThemePresets(): array
    {
        return ThemeStyleService::presetThemes();
    }

    public function detectThemePresetSlug(array $theme): ?string
    {
        return ThemeStyleService::detectPresetSlug($theme);
    }

    public function buildThemeFromPreset(?string $slug, array $defaults): array
    {
        $preset = ThemeStyleService::getPresetTheme($slug);

        if ($preset) {
            return ThemeStyleService::mergeSettings([
                'primary' => $preset['primary'],
                'surface' => $preset['surface'],
            ]);
        }

        return ThemeStyleService::mergeSettings($defaults);
    }

    public function buildCustomTheme(?string $primary, ?string $surface, array $defaults): array
    {
        $basePrimary = $primary ?? ($defaults['primary'] ?? ThemeStyleService::DEFAULT_THEME['primary']);
        $baseSurface = $surface ?? ($defaults['surface'] ?? ThemeStyleService::DEFAULT_THEME['surface']);

        return ThemeStyleService::mergeSettings([
            'primary' => $basePrimary,
            'surface' => $baseSurface,
        ]);
    }

    public function normalizeThemeMode($value): string
    {
        if (is_string($value)) {
            $candidate = strtolower(trim($value));
            if (in_array($candidate, [self::THEME_MODE_PRESET, self::THEME_MODE_CUSTOM], true)) {
                return $candidate;
            }
        }

        return self::THEME_MODE_PRESET;
    }

    public function getThemeModeOptions(): array
    {
        return [self::THEME_MODE_PRESET, self::THEME_MODE_CUSTOM];
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

    public function validateThemeAccessibility(array $theme, float $minimumRatio = 4.5): ?string
    {
        $primary = $theme['primary'] ?? ThemeStyleService::DEFAULT_THEME['primary'];
        $surface = $theme['surface'] ?? ThemeStyleService::DEFAULT_THEME['surface'];
        $neutral = $theme['neutral'] ?? ThemeStyleService::deriveAccessibleNeutral($surface);

        if (! ThemeStyleService::passesContrast('#FFFFFF', $primary, $minimumRatio)) {
            return 'Warna utama harus memiliki kontras yang cukup terhadap teks.';
        }

        if (! ThemeStyleService::passesContrast($neutral, $surface, $minimumRatio)) {
            return 'Kombinasi warna latar dan teks tidak memenuhi standar kontras.';
        }

        return null;
    }
}
