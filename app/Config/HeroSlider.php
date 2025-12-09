<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Hero Slider Configuration
 * 
 * Konfigurasi untuk pengelolaan hero slider
 * disesuaikan untuk kebutuhan web OPD pemerintahan
 */
class HeroSlider extends BaseConfig
{
    /**
     * Jumlah maksimal slot slider yang bisa dibuat
     * Best practice untuk OPD: 3-7 sudah cukup (user jarang scroll lebih)
     */
    public int $maxSlots = 7;

    /**
     * Jumlah default slider yang dibuat otomatis (jika diperlukan)
     * Best practice untuk government website: 3 slides
     */
    public int $defaultSlots = 3;

    /**
     * Ukuran maksimal file gambar (dalam bytes)
     * 2MB = 2,000,000 bytes
     */
    public int $maxImageSize = 2_000_000;

    /**
     * Format gambar yang diperbolehkan
     */
    public array $allowedImageMimes = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Ekstensi file yang diperbolehkan
     */
    public array $allowedImageExtensions = [
        'jpg',
        'jpeg',
        'png',
        'webp',
    ];

    /**
     * Dimensi minimal gambar (width x height)
     * Untuk hero slider, minimal 1200x600 recommended
     */
    public int $minImageWidth = 1200;
    public int $minImageHeight = 600;

    /**
     * Path relatif untuk menyimpan gambar slider
     */
    public string $uploadPath = 'uploads/hero-sliders';

    /**
     * Jumlah item per halaman di admin
     */
    public int $itemsPerPage = 10;

    /**
     * Source types yang valid
     */
    public array $validSourceTypes = [
        'manual',    // Manual input oleh admin
        'internal',  // Link ke konten internal (news, services, dll)
    ];

    /**
     * Durasi preview slider (dalam detik)
     * Untuk auto-advance di preview
     */
    public int $previewDuration = 5;

    /**
     * Apakah mengaktifkan view count tracking
     */
    public bool $enableViewTracking = true;

    /**
     * Apakah mengaktifkan drag & drop reordering
     */
    public bool $enableDragReorder = true;

    /**
     * Auto-create default sliders from latest news when list is empty
     * Untuk OPD: aktifkan agar otomatis terisi saat pertama kali akses
     */
    public bool $enableAutoCreateDefaults = true;
}
