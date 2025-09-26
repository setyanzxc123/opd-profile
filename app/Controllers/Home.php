<?php

namespace App\Controllers;

use App\Models\DocumentModel;
use App\Models\GalleryModel;
use App\Models\NewsModel;
use App\Models\OpdProfileModel;
use App\Models\ServiceModel;

class Home extends BaseController
{
    public function index(): string
    {
        $profileModel = model(OpdProfileModel::class);
        $profile      = $profileModel
            ->orderBy('id', 'desc')
            ->first();

        $serviceModel = model(ServiceModel::class);
        $serviceFields = [];
        $db = null;
        try {
            $db = db_connect();
            $serviceFields = $db->getFieldNames('services');
        } catch (\Throwable $th) {
            $serviceFields = [];
        }

        $serviceQuery = $serviceModel
            ->orderBy('sort_order', 'asc')
            ->orderBy('title', 'asc');

        if (in_array('is_active', $serviceFields, true)) {
            $serviceQuery = $serviceQuery->where('is_active', 1);
        }

        $services = $serviceQuery->findAll(3);

        $newsModel = model(NewsModel::class);
        $news      = $newsModel
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->findAll(3);

        $galleryModel = model(GalleryModel::class);
        $galleries    = $galleryModel
            ->orderBy('created_at', 'desc')
            ->findAll(6);

        $documentModel = model(DocumentModel::class);
        $documents     = $documentModel
            ->orderBy('year', 'desc')
            ->orderBy('created_at', 'desc')
            ->findAll(4);

        return view('public/home', [
            'title'      => 'Beranda OPD',
            'profile'    => $profile,
            'services'   => $services,
            'news'       => $news,
            'galleries'  => $galleries,
            'documents'  => $documents,
        ]);
    }
}
