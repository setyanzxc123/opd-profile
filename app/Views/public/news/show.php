<?php use CodeIgniter\I18n\Time; ?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="public-section bg-white">
  <div class="container public-container py-5">
    <div class="mb-4">
      <a class="btn btn-link text-decoration-none px-0" href="<?= site_url('berita') ?>">&larr; Kembali ke daftar berita</a>
    </div>
    <article class="surface-card news-article">
      <?php if (! empty($article['thumbnail'])): ?>
        <div class="news-article__media">
          <img src="<?= esc(base_url($article['thumbnail'])) ?>" alt="<?= esc($article['title']) ?>" loading="lazy">
        </div>
      <?php endif; ?>
      <div class="news-article__body">
        <?php if ($published_at instanceof Time): ?>
          <span class="badge bg-light text-primary mb-3">Dipublikasikan <?= esc($published_at->toLocalizedString('d MMMM yyyy')) ?></span>
        <?php endif; ?>
        <h1 class="fw-bold mb-4"><?= esc($article['title']) ?></h1>
        <div class="news-content text-muted lead">
          <?= esc($article['content'], 'raw') ?>
        </div>
      </div>
    </article>
  </div>
</section>
<?= $this->endSection() ?>
