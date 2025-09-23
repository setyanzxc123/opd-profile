<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
          <div>
            <h4 class="fw-bold mb-1">Dashboard</h4>
            <p class="text-muted mb-0">Selamat datang kembali, <?= esc(session('username')) ?>.</p>
          </div>
          <a href="<?= site_url('admin/profile') ?>" class="btn btn-primary">
            <i class="bx bx-edit-alt me-1"></i> Kelola Profil OPD
          </a>
        </div>

        <div class="row g-3">
          <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 bg-light shadow-sm position-relative overflow-hidden">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <small class="text-uppercase text-muted fw-semibold">Berita</small>
                    <h4 class="fw-bold mb-0">-</h4>
                  </div>
                  <span class="badge bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                    <i class="bx bx-news fs-5"></i>
                  </span>
                </div>
                <p class="text-muted small mb-0">Periksa dan kelola konten berita terbaru.</p>
                <a class="stretched-link" href="<?= site_url('admin/news') ?>" aria-label="Kelola berita"></a>
              </div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 bg-light shadow-sm position-relative overflow-hidden">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <small class="text-uppercase text-muted fw-semibold">Galeri</small>
                    <h4 class="fw-bold mb-0">-</h4>
                  </div>
                  <span class="badge bg-success bg-opacity-10 text-success p-3 rounded-circle">
                    <i class="bx bx-image fs-5"></i>
                  </span>
                </div>
                <p class="text-muted small mb-0">Tambahkan dokumentasi kegiatan instansi.</p>
                <a class="stretched-link" href="<?= site_url('admin/galleries') ?>" aria-label="Kelola galeri"></a>
              </div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 bg-light shadow-sm position-relative overflow-hidden">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <small class="text-uppercase text-muted fw-semibold">Dokumen</small>
                    <h4 class="fw-bold mb-0">-</h4>
                  </div>
                  <span class="badge bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                    <i class="bx bx-file fs-5"></i>
                  </span>
                </div>
                <p class="text-muted small mb-0">Unggah dan distribusikan berkas resmi OPD.</p>
                <a class="stretched-link" href="<?= site_url('admin/documents') ?>" aria-label="Kelola dokumen"></a>
              </div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 bg-light shadow-sm position-relative overflow-hidden">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <small class="text-uppercase text-muted fw-semibold">Aktivitas</small>
                    <h4 class="fw-bold mb-0">-</h4>
                  </div>
                  <span class="badge bg-info bg-opacity-10 text-info p-3 rounded-circle">
                    <i class="bx bx-history fs-5"></i>
                  </span>
                </div>
                <p class="text-muted small mb-0">Pantau aktivitas pengguna dan audit trail.</p>
                <a class="stretched-link" href="<?= site_url('admin/logs') ?>" aria-label="Lihat log aktivitas"></a>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <div class="alert alert-info mb-0" role="alert">
            <i class="bx bx-bulb me-2"></i>
            <span>Tambahkan statistik nyata (jumlah berita, galeri, layanan, dsb.) ketika modul terkait sudah siap.</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
