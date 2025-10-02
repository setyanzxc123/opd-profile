<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use Throwable;

class Users extends BaseController
{
    protected UserModel $users;

    public function __construct()
    {
        helper('activity');
        $this->users = new UserModel();
    }

    protected function ensureAdmin()
    {
        if (session('role') !== 'admin') {
            return redirect()->to(site_url('admin'))->with('error', 'Hanya admin yang boleh mengakses kelola pengguna.');
        }

        return null;
    }

    public function index()
    {
        if ($response = $this->ensureAdmin()) {
            return $response;
        }

        $users = $this->users->orderBy('id', 'DESC')->findAll(200);

        return view('admin/users/index', [
            'title' => 'Pengguna',
            'users' => $users,
        ]);
    }

    public function create()
    {
        if ($response = $this->ensureAdmin()) {
            return $response;
        }

        return view('admin/users/form', [
            'title'      => 'Tambah Pengguna',
            'mode'       => 'create',
            'user'       => [
                'id'        => 0,
                'username'  => '',
                'email'     => '',
                'name'      => '',
                'role'      => 'editor',
                'is_active' => 1,
            ],
            'roles'      => UserModel::ROLES,
            'validation' => session()->getFlashdata('validation') ?? \Config\Services::validation(),
            'formErrors' => session()->getFlashdata('formErrors') ?? [],
        ]);
    }

    public function store()
    {
        if ($response = $this->ensureAdmin()) {
            return $response;
        }

        $rules = [
            'username'          => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email'             => 'required|valid_email|is_unique[users.email]',
            'name'              => 'permit_empty|max_length[100]',
            'role'              => 'required|in_list[' . implode(',', UserModel::ROLES) . ']',
            'password'          => 'required|min_length[8]',
            'password_confirm'  => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', 'Periksa kembali isian.')
                ->with('formErrors', $this->validator->getErrors())
                ->with('validation', $this->validator);
        }

        $this->users->insert([
            'username'      => $this->request->getPost('username'),
            'email'         => $this->request->getPost('email'),
            'name'          => $this->request->getPost('name'),
            'role'          => $this->request->getPost('role'),
            'is_active'     => 1,
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        log_activity('user.create', 'Menambah pengguna: ' . $this->request->getPost('username'));

        return redirect()->to(site_url('admin/users'))->with('message', 'Pengguna ditambahkan.');
    }

    public function edit(int $id)
    {
        if ($response = $this->ensureAdmin()) {
            return $response;
        }

        $user = $this->users->find($id);
        if (! $user) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/users/form', [
            'title'      => 'Ubah Pengguna',
            'mode'       => 'edit',
            'user'       => $user,
            'roles'      => UserModel::ROLES,
            'validation' => session()->getFlashdata('validation') ?? \Config\Services::validation(),
            'formErrors' => session()->getFlashdata('formErrors') ?? [],
        ]);
    }

    public function update(int $id)
    {
        if ($response = $this->ensureAdmin()) {
            return $response;
        }

        $user = $this->users->find($id);
        if (! $user) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'username'          => "required|min_length[3]|max_length[50]|is_unique[users.username,id,{$id}]",
            'email'             => "required|valid_email|is_unique[users.email,id,{$id}]",
            'name'              => 'permit_empty|max_length[100]',
            'role'              => 'required|in_list[' . implode(',', UserModel::ROLES) . ']',
            'password'          => 'permit_empty|min_length[8]',
            'password_confirm'  => 'matches[password]',
            'is_active'         => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', 'Periksa kembali isian.')
                ->with('formErrors', $this->validator->getErrors())
                ->with('validation', $this->validator);
        }

        $data = [
            'username'  => $this->request->getPost('username'),
            'email'     => $this->request->getPost('email'),
            'name'      => $this->request->getPost('name'),
            'role'      => $this->request->getPost('role'),
            'is_active' => (int) ($this->request->getPost('is_active') ? 1 : 0),
        ];

        $password = $this->request->getPost('password');
        if ($password) {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ((int) $id === (int) session('user_id') && $data['is_active'] === 0) {
            return redirect()->back()->withInput()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $adminCount = $this->users->where('role', 'admin')->where('is_active', 1)->countAllResults();

        if ($user['role'] === 'admin' && (int) $user['is_active'] === 1) {
            if ($data['is_active'] === 0 && $adminCount <= 1) {
                return redirect()->back()->withInput()->with('error', 'Minimal satu admin harus aktif.');
            }

            if ($data['role'] !== 'admin' && $adminCount <= 1) {
                return redirect()->back()->withInput()->with('error', 'Minimal satu admin harus aktif.');
            }
        }

        $this->users->update($id, $data);

        log_activity('user.update', 'Memperbarui pengguna: ' . $user['username']);

        return redirect()->to(site_url('admin/users'))->with('message', 'Perubahan disimpan.');
    }

    public function toggle(int $id)
    {
        if ($response = $this->ensureAdmin()) {
            return $response;
        }

        $user = $this->users->find($id);
        if (! $user) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Data tidak ditemukan.');
        }

        if ((int) $id === (int) session('user_id')) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $adminCount = $this->users->where('role', 'admin')->where('is_active', 1)->countAllResults();
        if ($user['role'] === 'admin' && (int) $user['is_active'] === 1 && $adminCount <= 1) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Minimal satu admin harus aktif.');
        }

        $this->users->update($id, ['is_active' => $user['is_active'] ? 0 : 1]);

        $statusText = $user['is_active'] ? 'Menonaktifkan' : 'Mengaktifkan';
        log_activity('user.toggle', $statusText . ' pengguna: ' . $user['username']);

        return redirect()->to(site_url('admin/users'))->with('message', 'Status pengguna diperbarui.');
    }

    public function resetPassword(int $id)
    {
        if ($response = $this->ensureAdmin()) {
            return $response;
        }

        $user = $this->users->find($id);
        if (! $user) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Data tidak ditemukan.');
        }

        try {
            $temporaryPassword = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
        } catch (Throwable $exception) {
            log_message('error', 'Failed generating temporary password: {error}', ['error' => $exception->getMessage()]);

            return redirect()->to(site_url('admin/users'))->with('error', 'Gagal membuat password baru. Silakan coba lagi.');
        }

        $newHash = password_hash($temporaryPassword, PASSWORD_DEFAULT);
        $originalHash = $user['password_hash'] ?? null;

        if (! $this->users->update($id, ['password_hash' => $newHash])) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Gagal menyimpan password baru.');
        }

        if (! $this->storePasswordResetSecret($user, $temporaryPassword)) {
            if ($originalHash !== null) {
                $this->users->update($id, ['password_hash' => $originalHash]);
            }

            log_message('error', 'Failed to persist temporary password for user: {username}', ['username' => $user['username'] ?? '']);

            return redirect()->to(site_url('admin/users'))->with('error', 'Password baru gagal dicatat. Tidak ada perubahan yang dilakukan.');
        }

        log_activity('user.reset_password', 'Reset password untuk ' . $user['username']);

        return redirect()->to(site_url('admin/users'))
            ->with('message', 'Password sementara berhasil dibuat. Ambil secara aman dari berkas writable/private/password-resets.log dan bagikan kepada pengguna.');
    }

    private function storePasswordResetSecret(array $user, string $plainPassword): bool
    {
        $logDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'private';

        if (! is_dir($logDir) && ! @mkdir($logDir, 0700, true) && ! is_dir($logDir)) {
            return false;
        }

        $logPath = $logDir . DIRECTORY_SEPARATOR . 'password-resets.log';
        $username = str_replace(["\r", "\n"], '', (string) ($user['username'] ?? ''));

        $entry = sprintf(
            "[%s] user_id=%d username=%s reset_by=%d new_password=%s%s",
            date('c'),
            (int) ($user['id'] ?? 0),
            $username,
            (int) session('user_id'),
            $plainPassword,
            PHP_EOL
        );

        $result = @file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            return false;
        }

        @chmod($logPath, 0600);

        return true;
    }
}
