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
                    <input type="hidden" name="button_text" value="Selengkapnya">
                    <label for="button_link" class="form-label">Link Tombol</label>
                    <input type="url" 
                           class="form-control" 
                           id="button_link" 
                           name="button_link"
                           value="<?= old('button_link', $slider['button_link'] ?? '') ?>"
                           placeholder="https://example.com">
                    <div class="form-text">URL tujuan saat tombol "Selengkapnya" diklik</div>
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
                </div>
              </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
              <?php if (!empty($newsItems)): ?>
              <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                  <h5 class="fw-semibold mb-3">Sumber Konten</h5>
                  
                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="use_internal_source" 
                           <?= old('source_type', $slider['source_type'] ?? 'manual') === 'internal' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="use_internal_source">
                      Gunakan sumber dari Berita
                    </label>
                  </div>
                  
                  <div id="news_source_wrapper">
                    <label for="news_search" class="form-label">Cari Berita</label>
                    <input type="text" 
                           class="form-control mb-2" 
                           id="news_search" 
                           placeholder="Ketik untuk mencari berita..." 
                           disabled>
                    
                    <select class="form-select" id="prefill_news" disabled size="6" style="height: auto; max-height: 180px; overflow-y: auto;">
                      <option value="">-- Pilih Berita --</option>
                      <?php foreach ($newsItems as $news): ?>
                        <option value="<?= $news['id'] ?>"><?= esc($news['title']) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <div class="form-text"><span id="news_count"><?= count($newsItems) ?></span> berita ditemukan</div>
                    
                    <!-- News Image Preview -->
                    <div id="news_image_preview" class="mt-3" style="display: none;">
                      <label class="form-label small text-muted">Preview Gambar Berita</label>
                      <img id="news_thumbnail" src="" alt="Preview" 
                           style="width:100%;max-height:150px;object-fit:cover;border-radius:0.35rem;border:1px solid #dee2e6">
                      <div class="form-text text-success mt-1">
                        <i class="bx bx-check-circle"></i> Gambar ini dapat digunakan untuk slider
                      </div>
                    </div>
                  </div>
                  
                  <input type="hidden" name="source_type" id="source_type_input" value="<?= old('source_type', $slider['source_type'] ?? 'manual') ?>">
                </div>
              </div>
              <?php endif; ?>

              <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                  <h5 class="fw-semibold mb-3">Aksi</h5>

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
    const newsItems = <?= json_encode($newsItems ?? [], JSON_NUMERIC_CHECK) ?>;
    const useInternalCheckbox = document.getElementById('use_internal_source');
    const prefillSelect = document.getElementById('prefill_news');
    const newsSearch = document.getElementById('news_search');
    const newsCount = document.getElementById('news_count');
    const sourceTypeInput = document.getElementById('source_type_input');
    const titleInput = document.getElementById('title');
    const buttonLinkInput = document.getElementById('button_link');
    const newsImagePreview = document.getElementById('news_image_preview');
    const newsThumbnail = document.getElementById('news_thumbnail');
    const baseUrl = '<?= base_url() ?>';

    function showImagePreview(thumbnailPath) {
        if (!newsImagePreview || !newsThumbnail) return;
        
        if (thumbnailPath) {
            const imageUrl = thumbnailPath.startsWith('http') ? thumbnailPath : baseUrl + thumbnailPath;
            newsThumbnail.src = imageUrl;
            newsImagePreview.style.display = 'block';
        } else {
            hideImagePreview();
        }
    }
    
    function hideImagePreview() {
        if (!newsImagePreview) return;
        newsImagePreview.style.display = 'none';
        if (newsThumbnail) newsThumbnail.src = '';
    }

    function filterNews(query) {
        if (!prefillSelect) return;
        
        const lowerQuery = query.toLowerCase().trim();
        let visibleCount = 0;

        prefillSelect.innerHTML = '<option value="">-- Pilih Berita --</option>';
        
        newsItems.forEach(news => {
            if (!lowerQuery || news.title.toLowerCase().includes(lowerQuery)) {
                const option = document.createElement('option');
                option.value = news.id;
                option.textContent = news.title;
                prefillSelect.appendChild(option);
                visibleCount++;
            }
        });
        
        if (newsCount) {
            newsCount.textContent = visibleCount;
        }
    }

    function toggleNewsDropdown() {
        if (!useInternalCheckbox || !prefillSelect) return;
        
        const isChecked = useInternalCheckbox.checked;

        prefillSelect.disabled = !isChecked;
        if (newsSearch) newsSearch.disabled = !isChecked;

        if (sourceTypeInput) {
            sourceTypeInput.value = isChecked ? 'internal' : 'manual';
        }

        if (!isChecked) {
            prefillSelect.value = '';
            if (newsSearch) newsSearch.value = '';
            filterNews('');
            hideImagePreview();
        }
    }

    let searchTimeout;
    if (newsSearch) {
        newsSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterNews(this.value);
            }, 200);
        });
    }

    toggleNewsDropdown();

    if (useInternalCheckbox) {
        useInternalCheckbox.addEventListener('change', toggleNewsDropdown);
    }

    if (prefillSelect && newsItems.length > 0) {
        prefillSelect.addEventListener('change', function() {
            if (!useInternalCheckbox?.checked) return;
            
            const selectedId = parseInt(this.value, 10);

            if (!this.value) {
                hideImagePreview();
                return;
            }
            
            const item = newsItems.find(n => parseInt(n.id, 10) === selectedId);
            
            if (item) {
                const filledFields = [];
                
                if (titleInput) {
                    titleInput.value = item.title || '';
                    filledFields.push(titleInput);
                }
                
                if (buttonLinkInput) {
                    const slug = item.slug || '';
                    buttonLinkInput.value = slug ? '<?= site_url('berita/') ?>' + slug : '';
                    filledFields.push(buttonLinkInput);
                }

                if (item.thumbnail) {
                    showImagePreview(item.thumbnail);
                } else {
                    hideImagePreview();
                }

                filledFields.forEach(field => {
                    field.classList.add('is-valid');
                    setTimeout(() => field.classList.remove('is-valid'), 2000);
                });
                hideImagePreview();
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
