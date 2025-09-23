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
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
        <table id="galleriesTable" class="table table-striped align-middle">
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
                    <img src="<?= esc(base_url($g['image_path']), 'url') ?>" alt="img" style="width:64px;height:64px;object-fit:cover;border-radius:6px;">
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <a href="<?= site_url('admin/galleries/edit/'.$g['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bx bx-edit"></i> Edit</a>
                  <form action="<?= site_url('admin/galleries/delete/'.$g['id']) ?>" method="post" style="display:inline" onsubmit="return confirm('Hapus item ini?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
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
    $('#galleriesTable').DataTable({
      pageLength: 10,
      order: [],
      language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
      columnDefs: [
        { targets: -1, orderable: false, searchable: false },
        { targets: 3, orderable: false }
      ]
    });
  });
</script>
<?= $this->endSection() ?>

