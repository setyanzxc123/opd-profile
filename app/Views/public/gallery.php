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
            <article class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
              <img src="<?= esc(base_url($item['image_path'])) ?>" alt="<?= esc($item['title']) ?>" class="w-100" loading="lazy">
              <div class="card-body">
                <h2 class="h5 fw-semibold text-dark mb-2"><?= esc($item['title']) ?></h2>
                <?php if (! empty($item['description'])): ?>
                  <p class="mb-0 text-muted"><?= esc($item['description']) ?></p>
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
