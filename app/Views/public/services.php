<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="public-section">
  <div class="container public-container py-5">
    <div class="text-center mb-5">
      <span class="hero-badge text-uppercase">Layanan Publik</span>
      <h1 class="display-5 fw-bold mt-3 mb-3">Daftar Layanan</h1>
      <p class="lead text-muted">Temukan informasi lengkap mengenai persyaratan, biaya, dan estimasi waktu proses dari layanan kami.</p>
    </div>
    <?php if ($services): ?>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($services as $index => $service): ?>
          <div class="col">
            <article id="<?= esc($service['slug'], 'attr') ?>" class="surface-card service-card h-100">
              <span class="service-icon"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
              <h2 class="h5 fw-semibold mt-3 mb-2"><a class="text-decoration-none text-dark" href="<?= site_url('layanan#' . esc($service['slug'], 'url')) ?>"><?= esc($service['title']) ?></a></h2>
              <?php if (! empty($service['description'])): ?>
                <p class="text-muted"><?= esc($service['description']) ?></p>
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
        <p class="text-muted">Data layanan sedang disiapkan.</p>
      </div>
    <?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>
