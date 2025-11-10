<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="public-section services-archive">
  <div class="container public-container py-5">
    <div class="text-center mb-5">
      <span class="hero-badge text-uppercase">Layanan Publik</span>
      <h1 class="display-5 fw-bold mt-3 mb-3">Informasi Layanan</h1>
      <p class="lead text-muted">Jelajahi layanan yang tersedia lengkap dengan penjelasan singkat dan visual yang memudahkan Anda mengenalinya.</p>
    </div>
    <?php if ($services): ?>
      <div class="row g-4 service-article-grid">
        <?php foreach ($services as $service): ?>
          <div class="col-md-6 col-lg-4">
            <article <?php if (! empty($service['slug'])): ?>id="<?= esc($service['slug'], 'attr') ?>" <?php endif; ?>class="service-article-card h-100 position-relative">
              <?php if (! empty($service['thumbnail'])): ?>
                <div class="ratio ratio-16x9 service-article-media">
                  <img src="<?= esc($service['thumbnail'], 'attr') ?>"
                       alt="Ilustrasi layanan <?= esc($service['title']) ?>"
                       loading="lazy">
                </div>
              <?php endif; ?>
              <div class="service-article-body">
                <span class="service-chip text-uppercase">Layanan</span>
                <h2 class="h5 fw-semibold mt-2 mb-2">
                  <?php if (! empty($service['url'])): ?>
                    <a href="<?= esc($service['url']) ?>" class="stretched-link text-decoration-none text-dark"><?= esc($service['title']) ?></a>
                  <?php else: ?>
                    <span><?= esc($service['title']) ?></span>
                  <?php endif; ?>
                </h2>
                <?php if (! empty($service['summary'])): ?>
                  <p class="service-summary text-muted mb-2"><?= esc($service['summary']) ?></p>
                <?php endif; ?>
                <?php if (! empty($service['body']) && $service['body'] !== $service['summary']): ?>
                  <p class="service-body mb-0"><?= esc($service['body']) ?></p>
                <?php endif; ?>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <p class="text-muted">Data layanan sedang disiapkan.</p>
      </div>
    <?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>
