<?php helper('content'); ?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="public-section pt-3 pb-5">
  <div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item">
          <a href="<?= site_url('/') ?>" class="text-decoration-none">
            <i class="bx bx-home-alt me-1"></i>Beranda
          </a>
        </li>
        <li class="breadcrumb-item"><a href="<?= site_url('profil') ?>" class="text-decoration-none">Profil</a></li>
        <li class="breadcrumb-item active" aria-current="page">Sambutan</li>
      </ol>
    </nav>

    <!-- Header -->
    <header class="text-center mb-5">
      <h1 class="fw-bold mb-3">Kata Sambutan</h1>
    </header>

    <div class="row justify-content-center">
      <div class="col-lg-8">
        
        <article class="surface-card profile-card">
          <div class="prose rich-content">
            <?php
              $greetingContent = $profile['greeting'] ?? '';
              if (!empty($greetingContent)) {
                // Sanitize and output rich HTML content from TinyMCE
                echo sanitize_rich_text($greetingContent);
              } else {
                echo '<p class="text-muted">Sambutan belum tersedia.</p>';
              }
            ?>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
