<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DocumentModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class Documents extends BaseController
{
    private const UPLOAD_DIR = 'uploads/documents';
    private const ALLOWED_DOC_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/zip',
        'application/x-zip-compressed',
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

        return in_array($mime, self::ALLOWED_DOC_MIMES, true);
    }

    private function moveDocument(UploadedFile $file, ?string $originalPath = null): ?string
    {
        $targetDir = $this->ensureUploadsDir();
        $newName   = $file->getRandomName();

        try {
            $file->move($targetDir, $newName, true);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to store document file: {error}', ['error' => $e->getMessage()]);
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
        if (! $file || ! $file->isValid() || ! $this->hasAllowedMime($file)) {
            return redirect()->back()->withInput()->with('error', 'Jenis file dokumen tidak diizinkan.');
        }

        $newPath = $this->moveDocument($file);
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
            if (! $this->hasAllowedMime($file)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file dokumen tidak diizinkan.');
            }

            $newPath = $this->moveDocument($file, $item['file_path'] ?? null);
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
            $this->deleteFile($item['file_path'] ?? null);
            $model->delete($id);
            log_activity('document.delete', 'Menghapus dokumen: ' . ($item['title'] ?? ''));
        }
        return redirect()->to(site_url('admin/documents'))->with('message', 'Data dihapus.');
    }
}
