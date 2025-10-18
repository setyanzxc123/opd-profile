<?= $this->extend('layouts/admin') ?>
<?php
  $categoryOptions     = $categories ?? [];
  $tagOptions          = $tags ?? [];
  $selectedCategoryIds = old('categories', $selectedCategories ?? []);
  if (! is_array($selectedCategoryIds)) {
      $selectedCategoryIds = $selectedCategoryIds ? [$selectedCategoryIds] : [];
  }
  $selectedCategoryIds = array_values(array_unique(array_map(static fn ($value) => (int) $value, $selectedCategoryIds)));

  $primaryCategoryId = old('primary_category', $primaryCategory ?? null);
  $primaryCategoryId = $primaryCategoryId !== null ? (int) $primaryCategoryId : null;
  if ($primaryCategoryId && ! in_array($primaryCategoryId, $selectedCategoryIds, true)) {
      $primaryCategoryId = $selectedCategoryIds[0] ?? null;
  }

  $selectedTagIds = old('tags', $selectedTags ?? []);
  if (! is_array($selectedTagIds)) {
      $selectedTagIds = $selectedTagIds ? [$selectedTagIds] : [];
  }
  $selectedTagIds = array_values(array_unique(array_map(static fn ($value) => (int) $value, $selectedTagIds)));

  $newTagsInput = (string) old('new_tags', '');
?>

<?= $this->section('pageStyles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row g-4">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center gap-3 news-form-header pb-3 mb-4">
          <div>
            <h4 class="fw-bold mb-1"><?= esc($title ?? 'Form Berita') ?></h4>
          </div>
          <a href="<?= site_url('admin/news') ?>" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar</a>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" action="<?= $mode === 'edit' ? site_url('admin/news/update/'.$item['id']) : site_url('admin/news') ?>">
          <?= csrf_field() ?>

          <div class="row g-4">
            <div class="col-12">
              <div class="card shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="mb-10">
                    <label class="form-label fw-semibold" for="newsTitle">Judul Berita <span class="text-danger">*</span></label>
                    <input type="text" id="newsTitle" name="title" class="form-control form-control-lg" maxlength="200" required value="<?= esc(old('title', $item['title'])) ?>" placeholder="Contoh: Pemkab Gelar Diskusi Publik Transformasi Digital">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-1 form-text mt-2">
                      <span id="titleCounter" class="text-muted" role="status" aria-live="polite">0/200 karakter</span>
                      <span>Slug otomatis: <span id="slugPreview" class="fw-semibold text-primary" data-initial-slug="<?= esc($item['slug'] ?? 'slug-otomatis', 'attr') ?>" role="status" aria-live="polite"><?= esc($item['slug'] ?? 'slug-otomatis') ?></span></span>
                    </div>
                    <?php if (isset($validation) && $validation->hasError('title')): ?>
                      <div class="form-text text-danger mt-1"><?= esc($validation->getError('title')) ?></div>
                    <?php endif; ?>
                  </div>

                  <div class="mb-3 mt-3">
                    <label class="form-label fw-semibold mb-0" for="newsContent">Isi Berita <span class="text-danger">*</span></label>
                  </div>
                  <textarea id="newsContent" name="content" class="form-control" rows="12" placeholder="Tulis isi berita dengan struktur yang rapi..." required><?= old('content', $item['content']) ?></textarea>
                  <div class="form-text mt-2">Gunakan toolbar untuk menambahkan heading, daftar, tabel, media, hingga kutipan.</div>
                  <?php if (isset($validation) && $validation->hasError('content')): ?>
                    <div class="form-text text-danger mt-1"><?= esc($validation->getError('content')) ?></div>
                  <?php endif; ?>

                  <dl class="news-quick-stats mt-4" role="group" aria-label="Statistik tulisan">
                    <div>
                      <dt>Jumlah kata</dt>
                      <dd id="wordCount" role="status" aria-live="polite">0 kata</dd>
                    </div>
                    <div>
                      <dt>Jumlah karakter</dt>
                      <dd id="characterCount" role="status" aria-live="polite">0</dd>
                    </div>
                    <div>
                      <dt>Estimasi waktu baca</dt>
                      <dd id="readingTime" role="status" aria-live="polite">—</dd>
                    </div>
                    <div>
                      <dt>Terakhir diperbarui</dt>
                      <dd id="lastUpdated" role="status" aria-live="polite">Belum ada perubahan</dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="row row-cols-1 row-cols-md-2 g-4 mt-0 align-items-stretch">
            <div class="col">
              <div class="news-side-section">
                <div class="card shadow-sm h-100">
                  <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Pengaturan &amp; Media</h5>

                    <div class="mb-3">
                      <label class="form-label" for="editorLanguage">Bahasa Editor</label>
                      <select id="editorLanguage" class="form-select">
                        <option value="id" selected>Bahasa Indonesia</option>
                        <option value="en">English</option>
                      </select>
                      <div class="form-text">Pilih bahasa antarmuka TinyMCE yang paling nyaman digunakan.</div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label" for="publishedAt">Rencana Tanggal Terbit</label>
                      <?php
                        $val = old('published_at', $item['published_at']);
                        if ($val && strpos($val, 'T') === false) {
                          $val = str_replace(' ', 'T', substr($val, 0, 16));
                        }
                      ?>
                      <input type="datetime-local" id="publishedAt" name="published_at" class="form-control" value="<?= esc($val) ?>">
                      <div class="form-text">Kosongkan bila ingin publikasi segera setelah simpan.</div>
                    </div>

                    <div class="mb-0">
                      <label class="form-label" for="thumbnail">Gambar Sampul</label>
                      <input type="file" id="thumbnail" name="thumbnail" accept="image/*" class="form-control">
                      <?php if (!empty($item['thumbnail'])): ?>
                        <div class="form-text mt-1">Saat ini: <a target="_blank" href="<?= esc(base_url($item['thumbnail']), 'attr') ?>">lihat gambar</a></div>
                      <?php endif; ?>
                      <?php if (isset($validation) && $validation->hasError('thumbnail')): ?>
                        <div class="form-text text-danger mt-1"><?= esc($validation->getError('thumbnail')) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="news-side-section mt-4 mt-md-3">
                <div class="card shadow-sm h-100">
                  <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Kategorisasi &amp; Tag</h5>

                    <div class="mb-4">
                      <label class="form-label fw-semibold">Kategori Berita</label>
                      <?php if ($categoryOptions): ?>
                        <p class="form-text mb-2">Pilih kategori yang relevan lalu tandai kategori utama.</p>
                        <div class="taxonomy-category-list">
                          <?php foreach ($categoryOptions as $category): ?>
                            <?php
                              $categoryId  = (int) ($category['id'] ?? 0);
                              $isChecked   = in_array($categoryId, $selectedCategoryIds, true);
                              $isPrimary   = $primaryCategoryId !== null ? $primaryCategoryId === $categoryId : false;
                              $description = trim((string) ($category['description'] ?? ''));
                            ?>
                            <div class="border rounded p-2 mb-2">
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]"
                                       value="<?= $categoryId ?>"
                                       id="categoryCheckbox<?= $categoryId ?>"
                                       data-category-checkbox
                                       data-category-id="<?= $categoryId ?>"
                                       <?= $isChecked ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="categoryCheckbox<?= $categoryId ?>">
                                  <?= esc($category['name'] ?? 'Kategori') ?>
                                </label>
                              </div>
                              <?php if ($description !== ''): ?>
                                <p class="form-text mb-2 ms-4"><?= esc($description) ?></p>
                              <?php endif; ?>
                              <div class="form-check ms-4">
                                <input class="form-check-input" type="radio" name="primary_category"
                                       value="<?= $categoryId ?>"
                                       id="primaryCategory<?= $categoryId ?>"
                                       data-primary-radio
                                       data-primary-for="<?= $categoryId ?>"
                                       <?= $isPrimary ? 'checked' : '' ?>
                                       <?= $isChecked ? '' : 'disabled' ?>>
                                <label class="form-check-label small text-muted" for="primaryCategory<?= $categoryId ?>">
                                  Jadikan kategori utama
                                </label>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
                        <p class="text-muted small mb-0">Belum ada kategori. Tambahkan kategori terlebih dahulu dari manajemen data.</p>
                      <?php endif; ?>
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-semibold" for="newsTags">Tag Konten</label>
                      <?php if ($tagOptions): ?>
                        <?php $selectSize = max(3, min(8, count($tagOptions))); ?>
                        <select id="newsTags" name="tags[]" class="form-select" multiple size="<?= $selectSize ?>">
                          <?php foreach ($tagOptions as $tag): ?>
                            <?php $tagId = (int) ($tag['id'] ?? 0); ?>
                            <option value="<?= $tagId ?>" <?= in_array($tagId, $selectedTagIds, true) ? 'selected' : '' ?>>
                              <?= esc($tag['name'] ?? '') ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                        <div class="form-text">Gunakan Ctrl (Windows) atau Command (Mac) untuk memilih lebih dari satu tag.</div>
                      <?php else: ?>
                        <p class="text-muted small mb-0">Belum ada tag tersimpan. Anda dapat menambahkan tag baru di bawah.</p>
                      <?php endif; ?>
                    </div>

                    <div>
                      <label class="form-label" for="newTags">Tambah Tag Baru</label>
                      <textarea id="newTags" name="new_tags" class="form-control" rows="2" placeholder="Contoh: Data Terbuka, Smart City"><?= esc($newTagsInput) ?></textarea>
                      <div class="form-text">Pisahkan setiap tag dengan koma atau baris baru.</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col">
              <div class="news-side-section">
                <div class="card shadow-sm h-100">
                  <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Bantuan Penulisan</h5>

                    <div class="mb-3 news-template-dropdown">
                      <label class="form-label">Template Konten</label>
                      <div class="dropdown w-100">
                        <button class="btn btn-outline-primary w-100 dropdown-toggle" type="button" id="templateDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                          Sisipkan Template
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="templateDropdown">
                          <li><a class="dropdown-item js-insert-template" href="#" data-template="lead">Lead 5W + 1H</a></li>
                          <li><a class="dropdown-item js-insert-template" href="#" data-template="timeline">Kronologi / Timeline</a></li>
                          <li><a class="dropdown-item js-insert-template" href="#" data-template="quote">Kutipan Narasumber</a></li>
                          <li><a class="dropdown-item js-insert-template" href="#" data-template="data">Tabel Data Singkat</a></li>
                        </ul>
                      </div>
                    </div>

                    <button type="button" class="btn btn-outline-secondary w-100 js-editor-action" data-action="clear-format"><i class="bx bx-eraser me-1"></i> Bersihkan Format</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="bx bx-save me-2"></i> Simpan Berita</button>
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
  document.addEventListener('DOMContentLoaded', function () {
    const titleInput = document.querySelector('#newsTitle');
    const slugPreview = document.getElementById('slugPreview');
    const titleCounter = document.getElementById('titleCounter');
    const wordCountEl = document.getElementById('wordCount');
    const characterCountEl = document.getElementById('characterCount');
    const readingTimeEl = document.getElementById('readingTime');
    const lastUpdatedEl = document.getElementById('lastUpdated');
    const editorLanguageSelect = document.getElementById('editorLanguage');
    const categoryCheckboxes = document.querySelectorAll('[data-category-checkbox]');
    const primaryCategoryRadios = document.querySelectorAll('[data-primary-radio]');
    const slugPreviewInitial = slugPreview ? slugPreview.dataset.initialSlug || 'slug-otomatis' : 'slug-otomatis';
    let lastUpdateTimer;
    const safeStorage = (() => {
      if (typeof window === 'undefined') return null;
      try {
        return window.localStorage;
      } catch (error) {
        return null;
      }
    })();
    const LANGUAGE_STORAGE_KEY = 'newsEditorLanguage';
    const DEFAULT_EDITOR_LANGUAGE = 'id';
    let currentEditorLanguage = DEFAULT_EDITOR_LANGUAGE;

    const slugify = (value) => {
      const raw = value ? value.toString() : '';
      const normalized = raw.normalize ? raw.normalize('NFD') : raw;
      return normalized
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .substring(0, 200) || 'slug-otomatis';
    };

    const updateTitleMeta = () => {
      if (!titleInput || !slugPreview || !titleCounter) return;
      const value = titleInput.value || '';
      titleCounter.textContent = `${value.length}/200 karakter`;
      if (value.trim()) {
        slugPreview.textContent = slugify(value);
      } else {
        slugPreview.textContent = slugPreviewInitial;
      }
    };

    if (titleInput) {
      updateTitleMeta();
      titleInput.addEventListener('input', updateTitleMeta);
    }

    const setLastUpdatedNow = () => {
      if (!lastUpdatedEl) return;
      const now = new Date();
      lastUpdatedEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    };

    const updateContentStats = () => {
      const editor = tinymce.get('newsContent');
      if (!editor) return;
      const text = editor.getContent({ format: 'text' }).trim();
      const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
      const characters = text.replace(/\s/g, '').length;
      const minutes = words ? Math.max(1, Math.round(words / 200)) : 0;

      const statsLocale = currentEditorLanguage === 'en' ? 'en-US' : 'id-ID';
      if (wordCountEl) wordCountEl.textContent = `${words} kata`;
      if (characterCountEl) characterCountEl.textContent = characters.toLocaleString(statsLocale);
      if (readingTimeEl) {
        readingTimeEl.textContent = minutes ? `${minutes} menit` : '—';
      }

      if (lastUpdatedEl) {
        clearTimeout(lastUpdateTimer);
        lastUpdateTimer = setTimeout(() => {
          setLastUpdatedNow();
        }, 500);
      }
    };

    const insertTemplate = (key) => {
      const editor = tinymce.get('newsContent');
      if (!editor) return;
      const templates = {
        lead: `<p><strong>Lead:</strong> ${titleInput && titleInput.value ? titleInput.value : 'Ringkasan singkat peristiwa.'}</p>
<ul>
  <li><strong>Apa:</strong> Tuliskan inti kejadian.</li>
  <li><strong>Siapa:</strong> Pihak yang terlibat.</li>
  <li><strong>Kapan:</strong> Waktu kejadian.</li>
  <li><strong>Di mana:</strong> Lokasi peristiwa.</li>
  <li><strong>Mengapa:</strong> Latar belakang singkat.</li>
  <li><strong>Bagaimana:</strong> Rangkaian kejadian.</li>
</ul>`,
        timeline: `<h3>Kronologi Kejadian</h3>
<ol>
  <li><strong>Waktu 1:</strong> Uraikan peristiwa awal.</li>
  <li><strong>Waktu 2:</strong> Perkembangan penting berikutnya.</li>
  <li><strong>Waktu 3:</strong> Dampak atau tindak lanjut.</li>
</ol>
<p><strong>Kondisi Terkini:</strong> Jelaskan situasi terakhir yang diketahui.</p>`,
        quote: `<blockquote>
  <p>"Masukkan kutipan penting dari narasumber resmi di sini."</p>
  <cite>Nama Narasumber, Jabatan</cite>
</blockquote>`,
        data: `<h3>Data Pendukung</h3>
<table class="table table-bordered table-sm">
  <thead>
    <tr>
      <th>Indikator</th>
      <th>Nilai</th>
      <th>Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Contoh 1</td>
      <td>0</td>
      <td>Penjelasan singkat.</td>
    </tr>
  </tbody>
</table>`
      };

      if (templates[key]) {
        editor.undoManager.transact(() => {
          editor.focus();
          editor.execCommand('mceInsertContent', false, templates[key]);
        });
      }
    };

    document.querySelectorAll('.js-insert-template').forEach((link) => {
      link.addEventListener('click', (event) => {
        event.preventDefault();
        insertTemplate(link.dataset.template);
      });
    });

    document.querySelectorAll('.js-editor-action').forEach((button) => {
      button.addEventListener('click', () => {
        const action = button.dataset.action;
        const editor = tinymce.get('newsContent');
        if (!editor) return;
        if (action === 'clear-format') {
          editor.execCommand('RemoveFormat');
        }
      });
    });

    const syncPrimaryCategoryState = () => {
      if (primaryCategoryRadios.length === 0) {
        return;
      }

      primaryCategoryRadios.forEach((radio) => {
        const categoryId = radio.dataset.primaryFor;
        const relatedCheckbox = document.querySelector(`[data-category-checkbox][data-category-id="${categoryId}"]`);
        const enabled = relatedCheckbox ? relatedCheckbox.checked : false;
        radio.disabled = !enabled;
      });

      const activeRadio = Array.from(primaryCategoryRadios).find((radio) => radio.checked && !radio.disabled);
      if (!activeRadio) {
        const firstEnabled = Array.from(primaryCategoryRadios).find((radio) => !radio.disabled);
        if (firstEnabled) {
          firstEnabled.checked = true;
        }
      }
    };

    categoryCheckboxes.forEach((checkbox) => {
      checkbox.addEventListener('change', syncPrimaryCategoryState);
    });
    syncPrimaryCategoryState();

    const initTinyMCE = (languageCode) => {
      currentEditorLanguage = languageCode === 'en' ? 'en' : DEFAULT_EDITOR_LANGUAGE;
      tinymce.remove('#newsContent');

      const editorConfig = {
        selector: '#newsContent',
        branding: false,
        promotion: false,
        height: 520,
        menubar: 'file edit view insert format tools table help',
        toolbar_sticky: true,
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist checklist outdent indent | table image media link | removeformat | fullscreen preview print code',
        plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount help quickbars emoticons',
        quickbars_selection_toolbar: 'bold italic underline | quicklink blockquote quicktable',
        quickbars_insert_toolbar: 'image media codesample | hr',
        autosave_interval: '30s',
        autosave_restore_when_empty: true,
        autosave_retention: '2m',
        image_caption: true,
        content_style: 'body { font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif; font-size: 16px; line-height: 1.7; }',
        table_default_attributes: { class: 'table table-striped table-sm' },
        file_picker_types: 'image media',
        language: currentEditorLanguage,
        setup: function (editor) {
          editor.on('init', function () {
            updateContentStats();
          });
          editor.on('change keyup setcontent', updateContentStats);
        }
      };

      if (currentEditorLanguage === 'id') {
        editorConfig.language_url = '<?= base_url('assets/vendor/tinymce/langs/id.js') ?>';
      }

      tinymce.init(editorConfig);
    };

    let initialEditorLanguage = DEFAULT_EDITOR_LANGUAGE;

    if (safeStorage) {
      const storedLanguage = safeStorage.getItem(LANGUAGE_STORAGE_KEY);
      if (storedLanguage === 'en' || storedLanguage === 'id') {
        initialEditorLanguage = storedLanguage;
      }
    }

    if (editorLanguageSelect) {
      editorLanguageSelect.value = initialEditorLanguage;
      editorLanguageSelect.addEventListener('change', (event) => {
        const selectedLanguage = event.target.value === 'en' ? 'en' : DEFAULT_EDITOR_LANGUAGE;
        if (safeStorage) {
          safeStorage.setItem(LANGUAGE_STORAGE_KEY, selectedLanguage);
        }
        initTinyMCE(selectedLanguage);
      });
    }

    initTinyMCE(initialEditorLanguage);
  });
</script>
<?= $this->endSection() ?>

