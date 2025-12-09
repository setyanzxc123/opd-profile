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
                <input type="text" id="serviceTitle" name="title" class="form-control form-control-lg" required maxlength="150" value="<?= esc(old('title', $item['title'])) ?>" placeholder="Masukkan judul layanan">
                <div class="form-text">Slug URL akan dibuat otomatis dari judul.</div>
                <?php if (isset($validation) && $validation->hasError('title')): ?>
                  <div class="form-text text-danger"><?= esc($validation->getError('title')) ?></div>
                <?php endif; ?>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold" for="serviceSummary">Ringkasan Singkat</label>
                <textarea id="serviceSummary" name="description" class="form-control" rows="3" placeholder="Tuliskan deskripsi singkat yang akan tampil di kartu layanan"><?= esc(old('description', $item['description'])) ?></textarea>
                <?php if (isset($validation) && $validation->hasError('description')): ?>
                  <div class="form-text text-danger"><?= esc($validation->getError('description')) ?></div>
                <?php endif; ?>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold" for="serviceContent">Isi Detail <span class="text-danger">*</span></label>
                <textarea id="serviceContent" name="content" class="form-control" rows="12" placeholder="Deskripsi lengkap layanan"><?= old('content', $item['content']) ?></textarea>
                <div class="form-text">Gunakan toolbar untuk memformat teks, menambahkan list, tabel, atau tautan.</div>
                <?php if (isset($validation) && $validation->hasError('content')): ?>
                  <div class="form-text text-danger"><?= esc($validation->getError('content')) ?></div>
                <?php endif; ?>
                
                <!-- Content Stats -->
                <div class="d-flex flex-wrap gap-4 mt-3 text-muted small">
                  <div><span class="fw-semibold">Kata:</span> <span id="wordCount">0</span></div>
                  <div><span class="fw-semibold">Karakter:</span> <span id="characterCount">0</span></div>
                  <div><span class="fw-semibold">Est. Baca:</span> <span id="readingTime">--</span></div>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="card border rounded-4 shadow-sm bg-light">
                <div class="card-body">
                  <h6 class="fw-semibold mb-3">Pengaturan</h6>

                  <div class="mb-3">
                    <label class="form-label fw-semibold d-block" for="serviceIcon">Icon Layanan</label>
                    <div class="d-flex align-items-center gap-3">
                      <div class="flex-shrink-0">
                          <?php if (! empty($item['icon'])): ?>
                              <img src="<?= base_url($item['icon']) ?>" alt="Icon" class="rounded p-1 border" style="width: 48px; height: 48px; object-fit: contain; background: #f8f9fa;">
                          <?php else: ?>
                              <div class="rounded p-1 border d-flex align-items-center justify-content-center bg-light text-muted" style="width: 48px; height: 48px;">
                                  <i class="bx bx-image-alt fs-4"></i>
                              </div>
                          <?php endif; ?>
                      </div>
                      <div class="flex-grow-1">
                          <input type="file" id="serviceIcon" name="icon" class="form-control" accept=".jpg,.jpeg,.png,.webp,.svg">
                          <div class="form-text">Format: PNG, JPG, SVG. Maks 2MB. Disarankan persegi transparan.</div>
                      </div>
                    </div>
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
<script src="<?= base_url('assets/vendor/tinymce/js/tinymce/tinymce.min.js') ?>"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const wordCountEl = document.getElementById('wordCount');
    const characterCountEl = document.getElementById('characterCount');
    const readingTimeEl = document.getElementById('readingTime');

    // Content stats update
    const updateContentStats = () => {
      const editor = tinymce.get('serviceContent');
      if (!editor) return;
      const text = editor.getContent({ format: 'text' }).trim();
      const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
      const characters = text.replace(/\s/g, '').length;
      const minutes = words ? Math.max(1, Math.round(words / 200)) : 0;

      if (wordCountEl) wordCountEl.textContent = `${words} kata`;
      if (characterCountEl) characterCountEl.textContent = characters.toLocaleString('id-ID');
      if (readingTimeEl) {
        readingTimeEl.textContent = minutes ? `${minutes} menit` : '--';
      }
    };

    // Initialize TinyMCE
    tinymce.init({
      selector: '#serviceContent',
      branding: false,
      promotion: false,
      height: 450,
      menubar: 'file edit view insert format tools table help',
      toolbar_sticky: true,
      toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table link | removeformat | fullscreen preview code',
      plugins: 'preview searchreplace autolink autosave save code visualblocks visualchars fullscreen link table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount help',
      autosave_interval: '30s',
      autosave_restore_when_empty: true,
      autosave_retention: '2m',
      content_style: 'body { font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif; font-size: 16px; line-height: 1.7; }',
      table_default_attributes: { class: 'table table-striped table-sm' },
      language: 'id',
      language_url: '<?= base_url('assets/vendor/tinymce/langs/id.js') ?>',
      setup: function(editor) {
        editor.on('init', function() {
          updateContentStats();
        });
        editor.on('change keyup setcontent', updateContentStats);
      }
    });

    // Form submission handler
    const serviceForm = document.querySelector('form[action*="services"]');
    const submitBtn = serviceForm ? serviceForm.querySelector('button[type="submit"]') : null;

    if (serviceForm) {
      serviceForm.addEventListener('submit', function(e) {
        // Save TinyMCE content to textarea
        const editor = tinymce.get('serviceContent');
        if (editor) {
          try {
            editor.save();
          } catch (err) {
            console.error('[Service Form] Error saving editor content:', err);
          }
        }

        // Update button to show loading state
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Menyimpan...';
        }

        return true;
      });
    }
  });
</script>
<?= $this->endSection() ?>
