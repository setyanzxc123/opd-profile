<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $s = session();
        if (!$s->get('user_id')) {
            return redirect()->to(site_url('login'))->with('error', 'Silakan login.');
        }
        if (!in_array($s->get('role'), ['admin', 'editor'])) {
            return redirect()->to('/')->with('error', 'Tidak berwenang.');
        }

        $path = trim($request->getUri()->getPath(), '/');
        if (str_starts_with($path, 'admin/users') && $s->get('role') !== 'admin') {
            return redirect()->to(site_url('admin'))->with('error', 'Hanya admin yang boleh mengakses kelola pengguna.');
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
