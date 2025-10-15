<?php
  $request = service('request');
  $uri = $request->getUri();
  $path = trim($uri->getPath(), '/');
  $isHome = $path === '';
  $profileData = is_array($profile ?? null) ? $profile : [];

  if ($profileData === []) {
      $cachedProfile = cache('public_profile_latest');
      if (! is_array($cachedProfile)) {
          try {
              $cachedProfile = model(\App\Models\OpdProfileModel::class)
                  ->orderBy('id', 'desc')
                  ->first() ?: [];
          } catch (\Throwable $throwable) {
              log_message('debug', 'Failed to load profile for navbar fallback: {error}', ['error' => $throwable->getMessage()]);
              $cachedProfile = [];
          }
      }
      $profileData = is_array($cachedProfile) ? $cachedProfile : [];
  }

  $profileName = trim((string) ($profileData['name'] ?? ''));
  $logoPublicPath = trim((string) ($profileData['logo_public_path'] ?? ''));
  $logoPublicUrl = $logoPublicPath !== '' ? base_url($logoPublicPath) : null;

  $navItems = [
    ['label' => 'Beranda', 'href' => site_url('/') . '#beranda', 'active' => $isHome],
    ['label' => 'Profil', 'href' => site_url('profil'), 'active' => strpos($path, 'profil') === 0],
    ['label' => 'Layanan', 'href' => site_url('layanan'), 'active' => strpos($path, 'layanan') === 0],
    ['label' => 'Berita', 'href' => site_url('berita'), 'active' => strpos($path, 'berita') === 0],
    ['label' => 'Galeri', 'href' => site_url('galeri'), 'active' => strpos($path, 'galeri') === 0],
    ['label' => 'Dokumen', 'href' => site_url('dokumen'), 'active' => strpos($path, 'dokumen') === 0],
    ['label' => 'Kontak', 'href' => site_url('kontak'), 'active' => strpos($path, 'kontak') === 0],
  ];

  $initial = 'O';
  if ($profileName !== '') {
      if (function_exists('mb_substr')) {
          $char = mb_substr($profileName, 0, 1, 'UTF-8');
          if ($char !== false && $char !== '') {
              $initial = mb_strtoupper($char, 'UTF-8');
          }
      } else {
          $initial = strtoupper(substr($profileName, 0, 1));
      }
  }
?>
<header class="sticky top-0 z-50 shadow-[0_6px_12px_rgba(15,23,42,0.08)]" aria-label="Navigasi utama">
  <div class="border-b border-neutral-200 bg-white">
    <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
      <a class="flex items-center gap-3" href="<?= base_url('/') ?>">
        <?php if ($logoPublicUrl): ?>
          <img src="<?= esc($logoPublicUrl) ?>" alt="<?= esc($profileName !== '' ? $profileName : 'Logo OPD') ?>" class="h-12 w-auto rounded-md object-contain shadow-sm ring-1 ring-neutral-200" loading="lazy">
        <?php else: ?>
          <span class="inline-flex h-12 w-12 items-center justify-center rounded-md bg-sky-900 text-lg font-semibold text-white shadow-sm ring-1 ring-sky-700/70" aria-hidden="true"><?= esc($initial) ?></span>
        <?php endif; ?>
        <span class="flex flex-col text-left leading-tight">
          <span class="text-xs font-semibold uppercase tracking-[0.4em] text-sky-800">Situs Resmi</span>
          <span class="text-base font-semibold text-slate-900"><?= esc($profileName !== '' ? $profileName : 'Organisasi Pemerintah Daerah') ?></span>
        </span>
      </a>

      <div class="hidden items-center gap-4 text-sm font-medium text-sky-800 lg:flex">
        <a class="hover:text-sky-900 focus-visible:outline-none focus-visible:ring focus-visible:ring-sky-400/60" href="<?= site_url('berita') ?>">Rilis Berita</a>
        <span class="h-5 w-px bg-neutral-300"></span>
        <button type="button" class="inline-flex items-center gap-1 hover:text-sky-900 focus-visible:outline-none focus-visible:ring focus-visible:ring-sky-400/60">
          <span>Bahasa Indonesia</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 9l6 6 6-6" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <div class="bg-sky-900 text-sky-50">
    <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
      <button type="button"
              class="inline-flex items-center gap-2 py-3 text-sm font-semibold lg:hidden"
              data-nav-toggle
              aria-controls="mobileNavPanel"
              aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <span>Menu</span>
      </button>

      <nav aria-label="Halaman utama" class="hidden flex-1 lg:block">
        <ul class="flex items-center gap-6 text-sm font-semibold uppercase tracking-widest">
          <?php foreach ($navItems as $item): ?>
            <li>
              <a class="inline-flex items-center border-b-4 border-transparent pb-3 pt-3 transition hover:border-white/80 hover:text-white focus-visible:outline-none focus-visible:ring focus-visible:ring-white/40<?= $item['active'] ? ' border-white text-white' : '' ?>"
                 href="<?= esc($item['href']) ?>"<?= $item['active'] ? ' aria-current="page"' : '' ?>>
                <?= esc($item['label']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <form class="relative hidden items-center rounded-full bg-white/15 px-3 py-2 text-sky-50 transition hover:bg-white/20 lg:flex"
            action="<?= site_url('berita') ?>"
            method="get"
            role="search"
            data-nav-search-form
            data-nav-search-url="<?= site_url('search/berita') ?>">
        <label class="sr-only" for="navSearchDesktop">Cari berita</label>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
        </svg>
        <input id="navSearchDesktop"
               class="w-48 border-none bg-transparent pl-2 text-sm placeholder:text-sky-100/70 focus:outline-none focus:ring-0"
               type="search"
               name="q"
               placeholder="Cari berita"
               aria-label="Cari berita"
               value="<?= esc($path === 'berita' ? ($request->getGet('q') ?? '') : '') ?>"
               autocomplete="off"
               data-nav-search-input
               aria-autocomplete="list"
               aria-controls="navSearchResultsDesktop">
        <div class="nav-search-results absolute left-0 top-full z-50 mt-3 w-full rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"
             id="navSearchResultsDesktop"
             role="listbox"
             aria-label="Hasil pencarian berita"
             hidden
             data-nav-search-results></div>
      </form>
    </div>
  </div>

  <div id="mobileNavPanel" class="border-t border-neutral-200 bg-white lg:hidden" data-nav-panel hidden>
    <div class="mx-auto max-w-6xl px-4 py-4 sm:px-6 lg:px-8">
      <div class="flex flex-col gap-6">
        <form class="relative flex items-center gap-2 rounded-2xl border border-neutral-200 bg-white px-3 py-2 shadow-sm"
              action="<?= site_url('berita') ?>"
              method="get"
              role="search"
              data-nav-search-form
              data-nav-search-url="<?= site_url('search/berita') ?>">
          <label class="sr-only" for="navSearchMobile">Cari berita</label>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 text-sky-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
          </svg>
          <input id="navSearchMobile"
                 class="flex-1 border-none bg-transparent text-sm focus:outline-none focus:ring-0"
                 type="search"
                 name="q"
                 placeholder="Cari berita"
                 aria-label="Cari berita"
                 value="<?= esc($path === 'berita' ? ($request->getGet('q') ?? '') : '') ?>"
                 autocomplete="off"
                 data-nav-search-input
                 aria-autocomplete="list"
                 aria-controls="navSearchResultsMobile">
          <div class="nav-search-results absolute left-0 top-full z-50 mt-2 w-full rounded-2xl border border-neutral-200 bg-white p-2 shadow-xl"
               id="navSearchResultsMobile"
               role="listbox"
               aria-label="Hasil pencarian berita"
               hidden
               data-nav-search-results></div>
        </form>
        <nav aria-label="Navigasi utama (mobile)">
          <ul class="flex flex-col gap-2 text-sm font-semibold text-sky-900">
            <?php foreach ($navItems as $item): ?>
              <li>
                <a class="flex items-center justify-between rounded-2xl border border-neutral-200 px-4 py-3 transition hover:border-sky-600/50 hover:bg-sky-50 focus-visible:outline-none focus-visible:ring focus-visible:ring-sky-500/60<?= $item['active'] ? ' border-sky-600 bg-sky-50 text-sky-700' : '' ?>"
                   href="<?= esc($item['href']) ?>"<?= $item['active'] ? ' aria-current="page"' : '' ?> data-nav-close>
                  <span><?= esc($item['label']) ?></span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                  </svg>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </nav>
        <button type="button" class="btn btn-outline btn-sm self-start" data-nav-close>Tutup</button>
      </div>
    </div>
  </div>
</header>
