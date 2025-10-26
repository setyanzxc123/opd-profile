﻿<?php
  $validation      = $validation ?? null;
  $publicLogoPath  = $profile['logo_public_path'] ?? null;
  $publicLogoUrl   = $publicLogoPath ? base_url($publicLogoPath) : null;
  $removePublicOld = old('remove_logo_public');
  $themeSettings   = is_array($themeSettings ?? null) ? $themeSettings : [];
  $themeDefaults   = is_array($themeDefaults ?? null) ? $themeDefaults : [
    'primary' => '#05A5A8',
    'neutral' => '#22303E',
    'surface' => '#F5F5F9',
  ];
  $normalizeThemeValue = static function ($value, string $fallback) {
    $candidate = is_string($value) ? trim($value) : '';
    $fallbackValue = trim($fallback) !== '' ? $fallback : '#05A5A8';

    if ($candidate === '') {
      $candidate = $fallbackValue;
    }

    if ($candidate !== '' && $candidate[0] !== '#') {
      $candidate = '#' . $candidate;
    }

    if (preg_match('/^#([0-9A-Fa-f]{3})$/', $candidate, $matches)) {
      $candidate = sprintf('#%1$s%1$s%2$s%2$s%3$s%3$s', $matches[1][0], $matches[1][1], $matches[1][2]);
    }

    if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $candidate)) {
      $candidate = $fallbackValue;
      if ($candidate !== '' && $candidate[0] !== '#') {
        $candidate = '#' . $candidate;
      }
      if (preg_match('/^#([0-9A-Fa-f]{3})$/', $candidate, $matches)) {
        $candidate = sprintf('#%1$s%1$s%2$s%2$s%3$s%3$s', $matches[1][0], $matches[1][1], $matches[1][2]);
      }
    }

    return strtoupper($candidate);
  };

  $themeFieldConfig = [
    'primary' => [
      'name'    => 'theme_primary_color',
      'label'   => 'Warna Utama',
      'helper'  => 'Dipakai untuk tombol primer, tautan aktif, dan elemen sorotan utama.',
      'value'   => $normalizeThemeValue(old('theme_primary_color'), $themeSettings['primary'] ?? ($themeDefaults['primary'] ?? '#05A5A8')),
      'default' => $normalizeThemeValue($themeDefaults['primary'] ?? '#05A5A8', '#05A5A8'),
    ],
    'surface' => [
      'name'    => 'theme_surface_color',
      'label'   => 'Warna Latar Permukaan',
      'helper'  => 'Menentukan warna latar belakang halaman dan kartu permukaan.',
      'value'   => $normalizeThemeValue(old('theme_surface_color'), $themeSettings['surface'] ?? ($themeDefaults['surface'] ?? '#F5F5F9')),
      'default' => $normalizeThemeValue($themeDefaults['surface'] ?? '#F5F5F9', '#F5F5F9'),
    ],
    'neutral' => [
      'name'    => 'theme_neutral_color',
      'label'   => 'Warna Teks Utama',
      'helper'  => 'Menjadi warna utama untuk teks dan judul agar tetap kontras.',
      'value'   => $normalizeThemeValue(old('theme_neutral_color'), $themeSettings['neutral'] ?? ($themeDefaults['neutral'] ?? '#22303E')),
      'default' => $normalizeThemeValue($themeDefaults['neutral'] ?? '#22303E', '#22303E'),
    ],
  ];
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('pageStyles') ?>
  <link rel="stylesheet" href="<?= base_url('assets/vendor/cropperjs/cropper.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/vendor/leaflet/leaflet.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/admin/profile-map.css') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/nano.min.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/admin/profile-theme.css') ?>">
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
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-theme-tab" data-bs-toggle="tab" data-bs-target="#tab-theme" type="button" role="tab" aria-controls="tab-theme" aria-selected="false">Tampilan & Warna</button>
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
                         data-crop-max="512"
                         data-meta-target="logo_public_meta"
                         data-preview-target="public">
                  <input type="hidden" name="logo_public_meta" id="logo_public_meta" data-logo-meta value="<?= esc(old('logo_public_meta', '')) ?>">
                  <?php if ($validation && $validation->hasError('logo_public')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('logo_public')) ?></div>
                  <?php else: ?>
                    <div class="form-text text-muted">Gunakan logo berkualitas; sisi terpanjang maksimal 512 piksel dan sisi terpendek minimal ±96 piksel. Rasio akan dipertahankan otomatis.</div>
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
              <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                  <div class="row g-4 g-xl-5 align-items-start">
                    <div class="col-12 col-xl-4">
                      <h6 class="fw-semibold mb-3">Informasi Kontak</h6>
                      <div class="mb-3">
                        <label class="form-label">Alamat Kantor</label>
                        <textarea name="address" rows="4" class="form-control" placeholder="Tuliskan alamat lengkap beserta patokan bila perlu."><?= esc(old('address', $profile['address'] ?? '')) ?></textarea>
                        <?php if ($validation && $validation->hasError('address')): ?>
                          <div class="form-text text-danger"><?= esc($validation->getError('address')) ?></div>
                        <?php endif; ?>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bx bx-phone"></i></span>
                          <input type="text" name="phone" class="form-control" value="<?= esc(old('phone', $profile['phone'] ?? '')) ?>" placeholder="(021) 555-1234">
                        </div>
                        <?php if ($validation && $validation->hasError('phone')): ?>
                          <div class="form-text text-danger"><?= esc($validation->getError('phone')) ?></div>
                        <?php endif; ?>
                      </div>
                      <div>
                        <label class="form-label">Email</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                          <input type="email" name="email" class="form-control" value="<?= esc(old('email', $profile['email'] ?? '')) ?>" placeholder="kontak@opd.go.id">
                        </div>
                        <?php if ($validation && $validation->hasError('email')): ?>
                          <div class="form-text text-danger"><?= esc($validation->getError('email')) ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="col-12 col-xl-8">
                      <div class="row g-4">
                        <div class="col-12">
                          <h6 class="fw-semibold mb-3">Koordinat & Zoom</h6>
                          <div class="row row-cols-1 row-cols-md-3 g-3">
                            <div class="col">
                              <label class="form-label">Latitude</label>
                              <input type="number" name="latitude" class="form-control" step="any" value="<?= esc(old('latitude', $profile['latitude'] ?? '')) ?>" placeholder="-6.1753920">
                              <?php if ($validation && $validation->hasError('latitude')): ?>
                                <div class="form-text text-danger"><?= esc($validation->getError('latitude')) ?></div>
                              <?php else: ?>
                                <div class="form-text text-muted">Gunakan format desimal dengan titik.</div>
                              <?php endif; ?>
                            </div>
                            <div class="col">
                              <label class="form-label">Longitude</label>
                              <input type="number" name="longitude" class="form-control" step="any" value="<?= esc(old('longitude', $profile['longitude'] ?? '')) ?>" placeholder="106.8271530">
                              <?php if ($validation && $validation->hasError('longitude')): ?>
                                <div class="form-text text-danger"><?= esc($validation->getError('longitude')) ?></div>
                              <?php else: ?>
                                <div class="form-text text-muted">Gunakan format desimal dengan titik.</div>
                              <?php endif; ?>
                            </div>
                            <div class="col">
                              <label class="form-label">Level Zoom</label>
                              <input type="number" name="map_zoom" class="form-control" min="1" max="20" value="<?= esc(old('map_zoom', $profile['map_zoom'] ?? '')) ?>" placeholder="16">
                              <?php if ($validation && $validation->hasError('map_zoom')): ?>
                                <div class="form-text text-danger"><?= esc($validation->getError('map_zoom')) ?></div>
                              <?php else: ?>
                                <div class="form-text text-muted">1 = jauh, 20 = sangat dekat.</div>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <h6 class="fw-semibold mb-0">Peta Interaktif</h6>
                            <?php $mapDisplayOld = old('map_display', (string) ($profile['map_display'] ?? '0')); ?>
                            <div class="form-check form-switch mb-0">
                              <input class="form-check-input" type="checkbox" id="field-map-display" name="map_display" value="1" <?= $mapDisplayOld === '1' ? 'checked' : '' ?>>
                              <label class="form-check-label small" for="field-map-display">Tampilkan di situs publik</label>
                            </div>
                          </div>
                          <?php if ($validation && $validation->hasError('map_display')): ?>
                            <div class="form-text text-danger mb-2"><?= esc($validation->getError('map_display')) ?></div>
                          <?php else: ?>
                            <div class="form-text text-muted mb-3">Aktifkan agar marker lokasi muncul pada halaman publik.</div>
                          <?php endif; ?>
                          <div class="profile-map-wrapper" data-map-wrapper>
                            <div class="profile-map-search-area mb-2" data-map-search-area>
                              <div class="input-group">
                                <span class="input-group-text" id="map-search-addon">
                                  <i class="bx bx-map-pin" aria-hidden="true"></i>
                                </span>
                                <input type="search" class="form-control" placeholder="Cari nama tempat atau alamat (minimal 3 karakter)" aria-label="Cari lokasi" aria-describedby="map-search-addon" data-map-search-input>
                                <button class="btn btn-outline-secondary" type="button" data-map-search-clear>
                                  <span class="d-none d-lg-inline">Bersihkan</span>
                                  <span class="d-inline d-lg-none"><i class="bx bx-x" aria-hidden="true"></i></span>
                                </button>
                              </div>
                              <div class="profile-map-search-results list-group shadow-sm d-none" data-map-search-results role="listbox" aria-label="Hasil pencarian lokasi"></div>
                            </div>
                            <div class="alert alert-warning py-2 px-3 profile-map-alert small" role="alert" data-map-feedback></div>
                            <div id="profile-map" data-search-url="<?= site_url('admin/profile/search-location') ?>" data-default-lat="-2.548926" data-default-lng="118.0148634" data-default-zoom="5" aria-label="Pratinjau peta lokasi OPD"></div>
                          </div>
                          <div class="form-text text-muted mt-3">
                            Geser marker atau klik peta untuk memperbarui koordinat. Gunakan pencarian di atas untuk menemukan titik dengan cepat.
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-theme" role="tabpanel" aria-labelledby="tab-theme-tab">
              <div class="row g-4">
                <div class="col-12 col-xl-7">
                  <input type="hidden" name="theme_reset" value="<?= esc(old('theme_reset', '0')) ?>" data-theme-reset-flag>
                  <div class="d-grid gap-4 theme-color-grid" data-theme-config>
                    <?php foreach ($themeFieldConfig as $key => $config): ?>
                      <div class="card border-0 shadow-sm theme-color-card" data-theme-item="<?= esc($key) ?>" data-default-color="<?= esc($config['default']) ?>">
                        <div class="card-body d-flex flex-column gap-3">
                          <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                              <h6 class="fw-semibold mb-1"><?= esc($config['label']) ?></h6>
                              <p class="text-muted small mb-0"><?= esc($config['helper']) ?></p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary theme-color-default" data-theme-default-trigger="<?= esc($key) ?>">
                              <i class="bx bx-reset me-1" aria-hidden="true"></i><span>Default</span>
                            </button>
                          </div>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                              <div class="flex-grow-1">
                                <label for="field-<?= esc($config['name']) ?>" class="visually-hidden"><?= esc($config['label']) ?></label>
                                <input
                                  type="text"
                                class="form-control theme-color-input"
                                id="field-<?= esc($config['name']) ?>"
                                name="<?= esc($config['name']) ?>"
                                value="<?= esc($config['value']) ?>"
                                inputmode="text"
                                pattern="^#?[0-9A-Fa-f]{6}$"
                                placeholder="<?= esc($config['default']) ?>"
                                data-theme-input="<?= esc($key) ?>"
                                autocomplete="off"
                                spellcheck="false"
                              >
                            </div>
                            <div class="theme-color-picker" data-theme-picker="<?= esc($key) ?>"></div>
                          </div>
                          <?php if ($validation && $validation->hasError($config['name'])): ?>
                            <div class="form-text text-danger"><?= esc($validation->getError($config['name'])) ?></div>
                          <?php else: ?>
                            <div class="form-text text-muted">Masukkan kode warna HEX enam digit, contoh <?= esc($config['default']) ?>.</div>
                          <?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                  <button type="button" class="btn btn-outline-secondary btn-sm theme-reset-all" data-theme-reset-all>
                    <i class="bx bx-undo me-1" aria-hidden="true"></i>Gunakan Tema Default
                  </button>
                </div>
                <p class="text-muted small mt-3 mb-0">
                  <i class="bx bx-info-circle me-1" aria-hidden="true"></i>
                  Perubahan warna akan berlaku untuk tampilan admin dan publik. Pastikan kontras tetap nyaman dibaca.
                </p>
              </div>
                <div class="col-12 col-xl-5">
                  <div class="card border-0 shadow-sm theme-preview-card" data-theme-preview>
                    <div class="card-body">
                      <div class="theme-preview-hero mb-3 rounded-4 p-4 text-white" data-theme-preview-hero>
                        <span class="text-uppercase small fw-semibold opacity-75 d-block mb-2">Pratinjau</span>
                        <h5 class="fw-bold mb-2">Judul Halaman</h5>
                        <p class="mb-3 text-white-50 small">Contoh hero dengan warna utama saat diterapkan.</p>
                        <div class="d-flex flex-wrap gap-2">
                          <button type="button" class="btn btn-light btn-sm fw-semibold" data-theme-preview-hero-cta>Mulai</button>
                          <button type="button" class="btn btn-outline-light btn-sm fw-semibold" data-theme-preview-hero-outline>Pelajari</button>
                        </div>
                      </div>
                      <div class="theme-preview-surface rounded-4 p-3" data-theme-preview-surface>
                        <div class="d-flex align-items-start gap-3 mb-3">
                          <div class="theme-preview-badge rounded-circle flex-shrink-0" data-theme-preview-accent></div>
                          <div class="flex-grow-1">
                            <h6 class="fw-semibold mb-1" data-theme-preview-heading>Judul Kartu</h6>
                            <p class="mb-0 small" data-theme-preview-text>Contoh teks isi untuk melihat keterbacaan warna netral.</p>
                          </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                          <button type="button" class="btn btn-primary btn-sm" disabled data-theme-preview-button-primary>Utama</button>
                          <button type="button" class="btn btn-outline-primary btn-sm" disabled data-theme-preview-button-outline>Sorotan</button>
                          <button type="button" class="btn btn-outline-secondary btn-sm" disabled data-theme-preview-button-muted>Latar</button>
                        </div>
                      </div>
                    </div>
                  </div>
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
          Seret atau zoom untuk menyesuaikan logo. Sisi terpanjang akan disesuaikan maksimal <span data-cropper-max>512</span> piksel tanpa mengubah rasio.
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
  <script src="<?= base_url('assets/vendor/leaflet/leaflet.js') ?>" defer></script>
  <script src="<?= base_url('assets/js/admin/profile-map.js') ?>" defer></script>
  <script src="<?= base_url('assets/js/admin/profile-logos.js') ?>" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js" defer></script>
  <script src="<?= base_url('assets/js/admin/profile-theme.js') ?>" defer></script>
<?= $this->endSection() ?>





