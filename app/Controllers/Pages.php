<?php

namespace App\Controllers;

use App\Models\DocumentModel;
use App\Models\GalleryModel;
use App\Models\NewsModel;
use App\Models\OpdProfileModel;
use App\Models\ServiceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;

class Pages extends BaseController
{
    public function profil(): string
    {
        $profileModel = model(OpdProfileModel::class);
        $profile      = $profileModel
            ->orderBy('id', 'desc')
            ->first();

        if (! $profile) {
            throw PageNotFoundException::forPageNotFound('Profil OPD belum tersedia.');
        }

        return view('public/profile', [
            'title'   => 'Profil OPD',
            'profile' => $profile,
        ]);
    }

    public function layanan(): string
    {
        $serviceModel = model(ServiceModel::class);

        $serviceFields = [];
        try {
            $serviceFields = db_connect()->getFieldNames('services');
        } catch (\Throwable $th) {
            $serviceFields = [];
        }

        $serviceQuery = $serviceModel
            ->orderBy('sort_order', 'asc')
            ->orderBy('title', 'asc');

        if (in_array('is_active', $serviceFields, true)) {
            $serviceQuery = $serviceQuery->where('is_active', 1);
        }

        $services = $serviceQuery->findAll();

        return view('public/services', [
            'title'    => 'Layanan Publik',
            'services' => $services,
        ]);
    }

    public function berita(): string
    {
        $newsModel = model(NewsModel::class);

        $articles = $newsModel
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(6);

        return view('public/news/index', [
            'title'    => 'Berita Terbaru',
            'articles' => $articles,
            'pager'    => $newsModel->pager,
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
        $galleries = model(GalleryModel::class)
            ->orderBy('created_at', 'desc')
            ->findAll();

        return view('public/gallery', [
            'title'     => 'Galeri Kegiatan',
            'galleries' => $galleries,
        ]);
    }

    public function dokumen(): string
    {
        $documents = model(DocumentModel::class)
            ->orderBy('year', 'desc')
            ->orderBy('created_at', 'desc')
            ->findAll();

        return view('public/documents', [
            'title'     => 'Dokumen Publik',
            'documents' => $documents,
        ]);
    }
}
