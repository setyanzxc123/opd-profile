<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold mb-0"><?= esc($title ?? 'Form Dokumen') ?></h4>
          <a href="<?= site_url('admin/documents') ?>" class="btn btn-sm btn-outline-secondary"><i class="bx bx-arrow-back"></i> Kembali</a>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" action="<?= $mode === 'edit' ? site_url('admin/documents/update/'.$item['id']) : site_url('admin/documents') ?>">
          <?= csrf_field() ?>

          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Judul <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control" required value="<?= esc(old('title', $item['title'])) ?>">
              <?php if (isset($validation) && $validation->hasError('title')): ?>
                <div class="form-text text-danger"><?= esc($validation->getError('title')) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-4">
              <label class="form-label">Tahun</label>
              <input type="number" name="year" min="1900" max="2099" step="1" class="form-control" value="<?= esc(old('year', $item['year'])) ?>" placeholder="YYYY">
              <?php if (isset($validation) && $validation->hasError('year')): ?>
                <div class="form-text text-danger"><?= esc($validation->getError('year')) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <label class="form-label">Kategori</label>
              <input type="text" name="category" class="form-control" value="<?= esc(old('category', $item['category'])) ?>" placeholder="Misal: SK, Laporan, SOP">
              <?php if (isset($validation) && $validation->hasError('category')): ?>
                <div class="form-text text-danger"><?= esc($validation->getError('category')) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <label class="form-label">Berkas <?= $mode === 'create' ? '<span class="text-danger">*</span>' : '' ?></label>
              <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip" <?= $mode === 'create' ? 'required' : '' ?>>
              <?php if (!empty($item['file_path'])): ?>
                <div class="form-text">Saat ini: <a target="_blank" href="<?= base_url($item['file_path']) ?>">unduh</a></div>
              <?php endif; ?>
              <?php if (isset($validation) && $validation->hasError('file')): ?>
                <div class="form-text text-danger"><?= esc($validation->getError('file')) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

