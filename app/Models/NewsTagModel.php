<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsTagModel extends Model
{
    protected $table         = 'news_tags';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'name',
        'slug',
    ];

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

    public function getAllOrdered(): array
    {
        return $this->orderBy('name', 'asc')->findAll();
    }
}
