<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="public-section pt-3 pb-5">
  <div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item">
          <a href="<?= site_url('/') ?>" class="text-decoration-none">
            <i class="bx bx-home-alt me-1"></i>Beranda
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Layanan</li>
      </ol>
    </nav>

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
            <article class="service-card surface-card h-100" <?php if (! empty($service['slug'])): ?>id="<?= esc($service['slug'], 'attr') ?>"<?php endif; ?>>
              <div class="service-card__icon">
                <?php if (! empty($service['thumbnail'])): ?>
                  <img src="<?= esc($service['thumbnail'], 'attr') ?>" alt="<?= esc($service['title']) ?>" width="72" height="72" loading="lazy" decoding="async">
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
            </article>
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

<style>
/* Service Card - Grid Layout */
.service-card {
  display: flex;
  flex-direction: column;
  padding: 1.5rem;
  border-radius: 12px;
  text-align: center;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.service-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 28px -8px rgba(0, 0, 0, 0.12);
}

.service-card__icon {
  width: 72px;
  height: 72px;
  margin: 0 auto 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
  background: linear-gradient(135deg, 
    rgba(var(--public-primary-rgb, 13, 110, 253), 0.12) 0%,
    rgba(var(--public-primary-rgb, 13, 110, 253), 0.05) 100%);
  overflow: hidden;
}

.service-card__icon img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.service-card__icon i {
  font-size: 2rem;
  color: var(--public-primary, #0d6efd);
}

.service-card__body {
  flex: 1;
}

.service-card__title {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
  color: var(--public-neutral-900, #0f172a);
  line-height: 1.4;
}

.service-card__desc {
  font-size: 0.875rem;
  color: var(--public-neutral-600, #64748b);
  line-height: 1.5;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Empty State */
.empty-state {
  padding: 2rem;
}

.empty-state i {
  font-size: 3.5rem;
  color: var(--public-neutral-400, #94a3b8);
}
</style>
<?= $this->endSection() ?>
