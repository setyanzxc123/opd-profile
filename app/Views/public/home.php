<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<div class="public-home">
  <section class="hero-section hero-shell hero-soft" id="beranda" aria-labelledby="beranda-heading">
    <div class="container public-container">
      <?php if ($hero['hasSlider']): ?>
        <div class="hero-slider" data-carousel data-carousel-interval="6500">
          <header class="visually-hidden" id="beranda-heading">Berita Terbaru</header>
          <div class="hero-slides" role="list">
            <?php foreach ($hero['slides'] as $index => $slide): ?>
              <article class="hero-slide hero-slide-cover<?= $slide['isActive'] ? ' is-active' : '' ?>" role="listitem" data-carousel-slide data-index="<?= $index ?>">
                <figure class="hero-cover-media">
                  <?php if ($slide['thumbnail']): ?>
                    <img src="<?= esc($slide['thumbnail']) ?>" alt="<?= esc($slide['title']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="hero-placeholder" role="img" aria-label="Thumbnail belum tersedia">Thumbnail belum tersedia</div>
                  <?php endif; ?>
                </figure>
                <div class="hero-cover-overlay">
                  <div class="hero-cover-copy">
                    <?php if (! empty($slide['category'])): ?>
                      <a class="hero-eyebrow hero-eyebrow-light d-inline-block" href="<?= site_url('berita/kategori/' . esc($slide['category_slug'], 'url')) ?>">Kategori <?= esc($slide['category']) ?></a>
                    <?php endif; ?>
                    <?php if ($slide['published']): ?>
                      <span class="hero-eyebrow hero-eyebrow-light d-inline-block">Terbit <?= esc($slide['published']) ?></span>
                    <?php endif; ?>
                    <h2 class="hero-cover-title"><?= esc($slide['title']) ?></h2>
                    <div class="hero-cover-actions">
                      <a class="btn btn-public-primary" href="<?= site_url('berita/' . esc($slide['slug'], 'url')) ?>">Baca selengkapnya</a>
                    </div>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <div class="hero-slider-controls" aria-hidden="true">
            <button class="hero-slide-btn prev" type="button" data-carousel-prev aria-label="Berita sebelumnya">&#8592;</button>
            <button class="hero-slide-btn next" type="button" data-carousel-next aria-label="Berita selanjutnya">&#8594;</button>
          </div>
          <div class="hero-slider-dots" role="tablist" aria-label="Pilih slide berita">
            <?php foreach ($hero['slides'] as $index => $slide): ?>
              <button type="button"
                      class="hero-slide-dot<?= $slide['isActive'] ? ' is-active' : '' ?>"
                      role="tab"
                      data-carousel-dot
                      aria-selected="<?= $slide['isActive'] ? 'true' : 'false' ?>"
                      aria-label="Slide berita <?= $index + 1 ?>"
                      data-index="<?= $index ?>"></button>
            <?php endforeach; ?>
          </div>
          <button type="button" class="hero-slider-toggle" data-carousel-toggle aria-pressed="false" aria-label="Jeda putar otomatis">Jeda</button>
        </div>
      <?php else: ?>
        <div class="hero-fallback-wrap">
          <div class="hero-grid">
            <div class="hero-copy">
              <span class="hero-eyebrow" id="beranda-heading">Selamat datang</span>
              <h1 class="hero-title"><?= esc($hero['fallback']['title']) ?></h1>
              <p class="hero-lead"><?= esc($hero['fallback']['description']) ?></p>
              <div class="hero-actions">
                <a class="btn btn-public-primary" href="<?= esc($hero['fallback']['ctaServices']) ?>">Eksplor layanan</a>
                <a class="hero-link" href="<?= esc($hero['fallback']['ctaContact']) ?>">Hubungi kami</a>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>
  <div class="section-divider section-divider-brand" aria-hidden="true">
    <span class="section-divider__label">PROFIL</span>
  </div>

  <section class="public-section section-neutral" id="profil-singkat" aria-labelledby="profil-heading">
    <div class="container public-container">
      <header class="section-head">
        <h2 class="section-title"><?= esc($profileSummary['name']) ?></h2>
        <?php if ($profileSummary['description']): ?>
          <p class="section-lead"><?= esc($profileSummary['description']) ?></p>
        <?php endif; ?>
      </header>
      <div class="profile-grid">
        <dl class="profile-list">
          <?php if ($profileSummary['address']): ?>
            <div class="profile-item">
              <dt>Alamat</dt>
              <dd><?= nl2br(esc($profileSummary['address'])) ?></dd>
            </div>
          <?php endif; ?>
          <?php if ($profileSummary['phone']): ?>
            <div class="profile-item">
              <dt>Telepon</dt>
              <dd><?= esc($profileSummary['phone']) ?></dd>
            </div>
          <?php endif; ?>
          <?php if ($profileSummary['email']): ?>
            <div class="profile-item">
              <dt>Email</dt>
              <dd><a class="surface-link" href="mailto:<?= esc($profileSummary['email']) ?>"><?= esc($profileSummary['email']) ?></a></dd>
            </div>
          <?php endif; ?>
        </dl>
        <div class="profile-card surface-card" role="complementary" aria-label="Tautan cepat">
          <h3>Butuh layanan cepat?</h3>
          <p class="text-muted">Kunjungi halaman layanan untuk melihat persyaratan, biaya, dan estimasi waktu proses.</p>
          <a class="btn btn-public-primary" href="<?= site_url('layanan') ?>">Daftar layanan</a>
        </div>
      </div>
    </div>
  </section>
  <div class="section-divider section-divider-brand" aria-hidden="true">
    <span class="section-divider__label">LAYANAN UNGGULAN</span>
  </div>

  <section class="public-section section-warm" id="layanan" aria-labelledby="layanan-heading">
    <div class="container public-container">
      <header class="section-head">
        <h2 class="section-title">Kemudahan akses untuk layanan prioritas</h2>
        <p class="section-lead">Pilih layanan yang paling sering digunakan masyarakat dan mulai proses secara daring.</p>
      </header>
      <?php if ($services): ?>
        <div class="minimal-grid minimal-grid-4" role="list">
          <?php foreach ($services as $service): ?>
            <article class="surface-card service-minimal" role="listitem">
              <span class="mono-badge" aria-hidden="true"><?= esc($service['initial']) ?></span>
              <h3><a class="surface-link" href="<?= esc($service['target']) ?>"><?= esc($service['title']) ?></a></h3>
              <?php if ($service['summary']): ?>
                <p class="text-muted"><?= esc($service['summary']) ?></p>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-muted">Data layanan belum tersedia.</p>
      <?php endif; ?>
    </div>
  </section>
  <div class="section-divider section-divider-brand" aria-hidden="true">
    <span class="section-divider__label">BERITA</span>
  </div>

  <section class="public-section section-neutral" id="berita" aria-labelledby="berita-heading">
    <div class="container public-container">
      <header class="section-head">
        <h2 class="section-title">Informasi terkini</h2>
        <p class="section-lead">Ikuti kabar terbaru mengenai kebijakan, pelayanan, dan kegiatan penting.</p>
      </header>
      <div class="news-grid">
        <?php if ($featuredNews): ?>
          <article class="news-featured surface-card">
            <?php if ($featuredNews['thumbnail']): ?>
              <img class="news-featured__media" src="<?= esc($featuredNews['thumbnail']) ?>" alt="<?= esc($featuredNews['title']) ?>" loading="lazy">
            <?php endif; ?>
            <div class="news-featured__body">
              <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <?php if (! empty($featuredNews['category'])): ?>
                  <a class="badge bg-primary-subtle text-primary" href="<?= site_url('berita/kategori/' . esc($featuredNews['category_slug'], 'url')) ?>">
                    <?= esc($featuredNews['category']) ?>
                  </a>
                <?php endif; ?>
                <?php if ($featuredNews['published']): ?>
                  <span class="news-meta mb-0"><?= esc($featuredNews['published']) ?></span>
                <?php endif; ?>
              </div>
              <h3><a class="surface-link" href="<?= site_url('berita/' . esc($featuredNews['slug'], 'url')) ?>"><?= esc($featuredNews['title']) ?></a></h3>
              <?php if ($featuredNews['excerpt']): ?>
                <p class="text-muted"><?= esc($featuredNews['excerpt']) ?></p>
              <?php endif; ?>
              <a class="btn btn-public-primary" href="<?= site_url('berita/' . esc($featuredNews['slug'], 'url')) ?>">Baca selengkapnya</a>
            </div>
          </article>
        <?php endif; ?>
        <div class="news-list" role="list">
          <?php foreach ($otherNews as $news): ?>
            <article class="news-list-item card-base" role="listitem">
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <?php if (! empty($news['category'])): ?>
                    <a class="badge bg-primary-subtle text-primary" href="<?= site_url('berita/kategori/' . esc($news['category_slug'], 'url')) ?>">
                      <?= esc($news['category']) ?>
                    </a>
                  <?php endif; ?>
                  <?php if ($news['published']): ?>
                    <span class="news-meta mb-0"><?= esc($news['published']) ?></span>
                  <?php endif; ?>
                </div>
                <h3><a class="surface-link" href="<?= site_url('berita/' . esc($news['slug'], 'url')) ?>"><?= esc($news['title']) ?></a></h3>
                <?php if ($news['excerpt']): ?>
                  <p class="text-muted small mb-0"><?= esc($news['excerpt']) ?></p>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
          <?php if (! $otherNews && ! $featuredNews): ?>
            <p class="text-muted">Belum ada berita yang dipublikasikan.</p>
          <?php endif; ?>
        </div>
      </div>
      <div class="section-cta">
        <a class="hero-link" href="<?= site_url('berita') ?>">Lihat arsip berita</a>
      </div>
    </div>
  </section>
  <div class="section-divider section-divider-brand" aria-hidden="true">
    <span class="section-divider__label">GALERI KEGIATAN</span>
  </div>

  <section class="public-section section-neutral" id="galeri" aria-labelledby="galeri-heading">
    <div class="container public-container">
      <header class="section-head">
        <h2 class="section-title">Dokumentasi pelayanan dan aktivitas</h2>
      </header>
      <?php if ($galleries): ?>
        <div class="minimal-grid minimal-grid-4 gallery-minimal" role="list">
          <?php foreach ($galleries as $gallery): ?>
            <article class="card-base gallery-item" role="listitem">
              <?php if ($gallery['image']): ?>
                <div class="gallery-item-media">
                  <img src="<?= esc($gallery['image']) ?>" alt="<?= esc($gallery['title']) ?>" loading="lazy">
                </div>
              <?php endif; ?>
              <div class="gallery-item-body">
                <h3><?= esc($gallery['title']) ?></h3>
                <?php if ($gallery['description']): ?>
                  <p class="text-muted small mb-0"><?= esc($gallery['description']) ?></p>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-muted">Belum ada dokumentasi yang ditampilkan.</p>
      <?php endif; ?>
    </div>
  </section>
  <div class="section-divider section-divider-brand" aria-hidden="true">
    <span class="section-divider__label">DOKUMEN PUBLIK</span>
  </div>

  <section class="public-section section-neutral" id="dokumen" aria-labelledby="dokumen-heading">
    <div class="container public-container">
      <header class="section-head">
        <h2 class="section-title">Unduh regulasi dan laporan resmi</h2>
      </header>
      <?php if ($documents): ?>
        <div class="documents-list" role="list">
          <?php foreach ($documents as $document): ?>
            <article class="document-item surface-card" role="listitem">
              <div>
                <h3><?= esc($document['title']) ?></h3>
                <p class="text-muted mb-0">
                  <?php if ($document['category']): ?>
                    <span><?= esc($document['category']) ?></span>
                    <span class="mx-2">�</span>
                  <?php endif; ?>
                  <?php if ($document['year']): ?>
                    <span><?= esc($document['year']) ?></span>
                  <?php endif; ?>
                </p>
              </div>
              <a class="btn btn-public-ghost" href="<?= esc($document['url']) ?>" target="_blank" rel="noopener">Unduh</a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-muted">Belum ada dokumen yang dapat diunduh.</p>
      <?php endif; ?>
    </div>
  </section>
  <div class="section-divider section-divider-brand" aria-hidden="true">
    <span class="section-divider__label">KONTAK CEPAT</span>
  </div>

  <section class="public-section section-neutral" id="kontak" aria-labelledby="kontak-heading">
    <div class="container public-container">
      <header class="section-head">
        <h2 class="section-title">Hubungi kami</h2>
        <p class="section-lead">Gunakan kanal berikut untuk memperoleh respons tercepat dari tim kami.</p>
      </header>
      <ul class="contact-quick-links" role="list">
        <?php foreach ($contactQuickLinks as $link): ?>
          <li role="listitem">
            <a class="surface-link" href="<?= esc($link['href']) ?>"<?= $link['href'] !== '#' ? ' target="_blank" rel="noopener"' : '' ?>>
              <span><?= esc($link['label']) ?></span>
              <span class="contact-link-value"><?= esc($link['value']) ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="section-cta">
        <a class="btn btn-public-primary" href="<?= site_url('kontak') ?>">Form pengaduan</a>
      </div>
    </div>
  </section>
</div>
<?= $this->endSection() ?>


