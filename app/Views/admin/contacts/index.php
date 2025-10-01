<?php
use CodeIgniter\I18n\Time;
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <?php if (session()->getFlashdata('message')): ?>
      <div class="alert alert-soft-success alert-dismissible mb-3" role="alert">
        <?= esc(session('message')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-soft-danger alert-dismissible mb-3" role="alert">
        <?= esc(session('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
    <?php endif; ?>

    <?php
      $filters         = $filters ?? ['status' => 'all', 'q' => '', 'per_page' => 15];
      $statusLabels    = $statusLabels ?? ['new' => 'Baru', 'in_progress' => 'Diproses', 'closed' => 'Selesai'];
      $statusCounts    = $statusCounts ?? [];
      $perPageOptions  = $perPageOptions ?? [10, 15, 25, 50];
      $selectedStatus  = $filters['status'] ?? 'all';
      $queryQ          = trim((string) ($filters['q'] ?? ''));
      $selectedPerPage = (int) ($filters['per_page'] ?? 15);

      // Build redirect URL preserving minimal filters
      $redirectParams = [];
      if ($selectedStatus !== 'all') { $redirectParams['status'] = $selectedStatus; }
      if ($queryQ !== '') { $redirectParams['q'] = $queryQ; }
      if (in_array($selectedPerPage, $perPageOptions, true)) { $redirectParams['per_page'] = $selectedPerPage; }
      $redirectUrl = site_url('admin/contacts') . (! empty($redirectParams) ? '?' . http_build_query($redirectParams) : '');

      $totalMessages = isset($messages) && is_countable($messages) ? count($messages) : 0;
    ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h4 class="fw-bold mb-0">Pesan Kontak</h4>
      <form class="d-flex flex-wrap gap-2" method="get" action="<?= site_url('admin/contacts') ?>" role="search" aria-label="Filter pesan">
        <div class="input-group">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" name="q" class="form-control" placeholder="Cari nama, email, subjek..." value="<?= esc($queryQ) ?>" />
        </div>
        <select name="status" class="form-select">
          <option value="all" <?= $selectedStatus === 'all' ? 'selected' : '' ?>>Semua Status</option>
          <?php foreach ($statusLabels as $key => $label): ?>
            <option value="<?= esc($key) ?>" <?= $selectedStatus === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="per_page" class="form-select">
          <?php foreach ($perPageOptions as $opt): ?>
            <option value="<?= (int) $opt ?>" <?= $selectedPerPage === (int) $opt ? 'selected' : '' ?>><?= (int) $opt ?>/hal</option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Terapkan</button>
        <?php if ($selectedStatus !== 'all' || $queryQ !== ''): ?>
          <a href="<?= site_url('admin/contacts') ?>" class="btn btn-light">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <div class="card mb-4">
      <?php if (! empty($messages)): ?>
        <form id="contactBulkForm" method="post" action="<?= site_url('admin/contacts/bulk/status') ?>" class="d-flex flex-column">
          <?= csrf_field() ?>
          <input type="hidden" name="redirect_to" value="<?= esc($redirectUrl) ?>">

          <div class="card-body pb-0">
            <div class="alert alert-soft-warning contact-bulk-alert d-none mb-3" role="alert" data-bulk-alert></div>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th style="width:38px"><input class="form-check-input" type="checkbox" value="1" data-select-all aria-label="Pilih semua"></th>
                    <th>Pesan</th>
                    <th style="width:180px">Diterima</th>
                    <th style="width:120px">Status</th>
                    <th class="text-end" style="width:120px">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($messages as $item): ?>
                  <?php
                    $createdAt   = ! empty($item['created_at']) ? Time::parse($item['created_at']) : null;
                    $respondedAt = ! empty($item['responded_at']) ? Time::parse($item['responded_at']) : null;
                    $statusKey   = $item['status'] ?? 'new';
                    $statusLabel = $statusLabels[$statusKey] ?? ucfirst($statusKey);
                    $badgeClass  = [ 'new' => 'bg-label-primary', 'in_progress' => 'bg-label-warning', 'closed' => 'bg-label-success' ][$statusKey] ?? 'bg-label-secondary';
                    $messageRaw  = strip_tags((string) ($item['message'] ?? ''));
                    $preview     = mb_strimwidth($messageRaw, 0, 100, '...');
                  ?>
                  <tr>
                    <td>
                      <input class="form-check-input" type="checkbox" name="ids[]" value="<?= (int) $item['id'] ?>" data-row-checkbox aria-label="Pilih pesan">
                    </td>
                    <td>
                      <a class="fw-semibold text-body text-decoration-none d-block" href="<?= site_url('admin/contacts/' . (int) $item['id']) ?>">
                        <?= esc($item['subject'] ?: '(Tanpa subjek)') ?>
                      </a>
                      <div class="small text-muted">
                        dari <?= esc($item['name'] ?: 'Anonim') ?> &middot; <?= esc($item['email'] ?: '-') ?>
                      </div>
                      <?php if ($preview !== ''): ?>
                        <div class="text-muted small text-truncate" style="max-width: 640px;">&nbsp;<?= esc($preview) ?></div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($createdAt): ?>
                        <div><?= esc($createdAt->toLocalizedString('d MMM yyyy HH:mm')) ?></div>
                        <small class="text-muted"><?= esc($createdAt->humanize()) ?></small>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="badge <?= esc($badgeClass) ?>"><?= esc($statusLabel) ?></span>
                    </td>
                    <td class="text-end">
                      <div class="btn-group btn-group-sm" role="group">
                        <a class="btn btn-outline-secondary" href="<?= site_url('admin/contacts/' . (int) $item['id']) ?>" aria-label="Lihat detail">
                          <i class="bx bx-show"></i>
                        </a>
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi lainnya">
                          <span class="visually-hidden">Aksi lainnya</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                          <li>
                            <form method="post" action="<?= site_url('admin/contacts/' . (int) $item['id'] . '/status') ?>" class="m-0">
                              <?= csrf_field() ?>
                              <input type="hidden" name="status" value="in_progress">
                              <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-warning">
                                <i class="bx bx-play-circle"></i><span>Tandai Diproses</span>
                              </button>
                            </form>
                          </li>
                          <li>
                            <form method="post" action="<?= site_url('admin/contacts/' . (int) $item['id'] . '/status') ?>" class="m-0">
                              <?= csrf_field() ?>
                              <input type="hidden" name="status" value="closed">
                              <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-success">
                                <i class="bx bx-check-circle"></i><span>Tandai Selesai</span>
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

          <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="contact-bulk-actions d-none" data-bulk-actions>
              <div class="d-flex flex-wrap align-items-center gap-2">
                <label class="form-label mb-0" for="bulkStatus">Ubah status terpilih</label>
                <select class="form-select" id="bulkStatus" name="status">
                  <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">
                  <i class="bx bx-refresh"></i>
                  <span class="ms-1">Terapkan</span>
                </button>
              </div>
            </div>
            <div class="ms-auto">
              <?= isset($pager) ? $pager->links('contacts') : '' ?>
            </div>
          </div>
        </form>
      <?php else: ?>
        <div class="card-body text-center py-5">
          <i class="bx bx-envelope-open display-4 text-muted mb-3"></i>
          <p class="mb-1 fw-semibold">Belum ada pesan kontak.</p>
          <p class="text-muted mb-0">Pesan yang dikirim dari halaman publik akan tampil di sini.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
(() => {
  const bulkForm = document.querySelector('#contactBulkForm');
  if (!bulkForm) return;

  const selectAll = bulkForm.querySelector('[data-select-all]');
  const bulkActions = bulkForm.querySelector('[data-bulk-actions]');
  const alertBox = bulkForm.querySelector('[data-bulk-alert]');
  const rowSelector = '[data-row-checkbox]';

  const getRowCheckboxes = () => Array.from(bulkForm.querySelectorAll(rowSelector));

  const syncSelectAllState = () => {
    if (!selectAll) return;
    const rows = getRowCheckboxes();
    const checked = rows.filter(cb => cb.checked).length;
    selectAll.checked = checked > 0 && checked === rows.length;
    selectAll.indeterminate = checked > 0 && checked < rows.length;
  };

  const applySelectionState = () => {
    const hasSelection = getRowCheckboxes().some(cb => cb.checked);
    bulkActions && bulkActions.classList.toggle('d-none', !hasSelection);
    if (alertBox && hasSelection) {
      alertBox.classList.add('d-none');
      alertBox.textContent = '';
    }
    syncSelectAllState();
  };

  selectAll && selectAll.addEventListener('change', () => {
    getRowCheckboxes().forEach(cb => { cb.checked = selectAll.checked; });
    applySelectionState();
  });

  bulkForm.addEventListener('change', (e) => {
    if (e.target.matches(rowSelector)) applySelectionState();
  });

  bulkForm.addEventListener('submit', (e) => {
    const hasSelection = getRowCheckboxes().some(cb => cb.checked);
    if (!hasSelection) {
      e.preventDefault();
      if (alertBox) {
        alertBox.textContent = 'Pilih minimal satu pesan sebelum menerapkan aksi massal.';
        alertBox.classList.remove('d-none');
        bulkForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  });

  applySelectionState();
})();
</script>
<?= $this->endSection() ?>
