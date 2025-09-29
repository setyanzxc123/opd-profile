<?php
use CodeIgniter\I18n\Time;
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12 col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">Detail Pesan</h5>
          <small class="text-muted">Diterima melalui formulir kontak publik</small>
        </div>
        <a class="btn btn-label-secondary btn-sm" href="<?= site_url('admin/contacts') ?>">
          <i class="bx bx-arrow-back"></i> Kembali ke daftar
        </a>
      </div>
      <div class="card-body">
        <?php if (session()->getFlashdata('message')): ?>
          <div class="alert alert-soft-success alert-dismissible" role="alert">
            <?= esc(session('message')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-soft-danger alert-dismissible" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>

        <?php
          $statusKey   = $message['status'] ?? 'new';
          $statusLabel = $statusLabels[$statusKey] ?? ucfirst($statusKey);
          $badgeClass  = [
            'new'         => 'bg-label-primary',
            'in_progress' => 'bg-label-warning',
            'closed'      => 'bg-label-success',
          ][$statusKey] ?? 'bg-label-secondary';
          $createdAt   = ! empty($message['created_at']) ? Time::parse($message['created_at']) : null;
          $respondedAt = ! empty($message['responded_at']) ? Time::parse($message['responded_at']) : null;
          $phoneNumber = trim((string) ($message['phone'] ?? ''));
        ?>

        <dl class="row mb-4">
          <dt class="col-sm-4">Nama Pengirim</dt>
          <dd class="col-sm-8 fw-semibold"><?= esc($message['name']) ?></dd>

          <dt class="col-sm-4">Email</dt>
          <dd class="col-sm-8">
            <a href="mailto:<?= esc($message['email']) ?>" class="text-decoration-none"><?= esc($message['email']) ?></a>
          </dd>

          <dt class="col-sm-4">Nomor Telepon</dt>
          <dd class="col-sm-8">
            <?php if ($phoneNumber !== ''): ?>
              <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $phoneNumber)) ?>" class="text-decoration-none"><?= esc($phoneNumber) ?></a>
            <?php else: ?>
              <span class="text-muted">Tidak disertakan</span>
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Alamat IP</dt>
          <dd class="col-sm-8">
            <?= ! empty($message['ip_address']) ? esc($message['ip_address']) : '<span class="text-muted">Tidak tersedia</span>' ?>
          </dd>

          <dt class="col-sm-4">Peramban</dt>
          <dd class="col-sm-8">
            <?php if (! empty($message['user_agent'])): ?>
              <span class="text-break"><?= esc($message['user_agent']) ?></span>
            <?php else: ?>
              <span class="text-muted">Tidak tersedia</span>
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Subjek</dt>
          <dd class="col-sm-8"><?= esc($message['subject'] ?: '(Tanpa subjek)') ?></dd>

          <dt class="col-sm-4">Status</dt>
          <dd class="col-sm-8"><span class="badge <?= $badgeClass ?>"><?= esc($statusLabel) ?></span></dd>

          <dt class="col-sm-4">Diterima</dt>
          <dd class="col-sm-8">
            <?php if ($createdAt): ?>
              <?= esc($createdAt->toLocalizedString('d MMM yyyy HH:mm')) ?>
            <?php else: ?>
              <span class="text-muted">-</span>
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Ditangani oleh</dt>
          <dd class="col-sm-8">
            <?php if (! empty($message['handler_name'])): ?>
              <div><?= esc($message['handler_name']) ?></div>
              <?php if (! empty($message['handler_email'])): ?>
                <small class="text-muted"><?= esc($message['handler_email']) ?></small>
              <?php endif; ?>
            <?php else: ?>
              <span class="text-muted">Belum ada penanggung jawab</span>
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Ditanggapi</dt>
          <dd class="col-sm-8">
            <?php if ($respondedAt): ?>
              <?= esc($respondedAt->toLocalizedString('d MMM yyyy HH:mm')) ?>
            <?php elseif ($statusKey === 'closed'): ?>
              <span class="text-muted">Ditandai selesai</span>
            <?php else: ?>
              <span class="text-muted">Belum ada tindak lanjut</span>
            <?php endif; ?>
          </dd>
        </dl>

        <div class="mb-4">
          <h6 class="fw-semibold">Isi Pesan</h6>
          <div class="border rounded p-3 bg-light-subtle" style="white-space: pre-wrap;">
            <?= esc($message['message']) ?>
          </div>
        </div>

        <?php if (! empty($message['admin_note'])): ?>
          <div class="alert alert-soft-info" role="alert">
            <strong>Catatan Admin:</strong>
            <div class="mt-2 mb-0" style="white-space: pre-wrap;"><?= esc($message['admin_note']) ?></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Tindak Lanjut</h5>
      </div>
      <div class="card-body">
        <?php $errors = session()->getFlashdata('errors') ?? []; ?>
        <form method="post" action="<?= site_url('admin/contacts/' . $message['id'] . '/status') ?>" class="vstack gap-3">
          <?= csrf_field() ?>
          <div>
            <label class="form-label" for="contactStatus">Status</label>
            <select class="form-select<?= isset($errors['status']) ? ' is-invalid' : '' ?>" id="contactStatus" name="status" required>
              <?php foreach ($statusLabels as $key => $label): ?>
                <option value="<?= esc($key) ?>"<?= ($oldStatus = old('status')) !== null ? ($oldStatus === $key ? ' selected' : '') : ($statusKey === $key ? ' selected' : '') ?>><?= esc($label) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($errors['status'])): ?>
              <div class="invalid-feedback"><?= esc($errors['status']) ?></div>
            <?php endif; ?>
          </div>

          <div>
            <label class="form-label" for="contactNote">Catatan Internal</label>
            <textarea
              class="form-control<?= isset($errors['admin_note']) ? ' is-invalid' : '' ?>"
              id="contactNote"
              name="admin_note"
              rows="5"
              placeholder="Catat progres tindak lanjut, ringkas isi komunikasi, atau informasi penting lainnya."
            ><?= esc(old('admin_note', $message['admin_note'] ?? '')) ?></textarea>
            <?php if (isset($errors['admin_note'])): ?>
              <div class="invalid-feedback"><?= esc($errors['admin_note']) ?></div>
            <?php else: ?>
              <div class="form-text">Catatan ini tidak ditampilkan ke publik.</div>
            <?php endif; ?>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan Perubahan</button>
            <a class="btn btn-label-secondary" href="<?= site_url('admin/contacts') ?>">Kembali</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
