<?php declare(strict_types=1);

use App\Models\ContactMessageModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class ContactFormFeatureTest extends CIUnitTestCase
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

        // Disable CSRF filter for feature tests
        $filters                       = config('Filters');
        $this->originalBeforeFilters   = $filters->globals['before'];
        $filters->globals['before']    = array_values(array_filter(
            $filters->globals['before'],
            static fn ($filter) => $filter !== 'csrf'
        ));

        $this->clearContactEnv();
    }

    protected function tearDown(): void
    {
        config('Filters')->globals['before'] = $this->originalBeforeFilters;
        $this->clearContactEnv();

        parent::tearDown();
    }

    public function testSuccessfulSubmissionStoresMetadata(): void
    {
        $response = $this->withHeaders(['User-Agent' => 'FeatureTest/1.0'])
            ->withServer(['REMOTE_ADDR' => '203.0.113.10'])
            ->post('kontak', $this->validPayload([
                'message' => "Hello <strong>World</strong><script>alert('x')</script>",
            ]));

        $this->assertTrue($response->isRedirect());

        $message = model(ContactMessageModel::class)->first();
        $this->assertNotNull($message);
        $this->assertSame('FeatureTest/1.0', $message['user_agent']);
        $this->assertSame('203.0.113.10', $message['ip_address']);
        $this->assertSame('08123456789', $message['phone']);
        $this->assertSame("Hello Worldalert('x')", $message['message']);
    }

    public function testBlacklistedEmailIsRejected(): void
    {
        putenv('CONTACT_BLOCKED_EMAILS=blocked@example.com');

        $response = $this->withServer(['REMOTE_ADDR' => '198.51.100.50'])
            ->post('kontak', $this->validPayload([
                'email' => 'blocked@example.com',
            ]));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(0, model(ContactMessageModel::class)->countAllResults());
    }

    public function testBlacklistedDomainIsRejected(): void
    {
        putenv('CONTACT_BLOCKED_DOMAINS=spamdomain.test');

        $response = $this->withServer(['REMOTE_ADDR' => '198.51.100.12'])
            ->post('kontak', $this->validPayload([
                'email' => 'user@spamdomain.test',
            ]));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(0, model(ContactMessageModel::class)->countAllResults());
    }

    public function testDailyLimitPerIpIsEnforced(): void
    {
        putenv('CONTACT_LIMIT_PER_IP=1');
        putenv('CONTACT_LIMIT_PER_EMAIL=5');

        $this->withServer(['REMOTE_ADDR' => '198.51.100.5'])
            ->post('kontak', $this->validPayload());

        $response = $this->withServer(['REMOTE_ADDR' => '198.51.100.5'])
            ->post('kontak', $this->validPayload([
                'subject' => 'Percobaan Lagi',
            ]));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(1, model(ContactMessageModel::class)->countAllResults());
    }

    private function validPayload(array $override = []): array
    {
        return array_merge([
            'full_name' => 'Tester Feature',
            'email'     => 'tester@example.com',
            'phone'     => '08123456789',
            'subject'   => 'Uji Coba',
            'message'   => 'Isi pesan pengujian.',
        ], $override);
    }

    private function clearContactEnv(): void
    {
        foreach ([
            'CONTACT_BLOCKED_EMAILS',
            'CONTACT_BLOCKED_DOMAINS',
            'CONTACT_BLOCKED_IPS',
            'CONTACT_LIMIT_PER_IP',
            'CONTACT_LIMIT_PER_EMAIL',
        ] as $key) {
            putenv($key);
        }
    }
}


