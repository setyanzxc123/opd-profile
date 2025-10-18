<?php
namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table         = 'news';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false; // using explicit published_at only
    protected $allowedFields = [
        'title',
        'slug',
        'content',
        'thumbnail',
        'published_at',
        'author_id',
        'primary_category_id',
    ];

    /**
     * @param array<int,int> $categoryIds
     */
    public function syncCategories(int $newsId, array $categoryIds): void
    {
        $builder = $this->db->table('news_category_map');
        $builder->where('news_id', $newsId)->delete();

        if ($categoryIds === []) {
            return;
        }

        $rows = [];
        foreach (array_unique($categoryIds) as $categoryId) {
            $rows[] = [
                'news_id'     => $newsId,
                'category_id' => $categoryId,
            ];
        }

        if ($rows !== []) {
            $builder->insertBatch($rows);
        }
    }

    /**
     * @param array<int,int> $tagIds
     */
    public function syncTags(int $newsId, array $tagIds): void
    {
        $builder = $this->db->table('news_tag_map');
        $builder->where('news_id', $newsId)->delete();

        if ($tagIds === []) {
            return;
        }

        $rows = [];
        foreach (array_unique($tagIds) as $tagId) {
            $rows[] = [
                'news_id' => $newsId,
                'tag_id'  => $tagId,
            ];
        }

        if ($rows !== []) {
            $builder->insertBatch($rows);
        }
    }

    /**
     * @return array<int,int>
     */
    public function getCategoryIds(int $newsId): array
    {
        $rows = $this->db->table('news_category_map')
            ->select('category_id')
            ->where('news_id', $newsId)
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row) => (int) $row['category_id'], $rows);
    }

    /**
     * @return array<int,int>
     */
    public function getTagIds(int $newsId): array
    {
        $rows = $this->db->table('news_tag_map')
            ->select('tag_id')
            ->where('news_id', $newsId)
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row) => (int) $row['tag_id'], $rows);
    }
}
