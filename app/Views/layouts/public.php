<?php
  helper(['url', 'theme']);

  $footerProfileData = $footerProfile ?? ($profile ?? null);
  $headerProfileData = is_array($profile ?? null) ? $profile : (is_array($footerProfileData) ? $footerProfileData : null);
  $themeProfileData  = is_array($headerProfileData) ? $headerProfileData : (is_array($footerProfileData) ? $footerProfileData : null);
  $publicThemeVariables = is_array($themeProfileData) ? theme_public_variables($themeProfileData) : [];

  $metaData = is_array($meta ?? null) ? $meta : [];

  $metaTitle       = trim((string) ($metaData['title'] ?? ''));
  $pageTitle       = $metaTitle !== '' ? $metaTitle : (string) ($title ?? 'Situs Resmi OPD');
  $metaDescription = trim((string) ($metaData['description'] ?? ''));
  $metaKeywords    = trim((string) ($metaData['keywords'] ?? ''));
  $metaAuthor      = trim((string) ($metaData['author'] ?? ''));
  $metaType        = trim((string) ($metaData['type'] ?? 'website'));
  $metaUrl         = trim((string) ($metaData['url'] ?? current_url()));
  $metaImage       = trim((string) ($metaData['image'] ?? ''));

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= esc($pageTitle) ?></title>
  <?php if ($metaDescription !== ''): ?>
    <meta name="description" content="<?= esc($metaDescription, 'attr') ?>" />
  <?php endif; ?>
  <?php if ($metaKeywords !== ''): ?>
    <meta name="keywords" content="<?= esc($metaKeywords, 'attr') ?>" />
  <?php endif; ?>
  <?php if ($metaAuthor !== ''): ?>
    <meta name="author" content="<?= esc($metaAuthor, 'attr') ?>" />
  <?php endif; ?>
  <meta property="og:title" content="<?= esc($pageTitle, 'attr') ?>" />
  <?php if ($metaDescription !== ''): ?>
    <meta property="og:description" content="<?= esc($metaDescription, 'attr') ?>" />
  <?php endif; ?>
  <meta property="og:type" content="<?= esc($metaType !== '' ? $metaType : 'website', 'attr') ?>" />
  <?php if ($metaUrl !== ''): ?>
    <meta property="og:url" content="<?= esc($metaUrl, 'attr') ?>" />
  <?php endif; ?>
  <?php if ($metaImage !== ''): ?>
    <meta property="og:image" content="<?= esc($metaImage, 'attr') ?>" />
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="<?= base_url('assets/css/public/tokens.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/css/public/layout.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/css/public/navbar-dropdown.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/css/public/components.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/css/public/pages.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/css/public/hide-icons.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/css/public/global-search.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/css/public/hero-slider.css') ?>" rel="stylesheet" />
  <?php if ($publicThemeVariables !== []): ?>
    <?= theme_render_style($publicThemeVariables) ?>
  <?php endif; ?>
  <?= $this->renderSection('pageStyles') ?>
</head>
<body class="d-flex flex-column min-vh-100 public-body">
  <a class="skip-link" href="#konten-utama">Lewati ke konten utama</a>
  <?= $this->include('layouts/public_nav', ['profile' => $headerProfileData]) ?>
  <main id="konten-utama" class="flex-grow-1" tabindex="-1">
    <?= $this->renderSection('content') ?>
  </main>
  
  <!-- Back to Top Button -->
  <button type="button" class="back-to-top" id="backToTop" aria-label="Kembali ke atas" title="Kembali ke atas">
    <i class="bx bx-chevron-up"></i>
  </button>
  
  <?= $this->include('layouts/public_footer', ['footerProfile' => $footerProfileData]) ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="<?= base_url('assets/vendor/js/headroom.min.js') ?>" defer></script>
  <script src="<?= base_url('assets/js/public-min.js') ?>" defer></script>
  <?= $this->renderSection('pageScripts') ?>
</body>
</html>
