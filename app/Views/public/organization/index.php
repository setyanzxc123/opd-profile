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
        <li class="breadcrumb-item active" aria-current="page">Struktur Organisasi</li>
      </ol>
    </nav>

    <!-- Header -->
    <header class="text-center mb-5">
      <h1 class="fw-bold mb-3">Struktur Organisasi</h1>
      <p class="text-muted lead mx-auto" style="max-width: 540px;"><?= esc($profile['name'] ?? 'OPD') ?></p>
    </header>

      <?php
        $orgImgPath = $profile['org_structure_image'] ?? null;
        $orgImgUrl = $orgImgPath ? base_url($orgImgPath) : null;
        $orgAltText = $profile['org_structure_alt_text'] ?? 'Diagram Struktur Organisasi';
        $orgUpdatedAt = $profile['org_structure_updated_at'] ?? null;
      ?>

      <?php if ($orgImgUrl): ?>
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <div class="text-center mb-3">
              <a href="<?= esc($orgImgUrl) ?>" class="d-inline-block" data-org-lightbox>
                <img src="<?= esc($orgImgUrl) ?>" 
                     alt="<?= esc($orgAltText) ?>" 
                     class="img-fluid rounded shadow-sm"
                     style="max-width: 100%; height: auto; cursor: zoom-in;">
              </a>
            </div>
            
            <?php if ($orgAltText): ?>
              <p class="text-muted small text-center mb-2"><?= esc($orgAltText) ?></p>
            <?php endif; ?>
            
            <?php if ($orgUpdatedAt): ?>
              <p class="text-muted small text-center mb-0">
                <i class="bx bx-time-five me-1"></i> Terakhir diperbarui: <?= esc($orgUpdatedAt) ?>
              </p>
            <?php endif; ?>
            
            <div class="text-center mt-3">
              <a href="<?= esc($orgImgUrl) ?>" download class="btn btn-outline-primary btn-sm">
                <i class="bx bx-download me-1"></i> Unduh Gambar
              </a>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-info shadow-sm">
          <i class="bx bx-info-circle me-2"></i>
          Struktur organisasi belum tersedia. Silakan hubungi admin untuk informasi lebih lanjut.
        </div>
      <?php endif; ?>
  </div>
</section>

<!-- Simple Lightbox for Image Zoom -->
<div id="orgLightbox" class="modal fade" tabindex="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-body p-0 position-relative">
        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Tutup" style="z-index: 10;"></button>
        <img src="" alt="Struktur Organisasi" class="img-fluid w-100" id="lightboxImg">
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const lightbox = document.getElementById('orgLightbox');
  const lightboxImg = document.getElementById('lightboxImg');
  const trigger = document.querySelector('[data-org-lightbox]');
  
  if (trigger && lightbox) {
    trigger.addEventListener('click', function(e) {
      e.preventDefault();
      lightboxImg.src = this.href;
      const modal = new bootstrap.Modal(lightbox);
      modal.show();
    });
  }
});
</script>
<?= $this->endSection() ?>
