<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold">Activity Logs</h4>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3" action="<?= esc(current_url(), 'url') ?>">
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
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter me-1"></i> Filter</button>
                <a href="<?= esc(current_url(), 'url') ?>" class="btn btn-outline-secondary" title="Reset filter"><i class="bx bx-reset"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="logsTable" class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $index => $log): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= esc($log['created_at']) ?></td>
                            <td><?= esc($log['username'] ?? '-') ?></td>
                            <td><span class="badge bg-label-primary text-uppercase"><?= esc($log['action']) ?></span></td>
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

