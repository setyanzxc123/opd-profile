<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NewsCategoryModel;
use App\Models\NewsMediaModel;
use App\Models\NewsModel;
use App\Models\NewsTagModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class News extends BaseController
{
    private const UPLOAD_DIR = 'uploads/news';
    private const MEDIA_UPLOAD_DIR = 'uploads/news-media';
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

    private function ensureMediaDir(): string
    {
        $target = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, self::MEDIA_UPLOAD_DIR);
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

    private function moveMediaImage(UploadedFile $file): ?string
    {
        $targetDir = $this->ensureMediaDir();
        $newName   = $file->getRandomName();

        try {
            $file->move($targetDir, $newName, true);
        } catch (\Throwable $throwable) {
            log_message('error', 'Failed to store news media image: {error}', ['error' => $throwable->getMessage()]);

            return null;
        }

        $relativePath = self::MEDIA_UPLOAD_DIR . '/' . $newName;
        $fullPath     = $targetDir . DIRECTORY_SEPARATOR . $newName;

        try {
            $this->optimizeImage($fullPath);
        } catch (\Throwable $throwable) {
            log_message('warning', 'Failed to optimise news media image: {error}', ['error' => $throwable->getMessage()]);
        }

        return $relativePath;
    }

    /**
     * @param array<int,array<string,mixed>> $existingMedia
     * @return array{changes: array<string,array>, deleted_paths: array<int,string>, uploaded_paths: array<int,string>}
     */
    private function collectMediaChanges(array $existingMedia): array
    {
        helper('content');

        $existingIndex = [];
        foreach ($existingMedia as $media) {
            $existingIndex[(int) ($media['id'] ?? 0)] = $media;
        }

        $coverRef = (string) $this->request->getPost('media_cover');

        $changes = [
            'delete' => [],
            'update' => [],
            'insert' => [],
        ];

        $deletedPaths  = [];
        $uploadedPaths = [];

        $existingInput = $this->request->getPost('media_existing');
        if (! is_array($existingInput)) {
            $existingInput = [];
        }

        foreach ($existingInput as $id => $payload) {
            $numericId = (int) $id;
            if (! isset($existingIndex[$numericId])) {
                continue;
            }

            $payload = is_array($payload) ? $payload : [];
            $delete  = isset($payload['delete']) && (int) $payload['delete'] === 1;
            if ($delete) {
                $changes['delete'][] = $numericId;
                if (($existingIndex[$numericId]['media_type'] ?? '') === 'image' && ! empty($existingIndex[$numericId]['file_path'])) {
                    $deletedPaths[] = (string) $existingIndex[$numericId]['file_path'];
                }
                continue;
            }

            $sortOrder = isset($payload['sort_order']) ? (int) $payload['sort_order'] : 0;
            $caption   = sanitize_plain_text((string) ($payload['caption'] ?? ''));
            $caption   = $caption !== '' ? $caption : null;

            $update = [
                'id'         => $numericId,
                'caption'    => $caption,
                'sort_order' => $sortOrder,
                'is_cover'   => $coverRef === 'existing:' . $numericId ? 1 : 0,
            ];

            if (($existingIndex[$numericId]['media_type'] ?? '') === 'video') {
                $urlInput = isset($payload['external_url']) ? trim((string) $payload['external_url']) : '';
                if ($urlInput === '') {
                    $urlInput = (string) ($existingIndex[$numericId]['metadata']['source_url'] ?? $existingIndex[$numericId]['external_url'] ?? '');
                }

                if ($urlInput === '') {
                    throw new \RuntimeException('URL video tidak boleh dikosongkan.');
                }

                $normalised = $this->normaliseVideoUrl($urlInput);
                $update['external_url'] = $normalised['embed_url'];
                $update['metadata']     = json_encode($normalised, JSON_UNESCAPED_SLASHES);
            }

            $changes['update'][] = $update;
        }

        $pendingImageUploads = [];
        $newImageFiles       = $this->request->getFileMultiple('media_new_images_files') ?? [];
        $newImageCaptions    = $this->request->getPost('media_new_images_caption') ?? [];
        $newImageSorts       = $this->request->getPost('media_new_images_sort') ?? [];
        $newImageUids        = $this->request->getPost('media_new_images_uid') ?? [];

        if (! is_array($newImageCaptions)) {
            $newImageCaptions = [];
        }
        if (! is_array($newImageSorts)) {
            $newImageSorts = [];
        }
        if (! is_array($newImageUids)) {
            $newImageUids = [];
        }

        $newImageFiles    = array_values($newImageFiles);
        $newImageCaptions = array_values($newImageCaptions);
        $newImageSorts    = array_values($newImageSorts);
        $newImageUids     = array_values($newImageUids);

        $imageCount = count($newImageUids);
        for ($i = 0; $i < $imageCount; $i++) {
            $uid  = (string) $newImageUids[$i];
            $file = $newImageFiles[$i] ?? null;

            if (! $file instanceof UploadedFile) {
                continue;
            }

            if (! $file->isValid()) {
                if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                throw new \RuntimeException('Gagal memproses salah satu file gambar media.');
            }

            if ($file->hasMoved()) {
                throw new \RuntimeException('File gambar media sudah dipindahkan dan tidak dapat diproses ulang.');
            }

            if (! $this->hasAllowedMime($file)) {
                throw new \RuntimeException('Format gambar media tidak diizinkan.');
            }

            $caption   = sanitize_plain_text((string) ($newImageCaptions[$i] ?? ''));
            $sortOrder = isset($newImageSorts[$i]) ? (int) $newImageSorts[$i] : 0;

            $pendingImageUploads[] = [
                'uid'      => $uid,
                'file'     => $file,
                'caption'  => $caption !== '' ? $caption : null,
                'sort'     => $sortOrder,
                'is_cover' => $coverRef === 'new-image|' . $uid ? 1 : 0,
            ];
        }

        $newVideoUrls     = $this->request->getPost('media_new_videos_url') ?? [];
        $newVideoCaptions = $this->request->getPost('media_new_videos_caption') ?? [];
        $newVideoSorts    = $this->request->getPost('media_new_videos_sort') ?? [];
        $newVideoUids     = $this->request->getPost('media_new_videos_uid') ?? [];

        if (! is_array($newVideoUrls)) {
            $newVideoUrls = [];
        }
        if (! is_array($newVideoCaptions)) {
            $newVideoCaptions = [];
        }
        if (! is_array($newVideoSorts)) {
            $newVideoSorts = [];
        }
        if (! is_array($newVideoUids)) {
            $newVideoUids = [];
        }

        $newVideoUrls     = array_values($newVideoUrls);
        $newVideoCaptions = array_values($newVideoCaptions);
        $newVideoSorts    = array_values($newVideoSorts);
        $newVideoUids     = array_values($newVideoUids);

        $videoCount = count($newVideoUids);
        for ($i = 0; $i < $videoCount; $i++) {
            $uid = (string) $newVideoUids[$i];
            $url = trim((string) ($newVideoUrls[$i] ?? ''));
            if ($url === '') {
                continue;
            }

            $normalised = $this->normaliseVideoUrl($url);
            $caption    = sanitize_plain_text((string) ($newVideoCaptions[$i] ?? ''));
            $sortOrder  = isset($newVideoSorts[$i]) ? (int) $newVideoSorts[$i] : 0;

            $changes['insert'][] = [
                'media_type'   => 'video',
                'external_url' => $normalised['embed_url'],
                'metadata'     => json_encode($normalised, JSON_UNESCAPED_SLASHES),
                'caption'      => $caption !== '' ? $caption : null,
                'sort_order'   => $sortOrder,
                'is_cover'     => $coverRef === 'new-video|' . $uid ? 1 : 0,
            ];
        }

        try {
            foreach ($pendingImageUploads as $pending) {
                $path = $this->moveMediaImage($pending['file']);
                if (! $path) {
                    throw new \RuntimeException('Gagal menyimpan salah satu gambar media.');
                }

                $uploadedPaths[] = $path;

                $changes['insert'][] = [
                    'media_type' => 'image',
                    'file_path'  => $path,
                    'caption'    => $pending['caption'],
                    'sort_order' => $pending['sort'],
                    'is_cover'   => $pending['is_cover'],
                ];
            }
        } catch (\Throwable $throwable) {
            foreach ($uploadedPaths as $path) {
                $this->deleteFile($path);
            }

            throw $throwable instanceof \RuntimeException
                ? $throwable
                : new \RuntimeException('Gagal memproses unggahan gambar media.', 0, $throwable);
        }

        return [
            'changes'        => $changes,
            'deleted_paths'  => $deletedPaths,
            'uploaded_paths' => $uploadedPaths,
        ];
    }

    /**
     * @return array<string,string>
     */
    private function normaliseVideoUrl(string $url): array
    {
        $url = trim($url);
        if ($url === '') {
            throw new \RuntimeException('URL video tidak boleh kosong.');
        }

        $validated = filter_var($url, FILTER_VALIDATE_URL);
        if ($validated === false) {
            throw new \RuntimeException('URL video tidak valid.');
        }

        $parts = parse_url($validated);
        $host  = strtolower($parts['host'] ?? '');
        if ($host === '') {
            throw new \RuntimeException('URL video tidak valid.');
        }

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        $path = $parts['path'] ?? '';
        $videoId = '';

        switch ($host) {
            case 'youtu.be':
                $videoId = ltrim((string) $path, '/');
                break;
            case 'youtube.com':
            case 'm.youtube.com':
                parse_str($parts['query'] ?? '', $query);
                $videoId = (string) ($query['v'] ?? '');
                if ($videoId === '' && $path) {
                    $segments = explode('/', trim($path, '/'));
                    if (($segments[0] ?? '') === 'shorts' && isset($segments[1])) {
                        $videoId = $segments[1];
                    } elseif (($segments[0] ?? '') === 'embed' && isset($segments[1])) {
                        $videoId = $segments[1];
                    }
                }
                break;
            case 'player.vimeo.com':
                $segments = explode('/', trim((string) $path, '/'));
                $videoId  = $segments[1] ?? $segments[0] ?? '';
                break;
            case 'vimeo.com':
                $segments = explode('/', trim((string) $path, '/'));
                $videoId  = $segments[0] ?? '';
                break;
            default:
                throw new \RuntimeException('Saat ini hanya mendukung URL YouTube atau Vimeo.');
        }

        $videoId = trim($videoId);
        if ($videoId === '') {
            throw new \RuntimeException('Tidak dapat membaca ID video dari URL.');
        }

        if ($host === 'vimeo.com' || $host === 'player.vimeo.com') {
            if (! ctype_digit($videoId)) {
                throw new \RuntimeException('ID video Vimeo tidak valid.');
            }

            $embedUrl = 'https://player.vimeo.com/video/' . $videoId;
            $provider = 'vimeo';
        } else {
            $embedUrl = 'https://www.youtube.com/embed/' . $videoId;
            $provider = 'youtube';
        }

        return [
            'provider'   => $provider,
            'video_id'   => $videoId,
            'embed_url'  => $embedUrl,
            'source_url' => $validated,
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $mediaItems
     */
    private function refreshCoverThumbnail(NewsModel $model, int $newsId, array $mediaItems, ?string $fallback = null): void
    {
        $coverPath  = null;
        $firstImage = null;

        foreach ($mediaItems as $media) {
            if (($media['media_type'] ?? '') !== 'image') {
                continue;
            }

            $path = trim((string) ($media['file_path'] ?? ''));
            if ($path === '') {
                continue;
            }

            if ($firstImage === null) {
                $firstImage = $path;
            }

            if ((int) ($media['is_cover'] ?? 0) === 1) {
                $coverPath = $path;
                break;
            }
        }

        if ($coverPath) {
            $model->update($newsId, ['thumbnail' => $coverPath]);
        } elseif ($firstImage && $fallback !== $firstImage) {
            $model->update($newsId, ['thumbnail' => $firstImage]);
        } elseif ($fallback !== null) {
            $model->update($newsId, ['thumbnail' => $fallback]);
        }
    }

    /**
     * @param array<int,array<string,mixed>> $mediaItems
     * @return array<int,array<string,mixed>>
     */
    private function prepareMediaForForm(array $mediaItems): array
    {
        foreach ($mediaItems as &$media) {
            $metadata = [];
            if (! empty($media['metadata'])) {
                $decoded = json_decode((string) $media['metadata'], true);
                if (is_array($decoded)) {
                    $metadata = $decoded;
                }
            }

            $media['metadata']   = $metadata;
            $media['caption']    = (string) ($media['caption'] ?? '');
            $media['sort_order'] = (int) ($media['sort_order'] ?? 0);
            $media['is_cover']   = (int) ($media['is_cover'] ?? 0);
            $media['source_url'] = (string) ($metadata['source_url'] ?? $media['external_url'] ?? '');
        }
        unset($media);

        return $mediaItems;
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
            'mediaItems'         => [],
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

        $mediaPayload = [
            'changes'        => ['delete' => [], 'update' => [], 'insert' => []],
            'deleted_paths'  => [],
            'uploaded_paths' => [],
        ];

        try {
            $mediaPayload = $this->collectMediaChanges([]);
        } catch (\RuntimeException $exception) {
            foreach ($mediaPayload['uploaded_paths'] as $path) {
                $this->deleteFile($path);
            }
            if ($thumbPath) {
                $this->deleteFile($thumbPath);
            }

            return redirect()->back()->withInput()->with('error', $exception->getMessage());
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

            $mediaModel = model(NewsMediaModel::class);
            $hasChanges = ! empty($mediaPayload['changes']['delete'])
                || ! empty($mediaPayload['changes']['update'])
                || ! empty($mediaPayload['changes']['insert']);

            $syncedMedia = [];
            if ($hasChanges) {
                $syncedMedia = $mediaModel->syncMedia($newsId, $mediaPayload['changes']);
            }

            foreach ($mediaPayload['deleted_paths'] as $path) {
                $this->deleteFile($path);
            }

            if ($syncedMedia !== []) {
                $this->refreshCoverThumbnail($model, $newsId, $syncedMedia, $thumbPath);
            } elseif ($thumbPath) {
                $model->update($newsId, ['thumbnail' => $thumbPath]);
            }
        } else {
            foreach ($mediaPayload['uploaded_paths'] as $path) {
                $this->deleteFile($path);
            }
            if ($thumbPath) {
                $this->deleteFile($thumbPath);
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
        $mediaItems         = $this->prepareMediaForForm($mediaItems);

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
            $validTagIds    = model(NewsTagModel::class)->whereIn('id', $existingTagIds)->findColumn('id') ?? [];
            $existingTagIds = array_values(array_unique(array_map('intval', $validTagIds)));
        }
        $newTagIds = $this->createTagsFromInput((string) $this->request->getPost('new_tags'));
        $allTagIds = array_values(array_unique(array_merge($existingTagIds, $newTagIds)));

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

        $mediaModel          = model(NewsMediaModel::class);
        $existingMediaRaw    = $mediaModel->byNews($id);
        $existingMedia       = $this->prepareMediaForForm($existingMediaRaw);
        $mediaPayload        = [
            'changes'        => ['delete' => [], 'update' => [], 'insert' => []],
            'deleted_paths'  => [],
            'uploaded_paths' => [],
        ];

        $oldThumbnail      = $item['thumbnail'] ?? null;
        $thumbnailReplaced = false;

        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid()) {
            if (! $this->hasAllowedMime($file)) {
                return redirect()->back()->withInput()->with('error', 'Jenis file thumbnail tidak diizinkan.');
            }

            $newPath = $this->moveThumbnail($file);
            if (! $newPath) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan thumbnail.');
            }

            $data['thumbnail'] = $newPath;
            $thumbnailReplaced = true;
        }

        try {
            $mediaPayload = $this->collectMediaChanges($existingMedia);
        } catch (\RuntimeException $exception) {
            foreach ($mediaPayload['uploaded_paths'] as $path) {
                $this->deleteFile($path);
            }
            if ($thumbnailReplaced && isset($data['thumbnail'])) {
                $this->deleteFile($data['thumbnail']);
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
            $this->deleteFile($path);
        }

        $this->refreshCoverThumbnail($model, $id, $syncedMedia, $data['thumbnail'] ?? $oldThumbnail);

        if ($thumbnailReplaced && $oldThumbnail && ($data['thumbnail'] ?? '') !== $oldThumbnail) {
            $this->deleteFile($oldThumbnail);
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
                    $this->deleteFile($media['file_path']);
                }
            }

            $mediaModel->where('news_id', $id)->delete();

            $this->deleteFile($item['thumbnail'] ?? null);
            $model->delete($id);
            log_activity('news.delete', 'Menghapus berita: ' . ($item['title'] ?? ''));
        }

        return redirect()->to(site_url('admin/news'))->with('message', 'Berita berhasil dihapus.');
    }
}




