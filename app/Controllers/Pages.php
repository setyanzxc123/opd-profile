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
        helper(['url', 'news']);

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
        $articles   = array_map(static function (array $article): array {
            $article['excerpt'] = news_trim_excerpt($article['excerpt'] ?? null, (string) ($article['content'] ?? ''));

            return $article;
        }, $news['articles'] ?? []);
        $profile    = $this->contentService->latestProfile();
        $categories = $this->contentService->newsCategories();
        $tags       = $this->contentService->newsTags();

        $pageTitle   = 'Berita Terbaru';
        $description = 'Kumpulan berita resmi terbaru dari OPD lengkap dengan kategori dan tag.';
        $keywords    = ['berita resmi', 'OPD', 'informasi publik'];

        if ($activeCategory) {
            $pageTitle   = sprintf('Berita %s', $activeCategory['name']);
            $description = sprintf('Daftar berita OPD dalam kategori %s.', $activeCategory['name']);
            $keywords[]  = (string) $activeCategory['name'];
        }

        if ($activeTag) {
            $pageTitle   = sprintf('Berita dengan Tag %s', $activeTag['name']);
            $description = sprintf('Kumpulan berita OPD dengan tag %s.', $activeTag['name']);
            $keywords[]  = (string) $activeTag['name'];
        }

        if ($activeCategory && $activeTag) {
            $pageTitle   = sprintf('Berita %s dengan Tag %s', $activeCategory['name'], $activeTag['name']);
            $description = sprintf('Berita OPD pada kategori %s yang ditandai dengan %s.', $activeCategory['name'], $activeTag['name']);
        }

        if ($searchParam !== '') {
            $pageTitle   = sprintf('Hasil Pencarian Berita "%s"', $searchParam);
            $description = sprintf('Hasil pencarian berita OPD untuk kata kunci "%s".', $searchParam);
            $keywords[]  = $searchParam;
        }

        $breadcrumbs = [
            [
                'label' => 'Beranda',
                'url'   => site_url('/'),
            ],
            [
                'label'  => 'Berita',
                'url'    => site_url('berita'),
                'active' => ! $activeCategory && ! $activeTag && $searchParam === '',
            ],
        ];

        if ($activeCategory) {
            $breadcrumbs[] = [
                'label' => (string) $activeCategory['name'],
                'url'   => site_url('berita/kategori/' . ($activeCategory['slug'] ?? '')),
                'active'=> ! $activeTag && $searchParam === '',
            ];
        }

        if ($activeTag) {
            $breadcrumbs[] = [
                'label' => (string) $activeTag['name'],
                'url'   => site_url('berita/tag/' . ($activeTag['slug'] ?? '')),
                'active'=> $searchParam === '',
            ];
        }

        if ($searchParam !== '') {
            $breadcrumbs[] = [
                'label'  => sprintf('Pencarian "%s"', $searchParam),
                'url'    => '',
                'active' => true,
            ];
        } else {
            $lastIndex = array_key_last($breadcrumbs);
            if ($lastIndex !== null) {
                $breadcrumbs[$lastIndex]['active'] = true;
            }
        }

        $meta = [
            'title'       => $pageTitle,
            'description' => $description,
            'keywords'    => implode(', ', array_unique(array_filter($keywords))),
            'url'         => (string) $this->request->getUri(),
            'type'        => 'website',
        ];

        return view('public/news/index', [
            'title'          => $pageTitle,
            'meta'           => $meta,
            'articles'       => $articles,
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
            'breadcrumbs'   => $breadcrumbs,
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
        helper(['url', 'news']);

        $article = $this->contentService->newsBySlug($slug);

        if (! $article) {
            throw PageNotFoundException::forPageNotFound('Berita tidak ditemukan.');
        }

        $article['excerpt']          = news_trim_excerpt($article['excerpt'] ?? null, (string) ($article['content'] ?? ''));
        $article['meta_title']       = news_resolve_meta_title($article['meta_title'] ?? null, (string) ($article['title'] ?? ''));
        $article['meta_description'] = news_resolve_meta_description($article['meta_description'] ?? null, $article['excerpt'], (string) ($article['content'] ?? ''));
        $article['meta_keywords']    = isset($article['meta_keywords']) && $article['meta_keywords'] !== ''
            ? $article['meta_keywords']
            : implode(', ', array_filter(array_map(static fn (array $category): string => (string) ($category['name'] ?? ''), $article['categories'] ?? [])));

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

        $breadcrumbs[] = [
            'label'  => (string) ($article['title'] ?? 'Berita'),
            'url'    => '',
            'active' => true,
        ];

        $related = $this->contentService->relatedNews(
            (int) $article['id'],
            isset($article['primary_category']['id']) ? (int) $article['primary_category']['id'] : null,
            3
        );

        $meta = [
            'title'       => $article['meta_title'] ?: ($article['title'] ?? 'Berita Terbaru'),
            'description' => $article['meta_description'],
            'keywords'    => (string) $article['meta_keywords'],
            'author'      => (string) ($article['public_author'] ?? ''),
            'url'         => (string) $this->request->getUri(),
            'type'        => 'article',
        ];

        if (! empty($article['thumbnail'])) {
            $meta['image'] = base_url($article['thumbnail']);
        }

        return view('public/news/show', [
            'title'         => $article['title'] ?? 'Berita Terbaru',
            'meta'          => $meta,
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
