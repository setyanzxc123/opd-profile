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
                <svg class="footer-icon me-1" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                </svg>
                Alamat
              </dt>
              <dd class="mb-0 ps-4"><?= nl2br(esc($footerAddress)) ?></dd>
            </div>
          <?php endif; ?>
          <?php if ($footerPhone !== ''): ?>
            <div class="mb-3">
              <dt class="footer-contact-label text-uppercase fw-semibold small mb-1">
                <svg class="footer-icon me-1" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.568 17.568 0 0 0 4.168 6.608 17.569 17.569 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.745 1.745 0 0 1-1.657-.459L5.482 8.062a1.745 1.745 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58L3.654 1.328z"/>
                </svg>
                Telepon
              </dt>
              <dd class="mb-0 ps-4">
                <a class="footer-link link-light text-decoration-none" href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $footerPhone)) ?>"><?= esc($footerPhone) ?></a>
              </dd>
            </div>
          <?php endif; ?>
          <?php if ($footerEmail !== ''): ?>
            <div class="mb-3">
              <dt class="footer-contact-label text-uppercase fw-semibold small mb-1">
                <svg class="footer-icon me-1" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                </svg>
                Email
              </dt>
              <dd class="mb-0 ps-4">
                <a class="footer-link link-light text-decoration-none" href="mailto:<?= esc($footerEmail) ?>"><?= esc($footerEmail) ?></a>
              </dd>
            </div>
          <?php endif; ?>
        </dl>
      </div>

      <!-- Navigation Links -->
      <div class="col-6 col-md-3 col-lg-2">
        <h3 class="footer-heading text-white mb-3 h6 fw-bold text-uppercase">Navigasi</h3>
        <ul class="list-unstyled footer-links mb-0">
          <li><a href="<?= site_url('profil') ?>">Profil</a></li>
          <li><a href="<?= site_url('layanan') ?>">Layanan</a></li>
          <li><a href="<?= site_url('berita') ?>">Berita</a></li>
          <li><a href="<?= site_url('dokumen') ?>">Dokumen</a></li>
          <li><a href="<?= site_url('kontak') ?>">Kontak</a></li>
        </ul>
      </div>

      <!-- Quick Services -->
      <div class="col-6 col-md-3 col-lg-2">
        <h3 class="footer-heading text-white mb-3 h6 fw-bold text-uppercase">Layanan Cepat</h3>
        <ul class="list-unstyled footer-links mb-0">
          <li><a href="<?= site_url('kontak') ?>">Status Pengaduan</a></li>
          <li><a href="<?= site_url('layanan') ?>">Antrean &amp; Reservasi</a></li>
          <li><a href="<?= site_url('dokumen') ?>">Portal Dokumen</a></li>
          <li><a href="<?= site_url('kontak') ?>#form-kontak">Pengaduan WBS</a></li>
        </ul>
      </div>

      <!-- Map Section -->
      <div class="col-12 col-lg-4">
        <h3 class="footer-heading text-white mb-3 h6 fw-bold text-uppercase">Lokasi Kami</h3>
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
          <div class="footer-map ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm">
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
              <svg class="me-1" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
              </svg>
              Buka di Google Maps
            </a>
          </p>
        <?php else: ?>
          <div class="footer-map-placeholder text-white-50 small rounded-3 p-4 d-flex align-items-center justify-content-center text-center">
            <div>
              <svg class="mb-2 opacity-50" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
              </svg>
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
            Dibuat dengan <svg class="text-danger" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z"/>
            </svg> untuk melayani masyarakat
          </p>
        </div>
      </div>
    </div>
  </div>
</footer>
