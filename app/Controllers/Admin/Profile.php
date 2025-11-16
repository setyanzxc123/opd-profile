<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OpdProfileModel;
use App\Services\ProfileAdminService;
use App\Services\ProfileLogoService;
use App\Services\ThemeStyleService;

class Profile extends BaseController
{
    private const DEFAULT_THEME_SETTINGS = ThemeStyleService::DEFAULT_THEME;

    private ProfileAdminService $profileService;

    public function __construct()
    {
        $this->profileService = service('profileAdmin');
    }

    // Purpose: display edit form for the single OPD profile
    public function index()
    {
        return $this->edit();
    }

    public function edit()
    {
        $model = new OpdProfileModel();
        // we keep a single-row profile; fetch first or create a placeholder
        $profile = $model->orderBy('id', 'ASC')->first();
        if (! $profile) {
            $defaultPreset = $this->profileService->getDefaultThemePresetSlug();
            $defaultTheme  = $this->profileService->buildThemeFromPreset($defaultPreset, self::DEFAULT_THEME_SETTINGS);

            $model->insert([
                'name'        => '',
                'description' => null,
                'vision'      => null,
                'mission'     => null,
                'address'     => null,
                'latitude'    => null,
                'longitude'   => null,
                'map_zoom'    => null,
                'map_display' => 0,
                'phone'       => null,
                'email'       => null,
                'logo_public_path' => null,
                'logo_admin_path'  => null,
                'theme_settings'   => json_encode($defaultTheme),
            ]);
            $profile = $model->orderBy('id', 'ASC')->first();
        }

        $themeSettings = $this->profileService->mergeThemeSettings($profile['theme_settings'] ?? null);
        $themePresets   = $this->profileService->getThemePresets();
        $detectedPreset = $this->profileService->detectThemePresetSlug($themeSettings);
        $defaultPreset  = $this->profileService->getDefaultThemePresetSlug();
        $activePreset   = $detectedPreset ?? $defaultPreset;
        $activeThemeMode = $detectedPreset !== null
            ? ProfileAdminService::THEME_MODE_PRESET
            : ProfileAdminService::THEME_MODE_CUSTOM;
        $customThemeDefaults = [
            'primary' => $themeSettings['primary'] ?? self::DEFAULT_THEME_SETTINGS['primary'],
            'surface' => $themeSettings['surface'] ?? self::DEFAULT_THEME_SETTINGS['surface'],
        ];

        return view('admin/profile/edit', [
            'title'             => 'Profil',
            'profile'           => $profile,
            'themeSettings'     => $themeSettings,
            'themeDefaults'     => self::DEFAULT_THEME_SETTINGS,
            'themePresets'      => $themePresets,
            'activeThemePreset' => $activePreset,
            'activeThemeMode'   => $activeThemeMode,
            'themeCustomDefaults' => $customThemeDefaults,
            'validation'        => \Config\Services::validation(),
        ]);
    }

    public function update()
    {
        $themePresets = $this->profileService->getThemePresets();
        $presetKeys   = array_keys($themePresets);
        $themeModeInput = (string) $this->request->getPost('theme_mode');
        $themeMode = $this->profileService->normalizeThemeMode($themeModeInput);
        $modeOptions = $this->profileService->getThemeModeOptions();
        $modeOptionsRule = implode(',', $modeOptions);
        $hexRule = 'regex_match[/^#?(?:[0-9A-Fa-f]{3}){1,2}$/]';
        $presetRule = empty($presetKeys) ? 'permit_empty' : 'permit_empty|in_list[' . implode(',', $presetKeys) . ']';

        $rules = [
            'name'        => 'required|min_length[3]|max_length[150]',
            'email'       => 'permit_empty|valid_email|max_length[100]',
            'phone'       => 'permit_empty|max_length[20]',
            'description' => 'permit_empty',
            'vision'      => 'permit_empty',
            'mission'     => 'permit_empty',
            'address'     => 'permit_empty',
            'latitude'    => 'permit_empty|numeric|greater_than_equal_to[-90]|less_than_equal_to[90]',
            'longitude'   => 'permit_empty|numeric|greater_than_equal_to[-180]|less_than_equal_to[180]',
            'map_zoom'    => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[20]',
            'map_display' => 'permit_empty|in_list[0,1]',
            'logo_public' => 'permit_empty|max_size[logo_public,3072]|is_image[logo_public]|ext_in[logo_public,jpg,jpeg,png,webp,gif]|mime_in[logo_public,image/jpeg,image/jpg,image/png,image/webp,image/gif]',
            'theme_mode'   => 'required|in_list[' . $modeOptionsRule . ']',
            'theme_preset' => $presetRule,
            'theme_primary_color' => 'permit_empty|' . $hexRule,
            'theme_surface_color' => 'permit_empty|' . $hexRule,
        ];

        if ($themeMode === ProfileAdminService::THEME_MODE_CUSTOM) {
            $rules['theme_primary_color'] = 'required|' . $hexRule;
            $rules['theme_surface_color'] = 'required|' . $hexRule;
        } elseif (! empty($presetKeys)) {
            $rules['theme_preset'] = 'required|in_list[' . implode(',', $presetKeys) . ']';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali data yang diisi.');
        }

        helper(['activity', 'content']);

        $model      = new OpdProfileModel();
        $id         = (int) $this->request->getPost('id');
        $isUpdate   = $id > 0;
        $logoHelper = new ProfileLogoService();

        $currentProfile = $isUpdate ? $model->find($id) : null;
        $currentProfile = is_array($currentProfile) ? $currentProfile : [];

        $publicMeta = $this->profileService->decodeLogoMeta($this->request->getPost('logo_public_meta'));

        $publicPathBefore = $currentProfile['logo_public_path'] ?? null;
        $adminPathBefore  = $currentProfile['logo_admin_path'] ?? null;

        $publicPathAfter = $publicPathBefore;

        $newPublicUploadedPath = null;

        $data = [
            'name'        => sanitize_plain_text($this->request->getPost('name')),
            'description' => sanitize_rich_text($this->request->getPost('description')),
            'vision'      => sanitize_rich_text($this->request->getPost('vision')),
            'mission'     => sanitize_rich_text($this->request->getPost('mission')),
            'address'     => sanitize_plain_text($this->request->getPost('address')),
            'latitude'    => $this->profileService->normalizeCoordinate($this->request->getPost('latitude')),
            'longitude'   => $this->profileService->normalizeCoordinate($this->request->getPost('longitude')),
            'map_zoom'    => $this->profileService->normalizeZoom($this->request->getPost('map_zoom')),
            'map_display' => $this->profileService->normalizeDisplayFlag($this->request->getPost('map_display')),
            'phone'       => sanitize_plain_text($this->request->getPost('phone')),
            'email'       => sanitize_plain_text($this->request->getPost('email')),
        ];

        if ($themeMode === ProfileAdminService::THEME_MODE_CUSTOM) {
            $primaryColor = $this->profileService->normalizeHexColor($this->request->getPost('theme_primary_color'));
            $surfaceColor = $this->profileService->normalizeHexColor($this->request->getPost('theme_surface_color'));
            $finalTheme = $this->profileService->buildCustomTheme($primaryColor, $surfaceColor, self::DEFAULT_THEME_SETTINGS);
        } else {
            $presetSlug = (string) $this->request->getPost('theme_preset');
            $finalTheme = $this->profileService->buildThemeFromPreset($presetSlug, self::DEFAULT_THEME_SETTINGS);
        }

        $contrastError = $this->profileService->validateThemeAccessibility(
            $finalTheme,
            $this->profileService->minimumContrastRatio()
        );
        if ($contrastError !== null) {
            return redirect()->back()
                ->withInput()
                ->with('error', $contrastError);
        }

        try {
            $data['theme_settings'] = json_encode($finalTheme, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            log_message('error', 'Failed to encode theme settings: {error}', ['error' => $exception->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan pengaturan tema.');
        }

        $logoPublicFile = $this->request->getFile('logo_public');
        if ($logoPublicFile && $logoPublicFile->isValid() && ! $logoPublicFile->hasMoved()) {
            try {
                $newPublicUploadedPath = $logoHelper->store(
                    $logoPublicFile,
                    ProfileLogoService::TYPE_PUBLIC,
                    [
                        'maxDimension' => isset($publicMeta['maxDimension']) ? (int) $publicMeta['maxDimension'] : null,
                        'meta'         => $publicMeta,
                    ]
                );
            } catch (\RuntimeException $exception) {
                return redirect()->back()->withInput()->with('error', $exception->getMessage());
            }

            $publicPathAfter = $newPublicUploadedPath;
        }

        $removePublic = $this->profileService->shouldRemove($this->request->getPost('remove_logo_public'));

        if ($removePublic) {
            $publicPathAfter = null;
        }

        $data['logo_public_path'] = $publicPathAfter;
        $data['logo_admin_path']  = $publicPathAfter;

        if ($isUpdate) {
            $model->update($id, $data);
        } else {
            $model->insert($data);
        }

        $finalPublicPath = $publicPathAfter;
        $finalAdminPath  = $publicPathAfter;

        $pathsToDelete = [];

        if ($newPublicUploadedPath && $newPublicUploadedPath !== $finalPublicPath) {
            $pathsToDelete[] = $newPublicUploadedPath;
        }

        if ($publicPathBefore && $publicPathBefore !== $finalPublicPath) {
            $pathsToDelete[] = $publicPathBefore;
        }

        if ($adminPathBefore && $adminPathBefore !== $finalPublicPath) {
            $pathsToDelete[] = $adminPathBefore;
        }

        $pathsToDelete = array_unique(array_filter($pathsToDelete));

        foreach ($pathsToDelete as $unusedPath) {
            $logoHelper->delete($unusedPath);
        }

        $message = $isUpdate ? 'Memperbarui Profil' : 'Membuat Profil';
        log_activity('profile.save', $message);
        cache()->delete('public_profile_latest');

        return redirect()->to(site_url('admin/profile'))
            ->with('message', 'Profil berhasil disimpan.');
    }
}
