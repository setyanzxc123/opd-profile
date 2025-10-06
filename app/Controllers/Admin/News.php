<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NewsModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class News extends BaseController
{
    private const UPLOAD_DIR = 'uploads/news';
    private const ALLOWED_THUMBNAIL_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/pjpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    private function optimizeImage(string $path): void
    {
        $image = \Config\Services::image();
        
        $image->withFile($path)
            ->resize(1200, 630, true, 'width') // Optimal size for social media sharing
            ->save($path, 85);
    }

    private function ensureUploadsDir(): string
    {
        $target = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, self::UPLOAD_DIR);
        if (! is_dir($target)) {
            @mkdir($target, 0775, true);
        }

        return $target;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        helper('text');

        $base = url_title($title, '-', true);
        if ($base === '') {
            $base = 'news';
        }

        $slug  = $base;
        $model = new NewsModel();
        $i     = 2;

        while (true) {
            $existing = $model->where('slug', $slug);
            if ($ignoreId) {
                $existing = $existing->where('id !=', $ignoreId);
            }

            if (! $existing->first()) {
                break;
            }

            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function normalizePublishedAt(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = sanitize_plain_text($value);
        if ($value === '') {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        if (strlen($value) === 16) {
            // handle "Y-m-d H:i"
            $value .= ':00';
        }

        return $value;
    }

    private function hasAllowedMime(UploadedFile $file): bool
    {
        $mime = strtolower((string) $file->getMimeType());

        return in_array($mime, self::ALLOWED_THUMBNAIL_MIMES, true);
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

    private function moveThumbnail(UploadedFile $file, ?string $originalPath = null): ?string
    {
        $targetDir = $this->ensureUploadsDir();
        $newName   = $file->getRandomName();

        try {
            $file->move($targetDir, $newName, true);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to store news thumbnail: {error}', ['error' => $e->getMessage()]);
            return null;
        }

        $relativePath = self::UPLOAD_DIR . '/' . $newName;
        $fullPath     = $targetDir . DIRECTORY_SEPARATOR . $newName;

        try {
            $this->optimizeImage($fullPath);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to optimize news thumbnail: {error}', ['error' => $e->getMessage()]);
        }

        if ($originalPath && $originalPath !== $relativePath) {
            $this->deleteFile($originalPath);
        }

        return $relativePath;
    }

    public function index()
    {
        $model = new NewsModel();
        $items = $model->orderBy('id', 'DESC')->findAll(50);

        return view('admin/news/index', [
            'title' => 'Berita',
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('admin/news/form', [
            'title' => 'Tambah Berita',
            'item'  => [
                'id'           => 0,
                'title'        => '',
                'slug'         => '',
                'content'      => '',
                'thumbnail'    => '',
                'published_at' => '',
            ],
            'validation' => \Config\Services::validation(),
            'mode'       => 'create',
        ]);
    }

    public function store()
    {
        helper(['activity', 'content']);

        $rules = [
            'title'        => 'required|min_length[3]|max_length[200]',
            'content'      => 'required',
            'published_at' => 'permit_empty',
            'thumbnail'    => 'permit_empty|max_size[thumbnail,4096]|is_image[thumbnail]|ext_in[thumbnail,jpg,jpeg,png,webp,gif]|mime_in[thumbnail,image/jpeg,image/jpg,image/pjpeg,image/png,image/webp,image/gif]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali data yang diisi.');
        }

        $model       = new NewsModel();
        $titleInput  = sanitize_plain_text($this->request->getPost('title'));
        $slug        = $this->uniqueSlug($titleInput);
        $contentRaw  = (string) $this->request->getPost('content');
        $content     = sanitize_rich_text($contentRaw);
        $publishedAt = $this->normalizePublishedAt($this->request->getPost('published_at'));

        $thumbPath = null;
        $file      = $this->request->getFile('thumbnail');
        if ($file && $file->isValid()) {
            if (! $this->hasAllowedMime($file)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file thumbnail tidak diizinkan.');
            }

            $thumbPath = $this->moveThumbnail($file);
            if (! $thumbPath) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan thumbnail.');
            }
        }

        $model->insert([
            'title'        => $titleInput,
            'slug'         => $slug,
            'content'      => $content,
            'thumbnail'    => $thumbPath,
            'published_at' => $publishedAt,
            'author_id'    => (int) session('user_id'),
        ]);

        log_activity('news.create', 'Menambah berita: ' . $titleInput);

        return redirect()->to(site_url('admin/news'))->with('message', 'Berita berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $model = new NewsModel();
        $item  = $model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/news'))->with('error', 'Data berita tidak ditemukan.');
        }

        return view('admin/news/form', [
            'title'      => 'Ubah Berita',
            'item'       => $item,
            'validation' => \Config\Services::validation(),
            'mode'       => 'edit',
        ]);
    }

    public function update(int $id)
    {
        helper(['activity', 'content']);

        $model = new NewsModel();
        $item  = $model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/news'))->with('error', 'Data berita tidak ditemukan.');
        }

        $rules = [
            'title'        => 'required|min_length[3]|max_length[200]',
            'content'      => 'required',
            'published_at' => 'permit_empty',
            'thumbnail'    => 'permit_empty|max_size[thumbnail,4096]|is_image[thumbnail]|ext_in[thumbnail,jpg,jpeg,png,webp,gif]|mime_in[thumbnail,image/jpeg,image/jpg,image/pjpeg,image/png,image/webp,image/gif]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali data yang diisi.');
        }

        $titleInput  = sanitize_plain_text($this->request->getPost('title'));
        $slug        = $this->uniqueSlug($titleInput, $id);
        $contentRaw  = (string) $this->request->getPost('content');
        $content     = sanitize_rich_text($contentRaw);
        $publishedAt = $this->normalizePublishedAt($this->request->getPost('published_at'));

        $data = [
            'title'        => $titleInput,
            'slug'         => $slug,
            'content'      => $content,
            'published_at' => $publishedAt,
        ];

        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid()) {
            if (! $this->hasAllowedMime($file)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file thumbnail tidak diizinkan.');
            }

            $newPath = $this->moveThumbnail($file, $item['thumbnail'] ?? null);
            if (! $newPath) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan thumbnail.');
            }

            $data['thumbnail'] = $newPath;
        }

        $model->update($id, $data);

        log_activity('news.update', 'Mengubah berita: ' . $titleInput);

        return redirect()->to(site_url('admin/news'))->with('message', 'Berita berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        helper('activity');

        $model = new NewsModel();
        $item  = $model->find($id);
        if ($item) {
            $this->deleteFile($item['thumbnail'] ?? null);
            $model->delete($id);
            log_activity('news.delete', 'Menghapus berita: ' . ($item['title'] ?? ''));
        }

        return redirect()->to(site_url('admin/news'))->with('message', 'Berita berhasil dihapus.');
    }
}
