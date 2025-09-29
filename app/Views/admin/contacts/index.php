<?php
use CodeIgniter\I18n\Time;
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
      <div>
        <h4 class="fw-bold mb-0">Pesan Kontak</h4>
        <p class="text-muted mb-0">Kelola pesan masyarakat serta tindak lanjut yang telah dilakukan.</p>
      </div>
      <a class="btn btn-label-secondary" href="<?= site_url('kontak') ?>" target="_blank" rel="noopener">
        <i class="bx bx-link-external"></i> Lihat Halaman Publik
      </a>
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
    ?>

    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
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
              class="badge rounded-pill <?= $isActive ? 'bg-primary' : 'bg-label-secondary' ?>"
              href="<?= esc(site_url('admin/contacts') . ($queryString !== '' ? '?' . $queryString : '')) ?>"
            >
              <?= esc($label) ?>
              <span class="badge bg-white text-primary ms-1">
                <?= esc($statusCounts[$key] ?? 0) ?>
              </span>
            </a>
          <?php endforeach; ?>
        </div>

        <form class="row g-2 align-items-end" method="get" action="<?= current_url() ?>">
          <input type="hidden" name="status" value="<?= esc($filters['status'] ?? 'all') ?>">
          <div class="col-sm-6 col-md-4">
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
          <div class="col-sm-3 col-md-2">
            <label class="form-label" for="contactPerPage">Tampil</label>
            <select class="form-select" id="contactPerPage" name="per_page">
              <?php foreach ($perPageOptions as $option): ?>
                <option value="<?= $option ?>"<?= (int) ($filters['per_page'] ?? 15) === $option ? ' selected' : '' ?>><?= $option ?> / halaman</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-3 col-md-2">
            <label class="form-label d-none d-sm-block">&nbsp;</label>
            <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search"></i> Terapkan</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <?php if (! empty($messages)): ?>
          <form id="contactBulkForm" method="post" action="<?= site_url('admin/contacts/bulk/status') ?>" class="d-flex flex-column gap-3">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= esc($redirectUrl) ?>">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0" id="contactsTable">
                <thead>
                  <tr>
                    <th style="width: 36px;">
                      <input class="form-check-input" type="checkbox" value="1" data-select-all>
                    </th>
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
                  <?php foreach ($messages as $item): ?>
                    <?php
                      $createdAt   = ! empty($item['created_at']) ? Time::parse($item['created_at']) : null;
                      $respondedAt = ! empty($item['responded_at']) ? Time::parse($item['responded_at']) : null;
                      $statusKey   = $item['status'] ?? 'new';
                      $statusLabel = $statusLabels[$statusKey] ?? ucfirst($statusKey);
                      $badgeClass  = [
                        'new'         => 'bg-label-primary',
                        'in_progress' => 'bg-label-warning',
                        'closed'      => 'bg-label-success',
                      ][$statusKey] ?? 'bg-label-secondary';
                      $phoneNumber  = trim((string) ($item['phone'] ?? ''));
                    ?>
                    <tr>
                      <td>
                        <input class="form-check-input" type="checkbox" name="ids[]" value="<?= esc($item['id']) ?>" data-row-checkbox>
                      </td>
                      <td>
                        <div class="fw-semibold"><?= esc($item['name']) ?></div>
                        <small class="text-muted d-block"><?= esc($item['email']) ?></small>
                        <?php if ($phoneNumber !== ''): ?>
                          <small class="text-muted d-block">Tel: <?= esc($phoneNumber) ?></small>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="fw-semibold text-truncate" style="max-width: 240px;">
                          <?= esc($item['subject'] ?: '(Tanpa subjek)') ?>
                        </div>
                        <small class="text-muted text-truncate" style="max-width: 240px; display: inline-block;">
                          <?= esc(mb_strimwidth(strip_tags((string) $item['message']), 0, 90, '…', 'UTF-8')) ?>
                        </small>
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
                        <?php if (! empty($item['ip_address'])): ?>
                          <div class="text-muted small">IP: <?= esc($item['ip_address']) ?></div>
                        <?php endif; ?>
                        <?php if (! empty($item['user_agent'])): ?>
                          <small class="text-muted d-block text-truncate" style="max-width: 260px;">UA: <?= esc($item['user_agent']) ?></small>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">
                        <a class="btn btn-sm btn-label-primary" href="<?= site_url('admin/contacts/' . $item['id']) ?>">
                          <i class="bx bx-show"></i> Detail
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
              <div class="d-flex gap-2 align-items-center">
                <label class="form-label mb-0" for="bulkStatus">Ubah status terpilih ke</label>
                <select class="form-select" id="bulkStatus" name="status" style="min-width: 160px;">
                  <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary"><i class="bx bx-refresh"></i> Terapkan</button>
              </div>
              <div class="text-muted small">Total <?= esc($pager->getTotal('contacts') ?? count($messages)) ?> pesan</div>
            </div>
          </form>

          <div>
            <?= $pager->links('contacts', 'default_full') ?>
          </div>
        <?php else: ?>
          <div class="text-center py-5">
            <i class="bx bx-envelope-open display-4 text-muted mb-3"></i>
            <p class="mb-1 fw-semibold">Belum ada pesan kontak.</p>
            <p class="text-muted">Formulir publik akan menampilkan data di sini setelah pengunjung mengirim pesan.</p>
          </div>
        <?php endif; ?>
      </div>
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

  if (selectAll) {
    selectAll.addEventListener('change', () => {
      rowCheckboxes.forEach((checkbox) => {
        checkbox.checked = selectAll.checked;
      });
    });
  }

  bulkForm.addEventListener('submit', (event) => {
    const hasSelected = rowCheckboxes.some((checkbox) => checkbox.checked);
    if (!hasSelected) {
      event.preventDefault();
      window.scrollTo({ top: bulkForm.offsetTop - 120, behavior: 'smooth' });
      const alertId = 'bulkSelectionAlert';
      let alertBox = document.getElementById(alertId);
      if (!alertBox) {
        alertBox = document.createElement('div');
        alertBox.id = alertId;
        alertBox.className = 'alert alert-soft-warning';
        alertBox.role = 'alert';
        alertBox.textContent = 'Pilih minimal satu pesan sebelum menerapkan aksi massal.';
        bulkForm.prepend(alertBox);
      }
      return;
    }
  });
})();
</script>
<?= $this->endSection() ?>
