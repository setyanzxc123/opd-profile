<?php

namespace App\Services;

use App\Models\ActivityLogModel;
use App\Models\ContactMessageModel;
use App\Models\DocumentModel;
use App\Models\NewsModel;
use App\Models\ServiceModel;

class DashboardAdminService
{
    /**
     * @var array<string,array<string,bool>>
     */
    private array $columnCache = [];

    public function collectMetrics(): array
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
                'url'     => site_url('admin/services'),
                'icon'    => 'bx-git-branch',
                'variant' => 'success',
                'section' => 'services',
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

    public function collectContactSummary(): array
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

    public function collectLatestNews(): array
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

    public function collectLatestDocuments(): array
    {
        $model = model(DocumentModel::class);

        return $model->builder()
            ->select('id, title, category, year')
            ->orderBy('id', 'DESC')
            ->limit(3)
            ->get()
            ->getResultArray();
    }

    public function collectActivityFeed(): array
    {
        $builder = model(ActivityLogModel::class)->builder();
        $db      = $builder->db();

        $select = [
            'activity_logs.action',
            'activity_logs.description',
            'activity_logs.created_at',
        ];

        if ($this->tableHasColumn($db, 'users', 'name')) {
            $select[] = 'users.name AS actor_name';
        }

        if ($this->tableHasColumn($db, 'users', 'username')) {
            $select[] = 'users.username AS actor_username';
        }

        return $builder
            ->select(implode(', ', $select))
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
    public function resolveAccess(): array
    {
        $config = config('AdminAccess');
        $role   = $this->currentRole();
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

    private function tableHasColumn($db, string $table, string $column): bool
    {
        $tableKey  = $table;
        $columnKey = strtolower($column);

        if (isset($this->columnCache[$tableKey][$columnKey])) {
            return $this->columnCache[$tableKey][$columnKey];
        }

        $exists = $db->fieldExists($column, $table);
        $this->columnCache[$tableKey][$columnKey] = $exists;

        return $exists;
    }

    private function currentRole(): string
    {
        $auth = auth('session');

        if ($auth->loggedIn()) {
            /** @var \CodeIgniter\Shield\Entities\User $user */
            $user = $auth->user();
            $role = strtolower((string) ($user->role ?? ''));

            if ($role === '' && $user->inGroup('admin')) {
                $role = 'admin';
            }

            if ($role === '') {
                $role = 'editor';
            }

            return $role;
        }

        return strtolower((string) (session('role') ?? 'editor'));
    }
}
