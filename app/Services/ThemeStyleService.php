<?php

namespace App\Services;

class ThemeStyleService
{
    public const DEFAULT_THEME = [
        'primary' => '#05A5A8',
        'accent'  => '#03C3EC',
        'neutral' => '#22303E',
        'surface' => '#F5F5F9',
    ];

    public static function mergeSettings($raw): array
    {
        $defaults = self::DEFAULT_THEME;
        $decoded  = self::decode($raw);

        foreach ($defaults as $key => $fallback) {
            if (isset($decoded[$key])) {
                $normalized = self::normalizeHex($decoded[$key], $fallback);
                if ($normalized !== null) {
                    $defaults[$key] = $normalized;
                }
            }
        }

        return $defaults;
    }

    public static function compilePublicVariables(array $theme): array
    {
        $primary = self::normalizeHex($theme['primary'] ?? null, self::DEFAULT_THEME['primary']);
        $accent  = self::normalizeHex($theme['accent'] ?? null, self::DEFAULT_THEME['accent']);
        $neutral = self::normalizeHex($theme['neutral'] ?? null, self::DEFAULT_THEME['neutral']);
        $surface = self::normalizeHex($theme['surface'] ?? null, self::DEFAULT_THEME['surface']);

        $primaryRgb = self::toRgbString($primary);
        $neutralRgb = self::toRgbString($neutral);

        $neutral700 = self::lighten($neutral, 0.18);
        $neutral500 = self::lighten($neutral, 0.35);
        $neutral200 = self::lighten($neutral, 0.82);

        $surfaceNeutral = self::mix($surface, '#FFFFFF', 0.06);
        $surfaceCool    = self::mix($surface, '#FFFFFF', 0.09);
        $surfaceWarm    = self::mix($surface, '#FFFFFF', 0.1);

        $navbarBg       = self::mix($surface, $primary, 0.14);
        $navbarBorder   = self::mix('#FFFFFF', $primary, 0.25);
        $navbarShadow   = '0 0.75rem 1.75rem rgba(' . $primaryRgb . ', 0.18)';
        $navbarLink     = self::mix($neutral700, $primary, 0.12);
        $navbarHover    = self::mix($primary, '#FFFFFF', 0.18);
        $navbarContrast = self::contrastColor($primary);
        $navbarContrastHover = $navbarContrast === '#FFFFFF'
            ? self::mix('#FFFFFF', $primary, 0.2)
            : self::mix('#000000', $primary, 0.2);

        return [
            '--public-primary'         => $primary,
            '--public-primary-dark'    => self::darken($primary, 0.22),
            '--public-primary-rgb'     => $primaryRgb,
            '--public-primary-soft'    => 'rgba(' . $primaryRgb . ', 0.12)',
            '--public-accent'          => $accent,
            '--public-neutral-900'     => $neutral,
            '--public-neutral-700'     => $neutral700,
            '--public-neutral-500'     => $neutral500,
            '--public-neutral-200'     => $neutral200,
            '--public-neutral-100'     => '#FFFFFF',
            '--public-neutral-rgb'     => $neutralRgb,
            '--surface-base'           => $surface,
            '--surface-neutral'        => $surfaceNeutral,
            '--surface-cool'           => $surfaceCool,
            '--surface-warm'           => $surfaceWarm,
            '--surface-card'           => '#FFFFFF',
            '--surface-border'         => 'rgba(' . $neutralRgb . ', 0.08)',
            '--surface-border-strong'  => 'rgba(' . $neutralRgb . ', 0.16)',
            '--surface-shadow'         => '0 0.1875rem 0.5rem rgba(' . $neutralRgb . ', 0.08)',
            '--surface-shadow-sm'      => '0 0.125rem 0.375rem rgba(' . $neutralRgb . ', 0.07)',
            '--border-soft'            => 'color-mix(in sRGB, var(--public-primary) 18%, transparent)',
            '--public-navbar-bg'       => $navbarBg,
            '--public-navbar-border'   => $navbarBorder,
            '--public-navbar-shadow'   => $navbarShadow,
            '--public-navbar-link'     => $navbarLink,
            '--public-navbar-link-hover' => $navbarHover,
            '--public-navbar-link-active' => $primary,
            '--public-navbar-link-contrast' => $navbarContrast,
            '--public-navbar-link-contrast-hover' => $navbarContrastHover,
        ];
    }

    public static function compileAdminVariables(array $theme): array
    {
        $primary = self::normalizeHex($theme['primary'] ?? null, self::DEFAULT_THEME['primary']);
        $surface = self::normalizeHex($theme['surface'] ?? null, self::DEFAULT_THEME['surface']);
        $neutral = self::normalizeHex($theme['neutral'] ?? null, self::DEFAULT_THEME['neutral']);

        $primaryRgb = self::toRgbString($primary);
        $primaryBg  = self::mix($primary, '#FFFFFF', 0.85);
        $primaryBorder = self::mix($primary, '#FFFFFF', 0.65);
        $linkHover = self::darken($primary, 0.16);
        $primaryHover = self::darken($primary, 0.12);
        $primaryActive = self::darken($primary, 0.22);
        $surfacePaper = self::mix($surface, '#FFFFFF', 0.95);
        $neutralRgb = self::toRgbString($neutral);

        return [
            '--bs-primary'                 => $primary,
            '--bs-primary-rgb'             => $primaryRgb,
            '--bs-primary-text-emphasis'   => self::darken($primary, 0.45),
            '--bs-primary-bg-subtle'       => $primaryBg,
            '--bs-primary-border-subtle'   => $primaryBorder,
            '--bs-link-color'              => $primary,
            '--bs-link-hover-color'        => $linkHover,
            '--bs-card-border-radius'      => '16px',
            '--bs-card-inner-border-radius'=> '16px',
            '--bs-body-bg'                 => $surface,
            '--bs-base-color'              => $neutral,
            '--bs-paper-bg'                => $surfacePaper,
            '--theme-admin-btn-hover'      => $primaryHover,
            '--theme-admin-btn-active'     => $primaryActive,
            '--theme-admin-outline-muted'  => 'rgba(' . $neutralRgb . ', 0.22)',
        ];
    }

    public static function renderCssVariables(array $variables, string $selector = ':root'): string
    {
        if ($variables === []) {
            return '';
        }

        $lines = [];
        foreach ($variables as $name => $value) {
            $trimmedValue = trim((string) $value);
            if ($trimmedValue === '') {
                continue;
            }

            $lines[] = $name . ': ' . $trimmedValue . ';';
        }

        if ($lines === []) {
            return '';
        }

        return sprintf('%s { %s }', $selector, implode(' ', $lines));
    }

    private static function decode($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (! is_string($raw)) {
            return [];
        }

        $trimmed = trim($raw);
        if ($trimmed === '') {
            return [];
        }

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $throwable) {
            log_message('debug', 'Failed to decode theme settings: {error}', ['error' => $throwable->getMessage()]);

            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    private static function normalizeHex($value, string $fallback): ?string
    {
        if ($value === null) {
            return strtoupper($fallback);
        }

        if (is_array($value)) {
            return strtoupper($fallback);
        }

        $candidate = trim((string) $value);
        if ($candidate === '') {
            return strtoupper($fallback);
        }

        if ($candidate[0] !== '#') {
            $candidate = '#' . $candidate;
        }

        if (preg_match('/^#([0-9A-Fa-f]{3})$/', $candidate, $matches)) {
            $candidate = sprintf('#%1$s%1$s%2$s%2$s%3$s%3$s', $matches[1][0], $matches[1][1], $matches[1][2]);
        }

        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $candidate)) {
            return strtoupper($fallback);
        }

        return strtoupper($candidate);
    }

    private static function toRgbString(string $hex): string
    {
        $normalized = self::normalizeHex($hex, '#000000');
        $red   = hexdec(substr($normalized, 1, 2));
        $green = hexdec(substr($normalized, 3, 2));
        $blue  = hexdec(substr($normalized, 5, 2));

        return $red . ', ' . $green . ', ' . $blue;
    }

    private static function lighten(string $hex, float $ratio): string
    {
        $normalized = self::normalizeHex($hex, '#FFFFFF');
        $weight = min(max($ratio, 0.0), 1.0);

        $red   = hexdec(substr($normalized, 1, 2));
        $green = hexdec(substr($normalized, 3, 2));
        $blue  = hexdec(substr($normalized, 5, 2));

        $newRed   = (int) round($red + (255 - $red) * $weight);
        $newGreen = (int) round($green + (255 - $green) * $weight);
        $newBlue  = (int) round($blue + (255 - $blue) * $weight);

        return sprintf('#%02X%02X%02X', $newRed, $newGreen, $newBlue);
    }

    private static function darken(string $hex, float $ratio): string
    {
        $normalized = self::normalizeHex($hex, '#000000');
        $weight = min(max($ratio, 0.0), 1.0);

        $red   = hexdec(substr($normalized, 1, 2));
        $green = hexdec(substr($normalized, 3, 2));
        $blue  = hexdec(substr($normalized, 5, 2));

        $newRed   = (int) round($red * (1 - $weight));
        $newGreen = (int) round($green * (1 - $weight));
        $newBlue  = (int) round($blue * (1 - $weight));

        return sprintf('#%02X%02X%02X', $newRed, $newGreen, $newBlue);
    }

    private static function mix(string $hex, string $mixTo, float $ratio): string
    {
        $primary   = self::normalizeHex($hex, '#000000');
        $secondary = self::normalizeHex($mixTo, '#FFFFFF');
        $weight    = min(max($ratio, 0.0), 1.0);
        $inverse   = 1 - $weight;

        $red   = (int) round(hexdec(substr($primary, 1, 2)) * $inverse + hexdec(substr($secondary, 1, 2)) * $weight);
        $green = (int) round(hexdec(substr($primary, 3, 2)) * $inverse + hexdec(substr($secondary, 3, 2)) * $weight);
        $blue  = (int) round(hexdec(substr($primary, 5, 2)) * $inverse + hexdec(substr($secondary, 5, 2)) * $weight);

        return sprintf('#%02X%02X%02X', $red, $green, $blue);
    }

    private static function contrastColor(string $hex): string
    {
        $normalized = self::normalizeHex($hex, '#000000');
        $red   = hexdec(substr($normalized, 1, 2));
        $green = hexdec(substr($normalized, 3, 2));
        $blue  = hexdec(substr($normalized, 5, 2));

        $luminance = (0.299 * $red + 0.587 * $green + 0.114 * $blue) / 255;

        return $luminance > 0.58 ? '#111111' : '#FFFFFF';
    }
}
