<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsCategoryModel extends Model
{
    protected $table         = 'news_categories';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    public function getActiveOrdered(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->findAll();
    }

    /**
     * @param array<int,string> $slugs
     */
    public function findBySlugs(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        $rows = $this->whereIn('slug', $slugs)->findAll();

        $mapped = [];
        foreach ($rows as $row) {
            $mapped[$row['slug']] = $row;
        }

        return $mapped;
    }
}
