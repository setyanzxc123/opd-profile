<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php helper('image'); ?>
<section class="public-section pt-3 pb-5">
  <div class="container">
    <!-- Breadcrumb -->
    <?= $this->include('public/components/_breadcrumb', ['current' => 'Layanan']) ?>

    <!-- Header -->
    <header class="text-center mb-5">
      <h1 class="fw-bold mb-3">Layanan Publik</h1>
      <p class="text-muted lead mx-auto" style="max-width: 540px;">
        Temukan informasi lengkap tentang layanan yang kami sediakan untuk masyarakat.
      </p>
    </header>

    <?php if ($services): ?>
      <!-- Responsive Grid -->
      <div class="row g-4">
        <?php foreach ($services as $service): ?>
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <a href="<?= site_url('layanan/' . esc($service['slug'], 'url')) ?>" class="service-card-link text-decoration-none">
              <article class="service-card surface-card h-100" <?php if (! empty($service['slug'])): ?>id="<?= esc($service['slug'], 'attr') ?>"<?php endif; ?>>
                <div class="service-card__icon">
                  <?php if (! empty($service['thumbnail'])): ?>
                    <?php $serviceSrcset = responsive_srcset($service['thumbnail'], [400], '72px'); ?>
                    <img 
                      src="<?= esc(base_url($service['thumbnail']), 'attr') ?>" 
                      srcset="<?= esc($serviceSrcset['srcset']) ?>"
                      sizes="<?= esc($serviceSrcset['sizes']) ?>"
                      alt="<?= esc($service['title']) ?>" 
                      width="72" 
                      height="72" 
                      loading="lazy" 
                      decoding="async">
                  <?php else: ?>
                    <i class="bx bx-briefcase"></i>
                  <?php endif; ?>
                </div>
                <div class="service-card__body">
                  <h2 class="service-card__title"><?= esc($service['title']) ?></h2>
                  <?php if (! empty($service['summary'])): ?>
                    <p class="service-card__desc"><?= esc($service['summary']) ?></p>
                  <?php endif; ?>
                </div>
                <div class="service-card__footer mt-auto pt-3">
                  <span class="btn btn-sm btn-outline-primary">
                    Lihat Detail <i class="bx bx-right-arrow-alt ms-1"></i>
                  </span>
                </div>
              </article>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <!-- Empty State -->
      <div class="text-center py-5">
        <div class="empty-state mx-auto" style="max-width: 320px;">
          <i class="bx bx-briefcase-alt-2"></i>
          <h3 class="h5 mt-3 mb-2">Belum Ada Layanan</h3>
          <p class="text-muted small mb-0">Data layanan sedang disiapkan. Silakan kunjungi kembali nanti.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/public/services.css') ?>">
<?= $this->endSection() ?>
