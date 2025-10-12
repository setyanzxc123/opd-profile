<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OpdProfileModel;
use App\Services\ProfileLogoService;

class Profile extends BaseController
{

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
            ]);
            $profile = $model->orderBy('id', 'ASC')->first();
        }

        return view('admin/profile/edit', [
            'title'      => 'Profil',
            'profile'    => $profile,
            'validation' => \Config\Services::validation(),
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

