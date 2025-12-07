<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
  $isEdit = !empty($link['id']);
  $formAction = $isEdit 
    ? site_url('admin/app-links/update/' . $link['id']) 
    : site_url('admin/app-links');
?>

<div class="row">
  <div class="col-12 col-lg-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= $isEdit ? 'Edit Tautan Aplikasi' : 'Tambah Tautan Aplikasi' ?></h5>
      </div>
      <div class="card-body">
        <?php if (session()->getFlashdata('errors')): ?>
          <div class="alert alert-danger alert-dismissible" role="alert">
            <?php foreach ((array) session('errors') as $error): ?>
              <div><?= esc($error) ?></div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= $formAction ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <!-- Nama Aplikasi -->
          <div class="mb-3">
            <label for="name" class="form-label">Nama Aplikasi <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>" 
                   id="name" 
                   name="name" 
                   value="<?= old('name', $link['name'] ?? '') ?>"
                   placeholder="Contoh: SIPD Kota Jakarta"
                   maxlength="255"
                   required>
            <?php if (session('errors.name')): ?>
              <div class="invalid-feedback"><?= session('errors.name') ?></div>
            <?php endif; ?>
          </div>

          <!-- Deskripsi -->
          <div class="mb-3">
            <label for="description" class="form-label">Deskripsi (opsional)</label>
            <textarea class="form-control" 
                      id="description" 
                      name="description" 
                      rows="2"
                      placeholder="Deskripsi singkat aplikasi"><?= old('description', $link['description'] ?? '') ?></textarea>
            <div class="form-text">Deskripsi akan muncul saat hover pada logo</div>
          </div>

          <!-- URL -->
          <div class="mb-3">
            <label for="url" class="form-label">URL Aplikasi <span class="text-danger">*</span></label>
            <input type="url" 
                   class="form-control <?= session('errors.url') ? 'is-invalid' : '' ?>" 
                   id="url" 
                   name="url" 
                   value="<?= old('url', $link['url'] ?? '') ?>"
                   placeholder="https://example.com"
                   required>
            <?php if (session('errors.url')): ?>
              <div class="invalid-feedback"><?= session('errors.url') ?></div>
            <?php endif; ?>
          </div>

          <!-- Logo Upload -->
          <div class="mb-3">
            <label for="logo" class="form-label">Logo Aplikasi</label>
            <?php if (!empty($link['logo_path'])): ?>
              <div class="mb-2">
                <img src="<?= base_url($link['logo_path']) ?>" alt="Current logo" style="max-height: 80px; max-width: 200px;" class="rounded border p-1">
              </div>
              <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input" id="remove_logo" name="remove_logo" value="1">
                <label class="form-check-label" for="remove_logo">Hapus logo saat ini</label>
              </div>
            <?php endif; ?>
            <input type="file" 
                   class="form-control" 
                   id="logo" 
                   name="logo" 
                   accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp">
            <div class="form-text">Format: PNG, JPG, GIF, SVG, atau WebP. Ukuran maksimal 2MB. Disarankan logo transparan dengan rasio 1:1</div>
          </div>

          <!-- Status -->
          <div class="mb-4">
            <div class="form-check form-switch">
              <input type="checkbox" 
                     class="form-check-input" 
                     id="is_active" 
                     name="is_active" 
                     value="1"
                     <?= old('is_active', $link['is_active'] ?? 1) ? 'checked' : '' ?>>
              <label class="form-check-label" for="is_active">Aktif (tampil di halaman utama)</label>
            </div>
          </div>

          <!-- Actions -->
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bx bx-save"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Simpan' ?>
            </button>
            <a href="<?= site_url('admin/app-links') ?>" class="btn btn-outline-secondary">
              <i class="bx bx-x"></i> Batal
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Preview Panel -->
  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Pratinjau</h6>
      </div>
      <div class="card-body text-center">
        <div class="p-3 bg-light rounded mb-3">
          <div id="preview-logo" style="width: 80px; height: 80px; margin: 0 auto; display: flex; align-items: center; justify-content: center; background: #fff; border-radius: 12px; border: 1px solid #eee;">
            <?php if (!empty($link['logo_path'])): ?>
              <img src="<?= base_url($link['logo_path']) ?>" alt="Preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">
            <?php else: ?>
              <i class="bx bx-image text-muted" style="font-size: 2rem;"></i>
            <?php endif; ?>
          </div>
        </div>
        <div id="preview-name" class="fw-semibold"><?= esc($link['name'] ?? 'Nama Aplikasi') ?></div>
        <div id="preview-desc" class="text-muted small"><?= esc($link['description'] ?? 'Deskripsi aplikasi') ?></div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const nameInput = document.getElementById('name');
  const descInput = document.getElementById('description');
  const logoInput = document.getElementById('logo');
  const previewLogo = document.getElementById('preview-logo');
  const previewName = document.getElementById('preview-name');
  const previewDesc = document.getElementById('preview-desc');

  nameInput?.addEventListener('input', () => {
    previewName.textContent = nameInput.value || 'Nama Aplikasi';
  });

  descInput?.addEventListener('input', () => {
    previewDesc.textContent = descInput.value || 'Deskripsi aplikasi';
  });

  logoInput?.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (ev) => {
        previewLogo.innerHTML = `<img src="${ev.target.result}" alt="Preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">`;
      };
      reader.readAsDataURL(file);
    }
  });
});
</script>
<?= $this->endSection() ?>
