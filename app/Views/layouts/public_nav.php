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
  $profileTagline = trim((string) ($profileData['tagline_public'] ?? ($profileData['tagline'] ?? '')));
  $logoPublicPath = trim((string) ($profileData['logo_public_path'] ?? ''));
  $logoPublicUrl = $logoPublicPath !== '' ? base_url($logoPublicPath) : null;
  $brandLabel = $profileName !== '' ? $profileName : 'OPD Pemerintah';
  $metaLinks = [
    ['label' => 'Press Room', 'href' => site_url('berita')],
  ];
  $languageLabel = 'English';

  $navItems = [
    ['label' => 'Beranda', 'href' => site_url('/') . '#beranda', 'active' => $isHome],
    ['label' => 'Profil', 'href' => site_url('profil'), 'active' => strpos($path, 'profil') === 0],
    ['label' => 'Layanan', 'href' => site_url('layanan'), 'active' => strpos($path, 'layanan') === 0],
    ['label' => 'Berita', 'href' => site_url('berita'), 'active' => strpos($path, 'berita') === 0],
    ['label' => 'Galeri', 'href' => site_url('galeri'), 'active' => strpos($path, 'galeri') === 0],
    ['label' => 'Dokumen', 'href' => site_url('dokumen'), 'active' => strpos($path, 'dokumen') === 0],
    ['label' => 'Kontak', 'href' => site_url('kontak'), 'active' => strpos($path, 'kontak') === 0],
  ];
?>
<nav class="public-navbar navbar navbar-expand-lg sticky-top shadow-sm" aria-label="Navigasi utama">
  <div class="public-navbar-top">
    <div class="container d-flex align-items-center">
      <a class="public-navbar-brand d-flex align-items-center text-decoration-none" href="<?= base_url('/') ?>">
        <?php if ($logoPublicUrl): ?>
          <img src="<?= esc($logoPublicUrl) ?>" alt="<?= esc($profileName !== '' ? $profileName : 'Logo OPD') ?>" class="navbar-brand-logo flex-shrink-0">
        <?php else: ?>
          <span class="me-3 rounded-circle d-inline-flex align-items-center justify-content-center brand-circle text-white fs-6 flex-shrink-0" aria-hidden="true"></span>
        <?php endif; ?>
        <span class="public-navbar-brand-copy">
          <span class="public-navbar-brand-name"><?= esc($brandLabel) ?></span>
          <?php if ($profileTagline !== ''): ?>
            <span class="public-navbar-brand-tagline"><?= esc($profileTagline) ?></span>
          <?php endif; ?>
        </span>
      </a>
      <div class="public-navbar-meta public-navbar-top-meta d-none d-lg-flex ms-auto">
        <?php foreach ($metaLinks as $metaLink): ?>
          <a class="public-navbar-meta-link" href="<?= esc($metaLink['href']) ?>"><?= esc($metaLink['label']) ?></a>
        <?php endforeach; ?>
        <span class="public-navbar-meta-divider" aria-hidden="true"></span>
        <button class="public-navbar-language" type="button" aria-label="Ganti bahasa">
          <?= esc($languageLabel) ?>
          <span class="public-navbar-language-icon" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </div>
  <div class="public-navbar-bottom">
    <div class="container">
      <div class="public-navbar-bottom-inner d-flex align-items-center">
        <a class="public-navbar-brand public-navbar-brand--compact text-decoration-none me-3" href="<?= base_url('/') ?>">
          <?php if ($logoPublicUrl): ?>
            <img src="<?= esc($logoPublicUrl) ?>" alt="<?= esc($profileName !== '' ? $profileName : 'Logo OPD') ?>" class="navbar-brand-logo flex-shrink-0">
          <?php else: ?>
            <span class="me-3 rounded-circle d-inline-flex align-items-center justify-content-center brand-circle text-white fs-6 flex-shrink-0" aria-hidden="true"></span>
          <?php endif; ?>
        </a>
        <button class="navbar-toggler d-lg-none ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar" aria-controls="publicNavbar" aria-expanded="false" aria-label="Tampilkan navigasi">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="publicNavbar">
          <ul class="public-navbar-links navbar-nav flex-column flex-lg-row align-items-start align-items-lg-center mb-0">
            <?php foreach ($navItems as $item): ?>
              <li class="nav-item">
                <a class="nav-link<?= $item['active'] ? ' active' : '' ?>" href="<?= esc($item['href']) ?>"<?= $item['active'] ? ' aria-current="page"' : '' ?>><?= esc($item['label']) ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="public-navbar-meta public-navbar-meta--mobile d-lg-none">
            <?php foreach ($metaLinks as $metaLink): ?>
              <a class="public-navbar-meta-link" href="<?= esc($metaLink['href']) ?>"><?= esc($metaLink['label']) ?></a>
            <?php endforeach; ?>
            <button class="public-navbar-language" type="button" aria-label="Ganti bahasa">
              <?= esc($languageLabel) ?>
              <span class="public-navbar-language-icon" aria-hidden="true"></span>
            </button>
          </div>
        </div>
        <form class="public-search public-navbar-search" action="<?= site_url('berita') ?>" method="get" role="search" data-nav-search-form data-nav-search-url="<?= site_url('search/berita') ?>">
          <div class="public-search-field">
            <label class="visually-hidden" for="navSearch">Cari informasi</label>
            <input id="navSearch" class="public-search-input" type="search" name="q" placeholder="Cari berita" aria-label="Cari berita" value="<?= esc($path === 'berita' ? ($request->getGet('q') ?? '') : '') ?>" autocomplete="off" data-nav-search-input aria-autocomplete="list" aria-controls="navSearchResults">
            <span class="public-search-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" focusable="false" role="img">
                <path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.7.7l.27.28v.79l5 5L20.49 19l-5-5zm-6 0a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9z" fill="currentColor"/>
              </svg>
            </span>
          </div>
          <div class="public-search-results" id="navSearchResults" role="listbox" aria-label="Hasil pencarian berita" aria-live="polite" aria-relevant="additions" hidden data-nav-search-results></div>
        </form>
      </div>
    </div>
  </div>
</nav>
