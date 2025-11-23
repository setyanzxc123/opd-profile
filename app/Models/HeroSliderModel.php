<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Hero Slider Model
 * 
 * Model untuk tabel hero_sliders dengan validation rules lengkap
 */
class HeroSliderModel extends Model
{
    protected $table            = 'hero_sliders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields = [
        'title',
        'subtitle',
        'description',
        'button_text',
        'button_link',
        'image_path',
        'image_alt',
        'source_type',
        'source_ref_id',
        'is_active',
        'view_count',
        'sort_order',
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation Rules
    protected $validationRules = [
        'title' => [
            'rules'  => 'required|max_length[255]',
            'errors' => [
                'required'   => 'Judul slider wajib diisi.',
                'max_length' => 'Judul terlalu panjang (maksimal 255 karakter).',
            ],
        ],
        'subtitle' => [
            'rules'  => 'permit_empty|max_length[255]',
            'errors' => [
                'max_length' => 'Subtitle terlalu panjang (maksimal 255 karakter).',
            ],
        ],
        'description' => [
            'rules'  => 'permit_empty|max_length[1000]',
            'errors' => [
                'max_length' => 'Deskripsi terlalu panjang (maksimal 1000 karakter).',
            ],
        ],
        'button_text' => [
            'rules'  => 'permit_empty|max_length[50]',
            'errors' => [
                'max_length' => 'Teks tombol terlalu panjang (maksimal 50 karakter).',
            ],
        ],
        'button_link' => [
            'rules'  => 'permit_empty|max_length[500]|valid_url_strict',
            'errors' => [
                'max_length'      => 'Link terlalu panjang (maksimal 500 karakter).',
                'valid_url_strict' => 'Format URL tidak valid.',
            ],
        ],
        'image_path' => [
            'rules'  => 'permit_empty|max_length[500]',
            'errors' => [
                'max_length' => 'Path gambar terlalu panjang.',
            ],
        ],
        'image_alt' => [
            'rules'  => 'permit_empty|max_length[255]',
            'errors' => [
                'max_length' => 'Alt text terlalu panjang (maksimal 255 karakter).',
            ],
        ],
        'source_type' => [
            'rules'  => 'required|in_list[manual,internal]',
            'errors' => [
                'required' => 'Tipe sumber konten wajib dipilih.',
                'in_list'  => 'Tipe sumber tidak valid. Harus "manual" atau "internal".',
            ],
        ],
        'source_ref_id' => [
            'rules'  => 'permit_empty|max_length[100]',
            'errors' => [
                'max_length' => 'Referensi ID terlalu panjang.',
            ],
        ],
        'is_active' => [
            'rules'  => 'permit_empty|in_list[0,1]',
            'errors' => [
                'in_list' => 'Status aktif harus 0 atau 1.',
            ],
        ],
        'view_count' => [
            'rules'  => 'permit_empty|integer|greater_than_equal_to[0]',
            'errors' => [
                'integer'              => 'View count harus berupa angka.',
                'greater_than_equal_to' => 'View count tidak boleh negatif.',
            ],
        ],
        'sort_order' => [
            'rules'  => 'permit_empty|integer|greater_than_equal_to[0]',
            'errors' => [
                'integer'              => 'Sort order harus berupa angka.',
                'greater_than_equal_to' => 'Sort order tidak boleh negatif.',
            ],
        ],
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setDefaultValues'];
    protected $beforeUpdate   = [];
    protected $afterInsert    = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Set default values before insert
     */
    protected function setDefaultValues(array $data): array
    {
        // Set default view_count
        if (!isset($data['data']['view_count'])) {
            $data['data']['view_count'] = 0;
        }

        // Set default is_active
        if (!isset($data['data']['is_active'])) {
            $data['data']['is_active'] = 1;
        }

        // Set default sort_order (at the end)
        if (!isset($data['data']['sort_order'])) {
            $lastSlider = $this->selectMax('sort_order')->first();
            $data['data']['sort_order'] = ($lastSlider['sort_order'] ?? 0) + 1;
        }

        // Set default source_type
        if (!isset($data['data']['source_type'])) {
            $data['data']['source_type'] = 'manual';
        }

        return $data;
    }

    /**
     * Get paginated sliders ordered by sort_order
     */
    public function getPaginated(int $perPage = 10): array
    {
        return $this->orderBy('sort_order', 'ASC')
                    ->orderBy('created_at', 'DESC')
                    ->paginate($perPage) ?: [];
    }

    /**
     * Get sliders for public display
     * Renamed semantically but kept method name for backward compatibility
     */
    public function getActiveSlides(int $limit = 10): array
    {
        return $this->orderBy('sort_order', 'ASC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit) ?: [];
    }

    /**
     * Increment view count for a slider
     */
    public function incrementViewCount(int $id): bool
    {
        return $this->where('id', $id)
                    ->set('view_count', 'view_count + 1', false)
                    ->update();
    }

    /**
     * Get total count (override untuk better performance)
     */
    public function countAll(): int
    {
        return (int) $this->countAllResults(false);
    }

    /**
     * Get sliders by source type
     */
    public function getBySourceType(string $sourceType, int $limit = null): array
    {
        $builder = $this->where('source_type', $sourceType)
                        ->orderBy('sort_order', 'ASC');

        if ($limit) {
            return $builder->findAll($limit) ?: [];
        }

        return $builder->findAll() ?: [];
    }

    /**
     * Get slider statistics
     */
    public function getStatistics(): array
    {
        $total = $this->countAll();
        $active = $this->where('is_active', 1)->countAllResults(false);
        $inactive = $total - $active;
        $totalViews = $this->selectSum('view_count')->first()['view_count'] ?? 0;

        return [
            'total'       => $total,
            'active'      => $active,
            'inactive'    => $inactive,
            'total_views' => $totalViews,
        ];
    }

    /**
     * Reorder sort_order after deletion
     * Call this after deleting a slider to keep sort_order sequential
     */
    public function reorderSortOrder(): bool
    {
        $sliders = $this->orderBy('sort_order', 'ASC')->findAll();
        
        foreach ($sliders as $index => $slider) {
            if ($slider['sort_order'] !== $index) {
                $this->update($slider['id'], ['sort_order' => $index]);
            }
        }

        return true;
    }
}
