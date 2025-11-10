<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Role-based access control configuration for the admin area.
 *
 * @phpstan-type RoleConfig array{label?: string, allowedSections: list<string>}
 */
class AdminAccess extends BaseConfig
{
    /**
     * Route used when redirecting unauthorized users.
     */
    public string $fallbackRoute = 'admin';

    /**
     * Map of role identifiers to their accessible sections.
     *
     * @var array<string, array{label?: string, allowedSections: list<string>}>
     */
    public array $roles = [
        'admin' => [
            'label'           => 'Administrator',
            'allowedSections' => ['*'],
        ],
        'editor' => [
            'label'           => 'Editor',
            'allowedSections' => [
                'dashboard',
                'profile',
                'news',
                'services',
                'galleries',
                'documents',
                'contacts',
                'settings',
            ],
        ],
    ];

    /**
     * Section name assigned to /admin without further segments.
     */
    public string $defaultDashboardSection = 'dashboard';
}
