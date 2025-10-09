<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold mb-0"><?= esc($title ?? 'Form Galeri') ?></h4>
          <a href="<?= site_url('admin/galleries') ?>" class="btn btn-sm btn-outline-secondary"><i class="bx bx-arrow-back"></i> Kembali</a>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" action="<?= $mode === 'edit' ? site_url('admin/galleries/update/'.$item['id']) : site_url('admin/galleries') ?>">
          <?= csrf_field() ?>

          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label">Judul <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control" required value="<?= esc(old('title', $item['title'])) ?>">
              <?php if (isset($validation) && $validation->hasError('title')): ?>
                <div class="form-text text-danger"><?= esc($validation->getError('title')) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-9">
              <label class="form-label">Keterangan</label>
              <textarea name="description" rows="3" class="form-control" placeholder="Opsional..."><?= esc(old('description', $item['description'])) ?></textarea>
            </div>
            <div class="col-md-5">
              <label class="form-label">Gambar <?= $mode === 'create' ? '<span class="text-danger">*</span>' : '' ?></label>
              <input type="file" name="image" accept="image/*" class="form-control" <?= $mode === 'create' ? 'required' : '' ?>>
                  <?php if (!empty($item['image_path'])): ?>
                    <div class="form-text">Saat ini: <a target="_blank" href="<?= esc(base_url($item['image_path']), 'attr') ?>">lihat</a></div>
                  <?php endif; ?>
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


