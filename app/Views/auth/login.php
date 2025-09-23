<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Admin</title>
  <link href="<?= base_url('assets/vendor/css/core.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/vendor/css/pages/page-auth.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/vendor/fonts/iconify-icons.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/css/demo.css') ?>" rel="stylesheet">
</head>
<body>
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <div class="card">
          <div class="card-body">
            <h4 class="mb-2">Login</h4>
            <?php if (session()->getFlashdata('error')): ?>
              <div class="alert alert-danger" role="alert"><?= esc(session('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('message')): ?>
              <div class="alert alert-success" role="alert"><?= esc(session('message')) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= site_url('login') ?>">
              <?= csrf_field() ?>
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input id="username" type="text" class="form-control" name="username" value="<?= esc(old('username')) ?>" required autofocus>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" class="form-control" name="password" required>
              </div>
              <div class="mb-3">
                <button type="submit" class="btn btn-primary d-grid w-100">Masuk</button>
              </div>
            </form>
            <p class="text-center mt-2 mb-0">&copy; <?= date('Y') ?> OPD &bull; Admin Panel</p>
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
          <h1>Login Admin</h1>

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


