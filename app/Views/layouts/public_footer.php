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
      <div class="col-12 col-lg-4 text-center text-lg-start">
        <h2 class="footer-heading fw-bold mb-3 text-white" id="footer-heading"><?= esc($footerName) ?></h2>
        <?php if ($footerDesc !== ''): ?>
          <p class="footer-description text-white-50 mb-4 lh-base mx-auto mx-lg-0" style="max-width: 500px;"><?= esc($footerDesc) ?></p>
        <?php endif; ?>

        <div class="footer-contact-wrapper text-white-50 d-inline-block d-lg-block text-start">
          <?php if ($footerAddress !== ''): ?>
            <div class="contact-item d-flex align-items-start mb-3 justify-content-center justify-content-lg-start">
              <div class="contact-label text-uppercase fw-semibold small me-3 d-none d-lg-flex align-items-center" style="width: 90px; flex-shrink: 0;">
                <i class="bx bx-map me-2"></i>Alamat
              </div>
              <div class="contact-icon d-lg-none me-2"><i class="bx bx-map"></i></div>
              <div class="contact-value text-white"><?= nl2br(esc($footerAddress)) ?></div>
            </div>
          <?php endif; ?>
          
          <?php if ($footerPhone !== ''): ?>
            <div class="contact-item d-flex align-items-start mb-3 justify-content-center justify-content-lg-start">
              <div class="contact-label text-uppercase fw-semibold small me-3 d-none d-lg-flex align-items-center" style="width: 90px; flex-shrink: 0;">
                <i class="bx bx-phone me-2"></i>Telepon
              </div>
              <div class="contact-icon d-lg-none me-2"><i class="bx bx-phone"></i></div>
              <div class="contact-value">
                <a class="text-white text-decoration-none" href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $footerPhone)) ?>"><?= esc($footerPhone) ?></a>
              </div>
            </div>
          <?php endif; ?>
          
          <?php if ($footerEmail !== ''): ?>
            <div class="contact-item d-flex align-items-start mb-3 justify-content-center justify-content-lg-start">
              <div class="contact-label text-uppercase fw-semibold small me-3 d-none d-lg-flex align-items-center" style="width: 90px; flex-shrink: 0;">
                <i class="bx bx-envelope me-2"></i>Email
              </div>
              <div class="contact-icon d-lg-none me-2"><i class="bx bx-envelope"></i></div>
              <div class="contact-value">
                <a class="text-white text-decoration-none" href="mailto:<?= esc($footerEmail) ?>"><?= esc($footerEmail) ?></a>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Social Media Section -->
      <div class="col-12 col-md-6 col-lg-4 text-center">
        <h2 class="footer-heading text-white mb-3 h6 fw-bold text-uppercase">Ikuti Kami</h2>
        
        <!-- Mobile: Horizontal Icons -->
        <div class="d-flex d-lg-none justify-content-center gap-3 flex-wrap">
          <?php $hasSocial = false; ?>
          <?php if (!empty($profile['social_facebook']) && ($profile['social_facebook_active'] ?? '1') == '1'): ?>
            <?php $hasSocial = true; ?>
            <a href="<?= esc($profile['social_facebook']) ?>" target="_blank" rel="noopener noreferrer" class="social-link-footer facebook" aria-label="Facebook">
              <i class="bx bxl-facebook-circle"></i>
            </a>
          <?php endif; ?>
          <?php if (!empty($profile['social_instagram']) && ($profile['social_instagram_active'] ?? '1') == '1'): ?>
            <?php $hasSocial = true; ?>
            <a href="<?= esc($profile['social_instagram']) ?>" target="_blank" rel="noopener noreferrer" class="social-link-footer instagram" aria-label="Instagram">
              <i class="bx bxl-instagram"></i>
            </a>
          <?php endif; ?>
          <?php if (!empty($profile['social_twitter']) && ($profile['social_twitter_active'] ?? '1') == '1'): ?>
            <?php $hasSocial = true; ?>
            <a href="<?= esc($profile['social_twitter']) ?>" target="_blank" rel="noopener noreferrer" class="social-link-footer twitter" aria-label="Twitter">
              <i class="bx bxl-twitter"></i>
            </a>
          <?php endif; ?>
          <?php if (!empty($profile['social_youtube']) && ($profile['social_youtube_active'] ?? '1') == '1'): ?>
            <?php $hasSocial = true; ?>
            <a href="<?= esc($profile['social_youtube']) ?>" target="_blank" rel="noopener noreferrer" class="social-link-footer youtube" aria-label="YouTube">
              <i class="bx bxl-youtube"></i>
            </a>
          <?php endif; ?>
          <?php if (!empty($profile['social_tiktok']) && ($profile['social_tiktok_active'] ?? '1') == '1'): ?>
            <?php $hasSocial = true; ?>
            <a href="<?= esc($profile['social_tiktok']) ?>" target="_blank" rel="noopener noreferrer" class="social-link-footer tiktok" aria-label="TikTok">
              <i class="bx bxl-tiktok"></i>
            </a>
          <?php endif; ?>
        </div>

        <!-- Desktop: Vertical List with Labels -->
        <div class="d-none d-lg-flex flex-column align-items-center gap-3">
          <?php if (!empty($profile['social_facebook']) && ($profile['social_facebook_active'] ?? '1') == '1'): ?>
            <a href="<?= esc($profile['social_facebook']) ?>" target="_blank" rel="noopener noreferrer" class="social-link-list facebook">
              <div class="icon-wrapper"><i class="bx bxl-facebook-circle"></i></div>
              <span>Facebook</span>
            </a>
          <?php endif; ?>
          <?php if (!empty($profile['social_instagram']) && ($profile['social_instagram_active'] ?? '1') == '1'): ?>
            <a href="<?= esc($profile['social_instagram']) ?>" target="_blank" rel="noopener noreferrer" class="social-link-list instagram">
              <div class="icon-wrapper"><i class="bx bxl-instagram"></i></div>
              <span>Instagram</span>
            </a>
          <?php endif; ?>
          <?php if (!empty($profile['social_twitter']) && ($profile['social_twitter_active'] ?? '1') == '1'): ?>
            <a href="<?= esc($profile['social_twitter']) ?>" target="_blank" rel="noopener noreferrer" class="social-link-list twitter">
              <div class="icon-wrapper"><i class="bx bxl-twitter"></i></div>
              <span>Twitter / X</span>
            </a>
          <?php endif; ?>
          <?php if (!empty($profile['social_youtube']) && ($profile['social_youtube_active'] ?? '1') == '1'): ?>
            <a href="<?= esc($profile['social_youtube']) ?>" target="_blank" rel="noopener noreferrer" class="social-link-list youtube">
              <div class="icon-wrapper"><i class="bx bxl-youtube"></i></div>
              <span>YouTube Channel</span>
            </a>
          <?php endif; ?>
          <?php if (!empty($profile['social_tiktok']) && ($profile['social_tiktok_active'] ?? '1') == '1'): ?>
            <a href="<?= esc($profile['social_tiktok']) ?>" target="_blank" rel="noopener noreferrer" class="social-link-list tiktok">
              <div class="icon-wrapper"><i class="bx bxl-tiktok"></i></div>
              <span>TikTok Resmi</span>
            </a>
          <?php endif; ?>
          
           <?php if (!$hasSocial): ?>
            <p class="text-white-50 small fst-italic">Media sosial belum tersedia.</p>
          <?php endif; ?>
        </div>

        <style>
          /* Mobile Icons */
          .social-link-footer {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 1.5rem;
            transition: all 0.3s ease;
          }
          .social-link-footer:hover { transform: translateY(-3px); color: #ffffff; }

          /* Desktop List */
          .social-link-list {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.2s ease;
            width: fit-content;
          }
          .social-link-list .icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            color: #fff;
            font-size: 1.25rem;
            transition: all 0.2s ease;
          }
          .social-link-list:hover { color: #ffffff; transform: translateX(5px); }
          
          /* Brand Colors on Hover */
          .social-link-footer.facebook:hover, .social-link-list.facebook:hover .icon-wrapper { background: #1877f2; }
          .social-link-footer.instagram:hover, .social-link-list.instagram:hover .icon-wrapper { background: #e4405f; }
          .social-link-footer.twitter:hover, .social-link-list.twitter:hover .icon-wrapper { background: #1da1f2; }
          .social-link-footer.youtube:hover, .social-link-list.youtube:hover .icon-wrapper { background: #ff0000; }
          .social-link-footer.tiktok:hover, .social-link-list.tiktok:hover .icon-wrapper { background: #000000; }
        </style>
      </div>

      <!-- Map Section -->
      <div class="col-12 col-md-6 col-lg-4 text-center text-lg-start">
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
          <div class="footer-map ratio ratio-16x9 ratio-lg-4x3 rounded-3 overflow-hidden shadow-sm mx-auto mx-lg-0" style="max-width: 400px;">
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
          <div class="footer-map-placeholder text-white-50 small rounded-3 p-4 d-flex align-items-center justify-content-center text-center mx-auto mx-lg-0" style="max-width: 400px; background: rgba(255,255,255,0.05);">
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
            &copy; <?= date('Y') ?> <span class="text-white"><?= esc($footerName) ?> <?= esc($profile['name_line2'] ?? '') ?></span>. Seluruh hak cipta dilindungi.
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
