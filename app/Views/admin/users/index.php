<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold">Pengguna</h4>
    <a class="btn btn-primary" href="<?= site_url('admin/users/create') ?>"><i class="bx bx-plus"></i> Tambah</a>
</div>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-success alert-dismissible" role="alert" data-auto-dismiss="true" aria-live="polite">
        <i class="bx bx-check-circle me-1"></i> <?= esc(session('message')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible" role="alert" aria-live="assertive">
        <i class="bx bx-error-circle me-1"></i> <?= esc(session('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="usersTable" class="table table-striped table-sm align-middle">
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
                            <td><span class="badge text-bg-info"><?= esc(ucfirst($u['role'])) ?></span></td>
                            <td><?= !empty($u['is_active']) ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>' ?></td>
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

<?php 
    $resetData = session()->getFlashdata('reset_password_data');
    if ($resetData && !empty($resetData['username']) && !empty($resetData['password'])): 
?>
<!-- Password Reset Modal (One-time display) -->
<div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="passwordResetModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="passwordResetModalLabel">
                    <i class="bx bx-key me-2"></i>Password Baru Dibuat
                </h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="bx bx-info-circle me-1"></i>
                    <strong>Penting!</strong> Password ini hanya ditampilkan sekali. Salin dan simpan sebelum menutup.
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <div class="form-control bg-light"><?= esc($resetData['username']) ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password Baru</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light font-monospace" id="newPasswordDisplay" 
                               value="<?= esc($resetData['password']) ?>" readonly>
                        <button class="btn btn-outline-primary" type="button" id="copyPasswordBtn" title="Salin password">
                            <i class="bx bx-copy"></i>
                        </button>
                    </div>
                    <div id="copyFeedback" class="form-text text-success" style="display: none;">
                        <i class="bx bx-check"></i> Password berhasil disalin!
                    </div>
                </div>
                
                <div class="alert alert-light border mb-0">
                    <small class="text-muted">
                        <i class="bx bx-shield me-1"></i>
                        Berikan password ini kepada pengguna secara langsung atau melalui saluran yang aman. 
                        Hindari mengirim via email yang tidak terenkripsi.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="bx bx-check me-1"></i>Saya Sudah Menyimpan Password
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-show the password modal
    const modal = new bootstrap.Modal(document.getElementById('passwordResetModal'));
    modal.show();
    
    // Copy to clipboard functionality
    document.getElementById('copyPasswordBtn').addEventListener('click', function() {
        const passwordField = document.getElementById('newPasswordDisplay');
        const feedback = document.getElementById('copyFeedback');
        
        navigator.clipboard.writeText(passwordField.value).then(function() {
            feedback.style.display = 'block';
            setTimeout(() => { feedback.style.display = 'none'; }, 3000);
        }).catch(function() {
            // Fallback for older browsers
            passwordField.select();
            document.execCommand('copy');
            feedback.style.display = 'block';
            setTimeout(() => { feedback.style.display = 'none'; }, 3000);
        });
    });
});
</script>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet"
      href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"
      integrity="sha384-5oFfLntNy8kuC2TaebWZbaHTqdh3Q+7PwYbB490gupK0YtTAB7mBJGv4bQl9g9rK"
      crossorigin="anonymous">
<link rel="stylesheet"
      href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css"
      integrity="sha384-jj44VXG857zuSsNQ7iqJihzOcCFRhs3qS4oLwyc4Hg+P9WjpwyR6T1ulnFKzhhaQ"
      crossorigin="anonymous">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"
        integrity="sha384-k5vbMeKHbxEZ0AEBTSdR7UjAgWCcUfrS8c0c5b2AfIh7olfhNkyCZYwOfzOQhauK"
        crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"
        integrity="sha384-PgPBH0hy6DTJwu7pTf6bkRqPlf/+pjUBExpr/eIfzszlGYFlF9Wi9VTAJODPhgCO"
        crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"
        integrity="sha384-VUnyCeQcqiiTlSM4AISHjJWKgLSM5VSyOeipcD9S/ybCKR3OhChZrPPjjrLfVV0y"
        crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"
        integrity="sha384-T6YQaHyTPTbybQQV23jtlugHCneQYjePXdcEU+KMWGQY8EUQygBW9pRx0zpSU0/i"
        crossorigin="anonymous"></script>
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
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.childRowImmediate,
                        target: 'tr'
                    }
                },
                autoWidth: false,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                columnDefs: [
                    { targets: 0, responsivePriority: 6 },
                    { targets: 1, responsivePriority: 1 },
                    { targets: 2, responsivePriority: 3 },
                    { targets: 3, responsivePriority: 5 },
                    { targets: 4, responsivePriority: 4 },
                    { targets: 5, responsivePriority: 7 },
                    { targets: 6, responsivePriority: 8 },
                    { targets: -1, orderable: false, searchable: false, responsivePriority: 2 }
                ]
            });
        }
    });
</script>
<?= $this->endSection() ?>

