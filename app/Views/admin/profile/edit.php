<?php
  $validation = $validation ?? null;
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-header border-0 bg-transparent pb-0">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
          <div>
            <h4 class="fw-bold mb-1">Profil OPD</h4>
            <p class="text-muted mb-0">Perbarui informasi instansi yang tampil pada situs publik.</p>
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

        <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info" role="alert">
          <i class="bx bx-bulb me-2"></i>
          <span>Gunakan konten yang ringkas dan mudah dibaca. Perubahan akan langsung terlihat oleh pengunjung.</span>
        </div>

        <form method="post" action="<?= site_url('admin/profile') ?>" class="pt-2">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= esc($profile['id']) ?>">

          <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="tab-umum-tab" data-bs-toggle="tab" data-bs-target="#tab-umum" type="button" role="tab" aria-controls="tab-umum" aria-selected="true">Umum</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-visimisi-tab" data-bs-toggle="tab" data-bs-target="#tab-visimisi" type="button" role="tab" aria-controls="tab-visimisi" aria-selected="false">Visi &amp; Misi</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-kontak-tab" data-bs-toggle="tab" data-bs-target="#tab-kontak" type="button" role="tab" aria-controls="tab-kontak" aria-selected="false">Kontak &amp; Lokasi</button>
            </li>
          </ul>

          <div class="tab-content border border-top-0 p-3">
            <div class="tab-pane fade show active" id="tab-umum" role="tabpanel" aria-labelledby="tab-umum-tab">
              <div class="row g-3">
                <div class="col-xl-8 col-lg-9">
                  <label class="form-label">Nama OPD <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control" required maxlength="150" value="<?= esc(old('name', $profile['name'])) ?>" placeholder="Contoh: Dinas Komunikasi dan Informatika">
                  <?php if ($validation && $validation->hasError('name')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('name')) ?></div>
                  <?php else: ?>
                    <div class="form-text text-muted">Nama resmi instansi akan tampil di header situs publik.</div>
                  <?php endif; ?>
                </div>

                <div class="col-xl-10">
                  <label class="form-label">Deskripsi Singkat</label>
                  <textarea name="description" rows="4" class="form-control" placeholder="Tuliskan deskripsi ringkas mengenai profil dan layanan utama OPD."><?= esc(old('description', $profile['description'])) ?></textarea>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-visimisi" role="tabpanel" aria-labelledby="tab-visimisi-tab">
              <div class="row g-3">
                <div class="col-xl-10">
                  <label class="form-label">Visi</label>
                  <textarea name="vision" rows="3" class="form-control" placeholder="Masukkan rumusan visi instansi."><?= esc(old('vision', $profile['vision'])) ?></textarea>
                </div>
                <div class="col-xl-10">
                  <label class="form-label">Misi</label>
                  <textarea name="mission" rows="4" class="form-control" placeholder="Jabarkan poin-poin misi instansi."><?= esc(old('mission', $profile['mission'])) ?></textarea>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-kontak" role="tabpanel" aria-labelledby="tab-kontak-tab">
              <div class="row g-3">
                <div class="col-lg-8">
                  <label class="form-label">Alamat Kantor</label>
                  <textarea name="address" rows="3" class="form-control" placeholder="Tuliskan alamat lengkap beserta patokan bila perlu."><?= esc(old('address', $profile['address'])) ?></textarea>
                </div>
                <div class="col-md-6 col-lg-4">
                  <label class="form-label">Telepon</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-phone"></i></span>
                    <input type="text" name="phone" class="form-control" value="<?= esc(old('phone', $profile['phone'])) ?>" placeholder="(021) 555-1234">
                  </div>
                  <?php if ($validation && $validation->hasError('phone')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('phone')) ?></div>
                  <?php endif; ?>
                </div>
                <div class="col-md-6 col-lg-4">
                  <label class="form-label">Email</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                    <input type="email" name="email" class="form-control" value="<?= esc(old('email', $profile['email'])) ?>" placeholder="kontak@opd.go.id">
                  </div>
                  <?php if ($validation && $validation->hasError('email')): ?>
                    <div class="form-text text-danger"><?= esc($validation->getError('email')) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Wajib diisi.</p>
            <div class="d-flex flex-wrap gap-2">
              <button type="reset" class="btn btn-outline-secondary"><i class="bx bx-reset me-1"></i> Atur Ulang</button>
              <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
