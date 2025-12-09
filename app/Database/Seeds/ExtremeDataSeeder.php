<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class ExtremeDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create('id_ID');
        $db = \Config\Database::connect();
        
        // --- Konfigurasi Gambar Dummy ---
        $dummyImage = 'uploads/galleries/1758299996_39b62f1c4f747dd0f477.png';

        // --- Data Kontekstual ---
        $newsTitles = [
            'Rapat Koordinasi Pembangunan Daerah', 'Sosialisasi Pencegahan Stunting', 
            'Kunjungan Kerja Bupati ke Kecamatan', 'Upacara Peringatan Hari Pahlawan', 
            'Penyerahan Bantuan Sosial Tunai', 'Pelatihan UMKM Digital',
            'Peresmian Gedung Serbaguna Baru', 'Kampanye Imunisasi Nasional',
            'Sidak Pasar Jelang Lebaran', 'Evaluasi Kinerja Pegawai Triwulan I',
            'Lomba Desa Wisata Tingkat Kabupaten', 'Penyuluhan Hukum Terpadu',
            'Gotong Royong Membersihkan Sungai', 'Festival Budaya Daerah',
            'Pelantikan Pejabat Eselon III', 'Rapat Paripurna DPRD',
            'Safari Ramadhan Pemerintah Daerah', 'Bazar Murah Kebutuhan Pokok',
            'Workshop Peningkatan Kapasitas Guru', 'Seminar Smart City'
        ];

        $serviceTitles = [
            'Layanan Administrasi Kependudukan', 'Perizinan Bangunan (IMB/PBG)',
            'Pendaftaran Usaha Mikro', 'Layanan Pengaduan Masyarakat',
            'Layanan Kesehatan Puskesmas', 'Pembuatan Kartu Kuning (AK-1)',
            'Layanan Perpustakaan Digital', 'Fasilitasi Bantuan Pendidikan',
            'Layanan Informasi Publik (PPID)', 'Perizinan Lingkungan Hidup'
        ];

        $galleryTitles = [
            'Kegiatan Jumat Bersih', 'Upacara 17 Agustus', 'Kunjungan Gubernur',
            'Festival Kuliner Lokal', 'Rapat Koordinasi Bulanan', 'Pelatihan Staff',
            'Peresmian Jembatan Desa', 'Penyaluran BLT', 'Musrenbang Kecamatan',
            'Peringatan Maulid Nabi'
        ];

        $contactSubjects = [
            'Laporan Jalan Rusak', 'Pertanyaan Syarat KTP', 'Pengaduan Layanan RSUD',
            'Permohonan Data Stistik', 'Saran Pengembangan Wisata', 'Laporan Lampu Jalan Mati',
            'Pertanyaan Beasiswa Daerah', 'Undangan Sosialisasi', 'Apresiasi Kinerja Petugas',
            'Masalah Sampah Menumpuk'
        ];

        // TRUNCATE TABLES (Hati-hati, ini menghapus data!)
        $this->db->disableForeignKeyChecks();
        $db->table('news')->truncate();
        $db->table('services')->truncate();
        $db->table('galleries')->truncate();
        // ... Truncate code above ...
        $db->table('news_categories')->truncate(); // Truncate categories too
        $this->db->enableForeignKeyChecks();

        // 0. CREATE CATEGORIES
        echo "Seeding News Categories...\n";
        $catNames = ['Pemerintahan', 'Pembangunan', 'Kesehatan', 'Pendidikan', 'Teknologi', 'Sosial', 'Ekonomi'];
        $catIds = [];
        
        foreach ($catNames as $catName) {
            $catSlug = url_title($catName, '-', true);
            $catData = [
                'name' => $catName,
                'slug' => $catSlug,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // Insert and get ID
            $db->table('news_categories')->insert($catData);
            $catIds[] = $db->insertID();
        }

        // 1. BERITA (100 Data)
        echo "Seeding 100 News items...\n";
        $newsData = [];
        
        for ($i = 0; $i < 100; $i++) {
            // Kombinasi judul kontekstual + kata tambahan biar variatif
            $baseTitle = $faker->randomElement($newsTitles);
            $suffix = $faker->randomElement(['2024', 'di Daerah', 'Sukses', 'Lancar', 'Antusias']);
            $title = "$baseTitle $suffix " . $faker->numerify('##');
            
            $slug = url_title($title, '-', true) . '-' . uniqid();
            $publishedAt = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s');
            $randCatId = $faker->randomElement($catIds);

            // Konten yang lebih real
            $content = "
                <p><strong>" . strtoupper($faker->city) . "</strong> - $title yang dilaksanakan pada " . date('d F Y', strtotime($publishedAt)) . " berlangsung dengan khidmat dan lancar. Kegiatan ini dihadiri oleh berbagai elemen masyarakat dan pejabat terkait.</p>
                <p>Kepala Dinas dalam sambutannya menyampaikan pentingnya kegiatan ini untuk kemajuan daerah. \"Kami berharap program ini dapat memberikan dampak positif langsung kepada masyarakat,\" ujarnya di sela-sela acara.</p>
                <h3>Tujuan Kegiatan</h3>
                <p>Adapun tujuan utama dari kegiatan ini adalah:</p>
                <ul>
                    <li>Meningkatkan sinergi antar instansi pemerintah.</li>
                    <li>Memberikan pelayanan yang lebih baik kepada publik.</li>
                    <li>Mendengarkan aspirasi langsung dari warga " . $faker->streetName . ".</li>
                </ul>
                <p>Acara ditutup dengan doa bersama dan ramah tamah. Diharapkan kegiatan serupa dapat rutin dilaksanakan setiap tahunnya.</p>
            ";
            
            $newsData[] = [
                'title'               => $title,
                'slug'                => $slug, 
                'content'             => $content,
                'excerpt'             => "Liputan kegiatan $title yang berlangsung di " . $faker->city . ". Simak selengkapnya.",
                // 'status'       => 'published', // REMOVED: No such column
                'author_id'           => 1,
                'primary_category_id' => $randCatId, // FIXED: Use ID
                'thumbnail'           => $dummyImage,
                'published_at'        => $publishedAt,
                // 'created_at' and 'updated_at' removed, model uses useTimestamps = false or auto? 
                // Model says useTimestamps=false but let's check migration. Assuming safely ignore or manual
                // Best practice manual for seeder:
                // 'created_at'   => $publishedAt, 
                // 'updated_at'   => $publishedAt,
                'is_featured'         => ($i < 5) ? 1 : 0,
            ];
            
            try {
                $db->table('news')->insert($newsData[$i]);
            } catch (\Exception $e) {
                echo "Error inserting news $i: " . $e->getMessage() . "\n";
            }
        }
        
        // $newsChunks = array_chunk($newsData, 50);
        // foreach ($newsChunks as $chunk) {
        //     $db->table('news')->insertBatch($chunk);
        // }

        // 2. LAYANAN (10 Data)
        echo "Seeding 10 Services...\n";
        $servicesData = [];
        
        foreach ($serviceTitles as $index => $srvTitle) {
            $slug = url_title($srvTitle, '-', true);
            $icon = $dummyImage;
            
            $servicesData[] = [
                'title'       => $srvTitle,
                'slug'        => $slug,
                'description' => "Informasi lengkap mengenai prosedur dan persyaratan $srvTitle.",
                'content'     => "
                    <h3>Deskripsi Layanan</h3>
                    <p>Layanan ini disediakan untuk memfasilitasi kebutuhan masyarakat terkait $srvTitle secara efisien dan transparan.</p>
                    <h3>Persyaratan</h3>
                    <ul>
                        <li>KTP Asli dan Fotokopi</li>
                        <li>Kartu Keluarga (KK)</li>
                        <li>Formulir Permohonan (tersedia di loket)</li>
                        <li>Surat Pengantar dari RT/RW (jika diperlukan)</li>
                    </ul>
                    <h3>Prosedur</h3>
                    <ol>
                        <li>Pemohon datang ke loket atau mendaftar online.</li>
                        <li>Menyerahkan berkas persyaratan lengkap.</li>
                        <li>Petugas memverifikasi data.</li>
                        <li>Proses penerbitan dokumen (estimasi 1-3 hari kerja).</li>
                    </ol>
                    <h3>Biaya</h3>
                    <p>Layanan ini <strong>GRATIS</strong> tidak dipungut biaya.</p>
                ",
                'thumbnail'   => $dummyImage,
                'icon'        => $icon, // Add Icon
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
        }
        // $db->table('services')->ignore(true)->insertBatch($servicesData);
        foreach ($servicesData as $srv) {
            try { $db->table('services')->insert($srv); } catch (\Exception $e) {}
        }

        // 3. GALERI (20 Data)
        echo "Seeding 20 Gallery items...\n";
        $galleryData = [];
        for ($i = 0; $i < 20; $i++) {
            $galTitle = $faker->randomElement($galleryTitles) . ' ' . $faker->year;
            $data = [
                'title'       => $galTitle,
                'description' => "Dokumentasi foto kegiatan $galTitle yang dihadiri oleh staf dan pimpinan.",
                'image_path'  => $dummyImage,
                'category'    => 'Kegiatan',
                'created_at'  => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s'),
            ];
            try { $db->table('galleries')->insert($data); } catch (\Exception $e) {}
        }

        // 4. PESAN KONTAK (10 Data)
        echo "Seeding 10 Contact Messages...\n";
        $contactData = [];
        $statuses = ['new', 'in_progress', 'closed'];
        for ($i = 0; $i < 10; $i++) {
            $subject = $faker->randomElement($contactSubjects);
            $data = [
                'name'       => $faker->name('male'), // Atau mix male/female
                'email'      => $faker->freeEmail,
                'phone'      => '08' . $faker->numerify('##########'),
                'subject'    => $subject,
                'message'    => "Saya ingin menanyakan/melaporkan mengenai $subject di daerah saya. Mohon tindak lanjutnya. Terima kasih.",
                'status'     => $faker->randomElement($statuses),
                'ip_address' => $faker->ipv4,
                'user_agent' => $faker->userAgent,
                'created_at' => $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
            ];
            try { $db->table('contact_messages')->insert($data); } catch (\Exception $e) {}
        }
        // $db->table('contact_messages')->insertBatch($contactData);

        // 5. UPDATE PROFIL (Struktur Organisasi)
        echo "Updating Profile Organization Structure...\n";
        $db->table('opd_profile')->update([
            'org_structure_image' => $dummyImage,
            'org_structure_alt_text' => 'Bagan Struktur Organisasi Dinas Kominfo Periode 2024-2029',
            'org_structure_updated_at' => date('Y-m-d H:i:s'),
        ]);

        echo "✅ Extreme Real Context Seeding Completed!\n";
    }
}
