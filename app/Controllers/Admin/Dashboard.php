<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\ContactMessageModel;
use App\Models\DocumentModel;
use App\Models\NewsModel;
use App\Models\ServiceModel;

class Dashboard extends BaseController
{
    public function index(): string
    {
        helper('url');

        return view('admin/dashboard', [
            'title'           => 'Dashboard',
            'welcomeName'     => (string) (session('name') ?? session('username') ?? 'Admin'),
            'metrics'         => $this->collectMetrics(),
            'contactSummary'  => $this->collectContactSummary(),
            'latestNews'      => $this->collectLatestNews(),
            'latestDocuments' => $this->collectLatestDocuments(),
            'activityFeed'    => $this->collectActivityFeed(),
        ]);
    }

    private function collectMetrics(): array
    {
        $now          = date('Y-m-d H:i:s');
        $newsModel    = model(NewsModel::class);
        $documentModel = model(DocumentModel::class);
        $serviceModel = model(ServiceModel::class);
        $contactModel = model(ContactMessageModel::class);

        $publishedNews = (int) $newsModel->builder()
            ->where('published_at IS NOT NULL', null, false)
            ->where('published_at <=', $now)
            ->countAllResults();

        $documentTotal = (int) $documentModel->countAll();

        $activeServices = (int) $serviceModel->builder()
            ->where('is_active', 1)
            ->countAllResults();

        $openContacts = (int) $contactModel->builder()
            ->whereIn('status', ['new', 'in_progress'])
            ->countAllResults();

        return [
            [
                'label'   => 'Berita Terbit',
                'value'   => $publishedNews,
                'url'     => site_url('admin/news'),
                'icon'    => 'bx-news',
                'variant' => 'primary',
            ],
            [
                'label'   => 'Dokumen',
                'value'   => $documentTotal,
                'url'     => site_url('admin/documents'),
                'icon'    => 'bx-file',
                'variant' => 'info',
            ],
            [
                'label'   => 'Layanan Aktif',
                'value'   => $activeServices,
                'url'     => site_url('admin/profile'),
                'icon'    => 'bx-git-branch',
                'variant' => 'success',
            ],
            [
                'label'   => 'Pesan Terbuka',
                'value'   => $openContacts,
                'url'     => site_url('admin/contacts'),
                'icon'    => 'bx-message-rounded-dots',
                'variant' => 'warning',
            ],
        ];
    }

    private function collectContactSummary(): array
    {
        $model = model(ContactMessageModel::class);

        $statusCounts = [
            'new'         => 0,
            'in_progress' => 0,
            'closed'      => 0,
        ];

        $rows = $model->builder()
            ->select('status, COUNT(*) AS total')
            ->groupBy('status')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $status = $row['status'] ?? '';
            if (isset($statusCounts[$status])) {
                $statusCounts[$status] = (int) $row['total'];
            }
        }

        $latest = $model->builder()
            ->select('id, name, subject, status, created_at')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return [
            'counts' => $statusCounts,
            'total'  => array_sum($statusCounts),
            'latest' => $latest,
        ];
    }

    private function collectLatestNews(): array
    {
        $model = model(NewsModel::class);

        return $model->builder()
            ->select('id, title, published_at')
            ->orderBy('published_at IS NULL', 'ASC', false)
            ->orderBy('published_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(3)
            ->get()
            ->getResultArray();
    }

    private function collectLatestDocuments(): array
    {
        $model = model(DocumentModel::class);

        return $model->builder()
            ->select('id, title, category, year')
            ->orderBy('id', 'DESC')
            ->limit(3)
            ->get()
            ->getResultArray();
    }

    private function collectActivityFeed(): array
    {
        return model(ActivityLogModel::class)->builder()
            ->select('activity_logs.action, activity_logs.description, activity_logs.created_at, users.name, users.username')
            ->join('users', 'users.id = activity_logs.user_id', 'left')
            ->orderBy('activity_logs.created_at', 'DESC')
            ->orderBy('activity_logs.id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
    }
}


