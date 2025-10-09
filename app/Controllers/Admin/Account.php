<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Account extends BaseController
{
    private UserModel $users;

    public function __construct()
    {
        helper(['activity', 'content']);
        $this->users = new UserModel();
    }

    public function index()
    {
        return $this->edit();
    }

    public function edit(): string
    {
        $userId = (int) session('user_id');
        if ($userId <= 0) {
            return redirect()->to(site_url('login'))->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        $user = $this->users->find($userId);
        if (! $user) {
            session()->destroy();
            return redirect()->to(site_url('login'))->with('error', 'Akun tidak ditemukan. Silakan login.');
        }

        return view('admin/account/settings', [
            'title'      => 'Pengaturan Akun',
            'user'       => $user,
            'validation' => session()->getFlashdata('validation') ?? \Config\Services::validation(),
            'formErrors' => session()->getFlashdata('formErrors') ?? [],
        ]);
    }

    public function update()
    {
        $userId = (int) session('user_id');
        if ($userId <= 0) {
            return redirect()->to(site_url('login'))->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        $user = $this->users->find($userId);
        if (! $user) {
            session()->destroy();
            return redirect()->to(site_url('login'))->with('error', 'Akun tidak ditemukan. Silakan login.');
        }

        $rules = [
            'name'              => 'permit_empty|max_length[100]',
            'email'             => "required|valid_email|max_length[150]|is_unique[users.email,id,{$userId}]",
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

        $data = [
            'name'  => $name,
            'email' => $email,
        ];

        $formErrors = [];
        if ($newPassword !== '') {
            if ($currentPassword === '') {
                $formErrors['current_password'] = 'Isi password saat ini sebelum mengganti password.';
            } elseif (! password_verify($currentPassword, $user['password_hash'])) {
                $formErrors['current_password'] = 'Password saat ini tidak sesuai.';
            } else {
                $data['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }
        }

        if ($formErrors !== []) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui password. Periksa kembali data yang diisi.')
                ->with('validation', $this->validator)
                ->with('formErrors', $formErrors);
        }

        $this->users->update($userId, $data);

        $message = $newPassword !== '' ? 'Memperbarui profil dan password sendiri' : 'Memperbarui profil sendiri';
        log_activity('account.update', $message, $userId);

        return redirect()->to(site_url('admin/settings'))
            ->with('message', 'Pengaturan akun berhasil diperbarui.');
    }
}

