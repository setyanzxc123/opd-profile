<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow-sm public-navbar">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= base_url('/') ?>">
      <span class="me-2 rounded-circle bg-white bg-opacity-25 d-inline-flex align-items-center justify-content-center brand-circle">OPD</span>
      <span>Dinas Pelayanan Publik</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar" aria-controls="publicNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="publicNavbar">
      <ul class="navbar-nav ms-auto ms-lg-0 me-lg-4 mb-2 mb-lg-0 align-items-lg-center gap-lg-3">
        <li class="nav-item"><a class="nav-link" href="<?= site_url('/') ?>#beranda">Beranda</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= site_url('profil') ?>">Profil</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= site_url('layanan') ?>">Layanan</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= site_url('berita') ?>">Berita</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= site_url('galeri') ?>">Galeri</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= site_url('dokumen') ?>">Dokumen</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= site_url('/') ?>#kontak">Kontak</a></li>
      </ul>
      <div class="nav-actions d-flex flex-column flex-lg-row align-items-lg-center gap-3 mt-3 mt-lg-0">
        <form class="public-search w-100" action="<?= site_url('berita') ?>" method="get" role="search">
          <div class="input-group">
            <input class="form-control" type="search" name="q" placeholder="Cari informasi" aria-label="Cari informasi" />
            <button class="btn btn-public-primary" type="submit">Cari</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</nav>
