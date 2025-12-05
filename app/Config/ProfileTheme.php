<?php

namespace Config;

class ProfileTheme
{
    public string $defaultPreset = 'light-blue';

    /**
     * Rasio kontras minimum untuk validasi tema.
     * Nilai 3.5 memberi ruang bagi warna cerah namun tetap terbaca.
     */
    public float $minimumContrast = 3.5;

    /**
     * Daftar preset yang ditampilkan di panel admin.
     *
     * @var array<string,array{label:string,primary:string,surface:string}>
     */
    public array $presets = [
        // Tema cerah
        'light-red' => [
            'label'   => 'Merah Cerah',
            'primary' => '#DC2626',
            'surface' => '#FFF5F5',
            'tone'    => 'light',
        ],
        'light-yellow' => [
            'label'   => 'Kuning Cerah',
            'primary' => '#D97706',
            'surface' => '#FFF8E1',
            'tone'    => 'light',
        ],
        'light-green' => [
            'label'   => 'Hijau Cerah',
            'primary' => '#16A34A',
            'surface' => '#ECFDF5',
            'tone'    => 'light',
        ],
        'light-blue' => [
            'label'   => 'Biru Cerah',
            'primary' => '#1D4ED8',
            'surface' => '#EEF4FF',
            'tone'    => 'light',
        ],
        'light-orange' => [
            'label'   => 'Oranye Cerah',
            'primary' => '#EA580C',
            'surface' => '#FFF1E6',
            'tone'    => 'light',
        ],
        'light-black' => [
            'label'   => 'Hitam Cerah',
            'primary' => '#374151',
            'surface' => '#F4F6FB',
            'tone'    => 'light',
        ],
        'light-white' => [
            'label'   => 'Putih Cerah',
            'primary' => '#FFFFFF',
            'surface' => '#F9FAFB',
            'tone'    => 'light',
        ],

        // Tema gelap
        'dark-red' => [
            'label'   => 'Merah Gelap',
            'primary' => '#7F1D1D',
            'surface' => '#FBE4E4',
            'tone'    => 'dark',
        ],
        'dark-yellow' => [
            'label'   => 'Kuning Gelap',
            'primary' => '#B45309',
            'surface' => '#FCEFD9',
            'tone'    => 'dark',
        ],
        'dark-green' => [
            'label'   => 'Hijau Gelap',
            'primary' => '#065F46',
            'surface' => '#E6F4F1',
            'tone'    => 'dark',
        ],
        'dark-blue' => [
            'label'   => 'Biru Gelap',
            'primary' => '#1E3A8A',
            'surface' => '#E5EAF8',
            'tone'    => 'dark',
        ],
        'dark-orange' => [
            'label'   => 'Oranye Gelap',
            'primary' => '#C2410C',
            'surface' => '#FFEADF',
            'tone'    => 'dark',
        ],
        'dark-black' => [
            'label'   => 'Hitam Gelap',
            'primary' => '#0F172A',
            'surface' => '#F4F6FB',
            'tone'    => 'dark',
        ],
        'dark-white' => [
            'label'   => 'Putih Kontras',
            'primary' => '#1F2937',
            'surface' => '#F9FAFB',
            'tone'    => 'dark',
        ],
    ];
}
