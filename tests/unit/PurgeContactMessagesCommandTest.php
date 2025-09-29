<?php declare(strict_types=1);

use App\Commands\PurgeContactMessages;
use App\Models\ContactMessageModel;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

final class PurgeContactMessagesCommandTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetCliOptions();
    }

    protected function tearDown(): void
    {
        $this->resetCliOptions();
        parent::tearDown();
    }

    public function testDeletesOldClosedMessages(): void
    {
        $model = model(ContactMessageModel::class);

        $model->insert([
            'name'        => 'Old Closed',
            'email'       => 'old@example.com',
            'subject'     => 'Old',
            'message'     => 'Closed ticket',
            'status'      => 'closed',
            'responded_at'=> Time::now('UTC')->subDays(120)->toDateTimeString(),
            'created_at'  => Time::now('UTC')->subDays(130)->toDateTimeString(),
        ]);

        $model->insert([
            'name'       => 'Fresh',
            'email'      => 'fresh@example.com',
            'subject'    => 'Fresh',
            'message'    => 'Still open',
            'status'     => 'new',
            'created_at' => Time::now('UTC')->toDateTimeString(),
        ]);

        $command = new PurgeContactMessages();
        $command->run(['30']);

        $this->assertSame(0, $model->where('status', 'closed')->countAllResults());
        $this->assertSame(1, $model->where('status', 'new')->countAllResults());
    }

    public function testAnonymizeKeepsRecords(): void
    {
        $model = model(ContactMessageModel::class);

        $id = $model->insert([
            'name'        => 'Closed User',
            'email'       => 'closed@example.com',
            'phone'       => '0811111111',
            'subject'     => 'Closed',
            'message'     => 'Menunggu purge',
            'status'      => 'closed',
            'responded_at'=> Time::now('UTC')->subDays(60)->toDateTimeString(),
            'ip_address'  => '198.51.100.1',
            'user_agent'  => 'TestAgent',
            'created_at'  => Time::now('UTC')->subDays(65)->toDateTimeString(),
        ], true);

        $this->setCliOption('anonymize', true);
        $command = new PurgeContactMessages();
        $command->run(['30', '--anonymize']);

        $record = $model->find($id);
        $this->assertNotNull($record);
        $this->assertNull($record['email']);
        $this->assertNull($record['phone']);
        $this->assertNull($record['user_agent']);
        $this->assertStringContainsString('Anonimized', (string) $record['admin_note']);
    }

    private function resetCliOptions(): void
    {
        $ref  = new ReflectionClass(CLI::class);
        $prop = $ref->getProperty('options');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
    }

    private function setCliOption(string $name, bool $value = true): void
    {
        $ref  = new ReflectionClass(CLI::class);
        $prop = $ref->getProperty('options');
        $prop->setAccessible(true);
        $options = $prop->getValue();
        if (! is_array($options)) {
            $options = [];
        }
        $options[$name] = $value;
        $prop->setValue(null, $options);
    }
}


