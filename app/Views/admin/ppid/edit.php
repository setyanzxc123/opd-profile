<?php
  $validation = $validation ?? null;
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('pageStyles') ?>
  <link rel="stylesheet" href="<?= base_url('assets/css/admin/profile-edit.css') ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header border-0 bg-transparent pb-0">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
          <div>
            <h4 class="fw-bold mb-1">
              <i class="bx bx-info-circle me-2 text-primary"></i>Kelola PPID
            </h4>
            <p class="text-muted mb-0">Kelola informasi Pejabat Pengelola Informasi dan Dokumentasi (PPID)</p>
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
          <div class="alert alert-danger alert-dismissible fade show" role="alert" aria-live="polite">
            <i class="bx bx-error-circle me-2"></i><?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('admin/ppid') ?>" class="pt-2">
          <?= csrf_field() ?>

          <div class="row g-3">
            <!-- Sidebar Navigation -->
            <div class="col-md-3">
              <div class="nav flex-column nav-pills" id="ppidTabs" role="tablist" aria-orientation="vertical">
                <button class="nav-link active" id="tab-about-tab" data-bs-toggle="pill" data-bs-target="#tab-about" type="button" role="tab" aria-controls="tab-about" aria-selected="true">
                  <i class="bx bx-info-circle me-2"></i>Tentang PPID
                </button>
                <button class="nav-link" id="tab-visimisi-tab" data-bs-toggle="pill" data-bs-target="#tab-visimisi" type="button" role="tab" aria-controls="tab-visimisi" aria-selected="false">
                  <i class="bx bx-bullseye me-2"></i>Visi & Misi
                </button>
                <button class="nav-link" id="tab-tugasfungsi-tab" data-bs-toggle="pill" data-bs-target="#tab-tugasfungsi" type="button" role="tab" aria-controls="tab-tugasfungsi" aria-selected="false">
                  <i class="bx bx-list-check me-2"></i>Tugas & Fungsi
                </button>
              </div>
              
              <?php if (!empty($ppid['updated_at'])): ?>
              <div class="mt-3 p-3 bg-light rounded small">
                <div class="text-muted">Terakhir diperbarui:</div>
                <div class="fw-semibold"><?= date('d M Y H:i', strtotime($ppid['updated_at'])) ?></div>
              </div>
              <?php endif; ?>
            </div>

            <!-- Tab Content -->
            <div class="col-md-9">
              <div class="tab-content">
                <!-- Tab Tentang PPID -->
                <div class="tab-pane fade show active" id="tab-about" role="tabpanel" aria-labelledby="tab-about-tab">
                  <div class="card border shadow-none">
                    <div class="card-header bg-light">
                      <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Tentang PPID</h5>
                    </div>
                    <div class="card-body">
                      <p class="text-muted small mb-3">
                        Informasi umum tentang PPID (Pejabat Pengelola Informasi dan Dokumentasi), termasuk sejarah, latar belakang, dan deskripsi singkat.
                      </p>
                      <div class="mb-0">
                        <label class="form-label" for="about">Deskripsi Tentang PPID</label>
                        <textarea 
                          class="form-control <?= $validation && $validation->hasError('about') ? 'is-invalid' : '' ?>" 
                          id="about" 
                          name="about" 
                          rows="12"
                          placeholder="Tuliskan informasi tentang PPID..."
                        ><?= old('about', $ppid['about'] ?? '') ?></textarea>
                        <?php if ($validation && $validation->hasError('about')): ?>
                          <div class="invalid-feedback"><?= esc($validation->getError('about')) ?></div>
                        <?php endif; ?>
                        <div class="form-text">Anda dapat menggunakan format HTML untuk konten ini.</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Tab Visi & Misi -->
                <div class="tab-pane fade" id="tab-visimisi" role="tabpanel" aria-labelledby="tab-visimisi-tab">
                  <div class="card border shadow-none">
                    <div class="card-header bg-light">
                      <h5 class="mb-0"><i class="bx bx-bullseye me-2"></i>Visi & Misi PPID</h5>
                    </div>
                    <div class="card-body">
                      <div class="row g-4">
                        <div class="col-12">
                          <label class="form-label" for="vision">Visi PPID</label>
                          <textarea 
                            class="form-control <?= $validation && $validation->hasError('vision') ? 'is-invalid' : '' ?>" 
                            id="vision" 
                            name="vision" 
                            rows="5"
                            placeholder="Tuliskan visi PPID..."
                          ><?= old('vision', $ppid['vision'] ?? '') ?></textarea>
                          <?php if ($validation && $validation->hasError('vision')): ?>
                            <div class="invalid-feedback"><?= esc($validation->getError('vision')) ?></div>
                          <?php endif; ?>
                          <div class="form-text">Visi merupakan gambaran masa depan yang ingin dicapai PPID.</div>
                        </div>
                        <div class="col-12">
                          <label class="form-label" for="mission">Misi PPID</label>
                          <textarea 
                            class="form-control <?= $validation && $validation->hasError('mission') ? 'is-invalid' : '' ?>" 
                            id="mission" 
                            name="mission" 
                            rows="8"
                            placeholder="Tuliskan misi PPID..."
                          ><?= old('mission', $ppid['mission'] ?? '') ?></textarea>
                          <?php if ($validation && $validation->hasError('mission')): ?>
                            <div class="invalid-feedback"><?= esc($validation->getError('mission')) ?></div>
                          <?php endif; ?>
                          <div class="form-text">
                            Misi merupakan langkah-langkah strategis untuk mencapai visi. 
                            Anda dapat menggunakan format HTML untuk membuat daftar.
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Tab Tugas & Fungsi -->
                <div class="tab-pane fade" id="tab-tugasfungsi" role="tabpanel" aria-labelledby="tab-tugasfungsi-tab">
                  <div class="card border shadow-none">
                    <div class="card-header bg-light">
                      <h5 class="mb-0"><i class="bx bx-list-check me-2"></i>Tugas & Fungsi PPID</h5>
                    </div>
                    <div class="card-body">
                      <p class="text-muted small mb-3">
                        Tugas pokok dan fungsi PPID berdasarkan peraturan yang berlaku.
                      </p>
                      <div class="mb-0">
                        <label class="form-label" for="tasks_functions">Tugas dan Fungsi PPID</label>
                        <textarea 
                          class="form-control <?= $validation && $validation->hasError('tasks_functions') ? 'is-invalid' : '' ?>" 
                          id="tasks_functions" 
                          name="tasks_functions" 
                          rows="12"
                          placeholder="Tuliskan tugas dan fungsi PPID..."
                        ><?= old('tasks_functions', $ppid['tasks_functions'] ?? '') ?></textarea>
                        <?php if ($validation && $validation->hasError('tasks_functions')): ?>
                          <div class="invalid-feedback"><?= esc($validation->getError('tasks_functions')) ?></div>
                        <?php endif; ?>
                        <div class="form-text">
                          Anda dapat menggunakan format HTML untuk konten ini. 
                          Gunakan &lt;h3&gt; untuk judul bagian dan &lt;ul&gt;/&lt;ol&gt; untuk daftar.
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                  <i class="bx bx-save me-1"></i> Simpan Perubahan
                </button>
                <a href="<?= site_url('ppid') ?>" class="btn btn-outline-secondary" target="_blank">
                  <i class="bx bx-external-link me-1"></i> Lihat Halaman PPID
                </a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Auto-select tab based on URL parameter
  const urlParams = new URLSearchParams(window.location.search);
  const tabParam = urlParams.get('tab');
  const tabMapping = {
    'about': 'tab-about-tab',
    'visimisi': 'tab-visimisi-tab',
    'tugasfungsi': 'tab-tugasfungsi-tab'
  };

  if (tabParam && tabMapping[tabParam]) {
    const tabButton = document.getElementById(tabMapping[tabParam]);
    if (tabButton) {
      // Remove active from current tab
      document.querySelectorAll('#ppidTabs .nav-link').forEach(function(link) {
        link.classList.remove('active');
        link.setAttribute('aria-selected', 'false');
      });
      document.querySelectorAll('.tab-content .tab-pane').forEach(function(pane) {
        pane.classList.remove('show', 'active');
      });
      
      // Activate target tab
      tabButton.classList.add('active');
      tabButton.setAttribute('aria-selected', 'true');
      const targetPane = document.querySelector(tabButton.getAttribute('data-bs-target'));
      if (targetPane) {
        targetPane.classList.add('show', 'active');
      }
    }
  }

  // Update URL when tab changes (without page reload)
  document.querySelectorAll('#ppidTabs .nav-link').forEach(function(tabBtn) {
    tabBtn.addEventListener('shown.bs.tab', function(e) {
      const tabId = e.target.id;
      const reverseMapping = {
        'tab-about-tab': 'about',
        'tab-visimisi-tab': 'visimisi',
        'tab-tugasfungsi-tab': 'tugasfungsi'
      };
      
      const tabSlug = reverseMapping[tabId];
      if (tabSlug) {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabSlug);
        window.history.replaceState({}, '', url);
      }
    });
  });
});
</script>
<?= $this->endSection() ?>
