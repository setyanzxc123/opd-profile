<?php

namespace App\Controllers;

use App\Models\NewsModel;
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

        return view('public/services', [
            'title'         => 'Layanan Publik',
            'services'      => $services,
            'footerProfile' => $this->contentService->latestProfile(),
        ]);
    }

    public function berita(): string
    {
        $search = trim((string) $this->request->getGet('q'));
        $news   = $this->contentService->paginatedNews(6, $search);

        return view('public/news/index', [
            'title'         => 'Berita Terbaru',
            'articles'      => $news['articles'],
            'pager'         => $news['pager'],
            'search'        => $search,
            'footerProfile' => $this->contentService->latestProfile(),
        ]);
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
        $article = model(NewsModel::class)
            ->where('slug', $slug)
            ->first();

        if (! $article) {
            throw PageNotFoundException::forPageNotFound('Berita tidak ditemukan.');
        }

        $publishedAt = null;
        if (! empty($article['published_at'])) {
            $publishedAt = Time::parse($article['published_at']);
        }

        return view('public/news/show', [
            'title'         => $article['title'],
            'article'       => $article,
            'published_at'  => $publishedAt,
            'footerProfile' => $this->contentService->latestProfile(),
        ]);
    }

    public function galeri(): string
    {
        $galleries = $this->contentService->recentGalleries(12);

        return view('public/gallery', [
            'title'         => 'Galeri Kegiatan',
            'galleries'     => $galleries,
            'footerProfile' => $this->contentService->latestProfile(),
        ]);
    }

    public function dokumen(): string
    {
        $documents = $this->contentService->recentDocuments(50);

        return view('public/documents', [
            'title'         => 'Dokumen Publik',
            'documents'     => $documents,
            'footerProfile' => $this->contentService->latestProfile(),
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
