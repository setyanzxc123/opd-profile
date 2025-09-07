<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Admin</title>
  <link href="<?= base_url('assets/vendors/bootstrap/dist/css/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/vendors/font-awesome/css/font-awesome.min.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/vendors/nprogress/nprogress.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/vendors/animate.css/animate.min.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/build/css/custom.min.css') ?>" rel="stylesheet">
</head>
<body class="login">
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

          <div><input type="text" class="form-control" name="username" value="<?= old('username') ?>" placeholder="Username" required></div>
          <div><input type="password" class="form-control" name="password" placeholder="Password" required></div>
          <div><button type="submit" class="btn btn-default submit">Masuk</button></div>

          <div class="clearfix"></div>
          <div class="separator"><p>© <?= date('Y') ?> OPD — Admin Panel</p></div>
        </form>
      </section>
    </div>
  </div>
</div>

<script src="<?= base_url('assets/vendors/jquery/dist/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/vendors/bootstrap/dist/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/vendors/nprogress/nprogress.js') ?>"></script>
<script src="<?= base_url('assets/build/js/custom.min.js') ?>"></script>
</body>
</html>
