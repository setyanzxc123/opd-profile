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
              <li class="breadcrumb-item active" aria-current="page">Sambutan</li>
            </ol>
          </nav>
          <span class="hero-badge text-uppercase">Sambutan</span>
          <h1 class="display-5 fw-bold mt-3 mb-3">Kata Sambutan</h1>
        </div>
        
        <article class="surface-card profile-card">
          <div class="prose">
            <?= nl2br(esc($profile['greeting'] ?? 'Sambutan belum tersedia.')) ?>
          </div>
        </article>

        <?php if (!empty($profile['name'])): ?>
          <div class="mt-4 text-muted">
            <p class="mb-1"><strong><?= esc($profile['name']) ?></strong></p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
