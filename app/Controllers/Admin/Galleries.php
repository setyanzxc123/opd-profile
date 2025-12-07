<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\FileUploadManager;
use App\Models\GalleryModel;

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

    private function moveImageWithOptimization(\CodeIgniter\HTTP\Files\UploadedFile $file, ?string $originalPath = null): ?string
    {
        $newPath = FileUploadManager::moveFile($file, self::UPLOAD_DIR, $originalPath);
        if (! $newPath) {
            return null;
        }

        $fullPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
                  . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $newPath);

        try {
            $imageService = \Config\Services::image();
            $imageService->withFile($fullPath)
                ->resize(1920, 1080, true, 'width')
                ->save($fullPath, 80);
            
            // Generate variants for responsive images
            helper('image');
            generate_image_variants($fullPath);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to optimize gallery image: {error}', ['error' => $e->getMessage()]);
        }

        return $newPath;
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
        if (! $file || ! $file->isValid() || ! FileUploadManager::hasAllowedMime($file, self::ALLOWED_IMAGE_MIMES)) {
            return redirect()->back()->withInput()->with('error', 'Jenis file gambar tidak diizinkan.');
        }

        $newPath = $this->moveImageWithOptimization($file);
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
            if (! FileUploadManager::hasAllowedMime($file, self::ALLOWED_IMAGE_MIMES)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file gambar tidak diizinkan.');
            }
            
            // Delete old variants if updating image
            if (!empty($item['image_path'])) {
                helper('image');
                $oldFullPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $item['image_path']);
                delete_image_variants($oldFullPath);
            }

            $newPath = $this->moveImageWithOptimization($file, $item['image_path'] ?? null);
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
            if (!empty($item['image_path'])) {
                 helper('image');
                 $fullPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $item['image_path']);
                 delete_image_variants($fullPath);
            }
            
            FileUploadManager::deleteFile($item['image_path'] ?? null);
            $model->delete($id);
            log_activity('gallery.delete', 'Menghapus foto galeri: ' . ($item['title'] ?? ''));
        }

        return redirect()->to(site_url('admin/galleries'))->with('message', 'Data dihapus.');
    }
}
