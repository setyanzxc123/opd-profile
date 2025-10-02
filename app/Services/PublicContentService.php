<?php

namespace App\Services;

use App\Models\DocumentModel;
use App\Models\GalleryModel;
use App\Models\NewsModel;
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
                return $model
                    ->orderBy('published_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->findAll($limit) ?: [];
            } catch (Throwable $throwable) {
                log_message('warning', 'Failed to fetch recent news: {error}', ['error' => $throwable->getMessage()]);

                return [];
            }
        });
    }

    public function paginatedNews(int $perPage = 6, ?string $search = null): array
    {
        $perPage = max(1, $perPage);

        try {
            $model = model(NewsModel::class);
            $model->orderBy('published_at', 'desc')
                  ->orderBy('created_at', 'desc');

            if ($search !== null && $search !== '') {
                $model->like('title', $search)
                      ->orLike('content', $search);
            }

            $articles = $model->paginate($perPage);

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
}

