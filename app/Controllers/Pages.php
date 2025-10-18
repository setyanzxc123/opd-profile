<?php

namespace App\Controllers;

use App\Services\PublicContentService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

class Pages extends BaseController
{
    private PublicContentService $contentService;

    public function __construct()
    {
        $this->contentService = new PublicContentService();
    }

    public function profil(): string
    {
        $profile = $this->contentService->latestProfile();

        if (! $profile) {
            $profile = [
                'name'        => 'Profil OPD belum tersedia',
                'description' => 'Profil resmi OPD sedang diperbarui. Silakan kembali lagi untuk melihat informasi terbaru mengenai visi, misi, dan struktur organisasi.',
                'vision'      => null,
                'mission'     => null,
                'address'     => null,
                'phone'       => null,
                'email'       => null,
            ];
        }

        return view('public/profile', [
            'title'         => 'Profil OPD',
            'profile'       => $profile,
            'footerProfile' => $profile,
        ]);
    }

    public function layanan(): string
    {
        $services = $this->contentService->allActiveServices();
        $profile  = $this->contentService->latestProfile();

        return view('public/services', [
            'title'         => 'Layanan Publik',
            'services'      => $services,
            'footerProfile' => $profile,
            'profile'       => $profile,
        ]);
    }

    private function renderBeritaArchive(?string $categorySlug = null, ?string $tagSlug = null): string
    {
        $searchParam    = trim((string) $this->request->getGet('q'));
        $categoryParam  = $categorySlug ?? trim((string) $this->request->getGet('kategori'));
        $tagParam       = $tagSlug ?? trim((string) $this->request->getGet('tag'));

        $activeCategory = $categoryParam !== '' ? $this->contentService->findNewsCategoryBySlug($categoryParam) : null;
        $activeTag      = $tagParam !== '' ? $this->contentService->findNewsTagBySlug($tagParam) : null;

        $news    = $this->contentService->paginatedNews(
            6,
            $searchParam,
            $activeCategory['id'] ?? null,
            $activeTag['id'] ?? null
        );
        $profile    = $this->contentService->latestProfile();
        $categories = $this->contentService->newsCategories();
        $tags       = $this->contentService->newsTags();

        return view('public/news/index', [
            'title'          => 'Berita Terbaru',
            'articles'       => $news['articles'],
            'pager'          => $news['pager'],
            'search'         => $searchParam,
            'categories'     => $categories,
            'tags'           => $tags,
            'activeCategory' => $activeCategory,
            'activeTag'      => $activeTag,
            'filters'        => [
                'category' => $activeCategory['slug'] ?? null,
                'tag'      => $activeTag['slug'] ?? null,
            ],
            'footerProfile'  => $profile,
            'profile'        => $profile,
        ]);
    }

    public function berita(): string
    {
        return $this->renderBeritaArchive();
    }

    public function beritaKategori(string $slug): string
    {
        return $this->renderBeritaArchive($slug, null);
    }

    public function beritaTag(string $slug): string
    {
        return $this->renderBeritaArchive(null, $slug);
    }

    public function beritaSearch(): ResponseInterface
    {
        $query = trim((string) $this->request->getGet('q'));
        $limitParam = $this->request->getGet('limit');
        $limit = is_numeric($limitParam) ? (int) $limitParam : 5;
        $limit = max(1, min(10, $limit));

        if ($query === '') {
            return $this->response->setJSON(['results' => []]);
        }

        helper(['url']);

        $articles = $this->contentService->searchNews($query, $limit);

        $results = array_map(static function (array $article): array {
            $content = isset($article['content']) ? strip_tags((string) $article['content']) : '';
            $content = trim(preg_replace('/\s+/', ' ', $content));

            if (function_exists('mb_substr')) {
                $snippet = trim(mb_substr($content, 0, 120));
                $hasMore = mb_strlen($content) > 120;
            } else {
                $snippet = trim(substr($content, 0, 120));
                $hasMore = strlen($content) > 120;
            }

            if ($snippet !== '' && $hasMore) {
                $snippet = rtrim($snippet, " \t\n\r\0\x0B,.") . '…';
            }

            return [
                'title'         => $article['title'] ?? '',
                'url'           => site_url('berita/' . ($article['slug'] ?? '')),
                'published_at'  => $article['published_at'] ?? null,
                'thumbnail'     => $article['thumbnail'] ?? null,
                'snippet'       => $snippet,
            ];
        }, $articles);

        return $this->response->setJSON(['results' => $results]);
    }

    public function beritaDetail(string $slug): string
    {
        $article = $this->contentService->newsBySlug($slug);

        if (! $article) {
            throw PageNotFoundException::forPageNotFound('Berita tidak ditemukan.');
        }

        $publishedAt = null;
        if (! empty($article['published_at'])) {
            $publishedAt = Time::parse($article['published_at']);
        }

        $profile     = $this->contentService->latestProfile();
        $breadcrumbs = [
            [
                'label' => 'Beranda',
                'url'   => site_url('/'),
            ],
            [
                'label' => 'Berita',
                'url'   => site_url('berita'),
            ],
        ];

        if (! empty($article['primary_category'])) {
            $breadcrumbs[] = [
                'label' => (string) $article['primary_category']['name'],
                'url'   => site_url('berita/kategori/' . ($article['primary_category']['slug'] ?? '')),
            ];
        }

        $related = $this->contentService->relatedNews(
            (int) $article['id'],
            isset($article['primary_category']['id']) ? (int) $article['primary_category']['id'] : null,
            3
        );

        return view('public/news/show', [
            'title'         => $article['title'] ?? 'Berita Terbaru',
            'article'       => $article,
            'published_at'  => $publishedAt,
            'breadcrumbs'   => $breadcrumbs,
            'relatedNews'   => $related,
            'footerProfile' => $profile,
            'profile'       => $profile,
        ]);
    }

    public function galeri(): string
    {
        $galleries = $this->contentService->recentGalleries(12);
        $profile   = $this->contentService->latestProfile();

        return view('public/gallery', [
            'title'         => 'Galeri Kegiatan',
            'galleries'     => $galleries,
            'footerProfile' => $profile,
            'profile'       => $profile,
        ]);
    }

    public function dokumen(): string
    {
        $documents = $this->contentService->recentDocuments(50);
        $profile   = $this->contentService->latestProfile();

        return view('public/documents', [
            'title'         => 'Dokumen Publik',
            'documents'     => $documents,
            'footerProfile' => $profile,
            'profile'       => $profile,
        ]);
    }

    public function kontak(): string
    {
        $profile = $this->contentService->latestProfile() ?? [];

        return view('public/contact', [
            'title'         => 'Kontak & Pengaduan',
            'profile'       => $profile,
            'footerProfile' => $profile,
        ]);
    }
}
