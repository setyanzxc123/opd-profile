<?= $this->extend('layouts/admin') ?>

<?= $this->section('pageStyles') ?>
<style>
/* Table row styling */
.slide-row {
    cursor: grab;
    transition: background-color 0.2s ease;
}

.slide-row:hover {
    background-color: #f8f9fa;
}

.slide-row:active {
    cursor: grabbing;
}

/* Drag handle */
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

/* Sortable states */
.sortable-ghost {
    opacity: 0.4;
    background-color: #eff6ff !important;
}

.sortable-chosen {
    background-color: #faf9ff !important;
    box-shadow: 0 2px 8px rgba(103, 126, 234, 0.2);
}

/* Row being dragged */
.slide-row:active .drag-handle {
    cursor: grabbing;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const slidesList = document.getElementById('slides-list');
    const sortForm = document.getElementById('sort-order-form');
    const sortInput = document.getElementById('sort-order-data');
    
    function updateRowNumbers() {
        const rows = slidesList.querySelectorAll('.slide-row');
        rows.forEach((row, idx) => {
            const numberSpan = row.querySelector('.row-number');
            if (numberSpan) {
                numberSpan.textContent = idx + 1;
            }
        });
    }
    
    if (slidesList && sortForm && sortInput) {
        new Sortable(slidesList, {
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            draggable: 'tr.slide-row',
            
            onEnd: function(evt) {
                updateRowNumbers();

                const rows = Array.from(slidesList.querySelectorAll('tr.slide-row'));
                const order = {};
                
                rows.forEach((row, idx) => {
                    if (row.dataset.id) {
                        order[idx] = parseInt(row.dataset.id, 10);
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
        <h4 class="fw-bold mb-0">Hero Slider</h4>
        <p class="text-muted mb-0 small">Kelola slider yang tampil di halaman utama (<?= count($sliders ?? []) ?>/<?= $maxSlots ?? 10 ?> slot)</p>
      </div>
      <a class="btn btn-primary <?= !($canAddMore ?? true) ? 'disabled' : '' ?>" 
         href="<?= site_url('admin/hero-sliders/create') ?>">
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
        <form id="sort-order-form" method="post" action="<?= site_url('admin/hero-sliders/sort-order') ?>" style="display: none;">
          <?= csrf_field() ?>
          <input type="hidden" id="sort-order-data" name="order" value="">
        </form>

      <div class="card-body">
        <?php if ($config->enableDragReorder ?? true): ?>
          <div class="alert alert-info alert-dismissible" role="alert">
            <small>
              <i class="bx bx-info-circle"></i>
              <strong>Tips:</strong> Tarik baris untuk mengurutkan slider.
            </small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <?php if (!empty($sliders)): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width: 50px;" class="text-center">#</th>
                  <th style="width: 50px;"></th>
                  <th>Judul</th>
                  <th style="width: 120px;" class="text-center">Views</th>
                  <th style="width: 180px;" class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody id="slides-list">
                <?php foreach ($sliders as $index => $slider): ?>
                <tr class="slide-row" data-id="<?= $slider['id'] ?>">
                  <td class="text-center">
                    <span class="row-number text-muted fw-bold"><?= $index + 1 ?></span>
                  </td>
                  <td>
                    <span class="drag-handle">
                      <i class="bx bx-menu"></i>
                    </span>
                  </td>
                  <td>
                    <div class="fw-semibold"><?= esc($slider['title']) ?></div>
                    <?php if (!empty($slider['button_link'])): ?>
                      <small class="text-muted text-truncate d-block" style="max-width: 300px;">
                        <i class="bx bx-link-alt"></i> <?= esc($slider['button_link']) ?>
                      </small>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?php if (($slider['view_count'] ?? 0) > 0): ?>
                      <span class="badge bg-light text-dark">
                        <i class="bx bx-show"></i> <?= number_format($slider['view_count']) ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-1">
                      <a href="<?= site_url('admin/hero-sliders/edit/' . $slider['id']) ?>" 
                         class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-edit"></i>
                      </a>
                      <form method="post" action="<?= site_url('admin/hero-sliders/delete/' . $slider['id']) ?>" 
                            onsubmit="return confirm('Hapus slider ini?')" class="m-0">
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
            <p class="text-muted">Belum ada slider. Buat slider pertama untuk memulai.</p>
            <a href="<?= site_url('admin/hero-sliders/create') ?>" class="btn btn-primary">
              <i class="bx bx-plus"></i> Buat Slider Pertama
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>