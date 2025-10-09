<?php
    $formErrors = isset($formErrors) ? (array) $formErrors : [];
    $validation = $validation ?? null;
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0"><?= esc($title ?? 'Form Pengguna') ?></h4>
            <a href="<?= site_url('admin/users') ?>" class="btn btn-sm btn-outline-secondary"><i class="bx bx-arrow-back"></i> Kembali</a>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible" role="alert" aria-live="assertive">
                <?= esc(session('error')) ?>
                <?php if (!empty($formErrors)): ?>
                    <ul class="mb-0 mt-2 small">
                        <?php foreach ($formErrors as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <form id="userForm" method="post" action="<?= $mode === 'edit' ? site_url('admin/users/update/' . $user['id']) : site_url('admin/users') ?>" novalidate>
            <?= csrf_field() ?>

            <div class="row g-3">
                <?php $nameError = $validation && $validation->hasError('name'); ?>
                <div class="col-md-6">
                    <label class="form-label" for="nameInput">Nama</label>
                    <input type="text" class="form-control<?= $nameError ? ' is-invalid' : '' ?>" id="nameInput" name="name" value="<?= esc(old('name', $user['name'] ?? '')) ?>">
                    <?php if ($nameError): ?>
                        <div class="invalid-feedback"><?= esc($validation->getError('name')) ?></div>
                    <?php endif; ?>
                </div>

                <?php $usernameError = $validation && $validation->hasError('username'); ?>
                <div class="col-md-6">
                    <label class="form-label" for="usernameInput">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control<?= $usernameError ? ' is-invalid' : '' ?>" id="usernameInput" name="username" required value="<?= esc(old('username', $user['username'])) ?>">
                    <?php if ($usernameError): ?>
                        <div class="invalid-feedback"><?= esc($validation->getError('username')) ?></div>
                    <?php endif; ?>
                </div>

                <?php $emailError = $validation && $validation->hasError('email'); ?>
                <div class="col-md-6">
                    <label class="form-label" for="emailInput">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control<?= $emailError ? ' is-invalid' : '' ?>" id="emailInput" name="email" required value="<?= esc(old('email', $user['email'])) ?>">
                    <?php if ($emailError): ?>
                        <div class="invalid-feedback"><?= esc($validation->getError('email')) ?></div>
                    <?php endif; ?>
                </div>

                <?php $roleError = $validation && $validation->hasError('role'); ?>
                <div class="col-md-6">
                    <label class="form-label" for="roleInput">Peran <span class="text-danger">*</span></label>
                    <select id="roleInput" name="role" class="form-select<?= $roleError ? ' is-invalid' : '' ?>" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= esc($role) ?>" <?= old('role', $user['role']) === $role ? 'selected' : '' ?>>
                                <?= ucfirst($role) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($roleError): ?>
                        <div class="invalid-feedback"><?= esc($validation->getError('role')) ?></div>
                    <?php endif; ?>
                </div>

                <?php $passwordError = $validation && $validation->hasError('password'); ?>
                <div class="col-md-6">
                    <label class="form-label" for="passwordInput">
                        Password <?= $mode === 'edit' ? '<small class="text-muted">(isi jika ingin mengganti)</small>' : '<span class="text-danger">*</span>' ?>
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control<?= $passwordError ? ' is-invalid' : '' ?>" id="passwordInput" name="password" autocomplete="new-password" <?= $mode === 'edit' ? '' : 'required' ?>>
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#passwordInput" aria-label="Tampilkan password"><i class="bx bx-show"></i></button>
                    </div>
                    <?php if ($passwordError): ?>
                        <div class="invalid-feedback d-block"><?= esc($validation->getError('password')) ?></div>
                    <?php endif; ?>
                    <div id="passwordHelp" class="form-text">Gunakan minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.</div>
                    <div id="passwordStrength" class="small mt-1 text-muted" aria-live="polite">Kekuatan password: -</div>
                </div>

                <?php $passwordConfirmError = $validation && $validation->hasError('password_confirm'); ?>
                <div class="col-md-6">
                    <label class="form-label" for="passwordConfirmInput">Konfirmasi Password <?= $mode === 'edit' ? '' : '<span class="text-danger">*</span>' ?></label>
                    <div class="input-group">
                        <input type="password" class="form-control<?= $passwordConfirmError ? ' is-invalid' : '' ?>" id="passwordConfirmInput" name="password_confirm" autocomplete="new-password" <?= $mode === 'edit' ? '' : 'required' ?>>
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#passwordConfirmInput" aria-label="Tampilkan konfirmasi password"><i class="bx bx-show"></i></button>
                    </div>
                    <?php if ($passwordConfirmError): ?>
                        <div class="invalid-feedback d-block"><?= esc($validation->getError('password_confirm')) ?></div>
                    <?php endif; ?>
                </div>

                <?php if ($mode === 'edit'): ?>
                    <div class="col-md-6">
                        <label class="form-label" for="statusInput">Status Aktif</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="statusInput" name="is_active" value="1" <?= old('is_active', $user['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="statusInput"><?= old('is_active', $user['is_active']) ? 'Aktif' : 'Nonaktif' ?></label>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-4 d-flex flex-wrap justify-content-end gap-2 align-items-center">
                <button type="reset" class="btn btn-outline-secondary" id="resetFormButton"><i class="bx bx-reset me-1"></i> Atur Ulang</button>
                <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('#userForm');
        const passwordInput = document.querySelector('#passwordInput');
        const confirmInput = document.querySelector('#passwordConfirmInput');
        const strengthEl = document.querySelector('#passwordStrength');
        const strengthClasses = ['text-muted', 'text-danger', 'text-warning', 'text-success'];

        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', () => {
                const targetSelector = button.getAttribute('data-target');
                const target = document.querySelector(targetSelector);
                if (!target) return;

                const isHidden = target.getAttribute('type') === 'password';
                target.setAttribute('type', isHidden ? 'text' : 'password');

                const icon = button.querySelector('i');
                if (icon) {
                    icon.classList.toggle('bx-show', !isHidden);
                    icon.classList.toggle('bx-hide', isHidden);
                }
            });
        });

        const evaluateStrength = (value) => {
            let score = 0;
            if (value.length >= 8) score++;
            if (value.length >= 12) score++;
            const hasLetters = /[A-Za-z]/.test(value);
            const hasLower = /[a-z]/.test(value);
            const hasUpper = /[A-Z]/.test(value);
            const hasDigits = /[0-9]/.test(value);
            const hasSymbols = /[^A-Za-z0-9]/.test(value);
            if ((hasLetters && hasDigits) || (hasLetters && hasSymbols)) score++;
            if (hasUpper && hasLower && (hasDigits || hasSymbols)) score++;
            return Math.min(score, 3);
        };

        const updateStrength = () => {
            if (!strengthEl || !passwordInput) return;
            const value = passwordInput.value || '';
            const score = evaluateStrength(value);
            const labels = ['-', 'Lemah', 'Sedang', 'Kuat'];

            strengthClasses.forEach(cls => strengthEl.classList.remove(cls));
            strengthEl.textContent = 'Kekuatan password: ' + labels[score];
            strengthEl.classList.add(strengthClasses[score]);
        };

        if (passwordInput) {
            passwordInput.addEventListener('input', () => {
                passwordInput.setCustomValidity('');
                updateStrength();
            });
            updateStrength();
        }

        if (confirmInput) {
            confirmInput.addEventListener('input', () => {
                confirmInput.setCustomValidity('');
            });
        }

        form?.addEventListener('submit', (event) => {
            let valid = true;

            if (passwordInput && passwordInput.value.length && passwordInput.value.length < 8) {
                passwordInput.setCustomValidity('Password minimal 8 karakter.');
                passwordInput.reportValidity();
                valid = false;
            }

            if (passwordInput && confirmInput && passwordInput.value !== confirmInput.value) {
                confirmInput.setCustomValidity('Konfirmasi password tidak sama.');
                confirmInput.reportValidity();
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
                event.stopPropagation();
            }
        });

        const resetButton = document.querySelector('#resetFormButton');
        resetButton?.addEventListener('click', () => {
            passwordInput?.setCustomValidity('');
            confirmInput?.setCustomValidity('');
            setTimeout(() => {
                updateStrength();
            }, 0);
        });
    });
</script>
<?= $this->endSection() ?>



