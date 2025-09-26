<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold">Pengguna</h4>
    <a class="btn btn-primary" href="<?= site_url('admin/users/create') ?>"><i class="bx bx-plus"></i> Tambah</a>
</div>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-soft-success alert-dismissible" role="alert" data-auto-dismiss="true" aria-live="polite">
        <i class="bx bx-check-circle me-1"></i> <?= esc(session('message')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-soft-danger alert-dismissible" role="alert" aria-live="assertive">
        <i class="bx bx-error-circle me-1"></i> <?= esc(session('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="usersTable" class="table table-striped table-compact align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
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
                            <td><span class="badge bg-label-info"><?= esc(ucfirst($u['role'])) ?></span></td>
                            <td><?= !empty($u['is_active']) ? '<span class="badge bg-label-success">Aktif</span>' : '<span class="badge bg-label-secondary">Nonaktif</span>' ?></td>
                            <td><?= esc($u['last_login_at'] ?? '-') ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= site_url('admin/users/edit/' . $u['id']) ?>" class="btn btn-outline-secondary"><i class="bx bx-edit"></i> Ubah</a>
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi lainnya">
                                        <span class="visually-hidden">Aksi lainnya</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <form method="post" action="<?= site_url('admin/users/toggle/' . $u['id']) ?>" onsubmit="return confirm('Ubah status akun ini?')" class="m-0">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-warning">
                                                    <i class="bx bx-power-off"></i>
                                                    <span><?= !empty($u['is_active']) ? 'Nonaktifkan' : 'Aktifkan' ?></span>
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="post" action="<?= site_url('admin/users/reset/' . $u['id']) ?>" onsubmit="return confirm('Setel ulang kata sandi pengguna ini?')" class="m-0">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                                    <i class="bx bx-key"></i>
                                                    <span>Setel Ulang Kata Sandi</span>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
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
    document.addEventListener('DOMContentLoaded', () => {
        const bootstrapAvailable = typeof bootstrap !== 'undefined';

        document.querySelectorAll('[data-auto-dismiss="true"]').forEach(alert => {
            setTimeout(() => {
                if (bootstrapAvailable) {
                    const instance = bootstrap.Alert.getOrCreateInstance(alert);
                    instance.close();
                } else {
                    alert.classList.add('fade');
                    alert.addEventListener('transitionend', () => alert.remove());
                }
            }, 4000);
        });

        const table = document.querySelector('#usersTable');
        if (table && typeof $ === 'function' && $.fn.DataTable) {
            $(table).DataTable({
                order: [],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                columnDefs: [{
                    targets: -1,
                    orderable: false,
                    searchable: false
                }]
            });
        }
    });
</script>
<?= $this->endSection() ?>
