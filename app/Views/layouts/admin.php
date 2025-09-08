<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Admin') ?></title>
  <link href="<?= base_url('assets/vendor/css/core.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/vendor/fonts/iconify-icons.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/css/demo.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/css/custom.css') ?>" rel="stylesheet">
 </head>
<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Menu -->
      <aside id="layout-menu" class="layout-menu menu menu-vertical bg-menu-theme">
        <div class="app-brand demo">
          <a href="<?= site_url('admin') ?>" class="app-brand-link">
            <span class="app-brand-logo demo"><i class="tf-icons bx bx-building"></i></span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Admin OPD</span>
          </a>
        </div>
        <div class="menu-inner-shadow"></div>
        <ul class="menu-inner py-1">
          <li class="menu-item"><a href="<?= site_url('admin') ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-home me-2"></i><div>Dashboard</div></a></li>
          <li class="menu-item"><a href="<?= site_url('admin/profile') ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-building me-2"></i><div>Profil OPD</div></a></li>
          <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle"><i class="menu-icon tf-icons bx bx-cog me-2"></i><div>Layanan</div></a>
            <ul class="menu-sub">
              <li class="menu-item"><a href="<?= site_url('admin/services') ?>" class="menu-link"><div>Daftar Layanan</div></a></li>
            </ul>
          </li>
          <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle"><i class="menu-icon tf-icons bx bx-news me-2"></i><div>Berita</div></a>
            <ul class="menu-sub">
              <li class="menu-item"><a href="<?= site_url('admin/news') ?>" class="menu-link"><div>Daftar Berita</div></a></li>
            </ul>
          </li>
          <li class="menu-item"><a href="<?= site_url('admin/galleries') ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-image me-2"></i><div>Galeri</div></a></li>
          <li class="menu-item"><a href="<?= site_url('admin/documents') ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-file me-2"></i><div>Dokumen</div></a></li>
          <li class="menu-item"><a href="<?= site_url('admin/contacts') ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-envelope me-2"></i><div>Pesan Kontak</div></a></li>
          <li class="menu-item"><a href="<?= site_url('admin/users') ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-user me-2"></i><div>Pengguna</div></a></li>
          <li class="menu-item"><a href="<?= site_url('admin/logs') ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-history me-2"></i><div>Log Aktivitas</div></a></li>
        </ul>
      </aside>
      <!-- /Menu -->

      <!-- Page -->
      <div class="layout-page">
        <!-- Navbar -->
        <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
          <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <ul class="navbar-nav flex-row align-items-center ms-auto">
              <li class="nav-item me-2">
                <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-secondary"><i class="tf-icons bx bx-log-out me-1"></i>Keluar</a>
              </li>
            </ul>
          </div>
        </nav>
        <!-- /Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <?= $this->renderSection('content') ?>
          </div>
          <footer class="content-footer footer bg-footer-theme">
            <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
              <div class="mb-2 mb-md-0">OPD Admin</div>
            </div>
          </footer>
          <div class="content-backdrop fade"></div>
        </div>
        <!-- /Content wrapper -->
      </div>
      <!-- /Page -->

      <div class="layout-overlay layout-menu-toggle"></div>
      <div class="drag-target"></div>
    </div>
  </div>
<script src="<?= base_url('assets/vendor/libs/jquery/jquery.js') ?>"></script>
<script src="<?= base_url('assets/vendor/libs/popper/popper.js') ?>"></script>
<script src="<?= base_url('assets/vendor/js/bootstrap.js') ?>"></script>
<script src="<?= base_url('assets/vendor/js/helpers.js') ?>"></script>
<script src="<?= base_url('assets/js/config.js') ?>"></script>
<script src="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') ?>"></script>
<script src="<?= base_url('assets/vendor/js/menu.js') ?>"></script>
<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body></html>
