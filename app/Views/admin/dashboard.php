<?php
use CodeIgniter\I18n\Time;

$metrics          = $metrics ?? [];
$defaultContact   = [
    'counts' => ['new' => 0, 'in_progress' => 0, 'closed' => 0],
    'total'  => 0,
    'latest' => [],
];
$contactSummary   = array_replace_recursive($defaultContact, $contactSummary ?? []);
$latestNews       = $latestNews ?? [];
$latestDocuments  = $latestDocuments ?? [];
$activityFeed     = $activityFeed ?? [];
$welcomeName      = $welcomeName ?? 'Admin';

$formatNumber = static fn ($value): string => number_format((int) $value, 0, ',', '.');
$parseTime = static function (?string $datetime): ?Time {
    if (! $datetime) {
        return null;
    }
    try {
        return Time::parse($datetime);
    } catch (\Throwable $e) {
        return null;
    }
};

$now = Time::now();

$statusMeta = [
    'new'         => ['label' => 'Baru',      'badge' => 'text-bg-primary'],
    'in_progress' => ['label' => 'Diproses',  'badge' => 'text-bg-warning'],
    'closed'      => ['label' => 'Selesai',   'badge' => 'text-bg-success'],
];

$openContacts = (int) (($contactSummary['counts']['new'] ?? 0) + ($contactSummary['counts']['in_progress'] ?? 0));
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div class="pe-lg-4">
          <h4 class="mb-1">Halo, <?= esc($welcomeName) ?></h4>
          <p class="text-body-secondary mb-0">Fokus pada hal penting hari ini.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <a href="<?= site_url('admin/news/create') ?>" class="btn btn-primary btn-sm">
            <i class="bx bx-plus me-1"></i>Buat Konten
          </a>
          <a href="<?= site_url('admin/contacts') ?>" class="link-secondary small text-decoration-none">
            <i class="bx bx-message-rounded-dots me-1"></i>Lihat Pesan
          </a>
          <span class="text-body-secondary">&middot;</span>
          <a href="<?= site_url('admin/logs') ?>" class="link-secondary small text-decoration-none">
            <i class="bx bx-time-five me-1"></i>Aktivitas
          </a>
        </div>
      </div>
    </div>
  </div>

  <?php if (! empty($metrics)): ?>
    <div class="col-12">
      <div class="row g-3">
        <?php foreach ($metrics as $metric): ?>
          <?php
            $icon = 'bx ' . preg_replace('/[^a-z0-9_-]/i', '', (string) ($metric['icon'] ?? 'bx-data'));
            $url  = $metric['url'] ?? '#';
          ?>
          <div class="col-sm-6 col-xl-3">
            <a href="<?= esc($url) ?>" class="text-reset text-decoration-none">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-uppercase small text-body-secondary mb-1"><?= esc($metric['label'] ?? '-') ?></div>
                    <div class="fs-3 fw-semibold lh-1"><?= esc($formatNumber($metric['value'] ?? 0)) ?></div>
                  </div>
                  <span class="badge text-bg-light p-3 rounded-circle"><i class="<?= esc($icon) ?>"></i></span>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="col-xl-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h6 class="mb-0">Pesan Kontak</h6>
          <small class="text-body-secondary"><?= esc($formatNumber($openContacts)) ?> butuh tindakan</small>
        </div>
        <a href="<?= site_url('admin/contacts') ?>" class="link-secondary small text-decoration-none">Kelola</a>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-baseline justify-content-between mb-3">
          <div class="text-body-secondary text-uppercase small">Butuh Tindakan</div>
          <div class="fs-4 fw-semibold"><?= esc($formatNumber($openContacts)) ?></div>
        </div>
        <div class="text-body-secondary text-uppercase small mb-2">Terbaru</div>
        <ul class="list-group list-group-flush mb-0">
          <?php if (! empty($contactSummary['latest'])): ?>
            <?php foreach ($contactSummary['latest'] as $message): ?>
              <?php
                $createdAt   = $parseTime($message['created_at'] ?? null);
                $createdHuman= $createdAt ? $createdAt->humanize() : 'Waktu tidak tersedia';
                $statusKey   = $message['status'] ?? 'new';
                if ($statusKey === 'closed') { continue; }
                $statusInfo  = $statusMeta[$statusKey] ?? ['label' => ucfirst((string) $statusKey), 'badge' => 'text-bg-secondary'];
                $preview     = trim((string) ($message['message'] ?? ''));
                $preview     = $preview !== '' ? mb_strimwidth(strip_tags($preview), 0, 120, '...') : 'Tanpa isi pesan';
              ?>
              <li class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start gap-3">
                  <div class="min-w-0">
                    <div class="fw-semibold text-truncate">
                      <a href="<?= site_url('admin/contacts/' . (int) ($message['id'] ?? 0)) ?>" class="text-body text-decoration-none">
                        <?= esc($message['subject'] ?? ($message['name'] ?? 'Pesan')) ?>
                      </a>
                    </div>
                    <small class="text-body-secondary text-truncate d-block">
                      <?= esc($preview) ?> &middot; dari <?= esc($message['name'] ?: 'Anonim') ?> &middot; <?= esc($createdHuman) ?>
                    </small>
                  </div>
                  <?php $badgeClass = ['new'=>'text-bg-primary','in_progress'=>'text-bg-warning','closed'=>'text-bg-success'][$statusKey] ?? 'text-bg-secondary'; ?>
                  <span class="badge <?= esc($badgeClass) ?>"><?= esc($statusInfo['label'] ?? ucfirst((string) $statusKey)) ?></span>
                </div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="list-group-item px-0 text-body-secondary">Belum ada pesan yang tercatat.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-xl-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Aktivitas Terbaru</h6>
        <a href="<?= site_url('admin/logs') ?>" class="link-secondary small text-decoration-none">Semua</a>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush mb-0">
          <?php if (! empty($activityFeed)): ?>
            <?php foreach (array_slice($activityFeed, 0, 5) as $log): ?>
              <?php
                $logTime   = $parseTime($log['created_at'] ?? null);
                $timeLabel = $logTime ? $logTime->humanize() : 'Baru';
                $actor     = $log['name'] ?: ($log['username'] ?? 'Sistem');
              ?>
              <li class="py-2">
                <div class="d-flex justify-content-between align-items-start gap-3">
                  <div class="min-w-0">
                    <div class="fw-semibold text-truncate"><?= esc($log['action'] ?? 'Aktivitas') ?></div>
                    <small class="text-body-secondary d-block text-truncate"><?= esc($log['description'] ?? '-') ?></small>
                    <small class="text-body-secondary">oleh <?= esc($actor) ?></small>
                  </div>
                  <small class="text-body-secondary text-nowrap"><?= esc($timeLabel) ?></small>
                </div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="text-body-secondary">Belum ada aktivitas tercatat.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Berita Terbaru</h6>
        <a href="<?= site_url('admin/news') ?>" class="link-primary small text-decoration-none">Kelola</a>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush mb-0">
          <?php if (! empty($latestNews)): ?>
            <?php foreach (array_slice($latestNews, 0, 5) as $news): ?>
              <?php
                $publishedAt   = $parseTime($news['published_at'] ?? null);
                $isDraft       = empty($news['published_at']);
                $badgeClass    = 'text-bg-success';
                $badgeText     = 'Terbit';
                if ($isDraft) {
                  $badgeClass = 'text-bg-secondary';
                  $badgeText  = 'Draft';
                } elseif ($publishedAt && $publishedAt->getTimestamp() > $now->getTimestamp()) {
                  $badgeClass = 'text-bg-info';
                  $badgeText  = 'Terjadwal';
                }
                $publishedLabel = $publishedAt ? $publishedAt->humanize() : ($isDraft ? 'Draft disimpan' : 'Belum dijadwalkan');
              ?>
              <li class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start gap-3">
                  <div class="min-w-0">
                    <div class="fw-semibold text-truncate">
                      <a href="<?= site_url('admin/news/edit/' . (int) ($news['id'] ?? 0)) ?>" class="text-body text-decoration-none">
                        <?= esc($news['title'] ?? 'Tanpa judul') ?>
                      </a>
                    </div>
                    <small class="text-body-secondary d-block text-truncate"><?= esc($publishedLabel) ?></small>
                  </div>
                  <span class="badge <?= esc($badgeClass) ?>"><?= esc($badgeText) ?></span>
                </div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="list-group-item px-0 text-body-secondary">Belum ada berita terbaru.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Dokumen Terbaru</h6>
        <a href="<?= site_url('admin/documents') ?>" class="link-info small text-decoration-none">Kelola</a>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush mb-0">
          <?php if (! empty($latestDocuments)): ?>
            <?php foreach (array_slice($latestDocuments, 0, 5) as $document): ?>
              <?php
                $categoryLabel = trim((string) ($document['category'] ?? ''));
                if ($categoryLabel === '') { $categoryLabel = 'Tanpa Kategori'; }
                $yearLabel = trim((string) ($document['year'] ?? ''));
              ?>
              <li class="py-2">
                <div class="d-flex justify-content-between align-items-start gap-3">
                  <div class="min-w-0">
                    <div class="fw-semibold text-truncate">
                      <a href="<?= site_url('admin/documents/edit/' . (int) ($document['id'] ?? 0)) ?>" class="text-body text-decoration-none">
                        <?= esc($document['title'] ?? 'Tanpa judul') ?>
                      </a>
                    </div>
                    <small class="text-body-secondary d-block text-truncate">
                      <?= esc($categoryLabel) ?><?php if ($yearLabel !== ''): ?> &middot; <?= esc($yearLabel) ?><?php endif; ?>
                    </small>
                  </div>
                  <span class="badge text-bg-info">Dokumen</span>
                </div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="text-body-secondary">Belum ada dokumen terbaru.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
