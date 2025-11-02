<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NewsCategoryModel;
use App\Models\NewsMediaModel;
use App\Models\NewsModel;
use App\Models\NewsTagModel;
use App\Services\NewsMediaService;

class News extends BaseController
{
    private NewsMediaService $mediaService;

    public function __construct()
    {
        $this->mediaService = service('newsMedia');
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
            'mediaItems'         => [],
        ]);
    }

    public function store()
    {
        helper(['activity', 'content', 'news']);

        $rules = [
            'title'        => 'required|min_length[3]|max_length[200]',
            'content'      => 'required',
            'excerpt'      => 'permit_empty|max_length[300]',
            'published_at' => 'permit_empty',
            'thumbnail'    => 'permit_empty|max_size[thumbnail,4096]|is_image[thumbnail]|ext_in[thumbnail,jpg,jpeg,png,webp,gif]|mime_in[thumbnail,image/jpeg,image/jpg,image/pjpeg,image/png,image/webp,image/gif]',
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

        $rawExcerpt = $this->request->getPost('excerpt');
        $excerpt    = news_trim_excerpt(is_string($rawExcerpt) ? $rawExcerpt : null, $content);

        $thumbPath = null;
        $file      = $this->request->getFile('thumbnail');
        if ($file && $file->isValid()) {
            if (! $this->mediaService->isAllowedImageMime($file)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file thumbnail tidak diizinkan.');
            }

            $thumbPath = $this->mediaService->moveThumbnail($file);
            if (! $thumbPath) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan thumbnail.');
            }
        }

        $mediaPayload = [
            'changes'        => ['delete' => [], 'update' => [], 'insert' => []],
            'deleted_paths'  => [],
            'uploaded_paths' => [],
        ];

        try {
            $mediaPayload = $this->mediaService->collectChanges($this->request, []);
        } catch (\RuntimeException $exception) {
            if ($thumbPath) {
                $this->mediaService->deleteFile($thumbPath);
            }

            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        $model->insert([
            'title'               => $titleInput,
            'slug'                => $slug,
            'content'             => $content,
            'excerpt'             => $excerpt,
            'thumbnail'           => $thumbPath,
            'published_at'        => $publishedAt,
            'author_id'           => (int) session('user_id'),
            'primary_category_id' => $primaryCategory,
        ]);

        $newsId = (int) $model->getInsertID();
        if ($newsId > 0) {
            $model->syncCategories($newsId, $categoryIds);
            $model->syncTags($newsId, $allTagIds);

            $mediaModel = model(NewsMediaModel::class);
            $hasChanges = ! empty($mediaPayload['changes']['delete'])
                || ! empty($mediaPayload['changes']['update'])
                || ! empty($mediaPayload['changes']['insert']);

            $syncedMedia = [];
            if ($hasChanges) {
                $syncedMedia = $mediaModel->syncMedia($newsId, $mediaPayload['changes']);
            }

            foreach ($mediaPayload['deleted_paths'] as $path) {
                $this->mediaService->deleteFile($path);
            }

            if ($syncedMedia !== []) {
                $this->mediaService->refreshCoverThumbnail($model, $newsId, $syncedMedia, $thumbPath);
            } elseif ($thumbPath) {
                $model->update($newsId, ['thumbnail' => $thumbPath]);
            }
        } else {
            foreach ($mediaPayload['uploaded_paths'] as $path) {
                $this->mediaService->deleteFile($path);
            }
            if ($thumbPath) {
                $this->mediaService->deleteFile($thumbPath);
            }

            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data berita.');
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
        $mediaItems         = model(NewsMediaModel::class)->byNews($id);
        $mediaItems         = $this->mediaService->prepareMediaForForm($mediaItems);

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
            'mediaItems'         => $mediaItems,
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
            'title'        => 'required|min_length[3]|max_length[200]',
            'content'      => 'required',
            'excerpt'      => 'permit_empty|max_length[300]',
            'published_at' => 'permit_empty',
            'thumbnail'    => 'permit_empty|max_size[thumbnail,4096]|is_image[thumbnail]|ext_in[thumbnail,jpg,jpeg,png,webp,gif]|mime_in[thumbnail,image/jpeg,image/jpg,image/pjpeg,image/png,image/webp,image/gif]',
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
            $validTagIds    = model(NewsTagModel::class)->whereIn('id', $existingTagIds)->findColumn('id') ?? [];
            $existingTagIds = array_values(array_unique(array_map('intval', $validTagIds)));
        }
        $newTagIds = $this->createTagsFromInput((string) $this->request->getPost('new_tags'));
        $allTagIds = array_values(array_unique(array_merge($existingTagIds, $newTagIds)));

        $rawExcerpt = $this->request->getPost('excerpt');
        $excerpt    = news_trim_excerpt(is_string($rawExcerpt) ? $rawExcerpt : null, $content);

        $data = [
            'title'               => $titleInput,
            'slug'                => $slug,
            'content'             => $content,
            'excerpt'             => $excerpt,
            'published_at'        => $publishedAt,
            'primary_category_id' => $primaryCategory,
        ];

        $mediaModel          = model(NewsMediaModel::class);
        $existingMediaRaw    = $mediaModel->byNews($id);
        $existingMedia       = $this->mediaService->prepareMediaForForm($existingMediaRaw);
        $mediaPayload        = [
            'changes'        => ['delete' => [], 'update' => [], 'insert' => []],
            'deleted_paths'  => [],
            'uploaded_paths' => [],
        ];

        $oldThumbnail      = $item['thumbnail'] ?? null;
        $thumbnailReplaced = false;

        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid()) {
            if (! $this->mediaService->isAllowedImageMime($file)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file thumbnail tidak diizinkan.');
            }

            $newPath = $this->mediaService->moveThumbnail($file);
            if (! $newPath) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan thumbnail.');
            }

            $data['thumbnail'] = $newPath;
            $thumbnailReplaced = true;
        }

        try {
            $mediaPayload = $this->mediaService->collectChanges($this->request, $existingMedia);
        } catch (\RuntimeException $exception) {
            if ($thumbnailReplaced && isset($data['thumbnail'])) {
                $this->mediaService->deleteFile($data['thumbnail']);
            }

            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        $model->update($id, $data);
        $model->syncCategories($id, $categoryIds);
        $model->syncTags($id, $allTagIds);

        $hasChanges = ! empty($mediaPayload['changes']['delete'])
            || ! empty($mediaPayload['changes']['update'])
            || ! empty($mediaPayload['changes']['insert']);

        $syncedMedia = $existingMediaRaw;
        if ($hasChanges) {
            $syncedMedia = $mediaModel->syncMedia($id, $mediaPayload['changes']);
        }

        foreach ($mediaPayload['deleted_paths'] as $path) {
            $this->mediaService->deleteFile($path);
        }

        $this->mediaService->refreshCoverThumbnail($model, $id, $syncedMedia, $data['thumbnail'] ?? $oldThumbnail);

        if ($thumbnailReplaced && $oldThumbnail && ($data['thumbnail'] ?? '') !== $oldThumbnail) {
            $this->mediaService->deleteFile($oldThumbnail);
        }

        log_activity('news.update', 'Mengubah berita: ' . $titleInput);

        return redirect()->to(site_url('admin/news'))->with('message', 'Berita berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        helper('activity');

        $model = new NewsModel();
        $item  = $model->find($id);
        if ($item) {
            $mediaModel = model(NewsMediaModel::class);
            $mediaItems = $mediaModel->byNews($id);

            foreach ($mediaItems as $media) {
                if (($media['media_type'] ?? '') === 'image' && ! empty($media['file_path'])) {
                    $this->mediaService->deleteFile($media['file_path']);
                }
            }

            $mediaModel->where('news_id', $id)->delete();

            $this->mediaService->deleteFile($item['thumbnail'] ?? null);
            $model->delete($id);
            log_activity('news.delete', 'Menghapus berita: ' . ($item['title'] ?? ''));
        }

        return redirect()->to(site_url('admin/news'))->with('message', 'Berita berhasil dihapus.');
    }
}





