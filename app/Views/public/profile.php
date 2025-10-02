<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="public-section bg-white">
  <div class="container public-container py-5">
    <div class="row gy-5 align-items-start">
      <div class="col-lg-7">
        <div class="mb-4">
          <span class="hero-badge text-uppercase">Profil Organisasi</span>
          <h1 class="display-5 fw-bold mt-3 mb-3"><?= esc($profile['name']) ?></h1>
          <?php if (! empty($profile['description'])): ?>
            <p class="lead text-muted"><?= esc($profile['description']) ?></p>
          <?php endif; ?>
        </div>
        <article class="surface-card profile-card mb-4">
          <h2 class="h4 fw-semibold mb-3">Visi</h2>
          <p class="mb-0 text-muted"><?= nl2br(esc($profile['vision'] ?? 'Belum tersedia.')) ?></p>
        </article>
        <article class="surface-card profile-card">
          <h2 class="h4 fw-semibold mb-3">Misi</h2>
          <?php
            $missions = array_filter(array_map('trim', preg_split('/\r\n|\n|\r/', (string) ($profile['mission'] ?? ''))));
          ?>
          <?php if ($missions): ?>
            <ul class="text-muted mb-0">
              <?php foreach ($missions as $mission): ?>
                <li class="mb-2"><?= esc($mission) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="mb-0 text-muted">Belum tersedia.</p>
          <?php endif; ?>
        </article>
      </div>
      <div class="col-lg-5">
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
          <h2 class="h5 fw-semibold mb-3">Layanan Unggulan</h2>
          <p class="text-muted mb-3">Lihat detail layanan, persyaratan, dan estimasi waktu proses untuk tiap layanan kami.</p>
          <a class="btn btn-public-primary px-4" href="<?= site_url('layanan') ?>">Lihat Daftar Layanan</a>
        </article>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
