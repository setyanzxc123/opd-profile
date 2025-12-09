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
        <li class="breadcrumb-item"><a href="<?= site_url('profil') ?>" class="text-decoration-none">Profil</a></li>
        <li class="breadcrumb-item active" aria-current="page">Visi & Misi</li>
      </ol>
    </nav>

    <!-- Header -->
    <header class="text-center mb-5">
      <h1 class="fw-bold mb-3">Visi & Misi</h1>
      <p class="text-muted"><?= esc($profile['name'] ?? 'Organisasi Perangkat Daerah') ?></p>
    </header>

    <div class="row justify-content-center">
      <div class="col-lg-8">
        
        <article class="surface-card profile-card mb-4">
          <h2 class="h4 fw-semibold mb-3">
            <i class="bx bx-bullseye text-primary me-2"></i>Visi
          </h2>
          <?php if (!empty($profile['vision'])): ?>
            <div class="prose">
              <?= nl2br(esc($profile['vision'])) ?>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0">Visi belum tersedia.</p>
          <?php endif; ?>
        </article>
        
        <article class="surface-card profile-card">
          <h2 class="h4 fw-semibold mb-3">
            <i class="bx bx-target-lock text-primary me-2"></i>Misi
          </h2>
          <?php if (!empty($profile['mission'])): ?>
            <div class="prose">
              <?= nl2br(esc($profile['mission'])) ?>
            </div>
          <?php else: ?>
            <p class="mb-0 text-muted">Misi belum tersedia.</p>
          <?php endif; ?>
        </article>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
