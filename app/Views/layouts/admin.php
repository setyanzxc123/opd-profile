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
  <?= $this->renderSection('pageStyles') ?>
 </head>
<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Menu -->
      <aside id="layout-menu" class="layout-menu menu menu-vertical bg-menu-theme">
        <div class="app-brand demo d-flex align-items-center justify-content-between">
          <a href="<?= site_url('admin') ?>" class="app-brand-link d-inline-flex align-items-center">
            <span class="app-brand-logo demo"><i class="tf-icons bx bx-building"></i></span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Admin OPD</span>
          </a>
          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large" aria-label="Buka/tutup menu samping">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
          </a>
        </div>
        <div class="menu-inner-shadow"></div>
        <ul class="menu-inner py-1">
          <li class="menu-item"><a href="<?= site_url('admin') ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-home me-2"></i><div>Dasbor</div></a></li>
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
          <li class="menu-header small text-uppercase mt-4"><span class="menu-header-text">Lainnya</span></li>
          <li class="menu-item mt-2">
            <a href="<?= site_url('logout') ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-log-out me-2"></i>
              <div>Keluar</div>
            </a>
          </li>
        </ul>
      </aside>
      <!-- /Menu -->

      <!-- Page -->
      <div class="layout-page">
        

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <?php
              // Build dynamic breadcrumbs from URI segments for Admin pages
              $uri       = service('uri');
              $segments  = $uri->getSegments();
              $labelsMap = [
                'admin'     => 'Beranda',
                'profile'   => 'Profil OPD',
                'news'      => 'Berita',
                'services'  => 'Layanan',
                'galleries' => 'Galeri',
                'documents' => 'Dokumen',
                'contacts'  => 'Pesan Kontak',
                'users'     => 'Pengguna',
                'logs'      => 'Log Aktivitas',
                'create'    => 'Tambah',
                'edit'      => 'Ubah',
              ];

              // Only render when inside admin area
              if (!empty($segments) && $segments[0] === 'admin'):
                $crumbs = [];
                $path   = 'admin';
                // Start with home
                $crumbs[] = [ 'label' => $labelsMap['admin'], 'url' => site_url('admin'), 'active' => false ];

                for ($i = 1; $i < count($segments); $i++) {
                  $seg = $segments[$i];
                  // Skip numeric IDs or empty segments from route
                  if (is_numeric($seg)) continue;
                  $path .= '/' . $seg;
                  $label = $labelsMap[$seg] ?? ucfirst(str_replace('-', ' ', $seg));
                  $isLast = ($i === count($segments) - 1) || (isset($segments[$i+1]) && is_numeric($segments[$i+1]));
                  $crumbs[] = [
                    'label' => $label,
                    'url'   => $isLast ? null : site_url($path),
                    'active'=> $isLast,
                  ];
                }
            ?>
            <nav aria-label="breadcrumb" class="mb-3">
              <ol class="breadcrumb">
                <?php foreach ($crumbs as $c): ?>
                  <?php if (!empty($c['active'])): ?>
                    <li class="breadcrumb-item active" aria-current="page"><?= esc($c['label']) ?></li>
                  <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= esc($c['url'], 'url') ?>"><?= esc($c['label']) ?></a></li>
                  <?php endif; ?>
                <?php endforeach; ?>
              </ol>
            </nav>
            <?php endif; ?>
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
<script src="<?= base_url('assets/js/helpers-override.js') ?>"></script>
<script src="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') ?>"></script>
<script src="<?= base_url('assets/vendor/js/menu.js') ?>"></script>
<script src="<?= base_url('assets/js/main.js') ?>"></script>
<?= $this->renderSection('pageScripts') ?>
</body></html>
