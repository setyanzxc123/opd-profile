<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class CoreSeeder extends Seeder
{
    public function run()
    {
        // Ensure admin user exists
        $usersBuilder = $this->db->table('users');
        $admin        = $usersBuilder->where('username', 'admin')->get()->getRow();
        if (! $admin) {
            $now = Time::now('UTC')->toDateTimeString();
            $usersBuilder->insert([
                'username'      => 'admin',
                'email'         => 'admin@example.com',
                'password_hash' => password_hash('Admin123!', PASSWORD_DEFAULT),
                'name'          => 'Administrator',
                'role'          => 'admin',
                'is_active'     => 1,
                'created_at'    => $now,
            ]);
            $adminId = (int) $this->db->insertID();
        } else {
            $adminId = (int) $admin->id;
        }

        // Seed OPD profile if empty
        if (! $this->db->table('opd_profile')->countAllResults()) {
            $this->db->table('opd_profile')->insert([
                'name'        => 'Dinas Komunikasi dan Informatika',
                'description' => 'Profil singkat OPD. Perbarui sesuai kebutuhan.',
                'vision'      => 'Menjadi penyedia layanan informasi publik yang transparan dan cepat.',
                'mission'     => 'Menyediakan infrastruktur TI yang andal dan meningkatkan literasi digital masyarakat.',
                'address'     => 'Jl. Contoh No. 123, Kota Contoh',
                'latitude'    => '-6.1753920',
                'longitude'   => '106.8271530',
                'map_zoom'    => 16,
                'map_display' => 1,
                'phone'       => '(021) 1234567',
                'email'       => 'info@opd.go.id',
                'created_at'  => Time::now('UTC')->toDateTimeString(),
            ]);
        }

        // Seed services if empty
        if (! $this->db->table('services')->countAllResults()) {
            $this->db->table('services')->insertBatch([
                [
                    'title'          => 'Layanan Informasi Publik',
                    'slug'           => 'layanan-informasi-publik',
                    'description'    => 'Permintaan informasi dan dokumentasi resmi instansi.',
                    'requirements'   => 'Isi formulir, sertakan identitas pemohon.',
                    'fees'           => 'Gratis',
                    'processing_time'=> 'Maksimal 5 hari kerja',
                    'is_active'      => 1,
                    'sort_order'     => 1,
                    'created_at'     => Time::now('UTC')->toDateTimeString(),
                ],
                [
                    'title'          => 'Layanan Pengaduan Masyarakat',
                    'slug'           => 'layanan-pengaduan-masyarakat',
                    'description'    => 'Penanganan aduan terkait layanan OPD.',
                    'requirements'   => 'Aduan tertulis dengan data pendukung.',
                    'fees'           => 'Gratis',
                    'processing_time'=> 'Respon awal dalam 3 hari kerja',
                    'is_active'      => 1,
                    'sort_order'     => 2,
                    'created_at'     => Time::now('UTC')->toDateTimeString(),
                ],
            ]);
        }

        // Seed news taxonomy
        $categoryIdMap = [];
        $tagIdMap       = [];
        $nowUtc         = Time::now('UTC')->toDateTimeString();

        if ($this->db->tableExists('news_categories')) {
            $categoriesTable = $this->db->table('news_categories');
            if (! $categoriesTable->countAllResults()) {
                $categoriesTable->insertBatch([
                    [
                        'name'        => 'Pengumuman',
                        'slug'        => 'pengumuman',
                        'description' => 'Informasi resmi dan pemberitahuan penting untuk masyarakat.',
                        'sort_order'  => 1,
                        'created_at'  => $nowUtc,
                    ],
                    [
                        'name'        => 'Program Kegiatan',
                        'slug'        => 'program-kegiatan',
                        'description' => 'Liputan program dan kegiatan unggulan OPD.',
                        'sort_order'  => 2,
                        'created_at'  => $nowUtc,
                    ],
                    [
                        'name'        => 'Pelayanan Publik',
                        'slug'        => 'pelayanan-publik',
                        'description' => 'Perubahan kebijakan serta pengumuman layanan publik.',
                        'sort_order'  => 3,
                        'created_at'  => $nowUtc,
                    ],
                ]);
            }

            $categoryIdMap = array_column(
                $categoriesTable->select('id, slug')->get()->getResultArray(),
                'id',
                'slug'
            );
        }

        if ($this->db->tableExists('news_tags')) {
            $tagsTable = $this->db->table('news_tags');
            if (! $tagsTable->countAllResults()) {
                $tagsTable->insertBatch([
                    [
                        'name'       => 'Digitalisasi',
                        'slug'       => 'digitalisasi',
                        'created_at' => $nowUtc,
                    ],
                    [
                        'name'       => 'Transparansi',
                        'slug'       => 'transparansi',
                        'created_at' => $nowUtc,
                    ],
                    [
                        'name'       => 'Pelayanan Prima',
                        'slug'       => 'pelayanan-prima',
                        'created_at' => $nowUtc,
                    ],
                ]);
            }

            $tagIdMap = array_column(
                $tagsTable->select('id, slug')->get()->getResultArray(),
                'id',
                'slug'
            );
        }

        // Seed news samples
        if (! $this->db->table('news')->countAllResults()) {
            $newsTable        = $this->db->table('news');
            $categoryMapTable = $this->db->tableExists('news_category_map') ? $this->db->table('news_category_map') : null;
            $tagMapTable      = $this->db->tableExists('news_tag_map') ? $this->db->table('news_tag_map') : null;

            $newsSeed = [
                [
                    'title'       => 'Peluncuran Portal OPD Baru',
                    'slug'        => 'peluncuran-portal-opd-baru',
                    'content'     => '<p>Portal resmi OPD kini hadir dengan tampilan dan fitur terbaru.</p>',
                    'thumbnail'   => null,
                    'publishedAt' => Time::now('UTC')->subDays(2)->toDateTimeString(),
                    'createdAt'   => Time::now('UTC')->subDays(2)->toDateTimeString(),
                    'categories'  => ['pengumuman'],
                    'tags'        => ['digitalisasi', 'transparansi'],
                ],
                [
                    'title'       => 'Workshop Literasi Digital',
                    'slug'        => 'workshop-literasi-digital',
                    'content'     => '<p>OPD menyelenggarakan workshop untuk meningkatkan literasi digital masyarakat.</p>',
                    'thumbnail'   => null,
                    'publishedAt' => Time::now('UTC')->subDays(5)->toDateTimeString(),
                    'createdAt'   => Time::now('UTC')->subDays(5)->toDateTimeString(),
                    'categories'  => ['program-kegiatan'],
                    'tags'        => ['digitalisasi', 'pelayanan-prima'],
                ],
            ];

            foreach ($newsSeed as $item) {
                $primarySlug = $item['categories'][0] ?? null;
                $primaryId   = $primarySlug && isset($categoryIdMap[$primarySlug]) ? (int) $categoryIdMap[$primarySlug] : null;

                $newsTable->insert([
                    'title'               => $item['title'],
                    'slug'                => $item['slug'],
                    'content'             => $item['content'],
                    'thumbnail'           => $item['thumbnail'],
                    'published_at'        => $item['publishedAt'],
                    'author_id'           => $adminId ?: null,
                    'created_at'          => $item['createdAt'],
                    'primary_category_id' => $primaryId,
                ]);

                $newsId = (int) $this->db->insertID();

                if ($newsId && $categoryMapTable && $categoryIdMap) {
                    $batch = [];
                    foreach ($item['categories'] as $slug) {
                        if (! isset($categoryIdMap[$slug])) {
                            continue;
                        }
                        $batch[] = [
                            'news_id'     => $newsId,
                            'category_id' => (int) $categoryIdMap[$slug],
                        ];
                    }

                    if ($batch !== []) {
                        $categoryMapTable->insertBatch($batch);
                    }
                }

                if ($newsId && $tagMapTable && $tagIdMap) {
                    $batch = [];
                    foreach ($item['tags'] as $slug) {
                        if (! isset($tagIdMap[$slug])) {
                            continue;
                        }
                        $batch[] = [
                            'news_id' => $newsId,
                            'tag_id'  => (int) $tagIdMap[$slug],
                        ];
                    }

                    if ($batch !== []) {
                        $tagMapTable->insertBatch($batch);
                    }
                }
            }
        }

        // Seed gallery samples
        if (! $this->db->table('galleries')->countAllResults()) {
            $this->db->table('galleries')->insertBatch([
                [
                    'title'       => 'Kegiatan Sosialisasi',
                    'description' => 'Dokumentasi kegiatan sosialisasi layanan publik.',
                    'image_path'  => 'uploads/galleries/sample-1.jpg',
                    'created_at'  => Time::now('UTC')->subDays(3)->toDateTimeString(),
                ],
                [
                    'title'       => 'Monitoring Infrastruktur',
                    'description' => 'Monitoring rutin jaringan dan perangkat IT.',
                    'image_path'  => 'uploads/galleries/sample-2.jpg',
                    'created_at'  => Time::now('UTC')->subDays(7)->toDateTimeString(),
                ],
            ]);
        }

        // Seed documents samples
        if (! $this->db->table('documents')->countAllResults()) {
            $this->db->table('documents')->insertBatch([
                [
                    'title'     => 'Laporan Kinerja 2024',
                    'category'  => 'Laporan',
                    'year'      => '2024',
                    'file_path' => 'uploads/documents/laporan-kinerja-2024.pdf',
                    'created_at'=> Time::now('UTC')->subDays(10)->toDateTimeString(),
                ],
                [
                    'title'     => 'SOP Pelayanan Informasi',
                    'category'  => 'SOP',
                    'year'      => '2023',
                    'file_path' => 'uploads/documents/sop-pelayanan-informasi.pdf',
                    'created_at'=> Time::now('UTC')->subDays(20)->toDateTimeString(),
                ],
            ]);
        }

        // Seed contact messages sample
        if (! $this->db->table('contact_messages')->countAllResults()) {
            $this->db->table('contact_messages')->insert([
                'name'        => 'Budi Santoso',
                'email'       => 'budi@example.com',
                'phone'       => '+62 811-2233-4455',
                'subject'     => 'Permintaan Informasi Publik',
                'message'     => 'Mohon informasi mengenai jadwal layanan keliling.',
                'ip_address'  => '127.0.0.1',
                'user_agent'  => 'Seeder/1.0',
                'status'      => 'new',
                'created_at'  => Time::now('UTC')->toDateTimeString(),
            ]);
        }

        // Optional initial activity log
        if ($adminId) {
            $logBuilder = $this->db->table('activity_logs');
            if (! $logBuilder->where('action', 'system.seed')->get()->getRow()) {
                $logBuilder->insert([
                    'user_id'    => $adminId,
                    'action'     => 'system.seed',
                    'description'=> 'Initial database seeding executed.',
                    'created_at' => Time::now('UTC')->toDateTimeString(),
                ]);
            }
        }
    }
}
