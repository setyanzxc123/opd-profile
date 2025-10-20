<?php use CodeIgniter\I18n\Time; ?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
  $breadcrumbs     = $breadcrumbs ?? [];
  $categories      = $article['categories'] ?? [];
  $primaryCategory = $article['primary_category'] ?? null;
  $tags            = $article['tags'] ?? [];
  $relatedNews     = $relatedNews ?? [];
  $shareLinks      = $shareLinks ?? [];
  $mediaItems      = $article['media'] ?? [];
  $imageMedia      = array_values(array_filter($mediaItems, static fn (array $media): bool => ($media['media_type'] ?? '') === 'image' && ! empty($media['file_path'])));
  $videoMedia      = array_values(array_filter($mediaItems, static fn (array $media): bool => ($media['media_type'] ?? '') === 'video' && ! empty($media['external_url'])));
  $carouselId      = 'newsMediaCarousel-' . ($article['id'] ?? uniqid('media-', false));
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
      <?php if ($imageMedia || ! empty($article['thumbnail'])): ?>
        <div class="news-article__media mb-4">
          <?php if ($imageMedia): ?>
            <?php if (count($imageMedia) > 1): ?>
              <div id="<?= esc($carouselId, 'attr') ?>" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                  <?php foreach ($imageMedia as $index => $media): ?>
                    <?php
                      $isActive    = $index === 0 ? 'active' : '';
                      $imagePath   = base_url($media['file_path']);
                      $captionText = (string) ($media['caption'] ?? '');
                    ?>
                    <div class="carousel-item <?= $isActive ?>">
                      <figure class="mb-0">
                        <img src="<?= esc($imagePath, 'attr') ?>" class="d-block w-100 rounded" alt="<?= esc($captionText !== '' ? $captionText : ($article['title'] ?? 'Gambar Berita')) ?>" loading="lazy">
                        <?php if ($captionText !== ''): ?>
                          <figcaption class="carousel-caption d-none d-md-block">
                            <p class="mb-0"><?= esc($captionText) ?></p>
                          </figcaption>
                        <?php endif; ?>
                      </figure>
                    </div>
                  <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#<?= esc($carouselId, 'attr') ?>" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Sebelumnya</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#<?= esc($carouselId, 'attr') ?>" data-bs-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Selanjutnya</span>
                </button>
              </div>
            <?php else: ?>
              <?php
                $singleMedia = $imageMedia[0];
                $captionText = (string) ($singleMedia['caption'] ?? '');
                $imagePath   = base_url($singleMedia['file_path']);
              ?>
              <figure class="mb-0">
                <img src="<?= esc($imagePath, 'attr') ?>" class="img-fluid rounded" alt="<?= esc($captionText !== '' ? $captionText : ($article['title'] ?? 'Gambar Berita')) ?>" loading="lazy">
                <?php if ($captionText !== ''): ?>
                  <figcaption class="text-muted small mt-2"><?= esc($captionText) ?></figcaption>
                <?php endif; ?>
              </figure>
            <?php endif; ?>
          <?php else: ?>
            <figure class="mb-0">
              <img src="<?= esc(base_url($article['thumbnail'])) ?>" alt="<?= esc($article['title']) ?>" loading="lazy" class="img-fluid rounded">
            </figure>
          <?php endif; ?>
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
        <?php if ($shareLinks): ?>
          <div class="d-flex flex-wrap align-items-center gap-2 mt-4 mb-4">
            <span class="fw-semibold text-muted me-2">Bagikan:</span>
            <div class="d-flex flex-wrap gap-2">
              <?php if (! empty($shareLinks['whatsapp'])): ?>
                <a class="btn btn-outline-success btn-sm" href="<?= esc($shareLinks['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke WhatsApp">
                  <i class="bx bxl-whatsapp"></i>
                </a>
              <?php endif; ?>
              <?php if (! empty($shareLinks['telegram'])): ?>
                <a class="btn btn-outline-primary btn-sm" href="<?= esc($shareLinks['telegram']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke Telegram">
                  <i class="bx bxl-telegram"></i>
                </a>
              <?php endif; ?>
              <?php if (! empty($shareLinks['facebook'])): ?>
                <a class="btn btn-outline-secondary btn-sm" href="<?= esc($shareLinks['facebook']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke Facebook">
                  <i class="bx bxl-facebook"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
        <div class="news-content text-muted lead">
          <?= esc($article['content'], 'raw') ?>
        </div>
        <?php if ($videoMedia): ?>
          <div class="news-video-section mt-4">
            <h2 class="h5 fw-semibold mb-3">Video Terkait</h2>
            <div class="row row-cols-1 row-cols-md-2 g-4">
              <?php foreach ($videoMedia as $video): ?>
                <div class="col">
                  <div class="ratio ratio-16x9 border rounded overflow-hidden">
                    <iframe src="<?= esc($video['external_url'], 'attr') ?>" title="Video terkait" loading="lazy" allowfullscreen></iframe>
                  </div>
                  <?php if (! empty($video['caption'])): ?>
                    <p class="small text-muted mt-2 mb-0"><?= esc($video['caption']) ?></p>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
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
        <h2 class="h4 fw-semibold mb-3">Baca Juga</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
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
                  <h3 class="h6">
                    <a class="text-decoration-none stretched-link" href="<?= site_url('berita/' . esc($related['slug'], 'url')) ?>">
                      <?= esc($related['title']) ?>
                    </a>
                  </h3>
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
