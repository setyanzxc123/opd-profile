<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold">Log Aktivitas</h4>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3" action="<?= esc(current_url(), 'attr') ?>">
            <div class="col-md-4">
                <label class="form-label" for="filterUser">Pengguna</label>
                <select id="filterUser" name="user_id" class="form-select">
                    <option value="">Semua Pengguna</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= esc($user['id']) ?>" <?= (int)($filters['user_id'] ?? 0) === (int)$user['id'] ? 'selected' : '' ?>>
                            <?= esc($user['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filterFrom">Dari Tanggal</label>
                <input type="date" id="filterFrom" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filterTo">Sampai Tanggal</label>
                <input type="date" id="filterTo" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>" class="form-control">
            </div>
            <div class="col-md-2 d-flex flex-wrap align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter me-1"></i> Filter</button>
                <a href="<?= esc(current_url(), 'attr') ?>" class="btn btn-outline-secondary w-100" title="Atur ulang filter"><i class="bx bx-reset me-1"></i> Atur Ulang</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="logsTable" class="table table-striped table-hover table-sm align-middle">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Waktu</th>
                        <th scope="col">Pengguna</th>
                        <th scope="col">Aksi</th>
                        <th scope="col">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $index => $log): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= esc($log['created_at']) ?></td>
                            <td><?= esc($log['username'] ?? '-') ?></td>
                            <td>
                              <?php
                                $badgeClass = 'text-bg-primary';
                                $act = strtolower((string)($log['action'] ?? ''));
                                if ($act !== '') {
                                  if (strpos($act, 'failed') !== false && (strpos($act, 'login') !== false || strpos($act, 'auth') !== false)) {
                                    $badgeClass = 'text-bg-danger';
                                  } elseif (strpos($act, 'logout') !== false) {
                                    $badgeClass = 'text-bg-secondary';
                                  } elseif (strpos($act, 'login') !== false || strpos($act, 'auth') !== false) {
                                    $badgeClass = 'text-bg-success';
                                  } elseif (strpos($act, 'delete') !== false || strpos($act, 'remove') !== false) {
                                    $badgeClass = 'text-bg-danger';
                                  } elseif (strpos($act, 'update') !== false || strpos($act, 'edit') !== false) {
                                    $badgeClass = 'text-bg-warning';
                                  } elseif (strpos($act, 'create') !== false || strpos($act, 'add') !== false || strpos($act, 'insert') !== false) {
                                    $badgeClass = 'text-bg-success';
                                  } elseif (strpos($act, 'download') !== false) {
                                    $badgeClass = 'text-bg-info';
                                  } elseif (strpos($act, 'upload') !== false) {
                                    $badgeClass = 'text-bg-primary';
                                  }
                                }
                              ?>
                              <span class="badge <?= esc($badgeClass) ?> text-uppercase"><?= esc($log['action']) ?></span>
                            </td>
                            <td><?= esc($log['description']) ?></td>
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
        const table = document.querySelector('#logsTable');
        if (table && typeof $ === 'function' && $.fn.DataTable) {
            $(table).DataTable({
                order: [[1, 'desc']],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                pageLength: 25,
                columnDefs: [
                    { targets: 0, orderable: false },
                ],
            });
        }
    });
</script>
<?= $this->endSection() ?>

