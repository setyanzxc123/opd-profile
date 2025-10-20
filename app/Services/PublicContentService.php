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

    public function relatedNews(int $newsId, ?int $categoryId = null, int $limit = 3): array
    {
        $limit = max(1, $limit);

        try {
            $model = model(NewsModel::class);
            $model->select('news.*')
                  ->where('news.id !=', $newsId)
                  ->orderBy('published_at', 'desc')
                  ->orderBy('created_at', 'desc');

            if ($categoryId) {
                $model->join('news_category_map', 'news_category_map.news_id = news.id', 'inner')
                      ->where('news_category_map.category_id', $categoryId);
            }

            $model->groupBy('news.id');

            $rows = $model->findAll($limit) ?: [];

            return $this->hydrateNewsRelations($rows);
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
     * @param array<int,array<string,mixed>> $newsItems
     * @return array<int,array<string,mixed>>
     */
    private function hydrateNewsRelations(array $newsItems): array
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
