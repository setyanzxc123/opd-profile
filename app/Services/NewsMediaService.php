<?php

namespace App\Services;

use App\Models\NewsModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\IncomingRequest;
use Config\Services;
use RuntimeException;

class NewsMediaService
{
    private const THUMB_UPLOAD_DIR = 'uploads/news';
    private const MEDIA_UPLOAD_DIR = 'uploads/news-media';
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/pjpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    public function isAllowedImageMime(UploadedFile $file): bool
    {
        $mime = strtolower((string) $file->getMimeType());

        return in_array($mime, self::ALLOWED_IMAGE_MIMES, true);
    }

    public function moveThumbnail(UploadedFile $file, ?string $originalPath = null): ?string
    {
        $targetDir = $this->ensureDirectory(self::THUMB_UPLOAD_DIR);
        $newName   = $file->getRandomName();

        try {
            $file->move($targetDir, $newName, true);
        } catch (\Throwable $throwable) {
            log_message('error', 'Failed to store news thumbnail: {error}', ['error' => $throwable->getMessage()]);

            return null;
        }

        $relativePath = self::THUMB_UPLOAD_DIR . '/' . $newName;
        $fullPath     = $targetDir . DIRECTORY_SEPARATOR . $newName;

        try {
            $this->optimiseImage($fullPath);
        } catch (\Throwable $throwable) {
            log_message('error', 'Failed to optimize news thumbnail: {error}', ['error' => $throwable->getMessage()]);
        }

        if ($originalPath && $originalPath !== $relativePath) {
            $this->deleteFile($originalPath);
        }

        return $relativePath;
    }

    public function moveMediaImage(UploadedFile $file): ?string
    {
        $targetDir = $this->ensureDirectory(self::MEDIA_UPLOAD_DIR);
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
            $this->optimiseImage($fullPath);
        } catch (\Throwable $throwable) {
            log_message('warning', 'Failed to optimise news media image: {error}', ['error' => $throwable->getMessage()]);
        }

        return $relativePath;
    }

    /**
     * @param array<int,array<string,mixed>> $existingMedia
     * @return array{changes: array<string,array>, deleted_paths: array<int,string>, uploaded_paths: array<int,string>}
     */
    public function collectChanges(IncomingRequest $request, array $existingMedia): array
    {
        helper('content');

        $existingIndex = [];
        foreach ($existingMedia as $media) {
            $existingIndex[(int) ($media['id'] ?? 0)] = $media;
        }

        $coverRef = (string) $request->getPost('media_cover');

        $result = [
            'changes'        => ['delete' => [], 'update' => [], 'insert' => []],
            'deleted_paths'  => [],
            'uploaded_paths' => [],
        ];

        $pendingImageUploads = [];

        try {
            $existingInput = $request->getPost('media_existing');
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
                    $result['changes']['delete'][] = $numericId;
                    if (($existingIndex[$numericId]['media_type'] ?? '') === 'image' && ! empty($existingIndex[$numericId]['file_path'])) {
                        $result['deleted_paths'][] = (string) $existingIndex[$numericId]['file_path'];
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
                        throw new RuntimeException('URL video tidak boleh dikosongkan.');
                    }

                    $normalised = $this->normaliseVideoUrl($urlInput);
                    $update['external_url'] = $normalised['embed_url'];
                    $update['metadata']     = json_encode($normalised, JSON_UNESCAPED_SLASHES);
                }

                $result['changes']['update'][] = $update;
            }

            $newImageFiles    = $request->getFileMultiple('media_new_images_files') ?? [];
            $newImageCaptions = $request->getPost('media_new_images_caption') ?? [];
            $newImageSorts    = $request->getPost('media_new_images_sort') ?? [];
            $newImageUids     = $request->getPost('media_new_images_uid') ?? [];

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

                    throw new RuntimeException('Gagal memproses salah satu file gambar media.');
                }

                if ($file->hasMoved()) {
                    throw new RuntimeException('File gambar media sudah dipindahkan dan tidak dapat diproses ulang.');
                }

                if (! $this->isAllowedImageMime($file)) {
                    throw new RuntimeException('Format gambar media tidak diizinkan.');
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

            $newVideoUrls     = $request->getPost('media_new_videos_url') ?? [];
            $newVideoCaptions = $request->getPost('media_new_videos_caption') ?? [];
            $newVideoSorts    = $request->getPost('media_new_videos_sort') ?? [];
            $newVideoUids     = $request->getPost('media_new_videos_uid') ?? [];

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

                $result['changes']['insert'][] = [
                    'media_type'   => 'video',
                    'external_url' => $normalised['embed_url'],
                    'metadata'     => json_encode($normalised, JSON_UNESCAPED_SLASHES),
                    'caption'      => $caption !== '' ? $caption : null,
                    'sort_order'   => $sortOrder,
                    'is_cover'     => $coverRef === 'new-video|' . $uid ? 1 : 0,
                ];
            }

            foreach ($pendingImageUploads as $pending) {
                $path = $this->moveMediaImage($pending['file']);
                if (! $path) {
                    throw new RuntimeException('Gagal menyimpan salah satu gambar media.');
                }

                $result['uploaded_paths'][] = $path;

                $result['changes']['insert'][] = [
                    'media_type' => 'image',
                    'file_path'  => $path,
                    'caption'    => $pending['caption'],
                    'sort_order' => $pending['sort'],
                    'is_cover'   => $pending['is_cover'],
                ];
            }
        } catch (\Throwable $throwable) {
            foreach ($result['uploaded_paths'] as $path) {
                $this->deleteFile($path);
            }

            throw $throwable instanceof RuntimeException
                ? $throwable
                : new RuntimeException('Gagal memproses unggahan gambar media.', 0, $throwable);
        }

        return $result;
    }

    /**
     * @param array<int,array<string,mixed>> $mediaItems
     */
    public function refreshCoverThumbnail(NewsModel $model, int $newsId, array $mediaItems, ?string $fallback = null): void
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
    public function prepareMediaForForm(array $mediaItems): array
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

    public function deleteFile(?string $relativePath): void
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

    /**
     * @return array<string,string>
     */
    private function normaliseVideoUrl(string $url): array
    {
        $url = trim($url);
        if ($url === '') {
            throw new RuntimeException('URL video tidak boleh kosong.');
        }

        $validated = filter_var($url, FILTER_VALIDATE_URL);
        if ($validated === false) {
            throw new RuntimeException('URL video tidak valid.');
        }

        $parts = parse_url($validated);
        $host  = strtolower($parts['host'] ?? '');
        if ($host === '') {
            throw new RuntimeException('URL video tidak valid.');
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
                throw new RuntimeException('Saat ini hanya mendukung URL YouTube atau Vimeo.');
        }

        if ($videoId === '') {
            throw new RuntimeException('Tidak dapat menentukan ID video dari URL yang diberikan.');
        }

        $provider = str_contains($host, 'vimeo') ? 'vimeo' : 'youtube';
        $embedUrl = $provider === 'youtube'
            ? 'https://www.youtube.com/embed/' . $videoId
            : 'https://player.vimeo.com/video/' . $videoId;

        return [
            'provider'   => $provider,
            'video_id'   => $videoId,
            'embed_url'  => $embedUrl,
            'source_url' => $validated,
        ];
    }

    private function ensureDirectory(string $relativePath): string
    {
        $base   = rtrim(FCPATH, DIRECTORY_SEPARATOR);
        $target = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (! is_dir($target)) {
            @mkdir($target, 0775, true);
        }

        return $target;
    }

    private function optimiseImage(string $path): void
    {
        $image = Services::image();
        $info  = @getimagesize($path);
        $width = $info[0] ?? null;

        $editor = $image->withFile($path);

        if ($width !== null && $width <= 1200) {
            $editor->save($path, 85);

            return;
        }

        $editor
            ->resize(1200, 630, true, 'width')
            ->save($path, 85);
    }
}

