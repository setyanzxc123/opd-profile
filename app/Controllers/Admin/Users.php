<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\Shield\Entities\User;
use Config\Services;

class Users extends BaseController
{
    protected UserModel $users;

    public function __construct()
    {
        helper(['activity', 'auth']);
        $this->users = model(UserModel::class);
    }

    public function index()
    {
        /** @var list<User> $records */
        $records = $this->users->withIdentities()->orderBy('id', 'DESC')->findAll(200);
        $users   = array_map(fn (User $user) => $this->presentUser($user), $records);

        return view('admin/users/index', [
            'title' => 'Pengguna',
            'users' => $users,
        ]);
    }

    public function create()
    {
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
            'validation' => session()->getFlashdata('validation') ?? Services::validation(),
            'formErrors' => session()->getFlashdata('formErrors') ?? [],
        ]);
    }

    public function store()
    {
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

        $username = sanitize_plain_text($this->request->getPost('username'));
        $email    = sanitize_plain_text($this->request->getPost('email'));
        $name     = sanitize_plain_text($this->request->getPost('name'));
        $role     = $this->request->getPost('role');
        $password = (string) $this->request->getPost('password');

        $user = new User([
            'username'       => $username,
            'name'           => $name !== '' ? $name : null,
            'role'           => $role,
            'status'         => 'active',
            'status_message' => null,
            'active'         => 1,
            'is_active'      => 1,
        ]);

        $user->email = $email;
        $user->setEmail($email);
        $this->users->withPassword($user, $password);

        $this->users->save($user);
        $userId = (int) $this->users->getInsertID();

        /** @var User $persisted */
        $persisted = $this->users->withIdentities()->find($userId);
        $persisted->syncGroups([$role === 'admin' ? 'admin' : 'editor']);
        $this->users->save($persisted);

        log_activity('user.create', 'Menambah pengguna: ' . $username);

        return redirect()->to(site_url('admin/users'))->with('message', 'Pengguna ditambahkan.');
    }

    public function edit(int $id)
    {
        /** @var User|null $user */
        $user = $this->users->withIdentities()->find($id);
        if (! $user) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/users/form', [
            'title'      => 'Ubah Pengguna',
            'mode'       => 'edit',
            'user'       => $this->presentUser($user),
            'roles'      => UserModel::ROLES,
            'validation' => session()->getFlashdata('validation') ?? Services::validation(),
            'formErrors' => session()->getFlashdata('formErrors') ?? [],
        ]);
    }

    public function update(int $id)
    {
        /** @var User|null $user */
        $user = $this->users->withIdentities()->find($id);
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

        $username = sanitize_plain_text($this->request->getPost('username'));
        $email    = sanitize_plain_text($this->request->getPost('email'));
        $name     = sanitize_plain_text($this->request->getPost('name'));
        $role     = $this->request->getPost('role');
        $isActive = (int) ($this->request->getPost('is_active') ? 1 : 0);
        $password = (string) $this->request->getPost('password');

        $originalRole   = strtolower((string) ($user->role ?? 'editor'));
        $originalActive = (bool) $user->active;

        if ((int) $id === (int) auth('session')->user()->id && $isActive === 0) {
            return redirect()->back()->withInput()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        if ($originalRole === 'admin' && $originalActive) {
            if (($isActive === 0 || $role !== 'admin') && $this->countActiveAdmins($id) <= 1) {
                return redirect()->back()->withInput()->with('error', 'Minimal satu admin harus aktif.');
            }
        }

        $user->username  = $username;
        $user->name      = $name !== '' ? $name : null;
        $user->role      = $role;
        $user->email     = $email;
        $user->setEmail($email);
        $user->active    = $isActive;
        $user->is_active = $isActive;
        $user->status    = $isActive ? 'active' : 'inactive';

        if ($password !== '') {
            $this->users->withPassword($user, $password);
        }

        try {
            $user->syncGroups([$role === 'admin' ? 'admin' : 'editor']);
            $this->users->save($user);
        } catch (\Throwable $throwable) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui pengguna.')
                ->with('formErrors', ['general' => $throwable->getMessage()])
                ->with('validation', Services::validation());
        }

        log_activity('user.update', 'Memperbarui pengguna: ' . $username);

        return redirect()->to(site_url('admin/users'))->with('message', 'Perubahan disimpan.');
    }

    public function toggle(int $id)
    {
        /** @var User|null $user */
        $user = $this->users->withIdentities()->find($id);
        if (! $user) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Data tidak ditemukan.');
        }

        if ((int) $id === (int) auth('session')->user()->id) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $isActive = $user->active ? 0 : 1;
        $role     = strtolower((string) ($user->role ?? 'editor'));

        if ($role === 'admin' && $user->active && $this->countActiveAdmins($id) <= 1) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Minimal satu admin harus aktif.');
        }

        $user->active    = $isActive;
        $user->is_active = $isActive;
        $user->status    = $isActive ? 'active' : 'inactive';

        $this->users->save($user);

        $statusText = $isActive ? 'Mengaktifkan' : 'Menonaktifkan';
        log_activity('user.toggle', $statusText . ' pengguna: ' . $user->username);

        return redirect()->to(site_url('admin/users'))->with('message', 'Status pengguna diperbarui.');
    }

    public function resetPassword(int $id)
    {
        /** @var User|null $user */
        $user = $this->users->withIdentities()->find($id);
        if (! $user) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Data tidak ditemukan.');
        }

        $newPassword = bin2hex(random_bytes(4));
        $this->users->withPassword($user, $newPassword);
        $this->users->save($user);

        log_activity('user.reset_password', 'Reset password untuk ' . $user->username);

        return redirect()->to(site_url('admin/users'))->with('message', "Password baru untuk {$user->username}: {$newPassword}");
    }

    private function presentUser(User $user): array
    {
        $identity  = $user->getEmailIdentity();
        $email     = $user->email ?? ($identity?->secret ?? '');
        $lastLogin = $user->last_login_at ?? null;

        if ($lastLogin instanceof \CodeIgniter\I18n\Time) {
            $lastLogin = $lastLogin->toDateTimeString();
        }

        $role = strtolower((string) ($user->role ?? ($user->inGroup('admin') ? 'admin' : 'editor')));

        return [
            'id'            => $user->id,
            'username'      => $user->username,
            'email'         => $email,
            'name'          => $user->name,
            'role'          => $role,
            'is_active'     => (int) ($user->is_active ?? ($user->active ? 1 : 0)),
            'last_login_at' => $lastLogin,
        ];
    }

    private function countActiveAdmins(?int $excludeId = null): int
    {
        $builder = $this->users->builder();
        $builder->where('role', 'admin')
            ->where('active', 1);

        if ($excludeId !== null) {
            $builder->where('id <>', $excludeId);
        }

        return (int) $builder->countAllResults();
    }
}
