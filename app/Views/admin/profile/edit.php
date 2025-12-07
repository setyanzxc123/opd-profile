<?php
  use App\Services\ProfileAdminService;

  $validation      = $validation ?? null;
  $publicLogoPath  = $profile['logo_public_path'] ?? null;
  $publicLogoUrl   = $publicLogoPath ? base_url($publicLogoPath) : null;
  $removePublicOld = old('remove_logo_public');
  $themeSettings   = is_array($themeSettings ?? null) ? $themeSettings : [];
  $themeDefaults   = is_array($themeDefaults ?? null) ? $themeDefaults : [
    'primary' => '#046C72',
    'neutral' => '#22303E',
    'surface' => '#F5F5F9',
  ];
  $themePresets      = is_array($themePresets ?? null) ? $themePresets : [];
  $themeModeOptions  = [
    ProfileAdminService::THEME_MODE_PRESET => 'Preset Aman',
    ProfileAdminService::THEME_MODE_CUSTOM => 'Warna OPD',
  ];
  $activeThemeMode   = old('theme_mode', $activeThemeMode ?? ProfileAdminService::THEME_MODE_PRESET);
  if (! array_key_exists($activeThemeMode, $themeModeOptions)) {
    $activeThemeMode = ProfileAdminService::THEME_MODE_PRESET;
  }
  if ($themePresets === [] && $activeThemeMode === ProfileAdminService::THEME_MODE_PRESET) {
    $activeThemeMode = ProfileAdminService::THEME_MODE_CUSTOM;
  }
  $activeThemePreset = old('theme_preset', $activeThemePreset ?? array_key_first($themePresets));
  if ($themePresets !== [] && ! array_key_exists($activeThemePreset, $themePresets)) {
    $activeThemePreset = array_key_first($themePresets);
  }
  $themeCustomDefaults = is_array($themeCustomDefaults ?? null)
    ? $themeCustomDefaults
    : [
      'primary' => $themeSettings['primary'] ?? ($themeDefaults['primary'] ?? '#046C72'),
      'surface' => $themeSettings['surface'] ?? ($themeDefaults['surface'] ?? '#F5F5F9'),
    ];
  $normalizeColorValue = static function ($value, string $fallback) {
    $candidate = is_string($value) ? trim($value) : '';
    if ($candidate === '') {
      $candidate = $fallback;
    }
    if ($candidate !== '' && $candidate[0] !== '#') {
      $candidate = '#' . ltrim($candidate, '#');
    }
    if (strlen($candidate) === 4) {
      $candidate = '#' . $candidate[1] . $candidate[1] . $candidate[2] . $candidate[2] . $candidate[3] . $candidate[3];
    }
    if (strlen($candidate) !== 7) {
      $candidate = $fallback;
    }

    return strtoupper($candidate);
  };
  $customThemeValues = [
    'primary' => $normalizeColorValue(old('theme_primary_color', $themeCustomDefaults['primary'] ?? '#046C72'), $themeDefaults['primary'] ?? '#046C72'),
    'surface' => $normalizeColorValue(old('theme_surface_color', $themeCustomDefaults['surface'] ?? '#F5F5F9'), $themeDefaults['surface'] ?? '#F5F5F9'),
  ];
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('pageStyles') ?>
  <link rel="stylesheet" href="<?= base_url('assets/vendor/cropperjs/cropper.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/admin/profile-theme.css') ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/admin/profile-edit.css') ?>">

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

          <div class="row g-3">
            <!-- Sidebar Navigation -->
            <div class="col-md-3">
              <div class="nav flex-column nav-pills" id="profileTabs" role="tablist" aria-orientation="vertical">
                <button class="nav-link active" id="tab-umum-tab" data-bs-toggle="pill" data-bs-target="#tab-umum" type="button" role="tab" aria-controls="tab-umum" aria-selected="true">
                  <i class="bx bx-home-circle me-2"></i>Umum
                </button>
                <button class="nav-link" id="tab-visimisi-tab" data-bs-toggle="pill" data-bs-target="#tab-visimisi" type="button" role="tab" aria-controls="tab-visimisi" aria-selected="false">
                  <i class="bx bx-bullseye me-2"></i>Visi & Misi
                </button>
                <button class="nav-link" id="tab-sambutan-tab" data-bs-toggle="pill" data-bs-target="#tab-sambutan" type="button" role="tab" aria-controls="tab-sambutan" aria-selected="false">
                  <i class="bx bx-chat me-2"></i>Sambutan
                </button>
                <button class="nav-link" id="tab-tugas-tab" data-bs-toggle="pill" data-bs-target="#tab-tugas" type="button" role="tab" aria-controls="tab-tugas" aria-selected="false">
                  <i class="bx bx-task me-2"></i>Tugas & Fungsi
                </button>
                <button class="nav-link" id="tab-kontak-tab" data-bs-toggle="pill" data-bs-target="#tab-kontak" type="button" role="tab" aria-controls="tab-kontak" aria-selected="false">
                  <i class="bx bx-map-pin me-2"></i>Kontak & Lokasi
                </button>
                <button class="nav-link" id="tab-social-tab" data-bs-toggle="pill" data-bs-target="#tab-social" type="button" role="tab" aria-controls="tab-social" aria-selected="false">
                  <i class="bx bx-share-alt me-2"></i>Media Sosial
                </button>
                <button class="nav-link" id="tab-org-tab" data-bs-toggle="pill" data-bs-target="#tab-org" type="button" role="tab" aria-controls="tab-org" aria-selected="false">
                  <i class="bx bx-network-chart me-2"></i>Struktur Organisasi
                </button>
                <button class="nav-link" id="tab-theme-tab" data-bs-toggle="pill" data-bs-target="#tab-theme" type="button" role="tab" aria-controls="tab-theme" aria-selected="false">
                  <i class="bx bx-paint me-2"></i>Tampilan & Warna
                </button>
              </div>
            </div>

            <!-- Tab Content -->
            <div class="col-md-9">
              <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-umum" role="tabpanel" aria-labelledby="tab-umum-tab">
              <div class="row g-4">
                <div class="col-12 col-lg-8">
                  <div class="d-grid gap-4">
                    <div>
                      <label class="form-label">Nama OPD <span class="text-danger">*</span></label>
                      <input type="text" name="name" class="form-control" required maxlength="150" value="<?= esc(old('name', $profile['name'])) ?>" placeholder="Contoh: Dinas Komunikasi dan Informatika">
                      <?php if ($validation && $validation->hasError('name')): ?>
                        <div class="form-text text-danger"><?= esc($validation->getError('name')) ?></div>
                      <?php else: ?>
                        <div class="form-text text-muted">Nama resmi instansi (baris pertama) akan tampil di header situs publik.</div>
                      <?php endif; ?>
                    </div>

                    <div>
                      <label class="form-label">Nama OPD Baris Kedua <span class="text-muted small">(Opsional)</span></label>
                      <input type="text" name="name_line2" class="form-control" maxlength="150" value="<?= esc(old('name_line2', $profile['name_line2'] ?? '')) ?>" placeholder="Contoh: Kabupaten Donggala">
                      <?php if ($validation && $validation->hasError('name_line2')): ?>
                        <div class="form-text text-danger"><?= esc($validation->getError('name_line2')) ?></div>
                      <?php else: ?>
                        <div class="form-text text-muted">Baris kedua (opsional) akan ditampilkan dengan ukuran font lebih kecil.</div>
                      <?php endif; ?>
                    </div>

                    <div>
                      <label class="form-label">Deskripsi Singkat</label>
                      <textarea name="description" rows="4" class="form-control" placeholder="Tuliskan deskripsi ringkas mengenai profil dan layanan utama OPD."><?= esc(old('description', $profile['description'])) ?></textarea>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-lg-4">
                  <div>
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
                    <div class="form-check mt-2">
                      <input class="form-check-input"
                             type="checkbox"
                             id="hide-brand-text"
                             name="hide_brand_text"
                             value="1"
                             <?= (old('hide_brand_text', $profile['hide_brand_text'] ?? '0') == '1') ? 'checked' : '' ?>>
                      <label class="form-check-label small" for="hide-brand-text">
                        <strong>Sembunyikan nama OPD di navbar</strong>
                        <div class="text-muted" style="font-size: 0.85rem;">Aktifkan jika logo sudah include text nama OPD</div>
                      </label>
                    </div>
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

            <div class="tab-pane fade" id="tab-sambutan" role="tabpanel" aria-labelledby="tab-sambutan-tab">
              <div class="row g-3">
                <div class="col-12">
                  <div class="mb-3">
                    <label class="form-label fw-semibold" for="greetingContent">Sambutan Kepala OPD</label>
                    <p class="text-muted small mb-2">Tulis kata sambutan dari Kepala OPD untuk pengunjung situs. Gunakan toolbar untuk menambahkan heading, gambar, dan format lainnya.</p>
                  </div>
                  <textarea id="greetingContent" name="greeting" class="form-control" rows="12" placeholder="Tulis kata sambutan kepala instansi atau pesan selamat datang untuk pengunjung situs..."><?= old('greeting', $profile['greeting'] ?? '') ?></textarea>
                  <?php if ($validation && $validation->hasError('greeting')): ?>
                    <div class="form-text text-danger mt-1"><?= esc($validation->getError('greeting')) ?></div>
                  <?php else: ?>
                    <div class="form-text text-muted mt-2">Sambutan ini akan ditampilkan sebagai halaman terpisah di bagian Profil publik.</div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-tugas" role="tabpanel" aria-labelledby="tab-tugas-tab">
              <div class="row g-3">
                <div class="col-12 col-lg-10">
                  <label class="form-label">Tugas dan Fungsi</label>
                  <textarea name="tasks_functions" rows="10" class="form-control" placeholder="Jabarkan tugas pokok dan fungsi instansi secara lengkap."><?= esc(old('tasks_functions', $profile['tasks_functions'] ?? '')) ?></textarea>
                  <div class="form-text text-muted">Tugas dan fungsi ini akan ditampilkan sebagai halaman terpisah di bagian Profil publik.</div>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-kontak" role="tabpanel" aria-labelledby="tab-kontak-tab">
              <div class="row g-4">
                <div class="col-12">
                  <div class="card border-0 shadow-sm">
                    <div class="card-body">
                      <h6 class="fw-semibold mb-3">Informasi Kontak</h6>
                      <div class="row g-3">
                        <div class="col-12 col-md-6">
                          <label class="form-label">Alamat Kantor</label>
                          <textarea name="address" rows="4" class="form-control" placeholder="Tuliskan alamat lengkap beserta patokan bila perlu."><?= esc(old('address', $profile['address'] ?? '')) ?></textarea>
                          <?php if ($validation && $validation->hasError('address')): ?>
                            <div class="form-text text-danger"><?= esc($validation->getError('address')) ?></div>
                          <?php endif; ?>
                        </div>
                        <div class="col-12 col-md-6">
                          <div class="d-grid gap-3">
                            <div>
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
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="card border-0 shadow-sm">
                    <div class="card-body">
                      <h6 class="fw-semibold mb-3">Koordinat & Lokasi</h6>
                      <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                          <label class="form-label">Latitude</label>
                          <input type="number" name="latitude" class="form-control" step="any" value="<?= esc(old('latitude', $profile['latitude'] ?? '')) ?>" placeholder="-6.1753920">
                          <?php if ($validation && $validation->hasError('latitude')): ?>
                            <div class="form-text text-danger"><?= esc($validation->getError('latitude')) ?></div>
                          <?php else: ?>
                            <div class="form-text text-muted">Gunakan format desimal dengan titik.</div>
                          <?php endif; ?>
                        </div>
                        <div class="col-12 col-md-4">
                          <label class="form-label">Longitude</label>
                          <input type="number" name="longitude" class="form-control" step="any" value="<?= esc(old('longitude', $profile['longitude'] ?? '')) ?>" placeholder="106.8271530">
                          <?php if ($validation && $validation->hasError('longitude')): ?>
                            <div class="form-text text-danger"><?= esc($validation->getError('longitude')) ?></div>
                          <?php else: ?>
                            <div class="form-text text-muted">Gunakan format desimal dengan titik.</div>
                          <?php endif; ?>
                        </div>
                        <div class="col-12 col-md-4">
                          <label class="form-label">Level Zoom</label>
                          <input type="number" name="map_zoom" class="form-control" min="1" max="20" value="<?= esc(old('map_zoom', $profile['map_zoom'] ?? '')) ?>" placeholder="16">
                          <?php if ($validation && $validation->hasError('map_zoom')): ?>
                            <div class="form-text text-danger"><?= esc($validation->getError('map_zoom')) ?></div>
                          <?php else: ?>
                            <div class="form-text text-muted">1 = jauh, 20 = sangat dekat.</div>
                          <?php endif; ?>
                        </div>
                      </div>

                      <?php
                          $mapDisplayOld     = old('map_display', (string) ($profile['map_display'] ?? '0'));
                          $latPreviewValue   = old('latitude', $profile['latitude'] ?? '');
                          $lngPreviewValue   = old('longitude', $profile['longitude'] ?? '');
                          $zoomPreviewValue  = old('map_zoom', $profile['map_zoom'] ?? '');
                          $latIsNumeric      = is_numeric($latPreviewValue);
                          $lngIsNumeric      = is_numeric($lngPreviewValue);
                          $hasMapPreview     = $latIsNumeric && $lngIsNumeric;
                          $mapZoomValue      = is_numeric($zoomPreviewValue) ? (int) $zoomPreviewValue : 16;
                          if ($mapZoomValue < 1 || $mapZoomValue > 20) {
                            $mapZoomValue = 16;
                          }
                          $mapPreviewUrl = '';
                          if ($hasMapPreview) {
                            $llParameter = rawurlencode(trim((string) $latPreviewValue) . ',' . trim((string) $lngPreviewValue));
                            $opdName     = trim((string) ($profile['name'] ?? 'Lokasi OPD'));
                            if ($opdName === '') {
                              $opdName = 'Lokasi OPD';
                            }
                            $mapPreviewUrl = 'https://www.google.com/maps?q=' . rawurlencode($opdName) . '&ll=' . $llParameter . '&z=' . rawurlencode((string) $mapZoomValue) . '&output=embed';
                          }
                        ?>
                      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h6 class="fw-semibold mb-0">Pratinjau Peta Google Maps</h6>
                        <div class="form-check form-switch mb-0">
                          <input class="form-check-input" type="checkbox" id="field-map-display" name="map_display" value="1" <?= $mapDisplayOld === '1' ? 'checked' : '' ?>>
                          <label class="form-check-label small" for="field-map-display">Tampilkan di situs publik</label>
                        </div>
                      </div>
                      <?php if ($validation && $validation->hasError('map_display')): ?>
                        <div class="form-text text-danger mb-2"><?= esc($validation->getError('map_display')) ?></div>
                      <?php else: ?>
                        <div class="form-text text-muted mb-3">Aktifkan agar peta iframe muncul pada footer halaman publik.</div>
                      <?php endif; ?>

                      <div class="alert alert-info small">
                        <p class="mb-2 fw-semibold text-info">Cara mendapatkan koordinat:</p>
                        <ol class="ps-3 mb-2">
                          <li>Buka Google Maps, klik kanan lokasi kantor OPD.</li>
                          <li>Pilih <strong>Salin koordinat</strong>, lalu tempel ke kolom Latitude dan Longitude.</li>
                          <li>Atur level zoom sesuai kebutuhan tampilan embed (1 = jauh, 20 = sangat dekat).</li>
                        </ol>
                        <p class="mb-0">Nilai yang disimpan hanya dipakai untuk iframe sederhana di situs publik sehingga panel admin tetap ringan.</p>
                      </div>

                      <div class="ratio ratio-4x3 rounded-3 border bg-light overflow-hidden mt-3">
                        <?php if ($hasMapPreview): ?>
                          <iframe
                            src="<?= esc($mapPreviewUrl) ?>"
                            title="Pratinjau lokasi Google Maps"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen>
                          </iframe>
                        <?php else: ?>
                          <div class="d-flex flex-column justify-content-center align-items-center text-center px-3">
                            <i class="bx bx-map-pin display-6 text-muted mb-2" aria-hidden="true"></i>
                            <p class="mb-0 text-muted small">Isi koordinat untuk melihat pratinjau embed Google Maps.</p>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>



            <div class="tab-pane fade" id="tab-social" role="tabpanel" aria-labelledby="tab-social-tab">
              <div class="card border-0 shadow-sm">
                <div class="card-body">
                  <h6 class="fw-semibold mb-3">Pengaturan Media Sosial</h6>
                  <p class="text-muted small mb-4">Kelola tautan media sosial yang akan ditampilkan pada navigasi publik. Gunakan sakelar (switch) untuk mengaktifkan atau menonaktifkan platform tertentu.</p>
                  
                  <div class="row g-4">
                    <div class="col-12 col-lg-8">
                      <div class="d-grid gap-4">
                        
                        <!-- Facebook -->
                        <div class="p-3 border rounded bg-light-subtle">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-medium mb-0 d-flex align-items-center">
                              <i class="bx bxl-facebook-circle fs-4 me-2 text-primary"></i>Facebook
                            </label>
                            <div class="form-check form-switch">
                              <input type="hidden" name="social_facebook_active" value="0">
                              <input class="form-check-input" type="checkbox" name="social_facebook_active" value="1" id="social_facebook_active" <?= ($profile['social_facebook_active'] ?? '1') == '1' ? 'checked' : '' ?>>
                              <label class="form-check-label small" for="social_facebook_active">Aktif</label>
                            </div>
                          </div>
                          <input type="url" name="social_facebook" class="form-control" value="<?= esc(old('social_facebook', $profile['social_facebook'] ?? '')) ?>" placeholder="https://facebook.com/...">
                        </div>

                        <!-- Instagram -->
                        <div class="p-3 border rounded bg-light-subtle">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-medium mb-0 d-flex align-items-center">
                              <i class="bx bxl-instagram fs-4 me-2 text-danger"></i>Instagram
                            </label>
                            <div class="form-check form-switch">
                              <input type="hidden" name="social_instagram_active" value="0">
                              <input class="form-check-input" type="checkbox" name="social_instagram_active" value="1" id="social_instagram_active" <?= ($profile['social_instagram_active'] ?? '1') == '1' ? 'checked' : '' ?>>
                              <label class="form-check-label small" for="social_instagram_active">Aktif</label>
                            </div>
                          </div>
                          <input type="url" name="social_instagram" class="form-control" value="<?= esc(old('social_instagram', $profile['social_instagram'] ?? '')) ?>" placeholder="https://instagram.com/...">
                        </div>

                        <!-- Twitter -->
                        <div class="p-3 border rounded bg-light-subtle">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-medium mb-0 d-flex align-items-center">
                              <i class="bx bxl-twitter fs-4 me-2 text-info"></i>Twitter / X
                            </label>
                            <div class="form-check form-switch">
                              <input type="hidden" name="social_twitter_active" value="0">
                              <input class="form-check-input" type="checkbox" name="social_twitter_active" value="1" id="social_twitter_active" <?= ($profile['social_twitter_active'] ?? '1') == '1' ? 'checked' : '' ?>>
                              <label class="form-check-label small" for="social_twitter_active">Aktif</label>
                            </div>
                          </div>
                          <input type="url" name="social_twitter" class="form-control" value="<?= esc(old('social_twitter', $profile['social_twitter'] ?? '')) ?>" placeholder="https://twitter.com/...">
                        </div>

                        <!-- YouTube -->
                        <div class="p-3 border rounded bg-light-subtle">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-medium mb-0 d-flex align-items-center">
                              <i class="bx bxl-youtube fs-4 me-2 text-danger"></i>YouTube
                            </label>
                            <div class="form-check form-switch">
                              <input type="hidden" name="social_youtube_active" value="0">
                              <input class="form-check-input" type="checkbox" name="social_youtube_active" value="1" id="social_youtube_active" <?= ($profile['social_youtube_active'] ?? '1') == '1' ? 'checked' : '' ?>>
                              <label class="form-check-label small" for="social_youtube_active">Aktif</label>
                            </div>
                          </div>
                          <input type="url" name="social_youtube" class="form-control" value="<?= esc(old('social_youtube', $profile['social_youtube'] ?? '')) ?>" placeholder="https://youtube.com/...">
                        </div>

                        <!-- TikTok -->
                        <div class="p-3 border rounded bg-light-subtle">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-medium mb-0 d-flex align-items-center">
                              <i class="bx bxl-tiktok fs-4 me-2 text-dark"></i>TikTok
                            </label>
                            <div class="form-check form-switch">
                              <input type="hidden" name="social_tiktok_active" value="0">
                              <input class="form-check-input" type="checkbox" name="social_tiktok_active" value="1" id="social_tiktok_active" <?= ($profile['social_tiktok_active'] ?? '1') == '1' ? 'checked' : '' ?>>
                              <label class="form-check-label small" for="social_tiktok_active">Aktif</label>
                            </div>
                          </div>
                          <input type="url" name="social_tiktok" class="form-control" value="<?= esc(old('social_tiktok', $profile['social_tiktok'] ?? '')) ?>" placeholder="https://tiktok.com/@...">
                        </div>

                      </div>
                    </div>
                    
                    <div class="col-12 col-lg-4">
                      <div class="alert alert-info">
                        <div class="d-flex">
                          <i class="bx bx-info-circle fs-4 me-2"></i>
                          <div>
                            <h6 class="alert-heading fw-bold mb-1">Informasi</h6>
                            <p class="mb-0 small">Ikon media sosial hanya akan muncul di situs publik jika statusnya <strong>Aktif</strong> dan kolom tautan (URL) telah diisi.</p>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-org" role="tabpanel" aria-labelledby="tab-org-tab">
              <div class="card border-0 shadow-sm">
                <div class="card-body">
                  <h6 class="fw-semibold mb-3">Diagram Struktur Organisasi</h6>
                  <p class="text-muted small mb-4">Unggah diagram struktur organisasi yang telah dibuat di PowerPoint, Canva, CorelDraw, atau tools design lainnya.</p>
                  
                  <?php
                    $orgImgPath = $profile['org_structure_image'] ?? null;
                    $orgImgUrl = $orgImgPath ? base_url($orgImgPath) : null;
                    $orgAltText = old('org_structure_alt_text', $profile['org_structure_alt_text'] ?? '');
                    $orgUpdatedAt = $profile['org_structure_updated_at'] ?? null;
                    $removeOrgOld = old('remove_org_structure');
                  ?>
                  
                  <div class="row g-4">
                    <div class="col-12">
                      <?php if ($orgImgUrl): ?>
                        <label class="form-label d-block">Preview Saat Ini</label>
                        <div class="border rounded p-2 bg-light mb-3" style="max-height: 400px; overflow: auto;">
                          <img src="<?= esc($orgImgUrl) ?>" alt="Preview struktur organisasi" class="img-fluid w-100">
                        </div>
                        <?php if ($orgUpdatedAt): ?>
                          <p class="small text-muted mb-3">Terakhir diperbarui: <?= esc($orgUpdatedAt) ?></p>
                        <?php endif; ?>
                      <?php else: ?>
                        <div class="alert alert-info">
                         <i class="bx bx-info-circle me-2"></i> Belum ada diagram struktur organisasi. Unggah gambar untuk menampilkan di halaman publik.
                        </div>
                      <?php endif; ?>
                    </div>
                    
                    <div class="col-12 col-lg-8">
                      <label class="form-label">Upload Gambar Diagram Struktur Organisasi</label>
                      <input type="file"
                             name="org_structure_image"
                             class="form-control"
                             accept=".jpg,.jpeg,.png,.webp">
                      <?php if ($validation && $validation->hasError('org_structure_image')): ?>
                        <div class="form-text text-danger"><?= esc($validation->getError('org_structure_image')) ?></div>
                      <?php else: ?>
                        <div class="form-text text-muted">Format yang didukung: JPG, PNG, WEBP (maksimal 5 MB).</div>
                        <div class="form-text text-muted">Untuk hasil terbaik, gunakan gambar dengan resolusi minimal 1200px (lebar).</div>
                      <?php endif; ?>
                      
                      <?php if ($orgImgUrl): ?>
                        <div class="form-check mt-2">
                          <input class="form-check-input"
                                 type="checkbox"
                                 id="remove-org-structure"
                                 name="remove_org_structure"
                                 value="1"
                                 <?= ($removeOrgOld === '1' || $removeOrgOld === 'on') ? 'checked' : '' ?>>
                          <label class="form-check-label small text-muted" for="remove-org-structure">Hapus gambar saat ini</label>
                        </div>
                      <?php endif; ?>
                    </div>
                    
                    <div class="col-12 col-lg-8">
                      <label class="form-label">Deskripsi Alt Text (Opsional)</label>
                      <textarea name="org_structure_alt_text" rows="3" class="form-control" placeholder="Deskripsi singkat struktur organisasi untuk aksesibilitas"><?= esc($orgAltText) ?></textarea>
                      <?php if ($validation && $validation->hasError('org_structure_alt_text')): ?>
                        <div class="form-text text-danger"><?= esc($validation->getError('org_structure_alt_text')) ?></div>
                      <?php else: ?>
                        <div class="form-text text-muted">Alt text membantu aksesibilitas dan SEO. Contoh: "Struktur Organisasi Dinas Komunikasi dan Informatika"</div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-theme" role="tabpanel" aria-labelledby="tab-theme-tab">
              <div class="card border-0 shadow-sm">
                <div class="card-body">
                  <div class="d-flex flex-column gap-2 mb-4">
                    <h6 class="fw-semibold mb-0">Pengaturan Tema</h6>
                    <p class="text-muted small mb-0">Pilih salah satu preset atau gunakan warna khas OPD.</p>
                  </div>

                  <div class="theme-mode-toggle mb-4" data-theme-mode-toggle>
                    <?php foreach ($themeModeOptions as $modeValue => $modeLabel): ?>
                      <?php
                        $modeId    = 'theme-mode-' . $modeValue;
                        $disabled  = $modeValue === ProfileAdminService::THEME_MODE_PRESET && $themePresets === [];
                      ?>
                      <input
                        type="radio"
                        class="btn-check"
                        name="theme_mode"
                        id="<?= esc($modeId) ?>"
                        value="<?= esc($modeValue) ?>"
                        data-theme-mode-input
                        <?= $disabled ? 'disabled' : '' ?>
                        <?= $activeThemeMode === $modeValue ? 'checked' : '' ?>
                      >
                      <label class="btn btn-outline-primary<?= $disabled ? ' disabled' : '' ?>" for="<?= esc($modeId) ?>">
                        <?= esc($modeLabel) ?>
                      </label>
                    <?php endforeach; ?>
                  </div>
                  <?php if ($validation && $validation->hasError('theme_mode')): ?>
                    <div class="form-text text-danger mb-3"><?= esc($validation->getError('theme_mode')) ?></div>
                  <?php endif; ?>

                  <div data-theme-pane="<?= ProfileAdminService::THEME_MODE_PRESET ?>" <?= $activeThemeMode === ProfileAdminService::THEME_MODE_CUSTOM ? 'class="d-none"' : '' ?>>
                    <?php if ($themePresets === []): ?>
                      <div class="alert alert-warning mb-0">
                        <i class="bx bx-info-circle me-2" aria-hidden="true"></i>
                        Belum tersedia daftar preset warna. Tambahkan konfigurasi pada <code>Config/ProfileTheme.php</code> atau gunakan mode "Warna OPD".
                      </div>
                    <?php else: ?>
                      <?php
                        $toneGroups = [
                          'light' => ['label' => 'Tema Cerah', 'icon' => 'bx-sun'],
                          'dark'  => ['label' => 'Tema Gelap', 'icon' => 'bx-moon'],
                        ];
                        $presetsByTone = [];
                        foreach ($themePresets as $slug => $preset) {
                            $tone = strtolower($preset['tone'] ?? 'light');
                            $presetsByTone[$tone][$slug] = $preset;
                        }
                      ?>
                      <?php foreach ($toneGroups as $toneKey => $meta): ?>
                        <?php $groupPresets = $presetsByTone[$toneKey] ?? []; ?>
                        <?php if ($groupPresets === []): continue; endif; ?>
                        <div class="theme-preset-group mb-4">
                          <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bx <?= esc($meta['icon']) ?> text-primary"></i>
                            <h6 class="mb-0"><?= esc($meta['label']) ?></h6>
                          </div>
                          <div class="row g-3">
                            <?php foreach ($groupPresets as $slug => $preset): ?>
                              <?php
                                $primary = strtoupper($preset['primary']);
                                $surface = strtoupper($preset['surface']);
                                $isActive = $activeThemePreset === $slug;
                                $inputId = 'theme-preset-' . $slug;
                              ?>
                              <div class="col-12 col-md-6 col-xl-4">
                                <label class="card border-0 shadow-sm theme-preset-card <?= $isActive ? 'is-active' : '' ?>" for="<?= esc($inputId) ?>" data-theme-preset-card>
                                  <input
                                    type="radio"
                                    class="visually-hidden"
                                    id="<?= esc($inputId) ?>"
                                    name="theme_preset"
                                    value="<?= esc($slug) ?>"
                                    <?= $isActive ? 'checked' : '' ?>
                                  >
                                  <div class="card-body d-flex flex-column gap-3">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                      <span class="badge bg-label-primary"><?= esc($preset['label']) ?></span>
                                      <div class="theme-preset-swatches" aria-hidden="true">
                                        <span class="theme-preset-swatch" style="background-color: <?= esc($primary) ?>"></span>
                                        <span class="theme-preset-swatch" style="background-color: <?= esc($surface) ?>"></span>
                                      </div>
                                    </div>
                                    <div class="theme-preset-meta small text-muted">
                                      <div><strong>Primary:</strong> <?= esc($primary) ?></div>
                                      <div><strong>Surface:</strong> <?= esc($surface) ?></div>
                                    </div>
                                  </div>
                                  <span class="theme-preset-check" aria-hidden="true">
                                    <i class="bx bx-check"></i>
                                  </span>
                                </label>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                      <?php if ($validation && $validation->hasError('theme_preset')): ?>
                        <div class="form-text text-danger mt-3"><?= esc($validation->getError('theme_preset')) ?></div>
                      <?php else: ?>
                        <p class="text-muted small mt-3 mb-0">
                          <i class="bx bx-info-circle me-1" aria-hidden="true"></i>
                          Perubahan warna preset otomatis diterapkan ke situs publik dan panel admin.
                        </p>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>

                  <div data-theme-pane="<?= ProfileAdminService::THEME_MODE_CUSTOM ?>" <?= $activeThemeMode === ProfileAdminService::THEME_MODE_PRESET ? 'class="d-none"' : '' ?>>
                    <div class="row g-4 theme-custom-grid">
                      <?php
                        $customFields = [
                          'primary' => [
                            'label' => 'Warna Utama',
                            'name'  => 'theme_primary_color',
                            'helper'=> 'Dipakai untuk tombol, tautan, dan elemen sorotan.',
                          ],
                          'surface' => [
                            'label' => 'Warna Latar Permukaan',
                            'name'  => 'theme_surface_color',
                            'helper'=> 'Mengatur warna latar halaman dan kartu konten.',
                          ],
                        ];
                      ?>
                      <?php foreach ($customFields as $key => $config): ?>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="<?= esc($config['name']) ?>"><?= esc($config['label']) ?></label>
                          <input
                            type="color"
                            class="form-control form-control-color w-100"
                            id="<?= esc($config['name']) ?>"
                            name="<?= esc($config['name']) ?>"
                            value="<?= esc($customThemeValues[$key]) ?>"
                            title="<?= esc($config['label']) ?>"
                          >
                          <?php if ($validation && $validation->hasError($config['name'])): ?>
                            <div class="form-text text-danger"><?= esc($validation->getError($config['name'])) ?></div>
                          <?php else: ?>
                            <div class="form-text text-muted"><?= esc($config['helper']) ?></div>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>

                </div>
              </div>
            </div>

              </div>
            </div><!-- /.col-md-9 -->
          </div><!-- /.row -->

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
  <script src="<?= base_url('assets/vendor/tinymce/js/tinymce/tinymce.min.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/cropperjs/cropper.min.js') ?>"></script>
  <script src="<?= base_url('assets/js/admin/profile-logos.js') ?>" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var modeInputs = document.querySelectorAll('[data-theme-mode-input]');
      var panes = document.querySelectorAll('[data-theme-pane]');

      function togglePane(active) {
        panes.forEach(function (pane) {
          var paneMode = pane.getAttribute('data-theme-pane');
          if (! paneMode) {
            return;
          }

          if (paneMode === active) {
            pane.classList.remove('d-none');
          } else {
            pane.classList.add('d-none');
          }
        });
      }

      modeInputs.forEach(function (input) {
        input.addEventListener('change', function () {
          if (this.checked) {
            togglePane(this.value);
          }
        });
      });

      var active = document.querySelector('[data-theme-mode-input]:checked');
      if (active) {
        togglePane(active.value);
      }

      var presetInputs = document.querySelectorAll('input[name="theme_preset"]');
      presetInputs.forEach(function (input) {
        input.addEventListener('change', function () {
          document.querySelectorAll('[data-theme-preset-card]').forEach(function (card) {
            card.classList.remove('is-active');
          });

          var card = this.closest('[data-theme-preset-card]');
          if (card) {
            card.classList.add('is-active');
          }
        });
      });

      // Social Media: Disable toggle if URL is empty
      const socialMediaInputs = [
        { url: 'input[name="social_facebook"]', toggle: '#social_facebook_active' },
        { url: 'input[name="social_instagram"]', toggle: '#social_instagram_active' },
        { url: 'input[name="social_twitter"]', toggle: '#social_twitter_active' },
        { url: 'input[name="social_youtube"]', toggle: '#social_youtube_active' },
        { url: 'input[name="social_tiktok"]', toggle: '#social_tiktok_active' }
      ];

      socialMediaInputs.forEach(function(item) {
        const urlInput = document.querySelector(item.url);
        const toggleInput = document.querySelector(item.toggle);
        
        if (urlInput && toggleInput) {
          // Check on page load
          function checkAndToggle() {
            if (urlInput.value.trim() === '') {
              toggleInput.disabled = true;
              toggleInput.checked = false;
              toggleInput.closest('.form-check')?.classList.add('opacity-50');
            } else {
              toggleInput.disabled = false;
              toggleInput.closest('.form-check')?.classList.remove('opacity-50');
            }
          }
          
          checkAndToggle();
          
          // Check on input change
          urlInput.addEventListener('input', checkAndToggle);
        }
      });
    });

    // TinyMCE for Greeting/Sambutan
    (function() {
      const initGreetingEditor = function() {
        if (typeof tinymce === 'undefined') {
          console.warn('[Profile] TinyMCE not loaded');
          return;
        }

        tinymce.init({
          selector: '#greetingContent',
          branding: false,
          promotion: false,
          height: 480,
          menubar: 'file edit view insert format tools table help',
          toolbar_sticky: true,
          toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table image media link | removeformat | fullscreen preview code',
          plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount help quickbars emoticons',
          quickbars_selection_toolbar: 'bold italic underline | quicklink blockquote',
          quickbars_insert_toolbar: 'image media | hr',
          autosave_interval: '30s',
          autosave_restore_when_empty: true,
          autosave_retention: '2m',
          image_caption: true,
          content_style: 'body { font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif; font-size: 16px; line-height: 1.7; }',
          table_default_attributes: { class: 'table table-striped table-sm' },
          file_picker_types: 'image media',
          language: 'id',
          language_url: '<?= base_url('assets/vendor/tinymce/langs/id.js') ?>',
          setup: function(editor) {
            editor.on('init', function() {
              console.log('[Profile] Greeting TinyMCE initialized');
            });
          }
        });
      };

      // Initialize when DOM is ready
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGreetingEditor);
      } else {
        initGreetingEditor();
      }

      // Handle form submission
      const form = document.querySelector('form[action*="admin/profile"]');
      if (form) {
        form.addEventListener('submit', function() {
          const editor = tinymce.get('greetingContent');
          if (editor) {
            try {
              editor.save();
            } catch (err) {
              console.error('[Profile] Error saving greeting editor:', err);
            }
          }
        });
      }
    })();
  </script>
<?= $this->endSection() ?>
