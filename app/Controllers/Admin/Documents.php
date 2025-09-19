<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DocumentModel;

class Documents extends BaseController
{
    private function ensureUploadsDir(): string
    {
        $target = FCPATH . 'uploads/documents';
        if (!is_dir($target)) {
            @mkdir($target, 0775, true);
        }
        return $target;
    }

    public function index()
    {
        $model = new DocumentModel();
        $items = $model->orderBy('id', 'DESC')->findAll(100);
        return view('admin/documents/index', [
            'title' => 'Dokumen',
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('admin/documents/form', [
            'title' => 'Tambah Dokumen',
            'mode'  => 'create',
            'item'  => [
                'id' => 0,
                'title' => '',
                'category' => '',
                'year' => '',
                'file_path' => ''
            ],
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function store()
    {
        $rules = [
            'title'    => 'required|min_length[3]|max_length[150]',
            'category' => 'permit_empty|max_length[100]',
            'year'     => 'permit_empty|regex_match[/^\d{4}$/]',
            'file'     => 'uploaded[file]|max_size[file,10240]|ext_in[file,pdf,doc,docx,xls,xlsx,ppt,pptx,zip]'
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian Anda.');
        }

        helper('activity');

        $file = $this->request->getFile('file');
        $this->ensureUploadsDir();
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/documents', $newName);

        (new DocumentModel())->insert([
            'title'     => $this->request->getPost('title'),
            'category'  => $this->request->getPost('category'),
            'year'      => $this->request->getPost('year'),
            'file_path' => 'uploads/documents/' . $newName,
        ]);

        log_activity('document.create', 'Menambah dokumen: ' . $this->request->getPost('title'));

        return redirect()->to(site_url('admin/documents'))->with('message', 'Dokumen ditambahkan.');
    }

    public function edit(int $id)
    {
        $item = (new DocumentModel())->find($id);
        if (!$item) {
            return redirect()->to(site_url('admin/documents'))->with('error', 'Data tidak ditemukan.');
        }
        return view('admin/documents/form', [
            'title' => 'Ubah Dokumen',
            'mode'  => 'edit',
            'item'  => $item,
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function update(int $id)
    {
        $model = new DocumentModel();
        $item  = $model->find($id);
        if (!$item) {
            return redirect()->to(site_url('admin/documents'))->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'title'    => 'required|min_length[3]|max_length[150]',
            'category' => 'permit_empty|max_length[100]',
            'year'     => 'permit_empty|regex_match[/^\d{4}$/]',
            'file'     => 'permit_empty|uploaded[file]|max_size[file,10240]|ext_in[file,pdf,doc,docx,xls,xlsx,ppt,pptx,zip]'
        ];
        if (!$this->request->getFile('file')->isValid()) {
            $rules['file'] = 'permit_empty';
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian Anda.');
        }

        helper('activity');

        $data = [
            'title'    => $this->request->getPost('title'),
            'category' => $this->request->getPost('category'),
            'year'     => $this->request->getPost('year'),
        ];

        $file = $this->request->getFile('file');
        if ($file && $file->isValid()) {
            $this->ensureUploadsDir();
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/documents', $newName);
            $data['file_path'] = 'uploads/documents/' . $newName;
        }

        $model->update($id, $data);

        log_activity('document.update', 'Memperbarui dokumen: ' . $this->request->getPost('title'));

        return redirect()->to(site_url('admin/documents'))->with('message', 'Perubahan disimpan.');
    }

    public function delete(int $id)
    {
        helper('activity');
        $model = new DocumentModel();
        $item  = $model->find($id);
        if ($item) {
            $model->delete($id);
            log_activity('document.delete', 'Menghapus dokumen: ' . ($item['title'] ?? ''));
        }
        return redirect()->to(site_url('admin/documents'))->with('message', 'Data dihapus.');
    }
}
