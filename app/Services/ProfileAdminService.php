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
        $config = config('ProfileTheme');
        if ($config && is_array($config->presets ?? null) && $config->presets !== []) {
            return $config->presets;
        }

        return ThemeStyleService::presetThemes();
    }

    public function getDefaultThemePresetSlug(): string
    {
        $config  = config('ProfileTheme');
        $presets = $this->getThemePresets();
        $default = is_string($config->defaultPreset ?? null) ? $config->defaultPreset : null;

        if ($default && array_key_exists($default, $presets)) {
            return $default;
        }

        return array_key_first($presets) ?? ThemeStyleService::DEFAULT_PRESET;
    }

    public function detectThemePresetSlug(array $theme): ?string
    {
        $presets = $this->getThemePresets();
        if ($presets === []) {
            return null;
        }

        $merged = ThemeStyleService::mergeSettings($theme);

        foreach ($presets as $slug => $preset) {
            $candidate = ThemeStyleService::mergeSettings([
                'primary' => $preset['primary'] ?? null,
                'surface' => $preset['surface'] ?? null,
            ]);

            if (($candidate['primary'] ?? null) === ($merged['primary'] ?? null)
                && ($candidate['surface'] ?? null) === ($merged['surface'] ?? null)
            ) {
                return $slug;
            }
        }

        return null;
    }

    public function buildThemeFromPreset(?string $slug, array $defaults): array
    {
        $presets = $this->getThemePresets();
        $preset  = $slug !== null ? ($presets[$slug] ?? null) : null;

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
            $candidate = '#' . ltrim($candidate, '#');
        }

        if (preg_match('/^#([0-9A-Fa-f]{3})$/', $candidate, $matches)) {
            $candidate = sprintf(
                '#%s%s%s%s%s%s',
                $matches[1][0],
                $matches[1][0],
                $matches[1][1],
                $matches[1][1],
                $matches[1][2],
                $matches[1][2]
            );
        }

        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $candidate)) {
            return null;
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

    public function minimumContrastRatio(): float
    {
        $config = config('ProfileTheme');
        $value  = (float) ($config->minimumContrast ?? 4.5);

        if ($value < 1.0) {
            $value = 1.0;
        }

        return $value;
    }

    public function validateThemeAccessibility(array $theme, ?float $minimumRatio = null): ?string
    {
        $minimumRatio = $minimumRatio ?? $this->minimumContrastRatio();
        $primary      = $theme['primary'] ?? ThemeStyleService::DEFAULT_THEME['primary'];
        $surface      = $theme['surface'] ?? ThemeStyleService::DEFAULT_THEME['surface'];
        $neutral      = $theme['neutral'] ?? ThemeStyleService::deriveAccessibleNeutral($surface);

        if (! ThemeStyleService::passesContrast('#FFFFFF', $primary, $minimumRatio)) {
            return 'Warna utama harus memiliki kontras yang cukup terhadap teks.';
        }

        if (! ThemeStyleService::passesContrast($neutral, $surface, $minimumRatio)) {
            return 'Kombinasi warna latar dan teks tidak memenuhi standar kontras.';
        }

        return null;
    }
}
