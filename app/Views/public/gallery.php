<?= $this->extend('layouts/public') ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/public/gallery-lightbox.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/js/gallery-lightbox.js') ?>" defer></script>
<?= $this->endSection() ?>

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
        <li class="breadcrumb-item active" aria-current="page">Galeri</li>
      </ol>
    </nav>

    <!-- Header -->
    <header class="text-center mb-5">
      <h1 class="fw-bold mb-3">Galeri Kegiatan</h1>
      <p class="text-muted lead mx-auto" style="max-width: 540px;">
        Dokumentasi aktivitas dan layanan kami untuk masyarakat. Klik gambar untuk memperbesar.
      </p>
    </header>

    <?php if ($galleries): ?>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($galleries as $item): ?>
          <div class="col">
            <article class="surface-card gallery-item h-100">
              <?php if (! empty($item['image_path'])): ?>
                <div class="gallery-item-media">
                  <img src="<?= esc(base_url($item['image_path'])) ?>" alt="<?= esc($item['title']) ?>" width="400" height="300" loading="lazy" decoding="async">
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
