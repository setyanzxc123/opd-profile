<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold">Pengguna</h4>
  <a class="btn btn-primary" href="<?= site_url('admin/users/create') ?>"><i class="bx bx-plus"></i> Tambah</a>
  </div>

<?php if (session()->getFlashdata('message')): ?>
  <div class="alert alert-success alert-dismissible" role="alert">
    <?= esc(session('message')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible" role="alert">
    <?= esc(session('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table id="usersTable" class="table table-striped align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Login Terakhir</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $i => $u): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= esc($u['username']) ?></td>
              <td><?= esc($u['name'] ?? '-') ?></td>
              <td><?= esc($u['email']) ?></td>
              <td><span class="badge bg-label-info text-uppercase"><?= esc($u['role']) ?></span></td>
              <td>
                <?php if (!empty($u['is_active'])): ?>
                  <span class="badge bg-success">Aktif</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Nonaktif</span>
                <?php endif; ?>
              </td>
              <td><?= esc($u['last_login_at'] ?? '-') ?></td>
              <td class="text-end">
                <a href="<?= site_url('admin/users/edit/'.$u['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bx bx-edit"></i> Edit</a>
                <form method="post" action="<?= site_url('admin/users/toggle/'.$u['id']) ?>" style="display:inline" onsubmit="return confirm('Ubah status akun ini?')">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-outline-warning" title="Aktif/Nonaktif"><i class="bx bx-power-off"></i></button>
                </form>
                <form method="post" action="<?= site_url('admin/users/reset/'.$u['id']) ?>" style="display:inline" onsubmit="return confirm('Reset password pengguna ini?')">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Reset Password"><i class="bx bx-key"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  </div>

<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(function() {
    $('#usersTable').DataTable({
      order: [],
      language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
      columnDefs: [
        { targets: -1, orderable: false, searchable: false },
      ]
    });
  });
  </script>
<?= $this->endSection() ?>

