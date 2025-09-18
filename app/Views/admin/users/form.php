<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0"><?= esc($title ?? 'Form Pengguna') ?></h4>
      <a href="<?= site_url('admin/users') ?>" class="btn btn-sm btn-outline-secondary"><i class="bx bx-arrow-back"></i> Kembali</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible" role="alert">
        <?= esc(session('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= $mode === 'edit' ? site_url('admin/users/update/'.$user['id']) : site_url('admin/users') ?>">
      <?= csrf_field() ?>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nama</label>
          <input type="text" class="form-control" name="name" value="<?= esc(old('name', $user['name'] ?? '')) ?>">
          <?php if (isset($validation) && $validation->hasError('name')): ?>
            <div class="form-text text-danger"><?= esc($validation->getError('name')) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label">Username <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="username" required value="<?= esc(old('username', $user['username'])) ?>">
          <?php if (isset($validation) && $validation->hasError('username')): ?>
            <div class="form-text text-danger"><?= esc($validation->getError('username')) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" class="form-control" name="email" required value="<?= esc(old('email', $user['email'])) ?>">
          <?php if (isset($validation) && $validation->hasError('email')): ?>
            <div class="form-text text-danger"><?= esc($validation->getError('email')) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label">Role <span class="text-danger">*</span></label>
          <select name="role" class="form-select" required>
            <?php foreach ($roles as $role): ?>
              <option value="<?= esc($role) ?>" <?= old('role', $user['role']) === $role ? 'selected' : '' ?>>
                <?= ucfirst($role) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($validation) && $validation->hasError('role')): ?>
            <div class="form-text text-danger"><?= esc($validation->getError('role')) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label">Password <?= $mode === 'edit' ? '<small class="text-muted">(isi jika ingin mengganti)</small>' : '<span class="text-danger">*</span>' ?></label>
          <input type="password" class="form-control" name="password" <?= $mode === 'edit' ? '' : 'required' ?>>
          <?php if (isset($validation) && $validation->hasError('password')): ?>
            <div class="form-text text-danger"><?= esc($validation->getError('password')) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label">Konfirmasi Password <?= $mode === 'edit' ? '' : '<span class="text-danger">*</span>' ?></label>
          <input type="password" class="form-control" name="password_confirm" <?= $mode === 'edit' ? '' : 'required' ?>>
          <?php if (isset($validation) && $validation->hasError('password_confirm')): ?>
            <div class="form-text text-danger"><?= esc($validation->getError('password_confirm')) ?></div>
          <?php endif; ?>
        </div>

        <?php if ($mode === 'edit'): ?>
        <div class="col-md-6">
          <label class="form-label">Status Aktif</label>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= old('is_active', $user['is_active']) ? 'checked' : '' ?>>
            <label class="form-check-label"><?= old('is_active', $user['is_active']) ? 'Aktif' : 'Nonaktif' ?></label>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

