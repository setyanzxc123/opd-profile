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
        $rules = [
            'name' => 'required|max_length[255]',
            'url'  => 'required|valid_url_strict|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'url'         => $this->request->getPost('url'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
            'sort_order'  => $this->model->getNextSortOrder(),
        ];

        // Handle logo upload
        $logoFile = $this->request->getFile('logo');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
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
            return redirect()
                ->to('/admin/app-links')
                ->with('success', 'Tautan aplikasi berhasil ditambahkan.');
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('errors', ['Gagal menyimpan data.']);
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
        $link = $this->model->find($id);

        if (!$link) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'name' => 'required|max_length[255]',
            'url'  => 'required|valid_url_strict|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'url'         => $this->request->getPost('url'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Handle logo upload
        $logoFile = $this->request->getFile('logo');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            // Delete old logo
            if (!empty($link['logo_path'])) {
                $oldPath = FCPATH . ltrim($link['logo_path'], '/');
                if (is_file($oldPath)) {
                    @unlink($oldPath);
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

        // Handle logo removal
        if ($this->request->getPost('remove_logo') && !empty($link['logo_path'])) {
            $oldPath = FCPATH . ltrim($link['logo_path'], '/');
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
            $data['logo_path'] = null;
        }

        if ($this->model->update($id, $data)) {
            return redirect()
                ->to('/admin/app-links')
                ->with('success', 'Tautan aplikasi berhasil diperbarui.');
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('errors', ['Gagal memperbarui data.']);
    }

    /**
     * Delete app link
     */
    public function delete(int $id): RedirectResponse
    {
        if ($this->model->deleteWithLogo($id)) {
            return redirect()
                ->to('/admin/app-links')
                ->with('success', 'Tautan aplikasi berhasil dihapus.');
        }

        return redirect()
            ->back()
            ->with('errors', ['Gagal menghapus data.']);
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
