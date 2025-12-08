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
        <li class="breadcrumb-item active" aria-current="page">PPID</li>
      </ol>
    </nav>

    <!-- Header -->
    <header class="text-center mb-5">
      <h1 class="fw-bold mb-3">PPID</h1>
      <p class="text-muted lead">Pejabat Pengelola Informasi dan Dokumentasi</p>
    </header>

    <div class="row justify-content-center">
      <div class="col-lg-10">
        <!-- PPID Menu Cards -->
        <div class="row g-4">
          <!-- Tentang PPID -->
          <div class="col-md-4">
            <a href="<?= site_url('ppid/tentang') ?>" class="text-decoration-none">
              <div class="card h-100 border-0 shadow-sm ppid-card">
                <div class="card-body text-center py-5">
                  <div class="ppid-icon mb-3">
                    <i class="bx bx-info-circle"></i>
                  </div>
                  <h3 class="h5 fw-semibold mb-2">Tentang PPID</h3>
                  <p class="text-muted small mb-0">Informasi umum tentang PPID</p>
                </div>
              </div>
            </a>
          </div>

          <!-- Visi & Misi -->
          <div class="col-md-4">
            <a href="<?= site_url('ppid/visi-misi') ?>" class="text-decoration-none">
              <div class="card h-100 border-0 shadow-sm ppid-card">
                <div class="card-body text-center py-5">
                  <div class="ppid-icon mb-3">
                    <i class="bx bx-bullseye"></i>
                  </div>
                  <h3 class="h5 fw-semibold mb-2">Visi & Misi</h3>
                  <p class="text-muted small mb-0">Visi dan misi PPID</p>
                </div>
              </div>
            </a>
          </div>

          <!-- Tugas & Fungsi -->
          <div class="col-md-4">
            <a href="<?= site_url('ppid/tugas-fungsi') ?>" class="text-decoration-none">
              <div class="card h-100 border-0 shadow-sm ppid-card">
                <div class="card-body text-center py-5">
                  <div class="ppid-icon mb-3">
                    <i class="bx bx-list-check"></i>
                  </div>
                  <h3 class="h5 fw-semibold mb-2">Tugas & Fungsi</h3>
                  <p class="text-muted small mb-0">Tugas dan fungsi PPID</p>
                </div>
              </div>
            </a>
          </div>
        </div>

        <!-- About Section Preview -->
        <?php if (!empty($ppid['about'])): ?>
        <div class="mt-5">
          <article class="surface-card profile-card">
            <h2 class="h4 fw-semibold mb-3">
              <i class="bx bx-info-circle text-primary me-2"></i>Tentang PPID
            </h2>
            <div class="prose">
              <?php 
                $aboutPreview = strip_tags($ppid['about']);
                $aboutPreview = mb_strlen($aboutPreview) > 500 
                  ? mb_substr($aboutPreview, 0, 500) . '...' 
                  : $aboutPreview;
              ?>
              <?= nl2br(esc($aboutPreview)) ?>
            </div>
            <?php if (mb_strlen(strip_tags($ppid['about'])) > 500): ?>
            <div class="mt-3">
              <a href="<?= site_url('ppid/tentang') ?>" class="btn btn-outline-primary btn-sm">
                Selengkapnya <i class="bx bx-right-arrow-alt ms-1"></i>
              </a>
            </div>
            <?php endif; ?>
          </article>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<style>
.ppid-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.ppid-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.ppid-icon {
  width: 80px;
  height: 80px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--bs-primary) 0%, color-mix(in srgb, var(--bs-primary) 80%, black) 100%);
  color: white;
  font-size: 2rem;
}

.ppid-card:hover .ppid-icon {
  transform: scale(1.1);
  transition: transform 0.2s ease;
}
</style>
<?= $this->endSection() ?>
