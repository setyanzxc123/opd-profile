<?php use CodeIgniter\I18n\Time; ?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php $query = trim((string) ($search ?? '')); ?>
<section class="public-section bg-white" aria-labelledby="news-archive-heading">
  <div class="container public-container py-5">
    <div class="text-center mb-5">
      <span class="hero-badge text-uppercase" id="news-archive-heading">Berita Resmi</span>
      <h1 class="display-5 fw-bold mt-3 mb-3">Informasi &amp; Kabar Terbaru</h1>
      <p class="lead text-muted">Update kegiatan, kebijakan, dan layanan terbaru dari Dinas Pelayanan Publik.</p>
    </div>
    <form class="news-search mb-5" role="search" method="get" action="<?= current_url() ?>">
      <div class="input-group input-group-lg">
        <label class="visually-hidden" for="newsSearch">Cari berita</label>
        <input id="newsSearch" class="form-control" type="search" name="q" value="<?= esc($query) ?>" placeholder="Cari berita atau kebijakan" aria-label="Cari berita atau kebijakan">
        <button class="btn btn-public-primary" type="submit">Cari</button>
        <?php if ($query !== ''): ?>
          <a class="btn btn-outline-secondary" href="<?= current_url() ?>">Reset</a>
        <?php endif; ?>
      </div>
    </form>
    <?php if ($query !== ''): ?>
      <p class="text-muted">Menampilkan hasil untuk kata kunci <strong><?= esc($query) ?></strong>.</p>
    <?php endif; ?>
    <?php if ($articles): ?>
      <div class="row row-cols-1 row-cols-md-2 g-4 mb-5" role="list">
        <?php foreach ($articles as $article): ?>
          <div class="col" role="listitem">
            <article class="surface-card news-card h-100">
              <?php if (! empty($article['thumbnail'])): ?>
                <div class="news-card__media">
                  <img src="<?= esc(base_url($article['thumbnail'])) ?>" alt="<?= esc($article['title']) ?>" loading="lazy">
                </div>
              <?php endif; ?>
              <div class="news-card__body">
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
          <?= $pager->only(['q'])->links('default', 'default_full') ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="text-center py-5">
        <p class="text-muted mb-3">Belum ada berita yang cocok<?= $query !== '' ? ' dengan pencarian Anda' : '' ?>.</p>
        <?php if ($query !== ''): ?>
          <p class="text-muted">Coba gunakan kata kunci lain atau lihat arsip berita tanpa filter.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>

