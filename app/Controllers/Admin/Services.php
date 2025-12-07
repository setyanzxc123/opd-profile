<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\FileUploadManager;
use App\Models\ServiceModel;

class Services extends BaseController
{
    private const UPLOAD_DIR = 'uploads/services';
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/pjpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    private function moveImageWithOptimization(\CodeIgniter\HTTP\Files\UploadedFile $file, ?string $originalPath = null): ?string
    {
        $newPath = FileUploadManager::moveFile($file, self::UPLOAD_DIR, $originalPath);
        if (! $newPath) {
            return null;
        }

        $fullPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
                  . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $newPath);

        try {
            $image = \Config\Services::image();
            $image->withFile($fullPath)
                ->resize(1280, 720, true, 'width')
                ->save($fullPath, 80);
            
            // Generate variants for responsive images
            helper('image');
            generate_image_variants($fullPath);
        } catch (\Throwable $throwable) {
            log_message('debug', 'Thumbnail optimization skipped: {error}', ['error' => $throwable->getMessage()]);
        }

        return $newPath;
    }

    private function clearServiceCaches(): void
    {
        try {
            $cache = cache();
        } catch (\Throwable $throwable) {
            log_message('debug', 'Failed to access cache instance: {error}', ['error' => $throwable->getMessage()]);

            return;
        }

        if (! $cache) {
            return;
        }

        try {
            if (method_exists($cache, 'deleteMatching')) {
                $cache->deleteMatching('public_services_*');

                return;
            }
        } catch (\Throwable $throwable) {
            log_message('debug', 'Failed to flush services cache via pattern: {error}', ['error' => $throwable->getMessage()]);
        }

        $cache->delete('public_services_all');
        foreach ([4, 6, 8, 12] as $limit) {
            $cache->delete(sprintf('public_services_featured_%d', $limit));
        }
    }

    public function index(): string
    {
        $items = (new ServiceModel())
            ->orderBy('sort_order', 'ASC')
            ->orderBy('title', 'ASC')
            ->findAll();

        return view('admin/services/index', [
            'title' => 'Layanan',
            'items' => $items,
        ]);
    }

    public function create(): string
    {
        return view('admin/services/form', [
            'title'      => 'Tambah Layanan',
            'mode'       => 'create',
            'item'       => [
                'id'              => 0,
                'title'           => '',
                'slug'            => '',
                'description'     => '',
                'content'         => '',
                'requirements'    => '',
                'fees'            => '',
                'processing_time' => '',
                'thumbnail'       => '',
                'is_active'       => 1,
                'sort_order'      => 0,
            ],
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function store()
    {
        helper(['activity', 'content', 'text']);

        $rules = [
            'title'           => 'required|min_length[3]|max_length[150]',
            'slug'            => 'permit_empty|max_length[180]',
            'description'     => 'permit_empty',
            'content'         => 'permit_empty',
            'requirements'    => 'permit_empty',
            'fees'            => 'permit_empty|max_length[120]',
            'processing_time' => 'permit_empty|max_length[120]',
            'sort_order'      => 'permit_empty|integer',
            'thumbnail'       => 'permit_empty|max_size[thumbnail,4096]|is_image[thumbnail]|ext_in[thumbnail,jpg,jpeg,png,webp,gif]|mime_in[thumbnail,image/jpeg,image/jpg,image/pjpeg,image/png,image/webp,image/gif]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian Anda.');
        }

        helper('slug');

        $model            = new ServiceModel();
        $titleInput       = sanitize_plain_text($this->request->getPost('title'));
        $slugInput        = sanitize_plain_text($this->request->getPost('slug'));
        $slug             = unique_slug($titleInput, $model, 'slug', $slugInput ?: null, null, 'layanan');
        $descriptionInput = sanitize_plain_text($this->request->getPost('description'));
        $contentInput     = sanitize_rich_text($this->request->getPost('content'));
        $requirements     = sanitize_plain_text($this->request->getPost('requirements'));
        $fees             = sanitize_plain_text($this->request->getPost('fees'));
        $processingTime   = sanitize_plain_text($this->request->getPost('processing_time'));
        $sortOrder        = (int) ($this->request->getPost('sort_order') ?: 0);
        $isActive         = $this->request->getPost('is_active') ? 1 : 0;

        $data = [
            'title'           => $titleInput,
            'slug'            => $slug,
            'description'     => $descriptionInput,
            'content'         => $contentInput,
            'requirements'    => $requirements,
            'fees'            => $fees,
            'processing_time' => $processingTime,
            'is_active'       => $isActive,
            'sort_order'      => $sortOrder,
        ];

        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid()) {
            if (! FileUploadManager::hasAllowedMime($file, self::ALLOWED_IMAGE_MIMES)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file thumbnail tidak diizinkan.');
            }

            $newPath = $this->moveImageWithOptimization($file);
            if (! $newPath) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan thumbnail.');
            }

            $data['thumbnail'] = $newPath;
        }

        (new ServiceModel())->insert($data);

        $this->clearServiceCaches();

        log_activity('service.create', 'Menambah layanan: ' . $titleInput);

        return redirect()->to(site_url('admin/services'))->with('message', 'Layanan berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $item = (new ServiceModel())->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/services'))->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/services/form', [
            'title'      => 'Ubah Layanan',
            'mode'       => 'edit',
            'item'       => $item,
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function update(int $id)
    {
        helper(['activity', 'content', 'text']);

        $model = new ServiceModel();
        $item  = $model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/services'))->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'title'           => 'required|min_length[3]|max_length[150]',
            'slug'            => 'permit_empty|max_length[180]',
            'description'     => 'permit_empty',
            'content'         => 'permit_empty',
            'requirements'    => 'permit_empty',
            'fees'            => 'permit_empty|max_length[120]',
            'processing_time' => 'permit_empty|max_length[120]',
            'sort_order'      => 'permit_empty|integer',
            'thumbnail'       => 'permit_empty|max_size[thumbnail,4096]|is_image[thumbnail]|ext_in[thumbnail,jpg,jpeg,png,webp,gif]|mime_in[thumbnail,image/jpeg,image/jpg,image/pjpeg,image/png,image/webp,image/gif]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian Anda.');
        }

        helper('slug');

        $titleInput       = sanitize_plain_text($this->request->getPost('title'));
        $slugInput        = sanitize_plain_text($this->request->getPost('slug'));
        $slug             = unique_slug($titleInput, $model, 'slug', $slugInput ?: null, $id, 'layanan');
        $descriptionInput = sanitize_plain_text($this->request->getPost('description'));
        $contentInput     = sanitize_rich_text($this->request->getPost('content'));
        $requirements     = sanitize_plain_text($this->request->getPost('requirements'));
        $fees             = sanitize_plain_text($this->request->getPost('fees'));
        $processingTime   = sanitize_plain_text($this->request->getPost('processing_time'));
        $sortOrder        = (int) ($this->request->getPost('sort_order') ?: 0);
        $isActive         = $this->request->getPost('is_active') ? 1 : 0;

        $data = [
            'title'           => $titleInput,
            'slug'            => $slug,
            'description'     => $descriptionInput,
            'content'         => $contentInput,
            'requirements'    => $requirements,
            'fees'            => $fees,
            'processing_time' => $processingTime,
            'is_active'       => $isActive,
            'sort_order'      => $sortOrder,
        ];

        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid()) {
            if (! FileUploadManager::hasAllowedMime($file, self::ALLOWED_IMAGE_MIMES)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file thumbnail tidak diizinkan.');
            }
            
            // Delete old variants if updating image
            if (!empty($item['thumbnail'])) {
                helper('image');
                $oldFullPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $item['thumbnail']);
                delete_image_variants($oldFullPath);
            }

            $newPath = $this->moveImageWithOptimization($file, $item['thumbnail'] ?? null);
            if (! $newPath) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan thumbnail.');
            }

            $data['thumbnail'] = $newPath;
        }

        $model->update($id, $data);

        $this->clearServiceCaches();

        log_activity('service.update', 'Mengubah layanan: ' . $titleInput);

        return redirect()->to(site_url('admin/services'))->with('message', 'Perubahan berhasil disimpan.');
    }

    public function delete(int $id)
    {
        helper('activity');

        $model = new ServiceModel();
        $item  = $model->find($id);
        if ($item) {
            if (!empty($item['thumbnail'])) {
                 helper('image');
                 $fullPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $item['thumbnail']);
                 delete_image_variants($fullPath);
            }
            
            FileUploadManager::deleteFile($item['thumbnail'] ?? null);
            $model->delete($id);
            $this->clearServiceCaches();
            log_activity('service.delete', 'Menghapus layanan: ' . ($item['title'] ?? ''));
        }

        return redirect()->to(site_url('admin/services'))->with('message', 'Layanan dihapus.');
    }
}
