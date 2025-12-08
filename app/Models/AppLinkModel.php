<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * App Link Model
 * 
 * Model untuk mengelola tautan aplikasi OPD terkait
 */
class AppLinkModel extends Model
{
    protected $table            = 'app_links';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'name',
        'description',
        'logo_path',
        'url',
        'is_active',
        'sort_order',
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|max_length[255]',
        'url'  => 'required|valid_url_strict|max_length[500]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama aplikasi wajib diisi.',
            'max_length' => 'Nama aplikasi maksimal 255 karakter.',
        ],
        'url' => [
            'required'         => 'URL aplikasi wajib diisi.',
            'valid_url_strict' => 'URL harus berupa alamat yang valid (contoh: https://example.com).',
            'max_length'       => 'URL maksimal 500 karakter.',
        ],
    ];

    /**
     * Get all active app links ordered by sort_order
     */
    public function getActiveLinks(int $limit = 20): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get all links for admin (including inactive)
     */
    public function getAllForAdmin(): array
    {
        return $this->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get next sort order value
     */
    public function getNextSortOrder(): int
    {
        $max = $this->selectMax('sort_order')->first();
        return ($max['sort_order'] ?? 0) + 1;
    }

    /**
     * Update sort orders
     */
    public function updateSortOrders(array $orders): bool
    {
        $this->db->transStart();

        foreach ($orders as $id => $order) {
            $this->update($id, ['sort_order' => (int) $order]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $id): bool
    {
        $link = $this->find($id);
        if (!$link) {
            return false;
        }

        return $this->update($id, [
            'is_active' => $link['is_active'] ? 0 : 1,
        ]);
    }

    /**
     * Delete link and its logo file
     */
    public function deleteWithLogo(int $id): bool
    {
        $link = $this->find($id);
        if (!$link) {
            return false;
        }

        // Delete logo file if exists (using FileUploadManager for security)
        if (!empty($link['logo_path'])) {
            \App\Libraries\FileUploadManager::deleteFile($link['logo_path']);
        }

        return $this->delete($id);
    }
}
