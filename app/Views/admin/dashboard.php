<?php
use CodeIgniter\I18n\Time;

// Safely extract data with defaults
$metrics = $metrics ?? [];
$contactSummary = $contactSummary ?? ['counts' => ['new' => 0, 'in_progress' => 0, 'closed' => 0], 'total' => 0, 'latest' => []];
$latestNews = $latestNews ?? [];
$latestDocuments = $latestDocuments ?? [];
$activityFeed = $activityFeed ?? [];
$welcomeName = $welcomeName ?? 'Admin';

// Ensure canAccess is callable
if (!isset($canAccess) || !is_callable($canAccess)) {
    $canAccess = function($section) { return true; };
}
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<!-- Welcome Card -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
      <h4 class="mb-1">Halo, <?= esc($welcomeName) ?></h4>
      <p class="text-muted mb-0">Kelola konten website OPD Anda</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= site_url('admin/news/create') ?>" class="btn btn-primary btn-sm">
        <i class="bx bx-plus me-1"></i>Buat Berita
      </a>
    </div>
  </div>
</div>

<!-- Metrics Cards -->
<?php if (!empty($metrics)): ?>
<div class="row g-3 mb-4">
  <?php foreach ($metrics as $metric): ?>
    <div class="col-sm-6 col-xl-3">
      <a href="<?= esc($metric['url'] ?? '#') ?>" class="text-decoration-none">
        <div class="card border-0 shadow-sm h-100 hover-lift">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <p class="text-muted text-uppercase small mb-1"><?= esc($metric['label'] ?? '') ?></p>
                <h3 class="mb-0"><?= number_format((int)($metric['value'] ?? 0), 0, ',', '.') ?></h3>
              </div>
              <div class="bg-light rounded p-3">
                <i class="bx <?= esc($metric['icon'] ?? 'bx-data') ?> fs-4"></i>
              </div>
            </div>
          </div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row g-4">
  <!-- Messages Section -->
  <?php if ($canAccess('contacts') && !empty($contactSummary['latest'])): ?>
  <div class="col-12 col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Pesan Kontak Terbaru</h6>
        <a href="<?= site_url('admin/contacts') ?>" class="btn btn-sm btn-link">Lihat Semua</a>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <?php foreach (array_slice($contactSummary['latest'], 0, 5) as $msg): ?>
            <div class="list-group-item border-0 px-0 py-3">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="mb-0">
                  <a href="<?= site_url('admin/contacts/' . ($msg['id'] ?? 0)) ?>" class="text-dark text-decoration-none">
                    <?= esc($msg['subject'] ?? $msg['name'] ?? 'Tanpa subjek') ?>
                  </a>
                </h6>
                <?php
                  $status = $msg['status'] ?? 'new';
                  if ($status === 'in_progress') {
                    $badgeClass = 'warning';
                    $badgeLabel = 'Diproses';
                  } elseif ($status === 'closed') {
                    $badgeClass = 'success';
                    $badgeLabel = 'Selesai';
                  } else {
                    $badgeClass = 'primary';
                    $badgeLabel = 'Baru';
                  }
                ?>
                <span class="badge bg-<?= $badgeClass ?>"><?= $badgeLabel ?></span>
              </div>
              <p class="text-muted small mb-1">
                Dari: <?= esc($msg['name'] ?? 'Anonim') ?>
                <?php if (!empty($msg['created_at'])): ?>
                  · <?= Time::parse($msg['created_at'])->humanize() ?>
                <?php endif; ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Activity Log -->
  <?php if ($canAccess('logs') && !empty($activityFeed)): ?>
  <div class="col-12 col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Aktivitas Terbaru</h6>
        <a href="<?= site_url('admin/logs') ?>" class="btn btn-sm btn-link">Lihat Semua</a>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <?php foreach (array_slice($activityFeed, 0, 5) as $log): ?>
            <div class="list-group-item border-0 px-0 py-3">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <p class="mb-1 fw-medium"><?= esc($log['action'] ?? 'Aktivitas') ?></p>
                  <p class="text-muted small mb-0"><?= esc($log['description'] ?? '') ?></p>
                  <p class="text-muted small mb-0">
                    oleh <?= esc($log['actor_name'] ?? $log['actor_username'] ?? 'Sistem') ?>
                  </p>
                </div>
                <?php if (!empty($log['created_at'])): ?>
                  <small class="text-muted text-nowrap ms-2">
                    <?= Time::parse($log['created_at'])->humanize() ?>
                  </small>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Latest News -->
  <?php if ($canAccess('news') && !empty($latestNews)): ?>
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Berita Terbaru</h6>
        <a href="<?= site_url('admin/news') ?>" class="btn btn-sm btn-link">Kelola</a>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <?php foreach (array_slice($latestNews, 0, 3) as $news): ?>
            <?php
              $isDraft = empty($news['published_at']);
              $publishedAt = $isDraft ? null : Time::parse($news['published_at']);
              $isScheduled = $publishedAt && $publishedAt->getTimestamp() > time();

              if ($isDraft) {
                $badgeClass = 'secondary';
                $badgeText = 'Draft';
              } elseif ($isScheduled) {
                $badgeClass = 'info';
                $badgeText = 'Terjadwal';
              } else {
                $badgeClass = 'success';
                $badgeText = 'Terbit';
              }
            ?>
            <div class="list-group-item border-0 px-0 py-3">
              <div class="d-flex justify-content-between align-items-start mb-1">
                <h6 class="mb-0">
                  <a href="<?= site_url('admin/news/edit/' . ($news['id'] ?? 0)) ?>" class="text-dark text-decoration-none">
                    <?= esc($news['title'] ?? 'Tanpa judul') ?>
                  </a>
                </h6>
                <span class="badge bg-<?= $badgeClass ?>"><?= $badgeText ?></span>
              </div>
              <small class="text-muted">
                <?= $publishedAt ? $publishedAt->humanize() : 'Belum dipublikasikan' ?>
              </small>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Latest Documents -->
  <?php if ($canAccess('documents') && !empty($latestDocuments)): ?>
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Dokumen Terbaru</h6>
        <a href="<?= site_url('admin/documents') ?>" class="btn btn-sm btn-link">Kelola</a>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <?php foreach (array_slice($latestDocuments, 0, 3) as $doc): ?>
            <div class="list-group-item border-0 px-0 py-3">
              <h6 class="mb-1">
                <a href="<?= site_url('admin/documents/edit/' . ($doc['id'] ?? 0)) ?>" class="text-dark text-decoration-none">
                  <?= esc($doc['title'] ?? 'Tanpa judul') ?>
                </a>
              </h6>
              <small class="text-muted">
                <?= esc($doc['category'] ?? 'Tanpa kategori') ?>
                <?php if (!empty($doc['year'])): ?>
                  · <?= esc($doc['year']) ?>
                <?php endif; ?>
              </small>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<style>
.hover-lift {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}
</style>

<?= $this->endSection() ?>
