<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row g-4">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 pb-3 mb-4 border-bottom">
          <div>
            <h4 class="fw-bold mb-1"><?= esc($title ?? 'Form Hero Slider') ?></h4>
            <p class="text-muted small mb-0">Kelola konten slider yang tampil di halaman utama</p>
          </div>
          <a href="<?= site_url('admin/hero-sliders') ?>" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
          </a>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
          <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h6 class="alert-heading mb-2">
              <i class="bx bx-error-circle me-1"></i> Periksa kembali data yang diisi
            </h6>
            <ul class="mb-0 ps-3">
              <?php foreach ((array) session('errors') as $error): ?>
                <li><?= esc($error) ?></li>
              <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <?php 
          $actionUrl = isset($slider['id'])
              ? site_url('admin/hero-sliders/update/' . $slider['id'])
              : site_url('admin/hero-sliders');
        ?>

        <form method="post" action="<?= $actionUrl ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
              <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                  <h5 class="fw-semibold mb-3">Informasi Slider</h5>

                  <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">
                      Judul <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="title" 
                           name="title"
                           value="<?= old('title', $slider['title'] ?? '') ?>"
                           required 
                           maxlength="255"
                           placeholder="Masukkan judul slider">
                  </div>

                  <div class="mb-3">
                    <label for="subtitle" class="form-label">Subjudul</label>
                    <input type="text" 
                           class="form-control" 
                           id="subtitle" 
                           name="subtitle"
                           value="<?= old('subtitle', $slider['subtitle'] ?? '') ?>"
                           maxlength="255"
                           placeholder="Subjudul (opsional)">
                  </div>

                  <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control" 
                              id="description" 
                              name="description"
                              rows="3"
                              maxlength="1000"
                              placeholder="Deskripsi lengkap (opsional)"><?= old('description', $slider['description'] ?? '') ?></textarea>
                    <div class="form-text">Maksimal 1000 karakter</div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="button_text" class="form-label">Teks Tombol</label>
                      <input type="text" 
                             class="form-control" 
                             id="button_text" 
                             name="button_text"
                             value="<?= old('button_text', $slider['button_text'] ?? 'Selengka pnya') ?>"
                             maxlength="50">
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="button_link" class="form-label">Link Tombol</label>
                      <input type="url" 
                             class="form-control" 
                             id="button_link" 
                             name="button_link"
                             value="<?= old('button_link', $slider['button_link'] ?? '') ?>"
                             placeholder="https://example.com">
                    </div>
                  </div>
                </div>
              </div>

              <div class="card shadow-sm">
                <div class="card-body p-4">
                  <h5 class="fw-semibold mb-3">Gambar Slider</h5>

                  <div class="row">
                    <div class="col-md-8 mb-3">
                      <label for="image_file" class="form-label">
                        Upload Gambar <?= empty($slider['id']) ? '<span class="text-danger">*</span>' : '' ?>
                      </label>
                      <input type="file" 
                             class="form-control" 
                             id="image_file" 
                             name="image_file"
                             accept="image/jpeg,image/jpg,image/png,image/webp"
                             <?= empty($slider['id']) ? 'required' : '' ?>>
                      <div class="form-text">
                        Format: JPG, PNG, WebP • Max: <?= ($config->maxImageSize / 1000000) ?>MB • 
                        Min: <?= $config->minImageWidth ?>x<?= $config->minImageHeight ?>px
                      </div>
                    </div>

                    <?php if (!empty($slider['image_path'])): ?>
                    <div class="col-md-4 mb-3">
                      <label class="form-label d-block">Preview</label>
                      <img src="<?= base_url($slider['image_path']) ?>" 
                           alt="Preview" 
                           style="width:100%;max-height:150px;object-fit:cover;border-radius:0.35rem;border:1px solid #dee2e6">
                    </div>
                    <?php endif; ?>
                  </div>

                  <div class="mb-0">
                    <label for="image_alt" class="form-label">Alt Text</label>
                    <input type="text" 
                           class="form-control" 
                           id="image_alt" 
                           name="image_alt"
                           value="<?= old('image_alt', $slider['image_alt'] ?? '') ?>"
                           maxlength="255"
                           placeholder="Deskripsi gambar untuk SEO">
                    <div class="form-text">Untuk aksesibilitas dan SEO</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
              <?php if (empty($slider['id']) && !empty($newsItems)): ?>
              <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                  <h5 class="fw-semibold mb-3">Isi Cepat</h5>
                  <label for="prefill_news" class="form-label">Pilih dari Berita</label>
                  <select class="form-select" id="prefill_news">
                    <option value="">-- Pilih Berita --</option>
                    <?php foreach ($newsItems as $news): ?>
                      <option value="<?= $news['id'] ?>"><?= esc($news['title']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-text">Auto-fill judul dan link dari berita</div>
                </div>
              </div>
              <?php endif; ?>

              <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                  <h5 class="fw-semibold mb-3">Aksi</h5>

                  <input type="hidden" name="source_type" value="manual">
                  <input type="hidden" name="is_active" value="1">

                  <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                      <i class="bx bx-save me-1"></i> Simpan
                    </button>
                    <a href="<?= site_url('admin/hero-sliders') ?>" class="btn btn-outline-secondary">
                      Batal
                    </a>
                  </div>
                </div>
              </div>

              <?php if (!empty($slider['id'])): ?>
              <div class="card shadow-sm">
                <div class="card-body p-4">
                  <h5 class="fw-semibold mb-3">Informasi</h5>
                  <dl class="mb-0">
                    <div class="mb-2">
                      <dt class="small text-muted">Dibuat</dt>
                      <dd class="mb-0"><?= date('d M Y', strtotime($slider['created_at'])) ?></dd>
                    </div>
                    <div class="mb-2">
                      <dt class="small text-muted">Diupdate</dt>
                      <dd class="mb-0"><?= date('d M Y', strtotime($slider['updated_at'])) ?></dd>
                    </div>
                    <div>
                      <dt class="small text-muted">Views</dt>
                      <dd class="mb-0"><?= number_format($slider['view_count'] ?? 0) ?></dd>
                    </div>
                  </dl>
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const newsItems = <?= json_encode($newsItems ?? []) ?>;
    const prefillSelect = document.getElementById('prefill_news');
    const titleInput = document.getElementById('title');
    const buttonLinkInput = document.getElementById('button_link');

    if (prefillSelect) {
        prefillSelect.addEventListener('change', function() {
            const item = newsItems.find(n => n.id == this.value);
            if (item) {
                if (titleInput) titleInput.value = item.title || '';
                if (buttonLinkInput) buttonLinkInput.value = item.slug ? 
                    '<?= site_url('berita/') ?>' + item.slug : '';
                this.value = '';
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
