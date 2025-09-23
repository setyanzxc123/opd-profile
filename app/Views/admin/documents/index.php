<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold">Dokumen</h4>
      <a class="btn btn-primary" href="<?= site_url('admin/documents/create') ?>"><i class="bx bx-plus"></i> Tambah</a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
      <div class="alert alert-success alert-dismissible" role="alert">
        <?= esc(session('message')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
        <table id="documentsTable" class="table table-striped align-middle">
          <thead>
            <tr>
              <th style="width:60px">#</th>
              <th>Judul</th>
              <th>Kategori</th>
              <th>Tahun</th>
              <th>Berkas</th>
              <th style="width:160px" class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $i => $d): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= esc($d['title']) ?></td>
                <td><small class="text-muted"><?= esc($d['category']) ?: '-' ?></small></td>
                <td><?= esc($d['year'] ?: '-') ?></td>
                <td>
                  <?php if (!empty($d['file_path'])): ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?= esc(base_url($d['file_path']), 'url') ?>" target="_blank"><i class="bx bx-link-external"></i> Lihat</a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <a href="<?= site_url('admin/documents/edit/'.$d['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bx bx-edit"></i> Ubah</a>
                  <form action="<?= site_url('admin/documents/delete/'.$d['id']) ?>" method="post" style="display:inline" onsubmit="return confirm('Hapus dokumen ini?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i> Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(function() {
    $('#documentsTable').DataTable({
      pageLength: 10,
      order: [],
      language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
      columnDefs: [
        { targets: -1, orderable: false, searchable: false },
        { targets: 4, orderable: false }
      ]
    });
  });
</script>
<?= $this->endSection() ?>

