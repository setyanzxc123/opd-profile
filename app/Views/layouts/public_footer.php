<?php
  $profileSource  = $footerProfile ?? ($profile ?? null);
  $profile        = is_array($profileSource) ? $profileSource : [];
  $footerName     = trim((string) ($profile['name'] ?? 'Dinas ......'));
  $footerDesc     = trim((string) ($profile['description'] ?? 'Mewujudkan pelayanan publik yang cepat, transparan, dan berorientasi pada kepuasan masyarakat.'));
  $footerAddress  = trim((string) ($profile['address'] ?? ''));
  $footerPhone    = trim((string) ($profile['phone'] ?? ''));
  $footerEmail    = trim((string) ($profile['email'] ?? ''));
  $latitudeRaw    = $profile['latitude'] ?? null;
  $longitudeRaw   = $profile['longitude'] ?? null;
  $latitude       = is_numeric($latitudeRaw) ? (float) $latitudeRaw : null;
  $longitude      = is_numeric($longitudeRaw) ? (float) $longitudeRaw : null;
  $zoomLevel      = $profile['map_zoom'] ?? null;
  $mapDisplay     = (int) ($profile['map_display'] ?? 0) === 1;
  $hasCoordinates = $latitude !== null && $longitude !== null;
  $shouldShowMap  = $mapDisplay && $hasCoordinates;

  if (! $zoomLevel) {
      $zoomLevel = 16;
  }
?>
<footer class="public-footer mt-5 pt-5 pb-4" aria-labelledby="footer-heading">
  <div class="container">
    <div class="row g-4 g-lg-5 align-items-start">
      <!-- About Section -->
      <div class="col-12 col-md-6 col-lg-4">
        <h2 class="footer-heading fw-bold mb-3 text-white" id="footer-heading"><?= esc($footerName) ?></h2>
        <?php if ($footerDesc !== ''): ?>
          <p class="footer-description text-white-50 mb-4 lh-base"><?= esc($footerDesc) ?></p>
        <?php endif; ?>

        <dl class="footer-contact-list text-white-50 mb-0">
          <?php if ($footerAddress !== ''): ?>
            <div class="mb-3">
              <dt class="footer-contact-label text-uppercase fw-semibold small mb-1">
                <i class="bx bx-map me-1"></i>Alamat
              </dt>
              <dd class="mb-0 ps-4"><?= nl2br(esc($footerAddress)) ?></dd>
            </div>
          <?php endif; ?>
          <?php if ($footerPhone !== ''): ?>
            <div class="mb-3">
              <dt class="footer-contact-label text-uppercase fw-semibold small mb-1">
                <i class="bx bx-phone me-1"></i>Telepon
              </dt>
              <dd class="mb-0 ps-4">
                <a class="footer-link link-light text-decoration-none" href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $footerPhone)) ?>"><?= esc($footerPhone) ?></a>
              </dd>
            </div>
          <?php endif; ?>
          <?php if ($footerEmail !== ''): ?>
            <div class="mb-3">
              <dt class="footer-contact-label text-uppercase fw-semibold small mb-1">
                <i class="bx bx-envelope me-1"></i>Email
              </dt>
              <dd class="mb-0 ps-4">
                <a class="footer-link link-light text-decoration-none" href="mailto:<?= esc($footerEmail) ?>"><?= esc($footerEmail) ?></a>
              </dd>
            </div>
          <?php endif; ?>
        </dl>
      </div>

      <!-- Navigation Links -->
      <div class="col-6 col-md-4 col-lg-4 text-center">
        <h2 class="footer-heading text-white mb-3 h6 fw-bold text-uppercase">Navigasi</h2>
        <ul class="list-unstyled footer-links mb-0 d-inline-block text-start">
          <li><a href="<?= site_url('/') ?>">Beranda</a></li>
          <li><a href="<?= site_url('profil') ?>">Profil</a></li>
          <li><a href="<?= site_url('layanan') ?>">Layanan</a></li>
          <li><a href="<?= site_url('berita') ?>">Berita</a></li>
          <li><a href="<?= site_url('galeri') ?>">Galeri</a></li>
          <li><a href="<?= site_url('dokumen') ?>">Dokumen</a></li>
          <li><a href="<?= site_url('kontak') ?>">Kontak</a></li>
        </ul>
      </div>

      <!-- Map Section -->
      <div class="col-6 col-md-4 col-lg-4">
        <h2 class="footer-heading text-white mb-3 h6 fw-bold text-uppercase">Lokasi Kami</h2>
        <?php if ($shouldShowMap): ?>
          <?php
            $coordinateString = trim((string) $latitude) . ',' . trim((string) $longitude);
            $mapZoomValue     = is_numeric($zoomLevel) ? (int) $zoomLevel : 16;
            if ($mapZoomValue < 1 || $mapZoomValue > 20) {
                $mapZoomValue = 16;
            }
            $mapEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($coordinateString) . '&z=' . rawurlencode((string) $mapZoomValue) . '&output=embed';
            $mapExternalUrl = 'https://www.google.com/maps?q=' . rawurlencode($coordinateString);
          ?>
          <div class="footer-map ratio ratio-4x3 rounded-3 overflow-hidden shadow-sm">
            <iframe
              src="<?= esc($mapEmbedUrl) ?>"
              title="Lokasi <?= esc($footerName) ?>"
              loading="lazy"
              allowfullscreen
              referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>
          <p class="mt-2 mb-0">
            <a class="footer-map-link link-light text-decoration-none small d-inline-flex align-items-center" href="<?= esc($mapExternalUrl) ?>" target="_blank" rel="noopener">
              <i class="bx bx-link-external me-1"></i>Buka di Google Maps
            </a>
          </p>
        <?php else: ?>
          <div class="footer-map-placeholder text-white-50 small rounded-3 p-4 d-flex align-items-center justify-content-center text-center">
            <div>
              <i class="bx bx-map-pin bx-lg mb-2 opacity-50"></i>
              <p class="mb-1 fw-semibold text-white">Peta belum diaktifkan</p>
              <p class="mb-0 small">Perbarui koordinat dan aktifkan tampilan peta melalui panel admin.</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom border-top border-light border-opacity-25 mt-5 pt-4">
      <div class="row g-3 align-items-center">
        <div class="col-12 col-md-6 text-center text-md-start">
          <p class="mb-0 text-white-50 small">
            &copy; <?= date('Y') ?> <span class="text-white"><?= esc($footerName) ?></span>. Seluruh hak cipta dilindungi.
          </p>
        </div>
        <div class="col-12 col-md-6 text-center text-md-end">
          <p class="mb-0 text-white-50 small">
            Dibuat dengan <span class="text-danger">❤</span> untuk melayani masyarakat
          </p>
        </div>
      </div>
    </div>
  </div>
</footer>
