<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="card mb-4 bg-white">
      <div class="card-body">
        <h4 class="fw-bold mb-3">Profil OPD</h4>

    <?php if (session()->getFlashdata('message')): ?>
      <div class="alert alert-success alert-dismissible" role="alert">
        <?= esc(session('message')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible" role="alert">
        <?= esc(session('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('admin/profile') ?>" class="pt-2">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= esc($profile['id']) ?>">

      <!-- Tabs -->
      <ul class="nav nav-tabs" id="profileTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tab-general" data-bs-toggle="tab" data-bs-target="#pane-general" type="button" role="tab">General</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-vm" data-bs-toggle="tab" data-bs-target="#pane-vm" type="button" role="tab">Vision & Mission</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-contact" data-bs-toggle="tab" data-bs-target="#pane-contact" type="button" role="tab">Contact</button>
        </li>
      </ul>

      <div class="tab-content border border-top-0 p-3">
        <!-- General -->
        <div class="tab-pane fade show active" id="pane-general" role="tabpanel">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required value="<?= esc(old('name', $profile['name'])) ?>">
              <?php if (isset($validation) && $validation->hasError('name')): ?>
                <div class="form-text text-danger"><?= esc($validation->getError('name')) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-10">
              <label class="form-label">Description</label>
              <textarea name="description" rows="4" class="form-control" placeholder="Short description of the OPD..."><?= esc(old('description', $profile['description'])) ?></textarea>
            </div>
          </div>
        </div>

        <!-- Vision & Mission -->
        <div class="tab-pane fade" id="pane-vm" role="tabpanel">
          <div class="row g-3">
            <div class="col-md-10">
              <label class="form-label">Vision</label>
              <textarea name="vision" rows="3" class="form-control" placeholder="Vision statement..."><?= esc(old('vision', $profile['vision'])) ?></textarea>
            </div>
            <div class="col-md-10">
              <label class="form-label">Mission</label>
              <textarea name="mission" rows="3" class="form-control" placeholder="Mission statements..."><?= esc(old('mission', $profile['mission'])) ?></textarea>
            </div>
          </div>
        </div>

        <!-- Contact -->
        <div class="tab-pane fade" id="pane-contact" role="tabpanel">
          <div class="row g-3">
            <div class="col-md-10">
              <label class="form-label">Address</label>
              <textarea name="address" rows="2" class="form-control" placeholder="Office address..."><?= esc(old('address', $profile['address'])) ?></textarea>
            </div>
            <div class="col-md-5">
              <label class="form-label">Phone</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                <input type="text" name="phone" class="form-control" value="<?= esc(old('phone', $profile['phone'])) ?>">
              </div>
              <?php if (isset($validation) && $validation->hasError('phone')): ?>
                <div class="form-text text-danger"><?= esc($validation->getError('phone')) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-5">
              <label class="form-label">Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                <input type="email" name="email" class="form-control" value="<?= esc(old('email', $profile['email'])) ?>">
              </div>
              <?php if (isset($validation) && $validation->hasError('email')): ?>
                <div class="form-text text-danger"><?= esc($validation->getError('email')) ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save</button>
      </div>
    </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
