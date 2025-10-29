<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\Shield\Entities\User;
use Config\Services;

class Account extends BaseController
{
    private UserModel $users;

    public function __construct()
    {
        helper(['activity', 'content', 'auth']);
        $this->users = model(UserModel::class);
    }

    public function index()
    {
        return $this->edit();
    }

    public function edit(): string
    {
        $user = $this->currentUser();
        if (! $user) {
            return redirect()->to(site_url('login'))->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        return view('admin/account/settings', [
            'title'      => 'Pengaturan Akun',
            'user'       => $this->presentUser($user),
            'validation' => session()->getFlashdata('validation') ?? \Config\Services::validation(),
            'formErrors' => session()->getFlashdata('formErrors') ?? [],
        ]);
    }

    public function update()
    {
        $user = $this->currentUser();
        if (! $user) {
            return redirect()->to(site_url('login'))->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        $rules = [
            'name'              => 'permit_empty|max_length[100]',
            'email'             => "required|valid_email|max_length[150]|is_unique[users.email,id,{$user->id}]",
            'current_password'  => 'permit_empty',
            'password'          => 'permit_empty|min_length[8]',
            'password_confirm'  => 'permit_empty|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', 'Periksa kembali isian yang diberikan.')
                ->with('validation', $this->validator)
                ->with('formErrors', $this->validator->getErrors());
        }

        $name             = sanitize_plain_text($this->request->getPost('name'));
        $email            = sanitize_plain_text($this->request->getPost('email'));
        $currentPassword  = (string) $this->request->getPost('current_password');
        $newPassword      = (string) $this->request->getPost('password');

        $user->name  = $name !== '' ? $name : null;
        $user->email = $email;
        $user->setEmail($email);

        $formErrors = [];
        if ($newPassword !== '') {
            if ($currentPassword === '') {
                $formErrors['current_password'] = 'Isi password saat ini sebelum mengganti password.';
            } elseif (! service('passwords')->verify($currentPassword, (string) $user->password_hash)) {
                $formErrors['current_password'] = 'Password saat ini tidak sesuai.';
            } else {
                $this->users->withPassword($user, $newPassword);
            }
        }

        if ($formErrors !== []) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui password. Periksa kembali data yang diisi.')
                ->with('validation', $this->validator)
                ->with('formErrors', $formErrors);
        }

        try {
            $this->users->save($user);
        } catch (\Throwable $throwable) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui akun.')
                ->with('validation', Services::validation()->setError('general', $throwable->getMessage()));
        }

        $message = $newPassword !== '' ? 'Memperbarui profil dan password sendiri' : 'Memperbarui profil sendiri';
        log_activity('account.update', $message, (int) $user->id);

        return redirect()->to(site_url('admin/settings'))
            ->with('message', 'Pengaturan akun berhasil diperbarui.');
    }

    private function currentUser(): ?User
    {
        $auth = auth('session');
        if (! $auth->loggedIn()) {
            session()->destroy();

            return null;
        }

        /** @var User $identity */
        $identity = $auth->user();

        return $this->users->withIdentities()->find($identity->id);
    }

    private function presentUser(User $user): array
    {
        $identity = $user->getEmailIdentity();

        return [
            'id'       => $user->id,
            'username' => $user->username,
            'name'     => $user->name,
            'email'    => $user->email ?? ($identity?->secret ?? ''),
        ];
    }
}

