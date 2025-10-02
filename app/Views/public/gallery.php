<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="public-section">
  <div class="container public-container py-5">
    <div class="text-center mb-5">
      <span class="hero-badge text-uppercase">Galeri</span>
      <h1 class="display-5 fw-bold mt-3 mb-3">Dokumentasi Kegiatan</h1>
      <p class="lead text-muted">Potret aktivitas dan layanan yang kami hadirkan untuk masyarakat.</p>
    </div>
    <?php if ($galleries): ?>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($galleries as $item): ?>
          <div class="col">
            <article class="surface-card gallery-item h-100">
              <?php if (! empty($item['image_path'])): ?>
                <div class="gallery-item-media">
                  <img src="<?= esc(base_url($item['image_path'])) ?>" alt="<?= esc($item['title']) ?>" loading="lazy">
                </div>
              <?php endif; ?>
              <div class="gallery-item-body">
                <h3><?= esc($item['title']) ?></h3>
                <?php if (! empty($item['description'])): ?>
                  <p class="text-muted small mb-0"><?= esc($item['description']) ?></p>
                <?php endif; ?>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <p class="text-muted">Galeri belum tersedia.</p>
      </div>
    <?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>

