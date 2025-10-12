<?php
  $footerProfileData = $footerProfile ?? ($profile ?? null);
  $headerProfileData = is_array($profile ?? null) ? $profile : (is_array($footerProfileData) ? $footerProfileData : null);
  $footerMapEnabled  = false;

  if (is_array($footerProfileData)) {
      $lat = $footerProfileData['latitude'] ?? null;
      $lng = $footerProfileData['longitude'] ?? null;
      $display = (int) ($footerProfileData['map_display'] ?? 0) === 1;
      if ($display && $lat !== null && $lat !== '' && $lng !== null && $lng !== '') {
          $footerMapEnabled = true;
      }
  }

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= esc($title ?? 'Situs Resmi OPD') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="<?= base_url('assets/css/public/tokens.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/css/public/layout.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/css/public/components.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/css/public/pages.css') ?>" rel="stylesheet" />
  <?php if ($footerMapEnabled): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <?php endif; ?>
  <?= $this->renderSection('pageStyles') ?>
</head>
<body class="d-flex flex-column min-vh-100 public-body">
  <a class="skip-link" href="#konten-utama">Lewati ke konten utama</a>
  <?= $this->include('layouts/public_nav', ['profile' => $headerProfileData]) ?>
  <main id="konten-utama" class="flex-grow-1" tabindex="-1">
    <?= $this->renderSection('content') ?>
  </main>
  <?= $this->include('layouts/public_footer', ['footerProfile' => $footerProfileData]) ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="<?= base_url('assets/js/public.js') ?>" defer></script>
  <?php if ($footerMapEnabled): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    <script src="<?= base_url('assets/js/footer-map.js') ?>" defer></script>
  <?php endif; ?>
  <?= $this->renderSection('pageScripts') ?>
</body>
</html>
