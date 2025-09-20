<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold">News</h4>
      <a class="btn btn-primary" href="<?= site_url('admin/news/create') ?>"><i class="bx bx-plus"></i> New</a>
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
        <table id="newsTable" class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Title</th>
              <th>Slug</th>
              <th>Published</th>
              <th>Thumbnail</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($items as $i => $n): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= esc($n['title']) ?></td>
              <td><small class="text-muted"><?= esc($n['slug']) ?></small></td>
              <td><?= esc($n['published_at'] ?: '-') ?></td>
              <td>
                <?php if (!empty($n['thumbnail'])): ?>
                  <img src="<?= esc(base_url($n['thumbnail']), 'url') ?>" alt="thumb" style="width:48px;height:48px;object-fit:cover;border-radius:4px;">
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a href="<?= site_url('admin/news/edit/'.$n['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bx bx-edit"></i> Edit</a>
                <form method="post" action="<?= site_url('admin/news/delete/'.$n['id']) ?>" style="display:inline" onsubmit="return confirm('Delete this item?')">
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
    $('#newsTable').DataTable({
      pageLength: 10,
      lengthChange: true,
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

