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

        // Seed news samples
        if (! $this->db->table('news')->countAllResults()) {
            $this->db->table('news')->insertBatch([
                [
                    'title'        => 'Peluncuran Portal OPD Baru',
                    'slug'         => 'peluncuran-portal-opd-baru',
                    'content'      => '<p>Portal resmi OPD kini hadir dengan tampilan dan fitur terbaru.</p>',
                    'thumbnail'    => null,
                    'published_at' => Time::now('UTC')->subDays(2)->toDateTimeString(),
                    'author_id'    => $adminId ?: null,
                    'created_at'   => Time::now('UTC')->subDays(2)->toDateTimeString(),
                ],
                [
                    'title'        => 'Workshop Literasi Digital',
                    'slug'         => 'workshop-literasi-digital',
                    'content'      => '<p>OPD menyelenggarakan workshop untuk meningkatkan literasi digital masyarakat.</p>',
                    'thumbnail'    => null,
                    'published_at' => Time::now('UTC')->subDays(5)->toDateTimeString(),
                    'author_id'    => $adminId ?: null,
                    'created_at'   => Time::now('UTC')->subDays(5)->toDateTimeString(),
                ],
            ]);
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
                'subject'     => 'Permintaan Informasi Publik',
                'message'     => 'Mohon informasi mengenai jadwal layanan keliling.',
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
