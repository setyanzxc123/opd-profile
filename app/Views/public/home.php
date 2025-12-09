<?= $this->extend('layouts/public') ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" href="<?= base_url('assets/vendor/swiper/swiper-bundle.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/public/hero-slider.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/public/home-enhancements.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/public/home-news.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/public/home-app-links.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/vendor/swiper/swiper-bundle.min.js') ?>" defer></script>
<script src="<?= base_url('assets/js/public-hero.js') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php helper('image'); ?>
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
                    <?php
                      $srcsetData = responsive_srcset($slide['thumbnail'], [400, 800, 1200], '100vw');
                      $isFirst = $index === 0;
                    ?>
                    <img 
                      src="<?= esc($slide['thumbnail']) ?>" 
                      srcset="<?= esc($srcsetData['srcset']) ?>"
                      sizes="<?= esc($srcsetData['sizes']) ?>"
                      alt="<?= esc($slide['title']) ?>" 
                      width="1200" 
                      height="675" 
                      loading="<?= $isFirst ? 'eager' : 'lazy' ?>" 
                      decoding="async" 
                      fetchpriority="<?= $isFirst ? 'high' : 'auto' ?>">
                  <?php else: ?>
                    <div class="hero-placeholder" role="img" aria-label="Thumbnail belum tersedia">Thumbnail belum tersedia</div>
                  <?php endif; ?>
                </figure>
                <div class="hero-cover-overlay">
                  <div class="hero-cover-copy">
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

  <!-- Quick Actions -->
  <section class="public-section section-warm" aria-labelledby="quick-actions-heading">
    <div class="container public-container">
      <header class="section-head">
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
            <a href="<?= esc($service['target']) ?>" class="surface-card service-minimal text-decoration-none d-block h-100" role="listitem">
              <?php 
                $iconPath = $service['icon'] ?? '';
                $hasIcon = false;
                if (!empty($iconPath)) {
                    // Check if file exists relative to FCPATH (public folder)
                    $localPath = FCPATH . ltrim($iconPath, '/\\');
                    if (is_file($localPath)) {
                        $hasIcon = true;
                    }
                }
              ?>
              <?php if ($hasIcon): ?>
                <div class="d-flex justify-content-center mb-3">
                  <img src="<?= esc(base_url($iconPath), 'attr') ?>" alt="" width="48" height="48" style="object-fit: contain;" loading="lazy">
                </div>
              <?php else: ?>
                <div class="d-flex justify-content-center mb-3">
                  <span class="mono-badge" aria-hidden="true"><?= esc($service['initial']) ?></span>
                </div>
              <?php endif; ?>
              <h3 class="h5 mb-2 mt-2 text-center text-dark"><?= esc($service['title']) ?></h3>
              <?php if ($service['summary']): ?>
                <p class="text-muted text-center small mb-0"><?= esc($service['summary']) ?></p>
              <?php endif; ?>
            </a>
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
      <div class="news-home-grid">
        <?php if ($featuredNews): ?>
          <!-- Featured News (Left) - Fully clickable -->
          <a href="<?= site_url('berita/' . esc($featuredNews['slug'], 'url')) ?>" class="news-home-featured">
            <div class="news-home-featured__media">
              <?php if ($featuredNews['thumbnail']): ?>
                <?php $featuredSrcset = responsive_srcset($featuredNews['thumbnail'], [400, 800], '(max-width: 768px) 100vw, 50vw'); ?>
                <img 
                  src="<?= esc($featuredNews['thumbnail']) ?>" 
                  srcset="<?= esc($featuredSrcset['srcset']) ?>"
                  sizes="<?= esc($featuredSrcset['sizes']) ?>"
                  alt="<?= esc($featuredNews['title']) ?>" 
                  width="800" 
                  height="450" 
                  loading="eager" 
                  decoding="async" 
                  fetchpriority="high">
              <?php else: ?>
                <div class="news-placeholder"><i class="bx bx-image"></i></div>
              <?php endif; ?>
              <?php if (! empty($featuredNews['category'])): ?>
                <span class="news-badge-category"><?= esc($featuredNews['category']) ?></span>
              <?php endif; ?>
            </div>
            <div class="news-home-featured__body">
              <?php if ($featuredNews['published']): ?>
                <span class="news-date"><?= esc($featuredNews['published']) ?></span>
              <?php endif; ?>
              <h3><?= esc($featuredNews['title']) ?></h3>
              <?php if ($featuredNews['excerpt']): ?>
                <p class="news-excerpt"><?= esc($featuredNews['excerpt']) ?></p>
              <?php endif; ?>
            </div>
          </a>
        <?php endif; ?>
        
        <!-- Other News (Right - 4 items, fully clickable) -->
        <div class="news-home-list">
          <?php foreach ($otherNews as $news): ?>
            <a href="<?= site_url('berita/' . esc($news['slug'], 'url')) ?>" class="news-home-item">
              <div class="news-home-item__media">
                <?php if (! empty($news['thumbnail'])): ?>
                  <?php $newsSrcset = responsive_srcset($news['thumbnail'], [400], '120px'); ?>
                  <img 
                    src="<?= esc($news['thumbnail']) ?>" 
                    srcset="<?= esc($newsSrcset['srcset']) ?>"
                    sizes="<?= esc($newsSrcset['sizes']) ?>"
                    alt="<?= esc($news['title']) ?>" 
                    width="120" 
                    height="68" 
                    loading="lazy" 
                    decoding="async">
                <?php else: ?>
                  <div class="news-placeholder"><i class="bx bx-image"></i></div>
                <?php endif; ?>
                <?php if (! empty($news['category'])): ?>
                  <span class="news-badge-category"><?= esc($news['category']) ?></span>
                <?php endif; ?>
              </div>
              <div class="news-home-item__body">
                <?php if ($news['published']): ?>
                  <span class="news-date"><?= esc($news['published']) ?></span>
                <?php endif; ?>
                <h4><?= esc($news['title']) ?></h4>
              </div>
            </a>
          <?php endforeach; ?>
          <?php if (! $otherNews && ! $featuredNews): ?>
            <p class="text-muted">Belum ada berita yang dipublikasikan.</p>
          <?php endif; ?>
        </div>
      </div>
      <div class="section-cta mt-4">
        <a class="btn btn-public-primary" href="<?= site_url('berita') ?>">Lihat Semua Berita</a>
      </div>
    </div>
  </section>

  <!-- Tautan Aplikasi Terkait -->
  <?php 
    $showAppLinksEnabled = ($profile['show_app_links'] ?? '1') == '1';
  ?>
  <?php if (!empty($appLinks) && $showAppLinksEnabled): ?>
  <section class="public-section section-warm" id="tautan-aplikasi" aria-labelledby="app-links-heading">
    <div class="container public-container">
      <header class="section-head">
        <h2 class="section-title" id="app-links-heading">Tautan Aplikasi</h2>
        <p class="section-lead">Akses cepat ke aplikasi dan layanan terkait di daerah</p>
      </header>
      
      <?php 
        $linkCount = count($appLinks);
        $useSlider = $linkCount > 6; // Use slider if more than 6 links
      ?>
      
      <?php if ($useSlider): ?>
        <!-- Auto-scrolling slider for many links -->
        <div class="app-links-slider">
          <div class="app-links-track">
            <?php 
              // Duplicate items for seamless loop
              $allLinks = array_merge($appLinks, $appLinks);
            ?>
            <?php foreach ($allLinks as $link): ?>
              <a href="<?= esc($link['url']) ?>" 
                 class="app-link-item" 
                 target="_blank" 
                 rel="noopener" 
                 title="<?= esc($link['description'] ?: $link['name']) ?>">
                <?php if (!empty($link['logo_path'])): ?>
                  <div class="app-link-logo">
                    <img src="<?= base_url($link['logo_path']) ?>" alt="<?= esc($link['name']) ?>" width="64" height="64" loading="lazy" decoding="async">
                  </div>
                <?php else: ?>
                  <div class="app-link-logo-placeholder">
                    <i class="bx bx-link-external"></i>
                  </div>
                <?php endif; ?>
                <span class="app-link-name"><?= esc($link['name']) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php else: ?>
        <!-- Static centered display for few links -->
        <div class="app-links-static">
          <?php foreach ($appLinks as $link): ?>
            <a href="<?= esc($link['url']) ?>" 
               class="app-link-item" 
               target="_blank" 
               rel="noopener" 
               title="<?= esc($link['description'] ?: $link['name']) ?>">
              <?php if (!empty($link['logo_path'])): ?>
                <div class="app-link-logo">
                  <img src="<?= base_url($link['logo_path']) ?>" alt="<?= esc($link['name']) ?>" width="64" height="64" loading="lazy" decoding="async">
                </div>
              <?php else: ?>
                <div class="app-link-logo-placeholder">
                  <i class="bx bx-link-external"></i>
                </div>
              <?php endif; ?>
              <span class="app-link-name"><?= esc($link['name']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

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
