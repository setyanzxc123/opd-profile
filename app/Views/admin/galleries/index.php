<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold">Galeri</h4>
      <a class="btn btn-primary" href="<?= site_url('admin/galleries/create') ?>"><i class="bx bx-plus"></i> Tambah</a>
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
        <table id="galleriesTable" class="table table-striped table-sm align-middle">
          <thead>
            <tr>
              <th style="width:60px">#</th>
              <th>Judul</th>
              <th>Keterangan</th>
              <th>Gambar</th>
              <th style="width:160px" class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $i => $g): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= esc($g['title']) ?></td>
                <td><small class="text-muted"><?= esc($g['description']) ?></small></td>
                <td>
                <?php if (!empty($g['image_path'])): ?>
                  <img src="<?= esc(base_url($g['image_path']), 'attr') ?>" alt="Gambar galeri" style="width:64px;height:64px;object-fit:cover;border-radius:6px;">
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex flex-wrap justify-content-end gap-1">
                    <a href="<?= site_url('admin/galleries/edit/'.$g['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bx bx-edit"></i> Ubah</a>
                    <form action="<?= site_url('admin/galleries/delete/'.$g['id']) ?>" method="post" onsubmit="return confirm('Hapus galeri ini?')" class="m-0">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i> Hapus</button>
                    </form>
                  </div>
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
<link rel="stylesheet"
      href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"
      integrity="sha384-5oFfLntNy8kuC2TaebWZbaHTqdh3Q+7PwYbB490gupK0YtTAB7mBJGv4bQl9g9rK"
      crossorigin="anonymous">
<link rel="stylesheet"
      href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css"
      integrity="sha384-jj44VXG857zuSsNQ7iqJihzOcCFRhs3qS4oLwyc4Hg+P9WjpwyR6T1ulnFKzhhaQ"
      crossorigin="anonymous">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"
        integrity="sha384-k5vbMeKHbxEZ0AEBTSdR7UjAgWCcUfrS8c0c5b2AfIh7olfhNkyCZYwOfzOQhauK"
        crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"
        integrity="sha384-PgPBH0hy6DTJwu7pTf6bkRqPlf/+pjUBExpr/eIfzszlGYFlF9Wi9VTAJODPhgCO"
        crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"
        integrity="sha384-VUnyCeQcqiiTlSM4AISHjJWKgLSM5VSyOeipcD9S/ybCKR3OhChZrPPjjrLfVV0y"
        crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"
        integrity="sha384-T6YQaHyTPTbybQQV23jtlugHCneQYjePXdcEU+KMWGQY8EUQygBW9pRx0zpSU0/i"
        crossorigin="anonymous"></script>
<script>
  $(function() {
    $('#galleriesTable').DataTable({
      pageLength: 10,
      order: [],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.childRowImmediate,
          target: 'tr'
        }
      },
      autoWidth: false,
      language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
      columnDefs: [
        { targets: 0, responsivePriority: 5 },
        { targets: 1, responsivePriority: 1 },
        { targets: 2, responsivePriority: 6 },
        { targets: 3, orderable: false, responsivePriority: 4 },
        { targets: -1, orderable: false, searchable: false, responsivePriority: 2 }
      ]
    });
  });
</script>
<?= $this->endSection() ?>


