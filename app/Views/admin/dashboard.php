<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="row">
  <div class="col-md-12">
    <div class="x_panel">
      <div class="x_title"><h2>Dashboard</h2><div class="clearfix"></div></div>
      <div class="x_content">Selamat datang, <?= esc(session('username')) ?>.</div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
