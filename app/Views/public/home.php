<?php use CodeIgniter\I18n\Time; ?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
  $profile          = $profile ?? [];
  $profileName      = trim((string) ($profile['name'] ?? 'Dinas Pelayanan Publik Kota Harmoni'));
  $defaultIntro     = 'Menyediakan informasi terkini mengenai program kerja, layanan publik, data dokumen penting, serta berita terbaru dari Dinas Pelayanan Publik Kota Harmoni.';
  $profileIntro     = trim((string) ($profile['description'] ?? ''));
  $heroIntro        = $profileIntro !== '' ? $profileIntro : $defaultIntro;
  $vision           = trim((string) ($profile['vision'] ?? ''));
  $missionRaw       = trim((string) ($profile['mission'] ?? ''));
  $missionList      = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $missionRaw)));
  $address          = trim((string) ($profile['address'] ?? ''));
  $phone            = trim((string) ($profile['phone'] ?? ''));
  $email            = trim((string) ($profile['email'] ?? ''));
  $highlights       = [
      [
          'code'  => 'VS',
          'title' => 'Visi',
          'text'  => $vision !== '' ? mb_strimwidth(strip_tags($vision), 0, 140, '...') : 'Visi belum diperbarui.',
      ],
      [
          'code'  => 'MS',
          'title' => 'Misi',
          'text'  => $missionList ? 'Memiliki ' . count($missionList) . ' butir misi utama.' : 'Misi belum diperbarui.',
      ],
      [
          'code'  => 'CT',
          'title' => 'Kontak',
          'text'  => ($address !== '' || $phone !== '' || $email !== '')
              ? mb_strimwidth(trim($address . ($phone !== '' ? ' - ' . $phone : '') . ($email !== '' ? ' - ' . $email : '')), 0, 150, '...')
              : 'Informasi kontak belum tersedia.',
      ],
  ];
?>
<section class="hero-section" id="beranda">
  <div class="container public-container">
    <div class="row align-items-center gy-5">
      <div class="col-lg-6">
        <span class="hero-badge text-uppercase">Pelayanan Prima - Transparan - Responsif</span>
        <h1 class="display-5 fw-bold mt-3 mb-3"><?= esc($profileName) ?></h1>
        <p class="lead text-muted mb-4"><?= esc($heroIntro) ?></p>
        <div class="d-flex gap-3 flex-wrap hero-actions-stack">
          <a class="btn btn-public-primary btn-lg px-4" href="<?= site_url('layanan') ?>">Lihat Layanan Unggulan</a>
          <a class="btn btn-public-ghost btn-lg px-4" href="<?= site_url('/') ?>#kontak">Hubungi Kami</a>
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
          <span class="text-muted"><?= esc($profileIntro !== '' ? $profileIntro : 'Profil OPD akan terus diperbarui untuk menghadirkan informasi terbaru mengenai layanan kami.') ?></span>
        </div>
        <div class="d-flex flex-column gap-4">
          <?php foreach ($highlights as $item): ?>
            <div class="d-flex gap-3">
              <div class="service-icon"><?= esc($item['code']) ?></div>
              <div>
                <h5 class="fw-semibold mb-1"><?= esc($item['title']) ?></h5>
                <p class="text-muted mb-0"><?= esc($item['text']) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
          <div class="card-body p-4">
            <h5 class="fw-semibold">Visi</h5>
            <p class="text-muted mb-0"><?= $vision !== '' ? nl2br(esc($vision)) : 'Visi organisasi akan diperbarui segera.' ?></p>
          </div>
        </div>
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-4">
            <h5 class="fw-semibold">Misi</h5>
            <?php if ($missionList): ?>
              <ul class="mt-3 mb-0 text-muted">
                <?php foreach ($missionList as $mission): ?>
                  <li class="mb-2"><?= esc($mission) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="text-muted mb-0">Misi organisasi akan diperbarui segera.</p>
            <?php endif; ?>
            <a class="btn btn-link px-0 mt-3" href="<?= site_url('profil') ?>">Selengkapnya tentang profil &rarr;</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="public-section" id="layanan">
  <div class="container public-container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
      <div class="public-section-header"><h2 class="fw-semibold mb-1">Layanan Unggulan</h2><span class="text-muted">Detail syarat, biaya, dan estimasi waktu pada tiap kartu.</span></div>
      <a class="btn btn-public-ghost px-4 mt-2 mt-md-0" href="<?= site_url('layanan') ?>">Seluruh Katalog</a>
    </div>
    <?php if ($services): ?>
      <div class="row row-cols-1 row-cols-lg-3 g-4">
        <?php foreach ($services as $index => $service): ?>
          <div class="col">
            <article class="service-card p-4 h-100">
              <span class="service-icon"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
              <h5 class="fw-semibold mt-3 mb-1"><a class="text-decoration-none text-dark" href="<?= site_url('layanan#' . esc($service['slug'] ?? '', 'url')) ?>"><?= esc($service['title']) ?></a></h5>
              <?php if (! empty($service['description'])): ?>
                <p class="text-muted mb-3"><?= esc($service['description']) ?></p>
              <?php endif; ?>
              <dl class="text-muted small mb-0">
                <?php if (! empty($service['requirements'])): ?>
                  <dt class="text-uppercase text-dark">Persyaratan</dt>
                  <dd><?= nl2br(esc($service['requirements'])) ?></dd>
                <?php endif; ?>
                <?php if (! empty($service['fees'])): ?>
                  <dt class="text-uppercase text-dark">Biaya</dt>
                  <dd><?= esc($service['fees']) ?></dd>
                <?php endif; ?>
                <?php if (! empty($service['processing_time'])): ?>
                  <dt class="text-uppercase text-dark">Waktu Proses</dt>
                  <dd><?= esc($service['processing_time']) ?></dd>
                <?php endif; ?>
              </dl>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <p class="text-muted mb-0">Data layanan sedang disiapkan. Silakan cek kembali beberapa saat lagi.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="public-section bg-white" id="berita">
  <div class="container public-container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
      <div class="public-section-header"><h2 class="fw-semibold mb-1">Berita Terbaru</h2><span class="text-muted">Informasi aktual mengenai kegiatan dan kebijakan terbaru.</span></div>
      <a class="btn btn-public-ghost px-4 mt-2 mt-md-0" href="<?= site_url('berita') ?>">Arsip Berita</a>
    </div>
    <?php if ($news): ?>
      <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($news as $article): ?>
          <div class="col">
            <article class="news-card h-100 overflow-hidden shadow-sm rounded-4 bg-white">
              <?php if (! empty($article['thumbnail'])): ?>
                <img src="<?= esc(base_url($article['thumbnail'])) ?>" alt="<?= esc($article['title']) ?>" loading="lazy" class="w-100" />
              <?php endif; ?>
              <div class="p-4">
                <?php if (! empty($article['published_at'])): ?>
                  <?php $published = Time::parse($article['published_at']); ?>
                  <span class="badge bg-light text-primary mb-2"><?= esc($published->toLocalizedString('d MMM yyyy')) ?></span>
                <?php endif; ?>
                <h5 class="fw-semibold mb-2"><a class="text-decoration-none text-dark" href="<?= site_url('berita/' . esc($article['slug'], 'url')) ?>"><?= esc($article['title']) ?></a></h5>
                <?php if (! empty($article['content'])): ?>
                  <?php $excerpt = mb_strimwidth(strip_tags($article['content']), 0, 140, '...'); ?>
                  <p class="text-muted mb-3"><?= esc($excerpt) ?></p>
                <?php endif; ?>
                <a class="btn btn-public-ghost btn-sm" href="<?= site_url('berita/' . esc($article['slug'], 'url')) ?>">Baca Selengkapnya</a>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <p class="text-muted mb-0">Belum ada berita yang dipublikasikan.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="public-section" id="galeri">
  <div class="container public-container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
      <div class="public-section-header"><h2 class="fw-semibold mb-1">Galeri Kegiatan</h2><span class="text-muted">Potret pelayanan dan aktivitas kami untuk masyarakat.</span></div>
      <a class="btn btn-public-ghost px-4 mt-2 mt-md-0" href="<?= site_url('galeri') ?>">Lihat Semua</a>
    </div>
    <?php if ($galleries): ?>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php foreach ($galleries as $gallery): ?>
          <div class="col">
            <article class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
              <img src="<?= esc(base_url($gallery['image_path'])) ?>" alt="<?= esc($gallery['title']) ?>" class="w-100" loading="lazy">
              <div class="card-body">
                <h5 class="fw-semibold text-dark mb-2"><?= esc($gallery['title']) ?></h5>
                <?php if (! empty($gallery['description'])): ?>
                  <p class="mb-0 text-muted"><?= esc($gallery['description']) ?></p>
                <?php endif; ?>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <p class="text-muted mb-0">Galeri akan diunggah setelah dokumentasi tersedia.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="public-section bg-white" id="dokumen">
  <div class="container public-container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
      <div class="public-section-header"><h2 class="fw-semibold mb-1">Dokumen Publik</h2><span class="text-muted">Unduh SOP, laporan kinerja, dan regulasi terbaru.</span></div>
      <a class="btn btn-public-ghost px-4 mt-2 mt-md-0" href="<?= site_url('dokumen') ?>">Semua Dokumen</a>
    </div>
    <div class="documents-wrap">
      <?php if ($documents): ?>
        <div class="table-responsive">
          <table class="table align-middle document-table mb-0">
            <thead>
              <tr>
                <th scope="col">Judul</th>
                <th scope="col">Kategori</th>
                <th scope="col">Tahun</th>
                <th scope="col" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documents as $document): ?>
                <tr>
                  <td><?= esc($document['title']) ?></td>
                  <td><?= esc($document['category'] ?? '-') ?></td>
                  <td><?= esc($document['year'] ?? '-') ?></td>
                  <td class="text-center">
                    <?php if (! empty($document['file_path'])): ?>
                      <a class="btn btn-sm btn-public-primary" target="_blank" rel="noopener" href="<?= esc(base_url($document['file_path'])) ?>">Unduh</a>
                    <?php else: ?>
                      <span class="text-muted">Tidak tersedia</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-5">
          <p class="text-muted mb-0">Dokumen publik akan tersedia setelah proses unggah selesai.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="public-section contact-section" id="kontak">
  <div class="container public-container">
    <div class="public-section-header mb-3"><h2 class="fw-semibold mb-1">Hubungi &amp; Sampaikan Aspirasi</h2><span class="text-muted">Gunakan formulir berikut atau kunjungi gerai pelayanan kami.</span></div>
    <div class="row g-4">
      <div class="col-lg-6"><div class="contact-card p-4 h-100"><form class="row g-3"><div class="col-12"><label class="form-label" for="nama">Nama Lengkap</label><input id="nama" class="form-control" type="text" placeholder="Tuliskan nama Anda" /></div><div class="col-12"><label class="form-label" for="email">Email</label><input id="email" class="form-control" type="email" placeholder="nama@contoh.com" /></div><div class="col-12"><label class="form-label" for="layanan">Kategori Layanan</label><select id="layanan" class="form-select"><option>Perizinan Usaha</option><option>Administrasi Kependudukan</option><option>Informasi Publik</option><option>Pengaduan</option></select></div><div class="col-12"><label class="form-label" for="pesan">Pesan / Aduan</label><textarea id="pesan" class="form-control" rows="5" placeholder="Jelaskan kebutuhan atau aduan Anda"></textarea></div><div class="col-12"><button class="btn btn-public-primary px-4" type="button">Kirim Pesan</button></div></form></div></div>
      <div class="col-lg-6">
        <div class="contact-card p-4 h-100">
          <?php
            $contactBlocks = [];
            if ($address !== '') {
                $contactBlocks[] = [
                    'code'  => 'AL',
                    'title' => 'Alamat Kantor',
                    'body'  => nl2br(esc($address)),
                ];
            }
            if ($phone !== '' || $email !== '') {
                $contactDetails = [];
                if ($phone !== '') {
                    $contactDetails[] = esc($phone);
                }
                if ($email !== '') {
                    $contactDetails[] = '<a class="text-decoration-none" href="mailto:' . esc($email) . '">' . esc($email) . '</a>';
                }
                $contactBlocks[] = [
                    'code'  => 'TL',
                    'title' => 'Kontak',
                    'body'  => implode('<br />', $contactDetails),
                ];
            }
            if (! $contactBlocks) {
                $contactBlocks[] = [
                    'code'  => 'SM',
                    'title' => 'Informasi',
                    'body'  => 'Data kontak belum tersedia. Silakan hubungi kami melalui kunjungan langsung.',
                ];
            }
          ?>
          <div class="d-flex flex-column gap-4">
            <?php foreach ($contactBlocks as $block): ?>
              <div class="d-flex gap-3">
                <div class="service-icon"><?= esc($block['code']) ?></div>
                <div>
                  <h5 class="fw-semibold mb-1"><?= esc($block['title']) ?></h5>
                  <p class="text-muted mb-0"><?= $block['body'] ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
