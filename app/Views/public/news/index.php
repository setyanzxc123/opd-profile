<?php
  use CodeIgniter\I18n\Time;

  $breadcrumbs     = $breadcrumbs ?? [];
  $query           = trim((string) ($search ?? ''));
  $categoryOptions = $categories ?? [];
  $tagOptions      = $tags ?? [];
  $activeCategory  = $activeCategory ?? null;
  $activeTag       = $activeTag ?? null;
  $filters         = $filters ?? ['category' => null, 'tag' => null];
  $activeCategorySlug = $filters['category'] ?? null;
  $activeTagSlug      = $filters['tag'] ?? null;
  $hasFilter        = $query !== '' || $activeCategory || $activeTag;
  $searchParams     = $query !== '' ? ['q' => $query] : [];
  $popularNewsItems = $popularNews ?? [];

  $buildUrl = static function (string $base, array $extra = []) use ($searchParams): string {
      $params = array_merge($searchParams, $extra);
      $params = array_filter($params, static fn ($value) => $value !== null && $value !== '');

      return $params !== [] ? $base . '?' . http_build_query($params) : $base;
  };
?>

<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="public-section pt-3 pb-5" aria-labelledby="news-archive-heading">
  <div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item">
          <a href="<?= site_url('/') ?>" class="text-decoration-none">
            <i class="bx bx-home-alt me-1"></i>Beranda
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Berita</li>
      </ol>
    </nav>

    <!-- Header -->
    <header class="text-center mb-5" id="news-archive-heading">
      <h1 class="fw-bold mb-3">Berita & Informasi</h1>
      <p class="text-muted lead mx-auto" style="max-width: 540px;">
        Ikuti perkembangan terbaru, pengumuman, dan informasi penting dari kami.
      </p>
    </header>

    <!-- Search Bar -->
    <form class="news-search mb-4" role="search" method="get" action="<?= current_url() ?>">
      <div class="input-group">
        <label class="visually-hidden" for="newsSearch">Cari berita</label>
        <input id="newsSearch" class="form-control" type="search" name="q" value="<?= esc($query) ?>" placeholder="Cari berita atau kebijakan..." aria-label="Cari berita atau kebijakan">
        <?php if ($activeCategorySlug && strpos(current_url(), '/kategori/') === false): ?>
          <input type="hidden" name="kategori" value="<?= esc($activeCategorySlug) ?>">
        <?php endif; ?>
        <?php if ($activeTagSlug && strpos(current_url(), '/tag/') === false): ?>
          <input type="hidden" name="tag" value="<?= esc($activeTagSlug) ?>">
        <?php endif; ?>
        <button class="btn btn-public-primary" type="submit">
          <i class='bx bx-search'></i> Cari
        </button>
        <?php if ($query !== ''): ?>
          <a class="btn btn-outline-secondary" href="<?= current_url() ?>">
            <i class='bx bx-x'></i>
          </a>
        <?php endif; ?>
      </div>
    </form>

    <?php if ($hasFilter || $query !== ''): ?>
      <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start mb-4">
        <?php if ($query !== ''): ?>
          <span class="badge rounded-pill bg-info-subtle text-info">
            Pencarian: "<?= esc($query) ?>"
          </span>
        <?php endif; ?>
        <?php if ($activeCategory): ?>
          <a class="badge rounded-pill bg-primary-subtle text-primary text-decoration-none"
             href="<?= esc($buildUrl(site_url('berita'), ['tag' => $activeTagSlug])) ?>">
            Kategori: <?= esc($activeCategory['name']) ?>
          </a>
        <?php endif; ?>
        <?php if ($activeTag): ?>
          <a class="badge rounded-pill bg-secondary-subtle text-secondary text-decoration-none"
             href="<?= esc($buildUrl($activeCategory ? site_url('berita/kategori/' . $activeCategory['slug']) : site_url('berita'))) ?>">
            Tag: <?= esc($activeTag['name']) ?>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($categoryOptions || $tagOptions): ?>
      <?php
        $categoryResetUrl = $buildUrl(site_url('berita'), ['tag' => $activeTagSlug]);
        $tagResetBase     = $activeCategorySlug ? site_url('berita/kategori/' . $activeCategorySlug) : site_url('berita');
        $tagResetUrl      = $buildUrl($tagResetBase);
      ?>
      <div class="news-filter-panel border rounded p-3 mb-5">
        <?php if ($categoryOptions): ?>
          <div class="news-filter-group mb-3">
            <span class="fw-semibold d-block mb-2">Kategori</span>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-chip <?= $activeCategory ? 'btn-outline-secondary' : 'btn-chip-primary' ?>"
                 href="<?= esc($categoryResetUrl) ?>">
                Semua Kategori
              </a>
              <?php foreach ($categoryOptions as $category): ?>
                <?php
                  $slug      = (string) ($category['slug'] ?? '');
                  $isActive  = $activeCategorySlug === $slug;
                  $categoryUrl = $buildUrl(site_url('berita/kategori/' . $slug), ['tag' => $activeTagSlug]);
                ?>
                <a class="btn btn-chip <?= $isActive ? 'btn-chip-primary' : 'btn-outline-secondary' ?>"
                   href="<?= esc($categoryUrl) ?>">
                  <i class='bx <?= esc($category['icon'] ?? 'bx-news') ?> me-1'></i><?= esc($category['name'] ?? 'Kategori') ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($tagOptions): ?>
          <div class="news-filter-group">
            <span class="fw-semibold d-block mb-2">Tag Konten</span>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-chip <?= $activeTag ? 'btn-outline-secondary' : 'btn-chip-secondary' ?>"
                 href="<?= esc($tagResetUrl) ?>">
                Semua Tag
              </a>
              <?php foreach ($tagOptions as $tag): ?>
                <?php
                  $slug     = (string) ($tag['slug'] ?? '');
                  $isActive = $activeTagSlug === $slug;
                  $tagUrl   = $buildUrl(site_url('berita/tag/' . $slug), ['kategori' => $activeCategorySlug]);
                ?>
                <a class="btn btn-chip <?= $isActive ? 'btn-chip-secondary' : 'btn-outline-secondary' ?>"
                   href="<?= esc($tagUrl) ?>">
                  <?= esc($tag['name'] ?? 'Tag') ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($hasFilter): ?>
          <div class="mt-3">
            <a class="btn btn-sm btn-link text-decoration-none" href="<?= esc($buildUrl(site_url('berita'))) ?>">Reset semua filter</a>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php
      // Load helper for read time
      helper('news');
      
      // Featured News Hero (only show on main news page without filters)
      $featured = $featuredNews ?? null;
      $showFeatured = $featured && !$hasFilter && $query === '';
    ?>
    
    <?php if ($showFeatured): ?>
      <?php
        $featuredExcerpt = news_trim_excerpt($featured['excerpt'] ?? null, (string) ($featured['content'] ?? ''), 180);
        $featuredReadTime = calculate_read_time((string) ($featured['content'] ?? ''));
        $featuredDate = !empty($featured['published_at']) ? Time::parse($featured['published_at']) : null;
        $featuredCategory = $featured['primary_category'] ?? null;
      ?>
      <article class="featured-news-hero mb-5">
        <div class="row g-4 align-items-center">
          <div class="col-lg-7">
            <a href="<?= site_url('berita/' . esc($featured['slug'], 'url')) ?>" class="featured-news-hero__image">
              <?php if (!empty($featured['thumbnail'])): ?>
                <img src="<?= esc(base_url($featured['thumbnail'])) ?>" alt="<?= esc($featured['title']) ?>" loading="eager">
              <?php else: ?>
                <!-- Placeholder if no thumbnail -->
              <?php endif; ?>
            </a>
          </div>
          <div class="col-lg-5">
            <div class="featured-news-hero__content">
              <span class="featured-news-hero__badge">
                <i class='bx bx-star'></i> FEATURED
              </span>
              <h2 class="featured-news-hero__title">
                <a href="<?= site_url('berita/' . esc($featured['slug'], 'url')) ?>">
                  <?= esc($featured['title']) ?>
                </a>
              </h2>
              <?php if ($featuredExcerpt): ?>
                <p class="featured-news-hero__excerpt">
                  <?= esc($featuredExcerpt) ?>
                </p>
              <?php endif; ?>
              <div class="featured-news-hero__meta">
                <?php if ($featuredCategory): ?>
                  <span class="featured-news-hero__meta-item">
                    <a href="<?= site_url('berita/kategori/' . esc($featuredCategory['slug'], 'url')) ?>" 
                       class="badge bg-primary-subtle text-primary text-decoration-none">
                      <i class='bx <?= esc($featuredCategory['icon'] ?? 'bx-news') ?> me-1'></i><?= esc($featuredCategory['name']) ?>
                    </a>
                  </span>
                <?php endif; ?>
                <?php if ($featuredDate): ?>
                  <span class="featured-news-hero__meta-item">
                    <i class='bx bx-calendar'></i>
                    <?= esc($featuredDate->toLocalizedString('d MMM yyyy')) ?>
                  </span>
                <?php endif; ?>
                <span class="featured-news-hero__meta-item">
                  <i class='bx bx-time-five'></i>
                  <?= $featuredReadTime ?> mnt baca
                </span>
              </div>
            </div>
          </div>
        </div>
      </article>
    <?php endif; ?>

    <div class="row g-5 align-items-start">
      <div class="col-12 col-lg-8">
        <?php if ($articles): ?>
          <div class="row row-cols-1 row-cols-md-2 g-4" role="list">
            <?php foreach ($articles as $article): ?>
              <?php
                $coverImage = $article['thumbnail'] ?? null;
                $date       = !empty($article['published_at']) ? Time::parse($article['published_at']) : null;
                $excerpt    = news_trim_excerpt($article['excerpt'] ?? null, (string) ($article['content'] ?? ''));
                $primaryCat = $article['primary_category'] ?? null;
                $readTime   = calculate_read_time((string) ($article['content'] ?? ''));
              ?>
              <div class="col" role="listitem">
                <article class="surface-card news-card h-100">
                  <?php if ($coverImage): ?>
                    <div class="news-card__media">
                      <img src="<?= esc(base_url($coverImage)) ?>" alt="<?= esc($article['title']) ?>" loading="lazy">
                    </div>
                  <?php endif; ?>
                  <div class="news-card__body">
                    <div class="news-card__meta mb-2">
                      <?php if ($primaryCat): ?>
                        <a href="<?= site_url('berita/kategori/' . esc($primaryCat['slug'], 'url')) ?>" class="text-magazine-category text-decoration-none text-primary">
                          <i class='bx <?= esc($primaryCat['icon'] ?? 'bx-news') ?> me-1'></i><?= esc($primaryCat['name']) ?>
                        </a>
                      <?php endif; ?>
                      <?php if ($date): ?>
                        <span class="text-magazine-meta ms-2">
                          <?= esc($date->toLocalizedString('d MMM yyyy')) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    <h2 class="h5 fw-bold mb-3">
                      <a class="text-decoration-none text-dark" href="<?= site_url('berita/' . esc($article['slug'], 'url')) ?>">
                        <?= esc($article['title']) ?>
                      </a>
                    </h2>
                    <p class="text-muted small mb-3">
                      <?= esc($excerpt) ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                      <span class="text-magazine-meta small">
                        <i class='bx bx-time-five'></i> <?= $readTime ?> mnt baca
                      </span>
                      <a href="<?= site_url('berita/' . esc($article['slug'], 'url')) ?>" class="surface-link small">
                        Baca Selengkapnya <i class="bx bx-right-arrow-alt"></i>
                      </a>
                    </div>
                  </div>
                </article>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="mt-5">
            <?= $pager->links() ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <img src="<?= base_url('assets/images/illustrations/no-data.svg') ?>" alt="Tidak ada berita" style="max-width: 200px; margin-bottom: 1.5rem; opacity: 0.7;">
            <h3 class="h5 fw-bold">Belum Ada Berita</h3>
            <p class="text-muted">
              <?php if ($hasFilter): ?>
                Tidak ada berita yang sesuai dengan filter atau pencarian Anda. Coba kata kunci lain.
              <?php else: ?>
                Belum ada berita yang dipublikasikan saat ini.
              <?php endif; ?>
            </p>
            <?php if ($hasFilter): ?>
              <a href="<?= site_url('berita') ?>" class="btn btn-outline-primary mt-3">Lihat Semua Berita</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-12 col-lg-4">
        <aside class="news-sidebar">
          <!-- Popular News Widget -->
          <div class="surface-card mb-4">
            <h3 class="h5 fw-bold mb-4 border-bottom pb-2">Berita Populer</h3>
            <div class="d-flex flex-column gap-3">
              <?php if ($popularNewsItems): ?>
                <?php foreach ($popularNewsItems as $item): ?>
                  <?php
                    $itemDate = !empty($item['published_at']) ? Time::parse($item['published_at']) : null;
                  ?>
                  <article class="d-flex gap-3 align-items-start">
                    <?php if (!empty($item['thumbnail'])): ?>
                      <img src="<?= esc(base_url($item['thumbnail'])) ?>" alt="<?= esc($item['title']) ?>" class="rounded object-fit-cover" width="80" height="80" loading="lazy">
                    <?php else: ?>
                      <div class="rounded bg-light d-flex align-items-center justify-content-center text-muted" style="width: 80px; height: 80px; flex-shrink: 0;">
                        <i class='bx bx-image fs-4'></i>
                      </div>
                    <?php endif; ?>
                    <div>
                      <h4 class="h6 fw-semibold mb-1">
                        <a href="<?= site_url('berita/' . esc($item['slug'], 'url')) ?>" class="text-decoration-none text-dark">
                          <?= esc($item['title']) ?>
                        </a>
                      </h4>
                      <?php if ($itemDate): ?>
                        <small class="text-muted"><?= esc($itemDate->toLocalizedString('d MMM yyyy')) ?></small>
                      <?php endif; ?>
                    </div>
                  </article>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-muted small mb-0">Belum ada berita populer.</p>
              <?php endif; ?>
            </div>
          </div>

          <!-- Categories Widget -->
          <div class="surface-card">
            <h3 class="h5 fw-bold mb-4 border-bottom pb-2">Kategori</h3>
            <div class="d-flex flex-column gap-2">
              <?php foreach ($categoryOptions as $cat): ?>
                <a href="<?= site_url('berita/kategori/' . esc($cat['slug'], 'url')) ?>" class="d-flex justify-content-between align-items-center text-decoration-none text-dark p-2 rounded hover-bg-light">
                  <span><i class='bx <?= esc($cat['icon'] ?? 'bx-news') ?> me-2'></i><?= esc($cat['name']) ?></span>
                  <span class="badge bg-light text-dark rounded-pill border">
                    <i class='bx bx-chevron-right'></i>
                  </span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
