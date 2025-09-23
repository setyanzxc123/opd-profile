<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold mb-0"><?= esc($title ?? 'News Form') ?></h4>
          <a href="<?= site_url('admin/news') ?>" class="btn btn-sm btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" action="<?= $mode === 'edit' ? site_url('admin/news/update/'.$item['id']) : site_url('admin/news') ?>">
          <?= csrf_field() ?>

          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control" required value="<?= esc(old('title', $item['title'])) ?>">
              <?php if (isset($validation) && $validation->hasError('title')): ?>
                <div class="form-text text-danger"><?= esc($validation->getError('title')) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-4">
              <label class="form-label">Published At</label>
              <?php
                $val = old('published_at', $item['published_at']);
                if ($val && strpos($val, 'T') === false) { $val = str_replace(' ', 'T', substr($val, 0, 16)); }
              ?>
              <input type="datetime-local" name="published_at" class="form-control" value="<?= esc($val) ?>">
            </div>

            <div class="col-md-12">
              <label class="form-label">Content <span class="text-danger">*</span></label>
              <textarea name="content" rows="10" class="form-control" placeholder="Write the news content..." required><?= esc(old('content', $item['content'])) ?></textarea>
              <?php if (isset($validation) && $validation->hasError('content')): ?>
                <div class="form-text text-danger"><?= esc($validation->getError('content')) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <label class="form-label">Thumbnail</label>
              <input type="file" name="thumbnail" accept="image/*" class="form-control">
              <?php if (!empty($item['thumbnail'])): ?>
                <div class="form-text">Current: <a target="_blank" href="<?= esc(base_url($item['thumbnail']), 'url') ?>">view</a></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>


