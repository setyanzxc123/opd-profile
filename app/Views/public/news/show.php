<?php use CodeIgniter\I18n\Time; ?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
  $breadcrumbs     = $breadcrumbs ?? [];
  $categories      = $article['categories'] ?? [];
  $primaryCategory = $article['primary_category'] ?? null;
  $tags            = $article['tags'] ?? [];
  $relatedNews     = $relatedNews ?? [];
  $excerpt         = (string) ($article['excerpt'] ?? '');
  $publicAuthor    = (string) ($article['public_author'] ?? '');
  $sourceInfo      = (string) ($article['source'] ?? '');
?>
<section class="public-section">
  <div class="container public-container py-5">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
      <?= view('public/components/breadcrumb', [
        'trail'     => $breadcrumbs,
        'ariaLabel' => 'Lintasan navigasi berita',
      ]) ?>
      <a class="btn btn-link text-decoration-none px-0" href="<?= site_url('berita') ?>">&larr; Kembali ke daftar berita</a>
    </div>
    <article class="surface-card news-article">
      <?php if (! empty($article['thumbnail'])): ?>
        <div class="news-article__media">
          <img src="<?= esc(base_url($article['thumbnail'])) ?>" alt="<?= esc($article['title']) ?>" loading="lazy">
        </div>
      <?php endif; ?>
      <div class="news-article__body">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <?php if ($primaryCategory): ?>
            <a class="badge bg-primary-subtle text-primary" href="<?= site_url('berita/kategori/' . esc($primaryCategory['slug'], 'url')) ?>">
              <?= esc($primaryCategory['name']) ?>
            </a>
          <?php endif; ?>
          <?php if ($published_at instanceof Time): ?>
            <span class="badge bg-light text-primary">Dipublikasikan <?= esc($published_at->toLocalizedString('d MMMM yyyy')) ?></span>
          <?php endif; ?>
        </div>
        <h1 class="fw-bold mb-4"><?= esc($article['title']) ?></h1>
        <?php if ($excerpt !== ''): ?>
          <p class="lead text-muted fst-italic border-start border-4 ps-3"><?= esc($excerpt) ?></p>
        <?php endif; ?>
        <?php if ($publicAuthor !== '' || $sourceInfo !== ''): ?>
          <div class="alert alert-light border mt-3" role="note">
            <?php if ($publicAuthor !== ''): ?>
              <div><strong>Penulis:</strong> <?= esc($publicAuthor) ?></div>
            <?php endif; ?>
            <?php if ($sourceInfo !== ''): ?>
              <div><strong>Sumber:</strong> <?= esc($sourceInfo) ?></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <div class="news-content text-muted lead">
          <?= esc($article['content'], 'raw') ?>
        </div>
        <?php if ($categories): ?>
          <div class="mt-4">
            <span class="fw-semibold me-2">Kategori:</span>
            <div class="d-inline-flex flex-wrap gap-2 align-items-center">
              <?php foreach ($categories as $category): ?>
                <a class="badge bg-light text-secondary" href="<?= site_url('berita/kategori/' . esc($category['slug'], 'url')) ?>">
                  <?= esc($category['name']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
        <?php if ($tags): ?>
          <div class="mt-3">
            <span class="fw-semibold me-2">Tag:</span>
            <div class="d-inline-flex flex-wrap gap-2 align-items-center">
              <?php foreach ($tags as $tag): ?>
                <a class="badge bg-light text-secondary" href="<?= site_url('berita/tag/' . esc($tag['slug'], 'url')) ?>">#<?= esc($tag['name']) ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </article>

    <?php if ($relatedNews): ?>
      <div class="mt-5">
        <h2 class="h4 fw-semibold mb-3">Berita Terkait</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
          <?php foreach ($relatedNews as $related): ?>
            <div class="col">
              <article class="card h-100 border-0 shadow-sm">
                <?php if (! empty($related['thumbnail'])): ?>
                  <img class="card-img-top" src="<?= esc(base_url($related['thumbnail'])) ?>" alt="<?= esc($related['title']) ?>" loading="lazy">
                <?php endif; ?>
                <div class="card-body">
                  <?php if (! empty($related['published_at'])): ?>
                    <?php $time = Time::parse($related['published_at']); ?>
                    <span class="badge bg-light text-primary mb-2"><?= esc($time->toLocalizedString('d MMM yyyy')) ?></span>
                  <?php endif; ?>
                  <h3 class="h6"><a class="text-decoration-none" href="<?= site_url('berita/' . esc($related['slug'], 'url')) ?>"><?= esc($related['title']) ?></a></h3>
                  <?php if (! empty($related['primary_category'])): ?>
                    <a class="badge bg-primary-subtle text-primary mt-2 d-inline-block" href="<?= site_url('berita/kategori/' . esc($related['primary_category']['slug'], 'url')) ?>">
                      <?= esc($related['primary_category']['name']) ?>
                    </a>
                  <?php endif; ?>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>
