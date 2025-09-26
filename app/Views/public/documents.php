<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="public-section bg-white">
  <div class="container public-container py-5">
    <div class="text-center mb-5">
      <span class="hero-badge text-uppercase">Dokumen</span>
      <h1 class="display-5 fw-bold mt-3 mb-3">Pusat Dokumen Publik</h1>
      <p class="lead text-muted">Unduh dokumen resmi seperti SOP, laporan kinerja, dan regulasi yang dikeluarkan OPD.</p>
    </div>
    <div class="documents-wrap">
      <?php if ($documents): ?>
        <div class="table-responsive">
          <table class="table align-middle document-table mb-0">
            <thead>
              <tr>
                <th scope="col">Judul</th>
                <th scope="col">Kategori</th>
                <th scope="col">Tahun</th>
                <th scope="col" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documents as $document): ?>
                <tr>
                  <td><?= esc($document['title']) ?></td>
                  <td><?= esc($document['category'] ?? '-') ?></td>
                  <td><?= esc($document['year'] ?? '-') ?></td>
                  <td class="text-center">
                    <a class="btn btn-sm btn-public-primary" target="_blank" rel="noopener" href="<?= esc(base_url($document['file_path'])) ?>">Unduh</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-5">
          <p class="text-muted mb-0">Dokumen belum tersedia.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
