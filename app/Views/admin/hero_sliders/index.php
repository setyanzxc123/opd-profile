<?= $this->extend('layouts/admin') ?>

<?= $this->section('pageStyles') ?>
<style>
/* Wrapper for alignment */
.slider-number-wrapper,
.slide-item-wrapper {
    margin-bottom: 0.75rem;
    min-height: 60px;
    display: flex;
    align-items: stretch;
}

/* Number styling - matches wrapper height */
.slider-number {
    width: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Slider item styling - with border and better UX */
.slide-item {
    cursor: grab;
    transition: all 0.2s ease;
    padding: 0.75rem;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    width: 100%;
}

.slide-item:hover {
    background-color: #f8f9fa;
    border-color: #c7d2fe;
    box-shadow: 0 2px 8px rgba(103, 126, 234, 0.15);
}

.slide-item:active {
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

.sortable-ghost .slide-item {
    background-color: #eff6ff !important;
    border: 2px dashed #667eea !important;
}

.sortable-chosen .slide-item {
    background-color: #faf9ff !important;
    border-color: #667eea !important;
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
    
    if (slidesList && sortForm) {
        new Sortable(slidesList, {
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            // Drag only slide-item-wrapper (NOT numbers!)
            onEnd: function(evt) {
                // Get new order from slide-items
                const items = Array.from(slidesList.querySelectorAll('.slide-item[data-id]'));
                const order = {};
                items.forEach((el, idx) => {
                    order[idx] = el.dataset.id;
                });

                // Set to hidden input
                sortInput.value = JSON.stringify(order);
                
                // Submit form
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
              <strong>Tips:</strong> Drag icon <i class="bx bx-menu"></i> untuk mengurutkan slider.
            </small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <?php if (!empty($sliders)): ?>
          <div class="row">
            <!-- Left column: Static numbers (NOT draggable) -->
            <div class="col-auto pe-2">
              <div id="numbers-list">
                <?php foreach ($sliders as $index => $slider): ?>
                  <div class="slider-number-wrapper">
                    <div class="slider-number text-muted fw-bold">
                      #<?= $index + 1 ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Right column: Draggable sliders ONLY -->
            <div class="col">
              <div id="slides-list">
                <?php foreach ($sliders as $index => $slider): ?>
                  <div class="slide-item-wrapper">
                    <div class="slide-item" data-id="<?= $slider['id'] ?>">
                      <div class="row align-items-center">
                        <div class="col-auto">
                          <span class="drag-handle">
                            <i class="bx bx-menu"></i>
                          </span>
                        </div>
                        <div class="col">
                          <div class="fw-semibold"><?= esc($slider['title']) ?></div>
                          <small class="text-muted">
                            <?php if (!empty($slider['subtitle'])): ?>
                              <?= esc($slider['subtitle']) ?> •
                            <?php endif; ?>
                            <?php if (($slider['view_count'] ?? 0) > 0): ?>
                              <i class="bx bx-show"></i> <?= number_format($slider['view_count']) ?> views
                            <?php endif; ?>
                          </small>
                        </div>
                        <div class="col-auto">
                          <div class="d-inline-flex gap-1">
                            <a href="<?= site_url('admin/hero-sliders/edit/' . $slider['id']) ?>" 
                               class="btn btn-sm btn-outline-secondary">
                              <i class="bx bx-edit"></i> Ubah
                            </a>
                            <form method="post" action="<?= site_url('admin/hero-sliders/delete/' . $slider['id']) ?>" 
                                  onsubmit="return confirm('Hapus slider ini?')" class="m-0">
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