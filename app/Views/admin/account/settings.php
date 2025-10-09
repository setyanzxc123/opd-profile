<?php
  $validation = $validation ?? null;
  $formErrors = is_array($formErrors ?? null) ? $formErrors : [];
  $user       = is_array($user ?? null) ? $user : [];
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header border-0 bg-transparent pb-0">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
          <div>
            <h4 class="fw-bold mb-1">Pengaturan Akun</h4>
            <p class="text-muted mb-0">Perbarui informasi akun Anda sendiri.</p>
          </div>
          <a href="<?= site_url('admin') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Dashboard
          </a>
        </div>
      </div>
      <div class="card-body pt-3">
        <?php if (session()->getFlashdata('message')): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert" aria-live="polite">
            <i class="bx bx-check-circle me-2"></i><?= esc(session('message')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert" aria-live="assertive">
            <i class="bx bx-error-circle me-2"></i><?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('admin/settings') ?>" class="pt-2">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" value="<?= esc($user['username'] ?? '') ?>" disabled>
            <div class="form-text">Username tidak dapat diubah dari halaman ini.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="accountEmail">Email <span class="text-danger">*</span></label>
            <?php $emailError = $validation && $validation->hasError('email'); ?>
            <input
              type="email"
              id="accountEmail"
              name="email"
              required
              class="form-control<?= $emailError ? ' is-invalid' : '' ?>"
              value="<?= esc(old('email', $user['email'] ?? '')) ?>"
              autocomplete="email"
            >
            <?php if ($emailError): ?>
              <div class="invalid-feedback d-block"><?= esc($validation->getError('email')) ?></div>
            <?php else: ?>
              <div class="form-text text-muted">Pastikan email aktif untuk menerima pemberitahuan.</div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label" for="accountName">Nama</label>
            <?php $nameError = $validation && $validation->hasError('name'); ?>
            <input
              type="text"
              id="accountName"
              name="name"
              class="form-control<?= $nameError ? ' is-invalid' : '' ?>"
              value="<?= esc(old('name', $user['name'] ?? '')) ?>"
              maxlength="100"
              autocomplete="name"
            >
            <?php if ($nameError): ?>
              <div class="invalid-feedback d-block"><?= esc($validation->getError('name')) ?></div>
            <?php else: ?>
              <div class="form-text text-muted">Nama akan ditampilkan di riwayat aktivitas.</div>
            <?php endif; ?>
          </div>

          <hr class="my-4">
          <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bx bx-lock-alt text-primary fs-4"></i>
            <div>
              <h6 class="mb-0">Ganti Password</h6>
              <small class="text-muted">Opsional. Isi password saat ini dan password baru bila ingin mengganti.</small>
            </div>
          </div>

          <?php $currentPasswordError = $formErrors['current_password'] ?? ($validation && $validation->hasError('current_password') ? $validation->getError('current_password') : ''); ?>
          <div class="mb-3">
            <label class="form-label" for="currentPassword">Password Saat Ini</label>
            <input
              type="password"
              id="currentPassword"
              name="current_password"
              class="form-control<?= $currentPasswordError ? ' is-invalid' : '' ?>"
              autocomplete="current-password"
            >
            <?php if ($currentPasswordError): ?>
              <div class="invalid-feedback d-block"><?= esc($currentPasswordError) ?></div>
            <?php else: ?>
              <div class="form-text text-muted">Wajib diisi jika Anda ingin mengganti password.</div>
            <?php endif; ?>
          </div>

          <?php $passwordError = $validation && $validation->hasError('password'); ?>
          <div class="mb-3">
            <label class="form-label" for="newPassword">Password Baru</label>
            <input
              type="password"
              id="newPassword"
              name="password"
              class="form-control<?= $passwordError ? ' is-invalid' : '' ?>"
              minlength="8"
              autocomplete="new-password"
            >
            <?php if ($passwordError): ?>
              <div class="invalid-feedback d-block"><?= esc($validation->getError('password')) ?></div>
            <?php else: ?>
              <div class="form-text text-muted">Minimal 8 karakter. Biarkan kosong bila tidak ingin mengganti.</div>
            <?php endif; ?>
          </div>

          <?php $confirmError = $validation && $validation->hasError('password_confirm'); ?>
          <div class="mb-4">
            <label class="form-label" for="confirmPassword">Konfirmasi Password Baru</label>
            <input
              type="password"
              id="confirmPassword"
              name="password_confirm"
              class="form-control<?= $confirmError ? ' is-invalid' : '' ?>"
              autocomplete="new-password"
            >
            <?php if ($confirmError): ?>
              <div class="invalid-feedback d-block"><?= esc($validation->getError('password_confirm')) ?></div>
            <?php endif; ?>
          </div>

          <div class="d-flex flex-wrap justify-content-end gap-2">
            <button type="reset" class="btn btn-outline-secondary"><i class="bx bx-reset me-1"></i> Atur Ulang</button>
            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

