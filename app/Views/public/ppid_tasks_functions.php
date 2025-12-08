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
        <li class="breadcrumb-item active" aria-current="page">Tugas & Fungsi</li>
      </ol>
    </nav>

    <!-- Header -->
    <header class="text-center mb-5">
      <h1 class="fw-bold mb-3">Tugas & Fungsi PPID</h1>
      <p class="text-muted">Pejabat Pengelola Informasi dan Dokumentasi</p>
    </header>

    <div class="row justify-content-center">
      <div class="col-lg-8">
        <article class="surface-card profile-card">
          <?php if (!empty($ppid['tasks_functions'])): ?>
            <div class="prose">
              <?= $ppid['tasks_functions'] ?>
            </div>
          <?php else: ?>
            <div class="text-center py-5">
              <i class="bx bx-list-check fs-1 text-muted mb-3 d-block"></i>
              <p class="text-muted mb-0">Informasi tugas dan fungsi PPID belum tersedia.</p>
            </div>
          <?php endif; ?>
        </article>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
