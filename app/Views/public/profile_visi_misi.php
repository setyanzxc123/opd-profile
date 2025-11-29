<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="public-section">
  <div class="container public-container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="mb-4">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="<?= site_url('/') ?>">Beranda</a></li>
              <li class="breadcrumb-item"><a href="<?= site_url('profil') ?>">Profil</a></li>
              <li class="breadcrumb-item active" aria-current="page">Visi & Misi</li>
            </ol>
          </nav>
          <span class="hero-badge text-uppercase">Visi & Misi</span>
          <h1 class="display-5 fw-bold mt-3 mb-3"><?= esc($profile['name'] ?? 'OPD') ?></h1>
        </div>
        
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
