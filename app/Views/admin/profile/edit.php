<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row">
  <div class="col-md-12">
    <div class="x_panel">
      <div class="x_title"><h2>Profil OPD</h2><div class="clearfix"></div></div>
      <div class="x_content">

        <?php if (session()->getFlashdata('message')): ?>
          <div class="alert alert-success"><?= esc(session('message')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <form class="form-horizontal form-label-left" method="post" action="<?= site_url('admin/profile') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= esc($profile['id']) ?>">

          <div class="form-group">
            <label class="control-label col-md-2">Name <span class="required">*</span></label>
            <div class="col-md-8">
              <input type="text" name="name" class="form-control" required
                     value="<?= esc(old('name', $profile['name'])) ?>">
              <?php if (isset($validation) && $validation->hasError('name')): ?>
                <span class="text-danger small"><?= esc($validation->getError('name')) ?></span>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <label class="control-label col-md-2">Description</label>
            <div class="col-md-8">
              <textarea name="description" rows="4" class="form-control"><?= esc(old('description', $profile['description'])) ?></textarea>
            </div>
          </div>

          <div class="form-group">
            <label class="control-label col-md-2">Vision</label>
            <div class="col-md-8">
              <textarea name="vision" rows="3" class="form-control"><?= esc(old('vision', $profile['vision'])) ?></textarea>
            </div>
          </div>

          <div class="form-group">
            <label class="control-label col-md-2">Mission</label>
            <div class="col-md-8">
              <textarea name="mission" rows="3" class="form-control"><?= esc(old('mission', $profile['mission'])) ?></textarea>
            </div>
          </div>

          <div class="form-group">
            <label class="control-label col-md-2">Address</label>
            <div class="col-md-8">
              <textarea name="address" rows="2" class="form-control"><?= esc(old('address', $profile['address'])) ?></textarea>
            </div>
          </div>

          <div class="form-group">
            <label class="control-label col-md-2">Phone</label>
            <div class="col-md-8">
              <input type="text" name="phone" class="form-control" value="<?= esc(old('phone', $profile['phone'])) ?>">
              <?php if (isset($validation) && $validation->hasError('phone')): ?>
                <span class="text-danger small"><?= esc($validation->getError('phone')) ?></span>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <label class="control-label col-md-2">Email</label>
            <div class="col-md-8">
              <input type="email" name="email" class="form-control" value="<?= esc(old('email', $profile['email'])) ?>">
              <?php if (isset($validation) && $validation->hasError('email')): ?>
                <span class="text-danger small"><?= esc($validation->getError('email')) ?></span>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <div class="col-md-8 col-md-offset-2">
              <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

