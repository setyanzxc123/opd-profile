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
          <table id="documentsTable" class="table align-middle document-table mb-0">
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
<?= $this->section('pageStyles') ?>
<link rel="stylesheet"
      href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"
      integrity="sha384-5oFfLntNy8kuC2TaebWZbaHTqdh3Q+7PwYbB490gupK0YtTAB7mBJGv4bQl9g9rK"
      crossorigin="anonymous">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"
        integrity="sha384-k5vbMeKHbxEZ0AEBTSdR7UjAgWCcUfrS8c0c5b2AfIh7olfhNkyCZYwOfzOQhauK"
        crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"
        integrity="sha384-PgPBH0hy6DTJwu7pTf6bkRqPlf/+pjUBExpr/eIfzszlGYFlF9Wi9VTAJODPhgCO"
        crossorigin="anonymous"></script>
<script>
  (function() {
    const table = document.getElementById('documentsTable');
    if (!table || typeof $ !== 'function' || !$.fn.DataTable) {
      return;
    }

    $(table).DataTable({
      pageLength: 10,
      lengthChange: true,
      ordering: true,
      order: [],
      language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
      },
      columnDefs: [
        { targets: -1, orderable: false, searchable: false }
      ]
    });
  })();
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>

