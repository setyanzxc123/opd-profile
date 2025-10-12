<?php

namespace App\Controllers;

use App\Services\PublicContentService;
use CodeIgniter\I18n\Time;

class Home extends BaseController
{
    private PublicContentService $contentService;

    public function __construct()
    {
        $this->contentService = new PublicContentService();
    }

    public function index(): string
    {
        $profile   = $this->contentService->latestProfile();
        $services  = $this->contentService->featuredServices(4);
        $newsItems = $this->contentService->recentNews(4);
        $galleries = $this->contentService->recentGalleries(4);
        $documents = $this->contentService->recentDocuments(4);

        $hero = $this->buildHeroView($profile, $newsItems);
        $serviceCards = $this->transformServices($services);
        [$featuredNews, $otherNews] = $this->transformNews($newsItems);
        $galleryItems = $this->transformGalleries($galleries);
        $documentItems = $this->transformDocuments($documents);
        $contactQuickLinks = $this->buildQuickLinks($profile);

        return view('public/home', [
            'title'            => 'Beranda OPD',
            'hero'             => $hero,
            'profileSummary'   => $this->buildProfileSummary($profile),
            'services'         => $serviceCards,
            'featuredNews'     => $featuredNews,
            'otherNews'        => $otherNews,
            'galleries'        => $galleryItems,
            'documents'        => $documentItems,
            'contactQuickLinks'=> $contactQuickLinks,
            'footerProfile'    => $profile,
            'profile'          => $profile,
        ]);
    }

    private function buildHeroView(?array $profile, array $newsItems): array
    {
        $sliderItems = [];
        foreach ($newsItems as $index => $item) {
            $sliderItems[] = [
                'title'     => (string) ($item['title'] ?? ''),
                'excerpt'   => $this->limitText($item['content'] ?? '', 200),
                'thumbnail' => $this->resolveMediaUrl($item['thumbnail'] ?? ''),
                'slug'      => (string) ($item['slug'] ?? ''),
                'isActive'  => $index === 0,
                'published' => $this->formatDate($item['published_at'] ?? null),
            ];
        }

        $profileName  = trim((string) ($profile['name'] ?? 'Dinas ....'));
        $profileIntro = trim((string) ($profile['description'] ?? 'Menyediakan informasi terkini mengenai program kerja, layanan publik, data dokumen penting, serta berita terbaru dari dinas.'));

        return [
            'hasSlider' => $sliderItems !== [],
            'slides'    => $sliderItems,
            'fallback'  => [
                'title'       => $profileName,
                'description' => $profileIntro,
                'ctaServices' => site_url('layanan'),
                'ctaContact'  => site_url('kontak'),
            ],
        ];
    }

    private function buildProfileSummary(?array $profile): array
    {
        return [
            'name'        => trim((string) ($profile['name'] ?? 'Dinas......')),
            'description' => trim((string) ($profile['description'] ?? 'Melayani masyarakat dengan cepat, transparan, dan akuntabel.')),
            'address'     => trim((string) ($profile['address'] ?? '')),
            'phone'       => trim((string) ($profile['phone'] ?? '')),
            'email'       => trim((string) ($profile['email'] ?? '')),
        ];
    }

    private function transformServices(array $services): array
    {
        $items = [];
        foreach ($services as $service) {
            $title = trim((string) ($service['title'] ?? 'Layanan Publik'));
            $items[] = [
                'title'       => $title,
                'initial'     => mb_strtoupper(mb_substr($title, 0, 1, 'UTF-8'), 'UTF-8'),
                'summary'     => $this->limitText($service['description'] ?? '', 160),
                'target'      => site_url('layanan') . '#' . rawurlencode((string) ($service['slug'] ?? '')),
            ];
        }

        return $items;
    }

    private function transformNews(array $newsItems): array
    {
        if ($newsItems === []) {
            return [null, []];
        }

        $formatted = [];
        foreach ($newsItems as $item) {
            $formatted[] = [
                'title'     => (string) ($item['title'] ?? ''),
                'excerpt'   => $this->limitText($item['content'] ?? '', 200),
                'thumbnail' => $this->resolveMediaUrl($item['thumbnail'] ?? ''),
                'slug'      => (string) ($item['slug'] ?? ''),
                'published' => $this->formatDate($item['published_at'] ?? null),
            ];
        }

        $featured = array_shift($formatted);

        return [$featured, $formatted];
    }

    private function transformGalleries(array $galleries): array
    {
        $items = [];
        foreach ($galleries as $gallery) {
            $items[] = [
                'title'       => (string) ($gallery['title'] ?? 'Galeri Kegiatan'),
                'description' => $this->limitText($gallery['description'] ?? '', 120),
                'image'       => $this->resolveMediaUrl($gallery['image_path'] ?? ''),
            ];
        }

        return $items;
    }

    private function transformDocuments(array $documents): array
    {
        $items = [];
        foreach ($documents as $document) {
            $items[] = [
                'title'    => (string) ($document['title'] ?? 'Dokumen Publik'),
                'category' => (string) ($document['category'] ?? ''),
                'year'     => (string) ($document['year'] ?? ''),
                'url'      => $this->resolveMediaUrl($document['file_path'] ?? ''),
            ];
        }

        return $items;
    }

    private function buildQuickLinks(?array $profile): array
    {
        $links = [];
        $phone = trim((string) ($profile['phone'] ?? ''));
        $email = trim((string) ($profile['email'] ?? ''));

        if ($phone !== '') {
            $links[] = [
                'label' => 'Hubungi via Telepon',
                'value' => $phone,
                'href'  => 'tel:' . preg_replace('/[^0-9+]/', '', $phone),
            ];
        }

        if ($email !== '') {
            $links[] = [
                'label' => 'Kirim Email',
                'value' => $email,
                'href'  => 'mailto:' . $email,
            ];
        }

        if ($links === []) {
            $links[] = [
                'label' => 'Layanan Pengaduan',
                'value' => 'Segera hadir',
                'href'  => '#',
            ];
        }

        return $links;
    }

    private function limitText(string $text, int $limit): string
    {
        $plain = trim(strip_tags($text));

        if ($plain === '') {
            return '';
        }

        return mb_strimwidth($plain, 0, $limit, '...', 'UTF-8');
    }

    private function resolveMediaUrl(string $path): ?string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return null;
        }

        return base_url($trimmed);
    }

    private function formatDate($date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        try {
            return Time::parse($date)->toLocalizedString('d MMM yyyy');
        } catch (\Throwable $throwable) {
            log_message('debug', 'Failed to format date: {error}', ['error' => $throwable->getMessage()]);

            return null;
        }
    }
}
