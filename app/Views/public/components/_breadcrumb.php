<?php
/**
 * Public Breadcrumb Component
 * 
 * Usage:
 *   <?= $this->include('public/components/_breadcrumb', ['current' => 'Layanan']) ?>
 *   <?= $this->include('public/components/_breadcrumb', ['current' => 'Detail', 'parent' => ['label' => 'Berita', 'url' => site_url('berita')]]) ?>
 * 
 * @var string $current Current page name
 * @var array|null $parent Optional parent breadcrumb with 'label' and 'url' keys
 */
$currentPage = $current ?? 'Halaman';
$parentCrumb = $parent ?? null;
?>
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb mb-0 small">
    <li class="breadcrumb-item">
      <a href="<?= site_url('/') ?>" class="text-decoration-none">
        <i class="bx bx-home-alt me-1"></i>Beranda
      </a>
    </li>
    <?php if ($parentCrumb && isset($parentCrumb['label'], $parentCrumb['url'])): ?>
      <li class="breadcrumb-item">
        <a href="<?= esc($parentCrumb['url'], 'attr') ?>" class="text-decoration-none">
          <?= esc($parentCrumb['label']) ?>
        </a>
      </li>
    <?php endif; ?>
    <li class="breadcrumb-item active" aria-current="page"><?= esc($currentPage) ?></li>
  </ol>
</nav>
