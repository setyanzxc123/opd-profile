<?= $this->extend('layouts/admin') ?>

<?= $this->section('pageStyles') ?>
<style>
.link-item-wrapper {
    margin-bottom: 0.75rem;
    min-height: 60px;
    display: flex;
    align-items: stretch;
}

.link-number {
    width: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.link-item {
    cursor: grab;
    transition: all 0.2s ease;
    padding: 0.75rem;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    width: 100%;
}

.link-item:hover {
    background-color: #f8f9fa;
    border-color: #c7d2fe;
    box-shadow: 0 2px 8px rgba(103, 126, 234, 0.15);
}

.link-item:active {
    cursor: grabbing;
    box-shadow: 0 4px 12px rgba(103, 126, 234, 0.25);
}

.drag-handle {
    color: #6c757d;
    padding: 0.5rem;
    display: flex;
    align-items: center;
}

.drag-handle i {
    font-size: 1.25rem;
}

.sortable-ghost {
    opacity: 0.5;
}

.sortable-ghost .link-item {
    background-color: #eff6ff !important;
    border: 2px dashed #667eea !important;
}

.sortable-chosen .link-item {
    background-color: #faf9ff !important;
    border-color: #667eea !important;
}

.link-logo {
    width: 48px;
    height: 48px;
    object-fit: contain;
    border-radius: 8px;
    background: #f8f9fa;
    padding: 4px;
}

.link-logo-placeholder {
    width: 48px;
    height: 48px;
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
    
    if (linksList && sortForm) {
        new Sortable(linksList, {
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: function(evt) {
                const items = Array.from(linksList.querySelectorAll('.link-item[data-id]'));
                const order = {};
                items.forEach((el, idx) => {
                    order[el.dataset.id] = idx + 1;
                });

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
            <strong>Tips:</strong> Drag icon <i class="bx bx-menu"></i> untuk mengurutkan tautan. Logo yang ditampilkan akan muncul di slider halaman utama.
          </small>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <?php if (!empty($links)): ?>
          <div class="row">
            <!-- Left column: Static numbers -->
            <div class="col-auto pe-2">
              <div id="numbers-list">
                <?php foreach ($links as $index => $link): ?>
                  <div class="link-item-wrapper">
                    <div class="link-number text-muted fw-bold">
                      #<?= $index + 1 ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Right column: Draggable links -->
            <div class="col">
              <div id="links-list">
                <?php foreach ($links as $link): ?>
                  <div class="link-item-wrapper">
                    <div class="link-item" data-id="<?= $link['id'] ?>">
                      <div class="row align-items-center">
                        <div class="col-auto">
                          <span class="drag-handle">
                            <i class="bx bx-menu"></i>
                          </span>
                        </div>
                        <div class="col-auto">
                          <?php if (!empty($link['logo_path'])): ?>
                            <img src="<?= base_url($link['logo_path']) ?>" alt="<?= esc($link['name']) ?>" class="link-logo">
                          <?php else: ?>
                            <div class="link-logo-placeholder">
                              <i class="bx bx-image"></i>
                            </div>
                          <?php endif; ?>
                        </div>
                        <div class="col">
                          <div class="fw-semibold">
                            <?= esc($link['name']) ?>
                            <?php if (!$link['is_active']): ?>
                              <span class="badge badge-inactive ms-2">Nonaktif</span>
                            <?php endif; ?>
                          </div>
                          <small class="text-muted">
                            <a href="<?= esc($link['url']) ?>" target="_blank" rel="noopener" class="text-decoration-none">
                              <?= esc(substr($link['url'], 0, 50)) ?><?= strlen($link['url']) > 50 ? '...' : '' ?>
                              <i class="bx bx-link-external"></i>
                            </a>
                          </small>
                        </div>
                        <div class="col-auto">
                          <div class="d-inline-flex gap-1">
                            <form method="post" action="<?= site_url('admin/app-links/toggle/' . $link['id']) ?>" class="m-0">
                              <?= csrf_field() ?>
                              <button type="submit" class="btn btn-sm btn-outline-<?= $link['is_active'] ? 'warning' : 'success' ?>" title="<?= $link['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                <i class="bx bx-<?= $link['is_active'] ? 'hide' : 'show' ?>"></i>
                              </button>
                            </form>
                            <a href="<?= site_url('admin/app-links/edit/' . $link['id']) ?>" 
                               class="btn btn-sm btn-outline-secondary">
                              <i class="bx bx-edit"></i> Ubah
                            </a>
                            <form method="post" action="<?= site_url('admin/app-links/delete/' . $link['id']) ?>" 
                                  onsubmit="return confirm('Hapus tautan ini?')" class="m-0">
                              <?= csrf_field() ?>
                              <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bx bx-trash"></i> Hapus
                              </button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
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
