<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\NewsModel;
use App\Services\PublicContentService;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Cache\Handlers\BaseHandler;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;

final class ArrayCache extends BaseHandler implements CacheInterface
{
    /** @var array<string,mixed> */
    private array $store = [];

    public function initialize()
    {
    }

    public function get(string $key)
    {
        return $this->store[$key] ?? null;
    }

    public function save(string $key, $value, int $ttl = 60)
    {
        $this->store[$key] = $value;

        return true;
    }

    public function delete(string $key)
    {
        unset($this->store[$key]);

        return true;
    }

    public function deleteMatching(string $pattern)
    {
        return 0;
    }

    public function increment(string $key, int $offset = 1)
    {
        $current = $this->store[$key] ?? 0;
        if (! is_numeric($current)) {
            $current = 0;
        }

        $current += $offset;
        $this->store[$key] = $current;

        return $current;
    }

    public function decrement(string $key, int $offset = 1)
    {
        return $this->increment($key, -$offset);
    }

    public function clean()
    {
        $this->store = [];

        return true;
    }

    public function getCacheInfo()
    {
        return null;
    }

    public function getMetaData(string $key)
    {
        if (! array_key_exists($key, $this->store)) {
            return null;
        }

        return [
            'expire' => null,
            'data'   => $this->store[$key],
        ];
    }

    public function isSupported(): bool
    {
        return true;
    }
}

final class RecordingNewsModel extends NewsModel
{
    public array $joins = [];
    public array $wheres = [];
    public array $ordered = [];
    public array $selected = [];
    public array $paginateParams = [];
    public array $groupByCalls = [];
    public array $searchCalls = [];
    public array $whereIns = [];
    public array $orWhereIns = [];
    public array $findAllParams = [];
    private array $result;

    public function __construct(array $result = [['id' => 1]])
    {
        $this->result = $result;
    }

    public function setResult(array $result): void
    {
        $this->result = $result;
    }

    public function select($fields, ?bool $escape = null)
    {
        $this->selected[] = $fields;

        return $this;
    }

    public function orderBy($orderBy, $direction = '', $escape = null)
    {
        $this->ordered[] = [$orderBy, $direction];

        return $this;
    }

    public function join($table, $cond, $type = '', $escape = null)
    {
        $this->joins[] = [$table, $cond, $type];

        return $this;
    }

    public function where($key, $value = null, $escape = null)
    {
        $this->wheres[] = [$key, $value];

        return $this;
    }

    public function whereIn($key = null, ?array $values = null, ?bool $escape = null)
    {
        $this->whereIns[] = [$key, $values];

        return $this;
    }

    public function orWhereIn($key = null, ?array $values = null, ?bool $escape = null)
    {
        $this->orWhereIns[] = [$key, $values];

        return $this;
    }

    public function groupStart()
    {
        $this->searchCalls[] = 'groupStart';

        return $this;
    }

    public function like($field, $match = null, $side = 'both', ?bool $escape = null)
    {
        $this->searchCalls[] = ['like', $field, $match];

        return $this;
    }

    public function orLike($field, $match = null, $side = 'both', ?bool $escape = null)
    {
        $this->searchCalls[] = ['orLike', $field, $match];

        return $this;
    }

    public function groupEnd()
    {
        $this->searchCalls[] = 'groupEnd';

        return $this;
    }

    public function groupBy($by, ?bool $escape = null)
    {
        $this->groupByCalls[] = $by;

        return $this;
    }

    public function paginate(?int $perPage = null, string $group = 'default', ?int $page = null, int $segment = 0)
    {
        $effectivePerPage     = $perPage ?? 20;
        $this->paginateParams = [$effectivePerPage, $group];

        return $this->result;
    }

    public function findAll(?int $limit = null, int $offset = 0)
    {
        $this->findAllParams = [$limit, $offset];

        if ($limit !== null) {
            return array_slice($this->result, 0, $limit);
        }

        return $this->result;
    }
}

final class TestingPublicContentService extends PublicContentService
{
    private ?array $hydrateOverride = null;

    public function __construct(CacheInterface $cache, private bool $hasViewCount = false)
    {
        parent::__construct($cache, 60);
    }

    protected function newsHasViewCountColumn(): bool
    {
        return $this->hasViewCount;
    }

    public function setHydrateOverride(?array $items): void
    {
        $this->hydrateOverride = $items;
    }

    protected function hydrateNewsRelations(array $newsItems): array
    {
        if ($this->hydrateOverride !== null) {
            return $this->hydrateOverride;
        }

        return parent::hydrateNewsRelations($newsItems);
    }
}

final class PublicContentServiceTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Factories::reset('models');
        parent::tearDown();
    }

    public function testPaginatedNewsAppliesCategoryFilter(): void
    {
        $mock = new RecordingNewsModel([['id' => 1]]);

        Factories::injectMock('models', NewsModel::class, $mock);

        $service = new TestingPublicContentService(new ArrayCache());
        $service->paginatedNews(6, null, 99, null);

        $this->assertSame([['news_category_map', 'news_category_map.news_id = news.id', 'inner']], $mock->joins);
        $this->assertContains(['news_category_map.category_id', 99], $mock->wheres);
        $this->assertSame([6, 'default'], $mock->paginateParams);
    }

    public function testPaginatedNewsAppliesTagFilter(): void
    {
        $mock = new RecordingNewsModel([['id' => 5]]);

        Factories::injectMock('models', NewsModel::class, $mock);

        $service = new TestingPublicContentService(new ArrayCache());
        $service->paginatedNews(10, null, null, 7);

        $this->assertSame(
            ['news_tag_map', 'news_tag_map.news_id = news.id', 'inner'],
            $mock->joins[0] ?? null
        );
        $this->assertContains(['news_tag_map.tag_id', 7], $mock->wheres);
        $this->assertSame([10, 'default'], $mock->paginateParams);
    }

    public function testPaginatedNewsGroupsSearchConditionsWithFilters(): void
    {
        $mock = new RecordingNewsModel([['id' => 9]]);

        Factories::injectMock('models', NewsModel::class, $mock);

        $service = new TestingPublicContentService(new ArrayCache());
        $service->paginatedNews(6, 'layanan', 3, null);

        $this->assertSame(
            ['groupStart', ['like', 'title', 'layanan'], ['orLike', 'content', 'layanan'], 'groupEnd'],
            $mock->searchCalls
        );
        $this->assertContains(['news_category_map.category_id', 3], $mock->wheres);
        $this->assertSame([6, 'default'], $mock->paginateParams);
    }

    public function testPopularNewsOrdersByViewCountWhenAvailable(): void
    {
        $mock = new RecordingNewsModel([]);

        Factories::injectMock('models', NewsModel::class, $mock);

        $service = new TestingPublicContentService(new ArrayCache(), true);
        $service->popularNews(5);

        $this->assertSame(
            [
                ['view_count', 'desc'],
                ['published_at', 'desc'],
                ['created_at', 'desc'],
            ],
            $mock->ordered
        );
        $this->assertSame([5, 0], $mock->findAllParams);
    }

    public function testPopularNewsFallbacksToPublishedDateWhenNoViewCountColumn(): void
    {
        $mock = new RecordingNewsModel([]);

        Factories::injectMock('models', NewsModel::class, $mock);

        $service = new TestingPublicContentService(new ArrayCache(), false);
        $service->popularNews(3);

        $this->assertSame(
            [
                ['published_at', 'desc'],
                ['created_at', 'desc'],
            ],
            $mock->ordered
        );
        $this->assertSame([3, 0], $mock->findAllParams);
    }

    public function testRelatedNewsPrioritisesCategoryAndTagMatches(): void
    {
        $mock = new RecordingNewsModel([
            [
                'id'                   => 2,
                'slug'                 => 'kategori-utama',
                'title'                => 'Kategori Utama',
                'published_at'         => '2024-01-05 10:00:00',
                'created_at'           => '2024-01-05 09:00:00',
                'primary_category_id'  => 3,
            ],
            [
                'id'                   => 3,
                'slug'                 => 'tag-saja',
                'title'                => 'Tag Saja',
                'published_at'         => '2024-01-04 08:00:00',
                'created_at'           => '2024-01-04 07:00:00',
                'primary_category_id'  => null,
            ],
            [
                'id'                   => 4,
                'slug'                 => 'fallback',
                'title'                => 'Fallback',
                'published_at'         => '2024-01-03 06:00:00',
                'created_at'           => '2024-01-03 05:00:00',
                'primary_category_id'  => null,
            ],
        ]);

        Factories::injectMock('models', NewsModel::class, $mock);

        $hydrateData = [
            [
                'id'               => 2,
                'slug'             => 'kategori-utama',
                'title'            => 'Kategori Utama',
                'published_at'     => '2024-01-05 10:00:00',
                'created_at'       => '2024-01-05 09:00:00',
                'primary_category' => ['id' => 3, 'name' => 'Kategori A', 'slug' => 'kategori-a'],
                'categories'       => [['id' => 3, 'name' => 'Kategori A', 'slug' => 'kategori-a']],
                'tags'             => [['id' => 10, 'name' => 'Tag X', 'slug' => 'tag-x']],
            ],
            [
                'id'               => 3,
                'slug'             => 'tag-saja',
                'title'            => 'Tag Saja',
                'published_at'     => '2024-01-04 08:00:00',
                'created_at'       => '2024-01-04 07:00:00',
                'primary_category' => null,
                'categories'       => [],
                'tags'             => [['id' => 10, 'name' => 'Tag X', 'slug' => 'tag-x']],
            ],
            [
                'id'               => 4,
                'slug'             => 'fallback',
                'title'            => 'Fallback',
                'published_at'     => '2024-01-03 06:00:00',
                'created_at'       => '2024-01-03 05:00:00',
                'primary_category' => null,
                'categories'       => [],
                'tags'             => [],
            ],
        ];

        $service = new TestingPublicContentService(new ArrayCache());
        $service->setHydrateOverride($hydrateData);

        $result = $service->relatedNews(1, 3, [10], 4);

        $this->assertSame(['kategori-utama', 'tag-saja', 'fallback'], array_column($result, 'slug'));
        $this->assertContains(['news.id !=', 1], $mock->wheres);
        $this->assertContains('news.id', $mock->groupByCalls);
        $this->assertSame(
            [
                ['news_category_map', 'news_category_map.news_id = news.id', 'left'],
                ['news_tag_map', 'news_tag_map.news_id = news.id', 'left'],
            ],
            array_slice($mock->joins, 0, 2)
        );
        $this->assertSame([['news_tag_map.tag_id', [10]]], $mock->orWhereIns);
    }
}
