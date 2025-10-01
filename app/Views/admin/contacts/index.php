<?php
use CodeIgniter\I18n\Time;
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <?php if (session()->getFlashdata('message')): ?>
      <div class="alert alert-success alert-dismissible mb-3" role="alert">
        <?= esc(session('message')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible mb-3" role="alert">
        <?= esc(session('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
    <?php endif; ?>

    <?php
      $filters        = $filters ?? ['status' => 'all'];
      $statusFilters  = $statusFilters ?? ['all' => 'Semua'];
      $statusCounts   = $statusCounts ?? [];
      $selectedStatus = $filters['status'] ?? 'all';
      $redirectParams = [];
      if ($selectedStatus !== 'all') {
          $redirectParams['status'] = $selectedStatus;
      }
      $redirectUrl      = current_url() . (! empty($redirectParams) ? '?' . http_build_query($redirectParams) : '');
      $totalMessages    = isset($messages) && is_countable($messages) ? count($messages) : 0;
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">Pesan Kontak</h4>
    </div>

    <div class="card mb-4">

      <div class="card-body border-bottom pb-3">
        <ul class="nav nav-pills flex-wrap gap-2 contact-status-filter mb-0">
          <?php foreach ($statusFilters as $key => $label): ?>
            <?php
              $isActive       = $selectedStatus === $key;
              $navLinkClasses = 'nav-link rounded-pill d-flex align-items-center gap-2 px-3 py-1';
              if ($isActive) {
                  $navLinkClasses .= ' active';
              }
              $badgeClasses = $isActive ? 'badge rounded-pill bg-white text-primary' : 'badge rounded-pill text-bg-secondary';
              $queryParams  = [];
              if ($key !== 'all') {
                  $queryParams['status'] = $key;
              }
              $filterUrl = site_url('admin/contacts') . (! empty($queryParams) ? '?' . http_build_query($queryParams) : '');
            ?>
            <li class="nav-item">
              <a class="<?= esc($navLinkClasses) ?>" href="<?= esc($filterUrl) ?>">
                <span><?= esc($label) ?></span>
                <span class="<?= esc($badgeClasses) ?>"><?= esc($statusCounts[$key] ?? 0) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <?php if (! empty($messages)): ?>
        <form id="contactBulkForm" method="post" action="<?= site_url('admin/contacts/bulk/status') ?>" class="d-flex flex-column">
          <?= csrf_field() ?>
          <input type="hidden" name="redirect_to" value="<?= esc($redirectUrl) ?>">

          <div class="card-body pb-0">
            <div class="alert alert-warning contact-bulk-alert d-none mb-3" role="alert" data-bulk-alert></div>
            <div class="table-responsive">
              <table class="table table-striped table-hover table-sm align-middle mb-0" id="contactsTable">
                <thead>
                  <tr>
                    <th style="width: 38px;">
                      <input class="form-check-input" type="checkbox" value="1" data-select-all>
                    </th>
                    <th>Pengirim</th>
                    <th>Ringkasan</th>
                    <th>Tanggal Pesan Masuk</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($messages as $item): ?>
                    <?php
                      $createdAt      = ! empty($item['created_at']) ? Time::parse($item['created_at']) : null;
                      $respondedAt    = ! empty($item['responded_at']) ? Time::parse($item['responded_at']) : null;
                      $statusKey      = $item['status'] ?? 'new';
                      $statusLabel    = $statusLabels[$statusKey] ?? ucfirst($statusKey);
                      $badgeClass     = [
                        'new'         => 'text-bg-primary',
                        'in_progress' => 'text-bg-warning',
                        'closed'      => 'text-bg-success',
                      ][$statusKey] ?? 'text-bg-secondary';
                      $phoneNumber    = trim((string) ($item['phone'] ?? ''));
                      $messageRaw     = strip_tags((string) ($item['message'] ?? ''));
                      $messagePreview = mb_strimwidth($messageRaw, 0, 120, '...');
                      $ticketBadge    = sprintf('#%04d', (int) ($item['id'] ?? 0));
                      $sourceTooltip  = trim(implode(' | ', array_filter([
                        $item['ip_address'] ?? '',
                        $item['user_agent'] ?? '',
                      ])));
                    ?>
                    <tr>
                      <td>
                        <input class="form-check-input" type="checkbox" name="ids[]" value="<?= esc($item['id']) ?>" data-row-checkbox>
                      </td>
                      <td>
                        <div class="fw-semibold">
                          <a class="text-decoration-none" href="<?= site_url('admin/contacts/' . $item['id']) ?>"><?= esc($item['name']) ?></a>
                        </div>
                        <div class="small text-muted text-truncate" style="max-width: 200px;">
                          <?= esc($item['email']) ?>
                        </div>
                        <?php if ($phoneNumber !== ''): ?>
                          <div class="small text-muted"><?= esc($phoneNumber) ?></div>
                        <?php endif; ?>
                      </td>
                      <td>
                        <a class="fw-semibold d-block text-decoration-none text-body text-truncate" style="max-width: 360px;" href="<?= site_url('admin/contacts/' . $item['id']) ?>">
                          <?= esc($item['subject'] ?: '(Tanpa subjek)') ?>
                        </a>
                        <div class="text-muted small text-truncate" style="max-width: 360px;"><?= esc($messagePreview) ?></div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2 small text-muted">
                          <span class="badge text-bg-secondary"><?= esc($ticketBadge) ?></span>
                          <span class="badge text-bg-info"<?php if ($sourceTooltip !== ''): ?> data-bs-toggle="tooltip" title="<?= esc($sourceTooltip) ?>"<?php endif; ?>>Web</span>
                          <?php if ($phoneNumber !== ''): ?>
                            <span class="badge text-bg-secondary">Tel</span>
                          <?php endif; ?>
                        </div>
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
                        <div class="small text-muted mt-2">
                          <?php if ($respondedAt): ?>
                            Ditindak <?= esc($respondedAt->humanize()) ?>
                          <?php elseif ($statusKey === 'closed'): ?>
                            Ditandai selesai
                          <?php else: ?>
                            Belum ditangani
                          <?php endif; ?>
                        </div>
                      </td>
                      <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                          <a class="btn btn-outline-secondary" href="<?= site_url('admin/contacts/' . $item['id']) ?>">
                            <i class="bx bx-show"></i>
                            <span class="ms-1">Detail</span>
                          </a>
                          <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi lainnya">
                            <span class="visually-hidden">Aksi lainnya</span>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                              <form method="post" action="<?= site_url('admin/contacts/' . $item['id'] . '/status') ?>" class="m-0">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-warning">
                                  <i class="bx bx-play-circle"></i><span>Tandai Diproses</span>
                                </button>
                              </form>
                            </li>
                            <li>
                              <form method="post" action="<?= site_url('admin/contacts/' . $item['id'] . '/status') ?>" class="m-0">
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

          <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3 contact-bulk-actions d-none" data-bulk-actions>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <label class="form-label mb-0" for="bulkStatus">Ubah status terpilih ke</label>
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
            <div class="text-muted small">Total <?= esc($totalMessages) ?> pesan</div>
          </div>
        </form>
      <?php else: ?>
        <div class="card-body text-center py-5">
          <i class="bx bx-envelope-open display-4 text-muted mb-3"></i>
          <p class="mb-1 fw-semibold">Belum ada pesan kontak.</p>
          <p class="text-muted mb-0">Formulir publik akan menampilkan data di sini setelah pengunjung mengirim pesan.</p>
        </div>
      <?php endif; ?>
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
(() => {
  const initDataTable = () => {
    const tableElement = document.querySelector('#contactsTable');
    if (!tableElement || typeof $ !== 'function' || !$.fn.DataTable) {
      return null;
    }

    return $(tableElement).DataTable({
      order: [],
      language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
      },
      columnDefs: [
        { targets: 0, orderable: false, searchable: false },
        { targets: -1, orderable: false, searchable: false }
      ],
      pageLength: 10
    });
  };

  const initBulkActions = (dataTable) => {
    const bulkForm = document.querySelector('#contactBulkForm');
    if (!bulkForm) {
      return;
    }

    const selectAll = bulkForm.querySelector('[data-select-all]');
    const bulkAlert = bulkForm.querySelector('[data-bulk-alert]');
    const bulkActions = bulkForm.querySelector('[data-bulk-actions]');
    const rowCheckboxSelector = '[data-row-checkbox]';

    const getRowCheckboxes = () => Array.from(bulkForm.querySelectorAll(rowCheckboxSelector));

    const hideAlert = () => {
      if (bulkAlert) {
        bulkAlert.classList.add('d-none');
        bulkAlert.textContent = '';
      }
    };

    const showAlert = (message) => {
      if (bulkAlert) {
        bulkAlert.textContent = message;
        bulkAlert.classList.remove('d-none');
      }
    };

    const syncSelectAllState = () => {
      if (!selectAll) {
        return;
      }

      const rowCheckboxes = getRowCheckboxes();
      if (rowCheckboxes.length === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
        return;
      }

      const checkedCount = rowCheckboxes.filter((checkbox) => checkbox.checked).length;
      if (checkedCount === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
      } else if (checkedCount === rowCheckboxes.length) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
      } else {
        selectAll.checked = false;
        selectAll.indeterminate = true;
      }
    };

    const applySelectionState = () => {
      const hasSelection = getRowCheckboxes().some((checkbox) => checkbox.checked);

      if (hasSelection) {
        hideAlert();
      }

      if (bulkActions) {
        bulkActions.classList.toggle('d-none', !hasSelection);
      }

      syncSelectAllState();
    };

    if (selectAll) {
      selectAll.addEventListener('change', () => {
        getRowCheckboxes().forEach((checkbox) => {
          checkbox.checked = selectAll.checked;
        });
        applySelectionState();
      });
    }

    bulkForm.addEventListener('change', (event) => {
      if (event.target.matches(rowCheckboxSelector)) {
        applySelectionState();
      }
    });

    applySelectionState();

    bulkForm.addEventListener('submit', (event) => {
      const hasSelection = getRowCheckboxes().some((checkbox) => checkbox.checked);
      if (!hasSelection) {
        event.preventDefault();
        showAlert('Pilih minimal satu pesan sebelum menerapkan aksi massal.');
        bulkForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });

    if (dataTable) {
      $(dataTable.table().node()).on('draw.dt', () => {
        applySelectionState();
      });
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    const dataTable = initDataTable();
    initBulkActions(dataTable);
  });
})();
</script>
<?= $this->endSection() ?>
