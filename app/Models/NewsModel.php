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
        'excerpt',
        'thumbnail',
        'published_at',
        'author_id',
        'primary_category_id',
        'is_featured',
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

    /**
     * Get the currently featured news article
     * 
     * @return array|null
     */
    public function getFeaturedNews(): ?array
    {
        $news = $this->select('news.*, u.username as author_name')
            ->join('users u', 'u.id = news.author_id', 'left')
            ->where('news.is_featured', 1)
            ->where('news.published_at <=', date('Y-m-d H:i:s'))
            ->orderBy('news.published_at', 'DESC')
            ->first();

        if (!$news) {
            return null;
        }

        // Get primary category
        if ($news['primary_category_id']) {
            $category = $this->db->table('news_categories')
                ->where('id', $news['primary_category_id'])
                ->get()
                ->getRowArray();
            $news['primary_category'] = $category;
        }

        // Get all categories
        $categories = $this->db->table('news_category_map ncm')
            ->select('nc.*')
            ->join('news_categories nc', 'nc.id = ncm.category_id')
            ->where('ncm.news_id', $news['id'])
            ->get()
            ->getResultArray();
        $news['categories'] = $categories;

        // Get tags
        $tags = $this->db->table('news_tag_map ntm')
            ->select('nt.*')
            ->join('news_tags nt', 'nt.id = ntm.tag_id')
            ->where('ntm.news_id', $news['id'])
            ->get()
            ->getResultArray();
        $news['tags'] = $tags;

        return $news;
    }

    /**
     * Get published news with pagination and optional filtering
     *
     * @param array|null $category
     * @param array|null $tag
     * @param int $perPage
     * @return array
     */
    public function getPublished($category = null, $tag = null, int $perPage = 10): array
    {
        $this->select('news.*, nc.name as cat_name, nc.slug as cat_slug, nc.icon as cat_icon')
             ->join('news_categories nc', 'nc.id = news.primary_category_id', 'left')
             ->where('news.published_at <=', date('Y-m-d H:i:s'))
             ->orderBy('news.published_at', 'DESC');

        if ($category) {
            $this->join('news_category_map ncm', 'ncm.news_id = news.id')
                 ->where('ncm.category_id', $category['id']);
        }

        if ($tag) {
            $this->join('news_tag_map ntm', 'ntm.news_id = news.id')
                 ->where('ntm.tag_id', $tag['id']);
        }

        $results = $this->paginate($perPage, 'default');

        // Format primary category for view consistency
        foreach ($results as &$item) {
            if ($item['primary_category_id']) {
                $item['primary_category'] = [
                    'name' => $item['cat_name'],
                    'slug' => $item['cat_slug'],
                    'icon' => $item['cat_icon'] ?? 'bx-news',
                ];
            }
        }

        return $results;
    }
}
