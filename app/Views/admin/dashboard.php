<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-xxl-8 col-lg-7">
    <div class="card h-100">
      <div class="row g-0 align-items-center">
        <div class="col-sm-7">
          <div class="card-body">
            <h5 class="card-title text-primary mb-3">
              Selamat datang kembali, <?= esc(session('name') ?? session('username') ?? 'Admin') ?>!
            </h5>
            <p class="mb-4 text-body-secondary">
              Pantau perkembangan layanan, konten publik, dan aktivitas tim dari satu tempat. Pastikan informasi OPD
              selalu mutakhir dan responsif.
            </p>
            <div class="d-flex flex-wrap gap-2 mb-3">
              <a href="<?= site_url('admin/profile') ?>" class="btn btn-sm btn-primary">
                <i class="bx bx-buildings me-1"></i>
                Kelola Profil OPD
              </a>
              <a href="<?= site_url('admin/news/create') ?>" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-plus me-1"></i>
                Tambah Berita
              </a>
            </div>
            <button
              type="button"
              class="btn btn-sm btn-outline-secondary"
              data-bs-toggle="toast"
              data-bs-target="#dashboardWelcomeToast">
              Lihat Tips Dashboard
            </button>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-start">
          <div class="card-body pb-0">
            <img
              src="<?= base_url('assets/img/illustrations/man-with-laptop.png') ?>"
              class="img-fluid"
              alt="Dashboard Illustration" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xxl-4 col-lg-5">
    <div class="row g-4">
      <div class="col-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between mb-3">
              <div class="avatar flex-shrink-0">
                <img
                  src="<?= base_url('assets/img/icons/unicons/chart-success.png') ?>"
                  class="rounded"
                  alt="Ikon Berita" />
              </div>
              <div class="dropdown">
                <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                  <i class="bx bx-dots-vertical-rounded text-body-secondary"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="<?= site_url('admin/news') ?>">Kelola Berita</a>
                  <a class="dropdown-item" href="<?= site_url('admin/news/create') ?>">Tambah Baru</a>
                </div>
              </div>
            </div>
            <p class="mb-1">Berita aktif</p>
            <h4 class="card-title mb-2">-</h4>
            <small class="text-success fw-medium">
              <i class="bx bx-up-arrow-alt me-1"></i>
              Prioritaskan isu terkini
            </small>
          </div>
        </div>
      </div>

      <div class="col-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between mb-3">
              <div class="avatar flex-shrink-0">
                <img
                  src="<?= base_url('assets/img/icons/unicons/wallet-info.png') ?>"
                  class="rounded"
                  alt="Ikon Dokumen" />
              </div>
              <div class="dropdown">
                <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                  <i class="bx bx-dots-vertical-rounded text-body-secondary"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="<?= site_url('admin/documents') ?>">Kelola Dokumen</a>
                  <a class="dropdown-item" href="<?= site_url('admin/documents/create') ?>">Unggah Dokumen</a>
                </div>
              </div>
            </div>
            <p class="mb-1">Dokumen publik</p>
            <h4 class="card-title mb-2">-</h4>
            <small class="text-body-secondary">
              <i class="bx bx-time me-1"></i>
              Pastikan SOP terbaru tersedia
            </small>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card h-100">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between mb-4">
              <div>
                <p class="mb-1">Aduan masyarakat</p>
                <h4 class="card-title mb-0">-</h4>
              </div>
              <span class="badge bg-label-info p-3 rounded-circle">
                <i class="bx bx-message-dots fs-5"></i>
              </span>
            </div>
            <small class="text-body-secondary d-block mb-3">
              Tindak lanjuti tiket masuk sebelum SLA berakhir.
            </small>
            <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('admin/logs') ?>">Lihat Aktivitas</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mt-1">
  <div class="col-12 col-xxl-8">
    <div class="card h-100">
      <div class="row row-bordered g-0">
        <div class="col-lg-8">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div class="card-title mb-0">
              <h5 class="m-0">Trend layanan publik</h5>
              <small class="text-body-secondary">Ringkasan aduan dan permohonan masyarakat</small>
            </div>
            <div class="dropdown">
              <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded text-body-secondary"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="javascript:void(0);">Refresh data</a>
                <a class="dropdown-item" href="javascript:void(0);">Unduh laporan</a>
              </div>
            </div>
          </div>
          <div id="totalRevenueChart" class="px-3"></div>
        </div>
        <div class="col-lg-4">
          <div class="card-body d-flex flex-column align-items-center justify-content-center py-lg-9">
            <div class="btn-group mb-4">
              <button type="button" class="btn btn-outline-primary"><?= date('Y') ?></button>
              <button
                type="button"
                class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split"
                data-bs-toggle="dropdown"></button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:void(0);"><?= date('Y') - 1 ?></a></li>
                <li><a class="dropdown-item" href="javascript:void(0);"><?= date('Y') - 2 ?></a></li>
              </ul>
            </div>
            <div id="growthChart"></div>
            <div class="mt-4 text-center">
              <h6 class="fw-semibold mb-0">Pertumbuhan respons</h6>
              <small class="text-body-secondary">Perbandingan SLA dan volume aduan</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-xxl-4">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0">Kinerja unit</h5>
          <small class="text-body-secondary">Distribusi progres per bidang</small>
        </div>
        <div class="dropdown">
          <button class="btn p-0" type="button" data-bs-toggle="dropdown">
            <i class="bx bx-dots-vertical-rounded text-body-secondary"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="javascript:void(0);">Detail harian</a>
            <a class="dropdown-item" href="javascript:void(0);">Kirim laporan</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-start mb-4">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-pie-chart-alt"></i></span>
          </div>
          <div class="d-flex justify-content-between w-100 flex-wrap align-items-center">
            <div>
              <small class="text-body-secondary">Progress</small>
              <h6 class="mb-0">Bidang layanan</h6>
            </div>
            <div class="text-success fw-medium">+12%</div>
          </div>
        </div>
        <div id="profileReportChart"></div>
        <ul class="list-unstyled mb-0 mt-4">
          <li class="d-flex align-items-center mb-3">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-info"><i class="bx bx-user-voice"></i></span>
            </div>
            <div class="d-flex justify-content-between w-100">
              <div>
                <h6 class="mb-0">Pengaduan</h6>
                <small class="text-body-secondary">Tindak lanjut <strong>24 jam</strong></small>
              </div>
              <span class="fw-semibold">78%</span>
            </div>
          </li>
          <li class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-id-card"></i></span>
            </div>
            <div class="d-flex justify-content-between w-100">
              <div>
                <h6 class="mb-0">Perizinan</h6>
                <small class="text-body-secondary">Penyelesaian tepat waktu</small>
              </div>
              <span class="fw-semibold">64%</span>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mt-1">
  <div class="col-xl-4 col-lg-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0">Statistik layanan</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" data-bs-toggle="dropdown">
            <i class="bx bx-dots-vertical-rounded text-body-secondary"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="javascript:void(0);">Lihat detail</a>
            <a class="dropdown-item" href="javascript:void(0);">Export CSV</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="orderStatisticsChart"></div>
        <ul class="list-unstyled d-flex flex-column gap-3 mt-4 mb-0">
          <li class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <span class="badge rounded-circle bg-label-primary me-3 p-2"><i class="bx bx-check"></i></span>
              <div>
                <h6 class="mb-0">Selesai</h6>
                <small class="text-body-secondary">Permohonan tuntas</small>
              </div>
            </div>
            <span class="fw-semibold">120</span>
          </li>
          <li class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <span class="badge rounded-circle bg-label-warning me-3 p-2"><i class="bx bx-time"></i></span>
              <div>
                <h6 class="mb-0">Dalam proses</h6>
                <small class="text-body-secondary">Menunggu tindak lanjut</small>
              </div>
            </div>
            <span class="fw-semibold">56</span>
          </li>
          <li class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <span class="badge rounded-circle bg-label-danger me-3 p-2"><i class="bx bx-error"></i></span>
              <div>
                <h6 class="mb-0">Butuh respon</h6>
                <small class="text-body-secondary">Belum diassign</small>
              </div>
            </div>
            <span class="fw-semibold">9</span>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-xl-4 col-lg-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0">Rasio kinerja</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" data-bs-toggle="dropdown">
            <i class="bx bx-dots-vertical-rounded text-body-secondary"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="javascript:void(0);">Laporan bulanan</a>
            <a class="dropdown-item" href="javascript:void(0);">Kirim email</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-end gap-3 mb-4">
          <div>
            <h6 class="mb-1">Kepuasan</h6>
            <h4 class="mb-0">86%</h4>
          </div>
          <div>
            <h6 class="mb-1">Efektivitas</h6>
            <h4 class="mb-0">74%</h4>
          </div>
        </div>
        <div id="incomeChart"></div>
        <p class="text-body-secondary mt-4 mb-0">Pantau indikator utama untuk menjaga kualitas layanan publik.</p>
      </div>
    </div>
  </div>

  <div class="col-xl-4 col-lg-12">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0">Agenda minggu ini</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" data-bs-toggle="dropdown">
            <i class="bx bx-dots-vertical-rounded text-body-secondary"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="javascript:void(0);">Tambah agenda</a>
            <a class="dropdown-item" href="javascript:void(0);">Sinkron kalender</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="expensesOfWeek"></div>
        <ul class="list-unstyled mt-4 mb-0">
          <li class="d-flex align-items-center mb-3">
            <span class="badge bg-label-primary me-3"><i class="bx bx-megaphone"></i></span>
            <div>
              <h6 class="mb-0">Release berita utama</h6>
              <small class="text-body-secondary">Rabu, 10.00 WIB</small>
            </div>
          </li>
          <li class="d-flex align-items-center mb-3">
            <span class="badge bg-label-success me-3"><i class="bx bx-group"></i></span>
            <div>
              <h6 class="mb-0">Forum konsultasi publik</h6>
              <small class="text-body-secondary">Kamis, 13.30 WIB</small>
            </div>
          </li>
          <li class="d-flex align-items-center">
            <span class="badge bg-label-warning me-3"><i class="bx bx-file"></i></span>
            <div>
              <h6 class="mb-0">Evaluasi SOP internal</h6>
              <small class="text-body-secondary">Jumat, 09.00 WIB</small>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>


<!-- Toast -->
<div class="bs-toast toast fade" id="dashboardWelcomeToast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="8000">
  <div class="toast-header">
    <i class="bx bx-info-circle me-2"></i>
    <div class="me-auto fw-semibold">Panduan Dashboard</div>
    <small>Baru saja</small>
    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Tutup"></button>
  </div>
  <div class="toast-body">
    Gunakan tombol “Tambah Berita” atau kartu statistik untuk mengakses modul terkait. Data grafik masih placeholder, sesuaikan dengan data riil modul ketika tersedia.
  </div>
</div>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/vendor/libs/apex-charts/apexcharts.js') ?>"></script>
<script src="<?= base_url('assets/js/dashboards-analytics.js') ?>"></script>
<?= $this->endSection() ?>


