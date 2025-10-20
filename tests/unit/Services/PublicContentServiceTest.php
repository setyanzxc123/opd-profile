<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\NewsModel;
use App\Services\PublicContentService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;

final class RecordingNewsModel extends NewsModel
{
    public array $joins = [];
    public array $wheres = [];
    public array $ordered = [];
    public array $selected = [];
    public array $paginateParams = [];
    public array $groupByCalls = [];
    public array $searchCalls = [];
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

        $service = new PublicContentService();
        $service->paginatedNews(6, null, 99, null);

        $this->assertSame([['news_category_map', 'news_category_map.news_id = news.id', 'inner']], $mock->joins);
        $this->assertContains(['news_category_map.category_id', 99], $mock->wheres);
        $this->assertSame([6, 'default'], $mock->paginateParams);
    }

    public function testPaginatedNewsAppliesTagFilter(): void
    {
        $mock = new RecordingNewsModel([['id' => 5]]);

        Factories::injectMock('models', NewsModel::class, $mock);

        $service = new PublicContentService();
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

        $service = new PublicContentService();
        $service->paginatedNews(6, 'layanan', 3, null);

        $this->assertSame(
            ['groupStart', ['like', 'title', 'layanan'], ['orLike', 'content', 'layanan'], 'groupEnd'],
            $mock->searchCalls
        );
        $this->assertContains(['news_category_map.category_id', 3], $mock->wheres);
        $this->assertSame([6, 'default'], $mock->paginateParams);
    }
}
