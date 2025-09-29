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
      $filters        = $filters ?? ['status' => 'all', 'q' => '', 'per_page' => 15];
      $statusFilters  = $statusFilters ?? ['all' => 'Semua'];
      $statusCounts   = $statusCounts ?? [];
      $perPageOptions = $perPageOptions ?? [10, 15, 25, 50];
      $currentQuery   = [
        'q'        => $filters['q'] ?? '',
        'per_page' => $filters['per_page'] ?? 15,
      ];
      $currentUrl     = current_url();
      $redirectQuery  = $filters;
      $redirectUrl    = $currentUrl;
      if (! empty(array_filter($redirectQuery, static fn ($value) => $value !== '' && $value !== null && $value !== 'all'))) {
          $redirectUrl .= '?' . http_build_query(array_filter($redirectQuery, static fn ($value) => $value !== '' && $value !== null));
      }
      $hasFilterApplied = trim((string) ($filters['q'] ?? '')) !== '' || (int) ($filters['per_page'] ?? 15) !== 15;
    ?>

    <div class="card mb-4">
      <div class="card-header d-flex flex-column flex-sm-row align-items-sm-start justify-content-between gap-3">
        <div>
          <h4 class="fw-bold mb-1">Pesan Kontak</h4>
        </div>
      </div>

      <div class="card-body border-bottom pb-3">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
          <ul class="nav nav-pills flex-wrap gap-2 contact-status-filter mb-0">
            <?php foreach ($statusFilters as $key => $label): ?>
              <?php
                $query       = array_merge($currentQuery, ['status' => $key]);
                $queryString = http_build_query(array_filter($query, static fn ($value) => $value !== '' && $value !== null && $value !== 'all'));
                if ($key === 'all') {
                    $queryString = http_build_query(array_filter($currentQuery, static fn ($value) => $value !== '' && $value !== null));
                }
                $isActive        = ($filters['status'] ?? 'all') === $key;
                $navLinkClasses  = 'nav-link rounded-pill d-flex align-items-center gap-2 px-3 py-1';
                if ($isActive) {
                    $navLinkClasses .= ' active';
                }
                $badgeClasses = $isActive ? 'badge rounded-pill bg-white text-primary' : 'badge rounded-pill bg-label-secondary';
              ?>
              <li class="nav-item">
                <a
                  class="<?= esc($navLinkClasses) ?>"
                  href="<?= esc(site_url('admin/contacts') . ($queryString !== '' ? '?' . $queryString : '')) ?>"
                >
                  <span><?= esc($label) ?></span>
                  <span class="<?= esc($badgeClasses) ?>"><?= esc($statusCounts[$key] ?? 0) ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>

          <form class="d-flex flex-wrap align-items-center gap-2 justify-content-lg-end" method="get" action="<?= current_url() ?>">
            <input type="hidden" name="status" value="<?= esc($filters['status'] ?? 'all') ?>">
            <div class="input-group input-group-sm flex-grow-1 flex-lg-grow-0" style="min-width: 260px;">
              <span class="input-group-text"><i class="bx bx-search"></i></span>
              <input
                type="search"
                class="form-control"
                id="contactSearch"
                name="q"
                value="<?= esc($filters['q'] ?? '') ?>"
                placeholder="Cari pesan"
                aria-label="Cari pesan kontak"
              >
              <select class="form-select" id="contactPerPage" name="per_page" aria-label="Jumlah pesan per halaman">
                <?php foreach ($perPageOptions as $option): ?>
                  <option value="<?= $option ?>"<?= (int) ($filters['per_page'] ?? 15) === $option ? ' selected' : '' ?>><?= $option ?>/hal</option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">
              <i class="bx bx-filter-alt"></i>
              <span class="ms-1 d-none d-sm-inline">Terapkan</span>
            </button>
            <?php if ($hasFilterApplied): ?>
              <a class="btn btn-sm btn-link text-decoration-none" href="<?= site_url('admin/contacts') ?>">Reset</a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <?php if (! empty($messages)): ?>
        <form id="contactBulkForm" method="post" action="<?= site_url('admin/contacts/bulk/status') ?>" class="d-flex flex-column">
          <?= csrf_field() ?>
          <input type="hidden" name="redirect_to" value="<?= esc($redirectUrl) ?>">

          <div class="card-body pb-0">
            <div class="alert alert-soft-warning contact-bulk-alert d-none mb-3" role="alert" data-bulk-alert></div>
            <div class="table-responsive">
              <table class="table table-striped table-hover table-compact align-middle mb-0" id="contactsTable">
                <thead>
                  <tr>
                    <th style="width: 38px;">
                      <input class="form-check-input" type="checkbox" value="1" data-select-all>
                    </th>
                    <th>Pengirim</th>
                    <th>Ringkasan</th>
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
                        'new'         => 'bg-label-primary',
                        'in_progress' => 'bg-label-warning',
                        'closed'      => 'bg-label-success',
                      ][$statusKey] ?? 'bg-label-secondary';
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
                        <div class="small text-muted text-truncate" style="max-width: 200px;"><?= esc($item['email']) ?></div>
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
                          <span class="badge bg-label-secondary"><?= esc($ticketBadge) ?></span>
                          <?php if ($createdAt): ?>
                            <span>Masuk <?= esc($createdAt->toLocalizedString('d MMM yyyy HH:mm')) ?></span>
                          <?php endif; ?>
                          <span class="badge bg-label-info"<?php if ($sourceTooltip !== ''): ?> data-bs-toggle="tooltip" title="<?= esc($sourceTooltip) ?>"<?php endif; ?>>Web</span>
                          <?php if ($phoneNumber !== ''): ?>
                            <span class="badge bg-label-secondary">Tel</span>
                          <?php endif; ?>
                        </div>
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
                          <a
                            class="btn btn-outline-primary px-2"
                            href="<?= site_url('admin/contacts/' . $item['id']) ?>"
                            title="Lihat detail"
                          >
                            <i class="bx bx-show"></i>
                            <span class="visually-hidden">Detail</span>
                          </a>
                          <button type="button" class="btn btn-outline-primary px-2 dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
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
            <div class="text-muted small">Total <?= esc($pager->getTotal('contacts') ?? count($messages)) ?> pesan</div>
          </div>
        </form>

        <div class="card-footer bg-transparent border-top-0 pt-0">
          <?= $pager->links('contacts', 'default_full') ?>
        </div>
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

<?= $this->section('pageScripts') ?>
<script>
(() => {
  const bulkForm = document.querySelector('#contactBulkForm');
  if (!bulkForm) {
    return;
  }

  const selectAll = bulkForm.querySelector('[data-select-all]');
  const rowCheckboxes = Array.from(bulkForm.querySelectorAll('[data-row-checkbox]'));
  const bulkAlert = bulkForm.querySelector('[data-bulk-alert]');
  const bulkActions = bulkForm.querySelector('[data-bulk-actions]');

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

  const applySelectionState = () => {
    const hasSelection = rowCheckboxes.some((checkbox) => checkbox.checked);
    if (hasSelection) {
      hideAlert();
    }
    if (bulkActions) {
      bulkActions.classList.toggle('d-none', !hasSelection);
    }
  };

  if (selectAll) {
    selectAll.addEventListener('change', () => {
      rowCheckboxes.forEach((checkbox) => {
        checkbox.checked = selectAll.checked;
      });
      applySelectionState();
    });
  }

  rowCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', applySelectionState);
  });

  applySelectionState();

  bulkForm.addEventListener('submit', (event) => {
    if (!rowCheckboxes.some((checkbox) => checkbox.checked)) {
      event.preventDefault();
      showAlert('Pilih minimal satu pesan sebelum menerapkan aksi massal.');
      bulkForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
})();
</script>
<?= $this->endSection() ?>
