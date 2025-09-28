<?php use CodeIgniter\I18n\Time; ?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
  $profile        = $profile ?? [];
  $profileName    = trim((string) ($profile['name'] ?? 'Dinas Pelayanan Publik Kota Harmoni'));
  $defaultIntro   = 'Menyediakan informasi terkini mengenai program kerja, layanan publik, data dokumen penting, serta berita terbaru dari Dinas Pelayanan Publik Kota Harmoni.';
  $profileIntro   = trim((string) ($profile['description'] ?? ''));
  $heroIntro      = $profileIntro !== '' ? $profileIntro : $defaultIntro;
  $address        = trim((string) ($profile['address'] ?? ''));
  $phone          = trim((string) ($profile['phone'] ?? ''));
  $email          = trim((string) ($profile['email'] ?? ''));
  $newsItems      = $news ?? [];
  $latestNews     = $newsItems[0] ?? null;
  $secondaryNews  = $latestNews ? array_slice($newsItems, 1, 3) : array_slice($newsItems, 0, 3);
  $serviceItems   = $services ?? [];
  $galleryItems   = $galleries ?? [];
?>
<section class="hero-section hero-news" id="beranda">
  <div class="container public-container">
    <?php if ($latestNews): ?>
      <div class="hero-news-card rounded-4 shadow-sm overflow-hidden">
        <div class="row g-0 align-items-stretch">
          <div class="col-lg-7 p-4 p-lg-5 d-flex flex-column">
            <span class="hero-badge text-uppercase mb-2">Berita Terbaru</span>
            <h1 class="display-6 fw-bold mb-2"><?= esc($latestNews['title']) ?></h1>
            <?php $excerpt = mb_strimwidth(strip_tags($latestNews['content'] ?? ''), 0, 190, '...'); ?>
            <p class="lead text-muted mb-4"><?= esc($excerpt) ?></p>
            <div class="mt-auto pt-2">
              <a class="btn btn-public-primary px-4" href="<?= site_url('berita/' . esc($latestNews['slug'], 'url')) ?>">Baca Selengkapnya</a>
            </div>
          </div>
          <div class="col-lg-5">
            <?php if (! empty($latestNews['thumbnail'])): ?>
              <div class="ratio ratio-16x9 h-100">
                <img src="<?= esc(base_url($latestNews['thumbnail'])) ?>" alt="<?= esc($latestNews['title']) ?>" class="w-100 h-100 object-fit-cover">
              </div>
            <?php else: ?>
              <div class="ratio ratio-16x9 h-100 bg-light d-flex align-items-center justify-content-center">
                <div class="text-muted">Thumbnail belum tersedia</div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="row align-items-center gy-5">
        <div class="col-lg-6">
          <span class="hero-badge text-uppercase">Pelayanan Prima - Transparan - Responsif</span>
          <h1 class="display-5 fw-bold mt-3 mb-3"><?= esc($profileName) ?></h1>
          <p class="lead text-muted mb-4"><?= esc($heroIntro) ?></p>
          <div class="d-flex gap-3 flex-wrap hero-actions-stack">
            <a class="btn btn-public-primary btn-lg px-4" href="<?= site_url('layanan') ?>">Lihat Layanan Unggulan</a>
            <a class="btn btn-public-ghost btn-lg px-4" href="<?= site_url('/') ?>#kontak">Hubungi Kami</a>
          </div>
          <div class="row row-cols-1 row-cols-sm-3 g-3 mt-4">
            <div class="col"><div class="stat-card p-4 h-100"><p class="display-6 fw-bold mb-1">38</p><p class="mb-0 text-muted small">Layanan publik yang dapat diakses masyarakat.</p></div></div>
            <div class="col"><div class="stat-card p-4 h-100"><p class="display-6 fw-bold mb-1">12k+</p><p class="mb-0 text-muted small">Warga terlayani dengan standar kepuasan tinggi.</p></div></div>
            <div class="col"><div class="stat-card p-4 h-100"><p class="display-6 fw-bold mb-1">24/7</p><p class="mb-0 text-muted small">Pusat kontak dan kanal pengaduan responsif.</p></div></div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="ratio ratio-4x3 rounded-4 overflow-hidden shadow">
            <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=80" class="w-100 h-100 object-fit-cover" alt="Petugas layanan publik" loading="lazy" />
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="public-section" id="layanan">
  <div class="container public-container">
    <div class="public-section-header text-center text-md-start mb-4">
      <h2 class="fw-semibold mb-1">Layanan Unggulan</h2>
      <span class="text-muted">Akses layanan prioritas dengan syarat dan ketentuan ringkas.</span>
    </div>
    <?php if ($serviceItems): ?>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 services-grid">
        <?php foreach ($serviceItems as $service): ?>
          <?php $initial = mb_strtoupper(mb_substr($service['title'] ?? '', 0, 1, 'UTF-8'), 'UTF-8'); ?>
          <div class="col">
            <article class="service-pill h-100 p-4">
              <div class="service-pill-icon"><?= esc($initial !== '' ? $initial : 'L') ?></div>
              <h5 class="fw-semibold mb-2"><a class="text-decoration-none text-dark" href="<?= site_url('layanan') ?>#<?= esc($service['slug'] ?? '', 'url') ?>"><?= esc($service['title'] ?? 'Layanan Publik') ?></a></h5>
              <?php if (! empty($service['description'])): ?>
                <?php $summary = mb_strimwidth(strip_tags($service['description']), 0, 110, '...'); ?>
                <p class="text-muted mb-0 small"><?= esc($summary) ?></p>
              <?php else: ?>
                <p class="text-muted mb-0 small">Deskripsi layanan segera tersedia.</p>
              <?php endif; ?>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <p class="text-muted mb-0">Data layanan sedang disiapkan. Silakan cek kembali beberapa saat lagi.</p>
      </div>
    <?php endif; ?>
    <div class="text-center mt-4">
      <a class="btn btn-public-ghost px-4" href="<?= site_url('layanan') ?>">Lihat Selengkapnya</a>
    </div>
  </div>
</section>

<section class="public-section bg-white" id="berita">
  <div class="container public-container">
    <div class="public-section-header text-center text-md-start mb-4">
      <h2 class="fw-semibold mb-1">Berita</h2>
      <span class="text-muted">Informasi terbaru dari OPD.</span>
    </div>
    <?php if ($latestNews): ?>
      <div class="row g-4 news-layout">
        <div class="col-lg-7">
          <article class="news-main-card news-card h-100 rounded-4 shadow-sm bg-white overflow-hidden">
            <?php if (! empty($latestNews['thumbnail'])): ?>
              <div class="ratio ratio-16x9">
                <img src="<?= esc(base_url($latestNews['thumbnail'])) ?>" alt="<?= esc($latestNews['title']) ?>" class="w-100 h-100 object-fit-cover" loading="lazy">
              </div>
            <?php endif; ?>
            <div class="p-4 p-lg-5 d-flex flex-column h-100">
              <?php if (! empty($latestNews['published_at'])): ?>
                <?php $published = Time::parse($latestNews['published_at']); ?>
                <span class="badge bg-light text-primary mb-3"><?= esc($published->toLocalizedString('d MMM yyyy')) ?></span>
              <?php endif; ?>
              <h3 class="fw-semibold mb-3"><a class="text-decoration-none text-dark" href="<?= site_url('berita/' . esc($latestNews['slug'], 'url')) ?>"><?= esc($latestNews['title']) ?></a></h3>
              <?php if (! empty($latestNews['content'])): ?>
                <?php $lead = mb_strimwidth(strip_tags($latestNews['content']), 0, 220, '...'); ?>
                <p class="text-muted mb-4 flex-grow-1"><?= esc($lead) ?></p>
              <?php endif; ?>
              <a class="btn btn-public-primary px-4 align-self-start" href="<?= site_url('berita/' . esc($latestNews['slug'], 'url')) ?>">Baca Selengkapnya</a>
            </div>
          </article>
        </div>
        <div class="col-lg-5">
          <div class="d-grid gap-3 news-side">
            <?php foreach ($secondaryNews as $article): ?>
              <article class="news-side-card news-card rounded-4 shadow-sm bg-white p-3 p-lg-4">
                <div class="d-flex gap-3">
                  <?php if (! empty($article['thumbnail'])): ?>
                    <div class="news-side-thumb flex-shrink-0">
                      <img src="<?= esc(base_url($article['thumbnail'])) ?>" alt="<?= esc($article['title']) ?>" loading="lazy" class="w-100 h-100 object-fit-cover">
                    </div>
                  <?php endif; ?>
                  <div class="flex-grow-1">
                    <?php if (! empty($article['published_at'])): ?>
                      <?php $published = Time::parse($article['published_at']); ?>
                      <span class="badge bg-light text-primary mb-2"><?= esc($published->toLocalizedString('d MMM yyyy')) ?></span>
                    <?php endif; ?>
                    <h5 class="fw-semibold mb-2"><a class="text-decoration-none text-dark" href="<?= site_url('berita/' . esc($article['slug'], 'url')) ?>"><?= esc($article['title']) ?></a></h5>
                    <?php if (! empty($article['content'])): ?>
                      <?php $excerpt = mb_strimwidth(strip_tags($article['content']), 0, 120, '...'); ?>
                      <p class="text-muted mb-0 small"><?= esc($excerpt) ?></p>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
            <?php if (! $secondaryNews): ?>
              <div class="news-card placeholder-card d-flex align-items-center justify-content-center flex-column text-center p-4">
                <p class="text-muted mb-0">Belum ada berita lainnya.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <p class="text-muted mb-0">Belum ada berita yang dipublikasikan.</p>
      </div>
    <?php endif; ?>
    <div class="text-center mt-4">
      <a class="btn btn-public-ghost px-4" href="<?= site_url('berita') ?>">Lihat Selengkapnya</a>
    </div>
  </div>
</section>

<section class="public-section" id="galeri">
  <div class="container public-container">
    <div class="public-section-header text-center text-md-start mb-4">
      <h2 class="fw-semibold mb-1">Galeri Kegiatan</h2>
      <span class="text-muted">Potret pelayanan dan aktivitas kami untuk masyarakat.</span>
    </div>
    <?php if ($galleryItems): ?>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 gallery-grid">
        <?php foreach ($galleryItems as $gallery): ?>
          <div class="col">
            <article class="gallery-tile h-100 rounded-4 overflow-hidden">
              <div class="gallery-tile-media">
                <div class="ratio ratio-4x3">
                  <img src="<?= esc(base_url($gallery['image_path'])) ?>" alt="<?= esc($gallery['title']) ?>" class="w-100 h-100 object-fit-cover" loading="lazy">
                </div>
              </div>
              <div class="gallery-tile-body p-3">
                <h5 class="fw-semibold mb-1"><?= esc($gallery['title']) ?></h5>
                <?php if (! empty($gallery['description'])): ?>
                  <?php $caption = mb_strimwidth(strip_tags($gallery['description']), 0, 100, '...'); ?>
                  <p class="text-muted mb-0 small"><?= esc($caption) ?></p>
                <?php else: ?>
                  <p class="text-muted mb-0 small">Dokumentasi singkat akan ditambahkan.</p>
                <?php endif; ?>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <p class="text-muted mb-0">Galeri akan diunggah setelah dokumentasi tersedia.</p>
      </div>
    <?php endif; ?>
    <div class="text-center mt-4">
      <a class="btn btn-public-ghost px-4" href="<?= site_url('galeri') ?>">Lihat Selengkapnya</a>
    </div>
  </div>
</section>

<section class="public-section bg-white" id="dokumen">
  <div class="container public-container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
      <div class="public-section-header"><h2 class="fw-semibold mb-1">Dokumen Publik</h2><span class="text-muted">Unduh SOP, laporan kinerja, dan regulasi terbaru.</span></div>
      <a class="btn btn-public-ghost px-4 mt-2 mt-md-0" href="<?= site_url('dokumen') ?>">Semua Dokumen</a>
    </div>
    <div class="documents-wrap">
      <?php if ($documents): ?>
        <div class="table-responsive">
          <table class="table align-middle document-table mb-0">
            <thead>
              <tr>
                <th scope="col">Judul</th>
                <th scope="col">Kategori</th>
                <th scope="col">Tahun</th>
                <th scope="col" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documents as $document): ?>
                <tr>
                  <td><?= esc($document['title']) ?></td>
                  <td><?= esc($document['category'] ?? '-') ?></td>
                  <td><?= esc($document['year'] ?? '-') ?></td>
                  <td class="text-center">
                    <?php if (! empty($document['file_path'])): ?>
                      <a class="btn btn-sm btn-public-primary" target="_blank" rel="noopener" href="<?= esc(base_url($document['file_path'])) ?>">Unduh</a>
                    <?php else: ?>
                      <span class="text-muted">Tidak tersedia</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-5">
          <p class="text-muted mb-0">Dokumen publik akan tersedia setelah proses unggah selesai.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="public-section contact-summary" id="kontak">
  <div class="container public-container">
    <div class="public-section-header mb-3"><h2 class="fw-semibold mb-1">Hubungi Kami</h2><span class="text-muted">Kunjungi kantor pelayanan atau gunakan kanal digital berikut.</span></div>
    <?php
      $contactStatus  = session()->getFlashdata('contact_status');
      $contactMessage = session()->getFlashdata('contact_message');
      $contactErrors  = session()->getFlashdata('contact_errors') ?? [];
    ?>
    <div class="row g-4 align-items-stretch">
      <div class="col-lg-7">
        <article class="contact-card p-4 p-lg-5 h-100">
          <h3 class="fw-semibold mb-3">Tinggalkan Pesan</h3>
          <p class="text-muted mb-4">Isi formulir berikut agar tim kami dapat menindaklanjuti pertanyaan, informasi layanan, ataupun aduan Anda.</p>
          <?php if ($contactStatus === 'success'): ?>
            <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
              <i class="bx bx-check-circle fs-4"></i>
              <div><?= esc($contactMessage ?? 'Pesan Anda berhasil dikirim. Terima kasih!') ?></div>
            </div>
          <?php elseif ($contactStatus === 'error'): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
              <i class="bx bx-error-circle fs-4"></i>
              <div><?= esc($contactMessage ?? 'Terjadi kesalahan. Silakan coba kembali beberapa saat lagi.') ?></div>
            </div>
          <?php endif; ?>
          <form id="contactForm" class="contact-form needs-validation" action="<?= site_url('kontak/kirim') ?>" method="post" novalidate data-contact-status="<?= esc($contactStatus ?? '', 'attr') ?>">
            <?= csrf_field() ?>
            <div class="contact-form-honeypot" aria-hidden="true">
              <label for="contact_website" class="form-label">Website</label>
              <input type="text" id="contact_website" name="contact_website" tabindex="-1" autocomplete="off">
            </div>
            <div class="row g-3">
              <?php $nameError = $contactErrors['name'] ?? null; ?>
              <div class="col-12 col-md-6">
                <label for="contact_name" class="form-label">Nama Lengkap<span class="text-danger">*</span></label>
                <input type="text" id="contact_name" name="name" class="form-control<?= $nameError ? ' is-invalid' : '' ?>" value="<?= old('name') ?>" placeholder="Nama pemohon" required maxlength="100" autocomplete="name">
                <div class="invalid-feedback"><?= esc($nameError ?? 'Nama wajib diisi.') ?></div>
              </div>
              <?php $emailError = $contactErrors['email'] ?? null; ?>
              <div class="col-12 col-md-6">
                <label for="contact_email" class="form-label">Email<span class="text-danger">*</span></label>
                <input type="email" id="contact_email" name="email" class="form-control<?= $emailError ? ' is-invalid' : '' ?>" value="<?= old('email') ?>" placeholder="nama@email.com" required maxlength="100" autocomplete="email">
                <div class="invalid-feedback"><?= esc($emailError ?? 'Gunakan alamat email aktif yang valid.') ?></div>
              </div>
              <?php $subjectError = $contactErrors['subject'] ?? null; ?>
              <div class="col-12">
                <label for="contact_subject" class="form-label">Subjek Pesan</label>
                <input type="text" id="contact_subject" name="subject" class="form-control<?= $subjectError ? ' is-invalid' : '' ?>" value="<?= old('subject') ?>" placeholder="Contoh: Permintaan informasi publik" maxlength="150" autocomplete="off">
                <?php if ($subjectError): ?>
                  <div class="invalid-feedback"><?= esc($subjectError) ?></div>
                <?php else: ?>
                  <small class="form-text text-muted">Opsional, namun membantu kami mengkategorikan pesan Anda.</small>
                <?php endif; ?>
              </div>
              <?php $messageError = $contactErrors['message'] ?? null; ?>
              <div class="col-12">
                <label for="contact_message" class="form-label">Pesan<span class="text-danger">*</span></label>
                <textarea id="contact_message" name="message" class="form-control<?= $messageError ? ' is-invalid' : '' ?>" rows="5" placeholder="Tuliskan detail pertanyaan atau kebutuhan Anda" required maxlength="2000" autocomplete="off"><?= old('message') ?></textarea>
                <div class="invalid-feedback"><?= esc($messageError ?? 'Pesan wajib diisi.') ?></div>
              </div>
              <div class="col-12">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                  <button type="submit" class="btn btn-public-primary px-4" data-contact-submit>
                    <span class="contact-submit-label">Kirim Pesan</span>
                    <span class="contact-submit-spinner spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  </button>
                  <p class="text-muted small mb-0">Kami merespons pesan pada hari dan jam kerja. Pastikan email dan nomor telepon valid untuk tindak lanjut.</p>
                </div>
              </div>
            </div>
          </form>
        </article>
      </div>
      <div class="col-lg-5">
        <div class="d-flex flex-column gap-4 h-100">
          <article class="contact-card p-4 p-lg-5 h-100">
            <h3 class="fw-semibold mb-3">Alamat &amp; Informasi</h3>
            <p class="text-muted mb-4">Silakan datang langsung atau hubungi kami untuk informasi layanan dan aduan masyarakat.</p>
            <dl class="contact-info mb-0">
              <dt>Alamat Kantor</dt>
              <dd><?= $address !== '' ? nl2br(esc($address)) : 'Alamat belum tersedia.' ?></dd>
              <dt>Telepon</dt>
              <dd><?= $phone !== '' ? esc($phone) : 'Nomor telepon belum tersedia.' ?></dd>
              <dt>Email</dt>
              <dd>
                <?php if ($email !== ''): ?>
                  <a class="text-decoration-none" href="mailto:<?= esc($email) ?>"><?= esc($email) ?></a>
                <?php else: ?>
                  <span class="text-muted">Email belum tersedia.</span>
                <?php endif; ?>
              </dd>
            </dl>
          </article>
          <article class="contact-card p-4 p-lg-5 h-100">
            <h3 class="fw-semibold mb-3">Kanal Sosial</h3>
            <?php
              $socialLinks = [];
              if ($phone !== '') {
                  $socialLinks[] = [
                      'label' => 'Telepon / WhatsApp',
                      'value' => $phone,
                      'href'  => 'tel:' . preg_replace('/[^0-9+]/', '', $phone),
                  ];
              }
              if ($email !== '') {
                  $socialLinks[] = [
                      'label' => 'Email',
                      'value' => $email,
                      'href'  => 'mailto:' . $email,
                  ];
              }
              if (! $socialLinks) {
                  $socialLinks[] = [
                      'label' => 'Media Sosial',
                      'value' => 'Segera hadir',
                      'href'  => '#',
                  ];
              }
            ?>
            <ul class="list-unstyled contact-social mb-0">
              <?php foreach ($socialLinks as $item): ?>
                <?php $isExternal = $item['href'] !== '#'; ?>
                <li>
                  <a class="text-decoration-none" href="<?= esc($item['href']) ?>"<?php if ($isExternal): ?> target="_blank" rel="noopener"<?php endif; ?>>
                    <span class="contact-social-label"><?= esc($item['label']) ?></span>
                    <span class="contact-social-value"><?= esc($item['value']) ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </article>
        </div>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>




<?= $this->section('pageScripts') ?>
<script>
(() => {
  const form = document.getElementById('contactForm');
  if (!form) {
    return;
  }

  const submitButton = form.querySelector('[data-contact-submit]');
  const honeypot = form.querySelector('[name="contact_website"]');
  const formControls = Array.from(form.querySelectorAll('input, textarea'));
  const status = form.dataset.contactStatus || '';

  const setSubmitting = (isSubmitting) => {
    if (submitButton) {
      submitButton.disabled = isSubmitting;
    }
    form.classList.toggle('is-submitting', !!isSubmitting);
  };

  setSubmitting(false);

  if (form.querySelector('.is-invalid')) {
    form.classList.add('was-validated');
  }

  if (status === 'success') {
    form.reset();
    form.classList.remove('was-validated');
  }

  form.addEventListener('submit', (event) => {
    if (honeypot && honeypot.value.trim() !== '') {
      event.preventDefault();
      return false;
    }

    form.classList.add('was-validated');

    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
      const firstInvalid = form.querySelector('.form-control:invalid, textarea:invalid');
      if (firstInvalid && typeof firstInvalid.focus === 'function') {
        firstInvalid.focus({ preventScroll: false });
      }
      return false;
    }

    setSubmitting(true);
    return true;
  });

  formControls.forEach((field) => {
    field.addEventListener('input', () => {
      if (!form.classList.contains('was-validated')) {
        return;
      }
      if (field.checkValidity()) {
        field.classList.remove('is-invalid');
      } else {
        field.classList.add('is-invalid');
      }
    });

    field.addEventListener('invalid', () => {
      field.classList.add('is-invalid');
    });
  });

  window.addEventListener('pageshow', (evt) => {
    if (evt.persisted) {
      setSubmitting(false);
    }
  });
})();
</script>
<?= $this->endSection() ?>

