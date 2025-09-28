<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
    $profile = is_array($profile ?? null) ? $profile : [];
    $address = trim((string) ($profile['address'] ?? ''));
    $phone   = trim((string) ($profile['phone'] ?? ''));
    $email   = trim((string) ($profile['email'] ?? ''));
    $session = session();

    $successMessage = (string) ($session->getFlashdata('contact_success') ?? '');
    $errorMessage   = (string) ($session->getFlashdata('contact_error') ?? '');
    $contactErrors  = (array) ($session->getFlashdata('contact_errors') ?? []);
    $contactOld     = (array) ($session->getFlashdata('contact_old') ?? []);

    $formDefaults = [
        'full_name' => '',
        'email'     => '',
        'phone'     => '',
        'subject'   => '',
        'message'   => '',
    ];

    foreach ($formDefaults as $field => $defaultValue) {
        $candidate = $contactOld[$field] ?? old($field);
        $formDefaults[$field] = is_string($candidate) ? $candidate : $defaultValue;
    }

    $quickLinks = [];

    if ($phone !== '') {
        $quickLinks[] = [
            'label' => 'Hubungi via Telepon',
            'value' => $phone,
            'href'  => 'tel:' . preg_replace('/[^0-9+]/', '', $phone),
        ];
    }

    if ($email !== '') {
        $quickLinks[] = [
            'label' => 'Kirim Email',
            'value' => $email,
            'href'  => 'mailto:' . $email,
        ];
    }

    if (! $quickLinks) {
        $quickLinks[] = [
            'label' => 'Layanan Pengaduan',
            'value' => 'Segera hadir',
            'href'  => '#',
        ];
    }

    $infoChips = array_values(array_filter([
        $address !== '' ? 'Layanan tatap muka tersedia pada jam kerja.' : null,
        $phone !== '' ? 'Telepon aktif pukul 08.00-16.00 WIB.' : null,
        $email !== '' ? 'Balasan email dikirim maksimal 1x24 jam.' : null,
    ]));
?>
<section class="contact-hero section-surface">
  <div class="container public-container contact-hero__container">
    <div class="contact-hero__copy">
      <span class="contact-eyebrow">Butuh bantuan?</span>
      <h1>Kontak &amp; Aduan Masyarakat</h1>
      <p class="contact-lead">Kami siap membantu kebutuhan informasi, pengaduan, serta masukan Anda agar pelayanan publik semakin baik.</p>
      <?php if (! empty($infoChips)): ?>
      <ul class="contact-hero__chips">
        <?php foreach ($infoChips as $chip): ?>
        <li><?= esc($chip) ?></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
    <div class="contact-hero__panel">
      <div class="contact-hero__card surface-card">
        <h2 class="contact-hero__title">Saluran Prioritas</h2>
        <p class="text-muted mb-3">Gunakan kanal berikut untuk memperoleh respons tercepat dari tim kami.</p>
        <ul class="contact-quick-links">
          <?php foreach ($quickLinks as $link): ?>
          <li>
            <a class="surface-link" href="<?= esc($link['href']) ?>"<?php if ($link['href'] !== '#'): ?> target="_blank" rel="noopener"<?php endif; ?>>
              <span><?= esc($link['label']) ?></span>
              <span class="contact-link-value"><?= esc($link['value']) ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
        <p class="contact-hero__hint text-muted">Tidak menemukan kanal yang sesuai? Tinggalkan pesan melalui formulir di bawah.</p>
      </div>
    </div>
  </div>
</section>

<section class="contact-main">
  <div class="container public-container contact-main__container">
    <div class="contact-grid">
      <article class="contact-card contact-card--form surface-card">
        <header class="contact-card__header">
          <h2>Form Kontak &amp; Aspirasi</h2>
          <p>Isi formulir ini untuk mengirimkan pertanyaan, saran, atau pengaduan. Pastikan data kontak aktif agar kami dapat menindaklanjuti.</p>
        </header>

        <?php if ($successMessage !== ''): ?>
          <div class="alert alert-success contact-alert" role="status">
            <?= esc($successMessage) ?>
          </div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
          <div class="alert alert-danger contact-alert" role="alert">
            <?= esc($errorMessage) ?>
          </div>
        <?php endif; ?>

        <form id="contactForm" class="contact-form" method="post" action="<?= site_url('kontak') ?>" novalidate data-contact-form>
          <?= csrf_field() ?>
          <div class="contact-form-feedback" data-contact-feedback hidden></div>
          <div class="row g-3">
            <div class="col-md-6">
              <?php
                $fieldError = $contactErrors['full_name'] ?? '';
                $describedBy = $fieldError !== '' ? 'contactFullNameError' : 'contactFullNameHelp';
              ?>
              <label class="form-label" for="contactFullName">Nama Lengkap <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control<?= $fieldError !== '' ? ' is-invalid' : '' ?>"
                id="contactFullName"
                name="full_name"
                value="<?= esc($formDefaults['full_name']) ?>"
                maxlength="150"
                autocomplete="name"
                required
                aria-describedby="<?= $describedBy ?>"
              >
              <?php if ($fieldError !== ''): ?>
                <div id="contactFullNameError" class="invalid-feedback"><?= esc($fieldError) ?></div>
              <?php else: ?>
                <div id="contactFullNameHelp" class="form-text">Tuliskan nama sesuai identitas agar mudah diverifikasi.</div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <?php
                $fieldError = $contactErrors['email'] ?? '';
                $describedBy = $fieldError !== '' ? 'contactEmailError' : 'contactEmailHelp';
              ?>
              <label class="form-label" for="contactEmail">Email Aktif <span class="text-danger">*</span></label>
              <input
                type="email"
                class="form-control<?= $fieldError !== '' ? ' is-invalid' : '' ?>"
                id="contactEmail"
                name="email"
                value="<?= esc($formDefaults['email']) ?>"
                maxlength="150"
                autocomplete="email"
                required
                aria-describedby="<?= $describedBy ?>"
              >
              <?php if ($fieldError !== ''): ?>
                <div id="contactEmailError" class="invalid-feedback"><?= esc($fieldError) ?></div>
              <?php else: ?>
                <div id="contactEmailHelp" class="form-text">Kami akan mengirimkan notifikasi tindak lanjut ke email ini.</div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <?php
                $fieldError = $contactErrors['phone'] ?? '';
                $describedBy = $fieldError !== '' ? 'contactPhoneError' : 'contactPhoneHelp';
              ?>
              <label class="form-label" for="contactPhone">Nomor Telepon</label>
              <input
                type="tel"
                class="form-control<?= $fieldError !== '' ? ' is-invalid' : '' ?>"
                id="contactPhone"
                name="phone"
                value="<?= esc($formDefaults['phone']) ?>"
                maxlength="30"
                autocomplete="tel"
                pattern="^[0-9+().\\s-]{6,}$"
                aria-describedby="<?= $describedBy ?>"
              >
              <?php if ($fieldError !== ''): ?>
                <div id="contactPhoneError" class="invalid-feedback"><?= esc($fieldError) ?></div>
              <?php else: ?>
                <div id="contactPhoneHelp" class="form-text">Opsional namun membantu jika perlu dihubungi segera.</div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <?php
                $fieldError = $contactErrors['subject'] ?? '';
                $describedBy = $fieldError !== '' ? 'contactSubjectError' : 'contactSubjectHelp';
              ?>
              <label class="form-label" for="contactSubject">Subjek Pesan <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control<?= $fieldError !== '' ? ' is-invalid' : '' ?>"
                id="contactSubject"
                name="subject"
                value="<?= esc($formDefaults['subject']) ?>"
                maxlength="120"
                required
                aria-describedby="<?= $describedBy ?>"
              >
              <?php if ($fieldError !== ''): ?>
                <div id="contactSubjectError" class="invalid-feedback"><?= esc($fieldError) ?></div>
              <?php else: ?>
                <div id="contactSubjectHelp" class="form-text">Gunakan kalimat singkat yang mewakili topik utama pesan.</div>
              <?php endif; ?>
            </div>

            <div class="col-12">
              <?php
                $fieldError = $contactErrors['message'] ?? '';
                $describedBy = $fieldError !== '' ? 'contactMessageError' : 'contactMessageHelp';
              ?>
              <label class="form-label" for="contactMessage">Pesan <span class="text-danger">*</span></label>
              <textarea
                class="form-control<?= $fieldError !== '' ? ' is-invalid' : '' ?>"
                id="contactMessage"
                name="message"
                rows="6"
                maxlength="2000"
                required
                aria-describedby="<?= $describedBy ?>"><?= esc($formDefaults['message']) ?></textarea>
              <?php if ($fieldError !== ''): ?>
                <div id="contactMessageError" class="invalid-feedback"><?= esc($fieldError) ?></div>
              <?php else: ?>
                <div id="contactMessageHelp" class="form-text">Sertakan detail kronologi, tanggal kejadian, atau unit layanan terkait.</div>
              <?php endif; ?>
            </div>
          </div>

          <div class="contact-honeypot" aria-hidden="true">
            <label for="contactWebsite">Website</label>
            <input type="text" id="contactWebsite" name="website" tabindex="-1" autocomplete="off">
          </div>

          <div class="contact-captcha-placeholder" id="contactCaptchaPlaceholder" data-captcha-placeholder>
            <p class="contact-captcha-text text-muted mb-0">Captcha opsional akan muncul di sini ketika diaktifkan.</p>
          </div>

          <div class="contact-form__foot">
            <button type="submit" class="btn btn-public-primary btn-lg">Kirim Pesan</button>
            <p class="contact-form__meta text-muted">Dengan mengirimkan formulir ini Anda menyetujui kebijakan privasi dan tata tertib layanan pengaduan.</p>
          </div>
        </form>
      </article>

      <aside class="contact-card contact-card--info surface-card">
        <header class="contact-card__header">
          <h2>Informasi Kantor</h2>
          <p>Datang langsung ke kantor kami atau gunakan detail berikut untuk tindak lanjut mandiri.</p>
        </header>
        <dl class="contact-info-list">
          <div>
            <dt>Alamat Kantor</dt>
            <dd><?= $address !== '' ? nl2br(esc($address)) : '<span class="text-muted">Alamat belum tersedia.</span>' ?></dd>
          </div>
          <div>
            <dt>Telepon</dt>
            <dd><?= $phone !== '' ? esc($phone) : '<span class="text-muted">Nomor telepon belum tersedia.</span>' ?></dd>
          </div>
          <div>
            <dt>Email</dt>
            <dd>
              <?php if ($email !== ''): ?>
                <a class="surface-link" href="mailto:<?= esc($email) ?>"><?= esc($email) ?></a>
              <?php else: ?>
                <span class="text-muted">Email belum tersedia.</span>
              <?php endif; ?>
            </dd>
          </div>
          <div>
            <dt>Jam Pelayanan</dt>
            <dd>
              Senin s.d. Kamis 08.00-16.00 WIB<br>
              Jumat 08.00-15.00 WIB<br>
              Layanan daring 24 jam
            </dd>
          </div>
        </dl>

        <section class="contact-map" aria-labelledby="contactMapTitle">
          <h3 id="contactMapTitle">Lokasi Kantor</h3>
          <p class="text-muted">Sematkan peta digital instansi Anda di sini. Tambahkan iframe Google Maps melalui konfigurasi apabila tersedia.</p>
          <div class="contact-map__frame" data-map-placeholder>
            <span>Pra-tayang peta akan tampil di sini.</span>
          </div>
        </section>
      </aside>
    </div>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<?= $this->endSection() ?>
<?= $this->section('pageScripts') ?>
<script>
(() => {
  const form = document.querySelector('[data-contact-form]');
  if (!form) {
    return;
  }

  const feedbackEl = form.querySelector('[data-contact-feedback]');
  const honeypotField = form.querySelector('input[name="website"]');
  const fields = Array.from(form.querySelectorAll('input[name], textarea[name]'));

  const normalize = (value) => (typeof value === 'string' ? value.trim() : '');

  const validators = {
    full_name(value) {
      const normalized = normalize(value);
      if (normalized === '') {
        return 'Nama lengkap wajib diisi.';
      }
      if (normalized.length < 3) {
        return 'Nama lengkap minimal 3 karakter.';
      }
      return '';
    },
    email(value) {
      const normalized = normalize(value);
      if (normalized === '') {
        return 'Email wajib diisi.';
      }
      return '';
    },
    subject(value) {
      const normalized = normalize(value);
      if (normalized === '') {
        return 'Subjek wajib diisi.';
      }
      if (normalized.length < 3) {
        return 'Subjek minimal 3 karakter.';
      }
      return '';
    },
    message(value) {
      const normalized = normalize(value);
      if (normalized === '') {
        return 'Pesan wajib diisi.';
      }
      if (normalized.length < 10) {
        return 'Pesan minimal 10 karakter.';
      }
      return '';
    },
    phone(value) {
      const normalized = normalize(value);
      if (normalized === '') {
        return '';
      }
      const digits = normalized.replace(/[^0-9]/g, '');
      if (digits.length < 6) {
        return 'Nomor telepon minimal 6 digit.';
      }
      return '';
    }
  };

  const hideFeedback = () => {
    if (!feedbackEl) {
      return;
    }
    feedbackEl.textContent = '';
    feedbackEl.classList.remove('is-visible');
    feedbackEl.setAttribute('hidden', 'hidden');
  };

  const showFeedback = (message) => {
    if (!feedbackEl) {
      return;
    }
    feedbackEl.textContent = message;
    feedbackEl.classList.add('is-visible');
    feedbackEl.removeAttribute('hidden');
  };

  const updateFieldState = (field, message) => {
    field.setCustomValidity(message);
    if (message) {
      field.classList.add('is-invalid');
      field.classList.remove('is-valid');
      field.setAttribute('aria-invalid', 'true');
    } else {
      field.classList.remove('is-invalid');
      field.removeAttribute('aria-invalid');
      const value = normalize(field.value);
      if (value !== '') {
        field.classList.add('is-valid');
      } else {
        field.classList.remove('is-valid');
      }
    }
  };

  const validateField = (field) => {
    if (!field) {
      return true;
    }
    const validator = validators[field.name];
    let message = validator ? validator(field.value) : '';
    field.setCustomValidity('');
    if (message === '') {
      const nativeValid = field.checkValidity();
      if (!nativeValid) {
        message = field.validationMessage || 'Input tidak valid.';
      }
    }
    updateFieldState(field, message);
    return message === '';
  };

  fields.forEach((field) => {
    if (field === honeypotField || field.type === 'hidden') {
      return;
    }
    field.addEventListener('input', () => {
      field.dataset.touched = 'true';
      validateField(field);
      hideFeedback();
    });
    field.addEventListener('blur', () => {
      field.dataset.touched = 'true';
      validateField(field);
    });
  });

  form.addEventListener('submit', (event) => {
    if (honeypotField && normalize(honeypotField.value) !== '') {
      event.preventDefault();
      return;
    }

    let hasError = false;
    fields.forEach((field) => {
      if (field === honeypotField || field.type === 'hidden') {
        return;
      }
      if (!validateField(field)) {
        hasError = true;
      }
    });

    if (hasError || !form.checkValidity()) {
      event.preventDefault();
      form.reportValidity();
      showFeedback('Mohon lengkapi bidang wajib yang ditandai sebelum mengirim formulir.');
      form.classList.add('contact-form--has-error');
    } else {
      form.classList.remove('contact-form--has-error');
      hideFeedback();
    }
  });
})();
</script>
<?= $this->endSection() ?>
