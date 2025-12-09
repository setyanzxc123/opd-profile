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
                        Upload Gambar <span class="text-danger" id="image_required_mark" style="<?= empty($slider['id']) ? '' : 'display:none' ?>">*</span>
                      </label>
                      <input type="file" 
                             class="form-control" 
                             id="image_file" 
                             name="image_file"
                             accept="image/jpeg,image/jpg,image/png,image/webp"
                             <?= empty($slider['id']) ? 'required' : '' ?>>
                      <div class="form-text" id="image_help_text">
                        Format: JPG, PNG, WebP • Max: <?= ($config->maxImageSize / 1000000) ?>MB • 
                        Min: <?= $config->minImageWidth ?>x<?= $config->minImageHeight ?>px
                      </div>
                      <div class="alert alert-info py-2 px-3 mt-2 mb-0" id="internal_source_info" style="display: none;">
                        <i class="bx bx-info-circle me-1"></i> Gambar akan diambil otomatis dari berita yang dipilih.
                      </div>
                    </div>

                    <div class="col-md-4 mb-3" id="main_preview_container" style="<?= !empty($slider['image_path']) ? '' : 'display:none' ?>">
                      <label class="form-label d-block">Preview</label>
                      <?php
                        $imageSrc = '';
                        if (!empty($slider['image_path'])) {
                            $imageSrc = $slider['image_path'];
                            if (!preg_match('/^https?:\/\//', $imageSrc)) {
                                $imageSrc = base_url($imageSrc);
                            }
                        }
                      ?>
                      <img src="<?= $imageSrc ?>" 
                           id="main_preview_img"
                           data-original-src="<?= $imageSrc ?>"
                           alt="Preview" 
                           style="width:100%;max-height:150px;object-fit:cover;border-radius:0.35rem;border:1px solid #dee2e6">
                    </div>
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
                        <option value="<?= $news['id'] ?>" <?= (isset($slider['source_ref_id']) && $slider['source_ref_id'] == $news['id']) ? 'selected' : '' ?>><?= esc($news['title']) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <div class="form-text"><span id="news_count"><?= count($newsItems) ?></span> berita ditemukan</div>
                  </div>
                  
                  <input type="hidden" name="source_type" id="source_type_input" value="<?= old('source_type', $slider['source_type'] ?? 'manual') ?>">
                  <input type="hidden" name="source_ref_id" id="source_ref_id_input" value="<?= old('source_ref_id', $slider['source_ref_id'] ?? '') ?>">
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
    const sourceRefIdInput = document.getElementById('source_ref_id_input');
    const titleInput = document.getElementById('title');
    const buttonLinkInput = document.getElementById('button_link');
    
    // UI Elements
    const imageFileInput = document.getElementById('image_file');
    const imageHelpText = document.getElementById('image_help_text');
    const internalSourceInfo = document.getElementById('internal_source_info');
    const imageRequiredMark = document.getElementById('image_required_mark');
    
    const mainPreviewContainer = document.getElementById('main_preview_container');
    const mainPreviewImg = document.getElementById('main_preview_img');

    // Ensure base url ends with slash
    let baseUrl = '<?= base_url() ?>';
    if (!baseUrl.endsWith('/')) {
        baseUrl += '/';
    }

    function showMainPreview(thumbnailPath) {
        if (!mainPreviewImg || !mainPreviewContainer) return;
        
        if (thumbnailPath) {
            let imageUrl;
            if (thumbnailPath.match(/^https?:\/\//)) {
                imageUrl = thumbnailPath;
            } else {
                const cleanPath = thumbnailPath.startsWith('/') ? thumbnailPath.substring(1) : thumbnailPath;
                imageUrl = baseUrl + cleanPath;
            }
            mainPreviewImg.src = imageUrl;
            mainPreviewContainer.style.display = 'block';
        } else {
            // If no path provided, check if we have original src to revert to
            const originalSrc = mainPreviewImg.dataset.originalSrc;
            if (originalSrc && !useInternalCheckbox.checked) {
                mainPreviewImg.src = originalSrc;
                mainPreviewContainer.style.display = 'block';
            } else {
                mainPreviewContainer.style.display = 'none';
            }
        }
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
        
        // Handle File Input State
        if (imageFileInput) {
            imageFileInput.disabled = isChecked;
            if (isChecked) {
                imageFileInput.removeAttribute('required');
                if (imageRequiredMark) imageRequiredMark.style.display = 'none';
                if (imageHelpText) imageHelpText.style.display = 'none';
                if (internalSourceInfo) internalSourceInfo.style.display = 'block';
            } else {
                // Only make required if it's a new entry (no ID) logic handled by PHP typically, 
                // but JS can also check. Let's rely on PHP logic for 'required' attribute initial state
                // or just restore if it was originally required.
                // For simplicity: if it's create mode (no original src), make required.
                const originalSrc = mainPreviewImg ? mainPreviewImg.dataset.originalSrc : null;
                if (!originalSrc) {
                     imageFileInput.setAttribute('required', 'required');
                     if (imageRequiredMark) imageRequiredMark.style.display = 'inline';
                }
                
                if (imageHelpText) imageHelpText.style.display = 'block';
                if (internalSourceInfo) internalSourceInfo.style.display = 'none';
            }
        }

        if (sourceTypeInput) {
            sourceTypeInput.value = isChecked ? 'internal' : 'manual';
        }

        if (!isChecked) {
            prefillSelect.value = '';
            if (sourceRefIdInput) sourceRefIdInput.value = '';
            if (newsSearch) newsSearch.value = '';
            filterNews('');
            
            // Revert preview to original image if available
            showMainPreview(null);
        } else {
             if (sourceRefIdInput && sourceRefIdInput.value) {
                 prefillSelect.value = sourceRefIdInput.value;
                 // Trigger change to update preview
                 prefillSelect.dispatchEvent(new Event('change'));
             }
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
            updateFormFromNews(selectedId, true);
        });
    }

    function updateFormFromNews(selectedId, updateFields = true) {
        if (sourceRefIdInput) {
            sourceRefIdInput.value = selectedId || '';
        }

        if (!selectedId) {
            if (mainPreviewImg && mainPreviewContainer) {
                 const originalSrc = mainPreviewImg.dataset.originalSrc;
                 if (originalSrc && !useInternalCheckbox.checked) {
                     mainPreviewImg.src = originalSrc;
                     mainPreviewContainer.style.display = 'block';
                 } else {
                     mainPreviewContainer.style.display = 'none';
                 }
            }
            return;
        }
        
        const item = newsItems.find(n => parseInt(n.id, 10) === selectedId);
        
        if (item) {
            // Update fields only if requested (e.g. user manually changed selection)
            if (updateFields) {
                if (titleInput) {
                    titleInput.value = item.title || '';
                    highlightField(titleInput);
                }
                
                if (buttonLinkInput) {
                    const slug = item.slug || '';
                    buttonLinkInput.value = slug ? '<?= site_url('berita/') ?>' + slug : '';
                    highlightField(buttonLinkInput);
                }
            }

            // Always update preview to show latest news thumbnail
            if (item.thumbnail) {
                showMainPreview(item.thumbnail);
            } else {
                if (mainPreviewImg) mainPreviewImg.src = ''; 
                if (mainPreviewContainer) mainPreviewContainer.style.display = 'none';
            }
        }
    }

    function highlightField(field) {
        field.classList.add('is-valid');
        setTimeout(() => field.classList.remove('is-valid'), 2000);
    }
    
    // Initial Sync for Edit Mode
    if (useInternalCheckbox && useInternalCheckbox.checked && prefillSelect.value) {
        // Update ONLY preview, keep saved title/link
        updateFormFromNews(parseInt(prefillSelect.value, 10), false);
    }
});
</script>
<?= $this->endSection() ?>
