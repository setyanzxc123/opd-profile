<?php
  $validation      = $validation ?? null;
  $publicLogoPath  = $profile['logo_public_path'] ?? null;
  $publicLogoUrl   = $publicLogoPath ? base_url($publicLogoPath) : null;
  $removePublicOld = old('remove_logo_public');
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('pageStyles') ?>
  <link rel="stylesheet" href="<?= base_url('assets/vendor/cropperjs/cropper.min.css') ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header border-0 bg-transparent pb-0">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
          <div>
            <h4 class="fw-bold mb-1">Profil</h4>
            <p class="text-muted mb-0">Perbarui informasi instansi yang tampil pada situs publik.</p>
          </div>
          <a href="<?= site_url('admin') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Dashboard
          </a>
        </div>
      </div>

      <div class="card-body pt-3">
        <?php if (session()->getFlashdata('message')): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert" aria-live="polite">
            <i class="bx bx-check-circle me-2"></i><?= esc(session('message')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert" aria-live="assertive">
            <i class="bx bx-error-circle me-2"></i><?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>

        <div class="alert alert-info" role="alert">
          <i class="bx bx-bulb me-2"></i>
          <span>Gunakan konten yang ringkas dan mudah dibaca. Perubahan akan langsung terlihat oleh pengunjung.</span>
        </div>

        <form method="post" action="<?= site_url('admin/profile') ?>" class="pt-2" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= esc($profile['id']) ?>">

          <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="tab-umum-tab" data-bs-toggle="tab" data-bs-target="#tab-umum" type="button" role="tab" aria-controls="tab-umum" aria-selected="true">Umum</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-visimisi-tab" data-bs-toggle="tab" data-bs-target="#tab-visimisi" type="button" role="tab" aria-controls="tab-visimisi" aria-selected="false">Visi & Misi</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-kontak-tab" data-bs-toggle="tab" data-bs-target="#tab-kontak" type="button" role="tab" aria-controls="tab-kontak" aria-selected="false">Kontak & Lokasi</button>
            </li>
          </ul>

          <div class="tab-content border border-top-0 p-3">
            <div class="tab-pane fade show active" id="tab-umum" role="tabpanel" aria-labelledby="tab-umum-tab">
              <div class="row g-3">
                <div class="col-12 col-lg-9 col-xl-8">
                  <label class="form-label">Nama OPD <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control" required maxlength="150" value="<?= esc(old('name', $profile['name'])) ?>" placeholder="Contoh: Dinas Komunikasi dan Informatika">
                  <?php if ($validation && $validation->hasError('name')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('name')) ?></div>
                  <?php else: ?>
                    <div class="form-text text-muted">Nama resmi instansi akan tampil di header situs publik.</div>
                  <?php endif; ?>
                </div>

                <div class="col-12 col-lg-10">
                  <label class="form-label">Deskripsi Singkat</label>
                  <textarea name="description" rows="4" class="form-control" placeholder="Tuliskan deskripsi ringkas mengenai profil dan layanan utama OPD."><?= esc(old('description', $profile['description'])) ?></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label d-block">Logo</label>
                  <div class="profile-logo-preview-wrapper mb-2<?= $publicLogoUrl ? ' has-image' : '' ?>" data-logo-preview-wrapper="public">
                    <img
                      src="<?= esc($publicLogoUrl ?? '') ?>"
                      alt="Pratinjau logo"
                      class="profile-logo-preview img-thumbnail<?= $publicLogoUrl ? '' : ' d-none' ?>"
                      data-logo-preview-target="public"
                      data-default-src="<?= esc($publicLogoUrl ?? '') ?>"
                      <?= $publicLogoUrl ? '' : 'hidden' ?>>
                    <div class="profile-logo-empty<?= $publicLogoUrl ? ' d-none' : '' ?>" data-logo-preview-empty="public"<?= $publicLogoUrl ? ' hidden' : '' ?>>
                      <span class="text-muted small">Belum ada logo. Unggah untuk menampilkan identitas OPD.</span>
                    </div>
                  </div>
                  <input type="file"
                         name="logo_public"
                         class="form-control"
                         accept=".jpg,.jpeg,.png,.webp,.gif"
                         data-logo-input
                         data-crop-key="public"
                         data-crop-label="Logo OPD"
                         data-crop-aspect="1"
                         data-crop-max="512"
                         data-meta-target="logo_public_meta"
                         data-preview-target="public">
                  <input type="hidden" name="logo_public_meta" id="logo_public_meta" data-logo-meta value="<?= esc(old('logo_public_meta', '')) ?>">
                  <?php if ($validation && $validation->hasError('logo_public')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('logo_public')) ?></div>
                  <?php else: ?>
                    <div class="form-text text-muted">Disarankan rasio persegi, minimal 96x96 piksel, maksimum 512 piksel.</div>
                  <?php endif; ?>
                  <div class="form-text text-muted">Format yang didukung: JPG, PNG, WEBP, GIF (maksimal 3 MB).</div>
                  <div class="form-check mt-2">
                    <input class="form-check-input"
                           type="checkbox"
                           id="remove-logo-public"
                           name="remove_logo_public"
                           value="1"
                           data-logo-remove
                           data-preview-target="public"
                           <?= ($removePublicOld === '1' || $removePublicOld === 'on') ? 'checked' : '' ?>>
                    <label class="form-check-label small text-muted" for="remove-logo-public">Hapus logo saat ini</label>
                  </div>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-visimisi" role="tabpanel" aria-labelledby="tab-visimisi-tab">
              <div class="row g-3">
                <div class="col-12 col-lg-10">
                  <label class="form-label">Visi</label>
                  <textarea name="vision" rows="3" class="form-control" placeholder="Masukkan rumusan visi instansi."><?= esc(old('vision', $profile['vision'])) ?></textarea>
                </div>
                <div class="col-12 col-lg-10">
                  <label class="form-label">Misi</label>
                  <textarea name="mission" rows="4" class="form-control" placeholder="Jabarkan poin-poin misi instansi."><?= esc(old('mission', $profile['mission'])) ?></textarea>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-kontak" role="tabpanel" aria-labelledby="tab-kontak-tab">
              <div class="row g-3">
                <div class="col-lg-8">
                  <label class="form-label">Alamat Kantor</label>
                  <textarea name="address" rows="3" class="form-control" placeholder="Tuliskan alamat lengkap beserta patokan bila perlu."><?= esc(old('address', $profile['address'] ?? '')) ?></textarea>
                </div>
                <div class="col-md-6 col-lg-4">
                  <label class="form-label">Telepon</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-phone"></i></span>
                     <input type="text" name="phone" class="form-control" value="<?= esc(old('phone', $profile['phone'] ?? '')) ?>" placeholder="(021) 555-1234">
                  </div>
                  <?php if ($validation && $validation->hasError('phone')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('phone')) ?></div>
                  <?php endif; ?>
                </div>
                <div class="col-md-6 col-lg-4">
                  <label class="form-label">Email</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                     <input type="email" name="email" class="form-control" value="<?= esc(old('email', $profile['email'] ?? '')) ?>" placeholder="kontak@opd.go.id">
                  </div>
                  <?php if ($validation && $validation->hasError('email')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('email')) ?></div>
                  <?php endif; ?>
                </div>
                <div class="col-md-6 col-lg-4">
                  <label class="form-label">Latitude</label>
                  <input type="number"
                         name="latitude"
                         class="form-control"
                         step="any"
                         value="<?= esc(old('latitude', $profile['latitude'] ?? '')) ?>"
                         placeholder="-6.1753920">
                  <?php if ($validation && $validation->hasError('latitude')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('latitude')) ?></div>
                  <?php else: ?>
                    <div class="form-text text-muted">Koordinat lintang dalam format desimal (gunakan titik).</div>
                  <?php endif; ?>
                </div>
                <div class="col-md-6 col-lg-4">
                  <label class="form-label">Longitude</label>
                  <input type="number"
                         name="longitude"
                         class="form-control"
                         step="any"
                         value="<?= esc(old('longitude', $profile['longitude'] ?? '')) ?>"
                         placeholder="106.8271530">
                  <?php if ($validation && $validation->hasError('longitude')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('longitude')) ?></div>
                  <?php else: ?>
                    <div class="form-text text-muted">Koordinat bujur dalam format desimal (gunakan titik).</div>
                  <?php endif; ?>
                </div>
                <div class="col-md-6 col-lg-4">
                  <label class="form-label">Level Zoom Peta</label>
                  <input type="number"
                         name="map_zoom"
                         class="form-control"
                         min="1"
                         max="20"
                         value="<?= esc(old('map_zoom', $profile['map_zoom'] ?? '')) ?>"
                         placeholder="16">
                  <?php if ($validation && $validation->hasError('map_zoom')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('map_zoom')) ?></div>
                  <?php else: ?>
                    <div class="form-text text-muted">Nilai antara 1 (jauh) hingga 20 (sangat dekat). Kosongkan untuk default.</div>
                  <?php endif; ?>
                </div>
                <div class="col-12">
                  <?php $mapDisplayOld = old('map_display', (string) ($profile['map_display'] ?? '0')); ?>
                  <div class="form-check form-switch">
                    <input class="form-check-input"
                           type="checkbox"
                           id="field-map-display"
                           name="map_display"
                           value="1"
                           <?= $mapDisplayOld === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="field-map-display">Tampilkan peta lokasi pada halaman publik</label>
                  </div>
                  <?php if ($validation && $validation->hasError('map_display')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('map_display')) ?></div>
                  <?php else: ?>
                    <div class="form-text text-muted">Aktifkan agar peta dengan marker OPD muncul di footer publik.</div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Wajib diisi.</p>
            <div class="d-flex flex-wrap gap-2">
              <button type="reset" class="btn btn-outline-secondary"><i class="bx bx-reset me-1"></i> Atur Ulang</button>
              <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="logoCropperModal" tabindex="-1" aria-labelledby="logoCropperModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoCropperModalLabel" data-cropper-title>Sesuaikan Logo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="logo-cropper-canvas border rounded bg-light overflow-hidden">
          <img src="" alt="Area cropping logo" class="w-100 h-100 object-fit-contain" data-cropper-image>
        </div>
        <p class="small text-muted mt-3 mb-0">
          Seret atau zoom untuk menyesuaikan logo. Ukuran akhir akan disesuaikan maksimal <span data-cropper-max>512</span> piksel.
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batalkan</button>
        <button type="button" class="btn btn-primary" data-cropper-confirm>Gunakan Logo</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
  <script src="<?= base_url('assets/vendor/cropperjs/cropper.min.js') ?>"></script>
  <script src="<?= base_url('assets/js/admin/profile-logos.js') ?>" defer></script>
<?= $this->endSection() ?>


