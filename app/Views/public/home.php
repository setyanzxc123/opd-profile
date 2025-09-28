<?php use CodeIgniter\I18n\Time; ?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
  $profile        = $profile ?? [];
  $profileName    = trim((string) ($profile['name'] ?? 'Dinas Pelayanan Publik Kota Harmoni'));
  $defaultIntro   = 'Menyediakan informasi terkini mengenai program kerja, layanan publik, data dokumen penting, serta berita terbaru dari Dinas Pelayanan Publik Kota Harmoni.';
  $profileIntro   = trim((string) ($profile['description'] ?? ''));
  $heroIntro      = $profileIntro !== '' ? $profileIntro : $defaultIntro;
  $address        = trim((string) ($profile['address'] ?? ''));
  $phone          = trim((string) ($profile['phone'] ?? ''));
  $email          = trim((string) ($profile['email'] ?? ''));
  $newsItems      = $news ?? [];
  $latestNews     = $newsItems[0] ?? null;
  $secondaryNews  = $latestNews ? array_slice($newsItems, 1, 3) : array_slice($newsItems, 0, 3);
  $sliderItems    = $newsItems ? array_slice($newsItems, 0, 4) : [];
  $serviceItems   = $services ?? [];
  $galleryItems   = $galleries ?? [];
?>
<section class="hero-section hero-shell hero-soft" id="beranda">
  <div class="container public-container">
    <?php if ($sliderItems): ?>
      <div class="hero-slider" data-slider data-interval="6500">
        <div class="hero-slides">
          <?php foreach ($sliderItems as $index => $item): ?>
            <?php $excerpt = mb_strimwidth(strip_tags($item['content'] ?? ''), 0, 200, '...'); ?>
            <?php $thumbnail = ! empty($item['thumbnail']) ? esc(base_url($item['thumbnail'])) : ''; ?>
            <article class="hero-slide hero-slide-cover<?= $index === 0 ? ' is-active' : '' ?>">
              <figure class="hero-cover-media">
                <?php if ($thumbnail !== ''): ?>
                  <img src="<?= $thumbnail ?>" alt="<?= esc($item['title']) ?>" loading="lazy">
                <?php else: ?>
                  <div class="hero-placeholder">Thumbnail belum tersedia</div>
                <?php endif; ?>
              </figure>
              <div class="hero-cover-overlay">
                <div class="hero-cover-copy">
                  <span class="hero-eyebrow hero-eyebrow-light">Berita Terbaru</span>
                  <h1 class="hero-cover-title"><?= esc($item['title']) ?></h1>
                  <p class="hero-cover-lead"><?= esc($excerpt) ?></p>
                  <div class="hero-cover-actions">
                    <a class="btn btn-public-primary" href="<?= site_url('berita/' . esc($item['slug'], 'url')) ?>">Baca selengkapnya</a>
                    <a class="hero-link hero-link-light" href="<?= site_url('berita') ?>">Lihat semua</a>
                  </div>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
        <?php if (count($sliderItems) > 1): ?>
          <div class="hero-slider-controls" aria-hidden="true">
            <button class="hero-slide-btn prev" type="button" aria-label="Berita sebelumnya">&#8592;</button>
            <button class="hero-slide-btn next" type="button" aria-label="Berita selanjutnya">&#8594;</button>
          </div>
          <div class="hero-slider-dots" role="tablist">
            <?php foreach ($sliderItems as $index => $item): ?>
              <button type="button" class="hero-slide-dot<?= $index === 0 ? ' is-active' : '' ?>" aria-label="Slide berita <?= $index + 1 ?>" data-slide="<?= $index ?>"></button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="hero-fallback-wrap">
        <div class="hero-grid">
          <div class="hero-copy">
            <span class="hero-eyebrow">Selamat datang</span>
            <h1 class="hero-title"><?= esc($profileName) ?></h1>
            <p class="hero-lead"><?= esc($heroIntro) ?></p>
            <div class="hero-actions">
              <a class="btn btn-public-primary" href="<?= site_url('layanan') ?>">Eksplor layanan</a>
              <a class="hero-link" href="<?= site_url('kontak') ?>">Hubungi kami</a>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="public-section section-warm" id="layanan">
  <div class="container public-container">
    <div class="section-head">
      <h2>Layanan Unggulan</h2>
      <p>Akses singkat menuju layanan prioritas yang paling sering digunakan masyarakat.</p>
    </div>
    <?php if ($serviceItems): ?>
      <div class="minimal-grid minimal-grid-4">
        <?php foreach ($serviceItems as $service): ?>
          <?php $initial = mb_strtoupper(mb_substr($service['title'] ?? '', 0, 1, 'UTF-8'), 'UTF-8'); ?>
          <article class="surface-card service-minimal">
            <span class="mono-badge"><?= esc($initial !== '' ? $initial : 'L') ?></span>
            <h3><a class="surface-link" href="<?= site_url('layanan') ?>#<?= esc($service['slug'] ?? '', 'url') ?>"><?= esc($service['title'] ?? 'Layanan Publik') ?></a></h3>
            <?php if (! empty($service['description'])): ?>
              <?php $summary = mb_strimwidth(strip_tags($service['description']), 0, 120, '...'); ?>
              <p class="text-muted mb-0"><?= esc($summary) ?></p>
            <?php else: ?>
              <p class="text-muted mb-0">Detail layanan segera tersedia.</p>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <p>Data layanan sedang disiapkan. Silakan cek kembali beberapa saat lagi.</p>
      </div>
    <?php endif; ?>
    <div class="section-cta">
      <a class="hero-link" href="<?= site_url('layanan') ?>">Lihat seluruh layanan</a>
    </div>
  </div>
</section>

<section class="public-section section-cool" id="berita">
  <div class="container public-container">
    <div class="section-head">
      <h2>Berita</h2>
      <p>Ikuti perkembangan kebijakan dan layanan terbaru kami.</p>
    </div>
    <?php if ($latestNews): ?>
      <div class="news-grid">
        <article class="surface-card news-feature">
          <?php if (! empty($latestNews['thumbnail'])): ?>
            <div class="news-feature-media">
              <img src="<?= esc(base_url($latestNews['thumbnail'])) ?>" alt="<?= esc($latestNews['title']) ?>" loading="lazy">
            </div>
          <?php endif; ?>
          <div class="news-feature-body">
            <?php if (! empty($latestNews['published_at'])): ?>
              <?php $published = Time::parse($latestNews['published_at']); ?>
              <span class="news-meta"><?= esc($published->toLocalizedString('d MMM yyyy')) ?></span>
            <?php endif; ?>
            <h3><a class="surface-link" href="<?= site_url('berita/' . esc($latestNews['slug'], 'url')) ?>"><?= esc($latestNews['title']) ?></a></h3>
            <?php if (! empty($latestNews['content'])): ?>
              <?php $lead = mb_strimwidth(strip_tags($latestNews['content']), 0, 200, '...'); ?>
              <p class="text-muted mb-4"><?= esc($lead) ?></p>
            <?php endif; ?>
            <a class="btn btn-public-primary" href="<?= site_url('berita/' . esc($latestNews['slug'], 'url')) ?>">Baca selengkapnya</a>
          </div>
        </article>
        <div class="news-list">
          <?php foreach ($secondaryNews as $article): ?>
            <article class="news-list-item">
              <div>
                <?php if (! empty($article['published_at'])): ?>
                  <?php $published = Time::parse($article['published_at']); ?>
                  <span class="news-meta"><?= esc($published->toLocalizedString('d MMM yyyy')) ?></span>
                <?php endif; ?>
                <h4><a class="surface-link" href="<?= site_url('berita/' . esc($article['slug'], 'url')) ?>"><?= esc($article['title']) ?></a></h4>
                <?php if (! empty($article['content'])): ?>
                  <?php $excerpt = mb_strimwidth(strip_tags($article['content']), 0, 130, '...'); ?>
                  <p class="text-muted mb-0 small"><?= esc($excerpt) ?></p>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
          <?php if (! $secondaryNews): ?>
            <div class="empty-state">
              <p>Belum ada berita tambahan.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <p>Belum ada berita yang dipublikasikan.</p>
      </div>
    <?php endif; ?>
    <div class="section-cta">
      <a class="hero-link" href="<?= site_url('berita') ?>">Buka arsip berita</a>
    </div>
  </div>
</section>

<section class="public-section section-neutral" id="galeri">
  <div class="container public-container">
    <div class="section-head">
      <h2>Galeri Kegiatan</h2>
      <p>Dokumentasi singkat dari pelayanan dan aktivitas lapangan kami.</p>
    </div>
    <?php if ($galleryItems): ?>
      <div class="minimal-grid minimal-grid-4 gallery-minimal">
        <?php foreach ($galleryItems as $gallery): ?>
          <article class="surface-card gallery-item">
            <div class="gallery-item-media">
              <img src="<?= esc(base_url($gallery['image_path'])) ?>" alt="<?= esc($gallery['title']) ?>" loading="lazy">
            </div>
            <div class="gallery-item-body">
              <h3><?= esc($gallery['title']) ?></h3>
              <?php if (! empty($gallery['description'])): ?>
                <?php $caption = mb_strimwidth(strip_tags($gallery['description']), 0, 100, '...'); ?>
                <p class="text-muted mb-0 small"><?= esc($caption) ?></p>
              <?php else: ?>
                <p class="text-muted mb-0 small">Keterangan akan ditambahkan.</p>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <p>Galeri akan diunggah setelah dokumentasi tersedia.</p>
      </div>
    <?php endif; ?>
    <div class="section-cta">
      <a class="hero-link" href="<?= site_url('galeri') ?>">Buka galeri lengkap</a>
    </div>
  </div>
</section>

<section class="public-section section-warm" id="dokumen">
  <div class="container public-container">
    <div class="section-head">
      <h2>Dokumen Publik</h2>
      <p>Unduh SOP, laporan kinerja, dan regulasi terbaru.</p>
    </div>
    <?php if ($documents): ?>
      <div class="surface-card documents-card">
        <div class="table-responsive">
          <table class="table table-sm align-middle document-table mb-0">
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
                      <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="<?= esc(base_url($document['file_path'])) ?>">Unduh</a>
                    <?php else: ?>
                      <span class="text-muted">Tidak tersedia</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <p>Dokumen publik akan tersedia setelah proses unggah selesai.</p>
      </div>
    <?php endif; ?>
    <div class="section-cta">
      <a class="hero-link" href="<?= site_url('dokumen') ?>">Lihat semua dokumen</a>
    </div>
  </div>
</section>

<section class="public-section contact-summary" id="kontak">
  <div class="container public-container">
    <div class="section-head">
      <h2>Hubungi Kami</h2>
      <p>Informasi kantor dan kanal komunikasi resmi.</p>
    </div>
    <div class="minimal-grid minimal-grid-2 contact-minimal">
      <article class="surface-card contact-panel">
        <h3>Alamat &amp; Jadwal</h3>
        <dl class="contact-info mb-0">
          <dt>Alamat Kantor</dt>
          <dd><?= $address !== '' ? nl2br(esc($address)) : 'Alamat belum tersedia.' ?></dd>
          <dt>Telepon</dt>
          <dd><?= $phone !== '' ? esc($phone) : 'Nomor telepon belum tersedia.' ?></dd>
          <dt>Email</dt>
          <dd>
            <?php if ($email !== ''): ?>
              <a class="surface-link" href="mailto:<?= esc($email) ?>"><?= esc($email) ?></a>
            <?php else: ?>
              <span class="text-muted">Email belum tersedia.</span>
            <?php endif; ?>
          </dd>
        </dl>
      </article>
      <article class="surface-card contact-panel">
        <h3>Kanal Cepat</h3>
        <ul class="list-unstyled contact-links mb-0">
          <?php
            $quickLinks = [];
            if ($phone !== '') {
                $quickLinks[] = [
                    'label' => 'Hubungi via Telepon',
                    'value' => $phone,
                    'href'  => 'tel:' . preg_replace('/[^0-9+]/', '', $phone),
                ];
            }
            if ($email !== '') {
                $quickLinks[] = [
                    'label' => 'Kirim Email',
                    'value' => $email,
                    'href'  => 'mailto:' . $email,
                ];
            }
            if (! $quickLinks) {
                $quickLinks[] = [
                    'label' => 'Layanan Pengaduan',
                    'value' => 'Segera hadir',
                    'href'  => '#',
                ];
            }
          ?>
          <?php foreach ($quickLinks as $link): ?>
            <?php $isExternal = $link['href'] !== '#'; ?>
            <li>
              <a class="surface-link" href="<?= esc($link['href']) ?>"<?php if ($isExternal): ?> target="_blank" rel="noopener"<?php endif; ?>>
                <span><?= esc($link['label']) ?></span>
                <span class="contact-link-value"><?= esc($link['value']) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <p class="text-muted small mt-4 mb-0">Kunjungi langsung pada jam kerja atau hubungi kami melalui kanal di atas untuk respons cepat.</p>
      </article>
    </div>
  </div>
</section>
<?= $this->endSection() ?>



<?= $this->section('pageScripts') ?>
<script>
(() => {
  const slider = document.querySelector('[data-slider]');
  if (!slider) {
    return;
  }

  const slides = Array.from(slider.querySelectorAll('.hero-slide'));
  if (!slides.length) {
    return;
  }

  let activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
  if (activeIndex < 0) {
    activeIndex = 0;
    slides[0].classList.add('is-active');
  }

  const dots = Array.from(slider.querySelectorAll('.hero-slide-dot'));
  const prevBtn = slider.querySelector('.hero-slide-btn.prev');
  const nextBtn = slider.querySelector('.hero-slide-btn.next');
  const interval = Number(slider.getAttribute('data-interval')) || 6500;
  const hasMultiple = slides.length > 1;
  let timerId = null;

  const setActive = (index) => {
    slides[activeIndex].classList.remove('is-active');
    if (dots[activeIndex]) {
      dots[activeIndex].classList.remove('is-active');
    }

    activeIndex = (index + slides.length) % slides.length;

    slides[activeIndex].classList.add('is-active');
    if (dots[activeIndex]) {
      dots[activeIndex].classList.add('is-active');
    }
  };

  const move = (step) => {
    setActive(activeIndex + step);
  };

  const restartTimer = () => {
    if (timerId) {
      clearInterval(timerId);
      timerId = null;
    }
    if (hasMultiple) {
      timerId = setInterval(() => move(1), interval);
    }
  };

  if (prevBtn && nextBtn) {
    prevBtn.addEventListener('click', () => {
      move(-1);
      restartTimer();
    });
    nextBtn.addEventListener('click', () => {
      move(1);
      restartTimer();
    });
  }

  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
      setActive(index);
      restartTimer();
    });
  });

  slider.addEventListener('pointerenter', () => {
    if (timerId) {
      clearInterval(timerId);
      timerId = null;
    }
  });

  slider.addEventListener('pointerleave', () => {
    restartTimer();
  });

  restartTimer();
})();
</script>
<?= $this->endSection() ?>
