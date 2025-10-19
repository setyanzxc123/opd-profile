<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\NewsModel;
use App\Services\PublicContentService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Factories;

final class PublicContentServiceTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Factories::reset('models');
        parent::tearDown();
    }

    public function testPaginatedNewsAppliesCategoryFilter(): void
    {
        $mock = new class extends NewsModel {
            public array $joins = [];
            public array $wheres = [];
            public array $ordered = [];
            public array $selected = [];
            public array $paginateParams = [];
            public mixed $pager = null;
            private array $result;

            public function __construct()
            {
                $this->result = [['id' => 1]];
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

            public function paginate(int $perPage = 20, string $group = 'default', int $page = 1, ?int $segment = null)
            {
                $this->paginateParams = [$perPage, $group];
                return $this->result;
            }
        };

        Factories::injectMock('models', NewsModel::class, $mock);

        $service = new PublicContentService();
        $service->paginatedNews(6, null, 99, null);

        $this->assertSame([['news_category_map', 'news_category_map.news_id = news.id', 'inner']], $mock->joins);
        $this->assertContains(['news_category_map.category_id', 99], $mock->wheres);
        $this->assertSame([6, 'default'], $mock->paginateParams);
    }

    public function testPaginatedNewsAppliesTagFilter(): void
    {
        $mock = new class extends NewsModel {
            public array $joins = [];
            public array $wheres = [];
            public array $ordered = [];
            public array $selected = [];
            public array $paginateParams = [];
            public mixed $pager = null;
            private array $result;

            public function __construct()
            {
                $this->result = [['id' => 5]];
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

            public function paginate(int $perPage = 20, string $group = 'default', int $page = 1, ?int $segment = null)
            {
                $this->paginateParams = [$perPage, $group];
                return $this->result;
            }
        };

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
}
