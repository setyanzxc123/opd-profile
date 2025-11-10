<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 mb-4 border-bottom">
          <div>
            <h4 class="fw-bold mb-1"><?= esc($title ?? 'Form Layanan') ?></h4>
            <p class="text-muted mb-0 small">Masukkan informasi layanan yang akan ditampilkan ke publik.</p>
          </div>
          <a href="<?= site_url('admin/services') ?>" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Kembali</a>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" action="<?= $mode === 'edit' ? site_url('admin/services/update/'.$item['id']) : site_url('admin/services') ?>">
          <?= csrf_field() ?>

          <div class="row g-4">
            <div class="col-lg-8">
              <div class="mb-4">
                <label class="form-label fw-semibold" for="serviceTitle">Judul <span class="text-danger">*</span></label>
                <input type="text" id="serviceTitle" name="title" class="form-control" required maxlength="150" value="<?= esc(old('title', $item['title'])) ?>">
                <?php if (isset($validation) && $validation->hasError('title')): ?>
                  <div class="form-text text-danger"><?= esc($validation->getError('title')) ?></div>
                <?php endif; ?>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold" for="serviceSlug">Slug URL</label>
                <input type="text" id="serviceSlug" name="slug" class="form-control" maxlength="180" placeholder="Contoh: layanan-informasi" value="<?= esc(old('slug', $item['slug'])) ?>">
                <div class="form-text">Slug digunakan sebagai bagian dari URL layanan di halaman publik.</div>
                <?php if (isset($validation) && $validation->hasError('slug')): ?>
                  <div class="form-text text-danger"><?= esc($validation->getError('slug')) ?></div>
                <?php endif; ?>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold" for="serviceSummary">Ringkasan Singkat</label>
                <textarea id="serviceSummary" name="description" class="form-control" rows="3" placeholder="Tuliskan kalimat pembuka"><?= esc(old('description', $item['description'])) ?></textarea>
                <?php if (isset($validation) && $validation->hasError('description')): ?>
                  <div class="form-text text-danger"><?= esc($validation->getError('description')) ?></div>
                <?php endif; ?>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold" for="serviceContent">Isi Detail</label>
                <textarea id="serviceContent" name="content" class="form-control" rows="8" placeholder="Deskripsi lengkap pelayanan"><?= esc(old('content', $item['content'])) ?></textarea>
                <?php if (isset($validation) && $validation->hasError('content')): ?>
                  <div class="form-text text-danger"><?= esc($validation->getError('content')) ?></div>
                <?php endif; ?>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold" for="serviceRequirements">Persyaratan (opsional)</label>
                <textarea id="serviceRequirements" name="requirements" class="form-control" rows="3" placeholder="Pisahkan baris untuk setiap persyaratan"><?= esc(old('requirements', $item['requirements'])) ?></textarea>
                <?php if (isset($validation) && $validation->hasError('requirements')): ?>
                  <div class="form-text text-danger"><?= esc($validation->getError('requirements')) ?></div>
                <?php endif; ?>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold" for="serviceFees">Biaya</label>
                  <input type="text" id="serviceFees" name="fees" class="form-control" maxlength="120" placeholder="Contoh: Gratis" value="<?= esc(old('fees', $item['fees'])) ?>">
                  <?php if (isset($validation) && $validation->hasError('fees')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('fees')) ?></div>
                  <?php endif; ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold" for="serviceProcessingTime">Waktu Proses</label>
                  <input type="text" id="serviceProcessingTime" name="processing_time" class="form-control" maxlength="120" placeholder="Contoh: 3 hari kerja" value="<?= esc(old('processing_time', $item['processing_time'])) ?>">
                  <?php if (isset($validation) && $validation->hasError('processing_time')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('processing_time')) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="card border rounded-4 shadow-sm bg-light">
                <div class="card-body">
                  <h6 class="fw-semibold mb-3">Pengaturan</h6>
                  <div class="mb-3">
                    <label class="form-label fw-semibold" for="serviceOrder">Urutan Tampil</label>
                    <input type="number" min="0" step="1" id="serviceOrder" name="sort_order" class="form-control" value="<?= esc((string) old('sort_order', $item['sort_order'])) ?>">
                    <div class="form-text">Angka lebih kecil akan tampil lebih dulu.</div>
                    <?php if (isset($validation) && $validation->hasError('sort_order')): ?>
                      <div class="form-text text-danger"><?= esc($validation->getError('sort_order')) ?></div>
                    <?php endif; ?>
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold d-block" for="serviceThumbnail">Thumbnail</label>
                    <input type="file" id="serviceThumbnail" name="thumbnail" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif">
                    <div class="form-text">Disarankan rasio 16:9 (maks. 4 MB).</div>
                    <?php if (! empty($item['thumbnail'])): ?>
                      <div class="mt-2">
                        <img src="<?= esc(base_url($item['thumbnail']), 'attr') ?>" alt="Thumbnail saat ini" class="img-fluid rounded-3 border">
                      </div>
                    <?php endif; ?>
                    <?php if (isset($validation) && $validation->hasError('thumbnail')): ?>
                      <div class="form-text text-danger"><?= esc($validation->getError('thumbnail')) ?></div>
                    <?php endif; ?>
                  </div>

                  <div class="mb-0">
                    <label class="form-label fw-semibold d-block">Status</label>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="serviceActive" name="is_active" <?= old('is_active', $item['is_active']) ? 'checked' : '' ?>>
                      <label class="form-check-label" for="serviceActive">Tampilkan di publik</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  (function() {
    const titleInput = document.getElementById('serviceTitle');
    const slugInput = document.getElementById('serviceSlug');
    if (!titleInput || !slugInput) {
      return;
    }

    let slugTouched = slugInput.value.trim() !== '';

    const slugify = (value) => {
      return value
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .substring(0, 180);
    };

    slugInput.addEventListener('input', () => {
      slugTouched = slugInput.value.trim() !== '';
    });

    titleInput.addEventListener('input', () => {
      if (slugTouched) {
        return;
      }
      slugInput.value = slugify(titleInput.value);
    });
  })();
</script>
<?= $this->endSection() ?>
