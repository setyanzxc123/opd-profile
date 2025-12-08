<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php helper('image'); ?>
<section class="public-section pt-3 pb-5">
  <div class="container">
    <!-- Breadcrumb -->
    <?= $this->include('public/components/_breadcrumb', ['current' => $service['title'] ?? 'Layanan']) ?>

    <article class="service-detail">
      <div class="row justify-content-center">
        <!-- Main Content -->
        <div class="col-lg-10 col-xl-8">
          <!-- Header -->
          <header class="service-detail__header mb-4">
            <?php if (! empty($service['thumbnail'])): ?>
              <?php $srcset = responsive_srcset($service['thumbnail'], [800, 1200], '100vw'); ?>
              <figure class="service-detail__thumbnail mb-4">
                <img 
                  src="<?= esc(base_url($service['thumbnail']), 'attr') ?>" 
                  srcset="<?= esc($srcset['srcset']) ?>"
                  sizes="<?= esc($srcset['sizes']) ?>"
                  alt="<?= esc($service['title']) ?>" 
                  class="img-fluid rounded-4 w-100"
                  loading="eager"
                  decoding="async">
              </figure>
            <?php endif; ?>

            <h1 class="service-detail__title fw-bold mb-3"><?= esc($service['title']) ?></h1>

            <?php if (! empty($service['description'])): ?>
              <p class="service-detail__lead lead text-muted"><?= esc($service['description']) ?></p>
            <?php endif; ?>
          </header>

          <!-- Content -->
          <?php if (! empty($service['content'])): ?>
            <div class="service-detail__content prose">
              <?= $service['content'] ?>
            </div>
          <?php endif; ?>

          <!-- Back Button -->
          <div class="service-detail__actions mt-5 pt-4 border-top">
            <a href="<?= site_url('layanan') ?>" class="btn btn-outline-primary">
              <i class="bx bx-arrow-back me-2"></i>
              Kembali ke Daftar Layanan
            </a>
          </div>
        </div>
      </div>
    </article>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<style>
  .service-detail__thumbnail img {
    aspect-ratio: 16 / 9;
    object-fit: cover;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  }

  .service-detail__title {
    font-size: clamp(1.5rem, 4vw, 2.25rem);
    line-height: 1.3;
  }

  .service-detail__lead {
    font-size: 1.1rem;
    line-height: 1.7;
  }

  .service-detail__content {
    font-size: 1rem;
    line-height: 1.8;
  }

  .service-detail__content h2,
  .service-detail__content h3,
  .service-detail__content h4 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    font-weight: 600;
  }

  .service-detail__content h2 { font-size: 1.5rem; }
  .service-detail__content h3 { font-size: 1.25rem; }
  .service-detail__content h4 { font-size: 1.125rem; }

  .service-detail__content p {
    margin-bottom: 1rem;
  }

  .service-detail__content ul,
  .service-detail__content ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
  }

  .service-detail__content li {
    margin-bottom: 0.5rem;
  }

  .service-detail__content table {
    width: 100%;
    margin-bottom: 1rem;
    border-collapse: collapse;
  }

  .service-detail__content th,
  .service-detail__content td {
    padding: 0.75rem;
    border: 1px solid var(--color-border, #e5e7eb);
    text-align: left;
  }

  .service-detail__content th {
    background: var(--color-surface-elevated, #f8fafc);
    font-weight: 600;
  }

  .service-detail__content blockquote {
    margin: 1.5rem 0;
    padding: 1rem 1.5rem;
    border-left: 4px solid var(--color-primary, #3b82f6);
    background: var(--color-surface-elevated, #f8fafc);
    border-radius: 0 0.5rem 0.5rem 0;
  }

  .service-detail__content blockquote p:last-child {
    margin-bottom: 0;
  }

  .service-detail__content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
  }

  .service-detail__content a {
    color: var(--color-primary, #3b82f6);
    text-decoration: underline;
  }

  .service-detail__content a:hover {
    text-decoration: none;
  }
</style>
<?= $this->endSection() ?>
