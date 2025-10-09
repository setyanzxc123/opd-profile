<?php
  $request = service('request');
  $uri = $request->getUri();
  $path = trim($uri->getPath(), '/');
  $isHome = $path === '';
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
<nav class="navbar navbar-expand-lg navbar-light sticky-top shadow-sm public-navbar" aria-label="Navigasi utama">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= base_url('/') ?>">
      <span class="me-2 rounded-circle d-inline-flex align-items-center justify-content-center brand-circle text-white fs-6"></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar" aria-controls="publicNavbar" aria-expanded="false" aria-label="Tampilkan navigasi">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="publicNavbar">
      <ul class="navbar-nav ms-auto ms-lg-0 me-lg-4 mb-2 mb-lg-0 align-items-lg-center gap-lg-3">
        <?php foreach ($navItems as $item): ?>
          <li class="nav-item">
            <a class="nav-link<?= $item['active'] ? ' active' : '' ?>" href="<?= esc($item['href']) ?>"<?= $item['active'] ? ' aria-current="page"' : '' ?>><?= esc($item['label']) ?></a>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="nav-actions d-flex flex-column flex-lg-row align-items-lg-center gap-3 mt-3 mt-lg-0">
        <form class="public-search" action="<?= site_url('berita') ?>" method="get" role="search" data-nav-search-form data-nav-search-url="<?= site_url('search/berita') ?>">
          <div class="public-search-field">
            <label class="visually-hidden" for="navSearch">Cari informasi</label>
            <input id="navSearch" class="public-search-input" type="search" name="q" placeholder="Cari berita" aria-label="Cari berita" value="<?= esc($path === 'berita' ? ($request->getGet('q') ?? '') : '') ?>" autocomplete="off" data-nav-search-input aria-autocomplete="list" aria-controls="navSearchResults">
          </div>
          <div class="public-search-results" id="navSearchResults" role="listbox" aria-label="Hasil pencarian berita" hidden data-nav-search-results></div>
        </form>
      </div>
    </div>
  </div>
</nav>
