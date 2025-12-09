<?= $this->extend('layouts/admin') ?>

<?= $this->section('pageStyles') ?>
<style>
.link-row {
    cursor: grab;
    transition: background-color 0.2s ease;
}

.link-row:hover {
    background-color: #f8f9fa;
}

.link-row:active {
    cursor: grabbing;
}

.drag-handle {
    color: #9ca3af;
    cursor: grab;
    padding: 0.5rem;
    display: inline-flex;
    align-items: center;
}

.drag-handle:hover {
    color: #6b7280;
}

.drag-handle i {
    font-size: 1.25rem;
}

.sortable-ghost {
    opacity: 0.4;
    background-color: #eff6ff !important;
}

.sortable-chosen {
    background-color: #faf9ff !important;
    box-shadow: 0 2px 8px rgba(103, 126, 234, 0.2);
}

.link-row:active .drag-handle {
    cursor: grabbing;
}

.link-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 8px;
    background: #f8f9fa;
    padding: 4px;
}

.link-logo-placeholder {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
    border-radius: 8px;
    color: #999;
}

.badge-inactive {
    background-color: #fef3c7;
    color: #92400e;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const linksList = document.getElementById('links-list');
    const sortForm = document.getElementById('sort-order-form');
    const sortInput = document.getElementById('sort-order-data');
    
    function updateRowNumbers() {
        const rows = linksList.querySelectorAll('.link-row');
        rows.forEach((row, idx) => {
            const numberSpan = row.querySelector('.row-number');
            if (numberSpan) {
                numberSpan.textContent = idx + 1;
            }
        });
    }
    
    if (linksList && sortForm && sortInput) {
        new Sortable(linksList, {
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            draggable: 'tr.link-row',
            
            onEnd: function(evt) {
                updateRowNumbers();
                
                const rows = Array.from(linksList.querySelectorAll('tr.link-row'));
                const order = {};
                
                rows.forEach((row, idx) => {
                    if (row.dataset.id) {
                        order[row.dataset.id] = idx + 1;
                    }
                });

                if (Object.keys(order).length === 0) {
                    return;
                }

                sortInput.value = JSON.stringify(order);
                sortForm.submit();
            }
        });
    }
});
</script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row g-4">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div>
        <h4 class="fw-bold mb-0">Tautan Aplikasi</h4>
        <p class="text-muted mb-0 small">Kelola tautan aplikasi OPD terkait yang tampil di halaman utama</p>
      </div>
      <a class="btn btn-primary" href="<?= site_url('admin/app-links/create') ?>">
        <i class="bx bx-plus"></i> Tambah
      </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible" role="alert">
        <?= esc(session('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
      <div class="alert alert-danger alert-dismissible" role="alert">
        <?php foreach ((array) session('errors') as $error): ?>
          <div><?= esc($error) ?></div>
        <?php endforeach; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
    <?php endif; ?>

    <div class="card">
        <!-- Hidden form for sort order -->
        <form id="sort-order-form" method="post" action="<?= site_url('admin/app-links/sort-order') ?>" style="display: none;">
          <?= csrf_field() ?>
          <input type="hidden" id="sort-order-data" name="order" value="">
        </form>

      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
        <div>
          <span class="text-muted small">Pengaturan Tampilan</span>
        </div>
        <form method="post" action="<?= site_url('admin/app-links/toggle-section') ?>" class="d-flex align-items-center gap-2">
          <?= csrf_field() ?>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" 
                   type="checkbox" 
                   id="show_app_links" 
                   name="show_app_links" 
                   value="1"
                   onchange="this.form.submit()"
                   <?= ($showAppLinks ?? true) ? 'checked' : '' ?>>
            <label class="form-check-label small" for="show_app_links">
              Tampilkan di halaman utama
            </label>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="alert alert-info alert-dismissible" role="alert">
          <small>
            <i class="bx bx-info-circle"></i>
            <strong>Tips:</strong> Tarik baris untuk mengurutkan tautan. Logo akan muncul di slider halaman utama.
          </small>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <?php if (!empty($links)): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width: 50px;" class="text-center">#</th>
                  <th style="width: 50px;"></th>
                  <th style="width: 60px;">Logo</th>
                  <th>Nama & URL</th>
                  <th style="width: 80px;" class="text-center">Status</th>
                  <th style="width: 180px;" class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody id="links-list">
                <?php foreach ($links as $index => $link): ?>
                <tr class="link-row" data-id="<?= $link['id'] ?>">
                  <td class="text-center">
                    <span class="row-number text-muted fw-bold"><?= $index + 1 ?></span>
                  </td>
                  <td>
                    <span class="drag-handle">
                      <i class="bx bx-menu"></i>
                    </span>
                  </td>
                  <td>
                    <?php if (!empty($link['logo_path'])): ?>
                      <img src="<?= base_url($link['logo_path']) ?>" alt="<?= esc($link['name']) ?>" class="link-logo">
                    <?php else: ?>
                      <div class="link-logo-placeholder">
                        <i class="bx bx-image"></i>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="fw-semibold"><?= esc($link['name']) ?></div>
                    <small class="text-muted text-truncate d-block" style="max-width: 250px;">
                      <a href="<?= esc($link['url']) ?>" target="_blank" rel="noopener" class="text-decoration-none">
                        <?= esc(substr($link['url'], 0, 40)) ?><?= strlen($link['url']) > 40 ? '...' : '' ?>
                        <i class="bx bx-link-external"></i>
                      </a>
                    </small>
                  </td>
                  <td class="text-center">
                    <?php if ($link['is_active']): ?>
                      <span class="badge bg-success">Aktif</span>
                    <?php else: ?>
                      <span class="badge badge-inactive">Nonaktif</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-1">
                      <form method="post" action="<?= site_url('admin/app-links/toggle/' . $link['id']) ?>" class="m-0">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-<?= $link['is_active'] ? 'warning' : 'success' ?>" title="<?= $link['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                          <i class="bx bx-<?= $link['is_active'] ? 'hide' : 'show' ?>"></i>
                        </button>
                      </form>
                      <a href="<?= site_url('admin/app-links/edit/' . $link['id']) ?>" 
                         class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-edit"></i>
                      </a>
                      <form method="post" action="<?= site_url('admin/app-links/delete/' . $link['id']) ?>" 
                            onsubmit="return confirm('Hapus tautan ini?')" class="m-0">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                          <i class="bx bx-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-5">
            <p class="text-muted">Belum ada tautan aplikasi. Tambahkan tautan pertama untuk mulai menampilkan slider di halaman utama.</p>
            <a href="<?= site_url('admin/app-links/create') ?>" class="btn btn-primary">
              <i class="bx bx-plus"></i> Tambah Tautan Pertama
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
