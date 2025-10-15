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
<footer class="border-t border-base-200 bg-base-200/70 text-base-content/80" aria-labelledby="footer-heading">
  <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="grid gap-10 lg:grid-cols-[2.2fr,1.2fr,1.6fr]">
      <div class="space-y-4">
        <div>
          <h2 class="text-xl font-semibold text-base-content" id="footer-heading"><?= esc($footerName) ?></h2>
          <?php if ($footerDesc !== ''): ?>
            <p class="mt-2 max-w-xl text-sm leading-relaxed text-base-content/70"><?= esc($footerDesc) ?></p>
          <?php endif; ?>
        </div>
        <dl class="space-y-3 text-sm text-base-content/80">
          <?php if ($footerAddress !== ''): ?>
            <div>
              <dt class="text-xs font-semibold uppercase tracking-widest text-base-content/60">Alamat</dt>
              <dd class="mt-1 whitespace-pre-line"><?= esc($footerAddress) ?></dd>
            </div>
          <?php endif; ?>
          <?php if ($footerPhone !== ''): ?>
            <div>
              <dt class="text-xs font-semibold uppercase tracking-widest text-base-content/60">Telepon</dt>
              <dd class="mt-1">
                <a class="font-medium text-base-content hover:text-primary" href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $footerPhone)) ?>"><?= esc($footerPhone) ?></a>
              </dd>
            </div>
          <?php endif; ?>
          <?php if ($footerEmail !== ''): ?>
            <div>
              <dt class="text-xs font-semibold uppercase tracking-widest text-base-content/60">Email</dt>
              <dd class="mt-1">
                <a class="font-medium text-base-content hover:text-primary" href="mailto:<?= esc($footerEmail) ?>"><?= esc($footerEmail) ?></a>
              </dd>
            </div>
          <?php endif; ?>
        </dl>
      </div>
      <div class="grid gap-6 text-sm">
        <div>
          <h3 class="text-xs font-semibold uppercase tracking-[0.3em] text-base-content/60">Navigasi</h3>
          <ul class="mt-3 space-y-2">
            <li><a class="hover:text-primary" href="<?= site_url('profil') ?>">Profil</a></li>
            <li><a class="hover:text-primary" href="<?= site_url('layanan') ?>">Layanan</a></li>
            <li><a class="hover:text-primary" href="<?= site_url('berita') ?>">Berita</a></li>
            <li><a class="hover:text-primary" href="<?= site_url('dokumen') ?>">Dokumen</a></li>
            <li><a class="hover:text-primary" href="<?= site_url('kontak') ?>">Kontak</a></li>
          </ul>
        </div>
        <div>
          <h3 class="text-xs font-semibold uppercase tracking-[0.3em] text-base-content/60">Layanan cepat</h3>
          <ul class="mt-3 space-y-2">
            <li><a class="hover:text-primary" href="<?= site_url('kontak') ?>">Status Pengaduan</a></li>
            <li><a class="hover:text-primary" href="<?= site_url('layanan') ?>">Antrean &amp; Reservasi</a></li>
            <li><a class="hover:text-primary" href="<?= site_url('dokumen') ?>">Portal Dokumen</a></li>
            <li><a class="hover:text-primary" href="<?= site_url('kontak') ?>#form-kontak">Pengaduan WBS</a></li>
          </ul>
        </div>
      </div>
      <div class="space-y-4">
        <h3 class="text-xs font-semibold uppercase tracking-[0.3em] text-base-content/60">Lokasi kami</h3>
        <?php if ($shouldShowMap): ?>
          <div id="footer-map"
               class="h-64 w-full overflow-hidden rounded-2xl border border-base-300 bg-base-100 shadow-inner"
               data-lat="<?= esc((string) $latitude) ?>"
               data-lng="<?= esc((string) $longitude) ?>"
               data-zoom="<?= esc((string) $zoomLevel) ?>"
               data-title="<?= esc($footerName) ?>"
               data-address="<?= esc($footerAddress) ?>">
          </div>
        <?php else: ?>
          <div class="flex h-64 w-full items-center justify-center rounded-2xl border border-dashed border-base-300 bg-base-100 text-center text-sm text-base-content/60">
            <div class="space-y-1">
              <p class="font-semibold text-base-content">Peta belum diaktifkan</p>
              <p>Perbarui koordinat dan aktifkan tampilan peta melalui panel admin.</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="mt-10 border-t border-base-300 pt-6 text-center text-xs text-base-content/60">
      &copy; <?= date('Y') ?> <?= esc($footerName) ?>. Seluruh hak cipta dilindungi.
    </div>
  </div>
</footer>
