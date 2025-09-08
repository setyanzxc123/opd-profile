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
      <div class="table-responsive">
        <table class="table table-striped">
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
          <?php if (empty($items)): ?>
            <tr><td colspan="6" class="text-center text-muted">No news yet</td></tr>
          <?php else: foreach ($items as $i => $n): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= esc($n['title']) ?></td>
              <td><small class="text-muted"><?= esc($n['slug']) ?></small></td>
              <td><?= esc($n['published_at'] ?: '-') ?></td>
              <td>
                <?php if (!empty($n['thumbnail'])): ?>
                  <img src="<?= base_url($n['thumbnail']) ?>" alt="thumb" style="width:48px;height:48px;object-fit:cover;border-radius:4px;">
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
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

