<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="public-section">
  <div class="container public-container py-5">
    <div class="row gy-5 align-items-start">
      <div class="col-lg-8">
        <div class="mb-4">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="<?= site_url('/') ?>">Beranda</a></li>
              <li class="breadcrumb-item active" aria-current="page">Profil</li>
            </ol>
          </nav>
          <span class="hero-badge text-uppercase">Profil Organisasi</span>
          <h1 class="display-5 fw-bold mt-3 mb-3"><?= esc($profile['name']) ?></h1>
          <?php if (! empty($profile['description'])): ?>
            <p class="lead text-muted"><?= esc($profile['description']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Quick Links to Detail Pages -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <a href="<?= site_url('profil/sambutan') ?>" class="card surface-card h-100 text-decoration-none">
              <div class="card-body">
                <h3 class="h5 fw-semibold mb-2">
                  <i class="bx bx-message-square-detail me-2 text-primary"></i>Sambutan
                </h3>
                <p class="text-muted small mb-0">Kata sambutan dari pimpinan</p>
              </div>
            </a>
          </div>
          <div class="col-md-6">
            <a href="<?= site_url('profil/visi-misi') ?>" class="card surface-card h-100 text-decoration-none">
              <div class="card-body">
                <h3 class="h5 fw-semibold mb-2">
                  <i class="bx bx-target-lock me-2 text-primary"></i>Visi & Misi
                </h3>
                <p class="text-muted small mb-0">Visi dan misi organisasi</p>
              </div>
            </a>
          </div>
          <div class="col-md-6">
            <a href="<?= site_url('profil/tugas-fungsi') ?>" class="card surface-card h-100 text-decoration-none">
              <div class="card-body">
                <h3 class="h5 fw-semibold mb-2">
                  <i class="bx bx-briefcase me-2 text-primary"></i>Tugas & Fungsi
                </h3>
                <p class="text-muted small mb-0">Tugas pokok dan fungsi</p>
              </div>
            </a>
          </div>
          <div class="col-md-6">
            <a href="<?= site_url('struktur-organisasi') ?>" class="card surface-card h-100 text-decoration-none">
              <div class="card-body">
                <h3 class="h5 fw-semibold mb-2">
                  <i class="bx bx-sitemap me-2 text-primary"></i>Struktur Organisasi
                </h3>
                <p class="text-muted small mb-0">Diagram struktur organisasi</p>
              </div>
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <article class="surface-card profile-card mb-4">
          <h2 class="h5 fw-semibold mb-3">Informasi Kontak</h2>
          <ul class="list-unstyled text-muted mb-0">
            <?php if (! empty($profile['address'])): ?>
              <li class="mb-3"><strong class="d-block text-dark">Alamat</strong><?= nl2br(esc($profile['address'])) ?></li>
            <?php endif; ?>
            <?php if (! empty($profile['phone'])): ?>
              <li class="mb-3"><strong class="d-block text-dark">Telepon</strong><?= esc($profile['phone']) ?></li>
            <?php endif; ?>
            <?php if (! empty($profile['email'])): ?>
              <li class="mb-0"><strong class="d-block text-dark">Email</strong><a class="text-decoration-none" href="mailto:<?= esc($profile['email']) ?>"><?= esc($profile['email']) ?></a></li>
            <?php endif; ?>
          </ul>
          <?php if (empty($profile['address']) && empty($profile['phone']) && empty($profile['email'])): ?>
            <p class="mb-0 text-muted">Data kontak belum tersedia.</p>
          <?php endif; ?>
        </article>

        <article class="surface-card profile-card">
          <h3 class="h5 fw-semibold mb-3">Layanan Unggulan</h3>
          <p class="text-muted mb-3">Lihat detail layanan, persyaratan, dan estimasi waktu proses untuk tiap layanan kami.</p>
          <a class="btn btn-public-primary px-4 w-100" href="<?= site_url('layanan') ?>">Lihat Daftar Layanan</a>
        </article>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
