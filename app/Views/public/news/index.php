<?php use CodeIgniter\I18n\Time; ?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
  $query           = trim((string) ($search ?? ''));
  $categoryOptions = $categories ?? [];
  $tagOptions      = $tags ?? [];
  $activeCategory  = $activeCategory ?? null;
  $activeTag       = $activeTag ?? null;
  $filters         = $filters ?? ['category' => null, 'tag' => null];
  $activeCategorySlug = $filters['category'] ?? null;
  $activeTagSlug      = $filters['tag'] ?? null;
  $hasFilter      = $query !== '' || $activeCategory || $activeTag;
  $searchParams   = $query !== '' ? ['q' => $query] : [];

  $buildUrl = static function (string $base, array $extra = []) use ($searchParams): string {
      $params = array_merge($searchParams, $extra);
      $params = array_filter($params, static fn ($value) => $value !== null && $value !== '');

      return $params !== [] ? $base . '?' . http_build_query($params) : $base;
  };
?>
<section class="public-section" aria-labelledby="news-archive-heading">
  <div class="container public-container py-5">
    <div class="text-center mb-5">
      <span class="hero-badge text-uppercase" id="news-archive-heading">
        <?php if ($activeCategory && $activeTag): ?>
          Kategori <?= esc($activeCategory['name']) ?> • Tag <?= esc($activeTag['name']) ?>
        <?php elseif ($activeCategory): ?>
          Kategori <?= esc($activeCategory['name']) ?>
        <?php elseif ($activeTag): ?>
          Tag <?= esc($activeTag['name']) ?>
        <?php else: ?>
          Berita Resmi
        <?php endif; ?>
      </span>
      <h1 class="display-5 fw-bold mt-3 mb-3">
        <?php if ($activeCategory): ?>
          Arsip Berita <?= esc($activeCategory['name']) ?>
        <?php elseif ($activeTag): ?>
          Artikel dengan Tag <?= esc($activeTag['name']) ?>
        <?php else: ?>
          Informasi &amp; Kabar Terbaru
        <?php endif; ?>
      </h1>
      <form class="news-search mb-4" role="search" method="get" action="<?= current_url() ?>">
        <div class="input-group input-group-lg">
          <label class="visually-hidden" for="newsSearch">Cari berita</label>
          <input id="newsSearch" class="form-control" type="search" name="q" value="<?= esc($query) ?>" placeholder="Cari berita atau kebijakan" aria-label="Cari berita atau kebijakan">
          <?php if ($activeCategorySlug && strpos(current_url(), '/kategori/') === false): ?>
            <input type="hidden" name="kategori" value="<?= esc($activeCategorySlug) ?>">
          <?php endif; ?>
          <?php if ($activeTagSlug && strpos(current_url(), '/tag/') === false): ?>
            <input type="hidden" name="tag" value="<?= esc($activeTagSlug) ?>">
          <?php endif; ?>
          <button class="btn btn-public-primary" type="submit">Cari</button>
          <?php if ($query !== ''): ?>
            <a class="btn btn-outline-secondary" href="<?= current_url() ?>">Reset</a>
          <?php endif; ?>
        </div>
      </form>
      <p class="lead text-muted mb-0">
        <?php if ($activeCategory || $activeTag): ?>
          Temukan berita resmi yang sudah dipilah berdasarkan kategori dan tag pilihan Anda.
        <?php else: ?>
          Update kegiatan, kebijakan, dan layanan terbaru dari Dinas Pelayanan Publik.
        <?php endif; ?>
      </p>
    </div>

    <?php if ($query !== ''): ?>
      <p class="text-muted">Menampilkan hasil untuk kata kunci <strong><?= esc($query) ?></strong>.</p>
    <?php endif; ?>
    <?php if ($activeCategory): ?>
      <p class="text-muted mb-1">Kategori aktif: <strong><?= esc($activeCategory['name']) ?></strong></p>
    <?php endif; ?>
    <?php if ($activeTag): ?>
      <p class="text-muted">Tag aktif: <strong><?= esc($activeTag['name']) ?></strong></p>
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
              <a class="btn btn-sm <?= $activeCategory ? 'btn-outline-secondary' : 'btn-public-primary' ?>"
                 href="<?= esc($categoryResetUrl) ?>">Semua Kategori</a>
              <?php foreach ($categoryOptions as $category): ?>
                <?php
                  $slug      = (string) ($category['slug'] ?? '');
                  $isActive  = $activeCategorySlug === $slug;
                  $categoryUrl = $buildUrl(site_url('berita/kategori/' . $slug), ['tag' => $activeTagSlug]);
                ?>
                <a class="btn btn-sm <?= $isActive ? 'btn-public-primary' : 'btn-outline-secondary' ?>"
                   href="<?= esc($categoryUrl) ?>">
                  <?= esc($category['name'] ?? 'Kategori') ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($tagOptions): ?>
          <div class="news-filter-group">
            <span class="fw-semibold d-block mb-2">Tag Konten</span>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-sm <?= $activeTag ? 'btn-outline-secondary' : 'btn-public-primary' ?>"
                 href="<?= esc($tagResetUrl) ?>">Semua Tag</a>
              <?php foreach ($tagOptions as $tag): ?>
                <?php
                  $slug     = (string) ($tag['slug'] ?? '');
                  $isActive = $activeTagSlug === $slug;
                  $tagUrl   = $buildUrl(site_url('berita/tag/' . $slug), ['kategori' => $activeCategorySlug]);
                ?>
                <a class="btn btn-sm <?= $isActive ? 'btn-public-primary' : 'btn-outline-secondary' ?>"
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
                <div class="d-flex flex-wrap gap-2 mb-2">
                  <?php if (! empty($article['primary_category'])): ?>
                    <?php $category = $article['primary_category']; ?>
                    <a class="badge bg-primary-subtle text-primary" href="<?= esc($buildUrl(site_url('berita/kategori/' . $category['slug']), ['tag' => $activeTagSlug])) ?>">
                      <?= esc($category['name']) ?>
                    </a>
                  <?php endif; ?>
                  <?php if (! empty($article['published_at'])): ?>
                    <?php $time = Time::parse($article['published_at']); ?>
                    <span class="badge bg-light text-primary"><?= esc($time->toLocalizedString('d MMMM yyyy')) ?></span>
                  <?php endif; ?>
                </div>
                <h2 class="h5 fw-semibold"><a class="text-decoration-none text-dark" href="<?= site_url('berita/' . esc($article['slug'], 'url')) ?>"><?= esc($article['title']) ?></a></h2>
                <?php if (! empty($article['content'])): ?>
                  <?php $excerpt = strip_tags($article['content']); ?>
                  <p class="text-muted mb-3"><?= esc(mb_strimwidth($excerpt, 0, 140, '...')) ?></p>
                <?php endif; ?>
                <?php if (! empty($article['tags'])): ?>
                  <div class="d-flex flex-wrap gap-1 mb-3">
                    <?php foreach ($article['tags'] as $tag): ?>
                      <?php $tagUrl = $buildUrl(site_url('berita/tag/' . $tag['slug']), ['kategori' => $article['primary_category']['slug'] ?? $activeCategorySlug]); ?>
                      <a class="badge bg-light text-secondary" href="<?= esc($tagUrl) ?>">#<?= esc($tag['name']) ?></a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <a class="btn btn-public-ghost btn-sm" href="<?= site_url('berita/' . esc($article['slug'], 'url')) ?>">Baca Selengkapnya</a>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if ($pager !== null): ?>
        <div class="d-flex justify-content-center">
          <?= $pager->only(['q', 'kategori', 'tag'])->links('default', 'default_full') ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="text-center py-5">
        <p class="text-muted mb-3">Belum ada berita yang cocok<?= $query !== '' ? ' dengan pencarian Anda' : '' ?>.</p>
        <?php if ($query !== ''): ?>
          <p class="text-muted">Coba gunakan kata kunci lain atau lihat arsip berita tanpa filter.</p>
        <?php elseif ($hasFilter): ?>
          <p class="text-muted">Cobalah mengatur ulang filter kategori atau tag untuk melihat daftar berita lainnya.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>

