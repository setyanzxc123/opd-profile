<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Admin') ?></title>
  <link href="<?= base_url('assets/vendors/bootstrap/dist/css/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/vendors/font-awesome/css/font-awesome.min.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/vendors/nprogress/nprogress.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/build/css/custom.min.css') ?>" rel="stylesheet">
</head>
<body class="nav-md">
<div class="container body"><div class="main_container">

  <div class="col-md-3 left_col"><div class="left_col scroll-view">
    <div class="navbar nav_title" style="border:0;">
      <a href="<?= site_url('admin') ?>" class="site_title"><i class="fa fa-institution"></i> <span>Admin OPD</span></a>
    </div>
    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
      <div class="menu_section">
        <h3>Menu</h3>
        <ul class="nav side-menu">
          <li><a href="<?= site_url('admin') ?>"><i class="fa fa-home"></i> Dashboard</a></li>
          <li><a href="<?= site_url('admin/profile') ?>"><i class="fa fa-building"></i> Profil OPD</a></li>

          <li><a><i class="fa fa-cogs"></i> Layanan <span class="fa fa-chevron-down"></span></a>
            <ul class="nav child_menu">
              <li><a href="<?= site_url('admin/services') ?>">Daftar Layanan</a></li>
            </ul>
          </li>

          <li><a><i class="fa fa-newspaper-o"></i> Berita <span class="fa fa-chevron-down"></span></a>
            <ul class="nav child_menu">
              <li><a href="<?= site_url('admin/news') ?>">Daftar Berita</a></li>
            </ul>
          </li>

          <li><a href="<?= site_url('admin/galleries') ?>"><i class="fa fa-image"></i> Galeri</a></li>
          <li><a href="<?= site_url('admin/documents') ?>"><i class="fa fa-file-text-o"></i> Dokumen</a></li>
          <li><a href="<?= site_url('admin/contacts') ?>"><i class="fa fa-envelope"></i> Pesan Kontak</a></li>

          <li><a href="<?= site_url('admin/users') ?>"><i class="fa fa-users"></i> Pengguna</a></li>
          <li><a href="<?= site_url('admin/logs') ?>"><i class="fa fa-history"></i> Log Aktivitas</a></li>
        </ul>
      </div>
    </div>
  </div></div>

  <div class="top_nav"><div class="nav_menu"><nav>
    <ul class="nav navbar-nav navbar-right">
      <li><a href="<?= site_url('logout') ?>"><i class="fa fa-sign-out"></i> Keluar</a></li>
    </ul>
  </nav></div></div>

  <div class="right_col" role="main">
    <?= $this->renderSection('content') ?>
  </div>

  <footer><div class="pull-right">OPD Admin</div><div class="clearfix"></div></footer>

</div></div>
<script src="<?= base_url('assets/vendors/jquery/dist/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/vendors/bootstrap/dist/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/vendors/fastclick/lib/fastclick.js') ?>"></script>
<script src="<?= base_url('assets/vendors/nprogress/nprogress.js') ?>"></script>
<script src="<?= base_url('assets/build/js/custom.min.js') ?>"></script>
</body></html>
