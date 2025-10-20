<?php

namespace App\Services;

use App\Models\DocumentModel;
use App\Models\GalleryModel;
use App\Models\NewsCategoryModel;
use App\Models\NewsModel;
use App\Models\NewsTagModel;
use App\Models\OpdProfileModel;
use App\Models\ServiceModel;
use CodeIgniter\Cache\CacheInterface;
use Throwable;

class PublicContentService
{
    private CacheInterface $cache;
    private int $ttl;

    public function __construct(?CacheInterface $cache = null, int $cacheTtl = 300)
    {
        $this->cache = $cache ?? cache();
        $this->ttl   = max(30, $cacheTtl);
    }

    public function latestProfile(): ?array
    {
        return $this->cache->remember('public_profile_latest', $this->ttl, static function () {
            try {
                return model(OpdProfileModel::class)
                    ->orderBy('id', 'desc')
                    ->first() ?: null;
            } catch (Throwable $throwable) {
                log_message('warning', 'Failed to fetch profile: {error}', ['error' => $throwable->getMessage()]);

                return null;
            }
        });
    }

    public function featuredServices(int $limit = 4): array
    {
        $limit = max(1, $limit);
        $cacheKey = sprintf('public_services_featured_%d', $limit);

        return $this->cache->remember($cacheKey, $this->ttl, function () use ($limit) {
            try {
                $model = model(ServiceModel::class);
                $builder = $model
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('title', 'asc');

                if ($this->servicesHaveActiveColumn()) {
                    $builder = $builder->where('is_active', 1);
                }

                return $builder->findAll($limit) ?: [];
            } catch (Throwable $throwable) {
                log_message('warning', 'Failed to fetch featured services: {error}', ['error' => $throwable->getMessage()]);

                return [];
            }
        });
    }

    public function allActiveServices(): array
    {
        return $this->cache->remember('public_services_all', $this->ttl, function () {
            try {
                $model = model(ServiceModel::class);
                $builder = $model
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('title', 'asc');

                if ($this->servicesHaveActiveColumn()) {
                    $builder = $builder->where('is_active', 1);
                }

                return $builder->findAll() ?: [];
            } catch (Throwable $throwable) {
                log_message('warning', 'Failed to fetch services: {error}', ['error' => $throwable->getMessage()]);

                return [];
            }
        });
    }

    public function recentNews(int $limit = 4): array
    {
        $limit = max(1, $limit);
        $cacheKey = sprintf('public_news_recent_%d', $limit);

        return $this->cache->remember($cacheKey, $this->ttl, function () use ($limit) {
            try {
                $model = model(NewsModel::class);
                $items = $model
                    ->orderBy('published_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->findAll($limit) ?: [];

                return $this->hydrateNewsRelations($items);
            } catch (Throwable $throwable) {
                log_message('warning', 'Failed to fetch recent news: {error}', ['error' => $throwable->getMessage()]);

                return [];
            }
        });
    }

    public function popularNews(int $limit = 5): array
    {
        $limit = max(1, $limit);
        $cacheKey = sprintf('public_news_popular_%d', $limit);

        return $this->cache->remember($cacheKey, $this->ttl, function () use ($limit) {
            try {
                $model = model(NewsModel::class);

                if ($this->newsHasViewCountColumn()) {
                    $model->orderBy('view_count', 'desc');
                }

                $items = $model
                    ->orderBy('published_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->findAll($limit) ?: [];

                return $this->hydrateNewsRelations($items);
            } catch (Throwable $throwable) {
                log_message('warning', 'Failed to fetch popular news: {error}', ['error' => $throwable->getMessage()]);

                return [];
            }
        });
    }

    public function paginatedNews(int $perPage = 6, ?string $search = null, ?int $categoryId = null, ?int $tagId = null): array
    {
        $perPage = max(1, $perPage);

        try {
            $model = model(NewsModel::class);
            $model->select('news.*')
                  ->orderBy('published_at', 'desc')
                  ->orderBy('created_at', 'desc');

            if ($categoryId) {
                $model->join('news_category_map', 'news_category_map.news_id = news.id', 'inner')
                      ->where('news_category_map.category_id', $categoryId);
            }

            if ($tagId) {
                $model->join('news_tag_map', 'news_tag_map.news_id = news.id', 'inner')
                      ->where('news_tag_map.tag_id', $tagId);
            }

            if ($search !== null && $search !== '') {
                $model->groupStart()
                      ->like('title', $search)
                      ->orLike('content', $search)
                      ->groupEnd();
            }

            $model->groupBy('news.id');

            $articles = $model->paginate($perPage);
            $articles = $this->hydrateNewsRelations($articles ?: []);

            return [
                'articles' => $articles ?: [],
                'pager'    => $model->pager,
            ];
        } catch (Throwable $throwable) {
            log_message('warning', 'Failed to fetch paginated news: {error}', ['error' => $throwable->getMessage()]);

            return [
                'articles' => [],
                'pager'    => null,
            ];
        }
    }

    public function searchNews(string $query, int $limit = 5): array
    {
        $keyword = trim($query);

        if ($keyword === '') {
            return [];
        }

        $limit = max(1, min(10, $limit));

        try {
            $model = model(NewsModel::class);

            $items = $model
                ->orderBy('published_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->groupStart()
                    ->like('title', $keyword)
                    ->orLike('content', $keyword)
                ->groupEnd()
                ->findAll($limit) ?: [];

            return $this->hydrateNewsRelations($items);
        } catch (Throwable $throwable) {
            log_message('warning', 'Failed to search news: {error}', ['error' => $throwable->getMessage()]);

            return [];
        }
    }

    public function newsCategories(bool $onlyActive = true): array
    {
        $cacheKey = $onlyActive ? 'public_news_categories_active' : 'public_news_categories_all';

        return $this->cache->remember($cacheKey, $this->ttl, function () use ($onlyActive) {
            try {
                $model = model(NewsCategoryModel::class);
                $builder = $model->orderBy('sort_order', 'asc')
                                 ->orderBy('name', 'asc');

                if ($onlyActive) {
                    $builder->where('is_active', 1);
                }

                return $builder->findAll() ?: [];
            } catch (Throwable $throwable) {
                log_message('warning', 'Failed to fetch news categories: {error}', ['error' => $throwable->getMessage()]);

                return [];
            }
        });
    }

    public function newsTags(): array
    {
        return $this->cache->remember('public_news_tags_all', $this->ttl, static function () {
            try {
                return model(NewsTagModel::class)
                    ->orderBy('name', 'asc')
                    ->findAll() ?: [];
            } catch (Throwable $throwable) {
                log_message('warning', 'Failed to fetch news tags: {error}', ['error' => $throwable->getMessage()]);

                return [];
            }
        });
    }

    public function findNewsCategoryBySlug(string $slug): ?array
    {
        if ($slug === '') {
            return null;
        }

        try {
            return model(NewsCategoryModel::class)
                ->where('slug', $slug)
                ->first() ?: null;
        } catch (Throwable $throwable) {
            log_message('warning', 'Failed to lookup news category: {error}', ['error' => $throwable->getMessage()]);

            return null;
        }
    }

    public function findNewsTagBySlug(string $slug): ?array
    {
        if ($slug === '') {
            return null;
        }

        try {
            return model(NewsTagModel::class)
                ->where('slug', $slug)
                ->first() ?: null;
        } catch (Throwable $throwable) {
            log_message('warning', 'Failed to lookup news tag: {error}', ['error' => $throwable->getMessage()]);

            return null;
        }
    }

    public function newsBySlug(string $slug): ?array
    {
        if ($slug === '') {
            return null;
        }

        try {
            $item = model(NewsModel::class)
                ->where('slug', $slug)
                ->first();

            if (! $item) {
                return null;
            }

            $enriched = $this->hydrateNewsRelations([$item]);

            return $enriched[0] ?? null;
        } catch (Throwable $throwable) {
            log_message('warning', 'Failed to fetch news by slug: {error}', ['error' => $throwable->getMessage()]);

            return null;
        }
    }

    public function relatedNews(int $newsId, ?int $primaryCategoryId = null, array $tagIds = [], int $limit = 4): array
    {
        $limit = max(1, $limit);
        $primaryCategoryId = $primaryCategoryId !== null ? (int) $primaryCategoryId : null;
        $tagIds            = array_values(array_unique(array_filter(
            array_map(static fn ($value) => (int) $value, $tagIds),
            static fn (int $value): bool => $value > 0
        )));

        try {
            $model = model(NewsModel::class);
            $model->select('news.*')
                  ->where('news.id !=', $newsId)
                  ->orderBy('published_at', 'desc')
                  ->orderBy('created_at', 'desc');

            if ($primaryCategoryId !== null) {
                $model->join('news_category_map', 'news_category_map.news_id = news.id', 'left');
            }

            if ($tagIds !== []) {
                $model->join('news_tag_map', 'news_tag_map.news_id = news.id', 'left');
            }

            if ($primaryCategoryId !== null || $tagIds !== []) {
                $model->groupStart();

                if ($primaryCategoryId !== null) {
                    $model->where('news_category_map.category_id', $primaryCategoryId);
                }

                if ($tagIds !== []) {
                    $method = $primaryCategoryId !== null ? 'orWhereIn' : 'whereIn';
                    $model->{$method}('news_tag_map.tag_id', $tagIds);
                }

                $model->groupEnd();
            }

            $model->groupBy('news.id');

            $fetchLimit = max($limit * 3, $limit);
            $rows       = $model->findAll($fetchLimit) ?: [];
            $rows       = $this->hydrateNewsRelations($rows);

            if ($rows === []) {
                return [];
            }

            if ($primaryCategoryId === null && $tagIds === []) {
                return array_slice($rows, 0, $limit);
            }

            $tagLookup = $tagIds !== [] ? array_flip($tagIds) : [];

            foreach ($rows as &$row) {
                $score = 0;

                if ($primaryCategoryId !== null) {
                    $primary = $row['primary_category']['id'] ?? null;
                    if ((int) $primary === $primaryCategoryId) {
                        $score += 3;
                    }

                    foreach ($row['categories'] ?? [] as $category) {
                        if ((int) ($category['id'] ?? 0) === $primaryCategoryId) {
                            $score += 2;
                            break;
                        }
                    }
                }

                if ($tagLookup) {
                    $tagMatches = 0;
                    foreach ($row['tags'] ?? [] as $tag) {
                        $tagId = (int) ($tag['id'] ?? 0);
                        if (isset($tagLookup[$tagId])) {
                            $tagMatches++;
                        }
                    }
                    $score += $tagMatches;
                }

                $row['_relevance']    = $score;
                $row['_published_ts'] = isset($row['published_at']) ? strtotime((string) $row['published_at']) ?: 0 : 0;
                $row['_created_ts']   = isset($row['created_at']) ? strtotime((string) $row['created_at']) ?: 0 : 0;
            }
            unset($row);

            usort($rows, static function (array $a, array $b): int {
                if ($a['_relevance'] !== $b['_relevance']) {
                    return $b['_relevance'] <=> $a['_relevance'];
                }

                if ($a['_published_ts'] !== $b['_published_ts']) {
                    return $b['_published_ts'] <=> $a['_published_ts'];
                }

                return $b['_created_ts'] <=> $a['_created_ts'];
            });

            $rows = array_slice($rows, 0, $limit);

            foreach ($rows as &$row) {
                unset($row['_relevance'], $row['_published_ts'], $row['_created_ts']);
            }
            unset($row);

            return $rows;
        } catch (Throwable $throwable) {
            log_message('warning', 'Failed to fetch related news: {error}', ['error' => $throwable->getMessage()]);

            return [];
        }
    }

    public function recentGalleries(int $limit = 4): array
    {
        $limit = max(1, $limit);
        $cacheKey = sprintf('public_galleries_recent_%d', $limit);

        return $this->cache->remember($cacheKey, $this->ttl, function () use ($limit) {
            try {
                return model(GalleryModel::class)
                    ->orderBy('created_at', 'desc')
                    ->findAll($limit) ?: [];
            } catch (Throwable $throwable) {
                log_message('warning', 'Failed to fetch galleries: {error}', ['error' => $throwable->getMessage()]);

                return [];
            }
        });
    }

    public function recentDocuments(int $limit = 4): array
    {
        $limit = max(1, $limit);
        $cacheKey = sprintf('public_documents_recent_%d', $limit);

        return $this->cache->remember($cacheKey, $this->ttl, function () use ($limit) {
            try {
                return model(DocumentModel::class)
                    ->orderBy('year', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->findAll($limit) ?: [];
            } catch (Throwable $throwable) {
                log_message('warning', 'Failed to fetch documents: {error}', ['error' => $throwable->getMessage()]);

                return [];
            }
        });
    }

    protected function newsHasViewCountColumn(): bool
    {
        static $result;

        if ($result !== null) {
            return $result;
        }

        $cacheKey = 'schema_news_has_view_count';

        $result = (bool) $this->cache->remember($cacheKey, 86400, static function () {
            try {
                $db     = db_connect();
                $fields = $db->getFieldNames('news');
                $db->close();

                return in_array('view_count', $fields, true);
            } catch (Throwable $throwable) {
                log_message('debug', 'Unable to inspect news table: {error}', ['error' => $throwable->getMessage()]);

                return false;
            }
        });

        return $result;
    }

    private function servicesHaveActiveColumn(): bool
    {
        static $result;

        if ($result !== null) {
            return $result;
        }

        $cacheKey = 'schema_services_has_active';

        $result = (bool) $this->cache->remember($cacheKey, 86400, static function () {
            try {
                $db = db_connect();
                $fields = $db->getFieldNames('services');
                $db->close();

                return in_array('is_active', $fields, true);
            } catch (Throwable $throwable) {
                log_message('debug', 'Unable to inspect services table: {error}', ['error' => $throwable->getMessage()]);

                return false;
            }
        });

        return $result;
    }

    /**
     * @param array<int,int> $newsIds
     * @return array<int,array<int,array<string,mixed>>>
     */
    private function fetchCategoriesForNews(array $newsIds): array
    {
        if ($newsIds === []) {
            return [];
        }

        $db = db_connect();
        try {
            $rows = $db->table('news_category_map')
                ->select('news_category_map.news_id, news_categories.id, news_categories.name, news_categories.slug, news_categories.description, news_categories.is_active, news_categories.sort_order')
                ->join('news_categories', 'news_categories.id = news_category_map.category_id', 'inner')
                ->whereIn('news_category_map.news_id', $newsIds)
                ->orderBy('news_categories.sort_order', 'asc')
                ->orderBy('news_categories.name', 'asc')
                ->get()
                ->getResultArray();
        } finally {
            $db->close();
        }

        $grouped = [];
        foreach ($rows as $row) {
            if ((int) ($row['is_active'] ?? 0) !== 1) {
                continue;
            }

            $newsId = (int) $row['news_id'];
            $grouped[$newsId][] = [
                'id'          => (int) $row['id'],
                'name'        => (string) $row['name'],
                'slug'        => (string) $row['slug'],
                'description' => (string) ($row['description'] ?? ''),
            ];
        }

        return $grouped;
    }

    /**
     * @param array<int,int> $newsIds
     * @return array<int,array<int,array<string,mixed>>>
     */
    private function fetchTagsForNews(array $newsIds): array
    {
        if ($newsIds === []) {
            return [];
        }

        $db = db_connect();
        try {
            $rows = $db->table('news_tag_map')
                ->select('news_tag_map.news_id, news_tags.id, news_tags.name, news_tags.slug')
                ->join('news_tags', 'news_tags.id = news_tag_map.tag_id', 'inner')
                ->whereIn('news_tag_map.news_id', $newsIds)
                ->orderBy('news_tags.name', 'asc')
                ->get()
                ->getResultArray();
        } finally {
            $db->close();
        }

        $grouped = [];
        foreach ($rows as $row) {
            $newsId = (int) $row['news_id'];
            $grouped[$newsId][] = [
                'id'   => (int) $row['id'],
                'name' => (string) $row['name'],
                'slug' => (string) $row['slug'],
            ];
        }

        return $grouped;
    }

    /**
     * @param array<int,int> $newsIds
     * @return array<int,array<int,array<string,mixed>>>
     */
    private function fetchMediaForNews(array $newsIds): array
    {
        if ($newsIds === []) {
            return [];
        }

        $db = db_connect();
        try {
            $rows = $db->table('news_media')
                ->select('id, news_id, media_type, file_path, external_url, caption, metadata, is_cover, sort_order')
                ->whereIn('news_id', $newsIds)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->getResultArray();
        } finally {
            $db->close();
        }

        $grouped = [];
        foreach ($rows as $row) {
            $newsId = (int) ($row['news_id'] ?? 0);
            if ($newsId === 0) {
                continue;
            }

            $metadata = [];
            if (! empty($row['metadata'])) {
                $decoded = json_decode((string) $row['metadata'], true);
                if (is_array($decoded)) {
                    $metadata = $decoded;
                }
            }

            $caption = sanitize_plain_text((string) ($row['caption'] ?? ''));
            $caption = $caption !== '' ? $caption : '';

            $grouped[$newsId][] = [
                'id'           => (int) ($row['id'] ?? 0),
                'media_type'   => (string) ($row['media_type'] ?? 'image'),
                'file_path'    => (string) ($row['file_path'] ?? ''),
                'external_url' => (string) ($row['external_url'] ?? ''),
                'caption'      => $caption,
                'metadata'     => $metadata,
                'is_cover'     => (int) ($row['is_cover'] ?? 0),
                'sort_order'   => (int) ($row['sort_order'] ?? 0),
            ];
        }

        return $grouped;
    }

    /**
     * @param array<int,array<string,mixed>> $newsItems
     * @return array<int,array<string,mixed>>
     */
    protected function hydrateNewsRelations(array $newsItems): array
    {
        if ($newsItems === []) {
            return [];
        }

        helper(['news', 'content']);

        $newsIds = [];
        foreach ($newsItems as $row) {
            if (isset($row['id'])) {
                $newsIds[] = (int) $row['id'];
            }
        }

        $categoriesByNews = $this->fetchCategoriesForNews($newsIds);
        $tagsByNews       = $this->fetchTagsForNews($newsIds);
        $mediaByNews      = $this->fetchMediaForNews($newsIds);

        $categoryIndex = [];
        foreach ($categoriesByNews as $categories) {
            foreach ($categories as $category) {
                $categoryIndex[$category['id']] = $category;
            }
        }

        foreach ($newsItems as &$row) {
            $newsId = (int) ($row['id'] ?? 0);
            $row['categories'] = $categoriesByNews[$newsId] ?? [];
            $row['tags']       = $tagsByNews[$newsId] ?? [];
            $primaryId         = (int) ($row['primary_category_id'] ?? 0);
            $row['primary_category'] = $primaryId && isset($categoryIndex[$primaryId]) ? $categoryIndex[$primaryId] : null;
            $row['media']      = $mediaByNews[$newsId] ?? [];

            $row['public_author']    = sanitize_plain_text($row['public_author'] ?? '');
            $row['source']           = sanitize_plain_text($row['source'] ?? '');
            $row['excerpt']          = news_trim_excerpt($row['excerpt'] ?? null, (string) ($row['content'] ?? ''));
            $row['meta_title']       = news_resolve_meta_title($row['meta_title'] ?? null, (string) ($row['title'] ?? ''));
            $row['meta_description'] = news_resolve_meta_description($row['meta_description'] ?? null, $row['excerpt'], (string) ($row['content'] ?? ''));
            $row['meta_keywords']    = isset($row['meta_keywords']) && $row['meta_keywords'] !== null
                ? sanitize_plain_text($row['meta_keywords'])
                : '';
        }
        unset($row);

        return $newsItems;
    }
}
