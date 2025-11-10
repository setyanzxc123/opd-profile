<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ServiceModel;
use CodeIgniter\HTTP\Files\UploadedFile;

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

    private function ensureUploadsDir(): string
    {
        $target = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, self::UPLOAD_DIR);
        if (! is_dir($target)) {
            @mkdir($target, 0775, true);
        }

        return $target;
    }

    private function deleteFile(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        $relativePath = ltrim($relativePath, '/\\');
        $fullPath     = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        $uploadsRoot  = realpath(rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads');
        $realPath     = realpath($fullPath);

        if (! $uploadsRoot || ! $realPath || strpos($realPath, $uploadsRoot) !== 0) {
            return;
        }

        if (is_file($realPath)) {
            @unlink($realPath);
        }
    }

    private function hasAllowedMime(UploadedFile $file): bool
    {
        $mime = strtolower((string) $file->getMimeType());

        return in_array($mime, self::ALLOWED_IMAGE_MIMES, true);
    }

    private function moveImage(UploadedFile $file, ?string $originalPath = null): ?string
    {
        $targetDir = $this->ensureUploadsDir();
        $newName   = $file->getRandomName();

        try {
            $file->move($targetDir, $newName, true);
        } catch (\Throwable $throwable) {
            log_message('error', 'Failed to store service thumbnail: {error}', ['error' => $throwable->getMessage()]);

            return null;
        }

        $relativePath = self::UPLOAD_DIR . '/' . $newName;
        $fullPath     = $targetDir . DIRECTORY_SEPARATOR . $newName;

        try {
            $image = \Config\Services::image();
            $image->withFile($fullPath)
                ->resize(1280, 720, true, 'width')
                ->save($fullPath, 80);
        } catch (\Throwable $throwable) {
            log_message('debug', 'Thumbnail optimization skipped: {error}', ['error' => $throwable->getMessage()]);
        }

        if ($originalPath && $originalPath !== $relativePath) {
            $this->deleteFile($originalPath);
        }

        return $relativePath;
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

    private function uniqueSlug(string $title, ?string $slugInput = null, ?int $ignoreId = null): string
    {
        helper('text');

        $base = $slugInput !== null && $slugInput !== '' ? $slugInput : $title;
        $base = url_title($base, '-', true);
        if ($base === '') {
            $base = 'layanan';
        }

        $slug  = $base;
        $model = new ServiceModel();
        $i     = 2;

        while (true) {
            $query = $model->where('slug', $slug);
            if ($ignoreId) {
                $query = $query->where('id !=', $ignoreId);
            }

            if (! $query->first()) {
                break;
            }

            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
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

        $titleInput       = sanitize_plain_text($this->request->getPost('title'));
        $slugInput        = sanitize_plain_text($this->request->getPost('slug'));
        $slug             = $this->uniqueSlug($titleInput, $slugInput ?: null);
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
            if (! $this->hasAllowedMime($file)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file thumbnail tidak diizinkan.');
            }

            $newPath = $this->moveImage($file);
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

        $titleInput       = sanitize_plain_text($this->request->getPost('title'));
        $slugInput        = sanitize_plain_text($this->request->getPost('slug'));
        $slug             = $this->uniqueSlug($titleInput, $slugInput ?: null, $id);
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
            if (! $this->hasAllowedMime($file)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file thumbnail tidak diizinkan.');
            }

            $newPath = $this->moveImage($file, $item['thumbnail'] ?? null);
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
            $this->deleteFile($item['thumbnail'] ?? null);
            $model->delete($id);
            $this->clearServiceCaches();
            log_activity('service.delete', 'Menghapus layanan: ' . ($item['title'] ?? ''));
        }

        return redirect()->to(site_url('admin/services'))->with('message', 'Layanan dihapus.');
    }
}
