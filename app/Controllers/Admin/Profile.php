<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OpdProfileModel;
use App\Services\ProfileLogoService;
use App\Services\ThemeStyleService;
use CodeIgniter\HTTP\ResponseInterface;

class Profile extends BaseController
{
    private const DEFAULT_THEME_SETTINGS = ThemeStyleService::DEFAULT_THEME;

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
            'themeSettings'  => $this->mergeThemeSettings($profile['theme_settings'] ?? null),
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

        $publicMeta = $this->decodeLogoMeta($this->request->getPost('logo_public_meta'));

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
            'latitude'    => $this->normalizeCoordinate($this->request->getPost('latitude')),
            'longitude'   => $this->normalizeCoordinate($this->request->getPost('longitude')),
            'map_zoom'    => $this->normalizeZoom($this->request->getPost('map_zoom')),
            'map_display' => $this->normalizeDisplayFlag($this->request->getPost('map_display')),
            'phone'       => sanitize_plain_text($this->request->getPost('phone')),
            'email'       => sanitize_plain_text($this->request->getPost('email')),
        ];

        $currentTheme = $this->mergeThemeSettings($currentProfile['theme_settings'] ?? null);
        $incomingTheme = [
            'primary' => $this->normalizeHexColor($this->request->getPost('theme_primary_color')),
            'surface' => $this->normalizeHexColor($this->request->getPost('theme_surface_color')),
            'neutral' => $this->normalizeHexColor($this->request->getPost('theme_neutral_color')),
        ];

        $themeReset = $this->isAffirmative($this->request->getPost('theme_reset'));
        $finalTheme = $themeReset ? self::DEFAULT_THEME_SETTINGS : $currentTheme;

        if (! $themeReset) {
            foreach ($incomingTheme as $key => $value) {
                if ($value !== null) {
                    $finalTheme[$key] = $value;
                }
            }
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

        $removePublic = $this->shouldRemove($this->request->getPost('remove_logo_public'));

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

        $lowerQuery = function_exists('mb_strtolower') ? mb_strtolower($query, 'UTF-8') : strtolower($query);
        $cacheKey   = 'nominatim_search_' . md5($lowerQuery);
        $cacheStore = cache();
        $cached     = $cacheStore ? $cacheStore->get($cacheKey) : null;

        if (is_array($cached)) {
            return $this->response->setJSON(['data' => $cached]);
        }

        $contactEmail = trim((string) env('nominatim.contactEmail', ''));
        if ($contactEmail === '') {
            $emailConfig = null;
            try {
                $emailConfig = config('Email');
            } catch (\Throwable $ignore) {
                $emailConfig = null;
            }

            if ($emailConfig && property_exists($emailConfig, 'fromEmail')) {
                $candidate = trim((string) $emailConfig->fromEmail);
                if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                    $contactEmail = $candidate;
                }
            }
        }

        $userAgent = 'OPDProfileCMS/1.0 (+' . base_url();
        if ($contactEmail !== '' && filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $userAgent .= '; ' . $contactEmail;
        }
        $userAgent .= ')';

        $client = \Config\Services::curlrequest([
            'baseURI' => 'https://nominatim.openstreetmap.org/',
            'timeout'  => 5,
        ]);

        try {
            $response = $client->get('search', [
                'query' => [
                    'format'         => 'jsonv2',
                    'q'              => $query,
                    'addressdetails' => 1,
                    'limit'          => 5,
                ],
                'headers' => [
                    'User-Agent'      => $userAgent,
                    'Accept-Language' => 'id,en;q=0.8',
                ],
            ]);
        } catch (\Throwable $throwable) {
            log_message('error', 'Pencarian Nominatim gagal: {error}', ['error' => $throwable->getMessage()]);

            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_GATEWAY)
                ->setJSON(['message' => 'Tidak dapat terhubung ke layanan lokasi. Coba lagi nanti.']);
        }

        if ($response->getStatusCode() !== 200) {
            log_message('warning', 'Nominatim mengembalikan status {status}.', ['status' => $response->getStatusCode()]);

            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_GATEWAY)
                ->setJSON(['message' => 'Layanan lokasi sedang tidak tersedia.']);
        }

        try {
            $decoded = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $throwable) {
            log_message('error', 'Gagal mengurai hasil Nominatim: {error}', ['error' => $throwable->getMessage()]);

            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_GATEWAY)
                ->setJSON(['message' => 'Data lokasi tidak dapat diproses.']);
        }

        $results = [];

        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                $lat = isset($item['lat']) ? (float) $item['lat'] : null;
                $lng = isset($item['lon']) ? (float) $item['lon'] : (isset($item['lng']) ? (float) $item['lng'] : null);

                if ($lat === null || $lng === null) {
                    continue;
                }

                $label = trim((string) ($item['display_name'] ?? ''));
                $boundingBox = [];
                if (isset($item['boundingbox']) && is_array($item['boundingbox']) && count($item['boundingbox']) === 4) {
                    $boundingBox = array_values($item['boundingbox']);
                }

                $results[] = [
                    'label'       => $label !== '' ? $label : sprintf('Lat %.5f, Lng %.5f', $lat, $lng),
                    'lat'         => $lat,
                    'lng'         => $lng,
                    'boundingBox' => $boundingBox,
                ];
            }
        }

        if ($cacheStore) {
            $cacheStore->save($cacheKey, $results, 600);
        }

        return $this->response->setJSON(['data' => $results]);
    }

    private function mergeThemeSettings($raw): array
    {
        return ThemeStyleService::mergeSettings($raw);
    }

    private function normalizeHexColor($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
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

    private function isAffirmative($value): bool
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

    private function shouldRemove($value): bool
    {
        return $this->isAffirmative($value);
    }

    private function decodeLogoMeta($value): array
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

    private function normalizeCoordinate($value): ?float
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

    private function normalizeZoom($value): ?int
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

    private function normalizeDisplayFlag($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (int) ((string) $value === '1' ? 1 : 0);
    }
}

