<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="hero-section" id="beranda">
  <div class="container public-container">
    <div class="row align-items-center gy-5">
      <div class="col-lg-6">
        <span class="hero-badge text-uppercase">Pelayanan Prima - Transparan - Responsif</span>
        <h1 class="display-5 fw-bold mt-3 mb-3">Profil Resmi Organisasi Perangkat Daerah Kota Harmoni</h1>
        <p class="lead text-muted mb-4">Menyediakan informasi terkini mengenai program kerja, layanan publik, data dokumen penting, serta berita terbaru dari Dinas Pelayanan Publik Kota Harmoni.</p>
        <div class="d-flex gap-3 flex-wrap hero-actions-stack">
          <a class="btn btn-public-primary btn-lg px-4" href="#layanan">Lihat Layanan Unggulan</a>
          <a class="btn btn-public-ghost btn-lg px-4" href="#kontak">Hubungi Kami</a>
        </div>
        <div class="row row-cols-1 row-cols-sm-3 g-3 mt-4">
          <div class="col"><div class="stat-card p-4 h-100"><p class="display-6 fw-bold mb-1">38</p><p class="mb-0 text-muted small">Layanan publik yang dapat diakses masyarakat.</p></div></div>
          <div class="col"><div class="stat-card p-4 h-100"><p class="display-6 fw-bold mb-1">12k+</p><p class="mb-0 text-muted small">Warga terlayani dengan standar kepuasan tinggi.</p></div></div>
          <div class="col"><div class="stat-card p-4 h-100"><p class="display-6 fw-bold mb-1">24/7</p><p class="mb-0 text-muted small">Pusat kontak dan kanal pengaduan responsif.</p></div></div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="ratio ratio-4x3 rounded-4 overflow-hidden shadow">
          <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=80" class="w-100 h-100 object-fit-cover" alt="Petugas layanan publik" loading="lazy" />
        </div>
      </div>
    </div>
  </div>
</section>

<section class="public-section" id="profil">
  <div class="container public-container">
    <div class="row gy-4 align-items-start">
      <div class="col-lg-7">
        <div class="public-section-header mb-3">
          <h2 class="fw-semibold mb-1">Profil Singkat OPD</h2>
          <span class="text-muted">Visi kami adalah mewujudkan pelayanan publik yang manusiawi, gesit, dan berbasis data.</span>
        </div>
        <div class="d-flex flex-column gap-4">
          <div class="d-flex gap-3"><div class="service-icon">VM</div><div><h5 class="fw-semibold mb-1">Visi &amp; Misi</h5><p class="text-muted mb-0">Meningkatkan kualitas pelayanan dengan pusat layanan terpadu, pemanfaatan teknologi, dan budaya kerja profesional.</p></div></div>
          <div class="d-flex gap-3"><div class="service-icon">OR</div><div><h5 class="fw-semibold mb-1">Struktur Organisasi</h5><p class="text-muted mb-0">Dipimpin oleh Kepala Dinas dengan dukungan 4 bidang utama: Administrasi, Investasi, Pengaduan, dan Data &amp; Informasi.</p></div></div>
          <div class="d-flex gap-3"><div class="service-icon">WK</div><div><h5 class="fw-semibold mb-1">Jam Layanan</h5><p class="text-muted mb-0">Senin - Jumat 08.00 - 15.30 WIB. Layanan digital tersedia 24/7.</p></div></div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
          <h5 class="fw-semibold">Capaian Strategis</h5>
          <ul class="mt-3 mb-0 text-muted">
            <li class="mb-1">Indeks kepuasan masyarakat 92% (2025).</li>
            <li class="mb-1">100% layanan digital &amp; antrean online.</li>
            <li>Tersertifikasi ISO 9001 &amp; predikat WBK.</li>
          </ul>
        </div></div>
      </div>
    </div>
  </div>
</section>

<section class="public-section" id="layanan">
  <div class="container public-container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
      <div class="public-section-header"><h2 class="fw-semibold mb-1">Layanan Unggulan</h2><span class="text-muted">Detail syarat, biaya, dan estimasi waktu pada tiap kartu.</span></div>
      <a class="btn btn-public-ghost px-4 mt-2 mt-md-0" href="#">Seluruh Katalog</a>
    </div>
    <div class="row row-cols-1 row-cols-lg-3 g-4">
      <?php foreach ([
        ['icon' => '01', 'title' => 'Perizinan Usaha Mikro', 'desc' => 'Pengurusan NIB dan perizinan operasional UMKM cepat dengan pendampingan gratis.', 'meta' => 'Biaya: Gratis - Waktu: 1-2 hari'],
        ['icon' => '02', 'title' => 'Administrasi Kependudukan', 'desc' => 'KTP-el, KK, akta sipil dengan antrean online dan pengantaran.', 'meta' => 'Biaya: Gratis - Waktu: 1 hari'],
        ['icon' => '03', 'title' => 'Pengaduan Terpadu', 'desc' => 'Aspirasi 24/7 dengan tindak lanjut lintas bidang dan pelacakan status.', 'meta' => 'Biaya: Gratis - Waktu: Real-time']
      ] as $s): ?>
      <div class="col"><div class="service-card p-4 h-100"><span class="service-icon"><?= esc($s['icon']) ?></span><h5 class="fw-semibold mt-3 mb-1"><?= esc($s['title']) ?></h5><p class="text-muted mb-2"><?= esc($s['desc']) ?></p><div class="text-muted small mb-2"><?= esc($s['meta']) ?></div><a class="stretched-link fw-semibold text-primary text-decoration-none" href="#">Selengkapnya</a></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="public-section" id="berita">
  <div class="container public-container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
      <div class="public-section-header"><h2 class="fw-semibold mb-1">Berita Terbaru</h2><span class="text-muted">Agenda, rilis program, dan kegiatan terkini.</span></div>
      <a class="btn btn-public-ghost px-4 mt-2 mt-md-0" href="#">Arsip Berita</a>
    </div>
    <div class="row row-cols-1 row-cols-lg-3 g-4">
      <?php foreach ([
        ['date' => '21 September 2025', 'cat' => 'Program', 'title' => 'Peluncuran Ruang Pelayanan Terpadu Modern', 'desc' => 'Pengalaman bebas antre dengan anjungan dan konsultasi personal.', 'img' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=700&q=80'],
        ['date' => '17 September 2025', 'cat' => 'Kolaborasi', 'title' => 'Kolaborasi Data Terbuka dengan Kominfo', 'desc' => 'Integrasi data layanan untuk transparansi publik.', 'img' => 'https://images.unsplash.com/photo-1462804993656-fac4ff489837?auto=format&fit=crop&w=700&q=80'],
        ['date' => '13 September 2025', 'cat' => 'Pelatihan', 'title' => 'Pelatihan Budaya Pelayanan Humanis', 'desc' => 'Workshop komunikasi publik dan literasi digital.', 'img' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=700&q=80']
      ] as $n): ?>
      <div class="col"><div class="news-card h-100 overflow-hidden shadow-sm rounded-4"><img src="<?= esc($n['img']) ?>" alt="<?= esc($n['title']) ?>" loading="lazy" class="w-100" /><div class="p-4"><div class="d-flex justify-content-between text-muted small mb-2"><span><?= esc($n['date']) ?></span><span><?= esc($n['cat']) ?></span></div><h5 class="fw-semibold mb-2"><?= esc($n['title']) ?></h5><p class="text-muted mb-3"><?= esc($n['desc']) ?></p><a class="fw-semibold text-decoration-none" href="#">Baca Selengkapnya</a></div></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="public-section" id="galeri">
  <div class="container public-container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
      <div class="public-section-header"><h2 class="fw-semibold mb-1">Galeri Aktivitas</h2><span class="text-muted">Dokumentasi kegiatan pelayanan dan inovasi mingguan.</span></div>
      <a class="btn btn-public-ghost px-4 mt-2 mt-md-0" href="#">Lihat Semua</a>
    </div>
    <div class="row row-cols-2 row-cols-lg-4 g-3">
      <?php foreach (['Klinik UMKM','Pelayanan Keliling','Ruang Konsultasi','Workshop SDM','Layanan Investasi','Pusat Informasi','Digital Corner','Gerai Terpadu'] as $g): ?>
      <div class="col"><div class="gallery-card rounded-4"><?= esc($g) ?></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="public-section bg-white" id="dokumen">
  <div class="container public-container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
      <div class="public-section-header"><h2 class="fw-semibold mb-1">Dokumen Publik</h2><span class="text-muted">Unduh regulasi, SOP, dan laporan capaian.</span></div>
      <a class="btn btn-public-ghost px-4 mt-2 mt-md-0" href="#">Semua Dokumen</a>
    </div>
    <div class="documents-wrap">
      <div class="table-responsive">
        <table class="table document-table align-middle mb-0">
          <thead><tr><th>Nama Dokumen</th><th>Kategori</th><th>Tahun</th><th>Unduh</th></tr></thead>
          <tbody>
            <tr><td>Standar Pelayanan Dinas Pelayanan Publik</td><td><span class="badge text-bg-primary">SOP</span></td><td>2025</td><td><a href="#">PDF - 1.2MB</a></td></tr>
            <tr><td>Laporan Kinerja Triwulan II</td><td><span class="badge text-bg-info text-white">Laporan</span></td><td>2025</td><td><a href="#">PDF - 3.4MB</a></td></tr>
            <tr><td>Peraturan Wali Kota tentang Layanan Terpadu</td><td><span class="badge text-bg-warning text-dark">Regulasi</span></td><td>2024</td><td><a href="#">PDF - 950KB</a></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<section class="public-section contact-section" id="kontak">
  <div class="container public-container">
    <div class="public-section-header mb-3"><h2 class="fw-semibold mb-1">Hubungi &amp; Sampaikan Aspirasi</h2><span class="text-muted">Gunakan formulir berikut atau kunjungi gerai pelayanan kami.</span></div>
    <div class="row g-4">
      <div class="col-lg-6"><div class="contact-card p-4 h-100"><form class="row g-3"><div class="col-12"><label class="form-label" for="nama">Nama Lengkap</label><input id="nama" class="form-control" type="text" placeholder="Tuliskan nama Anda" /></div><div class="col-12"><label class="form-label" for="email">Email</label><input id="email" class="form-control" type="email" placeholder="nama@contoh.com" /></div><div class="col-12"><label class="form-label" for="layanan">Kategori Layanan</label><select id="layanan" class="form-select"><option>Perizinan Usaha</option><option>Administrasi Kependudukan</option><option>Informasi Publik</option><option>Pengaduan</option></select></div><div class="col-12"><label class="form-label" for="pesan">Pesan / Aduan</label><textarea id="pesan" class="form-control" rows="5" placeholder="Jelaskan kebutuhan atau aduan Anda"></textarea></div><div class="col-12"><button class="btn btn-public-primary px-4" type="button">Kirim Pesan</button></div></form></div></div>
      <div class="col-lg-6"><div class="contact-card p-4 h-100"><div class="d-flex flex-column gap-4"><?php foreach ([['code'=>'AL','title'=>'Alamat Kantor','body'=>'Jl. Melati No. 123, Kecamatan Harmoni, Kota Harmoni 54321.'],['code'=>'TL','title'=>'Kontak','body'=>'Call Center: (021) 555-0123<br />WhatsApp: 0811-123-4567<br />Email: layanan@harmoni.go.id'],['code'=>'SM','title'=>'Jam Layanan','body'=>'Senin - Jumat 08.00 - 15.30 WIB<br />Sabtu 08.00 - 12.00 WIB (khusus layanan prioritas).'],['code'=>'IG','title'=>'Media Sosial','body'=>'@harmoni.pelayanan (Instagram)<br />Dinas Pelayanan Publik Kota Harmoni (Facebook)<br />YouTube: OPD Harmoni TV']] as $info): ?><div class="d-flex gap-3"><div class="service-icon"><?= esc($info['code']) ?></div><div><h5 class="fw-semibold mb-1"><?= esc($info['title']) ?></h5><p class="text-muted mb-0"><?= $info['body'] ?></p></div></div><?php endforeach; ?></div></div></div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
