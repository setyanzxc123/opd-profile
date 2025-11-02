<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OpdProfileModel;
use App\Services\ProfileAdminService;
use App\Services\ProfileLocationService;
use App\Services\ProfileLogoService;
use App\Services\ThemeStyleService;
use CodeIgniter\HTTP\ResponseInterface;

class Profile extends BaseController
{
    private const DEFAULT_THEME_SETTINGS = ThemeStyleService::DEFAULT_THEME;

    private ProfileAdminService $profileService;
    private ProfileLocationService $locationService;

    public function __construct()
    {
        $this->profileService  = service('profileAdmin');
        $this->locationService = service('profileLocation');
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
                'theme_settings'   => json_encode(self::DEFAULT_THEME_SETTINGS),
            ]);
            $profile = $model->orderBy('id', 'ASC')->first();
        }

        return view('admin/profile/edit', [
            'title'          => 'Profil',
            'profile'        => $profile,
            'themeSettings'  => $this->profileService->mergeThemeSettings($profile['theme_settings'] ?? null),
            'themeDefaults'  => self::DEFAULT_THEME_SETTINGS,
            'validation'     => \Config\Services::validation(),
        ]);
    }

    public function update()
    {
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
            'theme_primary_color' => 'permit_empty|regex_match[/^#?(?:[0-9A-Fa-f]{3}){1,2}$/]',
            'theme_surface_color' => 'permit_empty|regex_match[/^#?(?:[0-9A-Fa-f]{3}){1,2}$/]',
            'theme_neutral_color' => 'permit_empty|regex_match[/^#?(?:[0-9A-Fa-f]{3}){1,2}$/]',
        ];

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

        $currentTheme = $this->profileService->mergeThemeSettings($currentProfile['theme_settings'] ?? null);
        $incomingTheme = [
            'primary' => $this->profileService->normalizeHexColor($this->request->getPost('theme_primary_color')),
            'surface' => $this->profileService->normalizeHexColor($this->request->getPost('theme_surface_color')),
            'neutral' => $this->profileService->normalizeHexColor($this->request->getPost('theme_neutral_color')),
        ];

        $themeReset = $this->profileService->isAffirmative($this->request->getPost('theme_reset'));
        $finalTheme = $this->profileService->resolveTheme($currentTheme, $incomingTheme, $themeReset, self::DEFAULT_THEME_SETTINGS);

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
            if (! $logoHelper->isAllowedMime($logoPublicFile)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file logo tidak diizinkan.');
            }

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

    public function searchLocation(): ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED)
                ->setJSON(['message' => 'Metode tidak diizinkan.']);
        }

        $query = trim((string) $this->request->getGet('q'));
        $length = function_exists('mb_strlen') ? mb_strlen($query) : strlen($query);

        if ($length < 3) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'Masukkan minimal 3 karakter untuk pencarian.']);
        }

        try {
            $results = $this->locationService->search($query);
        } catch (\RuntimeException $exception) {
            $status = $exception->getCode();
            if (! is_int($status) || $status < 400 || $status > 599) {
                $status = ResponseInterface::HTTP_BAD_GATEWAY;
            }

            return $this->response->setStatusCode($status)
                ->setJSON(['message' => $exception->getMessage()]);
        }

        return $this->response->setJSON(['data' => $results]);
    }
}

