<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

    public function attempt()
    {
        $rules = ['username'=>'required','password'=>'required'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error','Lengkapi data.');
        }

        $u = (new UserModel())->where('username', $this->request->getPost('username'))->first();
        if (!$u || !password_verify($this->request->getPost('password'), $u['password_hash'])) {
            return redirect()->back()->withInput()->with('error','Username atau password salah.');
        }

        session()->regenerate();
        session()->set([
            'user_id'   => $u['id'],
            'username'  => $u['username'],
            'role'      => $u['role'],
            'logged_in' => true,
        ]);

        return redirect()->to(site_url('admin'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'))->with('message','Anda telah logout.');
    }
}
