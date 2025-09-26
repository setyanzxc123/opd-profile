<?php use CodeIgniter\I18n\Time; ?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="public-section bg-white">
  <div class="container public-container py-5">
    <div class="text-center mb-5">
      <span class="hero-badge text-uppercase">Berita Resmi</span>
      <h1 class="display-5 fw-bold mt-3 mb-3">Informasi &amp; Kabar Terbaru</h1>
      <p class="lead text-muted">Update kegiatan, kebijakan, dan layanan terbaru dari Dinas Pelayanan Publik.</p>
    </div>
    <?php if ($articles): ?>
      <div class="row row-cols-1 row-cols-md-2 g-4 mb-5">
        <?php foreach ($articles as $article): ?>
          <div class="col">
            <article class="news-card h-100 shadow-sm border-0 bg-white rounded-4 overflow-hidden">
              <?php if (! empty($article['thumbnail'])): ?>
                <img src="<?= esc(base_url($article['thumbnail'])) ?>" alt="<?= esc($article['title']) ?>" class="w-100" loading="lazy">
              <?php endif; ?>
              <div class="p-4">
                <?php if (! empty($article['published_at'])): ?>
                  <?php $time = Time::parse($article['published_at']); ?>
                  <span class="badge bg-light text-primary mb-2"><?= esc($time->toLocalizedString('d MMMM yyyy')) ?></span>
                <?php endif; ?>
                <h2 class="h5 fw-semibold"><a class="text-decoration-none text-dark" href="<?= site_url('berita/' . esc($article['slug'], 'url')) ?>"><?= esc($article['title']) ?></a></h2>
                <?php if (! empty($article['content'])): ?>
                  <?php $excerpt = strip_tags($article['content']); ?>
                  <p class="text-muted mb-3"><?= esc(mb_strimwidth($excerpt, 0, 140, '...')) ?></p>
                <?php endif; ?>
                <a class="btn btn-public-ghost btn-sm" href="<?= site_url('berita/' . esc($article['slug'], 'url')) ?>">Baca Selengkapnya</a>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if ($pager !== null): ?>
        <div class="d-flex justify-content-center">
          <?= $pager->links('default', 'default_full') ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="text-center py-5">
        <p class="text-muted">Belum ada berita yang dipublikasikan.</p>
      </div>
    <?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>
