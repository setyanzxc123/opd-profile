<?php

namespace App\Controllers;

use App\Models\NewsModel;
use App\Services\PublicContentService;
use CodeIgniter\Exceptions\PageNotFoundException;
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
            'title'   => 'Profil OPD',
            'profile' => $profile,
        ]);
    }

    public function layanan(): string
    {
        $services = $this->contentService->allActiveServices();

        return view('public/services', [
            'title'    => 'Layanan Publik',
            'services' => $services,
        ]);
    }

    public function berita(): string
    {
        $search = trim((string) $this->request->getGet('q'));
        $news   = $this->contentService->paginatedNews(6, $search);

        return view('public/news/index', [
            'title'    => 'Berita Terbaru',
            'articles' => $news['articles'],
            'pager'    => $news['pager'],
            'search'   => $search,
        ]);
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
            'title'        => $article['title'],
            'article'      => $article,
            'published_at' => $publishedAt,
        ]);
    }

    public function galeri(): string
    {
        $galleries = $this->contentService->recentGalleries(12);

        return view('public/gallery', [
            'title'     => 'Galeri Kegiatan',
            'galleries' => $galleries,
        ]);
    }

    public function dokumen(): string
    {
        $documents = $this->contentService->recentDocuments(50);

        return view('public/documents', [
            'title'     => 'Dokumen Publik',
            'documents' => $documents,
        ]);
    }

    public function kontak(): string
    {
        $profile = $this->contentService->latestProfile() ?? [];

        return view('public/contact', [
            'title'   => 'Kontak & Pengaduan',
            'profile' => $profile,
        ]);
    }
}
