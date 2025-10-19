<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NewsCategoryModel;
use App\Models\NewsModel;
use App\Models\NewsTagModel;
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
        $info  = @getimagesize($path);
        $width = $info[0] ?? null;

        $editor = $image->withFile($path);

        if ($width !== null && $width <= 1200) {
            // Skip resizing to avoid upscaling small images; still re-encode for size/quality.
            $editor->save($path, 85);

            return;
        }

        $editor
            ->resize(1200, 630, true, 'width') // Resize only when the original is wider than 1200px.
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

    private function taxonomyOptions(): array
    {
        $categories = model(NewsCategoryModel::class)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->findAll();
        $tags = model(NewsTagModel::class)->getAllOrdered();

        return [
            'categories' => $categories,
            'tags'       => $tags,
        ];
    }

    /**
     * @return array<int,int>
     */
    private function parseCategoryInput(): array
    {
        $input = $this->request->getPost('categories');
        if (! is_array($input)) {
            $input = $input ? [$input] : [];
        }

        $ids = [];
        foreach ($input as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function determinePrimaryCategory(array $categoryIds): ?int
    {
        if ($categoryIds === []) {
            return null;
        }

        $primary = (int) $this->request->getPost('primary_category');
        if ($primary > 0 && in_array($primary, $categoryIds, true)) {
            return $primary;
        }

        return $categoryIds[0] ?? null;
    }

    /**
     * @return array<int,int>
     */
    private function parseExistingTagInput(): array
    {
        $input = $this->request->getPost('tags');
        if (! is_array($input)) {
            $input = $input ? [$input] : [];
        }

        $ids = [];
        foreach ($input as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array<int,int>
     */
    private function createTagsFromInput(string $rawInput): array
    {
        helper('text');

        $chunks = preg_split('/[,;\n]+/', $rawInput) ?: [];
        if ($chunks === []) {
            return [];
        }

        $tagModel = model(NewsTagModel::class);
        $result   = [];

        foreach ($chunks as $chunk) {
            $clean = trim(sanitize_plain_text((string) $chunk));
            if ($clean === '') {
                continue;
            }

            $slug = url_title($clean, '-', true);
            if ($slug === '') {
                continue;
            }

            $existing = $tagModel->where('slug', $slug)->first();
            if ($existing) {
                $result[] = (int) $existing['id'];
                continue;
            }

            $tagModel->insert([
                'name' => $clean,
                'slug' => $slug,
            ]);

            $result[] = (int) $tagModel->getInsertID();
        }

        return array_values(array_unique($result));
    }

    public function index()
    {
        $model = new NewsModel();
        $items = $model->orderBy('id', 'DESC')->findAll(50);

        $categoryLookup = [];
        if ($items !== []) {
            $categoryIds = array_unique(array_filter(array_map(static function (array $item) {
                return (int) ($item['primary_category_id'] ?? 0);
            }, $items)));

            if ($categoryIds !== []) {
                $categories = model(NewsCategoryModel::class)->whereIn('id', $categoryIds)->findAll();
                foreach ($categories as $category) {
                    $categoryLookup[(int) $category['id']] = $category['name'];
                }
            }
        }

        return view('admin/news/index', [
            'title'          => 'Berita',
            'items'          => $items,
            'categoryLookup' => $categoryLookup,
        ]);
    }

    public function create()
    {
        $taxonomy = $this->taxonomyOptions();

        return view('admin/news/form', [
            'title'              => 'Tambah Berita',
            'item'               => [
                'id'                 => 0,
                'title'              => '',
                'slug'               => '',
                'content'            => '',
                'excerpt'            => '',
                'public_author'      => '',
                'source'             => '',
                'meta_title'         => '',
                'meta_description'   => '',
                'meta_keywords'      => '',
                'thumbnail'          => '',
                'published_at'       => '',
                'primary_category_id'=> null,
            ],
            'validation'         => \Config\Services::validation(),
            'mode'               => 'create',
            'categories'         => $taxonomy['categories'],
            'tags'               => $taxonomy['tags'],
            'selectedCategories' => [],
            'selectedTags'       => [],
            'primaryCategory'    => null,
        ]);
    }

    public function store()
    {
        helper(['activity', 'content', 'news']);

        $rules = [
            'title'            => 'required|min_length[3]|max_length[200]',
            'content'          => 'required',
            'excerpt'          => 'permit_empty|max_length[300]',
            'public_author'    => 'permit_empty|max_length[255]',
            'source'           => 'permit_empty|max_length[255]',
            'meta_title'       => 'permit_empty|max_length[70]',
            'meta_description' => 'permit_empty|max_length[160]',
            'meta_keywords'    => 'permit_empty|max_length[500]',
            'published_at'     => 'permit_empty',
            'thumbnail'        => 'permit_empty|max_size[thumbnail,4096]|is_image[thumbnail]|ext_in[thumbnail,jpg,jpeg,png,webp,gif]|mime_in[thumbnail,image/jpeg,image/jpg,image/pjpeg,image/png,image/webp,image/gif]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali data yang diisi.');
        }

        $model          = new NewsModel();
        $titleInput     = sanitize_plain_text($this->request->getPost('title'));
        $slug           = $this->uniqueSlug($titleInput);
        $contentRaw     = (string) $this->request->getPost('content');
        $content        = sanitize_rich_text($contentRaw);
        $publishedAt    = $this->normalizePublishedAt($this->request->getPost('published_at'));
        $categoryIds    = $this->parseCategoryInput();
        if ($categoryIds !== []) {
            $validCategoryIds = model(NewsCategoryModel::class)->whereIn('id', $categoryIds)->findColumn('id') ?? [];
            $categoryIds      = array_values(array_unique(array_map('intval', $validCategoryIds)));
        }
        $primaryCategory = $this->determinePrimaryCategory($categoryIds);
        $existingTagIds  = $this->parseExistingTagInput();
        if ($existingTagIds !== []) {
            $validTags     = model(NewsTagModel::class)->whereIn('id', $existingTagIds)->findColumn('id') ?? [];
            $existingTagIds= array_values(array_unique(array_map('intval', $validTags)));
        }
        $newTagIds      = $this->createTagsFromInput((string) $this->request->getPost('new_tags'));
        $allTagIds      = array_values(array_unique(array_merge($existingTagIds, $newTagIds)));

        $rawExcerpt      = $this->request->getPost('excerpt');
        $excerpt         = news_trim_excerpt(is_string($rawExcerpt) ? $rawExcerpt : null, $content);
        $publicAuthor    = sanitize_plain_text($this->request->getPost('public_author'));
        $sourceInput     = sanitize_plain_text($this->request->getPost('source'));
        $metaTitleRaw    = $this->request->getPost('meta_title');
        $metaTitle       = news_resolve_meta_title(is_string($metaTitleRaw) ? $metaTitleRaw : null, $titleInput);
        $metaDescRaw     = $this->request->getPost('meta_description');
        $metaDescription = news_resolve_meta_description(is_string($metaDescRaw) ? $metaDescRaw : null, $excerpt, $content);
        $metaKeywordsRaw = $this->request->getPost('meta_keywords');
        $metaKeywords    = $metaKeywordsRaw !== null ? sanitize_plain_text((string) $metaKeywordsRaw) : '';
        $metaKeywords    = $metaKeywords !== '' ? $metaKeywords : null;

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
            'title'               => $titleInput,
            'slug'                => $slug,
            'content'             => $content,
            'excerpt'             => $excerpt,
            'public_author'       => $publicAuthor,
            'source'              => $sourceInput,
            'meta_title'          => $metaTitle,
            'meta_description'    => $metaDescription,
            'meta_keywords'       => $metaKeywords,
            'thumbnail'           => $thumbPath,
            'published_at'        => $publishedAt,
            'author_id'           => (int) session('user_id'),
            'primary_category_id' => $primaryCategory,
        ]);

        $newsId = (int) $model->getInsertID();
        if ($newsId > 0) {
            $model->syncCategories($newsId, $categoryIds);
            $model->syncTags($newsId, $allTagIds);
        }

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

        $taxonomy           = $this->taxonomyOptions();
        $selectedCategories = $model->getCategoryIds($id);
        $selectedTags       = $model->getTagIds($id);

        return view('admin/news/form', [
            'title'              => 'Ubah Berita',
            'item'               => $item,
            'validation'         => \Config\Services::validation(),
            'mode'               => 'edit',
            'categories'         => $taxonomy['categories'],
            'tags'               => $taxonomy['tags'],
            'selectedCategories' => $selectedCategories,
            'selectedTags'       => $selectedTags,
            'primaryCategory'    => $item['primary_category_id'] ?? null,
        ]);
    }

    public function update(int $id)
    {
        helper(['activity', 'content', 'news']);

        $model = new NewsModel();
        $item  = $model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/news'))->with('error', 'Data berita tidak ditemukan.');
        }

        $rules = [
            'title'            => 'required|min_length[3]|max_length[200]',
            'content'          => 'required',
            'excerpt'          => 'permit_empty|max_length[300]',
            'public_author'    => 'permit_empty|max_length[255]',
            'source'           => 'permit_empty|max_length[255]',
            'meta_title'       => 'permit_empty|max_length[70]',
            'meta_description' => 'permit_empty|max_length[160]',
            'meta_keywords'    => 'permit_empty|max_length[500]',
            'published_at'     => 'permit_empty',
            'thumbnail'        => 'permit_empty|max_size[thumbnail,4096]|is_image[thumbnail]|ext_in[thumbnail,jpg,jpeg,png,webp,gif]|mime_in[thumbnail,image/jpeg,image/jpg,image/pjpeg,image/png,image/webp,image/gif]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali data yang diisi.');
        }

        $titleInput     = sanitize_plain_text($this->request->getPost('title'));
        $slug           = $this->uniqueSlug($titleInput, $id);
        $contentRaw     = (string) $this->request->getPost('content');
        $content        = sanitize_rich_text($contentRaw);
        $publishedAt    = $this->normalizePublishedAt($this->request->getPost('published_at'));
        $categoryIds    = $this->parseCategoryInput();
        if ($categoryIds !== []) {
            $validCategoryIds = model(NewsCategoryModel::class)->whereIn('id', $categoryIds)->findColumn('id') ?? [];
            $categoryIds      = array_values(array_unique(array_map('intval', $validCategoryIds)));
        }
        $primaryCategory = $this->determinePrimaryCategory($categoryIds);
        $existingTagIds  = $this->parseExistingTagInput();
        if ($existingTagIds !== []) {
            $validTagIds   = model(NewsTagModel::class)->whereIn('id', $existingTagIds)->findColumn('id') ?? [];
            $existingTagIds= array_values(array_unique(array_map('intval', $validTagIds)));
        }
        $newTagIds      = $this->createTagsFromInput((string) $this->request->getPost('new_tags'));
        $allTagIds      = array_values(array_unique(array_merge($existingTagIds, $newTagIds)));

        $rawExcerpt      = $this->request->getPost('excerpt');
        $excerpt         = news_trim_excerpt(is_string($rawExcerpt) ? $rawExcerpt : null, $content);
        $publicAuthor    = sanitize_plain_text($this->request->getPost('public_author'));
        $sourceInput     = sanitize_plain_text($this->request->getPost('source'));
        $metaTitleRaw    = $this->request->getPost('meta_title');
        $metaTitle       = news_resolve_meta_title(is_string($metaTitleRaw) ? $metaTitleRaw : null, $titleInput);
        $metaDescRaw     = $this->request->getPost('meta_description');
        $metaDescription = news_resolve_meta_description(is_string($metaDescRaw) ? $metaDescRaw : null, $excerpt, $content);
        $metaKeywordsRaw = $this->request->getPost('meta_keywords');
        $metaKeywords    = $metaKeywordsRaw !== null ? sanitize_plain_text((string) $metaKeywordsRaw) : '';
        $metaKeywords    = $metaKeywords !== '' ? $metaKeywords : null;

        $data = [
            'title'               => $titleInput,
            'slug'                => $slug,
            'content'             => $content,
            'excerpt'             => $excerpt,
            'public_author'       => $publicAuthor,
            'source'              => $sourceInput,
            'meta_title'          => $metaTitle,
            'meta_description'    => $metaDescription,
            'meta_keywords'       => $metaKeywords,
            'published_at'        => $publishedAt,
            'primary_category_id' => $primaryCategory,
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
        $model->syncCategories($id, $categoryIds);
        $model->syncTags($id, $allTagIds);

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
