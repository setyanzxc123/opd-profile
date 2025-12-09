<?php

namespace App\Controllers;

use App\Models\HeroSliderModel;
use App\Models\AppLinkModel;
use App\Services\PublicContentService;
use CodeIgniter\I18n\Time;

class Home extends BaseController
{
    private PublicContentService $contentService;
    private HeroSliderModel $sliderModel;

    public function __construct()
    {
        $this->contentService = new PublicContentService();
        $this->sliderModel = model(HeroSliderModel::class);
    }

    public function index(): string
    {
        $profile   = $this->contentService->latestProfile();
        $services  = $this->contentService->featuredServices(4);
        $newsItems = $this->contentService->recentNews(5);

        $hero = $this->buildHeroView($profile, $newsItems);
        $serviceCards = $this->transformServices($services);
        [$featuredNews, $otherNews] = $this->transformNews($newsItems);
        $contactQuickLinks = $this->buildQuickLinks($profile);
        
        // Get app links for slider
        $appLinkModel = model(AppLinkModel::class);
        $appLinks = $appLinkModel->getActiveLinks(20);
        
        // Calculate statistics
        helper('statistics');
        $statistics = $this->buildStatistics();

        // Prepare professional title from profile
        $pageTitle = trim((string) ($profile['name'] ?? 'Website Resmi'));
        $nameLine2 = trim((string) ($profile['name_line2'] ?? ''));
        if ($nameLine2 !== '') {
            $pageTitle .= ' ' . $nameLine2;
        }

        return view('public/home', [
            'title'            => $pageTitle,
            'hero'             => $hero,
            'profileSummary'   => $this->buildProfileSummary($profile),
            'services'         => $serviceCards,
            'featuredNews'     => $featuredNews,
            'otherNews'        => $otherNews,
            'contactQuickLinks'=> $contactQuickLinks,
            'statistics'       => $statistics,
            'appLinks'         => $appLinks,
            'footerProfile'    => $profile,
            'profile'          => $profile,
        ]);
    }

    private function buildHeroView(?array $profile, array $newsItems): array
    {
        $limitSlots = 10;
        $minSlides  = 5;

        $manual = $this->sliderModel->getActiveSlides($limitSlots);
        $slides = [];
        foreach ($manual as $index => $item) {
            $slides[] = [
                'title'     => (string) ($item['title'] ?? ''),
                'excerpt'   => $this->limitText($item['description'] ?? '', 200),
                'thumbnail' => $this->resolveMediaUrl($item['image_path'] ?? ''),
                'link'      => (string) ($item['button_link'] ?? '#'),
                'isActive'  => $index === 0,
                'published' => null,
                'category'      => $item['subtitle'] ?? null,
                'category_slug' => null,
                'button_text'   => $item['button_text'] ?? 'Selengkapnya',
            ];
        }

        if (count($slides) < $minSlides) {
            $needed = $minSlides - count($slides);
            $fallback = $this->buildNewsFallback($newsItems, $needed, count($slides));
            $slides = array_merge($slides, $fallback);
        }

        $profileName  = trim((string) ($profile['name'] ?? 'Dinas ....'));
        $profileIntro = trim((string) ($profile['description'] ?? 'Menyediakan informasi terkini mengenai program kerja, layanan publik, data dokumen penting, serta berita terbaru dari dinas.'));

        return [
            'hasSlider' => $slides !== [],
            'slides'    => array_slice($slides, 0, $limitSlots),
            'fallback'  => [
                'title'       => $profileName,
                'description' => $profileIntro,
                'ctaServices' => site_url('layanan'),
                'ctaContact'  => site_url('kontak'),
            ],
        ];
    }

    private function buildNewsFallback(array $newsItems, int $limit, int $offsetCount = 0): array
    {
        $fallback = [];
        $newsItems = array_slice($newsItems, 0, $limit);

        foreach ($newsItems as $index => $item) {
            $primaryCategory = $item['primary_category'] ?? null;
            $fallback[] = [
                'title'     => (string) ($item['title'] ?? ''),
                'excerpt'   => $this->limitText($item['content'] ?? '', 200),
                'thumbnail' => $this->resolveMediaUrl($item['thumbnail'] ?? ''),
                'link'      => site_url('berita/' . (string) ($item['slug'] ?? '')),
                'isActive'  => ($index + $offsetCount) === 0,
                'published' => $this->formatDate($item['published_at'] ?? null),
                'category'      => $primaryCategory['name'] ?? null,
                'category_slug' => $primaryCategory['slug'] ?? null,
                'button_text'   => 'Baca Selengkapnya',
            ];
        }

        return $fallback;
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
                'icon'        => $service['icon'] ?? null,
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
            $primaryCategory = $item['primary_category'] ?? null;
            $formatted[] = [
                'title'     => (string) ($item['title'] ?? ''),
                'excerpt'   => $this->limitText($item['content'] ?? '', 200),
                'thumbnail' => $this->resolveMediaUrl($item['thumbnail'] ?? ''),
                'slug'      => (string) ($item['slug'] ?? ''),
                'published' => $this->formatDate($item['published_at'] ?? null),
                'category'      => $primaryCategory['name'] ?? null,
                'category_slug' => $primaryCategory['slug'] ?? null,
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
    
    private function buildStatistics(): array
    {
        $servicesModel = model('App\Models\ServiceModel');
        $newsModel = model('App\Models\NewsModel');
        $documentsModel = model('App\Models\DocumentModel');
        
        return [
            'services' => $servicesModel->where('is_active', 1)->countAllResults(),
            'news' => $newsModel->where('published_at IS NOT NULL', null, false)->countAllResults(),
            'documents' => $documentsModel->countAllResults(),
            'visitors' => 0, // Placeholder - bisa diintegrasikan dengan analytics nanti
        ];
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
