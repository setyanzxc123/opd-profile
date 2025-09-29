<?php
use CodeIgniter\I18n\Time;
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12 col-lg-7">
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
      $hasTechInfo = ! empty($message['ip_address']) || ! empty($message['user_agent']);
    ?>
    <div class="card h-100">
      <div class="card-header d-flex align-items-start justify-content-between gap-3">
        <div>
          <span class="badge <?= esc($badgeClass) ?> mb-2"><?= esc($statusLabel) ?></span>
          <h5 class="mb-1">Detail Pesan</h5>
          <?php if ($createdAt): ?>
            <small class="text-muted">Masuk <?= esc($createdAt->toLocalizedString('d MMM yyyy HH:mm')) ?></small>
          <?php else: ?>
            <small class="text-muted">Diterima melalui formulir kontak publik</small>
          <?php endif; ?>
        </div>
        <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('admin/contacts') ?>">
          <i class="bx bx-arrow-back"></i>
          <span class="ms-1 d-none d-sm-inline">Kembali</span>
        </a>
      </div>
      <div class="card-body">
        <?php if (session()->getFlashdata('message')): ?>
          <div class="alert alert-soft-success alert-dismissible mb-3" role="alert">
            <?= esc(session('message')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-soft-danger alert-dismissible mb-3" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
          <div class="col-sm-6">
            <div class="small text-uppercase text-muted fw-semibold">Nama Pengirim</div>
            <div class="fw-semibold"><?= esc($message['name']) ?></div>
          </div>
          <div class="col-sm-6">
            <div class="small text-uppercase text-muted fw-semibold">Email</div>
            <a href="mailto:<?= esc($message['email']) ?>" class="text-decoration-none"><?= esc($message['email']) ?></a>
          </div>
          <div class="col-sm-6">
            <div class="small text-uppercase text-muted fw-semibold">Nomor Telepon</div>
            <?php if ($phoneNumber !== ''): ?>
              <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $phoneNumber)) ?>" class="text-decoration-none"><?= esc($phoneNumber) ?></a>
            <?php else: ?>
              <span class="text-muted">Tidak disertakan</span>
            <?php endif; ?>
          </div>
          <div class="col-sm-6">
            <div class="small text-uppercase text-muted fw-semibold">Penanggung Jawab</div>
            <?php if (! empty($message['handler_name'])): ?>
              <div class="fw-semibold"><?= esc($message['handler_name']) ?></div>
              <?php if (! empty($message['handler_email'])): ?>
                <div class="small text-muted"><?= esc($message['handler_email']) ?></div>
              <?php endif; ?>
            <?php else: ?>
              <span class="text-muted">Belum ditetapkan</span>
            <?php endif; ?>
          </div>
          <div class="col-sm-6">
            <div class="small text-uppercase text-muted fw-semibold">Ditanggapi</div>
            <?php if ($respondedAt): ?>
              <span><?= esc($respondedAt->toLocalizedString('d MMM yyyy HH:mm')) ?></span>
            <?php elseif ($statusKey === 'closed'): ?>
              <span class="text-muted">Ditandai selesai</span>
            <?php else: ?>
              <span class="text-muted">Belum ada tindak lanjut</span>
            <?php endif; ?>
          </div>
          <div class="col-sm-6">
            <div class="small text-uppercase text-muted fw-semibold">Status Respons</div>
            <?php if ($respondedAt): ?>
              <span class="text-muted">Ditindak <?= esc($respondedAt->humanize()) ?></span>
            <?php else: ?>
              <span class="text-muted">Menunggu tindak lanjut</span>
            <?php endif; ?>
          </div>
          <div class="col-12">
            <div class="small text-uppercase text-muted fw-semibold">Subjek</div>
            <div class="fw-semibold"><?= esc($message['subject'] ?: '(Tanpa subjek)') ?></div>
          </div>
        </div>

        <div class="mb-4">
          <h6 class="fw-semibold mb-2">Isi Pesan</h6>
          <div class="bg-body-tertiary rounded p-3" style="white-space: pre-wrap;">
            <?= esc($message['message']) ?>
          </div>
        </div>

        <?php if (! empty($message['admin_note'])): ?>
          <div class="alert alert-soft-info" role="alert">
            <h6 class="alert-heading mb-2">Catatan Admin</h6>
            <div class="mb-0" style="white-space: pre-wrap;">
              <?= esc($message['admin_note']) ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($hasTechInfo): ?>
          <div class="accordion mt-4" id="contactTechInfo">
            <div class="accordion-item">
              <h2 class="accordion-header" id="contactTechInfoHeading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contactTechInfoCollapse" aria-expanded="false" aria-controls="contactTechInfoCollapse">
                  Info Teknis
                </button>
              </h2>
              <div id="contactTechInfoCollapse" class="accordion-collapse collapse" aria-labelledby="contactTechInfoHeading">
                <div class="accordion-body">
                  <?php if (! empty($message['ip_address'])): ?>
                    <div class="mb-3">
                      <div class="small text-uppercase text-muted fw-semibold">Alamat IP</div>
                      <div><?= esc($message['ip_address']) ?></div>
                    </div>
                  <?php endif; ?>
                  <?php if (! empty($message['user_agent'])): ?>
                    <div>
                      <div class="small text-uppercase text-muted fw-semibold">Peramban</div>
                      <div class="text-break"><?= esc($message['user_agent']) ?></div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
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
              rows="4"
              placeholder="Catat tindak lanjut internal"
            ><?= esc(old('admin_note', $message['admin_note'] ?? '')) ?></textarea>
            <?php if (isset($errors['admin_note'])): ?>
              <div class="invalid-feedback"><?= esc($errors['admin_note']) ?></div>
            <?php else: ?>
              <div class="form-text">Catatan ini hanya terlihat oleh admin.</div>
            <?php endif; ?>
          </div>

          <div class="d-flex flex-wrap justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bx bx-save"></i>
              <span class="ms-1">Simpan</span>
            </button>
            <a class="btn btn-link" href="<?= site_url('admin/contacts') ?>">Kembali</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
