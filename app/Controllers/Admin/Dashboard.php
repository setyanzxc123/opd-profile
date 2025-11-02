<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\DashboardAdminService;

class Dashboard extends BaseController
{
    private DashboardAdminService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = service('dashboardAdmin');
    }

    public function index(): string
    {
        helper(['url', 'auth']);

        $access = $this->dashboardService->resolveAccess();
        /** @var callable(string): bool $canAccess */
        $canAccess = $access['canAccess'];

        $metrics = $this->dashboardService->collectMetrics();
        $metrics = array_values(array_filter(
            $metrics,
            static fn (array $metric): bool => ! isset($metric['section']) || $metric['section'] === ''
                || $canAccess((string) $metric['section'])
        ));

        $contactSummary  = $canAccess('contacts') ? $this->dashboardService->collectContactSummary() : null;
        $latestNews      = $canAccess('news') ? $this->dashboardService->collectLatestNews() : [];
        $latestDocuments = $canAccess('documents') ? $this->dashboardService->collectLatestDocuments() : [];
        $activityFeed    = $canAccess('logs') ? $this->dashboardService->collectActivityFeed() : [];

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
}
