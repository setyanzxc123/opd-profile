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
  $profileNameLine2 = trim((string) ($profileData['name_line2'] ?? ''));
  $profileTagline = trim((string) ($profileData['tagline_public'] ?? ($profileData['tagline'] ?? '')));
  $logoPublicPath = trim((string) ($profileData['logo_public_path'] ?? ''));
  $logoPublicUrl = $logoPublicPath !== '' ? base_url($logoPublicPath) : null;
  $brandLabel = $profileName !== '' ? $profileName : 'OPD Pemerintah';
  $hideBrandText = ($profileData['hide_brand_text'] ?? '0') == '1';
  
  // Gunakan field name_line2 dari database (user-controlled)
  $brandParts = [
    'main' => $brandLabel,
    'region' => $profileNameLine2
  ];

  $navItems = [
    ['label' => 'Beranda', 'href' => site_url('/'), 'active' => $isHome],
    [
      'label' => 'Profil', 
      'href' => site_url('profil'), 
      'active' => strpos($path, 'profil') === 0 || strpos($path, 'struktur-organisasi') === 0,
      'dropdown' => [
        ['label' => 'Profil OPD', 'href' => site_url('profil')],
        ['label' => 'Sambutan', 'href' => site_url('profil/sambutan')],
        ['label' => 'Visi & Misi', 'href' => site_url('profil/visi-misi')],
        ['label' => 'Tugas & Fungsi', 'href' => site_url('profil/tugas-fungsi')],
        ['label' => 'Struktur Organisasi', 'href' => site_url('struktur-organisasi')],
      ]
    ],
    [
      'label' => 'PPID', 
      'href' => site_url('ppid'), 
      'active' => strpos($path, 'ppid') === 0,
      'dropdown' => [
        ['label' => 'Tentang PPID', 'href' => site_url('ppid/tentang')],
        ['label' => 'Visi & Misi PPID', 'href' => site_url('ppid/visi-misi')],
        ['label' => 'Tugas & Fungsi PPID', 'href' => site_url('ppid/tugas-fungsi')],
      ]
    ],
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
        <?php if (!$hideBrandText): ?>
          <span class="public-navbar-brand-copy">
            <span class="public-navbar-brand-name">
              <span class="public-navbar-brand-name-main"><?= esc($brandParts['main']) ?></span>
              <?php if ($brandParts['region'] !== ''): ?>
                <span class="public-navbar-brand-name-region"><?= esc($brandParts['region']) ?></span>
              <?php endif; ?>
            </span>
            <?php if ($profileTagline !== ''): ?>
              <span class="public-navbar-brand-tagline"><?= esc($profileTagline) ?></span>
            <?php endif; ?>
          </span>
        <?php endif; ?>
      </a>
      <div class="public-navbar-meta public-navbar-top-meta d-none d-lg-flex ms-auto align-items-center gap-3">
        <?php if (!empty($profileData['social_facebook']) && ($profileData['social_facebook_active'] ?? '1') == '1'): ?>
          <a href="<?= esc($profileData['social_facebook']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none fs-4" style="color: #1877f2;" title="Facebook"><i class="bx bxl-facebook-circle"></i></a>
        <?php endif; ?>
        <?php if (!empty($profileData['social_instagram']) && ($profileData['social_instagram_active'] ?? '1') == '1'): ?>
          <a href="<?= esc($profileData['social_instagram']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none fs-4" style="color: #e4405f;" title="Instagram"><i class="bx bxl-instagram"></i></a>
        <?php endif; ?>
        <?php if (!empty($profileData['social_twitter']) && ($profileData['social_twitter_active'] ?? '1') == '1'): ?>
          <a href="<?= esc($profileData['social_twitter']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none fs-4" style="color: #1da1f2;" title="Twitter / X"><i class="bx bxl-twitter"></i></a>
        <?php endif; ?>
        <?php if (!empty($profileData['social_youtube']) && ($profileData['social_youtube_active'] ?? '1') == '1'): ?>
          <a href="<?= esc($profileData['social_youtube']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none fs-4" style="color: #ff0000;" title="YouTube"><i class="bx bxl-youtube"></i></a>
        <?php endif; ?>
        <?php if (!empty($profileData['social_tiktok']) && ($profileData['social_tiktok_active'] ?? '1') == '1'): ?>
          <a href="<?= esc($profileData['social_tiktok']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none fs-4" style="color: #000000;" title="TikTok"><i class="bx bxl-tiktok"></i></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="public-navbar-bottom">
    <div class="container">
      <div class="public-navbar-bottom-inner d-flex align-items-center">
        <button class="navbar-toggler d-lg-none me-2" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar" aria-controls="publicNavbar" aria-expanded="false" aria-label="Tampilkan navigasi">
          <span class="navbar-toggler-icon"></span>
        </button>
        <a class="public-navbar-brand public-navbar-brand--compact text-decoration-none me-auto me-lg-3" href="<?= base_url('/') ?>">
          <?php if ($logoPublicUrl): ?>
            <img src="<?= esc($logoPublicUrl) ?>" alt="<?= esc($profileName !== '' ? $profileName : 'Logo OPD') ?>" class="navbar-brand-logo flex-shrink-0">
          <?php else: ?>
            <span class="me-3 rounded-circle d-inline-flex align-items-center justify-content-center brand-circle text-white fs-6 flex-shrink-0" aria-hidden="true"></span>
          <?php endif; ?>
        </a>
        <div class="collapse navbar-collapse" id="publicNavbar">
          <ul class="public-navbar-links navbar-nav flex-column flex-lg-row align-items-start align-items-lg-center mb-0">
            <?php foreach ($navItems as $item): ?>
              <?php if (isset($item['dropdown'])): ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle<?= $item['active'] ? ' active' : '' ?>" href="<?= esc($item['href']) ?>" id="navbarDropdown<?= esc($item['label']) ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false"<?= $item['active'] ? ' aria-current="page"' : '' ?>>
                    <?= esc($item['label']) ?> <i class="bx bx-chevron-down dropdown-arrow"></i>
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="navbarDropdown<?= esc($item['label']) ?>">
                    <?php foreach ($item['dropdown'] as $subItem): ?>
                      <li><a class="dropdown-item" href="<?= esc($subItem['href']) ?>"><?= esc($subItem['label']) ?></a></li>
                    <?php endforeach; ?>
                  </ul>
                </li>
              <?php else: ?>
                <li class="nav-item">
                  <a class="nav-link<?= $item['active'] ? ' active' : '' ?>" href="<?= esc($item['href']) ?>"<?= $item['active'] ? ' aria-current="page"' : '' ?>><?= esc($item['label']) ?></a>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
          <div class="public-navbar-meta public-navbar-meta--mobile d-lg-none d-flex align-items-center gap-3 mt-3">
            <?php if (!empty($profileData['social_facebook']) && ($profileData['social_facebook_active'] ?? '1') == '1'): ?>
              <a href="<?= esc($profileData['social_facebook']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-muted fs-4" title="Facebook"><i class="bx bxl-facebook-circle"></i></a>
            <?php endif; ?>
            <?php if (!empty($profileData['social_instagram']) && ($profileData['social_instagram_active'] ?? '1') == '1'): ?>
              <a href="<?= esc($profileData['social_instagram']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-muted fs-4" title="Instagram"><i class="bx bxl-instagram"></i></a>
            <?php endif; ?>
            <?php if (!empty($profileData['social_twitter']) && ($profileData['social_twitter_active'] ?? '1') == '1'): ?>
              <a href="<?= esc($profileData['social_twitter']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-muted fs-4" title="Twitter / X"><i class="bx bxl-twitter"></i></a>
            <?php endif; ?>
            <?php if (!empty($profileData['social_youtube']) && ($profileData['social_youtube_active'] ?? '1') == '1'): ?>
              <a href="<?= esc($profileData['social_youtube']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-muted fs-4" title="YouTube"><i class="bx bxl-youtube"></i></a>
            <?php endif; ?>
            <?php if (!empty($profileData['social_tiktok']) && ($profileData['social_tiktok_active'] ?? '1') == '1'): ?>
              <a href="<?= esc($profileData['social_tiktok']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-muted fs-4" title="TikTok"><i class="bx bxl-tiktok"></i></a>
            <?php endif; ?>
          </div>
        </div>
        <button type="button" class="public-search-trigger" aria-label="Buka pencarian" data-search-trigger>
          <i class="bx bx-search"></i>
        </button>
      </div>
    </div>
  </div>
</nav>

<!-- Full-screen Search Overlay -->
<div class="search-overlay" id="searchOverlay" role="dialog" aria-modal="true" aria-labelledby="searchOverlayTitle" hidden data-search-overlay>
  <div class="search-overlay__backdrop" data-search-close></div>
  <div class="search-overlay__container">
    <div class="search-overlay__header">
      <h2 class="visually-hidden" id="searchOverlayTitle">Pencarian Global</h2>
      <button type="button" class="search-overlay__close" aria-label="Tutup pencarian" data-search-close>
        <i class="bx bx-x"></i>
      </button>
    </div>
    <div class="search-overlay__body">
      <form class="search-overlay__form" action="<?= site_url('berita') ?>" method="get" role="search" data-search-form data-search-url="<?= site_url('search') ?>">
        <div class="search-overlay__input-wrap">
          <i class="bx bx-search search-overlay__input-icon"></i>
          <input 
            type="search" 
            class="search-overlay__input" 
            name="q" 
            placeholder="Cari berita, layanan, dokumen..." 
            aria-label="Cari informasi"
            autocomplete="off"
            autofocus
            data-search-input
          >
          <span class="search-overlay__hint">Tekan ESC untuk menutup</span>
        </div>
      </form>
      <div class="search-overlay__results" data-search-results>
        <div class="search-overlay__empty" data-search-empty>
          <i class="bx bx-search-alt-2"></i>
          <p>Ketik untuk mulai mencari</p>
        </div>
      </div>
    </div>
  </div>
</div>
