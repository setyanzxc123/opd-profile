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

        $access = $this->resolveAccess();
        /** @var callable(string): bool $canAccess */
        $canAccess = $access['canAccess'];

        $metrics = $this->collectMetrics();
        $metrics = array_values(array_filter(
            $metrics,
            static fn (array $metric): bool => ! isset($metric['section']) || $metric['section'] === ''
                || $canAccess((string) $metric['section'])
        ));

        $contactSummary  = $canAccess('contacts') ? $this->collectContactSummary() : null;
        $latestNews      = $canAccess('news') ? $this->collectLatestNews() : [];
        $latestDocuments = $canAccess('documents') ? $this->collectLatestDocuments() : [];
        $activityFeed    = $canAccess('logs') ? $this->collectActivityFeed() : [];

        return view('admin/dashboard', [
            'title'           => 'Dashboard',
            'welcomeName'     => (string) (session('name') ?? session('username') ?? 'Admin'),
            'metrics'         => $metrics,
            'contactSummary'  => $contactSummary,
            'latestNews'      => $latestNews,
            'latestDocuments' => $latestDocuments,
            'activityFeed'    => $activityFeed,
            'canAccess'       => $canAccess,
        ]);
    }

    private function collectMetrics(): array
    {
        $now           = date('Y-m-d H:i:s');
        $newsModel     = model(NewsModel::class);
        $documentModel = model(DocumentModel::class);
        $serviceModel  = model(ServiceModel::class);
        $contactModel  = model(ContactMessageModel::class);

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
                'section' => 'news',
            ],
            [
                'label'   => 'Dokumen',
                'value'   => $documentTotal,
                'url'     => site_url('admin/documents'),
                'icon'    => 'bx-file',
                'variant' => 'info',
                'section' => 'documents',
            ],
            [
                'label'   => 'Layanan Aktif',
                'value'   => $activeServices,
                'url'     => site_url('admin/profile'),
                'icon'    => 'bx-git-branch',
                'variant' => 'success',
                'section' => 'profile',
            ],
            [
                'label'   => 'Pesan Terbuka',
                'value'   => $openContacts,
                'url'     => site_url('admin/contacts'),
                'icon'    => 'bx-message-rounded-dots',
                'variant' => 'warning',
                'section' => 'contacts',
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

    /**
     * @return array{allowedSections: list<string>, hasFullAccess: bool, canAccess: callable(string):bool}
     */
    private function resolveAccess(): array
    {
        $config = config('AdminAccess');
        $role   = strtolower((string) (session('role') ?? ''));
        $roleConfig = $config->roles[$role] ?? ['allowedSections' => ['*']];

        $allowed = array_map(
            static fn ($item) => strtolower((string) $item),
            $roleConfig['allowedSections'] ?? []
        );

        if ($allowed === []) {
            $allowed = ['*'];
        }

        $hasFullAccess = in_array('*', $allowed, true);

        $canAccess = static function (string $section) use ($allowed, $hasFullAccess): bool {
            if ($hasFullAccess) {
                return true;
            }

            return in_array(strtolower($section), $allowed, true);
        };

        return [
            'allowedSections' => $allowed,
            'hasFullAccess'   => $hasFullAccess,
            'canAccess'       => $canAccess,
        ];
    }
}
