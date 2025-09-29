<?php
use CodeIgniter\I18n\Time;
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="d-sm-flex justify-content-between align-items-start gap-3 mb-3">
      <div>
        <h4 class="fw-bold mb-1">Pesan Kontak</h4>
        <p class="text-muted mb-0">Kelola pesan masyarakat serta tindak lanjut yang telah dilakukan.</p>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-label-secondary" href="<?= site_url('kontak') ?>" target="_blank" rel="noopener">
          <i class="bx bx-link-external"></i>
          <span class="d-none d-sm-inline-block ms-1">Halaman Publik</span>
        </a>
      </div>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
      <div class="alert alert-soft-success alert-dismissible" role="alert">
        <?= esc(session('message')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-soft-danger alert-dismissible" role="alert">
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
      <div class="card-body">
        <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
          <div class="contact-status-filter d-flex flex-wrap align-items-center gap-2">
            <?php foreach ($statusFilters as $key => $label): ?>
              <?php
                $query = array_merge($currentQuery, ['status' => $key]);
                $queryString = http_build_query(array_filter($query, static fn ($value) => $value !== '' && $value !== null && $value !== 'all'));
                if ($key === 'all') {
                    $queryString = http_build_query(array_filter($currentQuery, static fn ($value) => $value !== '' && $value !== null));
                }
                $isActive = ($filters['status'] ?? 'all') === $key;
              ?>
              <a
                class="btn btn-sm <?= $isActive ? 'btn-primary' : 'btn-outline-secondary' ?>"
                href="<?= esc(site_url('admin/contacts') . ($queryString !== '' ? '?' . $queryString : '')) ?>"
              >
                <span><?= esc($label) ?></span>
                <span class="badge rounded-pill ms-2 <?= $isActive ? 'bg-white text-primary' : 'bg-label-secondary' ?>">
                  <?= esc($statusCounts[$key] ?? 0) ?>
                </span>
              </a>
            <?php endforeach; ?>
          </div>

          <form class="row row-cols-1 row-cols-sm-auto g-2 align-items-end justify-content-lg-end" method="get" action="<?= current_url() ?>">
            <input type="hidden" name="status" value="<?= esc($filters['status'] ?? 'all') ?>">
            <div class="col">
              <label class="form-label" for="contactSearch">Cari</label>
              <input
                type="search"
                class="form-control"
                id="contactSearch"
                name="q"
                value="<?= esc($filters['q'] ?? '') ?>"
                placeholder="Nama, email, telepon, atau subjek"
              >
            </div>
            <div class="col">
              <label class="form-label" for="contactPerPage">Tampil</label>
              <select class="form-select" id="contactPerPage" name="per_page">
                <?php foreach ($perPageOptions as $option): ?>
                  <option value="<?= $option ?>"<?= (int) ($filters['per_page'] ?? 15) === $option ? ' selected' : '' ?>><?= $option ?> / halaman</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col">
              <label class="form-label d-none d-sm-block">&nbsp;</label>
              <button type="submit" class="btn btn-primary w-100">
                <i class="bx bx-search"></i>
                <span class="ms-1">Terapkan</span>
              </button>
            </div>
            <?php if ($hasFilterApplied): ?>
              <div class="col">
                <label class="form-label d-none d-sm-block">&nbsp;</label>
                <a class="btn btn-outline-secondary w-100" href="<?= site_url('admin/contacts') ?>">
                  <i class="bx bx-refresh"></i>
                  <span class="ms-1">Reset</span>
                </a>
              </div>
            <?php endif; ?>
          </form>
        </div>
      </div>
    </div>

    <?php if (! empty($messages)): ?>
      <div class="card">
        <form id="contactBulkForm" method="post" action="<?= site_url('admin/contacts/bulk/status') ?>" class="d-flex flex-column">
          <?= csrf_field() ?>
          <input type="hidden" name="redirect_to" value="<?= esc($redirectUrl) ?>">

          <div class="card-body pb-0">
            <div class="alert alert-soft-warning contact-bulk-alert d-none" role="alert" data-bulk-alert></div>
            <?php
              $currentPage = isset($pager) && method_exists($pager, 'getCurrentPage') ? (int) ($pager->getCurrentPage('contacts') ?? 1) : 1;
              $perPage = isset($pager) && method_exists($pager, 'getPerPage') ? (int) ($pager->getPerPage('contacts') ?? ($filters['per_page'] ?? count($messages))) : (int) ($filters['per_page'] ?? count($messages));
              if ($perPage <= 0) {
                  $perPage = count($messages) ?: 1;
              }
              $rowNumberStart = ($currentPage - 1) * $perPage;
            ?>
            <div class="table-responsive">
              <table class="table table-striped table-hover table-compact align-middle mb-0" id="contactsTable">
                <thead>
                  <tr>
                    <th style="width: 36px;">
                      <input class="form-check-input" type="checkbox" value="1" data-select-all>
                    </th>
                    <th style="width: 56px;">#</th>
                    <th>Pengirim</th>
                    <th>Subjek</th>
                    <th>Status</th>
                    <th>Diterima</th>
                    <th>Ditangani</th>
                    <th>Sumber</th>
                    <th class="text-end">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($messages as $index => $item): ?>
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
                    ?>
                    <tr>
                      <td>
                        <input class="form-check-input" type="checkbox" name="ids[]" value="<?= esc($item['id']) ?>" data-row-checkbox>
                      </td>
                      <td><?= esc($rowNumberStart + $index + 1) ?></td>
                      <td>
                        <div class="fw-semibold"><a class="text-decoration-none" href="<?= site_url('admin/contacts/' . $item['id']) ?>"><?= esc($item['name']) ?></a></div>
                        <small class="text-muted d-block"><?= esc($item['email']) ?></small>
                        <?php if ($phoneNumber !== ''): ?>
                          <small class="text-muted d-block">Tel: <?= esc($phoneNumber) ?></small>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="fw-semibold text-truncate" style="max-width: 260px;"><a class="text-decoration-none" href="<?= site_url('admin/contacts/' . $item['id']) ?>"><?= esc($item['subject'] ?: '(Tanpa subjek)') ?></a></div>
                        <small class="text-muted d-block text-truncate" style="max-width: 260px;"><?= esc($messagePreview) ?></small>
                      </td>
                      <td>
                        <span class="badge <?= $badgeClass ?>"><?= esc($statusLabel) ?></span>
                      </td>
                      <td>
                        <?php if ($createdAt): ?>
                          <div><?= esc($createdAt->toLocalizedString('d MMM yyyy HH:mm')) ?></div>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (! empty($item['handled_by'])): ?>
                          <div><?= esc($item['handler_name'] ?? 'Admin') ?></div>
                        <?php endif; ?>
                        <?php if ($respondedAt): ?>
                          <small class="text-muted"><?= esc($respondedAt->toLocalizedString('d MMM yyyy HH:mm')) ?></small>
                        <?php elseif ($statusKey === 'closed'): ?>
                          <small class="text-muted">Ditandai selesai</small>
                        <?php else: ?>
                          <small class="text-muted">Belum ditangani</small>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="badge bg-label-info me-1">Web</span>
                        <?php if ($phoneNumber !== ''): ?>
                          <span class="badge bg-label-secondary me-1">Tel</span>
                        <?php endif; ?>
                        <?php if (! empty($item['ip_address'])): ?>
                          <div class="text-muted small">IP: <?= esc($item['ip_address']) ?></div>
                        <?php endif; ?>
                        <?php if (! empty($item['user_agent'])): ?>
                          <small class="text-muted d-block text-truncate" style="max-width: 260px;" title="<?= esc($item['user_agent']) ?>">UA: <?= esc($item['user_agent']) ?></small>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                          <a class="btn btn-outline-primary" href="<?= site_url('admin/contacts/' . $item['id']) ?>">
                            <i class="bx bx-show"></i> Detail
                          </a>
                          <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
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

          <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3 contact-bulk-actions">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <label class="form-label mb-0" for="bulkStatus">Ubah status terpilih ke</label>
              <select class="form-select" id="bulkStatus" name="status">
                <?php foreach ($statusLabels as $key => $label): ?>
                  <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-primary">
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
      </div>
    <?php else: ?>
      <div class="card">
        <div class="card-body text-center py-5">
          <i class="bx bx-envelope-open display-4 text-muted mb-3"></i>
          <p class="mb-1 fw-semibold">Belum ada pesan kontak.</p>
          <p class="text-muted mb-0">Formulir publik akan menampilkan data di sini setelah pengunjung mengirim pesan.</p>
        </div>
      </div>
    <?php endif; ?>
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

  const updateAlertState = () => {
    if (rowCheckboxes.some((checkbox) => checkbox.checked)) {
      hideAlert();
    }
  };

  if (selectAll) {
    selectAll.addEventListener('change', () => {
      rowCheckboxes.forEach((checkbox) => {
        checkbox.checked = selectAll.checked;
      });
      updateAlertState();
    });
  }

  rowCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', updateAlertState);
  });

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
