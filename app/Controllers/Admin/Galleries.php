<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\FileUploadManager;
use App\Libraries\ImageOptimizer;
use App\Models\GalleryModel;
use Config\AllowedMimes;

class Galleries extends BaseController
{
    private const UPLOAD_DIR = 'uploads/galleries';
    
    protected GalleryModel $galleryModel;

    public function __construct()
    {
        $this->galleryModel = model(GalleryModel::class);
    }

    public function index()
    {
        $items = $this->galleryModel
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
        if (! $file || ! $file->isValid() || ! FileUploadManager::hasAllowedMime($file, AllowedMimes::IMAGES)) {
            return redirect()->back()->withInput()->with('error', 'Jenis file gambar tidak diizinkan.');
        }

        $newPath = ImageOptimizer::moveWithPreset($file, self::UPLOAD_DIR, 'gallery');
        if (! $newPath) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan file gambar.');
        }

        $titleInput       = sanitize_plain_text($this->request->getPost('title'));
        $descriptionInput = sanitize_rich_text($this->request->getPost('description'));

        $this->galleryModel->insert([
            'title'       => $titleInput,
            'description' => $descriptionInput,
            'image_path'  => $newPath,
        ]);

        log_activity('gallery.create', 'Menambah foto galeri: ' . $titleInput);

        return redirect()->to(site_url('admin/galleries'))->with('message', 'Foto ditambahkan.');
    }

    public function edit(int $id)
    {
        $item = $this->galleryModel->find($id);
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

        $item  = $this->galleryModel->find($id);
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
            if (! FileUploadManager::hasAllowedMime($file, AllowedMimes::IMAGES)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file gambar tidak diizinkan.');
            }
            
            // Delete old image with variants before uploading new one
            ImageOptimizer::deleteWithVariants($item['image_path'] ?? null);

            $newPath = ImageOptimizer::moveWithPreset($file, self::UPLOAD_DIR, 'gallery');
            if (! $newPath) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan file gambar.');
            }

            $data['image_path'] = $newPath;
        }

        $this->galleryModel->update($id, $data);

        log_activity('gallery.update', 'Mengubah foto galeri: ' . $titleInput);

        return redirect()->to(site_url('admin/galleries'))->with('message', 'Perubahan disimpan.');
    }

    public function delete(int $id)
    {
        helper('activity');

        $item  = $this->galleryModel->find($id);
        if ($item) {
            // Delete image with all responsive variants
            ImageOptimizer::deleteWithVariants($item['image_path'] ?? null);
            
            $this->galleryModel->delete($id);
            log_activity('gallery.delete', 'Menghapus foto galeri: ' . ($item['title'] ?? ''));
        }

        return redirect()->to(site_url('admin/galleries'))->with('message', 'Data dihapus.');
    }
}
