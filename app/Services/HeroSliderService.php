<?php

namespace App\Services;

use App\Models\HeroSliderModel;
use App\Models\NewsModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\HeroSlider as HeroSliderConfig;

/**
 * Hero Slider Service
 * 
 * Menangani business logic untuk pengelolaan hero slider
 * Memisahkan logic dari controller untuk better testability
 */
class HeroSliderService
{
    protected HeroSliderModel $model;
    protected HeroSliderConfig $config;

    public function __construct()
    {
        $this->model = model(HeroSliderModel::class);
        $this->config = config('HeroSlider');
    }

    /**
     * Get all sliders with pagination
     */
    public function getPaginatedSliders(int $perPage = null): array
    {
        $perPage = $perPage ?? $this->config->itemsPerPage;
        
        return [
            'sliders' => $this->model->getPaginated($perPage),
            'pager'   => $this->model->pager,
        ];
    }

    /**
     * Get active sliders for public display
     */
    public function getActiveSliders(int $limit = null): array
    {
        $limit = $limit ?? $this->config->defaultSlots;
        return $this->model->getActiveSlides($limit);
    }

    /**
     * Get slider by ID
     */
    public function getSliderById(int $id): ?array
    {
        return $this->model->find($id);
    }

    /**
     * Check if we can add more sliders
     */
    public function canAddMoreSliders(): bool
    {
        return $this->model->countAll() < $this->config->maxSlots;
    }

    /**
     * Get remaining slot count
     */
    public function getRemainingSlots(): int
    {
        $current = $this->model->countAll();
        return max(0, $this->config->maxSlots - $current);
    }

    /**
     * Create new slider
     * 
     * @param array $data Slider data
     * @param UploadedFile|null $imageFile Image file upload
     * @return array ['success' => bool, 'message' => string, 'id' => int|null]
     */
    public function createSlider(array $data, ?UploadedFile $imageFile = null): array
    {
        // Check slot limit
        if (!$this->canAddMoreSliders()) {
            return [
                'success' => false,
                'message' => "Slot maksimum ({$this->config->maxSlots}) sudah tercapai.",
                'id'      => null,
            ];
        }

        // Start transaction
        $this->model->db->transStart();

        try {
            // Handle image upload
            if ($imageFile && $imageFile->isValid()) {
                $validationResult = $this->validateImage($imageFile);
                
                if ($validationResult !== true) {
                    $this->model->db->transRollback();
                    return [
                        'success' => false,
                        'message' => $validationResult,
                        'id'      => null,
                    ];
                }

                $imagePath = $this->uploadImage($imageFile);
                
                if (!$imagePath) {
                    $this->model->db->transRollback();
                    return [
                        'success' => false,
                        'message' => 'Gagal mengunggah gambar.',
                        'id'      => null,
                    ];
                }

                $data['image_path'] = $imagePath;
            }

            // Sanitize and prepare data
            $data = $this->sanitizeData($data);

            // Insert to database
            if (!$this->model->insert($data)) {
                $this->model->db->transRollback();
                
                // Clean up uploaded image if insert failed
                if (isset($data['image_path'])) {
                    $this->deleteImage($data['image_path']);
                }

                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan slider: ' . implode(', ', $this->model->errors()),
                    'id'      => null,
                ];
            }

            $sliderId = $this->model->getInsertID();

            // Commit transaction
            $this->model->db->transComplete();

            if ($this->model->db->transStatus() === false) {
                // Clean up image on transaction failure
                if (isset($data['image_path'])) {
                    $this->deleteImage($data['image_path']);
                }

                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan slider (transaction failed).',
                    'id'      => null,
                ];
            }

            return [
                'success' => true,
                'message' => 'Slider berhasil dibuat.',
                'id'      => $sliderId,
            ];

        } catch (\Throwable $e) {
            $this->model->db->transRollback();
            
            // Clean up uploaded image on error
            if (isset($data['image_path'])) {
                $this->deleteImage($data['image_path']);
            }

            log_message('error', 'Create slider failed: {error}', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat slider.',
                'id'      => null,
            ];
        }
    }

    /**
     * Update existing slider
     * 
     * @param int $id Slider ID
     * @param array $data Updated data
     * @param UploadedFile|null $imageFile New image file (optional)
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateSlider(int $id, array $data, ?UploadedFile $imageFile = null): array
    {
        $existingSlider = $this->model->find($id);
        
        if (!$existingSlider) {
            return [
                'success' => false,
                'message' => 'Slider tidak ditemukan.',
            ];
        }

        $oldImagePath = $existingSlider['image_path'] ?? null;

        // Start transaction
        $this->model->db->transStart();

        try {
            // Handle new image upload
            if ($imageFile && $imageFile->isValid()) {
                $validationResult = $this->validateImage($imageFile);
                
                if ($validationResult !== true) {
                    $this->model->db->transRollback();
                    return [
                        'success' => false,
                        'message' => $validationResult,
                    ];
                }

                $imagePath = $this->uploadImage($imageFile);
                
                if (!$imagePath) {
                    $this->model->db->transRollback();
                    return [
                        'success' => false,
                        'message' => 'Gagal mengunggah gambar baru.',
                    ];
                }

                $data['image_path'] = $imagePath;
            }

            // Sanitize data
            $data = $this->sanitizeData($data);
            $data['id'] = $id;

            // Update database
            if (!$this->model->save($data)) {
                $this->model->db->transRollback();
                
                // Clean up new image if update failed
                if (isset($data['image_path']) && $data['image_path'] !== $oldImagePath) {
                    $this->deleteImage($data['image_path']);
                }

                return [
                    'success' => false,
                    'message' => 'Gagal memperbarui slider: ' . implode(', ', $this->model->errors()),
                ];
            }

            // Commit transaction
            $this->model->db->transComplete();

            if ($this->model->db->transStatus() === false) {
                // Rollback image changes
                if (isset($data['image_path']) && $data['image_path'] !== $oldImagePath) {
                    $this->deleteImage($data['image_path']);
                }

                return [
                    'success' => false,
                    'message' => 'Gagal memperbarui slider (transaction failed).',
                ];
            }

            // Delete old image only after successful update
            if (isset($data['image_path']) && $oldImagePath && $data['image_path'] !== $oldImagePath) {
                $this->deleteImage($oldImagePath);
            }

            return [
                'success' => true,
                'message' => 'Slider berhasil diperbarui.',
            ];

        } catch (\Throwable $e) {
            $this->model->db->transRollback();
            
            // Clean up new image on error
            if (isset($data['image_path']) && $data['image_path'] !== $oldImagePath) {
                $this->deleteImage($data['image_path']);
            }

            log_message('error', 'Update slider failed: {error}', [
                'error' => $e->getMessage(),
                'id'    => $id,
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui slider.',
            ];
        }
    }

    /**
     * Delete slider
     */
    public function deleteSlider(int $id): array
    {
        $slider = $this->model->find($id);
        
        if (!$slider) {
            return [
                'success' => false,
                'message' => 'Slider tidak ditemukan.',
            ];
        }

        // Start transaction
        $this->model->db->transStart();

        try {
            // Delete from database first
            if (!$this->model->delete($id)) {
                $this->model->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus slider dari database.',
                ];
            }

            // Commit transaction
            $this->model->db->transComplete();

            if ($this->model->db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus slider (transaction failed).',
                ];
            }

            // Delete image file after successful DB deletion
            if (!empty($slider['image_path'])) {
                $this->deleteImage($slider['image_path']);
            }

            return [
                'success' => true,
                'message' => 'Slider berhasil dihapus.',
            ];

        } catch (\Throwable $e) {
            $this->model->db->transRollback();

            log_message('error', 'Delete slider failed: {error}', [
                'error' => $e->getMessage(),
                'id'    => $id,
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus slider.',
            ];
        }
    }

    /**
     * Update sort order
     */
    public function updateSortOrder(array $orderData): array
    {
        if (empty($orderData) || !is_array($orderData)) {
            return [
                'success' => false,
                'message' => 'Data urutan tidak valid.',
            ];
        }

        $this->model->db->transStart();

        try {
            foreach ($orderData as $position => $id) {
                $this->model->update($id, ['sort_order' => $position]);
            }

            $this->model->db->transComplete();

            if ($this->model->db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Gagal memperbarui urutan.',
                ];
            }

            return [
                'success' => true,
                'message' => 'Urutan slider berhasil diperbarui.',
            ];

        } catch (\Throwable $e) {
            $this->model->db->transRollback();

            log_message('error', 'Update sort order failed: {error}', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui urutan.',
            ];
        }
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(int $id): bool
    {
        if (!$this->config->enableViewTracking) {
            return false;
        }

        return $this->model->incrementViewCount($id);
    }

    /**
     * Get news options for internal linking
     */
    public function getNewsOptions(int $limit = 10): array
    {
        try {
            return model(NewsModel::class)
                ->select('id, title, slug, thumbnail, published_at')
                ->orderBy('published_at', 'DESC')
                ->findAll($limit);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch news options: {error}', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Ensure default sliders exist
     * Auto-create 3 sliders from latest news if list is empty
     * 
     * @return array ['success' => bool, 'created' => int, 'message' => string]
     */
    public function ensureDefaultSliders(): array
    {
        // Check if feature is enabled
        if (!$this->config->enableAutoCreateDefaults) {
            return [
                'success' => true,
                'created' => 0,
                'message' => 'Auto-create disabled.',
            ];
        }

        // Only create if no sliders exist
        $existing = $this->model->countAll();
        
        if ($existing > 0) {
            return [
                'success' => true,
                'created' => 0,
                'message' => 'Slider sudah ada.',
            ];
        }

        // Get latest news
        $latestNews = $this->getNewsOptions($this->config->defaultSlots);

        if (empty($latestNews)) {
            return [
                'success' => false,
                'created' => 0,
                'message' => 'Tidak ada berita untuk membuat slider default.',
            ];
        }

        $created = 0;
        $errors = [];

        foreach ($latestNews as $index => $news) {
            try {
                $sliderData = [
                    'title' => $news['title'],
                    'subtitle' => 'Berita Terbaru',
                    'description' => '',
                    'button_text' => 'Selengkapnya',
                    'button_link' => site_url('berita/' . ($news['slug'] ?? $news['id'])),
                    'image_path' => $news['thumbnail'] ?? null,
                    'image_alt' => $news['title'],
                    'source_type' => 'internal',
                    'source_ref_id' => 'news:' . $news['id'],
                    'is_active' => 1,
                    'view_count' => 0,
                    'sort_order' => $index,
                ];

                if ($this->model->insert($sliderData)) {
                    $created++;
                } else {
                    $errors[] = "Failed to create slider for: {$news['title']}";
                }

            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
                log_message('error', 'Auto-create slider failed: {error}', [
                    'error' => $e->getMessage(),
                    'news_id' => $news['id'],
                ]);
            }
        }

        if ($created > 0) {
            return [
                'success' => true,
                'created' => $created,
                'message' => "{$created} slider default berhasil dibuat dari berita terbaru.",
            ];
        }

        return [
            'success' => false,
            'created' => 0,
            'message' => 'Gagal membuat slider default: ' . implode(', ', $errors),
        ];
    }


    /**
     * Validate image file
     * 
     * @return true|string True if valid, error message if invalid
     */
    protected function validateImage(UploadedFile $file): bool|string
    {
        // Check if file is valid
        if (!$file->isValid()) {
            return 'File gambar tidak valid.';
        }

        // Check file size
        if ($file->getSize() > $this->config->maxImageSize) {
            $maxSizeMB = round($this->config->maxImageSize / 1_000_000, 1);
            return "Ukuran file terlalu besar. Maksimal {$maxSizeMB}MB.";
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), $this->config->allowedImageMimes, true)) {
            return 'Format gambar harus JPG, PNG, atau WebP.';
        }

        // Check file extension
        if (!in_array(strtolower($file->getExtension()), $this->config->allowedImageExtensions, true)) {
            return 'Ekstensi file tidak diizinkan.';
        }

        // Check image dimensions
        $imageInfo = getimagesize($file->getTempName());
        
        if ($imageInfo === false) {
            return 'File bukan gambar yang valid.';
        }

        [$width, $height] = $imageInfo;

        if ($width < $this->config->minImageWidth || $height < $this->config->minImageHeight) {
            return "Dimensi gambar minimal {$this->config->minImageWidth}x{$this->config->minImageHeight} pixels. " .
                   "Gambar Anda: {$width}x{$height} pixels.";
        }

        return true;
    }

    /**
     * Upload image file
     * 
     * @return string|null Relative path to uploaded image, or null on failure
     */
    protected function uploadImage(UploadedFile $file): ?string
    {
        try {
            $uploadDir = FCPATH . $this->config->uploadPath;

            // Create directory if not exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $newName = $file->getRandomName();

            // Move file
            if (!$file->move($uploadDir, $newName)) {
                log_message('error', 'Failed to move uploaded file to {dir}', [
                    'dir' => $uploadDir,
                ]);
                return null;
            }

            // Return relative path
            return $this->config->uploadPath . '/' . $newName;

        } catch (\Throwable $e) {
            log_message('error', 'Image upload failed: {error}', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Delete image file
     */
    protected function deleteImage(string $relativePath): bool
    {
        try {
            if (empty($relativePath)) {
                return false;
            }

            $fullPath = FCPATH . ltrim($relativePath, '/');

            if (file_exists($fullPath)) {
                return unlink($fullPath);
            }

            return false;

        } catch (\Throwable $e) {
            log_message('error', 'Image deletion failed: {error}', [
                'error' => $e->getMessage(),
                'path'  => $relativePath,
            ]);
            return false;
        }
    }

    /**
     * Sanitize input data
     */
    protected function sanitizeData(array $data): array
    {
        // Remove unwanted fields
        unset($data['id'], $data['created_at'], $data['updated_at']);

        // Trim string values
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }

        // Ensure required fields have defaults
        $data['source_type'] = $data['source_type'] ?? 'manual';
        $data['is_active'] = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        return $data;
    }
}
