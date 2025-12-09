<?php
  $validation = $validation ?? null;
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('pageStyles') ?>
  <link rel="stylesheet" href="<?= base_url('assets/css/admin/profile-edit.css') ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-4 settings-grid">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header border-0 bg-transparent pb-0">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
          <div>
            <h4 class="fw-bold mb-1">Kelola PPID</h4>
            <p class="text-muted mb-0">Perbarui informasi Pejabat Pengelola Informasi dan Dokumentasi (PPID).</p>
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
                  <div class="row g-3">
                    <div class="col-12 col-lg-10">
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
                      <?php else: ?>
                        <div class="form-text text-muted">Informasi umum tentang PPID termasuk sejarah, latar belakang, dan deskripsi singkat. Anda dapat menggunakan format HTML.</div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>

                <!-- Tab Visi & Misi -->
                <div class="tab-pane fade" id="tab-visimisi" role="tabpanel" aria-labelledby="tab-visimisi-tab">
                  <div class="row g-3">
                    <div class="col-12 col-lg-10">
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
                      <?php else: ?>
                        <div class="form-text text-muted">Visi merupakan gambaran masa depan yang ingin dicapai PPID.</div>
                      <?php endif; ?>
                    </div>
                    <div class="col-12 col-lg-10">
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
                      <?php else: ?>
                        <div class="form-text text-muted">Misi merupakan langkah-langkah strategis untuk mencapai visi. Anda dapat menggunakan format HTML untuk membuat daftar.</div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>

                <!-- Tab Tugas & Fungsi -->
                <div class="tab-pane fade" id="tab-tugasfungsi" role="tabpanel" aria-labelledby="tab-tugasfungsi-tab">
                  <div class="row g-3">
                    <div class="col-12 col-lg-10">
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
                      <?php else: ?>
                        <div class="form-text text-muted">Tugas pokok dan fungsi PPID berdasarkan peraturan yang berlaku. Gunakan &lt;h3&gt; untuk judul bagian dan &lt;ul&gt;/&lt;ol&gt; untuk daftar.</div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Submit Button - Matching Profile style -->
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">
                <p class="text-muted small mb-0">
                  <a href="<?= site_url('ppid') ?>" target="_blank" class="text-decoration-none">
                    <i class="bx bx-external-link me-1"></i>Lihat Halaman PPID
                  </a>
                </p>
                <div class="d-flex flex-wrap gap-2">
                  <button type="reset" class="btn btn-outline-secondary"><i class="bx bx-reset me-1"></i> Atur Ulang</button>
                  <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
                </div>
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
<script src="<?= base_url('assets/vendor/tinymce/js/tinymce/tinymce.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // --- TinyMCE Initialization for Full-Text Editors ---
  const tinymceConfig = {
    branding: false,
    promotion: false,
    height: 400,
    menubar: 'file edit view insert format tools table help',
    toolbar_sticky: true,
    toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table link | removeformat | fullscreen preview code',
    plugins: 'preview searchreplace autolink autosave save code visualblocks visualchars fullscreen link table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount help',
    autosave_interval: '30s',
    autosave_restore_when_empty: true,
    autosave_retention: '2m',
    content_style: 'body { font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif; font-size: 16px; line-height: 1.7; }',
    table_default_attributes: { class: 'table table-striped table-sm' },
    language: 'id',
    language_url: '<?= base_url('assets/vendor/tinymce/langs/id.js') ?>',
  };

  // Initialize for all relevant textareas
  tinymce.init({
    ...tinymceConfig,
    selector: '#about, #vision, #mission, #tasks_functions'
  });

  // Handle Tab Switching & Editor Visibility
  // Bootstrap tabs hide content with display:none, which can cause TinyMCE 
  // to render with 0 width/height if initialized while hidden.
  // We force a refresh when a tab is shown.
  const tabEls = document.querySelectorAll('button[data-bs-toggle="pill"]');
  tabEls.forEach(tabBtn => {
    tabBtn.addEventListener('shown.bs.tab', function (event) {
      // Find textareas inside the target pane
      const targetId = event.target.getAttribute('data-bs-target');
      const targetPane = document.querySelector(targetId);
      if(targetPane) {
          const textareas = targetPane.querySelectorAll('textarea');
          textareas.forEach(ta => {
              const editor = tinymce.get(ta.id);
              if(editor) {
                  editor.show(); // Ensure visible
                  editor.nodeChanged(); // Refresh UI
              }
          });
      }
    });
  });

  // Form Submission Handler
  const ppidForm = document.querySelector('form[action*="admin/ppid"]');
  const submitBtn = ppidForm ? ppidForm.querySelector('button[type="submit"]') : null;

  if (ppidForm) {
      ppidForm.addEventListener('submit', function(e) {
          // Trigger save for all editors
          tinymce.triggerSave();
          
          if (submitBtn) {
              submitBtn.disabled = true;
              submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Menyimpan...';
          }
      });
  }

  // --- Existing Tab Logic ---
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
