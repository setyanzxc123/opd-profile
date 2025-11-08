<?php
  helper(['url', 'theme']);

  $loginThemeProfile = cache('public_profile_latest');
  if (! is_array($loginThemeProfile)) {
      try {
          $loginThemeProfile = model(\App\Models\OpdProfileModel::class)
              ->orderBy('id', 'desc')
              ->first();
      } catch (\Throwable $throwable) {
          log_message('debug', 'Failed to fetch profile for login theme: {error}', ['error' => $throwable->getMessage()]);
          $loginThemeProfile = [];
      }
  }
  $loginThemeProfile   = is_array($loginThemeProfile) ? $loginThemeProfile : [];
  $loginThemeVariables = $loginThemeProfile !== [] ? theme_admin_variables($loginThemeProfile) : [];
  $profileData = $loginThemeProfile;
  $siteName = trim((string) ($profileData['name'] ?? ''));
  $adminLogoPath = trim((string) ($profileData['logo_admin_path'] ?? ''));
  $publicLogoPath = trim((string) ($profileData['logo_public_path'] ?? ''));
  $logoUrl = $adminLogoPath !== '' ? base_url($adminLogoPath) : ($publicLogoPath !== '' ? base_url($publicLogoPath) : null);
  $displaySiteName = $siteName !== '' ? $siteName : 'Admin OPD';
?>
<!DOCTYPE html>
<html lang="id" data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel Admin</title>
  <link href="<?= base_url('assets/vendor/css/core.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/vendor/css/pages/page-auth.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/vendor/fonts/iconify-icons.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/css/demo.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/css/custom.css') ?>" rel="stylesheet">
  <?php if ($loginThemeVariables !== []): ?>
    <?= theme_render_style($loginThemeVariables, ':root[data-template="vertical-menu-template-free"]') ?>
  <?php endif; ?>
</head>
<body class="login-page">
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <div class="card login-card shadow-lg border-0">
          <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
              <?php if ($logoUrl !== null): ?>
                <img src="<?= $logoUrl ?>" alt="Logo <?= esc($displaySiteName) ?>" class="login-logo mb-3">
              <?php endif; ?>
              <h4 class="mb-1 text-uppercase">Panel Admin</h4>
              <p class="text-muted mb-0">Silahkan masuk ke panel admin.</p>
            </div>
            <?php if (session()->getFlashdata('error')): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert" aria-live="assertive">
                <?= esc(session('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
              </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('message')): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert" aria-live="polite">
                <?= esc(session('message')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
              </div>
            <?php endif; ?>
            <form method="post" action="<?= site_url('login') ?>" class="login-form">
              <?= csrf_field() ?>
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input id="username" type="text" class="form-control" name="username" value="<?= esc(old('username')) ?>" required autofocus>
              </div>
              <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" class="form-control" name="password" required>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Masuk</button>
              </div>
            </form>
            <p class="text-center text-muted small mt-4 mb-0">Kesulitan login? Hubungi administrator sistem OPD.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
<!-- Legacy login markup (disabled) -->
<!--
<div>
  <div class="login_wrapper">
    <div class="animate form login_form">
      <section class="login_content">
        <form method="post" action="<?= site_url('login') ?>">
          <?= csrf_field() ?>
          <h1>Panel Admin</h1>

          <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
          <?php endif; ?>
          <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success"><?= esc(session('message')) ?></div>
          <?php endif; ?>

          <div><input type="text" class="form-control" name="username" value="<?= esc(old('username')) ?>" placeholder="Username" required></div>
          <div><input type="password" class="form-control" name="password" placeholder="Password" required></div>
          <div><button type="submit" class="btn btn-default submit">Masuk</button></div>

          <div class="clearfix"></div>
          <div class="separator"><p>&copy; <?= date('Y') ?> OPD &bull; Admin Panel</p></div>
        </form>
      </section>
    </div>
  </div>
</div>

-->
<script src="<?= base_url('assets/vendor/libs/jquery/jquery.js') ?>"></script>
<script src="<?= base_url('assets/vendor/libs/popper/popper.js') ?>"></script>
<script src="<?= base_url('assets/vendor/js/bootstrap.js') ?>"></script>
<script src="<?= base_url('assets/vendor/js/helpers.js') ?>"></script>
<script src="<?= base_url('assets/js/config.js') ?>"></script>
<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>



