<?= $this->extend('layouts/public') ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/public/contact.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/js/public-contact.js') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $profile = is_array($profile ?? null) ? $profile : [];
    $address = trim((string) ($profile['address'] ?? ''));
    $phone   = trim((string) ($profile['phone'] ?? ''));
    $email   = trim((string) ($profile['email'] ?? ''));
    $session = session();

    // Map data
    $latitudeRaw    = $profile['latitude'] ?? null;
    $longitudeRaw   = $profile['longitude'] ?? null;
    $latitude       = is_numeric($latitudeRaw) ? (float) $latitudeRaw : null;
    $longitude      = is_numeric($longitudeRaw) ? (float) $longitudeRaw : null;
    $zoomLevel      = $profile['map_zoom'] ?? 16;
    $mapDisplay     = (int) ($profile['map_display'] ?? 0) === 1;
    $hasCoordinates = $latitude !== null && $longitude !== null;
    $shouldShowMap  = $mapDisplay && $hasCoordinates;

    $successMessage = (string) ($session->getFlashdata('contact_success') ?? '');
    $errorMessage   = (string) ($session->getFlashdata('contact_error') ?? '');
    $contactErrors  = (array) ($session->getFlashdata('contact_errors') ?? []);
    $contactOld     = (array) ($session->getFlashdata('contact_old') ?? []);

    $formDefaults = [
        'full_name' => '',
        'email'     => '',
        'phone'     => '',
        'subject'   => '',
        'message'   => '',
    ];

    foreach ($formDefaults as $field => $defaultValue) {
        $candidate = $contactOld[$field] ?? old($field);
        $formDefaults[$field] = is_string($candidate) ? $candidate : $defaultValue;
    }
?>

<section class="public-section pt-3 pb-5">
  <div class="container">
    <!-- Breadcrumb -->
    <?= $this->include('public/components/_breadcrumb', ['current' => 'Kontak']) ?>

    <!-- Header -->
    <header class="text-center mb-5">
      <h1 class="fw-bold mb-3">Hubungi Kami</h1>
      <p class="text-muted lead mx-auto" style="max-width: 540px;">
        Sampaikan pertanyaan, saran, atau pengaduan Anda. Tim kami siap membantu.
      </p>
    </header>

    <div class="row g-4">
      <!-- Informasi Kontak -->
      <div class="col-lg-4">
        <div class="surface-card h-100 p-4">
          <h2 class="h5 mb-4">Informasi Kontak</h2>
          
          <div class="d-flex flex-column gap-4">
            <!-- Alamat -->
            <div class="d-flex gap-3">
              <div class="flex-shrink-0">
                <div class="contact-icon-wrap">
                  <i class="bx bx-map"></i>
                </div>
              </div>
              <div>
                <h6 class="mb-1">Alamat Kantor</h6>
                <p class="text-muted mb-0 small">
                  <?= $address !== '' ? nl2br(esc($address)) : 'Alamat belum tersedia' ?>
                </p>
              </div>
            </div>

            <!-- Telepon -->
            <div class="d-flex gap-3">
              <div class="flex-shrink-0">
                <div class="contact-icon-wrap">
                  <i class="bx bx-phone"></i>
                </div>
              </div>
              <div>
                <h6 class="mb-1">Telepon</h6>
                <?php if ($phone !== ''): ?>
                  <a href="tel:<?= preg_replace('/[^0-9+]/', '', $phone) ?>" class="text-decoration-none">
                    <?= esc($phone) ?>
                  </a>
                <?php else: ?>
                  <p class="text-muted mb-0 small">Nomor telepon belum tersedia</p>
                <?php endif; ?>
              </div>
            </div>

            <!-- Email -->
            <div class="d-flex gap-3">
              <div class="flex-shrink-0">
                <div class="contact-icon-wrap">
                  <i class="bx bx-envelope"></i>
                </div>
              </div>
              <div>
                <h6 class="mb-1">Email</h6>
                <?php if ($email !== ''): ?>
                  <a href="mailto:<?= esc($email) ?>" class="text-decoration-none">
                    <?= esc($email) ?>
                  </a>
                <?php else: ?>
                  <p class="text-muted mb-0 small">Email belum tersedia</p>
                <?php endif; ?>
              </div>
            </div>

            <!-- Jam Pelayanan -->
            <div class="d-flex gap-3">
              <div class="flex-shrink-0">
                <div class="contact-icon-wrap">
                  <i class="bx bx-time"></i>
                </div>
              </div>
              <div>
                <h6 class="mb-1">Jam Pelayanan</h6>
                <p class="text-muted mb-0 small">
                  Senin - Kamis: 08.00 - 16.00 WIB<br>
                  Jumat: 08.00 - 15.00 WIB
                </p>
              </div>
            </div>
          </div>

          <!-- Peta Lokasi -->
          <?php if ($shouldShowMap): ?>
            <?php
              $coordinateString = trim((string) $latitude) . ',' . trim((string) $longitude);
              $mapZoomValue     = is_numeric($zoomLevel) ? (int) $zoomLevel : 16;
              if ($mapZoomValue < 1 || $mapZoomValue > 20) { $mapZoomValue = 16; }
              $mapEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($coordinateString) . '&z=' . rawurlencode((string) $mapZoomValue) . '&output=embed';
              $mapExternalUrl = 'https://www.google.com/maps?q=' . rawurlencode($coordinateString);
            ?>
            <div class="mt-4 pt-3 border-top">
              <h6 class="mb-3">Lokasi Kantor</h6>
              <div class="contact-map-frame ratio ratio-4x3">
                <iframe
                  src="<?= esc($mapEmbedUrl) ?>"
                  title="Lokasi Kantor"
                  loading="lazy"
                  allowfullscreen
                  referrerpolicy="no-referrer-when-downgrade">
                </iframe>
              </div>
              <a class="btn btn-sm btn-outline-primary mt-2 w-100" href="<?= esc($mapExternalUrl) ?>" target="_blank" rel="noopener">
                <i class="bx bx-map me-1"></i>Buka di Google Maps
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Form Kontak -->
      <div class="col-lg-8">
        <div class="surface-card p-4">
          <h2 class="h5 mb-4">Kirim Pesan</h2>

          <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success" role="status">
              <i class="bx bx-check-circle me-2"></i><?= esc($successMessage) ?>
            </div>
          <?php endif; ?>

          <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger" role="alert">
              <i class="bx bx-error-circle me-2"></i><?= esc($errorMessage) ?>
            </div>
          <?php endif; ?>

          <form method="post" action="<?= site_url('kontak') ?>" novalidate data-contact-form>
            <?= csrf_field() ?>
            
            <div class="row g-3">
              <!-- Nama -->
              <div class="col-md-6">
                <?php $fieldError = $contactErrors['full_name'] ?? ''; ?>
                <label class="form-label" for="contactFullName">Nama Lengkap <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control<?= $fieldError !== '' ? ' is-invalid' : '' ?>"
                  id="contactFullName"
                  name="full_name"
                  value="<?= esc($formDefaults['full_name']) ?>"
                  maxlength="150"
                  autocomplete="name"
                  required
                >
                <?php if ($fieldError !== ''): ?>
                  <div class="invalid-feedback"><?= esc($fieldError) ?></div>
                <?php endif; ?>
              </div>

              <!-- Email -->
              <div class="col-md-6">
                <?php $fieldError = $contactErrors['email'] ?? ''; ?>
                <label class="form-label" for="contactEmail">Email <span class="text-danger">*</span></label>
                <input
                  type="email"
                  class="form-control<?= $fieldError !== '' ? ' is-invalid' : '' ?>"
                  id="contactEmail"
                  name="email"
                  value="<?= esc($formDefaults['email']) ?>"
                  maxlength="150"
                  autocomplete="email"
                  required
                >
                <?php if ($fieldError !== ''): ?>
                  <div class="invalid-feedback"><?= esc($fieldError) ?></div>
                <?php endif; ?>
              </div>

              <!-- Telepon -->
              <div class="col-md-6">
                <?php $fieldError = $contactErrors['phone'] ?? ''; ?>
                <label class="form-label" for="contactPhone">Nomor Telepon</label>
                <input
                  type="tel"
                  class="form-control<?= $fieldError !== '' ? ' is-invalid' : '' ?>"
                  id="contactPhone"
                  name="phone"
                  value="<?= esc($formDefaults['phone']) ?>"
                  maxlength="30"
                  autocomplete="tel"
                >
                <?php if ($fieldError !== ''): ?>
                  <div class="invalid-feedback"><?= esc($fieldError) ?></div>
                <?php endif; ?>
              </div>

              <!-- Subjek -->
              <div class="col-md-6">
                <?php $fieldError = $contactErrors['subject'] ?? ''; ?>
                <label class="form-label" for="contactSubject">Subjek <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control<?= $fieldError !== '' ? ' is-invalid' : '' ?>"
                  id="contactSubject"
                  name="subject"
                  value="<?= esc($formDefaults['subject']) ?>"
                  maxlength="120"
                  required
                >
                <?php if ($fieldError !== ''): ?>
                  <div class="invalid-feedback"><?= esc($fieldError) ?></div>
                <?php endif; ?>
              </div>

              <!-- Pesan -->
              <div class="col-12">
                <?php $fieldError = $contactErrors['message'] ?? ''; ?>
                <label class="form-label" for="contactMessage">Pesan <span class="text-danger">*</span></label>
                <textarea
                  class="form-control<?= $fieldError !== '' ? ' is-invalid' : '' ?>"
                  id="contactMessage"
                  name="message"
                  rows="5"
                  maxlength="2000"
                  required
                ><?= esc($formDefaults['message']) ?></textarea>
                <?php if ($fieldError !== ''): ?>
                  <div class="invalid-feedback"><?= esc($fieldError) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Honeypot -->
            <div style="position: absolute; left: -9999px;" aria-hidden="true">
              <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <!-- Captcha -->
            <?php 
              helper('captcha');
              $captcha = captcha_generate();
              $captchaError = $contactErrors['captcha_answer'] ?? '';
            ?>
            <div class="row mt-3">
              <div class="col-md-6">
                <label class="form-label" for="contactCaptcha">
                  Verifikasi: <strong><?= esc($captcha['question']) ?> = ?</strong> <span class="text-danger">*</span>
                </label>
                <input
                  type="text"
                  class="form-control<?= $captchaError !== '' ? ' is-invalid' : '' ?>"
                  id="contactCaptcha"
                  name="captcha_answer"
                  placeholder="Masukkan jawaban"
                  maxlength="5"
                  inputmode="numeric"
                  pattern="[0-9]*"
                  required
                >
                <?php if ($captchaError !== ''): ?>
                  <div class="invalid-feedback"><?= esc($captchaError) ?></div>
                <?php else: ?>
                  <div class="form-text">Jawab pertanyaan matematika di atas untuk membuktikan Anda bukan robot.</div>
                <?php endif; ?>
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-public-primary btn-lg">
                <i class="bx bx-send me-2"></i>Kirim Pesan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
