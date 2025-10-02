<?php declare(strict_types=1);

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class AdminAuthorizationFeatureTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $namespace = 'App';

    /** @var array<int, mixed> */
    private array $originalBeforeFilters = [];

    protected function setUp(): void
    {
        parent::setUp();

        helper('url');

        $filters                     = config('Filters');
        $this->originalBeforeFilters = $filters->globals['before'];
        $filters->globals['before']  = array_values(array_filter(
            $filters->globals['before'],
            static fn ($filter) => $filter !== 'csrf'
        ));
    }

    protected function tearDown(): void
    {
        config('Filters')->globals['before'] = $this->originalBeforeFilters;
        parent::tearDown();
    }

    public function testEditorCannotAccessUserManagement(): void
    {
        $editorId = $this->createUser('editor');

        $response = $this->withSession([
                'user_id' => $editorId,
                'role'    => 'editor',
            ])
            ->get('admin/users');

        $this->assertTrue($response->isRedirect());
        $this->assertSame(site_url('admin'), $response->getHeaderLine('Location'));
    }

    public function testEditorCannotAccessActivityLogs(): void
    {
        $editorId = $this->createUser('editor');

        $response = $this->withSession([
                'user_id' => $editorId,
                'role'    => 'editor',
            ])
            ->get('admin/logs');

        $this->assertTrue($response->isRedirect());
        $this->assertSame(site_url('admin'), $response->getHeaderLine('Location'));
    }

    public function testEditorCanAccessAllowedContentSection(): void
    {
        $editorId = $this->createUser('editor');

        $response = $this->withSession([
                'user_id' => $editorId,
                'role'    => 'editor',
            ])
            ->get('admin/news');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAdminStillAccessesProtectedSection(): void
    {
        $adminId = $this->createUser('admin');

        $response = $this->withSession([
                'user_id' => $adminId,
                'role'    => 'admin',
            ])
            ->get('admin/users');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testEditorDashboardHidesRestrictedWidgets(): void
    {
        $editorId = $this->createUser('editor');

        $response = $this->withSession([
                'user_id' => $editorId,
                'role'    => 'editor',
            ])
            ->get('admin');

        $this->assertSame(200, $response->getStatusCode());
        $body = $response->getBody();
        $this->assertStringNotContainsString('Aktivitas Terbaru', $body);
        $this->assertStringNotContainsString('admin/logs', $body);
    }

    public function testAdminDashboardShowsActivityWidget(): void
    {
        $adminId = $this->createUser('admin');

        $response = $this->withSession([
                'user_id' => $adminId,
                'role'    => 'admin',
            ])
            ->get('admin');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Aktivitas Terbaru', $response->getBody());
    }

    private function createUser(string $role): int
    {
        $db       = db_connect();
        $username = $role . uniqid('', true);

        $db->table('users')->insert([
            'username'      => $username,
            'email'         => $username . '@example.com',
            'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
            'name'          => ucfirst($role) . ' Tester',
            'role'          => $role,
            'is_active'     => 1,
            'created_at'    => Time::now('UTC')->toDateTimeString(),
            'updated_at'    => null,
            'last_login_at' => null,
        ]);

        return (int) $db->insertID();
    }
}


