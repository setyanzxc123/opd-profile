<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<div class="space-y-20 pb-24">
  <section id="beranda" aria-labelledby="beranda-heading" class="bg-base-100 pt-10 lg:pt-16">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
      <?php if ($hero['hasSlider']): ?>
        <div class="hero-slider relative overflow-hidden rounded-3xl bg-primary text-primary-content shadow-2xl" data-carousel data-carousel-interval="6500">
          <header class="sr-only" id="beranda-heading">Berita Terbaru</header>
          <div class="hero-slides relative" role="list">
            <?php foreach ($hero['slides'] as $index => $slide): ?>
              <article
                class="hero-slide hero-slide-cover<?= $slide['isActive'] ? ' is-active' : '' ?><?= $slide['isActive'] ? '' : ' hidden' ?> grid min-h-[320px] gap-6 p-8 lg:grid-cols-[1.35fr,1fr] lg:items-center"
                role="listitem"
                data-carousel-slide
                data-index="<?= $index ?>">
                <div class="order-last space-y-5 lg:order-first">
                  <span class="inline-flex items-center gap-2 rounded-full bg-primary-content/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.35em] text-primary-content/80">Berita</span>
                  <h2 class="text-2xl font-semibold leading-tight text-primary-content lg:text-3xl"><?= esc($slide['title']) ?></h2>
                  <div class="flex flex-wrap items-center gap-3">
                    <a class="btn btn-secondary btn-sm px-4" href="<?= site_url('berita/' . esc($slide['slug'], 'url')) ?>">Baca selengkapnya</a>
                  </div>
                </div>
                <figure class="relative flex h-56 overflow-hidden rounded-2xl border border-primary-content/20 bg-primary/20 lg:h-full">
                  <?php if ($slide['thumbnail']): ?>
                    <img class="h-full w-full object-cover object-center" src="<?= esc($slide['thumbnail']) ?>" alt="<?= esc($slide['title']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="flex h-full w-full items-center justify-center text-sm text-primary-content/70" role="img" aria-label="Thumbnail belum tersedia">
                      Thumbnail belum tersedia
                    </div>
                  <?php endif; ?>
                </figure>
              </article>
            <?php endforeach; ?>
          </div>
          <div class="hero-slider-controls pointer-events-none absolute inset-y-0 left-0 right-0 flex items-center justify-between px-4 lg:px-6">
            <button class="hero-slide-btn prev pointer-events-auto" type="button" data-carousel-prev aria-label="Berita sebelumnya">
              <span aria-hidden="true">&larr;</span>
            </button>
            <button class="hero-slide-btn next pointer-events-auto" type="button" data-carousel-next aria-label="Berita selanjutnya">
              <span aria-hidden="true">&rarr;</span>
            </button>
          </div>
          <div class="hero-slider-dots absolute bottom-6 left-1/2 flex -translate-x-1/2 gap-2" role="tablist" aria-label="Pilih slide berita">
            <?php foreach ($hero['slides'] as $index => $slide): ?>
              <button
                type="button"
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
        <?php
          $serviceCount  = is_iterable($services ?? null) ? count($services) : 0;
          $documentCount = is_iterable($documents ?? null) ? count($documents) : 0;
          $newsCount     = (is_iterable($otherNews ?? null) ? count($otherNews) : 0) + (! empty($featuredNews) ? 1 : 0);
        ?>
        <div class="hero min-h-[420px] rounded-3xl bg-gradient-to-br from-sky-900 via-sky-800 to-sky-900 text-white shadow-2xl">
          <div class="hero-content flex-col items-start gap-12 px-6 py-14 lg:flex-row-reverse lg:px-16">
            <div class="flex w-full max-w-sm flex-col gap-4">
              <div class="rounded-3xl bg-white/10 p-6 shadow-lg backdrop-blur">
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-sky-100">Pelayanan Publik</p>
                <p class="mt-2 text-2xl font-semibold leading-snug text-white"><?= esc($hero['fallback']['title']) ?></p>
                <p class="mt-3 text-sm leading-relaxed text-sky-100/80"><?= esc($hero['fallback']['description']) ?></p>
                <div class="mt-6 flex flex-wrap gap-3">
                  <a class="btn btn-primary btn-sm normal-case" href="<?= esc($hero['fallback']['ctaServices']) ?>">Lihat layanan</a>
                  <a class="btn btn-outline btn-sm border-white text-white hover:bg-white/20 normal-case" href="<?= esc($hero['fallback']['ctaContact']) ?>">Hubungi kami</a>
                </div>
              </div>
            </div>
            <div class="flex w-full flex-1 flex-col gap-4">
              <div class="stats stats-vertical lg:stats-horizontal shadow-lg bg-white/10 text-sky-100">
                <div class="stat">
                  <div class="stat-title text-white/70">Layanan aktif</div>
                  <div class="stat-value text-white"><?= esc(number_format($serviceCount)) ?></div>
                  <div class="stat-desc text-white/60">Informasi persyaratan tersedia</div>
                </div>
                <div class="stat">
                  <div class="stat-title text-white/70">Dokumen publik</div>
                  <div class="stat-value text-white"><?= esc(number_format($documentCount)) ?></div>
                  <div class="stat-desc text-white/60">Regulasi & laporan terbaru</div>
                </div>
                <div class="stat">
                  <div class="stat-title text-white/70">Berita terkini</div>
                  <div class="stat-value text-white"><?= esc(number_format($newsCount)) ?></div>
                  <div class="stat-desc text-white/60">Update kegiatan dan layanan</div>
                </div>
              </div>
              <div class="rounded-3xl bg-white/10 p-6 shadow-lg backdrop-blur">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-sky-100">Jam layanan</p>
                <p class="mt-2 text-base leading-relaxed text-sky-50">Senin - Jumat, 08.00 - 15.00 WITA · Pengaduan 24/7 via kanal digital.</p>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section id="profil-singkat" aria-labelledby="profil-heading" class="bg-white py-20">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
      <div class="rounded-3xl border border-slate-200 bg-white p-10 shadow-sm">
        <div class="grid gap-10 lg:grid-cols-[1.6fr,1fr] lg:items-start">
          <div class="space-y-6">
            <?= view('components/public/section_header', [
              'id'          => 'profil-heading',
              'eyebrow'     => 'Profil Instansi',
              'title'       => $profileSummary['name'],
              'description' => $profileSummary['description'] ?? '',
              'align'       => 'left',
              'maxWidth'    => '3xl',
            ]) ?>
            <dl class="grid gap-6 text-sm text-slate-700 sm:grid-cols-2">
              <?php if ($profileSummary['address']): ?>
                <div>
                  <dt class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Alamat</dt>
                  <dd class="mt-2 whitespace-pre-line leading-relaxed"><?= esc($profileSummary['address']) ?></dd>
                </div>
              <?php endif; ?>
              <?php if ($profileSummary['phone']): ?>
                <div>
                  <dt class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Telepon</dt>
                  <dd class="mt-2">
                    <a class="font-semibold text-slate-900 hover:text-sky-700" href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $profileSummary['phone'])) ?>"><?= esc($profileSummary['phone']) ?></a>
                  </dd>
                </div>
              <?php endif; ?>
              <?php if ($profileSummary['email']): ?>
                <div>
                  <dt class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Email</dt>
                  <dd class="mt-2">
                    <a class="font-semibold text-slate-900 hover:text-sky-700" href="mailto:<?= esc($profileSummary['email']) ?>"><?= esc($profileSummary['email']) ?></a>
                  </dd>
                </div>
              <?php endif; ?>
            </dl>
          </div>
          <aside class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-inner">
            <h3 class="text-base font-semibold text-slate-900">Portal Informasi</h3>
            <p class="text-sm leading-relaxed text-slate-700">Akses ringkasan layanan, standar pelayanan, dan kanal pengaduan masyarakat dalam satu tempat.</p>
            <div class="grid gap-3 text-sm">
              <a class="flex items-center justify-between rounded-2xl border border-sky-200 bg-white px-4 py-3 font-semibold text-sky-900 transition hover:-translate-y-0.5 hover:border-sky-500 hover:shadow" href="<?= site_url('layanan') ?>">
                <span>Layanan Publik</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                </svg>
              </a>
              <a class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-800 transition hover:-translate-y-0.5 hover:border-slate-400 hover:shadow" href="<?= site_url('dokumen') ?>">
                <span>Dokumen Resmi</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                </svg>
              </a>
              <a class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-800 transition hover:-translate-y-0.5 hover:border-slate-400 hover:shadow" href="<?= site_url('kontak') ?>">
                <span>Unit Pengaduan</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                </svg>
              </a>
            </div>
          </aside>
        </div>
      </div>
    </div>
  </section>

  <section id="layanan" aria-labelledby="layanan-heading" class="bg-slate-100 py-20">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
      <?= view('components/public/section_header', [
        'id'          => 'layanan-heading',
        'eyebrow'     => 'Layanan Prioritas',
        'title'       => 'Kemudahan akses layanan utama masyarakat',
        'description' => 'Semua layanan dilengkapi persyaratan, estimasi waktu, dan kanal pengajuan untuk memastikan transparansi.',
        'align'       => 'center',
        'maxWidth'    => '3xl',
      ]) ?>
      <div class="mt-12">
        <?php if ($services): ?>
          <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($services as $service): ?>
              <article class="group flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-sky-500 hover:shadow-lg" role="listitem">
                <div class="flex items-center gap-3">
                  <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sm font-semibold text-sky-800"><?= esc($service['initial']) ?></span>
                  <h3 class="text-base font-semibold text-slate-900">
                    <a class="hover:text-sky-700 focus-visible:outline-none focus-visible:ring focus-visible:ring-sky-400" href="<?= esc($service['target']) ?>"><?= esc($service['title']) ?></a>
                  </h3>
                </div>
                <?php if ($service['summary']): ?>
                  <p class="mt-4 flex-1 text-sm leading-relaxed text-slate-700"><?= esc($service['summary']) ?></p>
                <?php endif; ?>
                <a class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-sky-700 hover:text-sky-900" href="<?= esc($service['target']) ?>">
                  Lihat detail
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                  </svg>
                </a>
              </article>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-center text-sm text-slate-600">Data layanan belum tersedia. Perbarui melalui panel admin.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section id="berita" aria-labelledby="berita-heading" class="bg-white py-20">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
          <?= view('components/public/section_header', [
            'id'          => 'berita-heading',
            'eyebrow'     => 'Berita',
            'title'       => 'Informasi pelaksanaan tugas dan pelayanan publik',
            'description' => 'Ikuti perkembangan kebijakan, agenda, dan inovasi layanan terkini dari instansi kami.',
            'align'       => 'left',
            'maxWidth'    => '3xl',
          ]) ?>
        </div>
        <a class="btn btn-outline btn-primary btn-sm" href="<?= site_url('berita') ?>">Arsip berita</a>
      </div>
      <div class="mt-10 grid gap-8 lg:grid-cols-[1.5fr,1fr]">
        <?php if ($featuredNews): ?>
          <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
            <div class="grid gap-6 lg:grid-cols-[1.1fr,1fr] lg:items-center">
              <?php if ($featuredNews['thumbnail']): ?>
                <img class="h-56 w-full rounded-2xl object-cover shadow" src="<?= esc($featuredNews['thumbnail']) ?>" alt="<?= esc($featuredNews['title']) ?>" loading="lazy">
              <?php endif; ?>
              <div class="space-y-4">
                <?php if ($featuredNews['published']): ?>
                  <span class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-700"><?= esc($featuredNews['published']) ?></span>
                <?php endif; ?>
                <h3 class="text-xl font-semibold text-slate-900">
                  <a class="hover:text-sky-700 focus-visible:outline-none focus-visible:ring focus-visible:ring-sky-400/60" href="<?= site_url('berita/' . esc($featuredNews['slug'], 'url')) ?>"><?= esc($featuredNews['title']) ?></a>
                </h3>
                <?php if ($featuredNews['excerpt']): ?>
                  <p class="text-sm leading-relaxed text-slate-700"><?= esc($featuredNews['excerpt']) ?></p>
                <?php endif; ?>
                <a class="btn btn-primary btn-sm normal-case" href="<?= site_url('berita/' . esc($featuredNews['slug'], 'url')) ?>">Baca selengkapnya</a>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <div class="space-y-4">
          <?php foreach ($otherNews as $news): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg" role="listitem">
              <?php if ($news['published']): ?>
                <span class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500"><?= esc($news['published']) ?></span>
              <?php endif; ?>
              <h3 class="mt-2 text-lg font-semibold text-slate-900">
                <a class="hover:text-sky-700 focus-visible:outline-none focus-visible:ring focus-visible:ring-sky-400/60" href="<?= site_url('berita/' . esc($news['slug'], 'url')) ?>"><?= esc($news['title']) ?></a>
              </h3>
              <?php if ($news['excerpt']): ?>
                <p class="mt-2 text-sm leading-relaxed text-slate-700"><?= esc($news['excerpt']) ?></p>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
          <?php if (! $otherNews && ! $featuredNews): ?>
            <p class="text-sm text-slate-600">Belum ada berita yang dipublikasikan.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section id="galeri" aria-labelledby="galeri-heading" class="bg-slate-100 py-20">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
      <?= view('components/public/section_header', [
        'id'       => 'galeri-heading',
        'eyebrow'  => 'Galeri kegiatan',
        'title'    => 'Dokumentasi pelayanan dan aktivitas lapangan',
        'align'    => 'center',
        'maxWidth' => '3xl',
      ]) ?>
      <div class="mt-12">
        <?php if ($galleries): ?>
          <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($galleries as $gallery): ?>
              <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg" role="listitem">
                <?php if ($gallery['image']): ?>
                  <img class="h-56 w-full object-cover" src="<?= esc($gallery['image']) ?>" alt="<?= esc($gallery['title']) ?>" loading="lazy">
                <?php endif; ?>
                <div class="space-y-2 p-5">
                  <h3 class="text-base font-semibold text-slate-900"><?= esc($gallery['title']) ?></h3>
                  <?php if ($gallery['description']): ?>
                    <p class="text-sm leading-relaxed text-slate-700"><?= esc($gallery['description']) ?></p>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-center text-sm text-slate-600">Belum ada dokumentasi yang ditampilkan.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section id="dokumen" aria-labelledby="dokumen-heading" class="bg-white py-20">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
      <?= view('components/public/section_header', [
        'id'       => 'dokumen-heading',
        'eyebrow'  => 'Dokumen publik',
        'title'    => 'Regulasi, laporan kinerja, dan informasi keterbukaan',
        'align'    => 'center',
        'maxWidth' => '3xl',
      ]) ?>
      <div class="mt-10">
        <?php if ($documents): ?>
          <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="hidden items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-3 text-xs font-semibold uppercase tracking-[0.35em] text-slate-500 md:flex">
              <span>Judul</span>
              <span>Kategori / Tahun</span>
            </div>
            <div class="divide-y divide-slate-200">
              <?php foreach ($documents as $document): ?>
                <article class="flex flex-col gap-4 px-6 py-5 md:flex-row md:items-center md:justify-between" role="listitem">
                  <div>
                    <h3 class="text-base font-semibold text-slate-900"><?= esc($document['title']) ?></h3>
                    <p class="mt-1 text-sm text-slate-600 md:hidden">
                      <?php if ($document['category']): ?>
                        <span><?= esc($document['category']) ?></span>
                      <?php endif; ?>
                      <?php if ($document['category'] && $document['year']): ?>
                        <span class="mx-2 text-slate-400">&bull;</span>
                      <?php endif; ?>
                      <?php if ($document['year']): ?>
                        <span><?= esc($document['year']) ?></span>
                      <?php endif; ?>
                    </p>
                  </div>
                  <div class="flex items-center justify-between gap-6 md:w-72">
                    <p class="hidden text-sm text-slate-600 md:block">
                      <?php if ($document['category']): ?>
                        <span><?= esc($document['category']) ?></span>
                      <?php endif; ?>
                      <?php if ($document['category'] && $document['year']): ?>
                        <span class="mx-2 text-slate-400">&bull;</span>
                      <?php endif; ?>
                      <?php if ($document['year']): ?>
                        <span><?= esc($document['year']) ?></span>
                      <?php endif; ?>
                    </p>
                    <a class="btn btn-outline btn-primary btn-sm px-4" href="<?= esc($document['url']) ?>" target="_blank" rel="noopener">Unduh</a>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        <?php else: ?>
          <p class="text-center text-sm text-slate-600">Belum ada dokumen yang dapat diunduh.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section id="kontak" aria-labelledby="kontak-heading" class="bg-slate-100 py-20">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
      <?= view('components/public/section_header', [
        'id'          => 'kontak-heading',
        'eyebrow'     => 'Kontak cepat',
        'title'       => 'Hubungi kami',
        'description' => 'Gunakan kanal berikut untuk memperoleh respons tercepat dari tim kami.',
        'align'       => 'center',
        'maxWidth'    => '3xl',
      ]) ?>
      <div class="mt-12">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
          <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" role="list">
            <?php foreach ($contactQuickLinks as $link): ?>
              <li role="listitem">
                <a class="flex h-full flex-col justify-between rounded-2xl border border-slate-200 px-5 py-4 text-left transition hover:-translate-y-1 hover:border-sky-500 hover:shadow-lg focus-visible:outline-none focus-visible:ring focus-visible:ring-sky-400/60"
                   href="<?= esc($link['href']) ?>"<?= $link['href'] !== '#' ? ' target="_blank" rel="noopener"' : '' ?>>
                  <span class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-700"><?= esc($link['label']) ?></span>
                  <span class="mt-3 text-lg font-semibold text-slate-900"><?= esc($link['value']) ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="mt-10 flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-slate-600">Pelayanan tatap muka mengikuti jam operasional. Untuk pengaduan di luar jam kerja gunakan formulir digital.</p>
            <a class="btn btn-primary btn-sm px-6" href="<?= site_url('kontak') ?>">Form pengaduan masyarakat</a>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<?= $this->endSection() ?>

