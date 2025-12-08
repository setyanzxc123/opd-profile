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
        <li class="breadcrumb-item"><a href="<?= site_url('ppid') ?>" class="text-decoration-none">PPID</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tentang PPID</li>
      </ol>
    </nav>

    <!-- Header -->
    <header class="text-center mb-5">
      <h1 class="fw-bold mb-3">Tentang PPID</h1>
      <p class="text-muted">Pejabat Pengelola Informasi dan Dokumentasi</p>
    </header>

    <div class="row justify-content-center">
      <div class="col-lg-8">
        <article class="surface-card profile-card">
          <?php if (!empty($ppid['about'])): ?>
            <div class="prose">
              <?= $ppid['about'] ?>
            </div>
          <?php else: ?>
            <div class="text-center py-5">
              <i class="bx bx-info-circle fs-1 text-muted mb-3 d-block"></i>
              <p class="text-muted mb-0">Informasi tentang PPID belum tersedia.</p>
            </div>
          <?php endif; ?>
        </article>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
