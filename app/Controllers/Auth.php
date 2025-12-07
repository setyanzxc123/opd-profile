<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\Shield\Result;

class Auth extends BaseController
{
    public function login()
    {
        helper('auth');

        if (auth('session')->loggedIn()) {
            return redirect()->to(site_url('admin'));
        }

        return view('auth/login');
    }

    public function attempt()
    {
        helper(['activity', 'auth']);

        $rules = ['username' => 'required', 'password' => 'required'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Lengkapi data.');
        }

        $throttler     = service('throttler');
        $ipAddress     = (string) $this->request->getIPAddress();
        $ipKey         = 'login-ip-' . hash('sha256', $ipAddress ?: 'unknown');
        $usernameInput = (string) $this->request->getPost('username');
        $sanitizedUser = strtolower(preg_replace('/[^a-z0-9]/', '', $usernameInput));
        $userKey       = $sanitizedUser !== '' ? 'login-user-' . $sanitizedUser : null;
        $maxAttempts   = 5;
        $decaySeconds  = 60;

        if (! $throttler->check($ipKey, $maxAttempts, $decaySeconds)) {
            $wait = (int) ceil($throttler->getTokenTime($ipKey));
            return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan login. Coba lagi dalam ' . $wait . ' detik.');
        }

        if ($userKey && ! $throttler->check($userKey, $maxAttempts, $decaySeconds)) {
            $wait = (int) ceil($throttler->getTokenTime($userKey));
            return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan untuk username ini. Coba lagi dalam ' . $wait . ' detik.');
        }

        $credentials = [
            'username' => $usernameInput,
            'password' => (string) $this->request->getPost('password'),
        ];

        $auth   = auth('session');
        $result = $auth->attempt($credentials);

        if (! $result instanceof Result || ! $result->isOK()) {
            return redirect()->back()->withInput()->with('error', $result->reason() ?? 'Username atau password salah.');
        }

        // Regenerate session ID to prevent session fixation attacks
        session()->regenerate(true);

        /** @var \CodeIgniter\Shield\Entities\User $userEntity */
        $userEntity = $auth->user();
        $userModel  = model(UserModel::class);
        $userModel->update($userEntity->id, ['last_login_at' => date('Y-m-d H:i:s')]);

        log_activity('auth.login', 'Login berhasil', (int) $userEntity->id);

        return redirect()->to(site_url('admin'));
    }

    public function logout()
    {
        helper(['activity', 'auth']);

        $auth = auth('session');
        if ($auth->loggedIn()) {
            /** @var \CodeIgniter\Shield\Entities\User $user */
            $user = $auth->user();
            log_activity('auth.logout', 'Logout manual', (int) $user->id);
        }

        $auth->logout();

        session()->destroy();

        return redirect()->to(site_url('login'))->with('message', 'Anda telah logout.');
    }
}
