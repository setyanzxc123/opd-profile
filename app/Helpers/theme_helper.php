<?php

use App\Services\ThemeStyleService;

if (! function_exists('theme_merge_settings')) {
    function theme_merge_settings($raw): array
    {
        return ThemeStyleService::mergeSettings($raw);
    }
}

if (! function_exists('theme_public_variables')) {
    function theme_public_variables($profile): array
    {
        $settings = [];
        if (is_array($profile) && array_key_exists('theme_settings', $profile)) {
            $settings = $profile['theme_settings'];
        } elseif (is_array($profile)) {
            $settings = $profile;
        }

        $merged = ThemeStyleService::mergeSettings($settings);

        return ThemeStyleService::compilePublicVariables($merged);
    }
}

if (! function_exists('theme_admin_variables')) {
    function theme_admin_variables($profile): array
    {
        $settings = [];
        if (is_array($profile) && array_key_exists('theme_settings', $profile)) {
            $settings = $profile['theme_settings'];
        } elseif (is_array($profile)) {
            $settings = $profile;
        }

        $merged = ThemeStyleService::mergeSettings($settings);

        return ThemeStyleService::compileAdminVariables($merged);
    }
}

if (! function_exists('theme_render_style')) {
    function theme_render_style(array $variables, string $selector = ':root'): string
    {
        $style = ThemeStyleService::renderCssVariables($variables, $selector);

        return $style === '' ? '' : '<style>' . $style . '</style>';
    }
}

