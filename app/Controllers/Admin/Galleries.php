<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GalleryModel;

class Galleries extends BaseController
{
    private function ensureUploadsDir(): string
    {
        $target = FCPATH . 'uploads/galleries';
        if (! is_dir($target)) {
            @mkdir($target, 0775, true);
        }
        return $target;
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
            'title' => 'Tambah Foto Galeri',
            'item'  => [
                'id' => 0,
                'title' => '',
                'description' => '',
                'image_path' => '',
            ],
            'mode' => 'create',
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function store()
    {
        $rules = [
            'title' => 'required|min_length[3]|max_length[150]',
            'image' => 'uploaded[image]|max_size[image,4096]|is_image[image]'
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian Anda.');
        }

        $file = $this->request->getFile('image');
        $this->ensureUploadsDir();
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/galleries', $newName);

        (new GalleryModel())->insert([
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'image_path'  => 'uploads/galleries/' . $newName,
        ]);

        return redirect()->to(site_url('admin/galleries'))->with('message', 'Foto ditambahkan.');
    }

    public function edit(int $id)
    {
        $item = (new GalleryModel())->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/galleries'))->with('error', 'Data tidak ditemukan.');
        }
        return view('admin/galleries/form', [
            'title' => 'Ubah Foto Galeri',
            'item'  => $item,
            'mode'  => 'edit',
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function update(int $id)
    {
        $model = new GalleryModel();
        $item = $model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/galleries'))->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'title' => 'required|min_length[3]|max_length[150]',
            'image' => 'permit_empty|uploaded[image]|max_size[image,4096]|is_image[image]'
        ];
        if (! $this->request->getFile('image')->isValid()) {
            $rules['image'] = 'permit_empty';
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian Anda.');
        }

        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
        ];

        $file = $this->request->getFile('image');
        if ($file && $file->isValid()) {
            $this->ensureUploadsDir();
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/galleries', $newName);
            $data['image_path'] = 'uploads/galleries/' . $newName;
        }

        $model->update($id, $data);
        return redirect()->to(site_url('admin/galleries'))->with('message', 'Perubahan disimpan.');
    }

    public function delete(int $id)
    {
        $model = new GalleryModel();
        if ($model->find($id)) {
            $model->delete($id);
        }
        return redirect()->to(site_url('admin/galleries'))->with('message', 'Data dihapus.');
    }
}

