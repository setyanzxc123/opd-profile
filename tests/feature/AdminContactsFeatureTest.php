<?php declare(strict_types=1);

use App\Models\ActivityLogModel;
use App\Models\ContactMessageModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class AdminContactsFeatureTest extends CIUnitTestCase
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

        $filters                       = config('Filters');
        $this->originalBeforeFilters   = $filters->globals['before'];
        $filters->globals['before']    = array_values(array_filter(
            $filters->globals['before'],
            static fn ($filter) => $filter !== 'csrf'
        ));
    }

    protected function tearDown(): void
    {
        config('Filters')->globals['before'] = $this->originalBeforeFilters;
        parent::tearDown();
    }

    public function testAdminCanUpdateStatus(): void
    {
        $adminId    = $this->createAdminUser();
        $contactId  = $this->createContactMessage(['status' => 'new']);

        $response = $this->withSession([
                'user_id' => $adminId,
                'role'    => 'admin',
            ])
            ->post('admin/contacts/' . $contactId . '/status', [
                'status'     => 'in_progress',
                'admin_note' => 'Sedang diproses',
            ]);

        $this->assertTrue($response->isRedirect());

        $message = model(ContactMessageModel::class)->find($contactId);
        $this->assertSame('in_progress', $message['status']);
        $this->assertSame('Sedang diproses', $message['admin_note']);
        $this->assertSame($adminId, (int) $message['handled_by']);
        $this->assertNull($message['responded_at']);

        $log = model(ActivityLogModel::class)->first();
        $this->assertNotNull($log);
        $this->assertSame('contacts.update_status', $log['action']);
        $this->assertStringContainsString((string) $contactId, $log['description']);
    }

    public function testBulkCloseUpdatesRespondedAt(): void
    {
        $adminId   = $this->createAdminUser();
        $firstId   = $this->createContactMessage(['status' => 'new']);
        $secondId  = $this->createContactMessage(['status' => 'new']);

        $response = $this->withSession([
                'user_id' => $adminId,
                'role'    => 'admin',
            ])
            ->post('admin/contacts/bulk/status', [
                'status'      => 'closed',
                'ids'         => [$firstId, $secondId],
                'redirect_to' => site_url('admin/contacts'),
            ]);

        $this->assertTrue($response->isRedirect());

        $model = model(ContactMessageModel::class);
        $first = $model->find($firstId);
        $second = $model->find($secondId);

        $this->assertSame('closed', $first['status']);
        $this->assertSame('closed', $second['status']);
        $this->assertNotNull($first['responded_at']);
        $this->assertNotNull($second['responded_at']);

        $log = model(ActivityLogModel::class)
            ->where('action', 'contacts.bulk_update_status')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('2 pesan kontak', $log['description']);
    }

    private function createAdminUser(): int
    {
        $db = db_connect();
        $db->table('users')->insert([
            'username'       => 'admin' . uniqid(),
            'email'          => 'admin' . uniqid() . '@example.com',
            'password_hash'  => password_hash('secret', PASSWORD_DEFAULT),
            'name'           => 'Admin Test',
            'role'           => 'admin',
            'is_active'      => 1,
            'created_at'     => Time::now('UTC')->toDateTimeString(),
            'updated_at'     => null,
            'last_login_at'  => null,
        ]);

        return (int) $db->insertID();
    }

    private function createContactMessage(array $override = []): int
    {
        $model = model(ContactMessageModel::class);

        $data = array_merge([
            'name'        => 'Pengirim',
            'email'       => 'pengirim@example.com',
            'phone'       => '0800000000',
            'subject'     => 'Need Support',
            'message'     => 'Mohon dibantu.',
            'status'      => 'new',
            'created_at'  => Time::now('UTC')->toDateTimeString(),
            'ip_address'  => '192.0.2.10',
            'user_agent'  => 'PHPUnit',
        ], $override);

        return $model->insert($data, true);
    }
}


