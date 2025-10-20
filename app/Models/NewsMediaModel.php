<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsMediaModel extends Model
{
    protected $table            = 'news_media';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $allowedFields    = [
        'news_id',
        'media_type',
        'file_path',
        'external_url',
        'caption',
        'metadata',
        'is_cover',
        'sort_order',
    ];

    /**
     * @param array{
     *     delete?: array<int,int>,
     *     update?: array<int,array<string,mixed>>,
     *     insert?: array<int,array<string,mixed>>
     * } $changes
     *
     * @return array<int,array<string,mixed>>
     */
    public function syncMedia(int $newsId, array $changes): array
    {
        $deleteIds = $changes['delete'] ?? [];
        $updates   = $changes['update'] ?? [];
        $inserts   = $changes['insert'] ?? [];

        $this->db->transStart();

        if ($deleteIds !== []) {
            $this->whereIn('id', $deleteIds)->delete();
        }

        foreach ($updates as $update) {
            $id = (int) ($update['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $data = [
                'caption'     => $this->normaliseText($update['caption'] ?? null),
                'metadata'    => $update['metadata'] ?? null,
                'is_cover'    => (int) ($update['is_cover'] ?? 0),
                'sort_order'  => (int) ($update['sort_order'] ?? 0),
            ];

            if (array_key_exists('file_path', $update)) {
                $data['file_path'] = $update['file_path'];
            }

            if (array_key_exists('external_url', $update)) {
                $data['external_url'] = $update['external_url'];
            }

            $this->update($id, $data);
        }

        foreach ($inserts as $insert) {
            $data = [
                'news_id'      => $newsId,
                'media_type'   => $insert['media_type'] ?? 'image',
                'file_path'    => $insert['file_path'] ?? null,
                'external_url' => $insert['external_url'] ?? null,
                'caption'      => $this->normaliseText($insert['caption'] ?? null),
                'metadata'     => $insert['metadata'] ?? null,
                'is_cover'     => (int) ($insert['is_cover'] ?? 0),
                'sort_order'   => (int) ($insert['sort_order'] ?? 0),
            ];

            $this->insert($data);
        }

        $this->db->transComplete();

        return $this->where('news_id', $newsId)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->findAll();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function byNews(int $newsId): array
    {
        return $this->where('news_id', $newsId)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->findAll();
    }

    private function normaliseText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $text = trim($text);

        return $text !== '' ? $text : null;
    }
}
