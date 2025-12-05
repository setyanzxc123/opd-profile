<?= $this->extend('layouts/public') ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" href="<?= base_url('assets/vendor/swiper/swiper-bundle.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/public/home-enhancements.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/vendor/swiper/swiper-bundle.min.js') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="public-home">
  <!-- Hero Slider - TETAP ADA -->
  <section class="hero-section hero-shell hero-soft" id="beranda" aria-labelledby="beranda-heading">
    <div class="container public-container">
      <?php if ($hero['hasSlider']): ?>
        <?php $heroSlideCount = is_array($hero['slides']) ? count($hero['slides']) : 0; ?>
        <?php $heroHasMultipleSlides = $heroSlideCount > 1; ?>
        <header class="visually-hidden" id="beranda-heading">Berita Terbaru</header>
        <div class="hero-slider swiper" data-hero-swiper data-autoplay-interval="6500">
          <div class="hero-slides swiper-wrapper" role="list">
            <?php foreach ($hero['slides'] as $index => $slide): ?>
              <article class="hero-slide hero-slide-cover swiper-slide" role="listitem" data-hero-slide>
                <figure class="hero-cover-media">
                  <?php if ($slide['thumbnail']): ?>
                    <img src="<?= esc($slide['thumbnail']) ?>" alt="<?= esc($slide['title']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="hero-placeholder" role="img" aria-label="Thumbnail belum tersedia">Thumbnail belum tersedia</div>
                  <?php endif; ?>
                </figure>
                <div class="hero-cover-overlay">
                  <div class="hero-cover-copy">
                    <?php if (! empty($slide['category'])): ?>
                      <?php if (! empty($slide['category_slug'])): ?>
                        <a class="hero-eyebrow hero-eyebrow-light d-inline-block" href="<?= site_url('berita/kategori/' . esc($slide['category_slug'], 'url')) ?>">Kategori <?= esc($slide['category']) ?></a>
                      <?php else: ?>
                        <span class="hero-eyebrow hero-eyebrow-light d-inline-block"><?= esc($slide['category']) ?></span>
                      <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($slide['published']): ?>
                      <span class="hero-eyebrow hero-eyebrow-light d-inline-block">Terbit <?= esc($slide['published']) ?></span>
                    <?php endif; ?>
                    <h2 class="hero-cover-title"><?= esc($slide['title']) ?></h2>
                    <div class="hero-cover-actions">
                      <a class="btn btn-public-primary" href="<?= esc($slide['link']) ?>"><?= esc($slide['button_text'] ?? 'Baca selengkapnya') ?></a>
                    </div>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <?php if ($heroHasMultipleSlides): ?>
            <div class="swiper-button-prev" aria-label="Slide sebelumnya"></div>
            <div class="swiper-button-next" aria-label="Slide selanjutnya"></div>
            <div class="swiper-pagination" aria-label="Pilih slide berita"></div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="hero-fallback-wrap">
          <div class="hero-grid">
            <div class="hero-copy">
              <span class="hero-eyebrow" id="beranda-heading">Selamat datang</span>
              <h1 class="hero-title"><?= esc($hero['fallback']['title']) ?></h1>
              <p class="hero-lead"><?= esc($hero['fallback']['description']) ?></p>
              <div class="hero-actions">
                <a class="btn btn-public-primary" href="<?= esc($hero['fallback']['ctaServices']) ?>">Eksplor layanan</a>
                <a class="hero-link" href="<?= esc($hero['fallback']['ctaContact']) ?>">Hubungi kami</a>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Quick Stats Bar -->
  <section class="public-section section-neutral py-4" aria-label="Statistik">
    <div class="container public-container">
      <div class="quick-stats">
        <div class="quick-stat">
          <div class="quick-stat__icon">
            <i class="bx bx-briefcase-alt-2"></i>
          </div>
          <span class="quick-stat__value"><?= esc($statistics['services'] ?? 0) ?></span>
          <span class="quick-stat__label">Layanan Publik</span>
        </div>
        <div class="quick-stat">
          <div class="quick-stat__icon">
            <i class="bx bx-news"></i>
          </div>
          <span class="quick-stat__value"><?= esc($statistics['news'] ?? 0) ?></span>
          <span class="quick-stat__label">Berita</span>
        </div>
        <div class="quick-stat">
          <div class="quick-stat__icon">
            <i class="bx bx-file"></i>
          </div>
          <span class="quick-stat__value"><?= esc($statistics['documents'] ?? 0) ?></span>
          <span class="quick-stat__label">Dokumen</span>
        </div>
        <div class="quick-stat">
          <div class="quick-stat__icon">
            <i class="bx bx-group"></i>
          </div>
          <span class="quick-stat__value"><?= number_format($statistics['visitors'] ?? 0) ?></span>
          <span class="quick-stat__label">Pengunjung</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Quick Actions -->
  <section class="public-section section-warm" aria-labelledby="quick-actions-heading">
    <div class="container public-container">
      <header class="section-head text-center">
        <h2 class="section-title" id="quick-actions-heading">Akses Cepat</h2>
        <p class="section-lead">Pilih menu yang Anda butuhkan</p>
      </header>
      <div class="quick-actions">
        <a href="<?= site_url('layanan') ?>" class="quick-action">
          <div class="quick-action__icon">
            <i class="bx bx-list-check"></i>
          </div>
          <span class="quick-action__label">Layanan Publik</span>
        </a>
        <a href="<?= site_url('berita') ?>" class="quick-action">
          <div class="quick-action__icon">
            <i class="bx bx-news"></i>
          </div>
          <span class="quick-action__label">Berita Terkini</span>
        </a>
        <a href="<?= site_url('dokumen') ?>" class="quick-action">
          <div class="quick-action__icon">
            <i class="bx bx-download"></i>
          </div>
          <span class="quick-action__label">Unduh Dokumen</span>
        </a>
        <a href="<?= site_url('kontak') ?>" class="quick-action">
          <div class="quick-action__icon">
            <i class="bx bx-phone-call"></i>
          </div>
          <span class="quick-action__label">Hubungi Kami</span>
        </a>
      </div>
    </div>
  </section>

  <!-- Sambutan Pimpinan -->
  <?php 
    $hasGreeting = !empty($profile['greeting']);
    $greetingText = $hasGreeting ? $profile['greeting'] : null;
    $leaderName = $profile['name'] ?? 'Kepala Dinas';
  ?>
  <?php if ($hasGreeting): ?>
  <section class="public-section section-neutral" aria-labelledby="welcome-heading">
    <div class="container public-container">
      <header class="section-head text-center">
        <h2 class="section-title" id="welcome-heading">Sambutan</h2>
      </header>
      <div class="welcome-section">
        <div class="welcome-photo welcome-photo--placeholder">
          <i class="bx bx-user"></i>
        </div>
        <div class="welcome-content">
          <p class="welcome-greeting">
            <?= esc(mb_strimwidth(strip_tags($greetingText), 0, 300, '...', 'UTF-8')) ?>
          </p>
          <div class="welcome-author">
            <span class="welcome-author__name"><?= esc($leaderName) ?></span>
            <span class="welcome-author__title">Kepala Dinas</span>
          </div>
          <a href="<?= site_url('profil/sambutan') ?>" class="btn btn-public-ghost btn-sm mt-2">Baca selengkapnya</a>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Layanan Unggulan -->
  <section class="public-section section-cool" id="layanan" aria-labelledby="layanan-heading">
    <div class="container public-container">
      <header class="section-head">
        <h2 class="section-title" id="layanan-heading">Layanan Unggulan</h2>
        <p class="section-lead">Layanan yang paling sering digunakan masyarakat</p>
      </header>
      <?php if ($services): ?>
        <div class="minimal-grid minimal-grid-4" role="list">
          <?php foreach ($services as $service): ?>
            <article class="surface-card service-minimal" role="listitem">
              <span class="mono-badge" aria-hidden="true"><?= esc($service['initial']) ?></span>
              <h3><a class="surface-link" href="<?= esc($service['target']) ?>"><?= esc($service['title']) ?></a></h3>
              <?php if ($service['summary']): ?>
                <p class="text-muted"><?= esc($service['summary']) ?></p>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
        <div class="section-cta mt-4">
          <a class="btn btn-public-primary" href="<?= site_url('layanan') ?>">Lihat Semua Layanan</a>
        </div>
      <?php else: ?>
        <p class="text-muted">Data layanan belum tersedia.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Berita Terkini -->
  <section class="public-section section-neutral" id="berita" aria-labelledby="berita-heading">
    <div class="container public-container">
      <header class="section-head">
        <h2 class="section-title" id="berita-heading">Berita Terkini</h2>
        <p class="section-lead">Informasi terbaru seputar kegiatan dan kebijakan</p>
      </header>
      <div class="news-grid">
        <?php if ($featuredNews): ?>
          <article class="news-featured surface-card">
            <?php if ($featuredNews['thumbnail']): ?>
              <img class="news-featured__media" src="<?= esc($featuredNews['thumbnail']) ?>" alt="<?= esc($featuredNews['title']) ?>" loading="lazy">
            <?php endif; ?>
            <div class="news-featured__body">
              <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <?php if (! empty($featuredNews['category'])): ?>
                  <a class="badge bg-primary-subtle text-primary" href="<?= site_url('berita/kategori/' . esc($featuredNews['category_slug'], 'url')) ?>">
                    <?= esc($featuredNews['category']) ?>
                  </a>
                <?php endif; ?>
                <?php if ($featuredNews['published']): ?>
                  <span class="news-meta mb-0"><?= esc($featuredNews['published']) ?></span>
                <?php endif; ?>
              </div>
              <h3><a class="surface-link" href="<?= site_url('berita/' . esc($featuredNews['slug'], 'url')) ?>"><?= esc($featuredNews['title']) ?></a></h3>
              <?php if ($featuredNews['excerpt']): ?>
                <p class="text-muted"><?= esc($featuredNews['excerpt']) ?></p>
              <?php endif; ?>
              <a class="btn btn-public-primary" href="<?= site_url('berita/' . esc($featuredNews['slug'], 'url')) ?>">Baca selengkapnya</a>
            </div>
          </article>
        <?php endif; ?>
        <div class="news-list" role="list">
          <?php foreach ($otherNews as $news): ?>
            <article class="news-list-item card-base" role="listitem">
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <?php if (! empty($news['category'])): ?>
                    <a class="badge bg-primary-subtle text-primary" href="<?= site_url('berita/kategori/' . esc($news['category_slug'], 'url')) ?>">
                      <?= esc($news['category']) ?>
                    </a>
                  <?php endif; ?>
                  <?php if ($news['published']): ?>
                    <span class="news-meta mb-0"><?= esc($news['published']) ?></span>
                  <?php endif; ?>
                </div>
                <h3><a class="surface-link" href="<?= site_url('berita/' . esc($news['slug'], 'url')) ?>"><?= esc($news['title']) ?></a></h3>
                <?php if ($news['excerpt']): ?>
                  <p class="text-muted small mb-0"><?= esc($news['excerpt']) ?></p>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
          <?php if (! $otherNews && ! $featuredNews): ?>
            <p class="text-muted">Belum ada berita yang dipublikasikan.</p>
          <?php endif; ?>
        </div>
      </div>
      <div class="section-cta">
        <a class="hero-link" href="<?= site_url('berita') ?>">Lihat arsip berita</a>
      </div>
    </div>
  </section>

  <!-- Hubungi Kami dengan Peta -->
  <?php 
    $hasCoordinates = !empty($profile['latitude']) && !empty($profile['longitude']);
    $latitude = $profile['latitude'] ?? null;
    $longitude = $profile['longitude'] ?? null;
    $mapZoom = $profile['map_zoom'] ?? 16;
  ?>
  <section class="public-section section-warm" id="kontak" aria-labelledby="kontak-heading">
    <div class="container public-container">
      <header class="section-head">
        <h2 class="section-title" id="kontak-heading">Hubungi Kami</h2>
        <p class="section-lead">Kami siap melayani pertanyaan dan pengaduan Anda</p>
      </header>
      
      <div class="contact-grid">
        <div class="contact-info-list">
          <?php if (!empty($profileSummary['address'])): ?>
          <div class="contact-info-item">
            <div class="contact-info-item__icon">
              <i class="bx bx-map"></i>
            </div>
            <div class="contact-info-item__content">
              <div class="contact-info-item__label">Alamat</div>
              <div class="contact-info-item__value"><?= nl2br(esc($profileSummary['address'])) ?></div>
            </div>
          </div>
          <?php endif; ?>
          
          <?php if (!empty($profileSummary['phone'])): ?>
          <a href="tel:<?= preg_replace('/[^0-9+]/', '', $profileSummary['phone']) ?>" class="contact-info-item">
            <div class="contact-info-item__icon">
              <i class="bx bx-phone"></i>
            </div>
            <div class="contact-info-item__content">
              <div class="contact-info-item__label">Telepon</div>
              <div class="contact-info-item__value"><?= esc($profileSummary['phone']) ?></div>
            </div>
          </a>
          <?php endif; ?>
          
          <?php if (!empty($profileSummary['email'])): ?>
          <a href="mailto:<?= esc($profileSummary['email']) ?>" class="contact-info-item">
            <div class="contact-info-item__icon">
              <i class="bx bx-envelope"></i>
            </div>
            <div class="contact-info-item__content">
              <div class="contact-info-item__label">Email</div>
              <div class="contact-info-item__value"><?= esc($profileSummary['email']) ?></div>
            </div>
          </a>
          <?php endif; ?>
          
          <?php if (!empty($profile['operational_hours'])): ?>
          <div class="contact-info-item">
            <div class="contact-info-item__icon">
              <i class="bx bx-time"></i>
            </div>
            <div class="contact-info-item__content">
              <div class="contact-info-item__label">Jam Operasional</div>
              <div class="contact-info-item__value"><?= esc($profile['operational_hours']) ?></div>
            </div>
          </div>
          <?php endif; ?>
          
          <div class="mt-3">
            <a class="btn btn-public-primary" href="<?= site_url('kontak') ?>">
              <i class="bx bx-message-square-detail me-1"></i>Form Pengaduan
            </a>
          </div>
        </div>
        
        <?php if ($hasCoordinates): ?>
        <div class="contact-map">
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d<?= esc($longitude) ?>!3d<?= esc($latitude) ?>!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zM!5e0!3m2!1sid!2sid!4v1234567890"
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade"
            title="Lokasi Kantor">
          </iframe>
        </div>
        <?php else: ?>
        <div class="contact-map d-flex align-items-center justify-content-center bg-light">
          <div class="text-center text-muted p-4">
            <i class="bx bx-map-alt" style="font-size: 3rem;"></i>
            <p class="mb-0 mt-2">Peta lokasi belum tersedia</p>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

</div>
<?= $this->endSection() ?>
