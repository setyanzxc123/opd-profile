<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\FileUploadManager;
use App\Models\DocumentModel;
use Config\AllowedMimes;

class Documents extends BaseController
{
    private const UPLOAD_DIR = 'uploads/documents';

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
            'title'      => 'Tambah Dokumen',
            'mode'       => 'create',
            'item'       => [
                'id'        => 0,
                'title'     => '',
                'category'  => '',
                'year'      => '',
                'file_path' => '',
            ],
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function store()
    {
        helper(['activity', 'content']);

        $rules = [
            'title'    => 'required|min_length[3]|max_length[150]',
            'category' => 'permit_empty|max_length[100]',
            'year'     => 'permit_empty|regex_match[/^\\d{4}$/]',
            'file'     => 'uploaded[file]|max_size[file,10240]|ext_in[file,pdf,doc,docx,xls,xlsx,ppt,pptx,zip]|mime_in[file,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip,application/x-zip-compressed]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian Anda.');
        }

        $file = $this->request->getFile('file');
        if (! $file || ! $file->isValid() || ! FileUploadManager::hasAllowedMime($file, AllowedMimes::DOCUMENTS)) {
            return redirect()->back()->withInput()->with('error', 'Jenis file dokumen tidak diizinkan.');
        }

        $newPath = FileUploadManager::moveFile($file, self::UPLOAD_DIR);
        if (! $newPath) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan file dokumen.');
        }

        $titleInput    = sanitize_plain_text($this->request->getPost('title'));
        $categoryInput = sanitize_plain_text($this->request->getPost('category'));
        $yearInput     = sanitize_plain_text($this->request->getPost('year'));

        (new DocumentModel())->insert([
            'title'     => $titleInput,
            'category'  => $categoryInput,
            'year'      => $yearInput,
            'file_path' => $newPath,
        ]);

        log_activity('document.create', 'Menambah dokumen: ' . $titleInput);

        return redirect()->to(site_url('admin/documents'))->with('message', 'Dokumen ditambahkan.');
    }

    public function edit(int $id)
    {
        $item = (new DocumentModel())->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/documents'))->with('error', 'Data tidak ditemukan.');
        }
        return view('admin/documents/form', [
            'title'      => 'Ubah Dokumen',
            'mode'       => 'edit',
            'item'       => $item,
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function update(int $id)
    {
        helper(['activity', 'content']);

        $model = new DocumentModel();
        $item  = $model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/documents'))->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'title'    => 'required|min_length[3]|max_length[150]',
            'category' => 'permit_empty|max_length[100]',
            'year'     => 'permit_empty|regex_match[/^\\d{4}$/]',
            'file'     => 'permit_empty|max_size[file,10240]|ext_in[file,pdf,doc,docx,xls,xlsx,ppt,pptx,zip]|mime_in[file,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip,application/x-zip-compressed]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian Anda.');
        }

        $titleInput    = sanitize_plain_text($this->request->getPost('title'));
        $categoryInput = sanitize_plain_text($this->request->getPost('category'));
        $yearInput     = sanitize_plain_text($this->request->getPost('year'));

        $data = [
            'title'    => $titleInput,
            'category' => $categoryInput,
            'year'     => $yearInput,
        ];

        $file = $this->request->getFile('file');
        if ($file && $file->isValid()) {
            if (! FileUploadManager::hasAllowedMime($file, AllowedMimes::DOCUMENTS)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file dokumen tidak diizinkan.');
            }

            $newPath = FileUploadManager::moveFile($file, self::UPLOAD_DIR, $item['file_path'] ?? null);
            if (! $newPath) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan file dokumen.');
            }

            $data['file_path'] = $newPath;
        }

        $model->update($id, $data);

        log_activity('document.update', 'Memperbarui dokumen: ' . $titleInput);

        return redirect()->to(site_url('admin/documents'))->with('message', 'Perubahan disimpan.');
    }

    public function delete(int $id)
    {
        helper('activity');
        $model = new DocumentModel();
        $item  = $model->find($id);
        if ($item) {
            FileUploadManager::deleteFile($item['file_path'] ?? null);
            $model->delete($id);
            log_activity('document.delete', 'Menghapus dokumen: ' . ($item['title'] ?? ''));
        }
        return redirect()->to(site_url('admin/documents'))->with('message', 'Data dihapus.');
    }
}
