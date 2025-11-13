<?php

use App\Models\OpdProfileModel;

if (! function_exists('admin_theme_context')) {
    /**
     * Mengambil informasi tema/layout admin sekali per-request.
     */
    function admin_theme_context(): array
    {
        static $cache;
        if ($cache !== null) {
            return $cache;
        }

        helper(['theme', 'url']);

        $profile = cache('public_profile_latest');
        if (! is_array($profile)) {
            try {
                $profile = model(OpdProfileModel::class)
                    ->orderBy('id', 'desc')
                    ->first() ?? [];
            } catch (\Throwable $throwable) {
                log_message('debug', 'Failed to fetch profile for admin theme: {error}', ['error' => $throwable->getMessage()]);
                $profile = [];
            }
        }

        $profile = is_array($profile) ? $profile : [];
        $variables = $profile !== [] ? theme_admin_variables($profile) : [];
        $siteName = trim((string) ($profile['name'] ?? ''));
        $logoPath = trim((string) ($profile['logo_admin_path'] ?? ($profile['logo_public_path'] ?? '')));
        $logoUrl = $logoPath !== '' ? base_url($logoPath) : null;
        $faviconUrl = $logoUrl ?? base_url('favicon.ico');

        return $cache = [
            'profile'    => $profile,
            'variables'  => $variables,
            'siteName'   => $siteName,
            'logoUrl'    => $logoUrl,
            'faviconUrl' => $faviconUrl,
        ];
    }
}

if (! function_exists('admin_user_identity')) {
    /**
     * Data ringkas untuk ditampilkan di avatar/header.
     */
    function admin_user_identity(): array
    {
        $displayName = trim((string) (session('admin_display_name') ?? session('name') ?? session('username') ?? 'Pengguna'));
        $initial     = trim((string) (session('admin_initial') ?? ''));
        $roleLabel   = trim((string) (session('admin_role_label') ?? ''));

        if ($initial === '') {
            $initial = strtoupper(substr($displayName, 0, 1));
            if (function_exists('mb_substr')) {
                $initialCandidate = mb_substr($displayName, 0, 1, 'UTF-8');
                if ($initialCandidate !== false && $initialCandidate !== '') {
                    $initial = mb_strtoupper($initialCandidate, 'UTF-8');
                }
            }
        }

        if ($initial === '') {
            $initial = 'P';
        }

        if ($roleLabel === '') {
            $role = trim((string) (session('role') ?? ''));
            $roleLabel = $role !== '' ? ucfirst(strtolower($role)) : '-';
        }

        return [
            'displayName' => $displayName,
            'initial'     => $initial,
            'roleLabel'   => $roleLabel,
        ];
    }
}

if (! function_exists('admin_menu_access')) {
    /**
     * Mengambil daftar section yang boleh diakses beserta flag full access.
     */
    function admin_menu_access(): array
    {
        $sections = session('admin_allowed_sections');
        if (! is_array($sections)) {
            $sections = [];
        }

        $normalized = array_values(array_unique(array_map(
            static fn ($item) => strtolower((string) $item),
            $sections
        )));

        $hasFullAccess = session('admin_has_full_access');
        if (! is_bool($hasFullAccess)) {
            $hasFullAccess = in_array('*', $normalized, true);
        }

        return [
            'sections'      => $normalized,
            'hasFullAccess' => $hasFullAccess,
        ];
    }
}

if (! function_exists('admin_can_access')) {
    /**
     * Helper untuk memeriksa akses menu tertentu.
     */
    function admin_can_access(string $target): bool
    {
        $access = admin_menu_access();
        if ($access['hasFullAccess']) {
            return true;
        }

        return in_array(strtolower($target), $access['sections'], true);
    }
}
