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
<html lang="id" data-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= esc($title ?? 'Situs Resmi OPD') ?></title>
  <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>">
  <?php if ($footerMapEnabled): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <?php endif; ?>
  <?= $this->renderSection('pageStyles') ?>
</head>
<body class="min-h-screen bg-base-100 text-base-content antialiased flex flex-col">
  <a class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:rounded-full focus:bg-primary focus:px-4 focus:py-2 focus:text-primary-content focus:outline-none" href="#konten-utama">Lewati ke konten utama</a>
  <?= $this->include('layouts/public_nav', ['profile' => $headerProfileData]) ?>
  <main id="konten-utama" class="flex-1 outline-none focus-visible:ring focus-visible:ring-primary/40">
    <?= $this->renderSection('content') ?>
  </main>
  <?= $this->include('layouts/public_footer', ['footerProfile' => $footerProfileData]) ?>
  <script src="<?= base_url('assets/js/public.js') ?>" defer></script>
  <?php if ($footerMapEnabled): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    <script src="<?= base_url('assets/js/footer-map.js') ?>" defer></script>
  <?php endif; ?>
  <?= $this->renderSection('pageScripts') ?>
</body>
</html>
