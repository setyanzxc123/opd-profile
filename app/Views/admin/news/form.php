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

  $excerptValue         = (string) old('excerpt', $item['excerpt'] ?? '');
  $publicAuthorValue    = (string) old('public_author', $item['public_author'] ?? '');
  $sourceValue          = (string) old('source', $item['source'] ?? '');
  $metaTitleValue       = (string) old('meta_title', $item['meta_title'] ?? '');
  $metaDescriptionValue = (string) old('meta_description', $item['meta_description'] ?? '');
  $metaKeywordsValue    = (string) old('meta_keywords', $item['meta_keywords'] ?? '');

  $mediaItems = $mediaItems ?? [];
  $mediaItems = array_values($mediaItems);
  $mediaCountInitial = count($mediaItems);

  $countChars = static function (string $value): int {
      return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
  };

  $excerptLength         = $countChars($excerptValue);
  $metaTitleLength       = $countChars($metaTitleValue);
  $metaDescriptionLength = $countChars($metaDescriptionValue);
?>

<?= $this->section('pageStyles') ?>
<style>
  .news-media-manager .news-media-item {
    border: 1px solid rgba(0, 0, 0, 0.06);
    transition: border-color 0.2s ease, background-color 0.2s ease, opacity 0.2s ease;
  }

  .news-media-manager .news-media-item .news-media-preview figure,
  .news-media-manager .news-media-item .news-media-preview .ratio {
    width: 260px;
    max-width: 100%;
  }

  .news-media-manager .news-media-item .news-media-preview img {
    max-height: 160px;
    object-fit: cover;
  }

  .news-media-manager .news-media-item.is-media-deleted {
    opacity: 0.6;
    border-color: rgba(220, 53, 69, 0.4);
    background-color: rgba(220, 53, 69, 0.05);
  }

  .news-media-manager .placeholder {
    min-height: 150px;
  }

  @media (max-width: 767.98px) {
    .news-media-manager .news-media-item .news-media-preview figure,
    .news-media-manager .news-media-item .news-media-preview .ratio {
      width: 100%;
    }
  }
</style>
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

                  <div class="border-top pt-4 mt-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-3">
                      <div>
                        <h5 class="fw-semibold mb-1">Metadata &amp; SEO</h5>
                        <p class="form-text mb-0">Isi ringkasan dan meta tag agar berita tampil optimal di pencarian.</p>
                      </div>
                    </div>

                    <div class="mb-4">
                      <label class="form-label fw-semibold" for="excerptField">Ringkasan Publik (Excerpt)</label>
                      <textarea id="excerptField" name="excerpt" class="form-control" rows="3" maxlength="300" data-counter-target="#excerptCounter" data-counter-limit="160" placeholder="Tuliskan ringkasan singkat berita (maksimal 160 karakter ideal)."><?= esc($excerptValue) ?></textarea>
                      <div class="d-flex flex-column flex-lg-row justify-content-between gap-1 form-text mt-1">
                        <span id="excerptCounter" role="status" aria-live="polite"><?= $excerptLength ?>/160 karakter disarankan</span>
                        <span class="text-muted">Kosongkan bila ingin memakai potongan otomatis dari isi berita.</span>
                      </div>
                      <?php if (isset($validation) && $validation->hasError('excerpt')): ?>
                        <div class="form-text text-danger mt-1"><?= esc($validation->getError('excerpt')) ?></div>
                      <?php endif; ?>
                    </div>

                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label" for="publicAuthorField">Nama Penulis Publik</label>
                        <input type="text" id="publicAuthorField" name="public_author" class="form-control" maxlength="255" value="<?= esc($publicAuthorValue) ?>" placeholder="Contoh: Tim Humas OPD">
                        <div class="form-text">Ditampilkan pada halaman berita. Gunakan nama tim atau jabatan.</div>
                        <?php if (isset($validation) && $validation->hasError('public_author')): ?>
                          <div class="form-text text-danger mt-1"><?= esc($validation->getError('public_author')) ?></div>
                        <?php endif; ?>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label" for="sourceField">Sumber Berita</label>
                        <input type="text" id="sourceField" name="source" class="form-control" maxlength="255" value="<?= esc($sourceValue) ?>" placeholder="Contoh: Dinas Kominfo">
                        <div class="form-text">Opsional, tampil sebagai sumber di halaman publik.</div>
                        <?php if (isset($validation) && $validation->hasError('source')): ?>
                          <div class="form-text text-danger mt-1"><?= esc($validation->getError('source')) ?></div>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="mt-4">
                      <label class="form-label" for="metaTitleField">Meta Title</label>
                      <input type="text" id="metaTitleField" name="meta_title" class="form-control" maxlength="70" value="<?= esc($metaTitleValue) ?>" placeholder="Judul SEO (maks. 70 karakter)" data-counter-target="#metaTitleCounter" data-counter-limit="70">
                      <div class="d-flex flex-column flex-lg-row justify-content-between gap-1 form-text mt-1">
                        <span id="metaTitleCounter" role="status" aria-live="polite"><?= $metaTitleLength ?>/70 karakter</span>
                        <span class="text-muted">Kosongkan jika ingin memakai judul berita.</span>
                      </div>
                      <?php if (isset($validation) && $validation->hasError('meta_title')): ?>
                        <div class="form-text text-danger mt-1"><?= esc($validation->getError('meta_title')) ?></div>
                      <?php endif; ?>
                    </div>

                    <div class="mt-4">
                      <label class="form-label" for="metaDescriptionField">Meta Description</label>
                      <textarea id="metaDescriptionField" name="meta_description" class="form-control" rows="3" maxlength="160" data-counter-target="#metaDescriptionCounter" data-counter-limit="160" placeholder="Deskripsi singkat untuk mesin pencari (maks. 160 karakter)."><?= esc($metaDescriptionValue) ?></textarea>
                      <div class="d-flex flex-column flex-lg-row justify-content-between gap-1 form-text mt-1">
                        <span id="metaDescriptionCounter" role="status" aria-live="polite"><?= $metaDescriptionLength ?>/160 karakter</span>
                        <span class="text-muted">Boleh dikosongkan untuk menggunakan ringkasan otomatis.</span>
                      </div>
                      <?php if (isset($validation) && $validation->hasError('meta_description')): ?>
                        <div class="form-text text-danger mt-1"><?= esc($validation->getError('meta_description')) ?></div>
                      <?php endif; ?>
                    </div>

                    <div class="mt-4">
                      <label class="form-label" for="metaKeywordsField">Meta Keywords</label>
                      <input type="text" id="metaKeywordsField" name="meta_keywords" class="form-control" maxlength="500" value="<?= esc($metaKeywordsValue) ?>" placeholder="Contoh: layanan publik, digitalisasi, inovasi">
                      <div class="form-text">Opsional. Pisahkan kata kunci dengan koma.</div>
                      <?php if (isset($validation) && $validation->hasError('meta_keywords')): ?>
                        <div class="form-text text-danger mt-1"><?= esc($validation->getError('meta_keywords')) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
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
            <div class="card shadow-sm mt-4">
              <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                  <div>
                    <h5 class="fw-semibold mb-1">Galeri &amp; Multimedia</h5>
                    <p class="text-muted small mb-0">Tambahkan gambar aktivitas atau sematkan video pendukung. Tandai salah satu sebagai cover untuk tampil pada halaman publik.</p>
                  </div>
                  <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addImageMediaBtn"><i class="bx bx-image-add"></i> Tambah Gambar</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="addVideoMediaBtn"><i class="bx bx-video-plus"></i> Tambah Video</button>
                  </div>
                </div>

                <div id="mediaItemsContainer" class="news-media-manager" data-initial-counter="<?= (int) $mediaCountInitial ?>">
                  <?php if ($mediaItems): ?>
                    <?php foreach ($mediaItems as $media): ?>
                      <?php
                        $mediaId      = (int) ($media['id'] ?? 0);
                        $mediaType    = (string) ($media['media_type'] ?? 'image');
                        $mediaCaption = (string) ($media['caption'] ?? '');
                        $mediaSort    = (int) ($media['sort_order'] ?? 0);
                        $mediaCover   = (int) ($media['is_cover'] ?? 0) === 1;
                        $mediaFile    = (string) ($media['file_path'] ?? '');
                        $mediaUrl     = (string) ($media['source_url'] ?? $media['external_url'] ?? '');
                        $mediaEmbed   = (string) ($media['external_url'] ?? '');
                      ?>
                      <div class="news-media-item card mb-3" data-existing-id="<?= $mediaId ?>" data-type="<?= esc($mediaType, 'attr') ?>">
                        <div class="card-body">
                          <div class="d-flex flex-column flex-md-row gap-3">
                            <div class="news-media-preview flex-shrink-0">
                              <?php if ($mediaType === 'image' && $mediaFile !== ''): ?>
                                <figure class="mb-0 text-center">
                                  <img src="<?= esc(base_url($mediaFile), 'attr') ?>" alt="<?= esc($mediaCaption !== '' ? $mediaCaption : ($item['title'] ?? 'Gambar Berita')) ?>" class="img-fluid rounded border" loading="lazy">
                                </figure>
                              <?php elseif ($mediaType === 'video' && $mediaEmbed !== ''): ?>
                                <div class="ratio ratio-16x9">
                                  <iframe src="<?= esc($mediaEmbed, 'attr') ?>" title="Video terkait" allowfullscreen loading="lazy"></iframe>
                                </div>
                              <?php else: ?>
                                <div class="placeholder bg-light border rounded d-flex align-items-center justify-content-center text-muted">
                                  <span>Tidak ada pratinjau</span>
                                </div>
                              <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                              <?php if ($mediaType === 'image'): ?>
                                <div class="mb-3">
                                  <label class="form-label">Caption Gambar</label>
                                  <input type="text" class="form-control media-caption-input" name="media_existing[<?= $mediaId ?>][caption]" value="<?= esc($mediaCaption) ?>" placeholder="Judul atau deskripsi singkat">
                                </div>
                              <?php else: ?>
                                <div class="mb-3">
                                  <label class="form-label">URL Video (YouTube/Vimeo)</label>
                                  <input type="url" class="form-control media-video-url-input" name="media_existing[<?= $mediaId ?>][external_url]" value="<?= esc($mediaUrl) ?>" placeholder="https://www.youtube.com/watch?v=...">
                                  <div class="form-text">URL harus mengarah ke video publik.</div>
                                </div>
                                <div class="mb-3">
                                  <label class="form-label">Caption Video</label>
                                  <input type="text" class="form-control media-caption-input" name="media_existing[<?= $mediaId ?>][caption]" value="<?= esc($mediaCaption) ?>" placeholder="Keterangan singkat">
                                </div>
                              <?php endif; ?>

                              <input type="hidden" name="media_existing[<?= $mediaId ?>][sort_order]" class="media-sort-order-field" value="<?= $mediaSort ?>">

                              <div class="d-flex flex-wrap gap-3 align-items-center">
                                <div class="form-check">
                                  <input class="form-check-input" type="radio" name="media_cover" value="existing:<?= $mediaId ?>" id="mediaCoverExisting<?= $mediaId ?>" data-role="cover-radio" <?= $mediaCover ? 'checked' : '' ?>>
                                  <label class="form-check-label small" for="mediaCoverExisting<?= $mediaId ?>">Jadikan cover</label>
                                </div>
                                <div class="form-check">
                                  <input class="form-check-input media-delete-checkbox" type="checkbox" name="media_existing[<?= $mediaId ?>][delete]" value="1" id="mediaDelete<?= $mediaId ?>" data-role="delete-checkbox">
                                  <label class="form-check-label text-danger small" for="mediaDelete<?= $mediaId ?>">Hapus media ini</label>
                                </div>
                                <div class="ms-auto d-flex flex-wrap gap-2">
                                  <button type="button" class="btn btn-outline-secondary btn-sm media-move-up" title="Naikkan"><i class="bx bx-up-arrow-alt"></i></button>
                                  <button type="button" class="btn btn-outline-secondary btn-sm media-move-down" title="Turunkan"><i class="bx bx-down-arrow-alt"></i></button>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>

                  <p id="mediaEmptyState" class="text-muted small<?= $mediaItems ? ' d-none' : '' ?>">Belum ada media tambahan. Gunakan tombol di atas untuk mulai menambahkan gambar atau video.</p>
                </div>

                <template id="media-image-template">
                  <div class="news-media-item card mb-3" data-type="image">
                    <div class="card-body">
                      <div class="d-flex flex-column flex-md-row gap-3">
                        <div class="news-media-preview flex-shrink-0">
                          <figure class="mb-0 text-center">
                            <img src="" alt="" class="img-fluid rounded border d-none" data-role="preview-img">
                            <div class="placeholder bg-light border rounded d-flex align-items-center justify-content-center text-muted" data-role="preview-placeholder">
                              <span>Belum ada pratinjau</span>
                            </div>
                          </figure>
                        </div>
                        <div class="flex-grow-1">
                          <div class="mb-3">
                            <label class="form-label">Gambar <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" accept="image/*" name="media_new_images_files[]" data-role="file-input" required>
                            <div class="form-text">Gunakan resolusi tinggi agar tampilan publik tetap tajam.</div>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Caption</label>
                            <input type="text" class="form-control media-caption-input" name="media_new_images_caption[]" data-role="caption-input" placeholder="Judul atau deskripsi singkat">
                          </div>
                          <input type="hidden" name="media_new_images_uid[]" data-role="uid-input" value="">
                          <input type="hidden" name="media_new_images_sort[]" class="media-sort-order-field" value="0">
                          <div class="d-flex flex-wrap gap-2 align-items-center">
                            <div class="form-check">
                              <input class="form-check-input" type="radio" name="media_cover" value="" data-role="cover-radio">
                              <label class="form-check-label small" data-role="cover-label">Jadikan cover</label>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm media-move-up" title="Naikkan"><i class="bx bx-up-arrow-alt"></i></button>
                            <button type="button" class="btn btn-outline-secondary btn-sm media-move-down" title="Turunkan"><i class="bx bx-down-arrow-alt"></i></button>
                            <button type="button" class="btn btn-outline-danger btn-sm media-remove"><i class="bx bx-trash"></i> Hapus</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>

                <template id="media-video-template">
                  <div class="news-media-item card mb-3" data-type="video">
                    <div class="card-body">
                      <div class="d-flex flex-column flex-md-row gap-3">
                        <div class="news-media-preview flex-shrink-0">
                          <div class="ratio ratio-16x9 bg-light border rounded d-flex align-items-center justify-content-center text-muted">
                            <i class="bx bx-play-circle fs-3"></i>
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <div class="mb-3">
                            <label class="form-label">URL Video (YouTube/Vimeo)</label>
                            <input type="url" class="form-control media-video-url-input" name="media_new_videos_url[]" data-role="video-url-input" placeholder="https://www.youtube.com/watch?v=..." required>
                            <div class="form-text">URL harus mengarah ke video publik YouTube atau Vimeo.</div>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Caption</label>
                            <input type="text" class="form-control media-caption-input" name="media_new_videos_caption[]" data-role="caption-input" placeholder="Keterangan singkat">
                          </div>
                          <input type="hidden" name="media_new_videos_uid[]" data-role="uid-input" value="">
                          <input type="hidden" name="media_new_videos_sort[]" class="media-sort-order-field" value="0">
                          <div class="d-flex flex-wrap gap-2 align-items-center">
                            <div class="form-check">
                              <input class="form-check-input" type="radio" name="media_cover" value="" data-role="cover-radio">
                              <label class="form-check-label small" data-role="cover-label">Jadikan cover</label>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm media-move-up" title="Naikkan"><i class="bx bx-up-arrow-alt"></i></button>
                            <button type="button" class="btn btn-outline-secondary btn-sm media-move-down" title="Turunkan"><i class="bx bx-down-arrow-alt"></i></button>
                            <button type="button" class="btn btn-outline-danger btn-sm media-remove"><i class="bx bx-trash"></i> Hapus</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
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
    const counterFields = document.querySelectorAll('[data-counter-target]');
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
    const attachCounters = () => {
      counterFields.forEach((field) => {
        const targetSelector = field.getAttribute('data-counter-target');
        if (!targetSelector) {
          return;
        }
        const limitAttr = field.getAttribute('data-counter-limit');
        const limit = limitAttr ? parseInt(limitAttr, 10) : 0;
        const target = document.querySelector(targetSelector);
        if (!target) {
          return;
        }

        const update = () => {
          const value = field.value || '';
          const length = value.length;
          const base = limit ? `${length}/${limit}` : `${length}`;
          target.textContent = limit ? `${base} karakter` : `${base} karakter`;
          if (limit && length > limit) {
            target.classList.add('text-danger');
          } else {
            target.classList.remove('text-danger');
          }
        };

        update();
        field.addEventListener('input', update);
      });
    };

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

    const mediaContainer = document.getElementById('mediaItemsContainer');
    if (mediaContainer) {
      const emptyState = document.getElementById('mediaEmptyState');
      const addImageBtn = document.getElementById('addImageMediaBtn');
      const addVideoBtn = document.getElementById('addVideoMediaBtn');
      const imageTemplate = document.getElementById('media-image-template');
      const videoTemplate = document.getElementById('media-video-template');
      const formElement = document.querySelector('form');
      let mediaUidCounter = Number(mediaContainer.dataset.initialCounter || 0);

      const getMediaItems = () => Array.from(mediaContainer.querySelectorAll('.news-media-item'));

      const updateSortOrders = () => {
        getMediaItems().forEach((item, index) => {
          const sortField = item.querySelector('.media-sort-order-field');
          if (sortField) {
            sortField.value = (index + 1) * 10;
          }
        });
      };

      const updateEmptyState = () => {
        if (!emptyState) return;
        emptyState.classList.toggle('d-none', getMediaItems().length > 0);
      };

      const ensureCoverSelection = () => {
        const radios = Array.from(document.querySelectorAll('input[name="media_cover"]')).filter((radio) => !radio.disabled);
        if (radios.length === 0) {
          return;
        }
        const hasActive = radios.some((radio) => radio.checked && !radio.disabled);
        if (!hasActive) {
          radios[0].checked = true;
        }
      };

      const moveItem = (item, direction) => {
        const items = getMediaItems();
        const index = items.indexOf(item);
        if (index === -1) {
          return;
        }

        if (direction === 'up' && index > 0) {
          mediaContainer.insertBefore(item, items[index - 1]);
        } else if (direction === 'down' && index < items.length - 1) {
          mediaContainer.insertBefore(items[index + 1], item);
        }

        updateSortOrders();
      };

      const handleDeleteToggle = (item, checked) => {
        item.classList.toggle('is-media-deleted', checked);
        item.querySelectorAll('.media-caption-input, .media-video-url-input').forEach((input) => {
          input.disabled = checked;
        });

        const coverRadio = item.querySelector('[data-role="cover-radio"]');
        if (coverRadio) {
          coverRadio.disabled = checked;
          if (checked && coverRadio.checked) {
            coverRadio.checked = false;
            ensureCoverSelection();
          }
        }
      };

      const handleImagePreview = (input) => {
        if (!input) return;
        const wrapper = input.closest('.news-media-item');
        const previewImg = wrapper ? wrapper.querySelector('[data-role="preview-img"]') : null;
        const placeholder = wrapper ? wrapper.querySelector('[data-role="preview-placeholder"]') : null;
        const file = input.files && input.files[0] ? input.files[0] : null;

        if (!previewImg || !placeholder) {
          return;
        }

        if (!file) {
          previewImg.src = '';
          previewImg.classList.add('d-none');
          placeholder.classList.remove('d-none');
          return;
        }

        const reader = new FileReader();
        reader.onload = () => {
          previewImg.src = reader.result;
          previewImg.classList.remove('d-none');
          placeholder.classList.add('d-none');
        };
        reader.readAsDataURL(file);
      };

      const attachMediaItemEvents = (item, { isExisting = false } = {}) => {
        const moveUpBtn = item.querySelector('.media-move-up');
        const moveDownBtn = item.querySelector('.media-move-down');
        const removeBtn = item.querySelector('.media-remove');
        const deleteCheckbox = item.querySelector('[data-role="delete-checkbox"]');
        const fileInput = item.querySelector('[data-role="file-input"]');
        const coverRadio = item.querySelector('[data-role="cover-radio"]');

        if (moveUpBtn) {
          moveUpBtn.addEventListener('click', (event) => {
            event.preventDefault();
            moveItem(item, 'up');
          });
        }

        if (moveDownBtn) {
          moveDownBtn.addEventListener('click', (event) => {
            event.preventDefault();
            moveItem(item, 'down');
          });
        }

        if (removeBtn && !isExisting) {
          removeBtn.addEventListener('click', (event) => {
            event.preventDefault();
            item.remove();
            updateSortOrders();
            updateEmptyState();
            ensureCoverSelection();
          });
        }

        if (fileInput) {
          fileInput.addEventListener('change', () => handleImagePreview(fileInput));
        }

        if (deleteCheckbox) {
          deleteCheckbox.addEventListener('change', () => {
            handleDeleteToggle(item, deleteCheckbox.checked);
          });
          handleDeleteToggle(item, deleteCheckbox.checked);
        }

        if (coverRadio) {
          coverRadio.addEventListener('change', () => ensureCoverSelection());
        }
      };

      const buildImageItem = () => {
        if (!imageTemplate) return;
        mediaUidCounter += 1;
        const uid = `img${mediaUidCounter}`;

        const fragment = imageTemplate.content.cloneNode(true);
        const item = fragment.querySelector('.news-media-item');
        if (!item) return;

        const uidInput = item.querySelector('[data-role="uid-input"]');
        const coverRadio = item.querySelector('[data-role="cover-radio"]');
        const coverLabel = item.querySelector('[data-role="cover-label"]');
        const fileInput = item.querySelector('[data-role="file-input"]');

        if (uidInput) {
          uidInput.value = uid;
        }
        if (coverRadio) {
          coverRadio.value = `new-image|${uid}`;
          coverRadio.id = `mediaCoverNew${uid}`;
        }
        if (coverLabel && coverRadio) {
          coverLabel.setAttribute('for', coverRadio.id);
        }
        if (fileInput) {
          fileInput.addEventListener('change', () => handleImagePreview(fileInput));
        }

        mediaContainer.appendChild(item);
        attachMediaItemEvents(item, { isExisting: false });
        updateSortOrders();
        updateEmptyState();
        ensureCoverSelection();
      };

      const buildVideoItem = () => {
        if (!videoTemplate) return;
        mediaUidCounter += 1;
        const uid = `vid${mediaUidCounter}`;

        const fragment = videoTemplate.content.cloneNode(true);
        const item = fragment.querySelector('.news-media-item');
        if (!item) return;

        const uidInput = item.querySelector('[data-role="uid-input"]');
        const coverRadio = item.querySelector('[data-role="cover-radio"]');
        const coverLabel = item.querySelector('[data-role="cover-label"]');

        if (uidInput) {
          uidInput.value = uid;
        }
        if (coverRadio) {
          coverRadio.value = `new-video|${uid}`;
          coverRadio.id = `mediaCoverNew${uid}`;
        }
        if (coverLabel && coverRadio) {
          coverLabel.setAttribute('for', coverRadio.id);
        }

        mediaContainer.appendChild(item);
        attachMediaItemEvents(item, { isExisting: false });
        updateSortOrders();
        updateEmptyState();
        ensureCoverSelection();
      };

      if (addImageBtn) {
        addImageBtn.addEventListener('click', (event) => {
          event.preventDefault();
          buildImageItem();
        });
      }

      if (addVideoBtn) {
        addVideoBtn.addEventListener('click', (event) => {
          event.preventDefault();
          buildVideoItem();
        });
      }

      getMediaItems().forEach((item) => attachMediaItemEvents(item, { isExisting: true }));
      updateSortOrders();
      updateEmptyState();
      ensureCoverSelection();

      if (formElement) {
        formElement.addEventListener('submit', () => {
          updateSortOrders();
        });
      }
    }

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
    attachCounters();
  });
</script>
<?= $this->endSection() ?>
