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
    <div class="row g-4 align-items-start">
      <div class="col-12 col-lg-4">
        <h2 class="fw-semibold mb-3 text-white" id="footer-heading"><?= esc($footerName) ?></h2>
        <?php if ($footerDesc !== ''): ?>
          <p class="text-white-50 mb-3"><?= esc($footerDesc) ?></p>
        <?php endif; ?>

        <dl class="text-white-50 small mb-0 footer-contact-list">
          <?php if ($footerAddress !== ''): ?>
            <div class="mb-2">
              <dt class="text-uppercase text-white-50 fw-semibold small">Alamat</dt>
              <dd class="mb-0"><?= nl2br(esc($footerAddress)) ?></dd>
            </div>
          <?php endif; ?>
          <?php if ($footerPhone !== ''): ?>
            <div class="mb-2">
              <dt class="text-uppercase text-white-50 fw-semibold small">Telepon</dt>
              <dd class="mb-0"><a class="link-light text-decoration-none" href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $footerPhone)) ?>"><?= esc($footerPhone) ?></a></dd>
            </div>
          <?php endif; ?>
          <?php if ($footerEmail !== ''): ?>
            <div>
              <dt class="text-uppercase text-white-50 fw-semibold small">Email</dt>
              <dd class="mb-0"><a class="link-light text-decoration-none" href="mailto:<?= esc($footerEmail) ?>"><?= esc($footerEmail) ?></a></dd>
            </div>
          <?php endif; ?>
        </dl>
      </div>
      <div class="col-6 col-lg-3">
        <div class="mb-4">
          <h3 class="text-white mb-3 h6 text-uppercase">Navigasi</h3>
          <ul class="list-unstyled footer-links">
            <li><a href="<?= site_url('profil') ?>">Profil</a></li>
            <li><a href="<?= site_url('layanan') ?>">Layanan</a></li>
            <li><a href="<?= site_url('berita') ?>">Berita</a></li>
            <li><a href="<?= site_url('dokumen') ?>">Dokumen</a></li>
            <li><a href="<?= site_url('kontak') ?>">Kontak</a></li>
          </ul>
        </div>
        <div>
          <h3 class="text-white mb-3 h6 text-uppercase">Layanan Cepat</h3>
          <ul class="list-unstyled footer-links">
            <li><a href="<?= site_url('kontak') ?>">Status Pengaduan</a></li>
            <li><a href="<?= site_url('layanan') ?>">Antrean &amp; Reservasi</a></li>
            <li><a href="<?= site_url('dokumen') ?>">Portal Dokumen</a></li>
            <li><a href="<?= site_url('kontak') ?>#form-kontak">Pengaduan WBS</a></li>
          </ul>
        </div>
      </div>
      <div class="col-12 col-lg-5">
        <h3 class="text-white mb-3 h6 text-uppercase">Lokasi Kami</h3>
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
          <div class="footer-map ratio ratio-4x3 rounded-3 overflow-hidden border border-light border-opacity-25">
            <iframe
              src="<?= esc($mapEmbedUrl) ?>"
              title="Lokasi <?= esc($footerName) ?>"
              loading="lazy"
              allowfullscreen
              referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>
          <p class="mt-2 mb-0">
            <a class="link-light text-decoration-none small" href="<?= esc($mapExternalUrl) ?>" target="_blank" rel="noopener">
              Buka di Google Maps
            </a>
          </p>
        <?php else: ?>
          <div class="footer-map-placeholder text-white-50 small border border-light border-opacity-25 rounded-3 p-4 h-100 d-flex align-items-center justify-content-center text-center">
            <div>
              <p class="mb-1 fw-semibold text-white">Peta belum diaktifkan</p>
              <p class="mb-0">Perbarui koordinat dan aktifkan tampilan peta melalui panel admin.</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="border-top border-light border-opacity-25 mt-4 pt-3 text-center text-white-50 small">
      &copy; <?= date('Y') ?> <?= esc($footerName) ?>. Seluruh hak cipta dilindungi.
    </div>
  </div>
</footer>
