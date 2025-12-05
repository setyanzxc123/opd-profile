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
    </header>

    <div class="row justify-content-center">
      <div class="col-lg-8">
        
        <article class="surface-card profile-card mb-4">
          <h2 class="h4 fw-semibold mb-3">Visi</h2>
          <div class="prose">
            <?= nl2br(esc($profile['vision'] ?? 'Visi belum tersedia.')) ?>
          </div>
        </article>
        
        <article class="surface-card profile-card">
          <h2 class="h4 fw-semibold mb-3">Misi</h2>
          <?php
            $missions = array_filter(array_map('trim', preg_split('/\r\n|\n|\r/', (string) ($profile['mission'] ?? ''))));
          ?>
          <?php if ($missions): ?>
            <ul class="prose mb-0">
              <?php foreach ($missions as $mission): ?>
                <li class="mb-2"><?= esc($mission) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="mb-0 text-muted">Misi belum tersedia.</p>
          <?php endif; ?>
        </article>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
