<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GalleryModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class Galleries extends BaseController
{
    private const UPLOAD_DIR = 'uploads/galleries';
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
        } catch (\Throwable $e) {
            log_message('error', 'Failed to store gallery image: {error}', ['error' => $e->getMessage()]);
            return null;
        }

        $relativePath = self::UPLOAD_DIR . '/' . $newName;

        if ($originalPath && $originalPath !== $relativePath) {
            $this->deleteFile($originalPath);
        }

        return $relativePath;
    }

    public function index()
    {
        $items = (new GalleryModel())
            ->orderBy('id', 'DESC')
            ->findAll(100);

        return view('admin/galleries/index', [
            'title' => 'Galeri',
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('admin/galleries/form', [
            'title'      => 'Tambah Foto Galeri',
            'item'       => [
                'id'          => 0,
                'title'       => '',
                'description' => '',
                'image_path'  => '',
            ],
            'mode'       => 'create',
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function store()
    {
        helper(['activity', 'content']);

        $rules = [
            'title' => 'required|min_length[3]|max_length[150]',
            'image' => 'uploaded[image]|max_size[image,4096]|is_image[image]|ext_in[image,jpg,jpeg,png,webp,gif]|mime_in[image,image/jpeg,image/jpg,image/pjpeg,image/png,image/webp,image/gif]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian Anda.');
        }

        $file = $this->request->getFile('image');
        if (! $file || ! $file->isValid() || ! $this->hasAllowedMime($file)) {
            return redirect()->back()->withInput()->with('error', 'Jenis file gambar tidak diizinkan.');
        }

        $newPath = $this->moveImage($file);
        if (! $newPath) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan file gambar.');
        }

        $titleInput       = sanitize_plain_text($this->request->getPost('title'));
        $descriptionInput = sanitize_rich_text($this->request->getPost('description'));

        (new GalleryModel())->insert([
            'title'       => $titleInput,
            'description' => $descriptionInput,
            'image_path'  => $newPath,
        ]);

        log_activity('gallery.create', 'Menambah foto galeri: ' . $titleInput);

        return redirect()->to(site_url('admin/galleries'))->with('message', 'Foto ditambahkan.');
    }

    public function edit(int $id)
    {
        $item = (new GalleryModel())->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/galleries'))->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/galleries/form', [
            'title'      => 'Ubah Foto Galeri',
            'item'       => $item,
            'mode'       => 'edit',
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function update(int $id)
    {
        helper(['activity', 'content']);

        $model = new GalleryModel();
        $item  = $model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/galleries'))->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'title' => 'required|min_length[3]|max_length[150]',
            'image' => 'permit_empty|max_size[image,4096]|is_image[image]|ext_in[image,jpg,jpeg,png,webp,gif]|mime_in[image,image/jpeg,image/jpg,image/pjpeg,image/png,image/webp,image/gif]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian Anda.');
        }

        $titleInput       = sanitize_plain_text($this->request->getPost('title'));
        $descriptionInput = sanitize_rich_text($this->request->getPost('description'));

        $data = [
            'title'       => $titleInput,
            'description' => $descriptionInput,
        ];

        $file = $this->request->getFile('image');
        if ($file && $file->isValid()) {
            if (! $this->hasAllowedMime($file)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file gambar tidak diizinkan.');
            }

            $newPath = $this->moveImage($file, $item['image_path'] ?? null);
            if (! $newPath) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan file gambar.');
            }

            $data['image_path'] = $newPath;
        }

        $model->update($id, $data);

        log_activity('gallery.update', 'Mengubah foto galeri: ' . $titleInput);

        return redirect()->to(site_url('admin/galleries'))->with('message', 'Perubahan disimpan.');
    }

    public function delete(int $id)
    {
        helper('activity');

        $model = new GalleryModel();
        $item  = $model->find($id);
        if ($item) {
            $this->deleteFile($item['image_path'] ?? null);
            $model->delete($id);
            log_activity('gallery.delete', 'Menghapus foto galeri: ' . ($item['title'] ?? ''));
        }

        return redirect()->to(site_url('admin/galleries'))->with('message', 'Data dihapus.');
    }
}
