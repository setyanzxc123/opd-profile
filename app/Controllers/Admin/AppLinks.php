<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AppLinkModel;
use App\Models\OpdProfileModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * App Links Controller
 * 
 * Mengelola CRUD tautan aplikasi OPD terkait
 */
class AppLinks extends BaseController
{
    protected AppLinkModel $model;

    public function __construct()
    {
        $this->model = new AppLinkModel();
    }

    /**
     * Display list of app links
     */
    public function index(): string
    {
        $links = $this->model->getAllForAdmin();
        
        // Get show_app_links setting from profile
        $profileModel = new OpdProfileModel();
        $profile = $profileModel->orderBy('id', 'ASC')->first();
        $showAppLinks = ($profile['show_app_links'] ?? '1') == '1';

        return view('admin/app_links/index', [
            'title'        => 'Tautan Aplikasi',
            'section'      => 'app-links',
            'links'        => $links,
            'showAppLinks' => $showAppLinks,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): string
    {
        return view('admin/app_links/form', [
            'title'      => 'Tambah Tautan Aplikasi',
            'section'    => 'app-links',
            'link'       => null,
            'validation' => \Config\Services::validation(),
        ]);
    }

    /**
     * Store new app link
     */
    public function store(): RedirectResponse
    {
        helper(['content', 'activity']);

        $rules = [
            'name'        => 'required|min_length[2]|max_length[255]',
            'url'         => 'required|valid_url_strict|max_length[500]',
            'description' => 'permit_empty|max_length[500]',
            'logo'        => 'permit_empty|max_size[logo,2048]|is_image[logo]|ext_in[logo,jpg,jpeg,png,webp,gif,svg]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Sanitize inputs
        $data = [
            'name'        => sanitize_plain_text($this->request->getPost('name')),
            'description' => sanitize_plain_text($this->request->getPost('description')),
            'url'         => filter_var($this->request->getPost('url'), FILTER_SANITIZE_URL),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
            'sort_order'  => $this->model->getNextSortOrder(),
        ];

        // Handle logo upload with MIME validation
        $logoFile = $this->request->getFile('logo');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
            $mime = strtolower($logoFile->getMimeType());
            
            if (!in_array($mime, $allowedMimes, true)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Jenis file logo tidak diizinkan.');
            }

            $newName = $logoFile->getRandomName();
            $uploadPath = 'uploads/app_links';
            
            // Create directory if not exists
            $fullPath = FCPATH . $uploadPath;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $logoFile->move($fullPath, $newName);
            $data['logo_path'] = $uploadPath . '/' . $newName;
        }

        if ($this->model->insert($data)) {
            log_activity('app_link.create', 'Menambah tautan aplikasi: ' . $data['name']);
            return redirect()
                ->to('/admin/app-links')
                ->with('success', 'Tautan aplikasi berhasil ditambahkan.');
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Gagal menyimpan data.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $link = $this->model->find($id);

        if (!$link) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/app_links/form', [
            'title'      => 'Edit Tautan Aplikasi',
            'section'    => 'app-links',
            'link'       => $link,
            'validation' => \Config\Services::validation(),
        ]);
    }

    /**
     * Update existing app link
     */
    public function update(int $id): RedirectResponse
    {
        helper(['content', 'activity']);

        $link = $this->model->find($id);

        if (!$link) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'name'        => 'required|min_length[2]|max_length[255]',
            'url'         => 'required|valid_url_strict|max_length[500]',
            'description' => 'permit_empty|max_length[500]',
            'logo'        => 'permit_empty|max_size[logo,2048]|is_image[logo]|ext_in[logo,jpg,jpeg,png,webp,gif,svg]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Sanitize inputs
        $data = [
            'name'        => sanitize_plain_text($this->request->getPost('name')),
            'description' => sanitize_plain_text($this->request->getPost('description')),
            'url'         => filter_var($this->request->getPost('url'), FILTER_SANITIZE_URL),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Handle logo upload with MIME validation
        $logoFile = $this->request->getFile('logo');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
            $mime = strtolower($logoFile->getMimeType());
            
            if (!in_array($mime, $allowedMimes, true)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Jenis file logo tidak diizinkan.');
            }

            // Delete old logo safely
            if (!empty($link['logo_path'])) {
                $oldPath = FCPATH . ltrim($link['logo_path'], '/');
                $uploadsRoot = realpath(FCPATH . 'uploads');
                $realOldPath = realpath($oldPath);
                
                // Security: only delete if within uploads directory
                if ($uploadsRoot && $realOldPath && strpos($realOldPath, $uploadsRoot) === 0 && is_file($realOldPath)) {
                    @unlink($realOldPath);
                }
            }

            $newName = $logoFile->getRandomName();
            $uploadPath = 'uploads/app_links';
            
            $fullPath = FCPATH . $uploadPath;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $logoFile->move($fullPath, $newName);
            $data['logo_path'] = $uploadPath . '/' . $newName;
        }

        // Handle logo removal with path traversal protection
        if ($this->request->getPost('remove_logo') && !empty($link['logo_path'])) {
            $oldPath = FCPATH . ltrim($link['logo_path'], '/');
            $uploadsRoot = realpath(FCPATH . 'uploads');
            $realOldPath = realpath($oldPath);
            
            // Security: only delete if within uploads directory
            if ($uploadsRoot && $realOldPath && strpos($realOldPath, $uploadsRoot) === 0 && is_file($realOldPath)) {
                @unlink($realOldPath);
            }
            $data['logo_path'] = null;
        }

        if ($this->model->update($id, $data)) {
            log_activity('app_link.update', 'Memperbarui tautan aplikasi: ' . $data['name']);
            return redirect()
                ->to('/admin/app-links')
                ->with('success', 'Tautan aplikasi berhasil diperbarui.');
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Gagal memperbarui data.');
    }

    /**
     * Delete app link
     */
    public function delete(int $id): RedirectResponse
    {
        helper('activity');
        
        $link = $this->model->find($id);
        $linkName = $link['name'] ?? 'Unknown';

        if ($this->model->deleteWithLogo($id)) {
            log_activity('app_link.delete', 'Menghapus tautan aplikasi: ' . $linkName);
            return redirect()
                ->to('/admin/app-links')
                ->with('success', 'Tautan aplikasi berhasil dihapus.');
        }

        return redirect()
            ->back()
            ->with('error', 'Gagal menghapus data.');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $id): RedirectResponse
    {
        if ($this->model->toggleActive($id)) {
            return redirect()
                ->to('/admin/app-links')
                ->with('success', 'Status berhasil diubah.');
        }

        return redirect()
            ->back()
            ->with('errors', ['Gagal mengubah status.']);
    }

    /**
     * Update sort orders
     */
    public function updateSortOrder(): RedirectResponse
    {
        $orderJson = $this->request->getPost('order');
        $orderData = json_decode($orderJson, true);

        if (!is_array($orderData)) {
            return redirect()
                ->back()
                ->with('errors', ['Data urutan tidak valid.']);
        }

        if ($this->model->updateSortOrders($orderData)) {
            return redirect()
                ->to('/admin/app-links')
                ->with('success', 'Urutan berhasil diperbarui.');
        }

        return redirect()
            ->back()
            ->with('errors', ['Gagal memperbarui urutan.']);
    }

    /**
     * Toggle section visibility on homepage
     */
    public function toggleSection(): RedirectResponse
    {
        $profileModel = new OpdProfileModel();
        $profile = $profileModel->orderBy('id', 'ASC')->first();
        
        if (!$profile) {
            return redirect()
                ->to('/admin/app-links')
                ->with('errors', ['Profil tidak ditemukan.']);
        }

        $newValue = $this->request->getPost('show_app_links') ? '1' : '0';
        
        $profileModel->update($profile['id'], [
            'show_app_links' => $newValue,
        ]);

        $message = $newValue === '1' 
            ? 'Sesi tautan aplikasi akan ditampilkan di halaman utama.' 
            : 'Sesi tautan aplikasi disembunyikan dari halaman utama.';

        return redirect()
            ->to('/admin/app-links')
            ->with('success', $message);
    }
}
