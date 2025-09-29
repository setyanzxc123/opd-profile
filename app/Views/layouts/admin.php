<!DOCTYPE html>
<html
  lang="id"
 
  data-assets-path="<?= base_url('assets/') ?>"
  data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= esc($title ?? 'Admin OPD') ?></title>
  <link rel="icon" type="image/x-icon" href="<?= base_url('assets/img/favicon/favicon.ico') ?>" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600&display=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="<?= base_url('assets/vendor/fonts/iconify-icons.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/vendor/css/core.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/demo.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>" />
  <?= $this->renderSection('pageStyles') ?>

  <script src="<?= base_url('assets/vendor/js/helpers.js') ?>"></script>
  <script src="<?= base_url('assets/js/config.js') ?>"></script>
</head>

<body class="layout-menu-fixed">
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <?php
        $uri          = service('uri');
        $matchedRoute = service('router')->getMatchedRoute();
        $currentRoute = $matchedRoute[0] ?? '';
        $section      = $uri->getSegment(2);
      ?>
      <!-- Menu -->
      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo">
          <a href="<?= site_url('admin') ?>" class="app-brand-link">
            <span class="app-brand-logo demo">
              <span class="text-primary">
                <i class="tf-icons bx bx-building fs-3"></i>
              </span>
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">Admin</span>
          </a>
          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
          </a>
        </div>
        <div class="menu-inner-shadow"></div>
        <ul class="menu-inner py-1">
          <li class="menu-item<?= $currentRoute === 'admin' ? ' active' : '' ?>">
            <a href="<?= site_url('admin') ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-smile"></i>
              <div class="text-truncate">Dasbor</div>
            </a>
          </li>
          <li class="menu-item<?= $section === 'profile' ? ' active' : '' ?>">
            <a href="<?= site_url('admin/profile') ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-buildings"></i>
              <div class="text-truncate">Profil OPD</div>
            </a>
          </li>
          <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Konten</span>
          </li>
          <li class="menu-item<?= $section === 'news' ? ' active' : '' ?>">
            <a href="<?= site_url('admin/news') ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-news"></i>
              <div class="text-truncate">Berita</div>
            </a>
          </li>
          <li class="menu-item<?= $section === 'galleries' ? ' active' : '' ?>">
            <a href="<?= site_url('admin/galleries') ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-image-alt"></i>
              <div class="text-truncate">Galeri</div>
            </a>
          </li>
          <li class="menu-item<?= $section === 'documents' ? ' active' : '' ?>">
            <a href="<?= site_url('admin/documents') ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-file"></i>
              <div class="text-truncate">Dokumen</div>
            </a>
          </li>
          <li class="menu-item<?= $section === 'contacts' ? ' active' : '' ?>">
            <a href="<?= site_url('admin/contacts') ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-envelope"></i>
              <div class="text-truncate">Pesan Kontak</div>
            </a>
          </li>

          <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Pengelolaan</span>
          </li>
          <li class="menu-item<?= $section === 'users' ? ' active' : '' ?>">
            <a href="<?= site_url('admin/users') ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-user"></i>
              <div class="text-truncate">Pengguna</div>
            </a>
          </li>
          <li class="menu-item<?= $section === 'logs' ? ' active' : '' ?>">
            <a href="<?= site_url('admin/logs') ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-history"></i>
              <div class="text-truncate">Log Aktivitas</div>
            </a>
          </li>
          <li class="menu-item mt-auto">
            <a href="<?= site_url('logout') ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-log-out"></i>
              <div class="text-truncate">Keluar</div>
            </a>
          </li>
        </ul>
      </aside>
      <!-- / Menu -->

      <!-- Layout container -->
      <div class="layout-page">

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <div class="layout-menu-toggle navbar-nav align-items-center d-xl-none mb-3">
              <a class="nav-item nav-link px-0" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>
            <?php
              $segments = $uri->getSegments();
              $labelsMap = [
                'admin'     => 'Dasbor',
                'profile'   => 'Profil OPD',
                'news'      => 'Berita',
                'galleries' => 'Galeri',
                'documents' => 'Dokumen',
                'contacts'  => 'Pesan Kontak',
                'users'     => 'Pengguna',
                'logs'      => 'Log Aktivitas',
                'create'    => 'Tambah',
                'edit'      => 'Ubah',
              ];

              if (!empty($segments) && $segments[0] === 'admin'):
                $crumbs = [];
                $path = 'admin';
                $crumbs[] = ['label' => $labelsMap['admin'], 'url' => site_url('admin'), 'active' => count($segments) === 1];

                if (count($segments) > 1) {
                  for ($i = 1; $i < count($segments); $i++) {
                    $seg = $segments[$i];
                    if (is_numeric($seg)) {
                      continue;
                    }

                    $path .= '/' . $seg;
                    $label = $labelsMap[$seg] ?? ucfirst(str_replace('-', ' ', $seg));
                    $isLast = ($i === count($segments) - 1) || (isset($segments[$i + 1]) && is_numeric($segments[$i + 1]));
                    $noLink = in_array($seg, ['create', 'edit', 'update'], true);

                    $crumbs[] = [
                      'label' => $label,
                      'url'   => (!$isLast && !$noLink) ? site_url($path) : null,
                      'active'=> $isLast,
                    ];
                  }
                }
            ?>
            <nav aria-label="breadcrumb" class="mb-4">
              <ol class="breadcrumb">
                <?php foreach ($crumbs as $c): ?>
                  <?php if (!empty($c['active']) || empty($c['url'])): ?>
                    <li class="breadcrumb-item<?= !empty($c['active']) ? ' active' : '' ?>"<?= !empty($c['active']) ? ' aria-current="page"' : '' ?>><?= esc($c['label']) ?></li>
                  <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= esc($c['url'], 'attr') ?>"><?= esc($c['label']) ?></a></li>
                  <?php endif; ?>
                <?php endforeach; ?>
              </ol>
            </nav>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
          </div>

          <footer class="content-footer footer bg-footer-theme">
            <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
              <div class="mb-2 mb-md-0">&copy; <?= date('Y') ?> Dinas</div>
            </div>
          </footer>

          <div class="content-backdrop fade"></div>
        </div>
        <!-- / Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>
    <!-- / Layout container -->

    <div class="layout-overlay layout-menu-toggle"></div>
    <div class="drag-target"></div>
  </div>

  <script src="<?= base_url('assets/vendor/libs/jquery/jquery.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/libs/popper/popper.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/js/bootstrap.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/js/menu.js') ?>"></script>
  <script src="<?= base_url('assets/js/helpers-override.js') ?>"></script>
  <script src="<?= base_url('assets/js/main.js') ?>"></script>
  <?= $this->renderSection('pageScripts') ?>
</body>
</html>
