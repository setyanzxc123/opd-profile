<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Entities\User;
use LogicException;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');

        $auth = auth('session');

        if (! $auth->loggedIn()) {
            return redirect()->to(site_url('login'))->with('error', 'Silakan login.');
        }

        try {
            /** @var User $user */
            $user = $auth->user();
        } catch (LogicException) {
            $auth->logout();

            return redirect()->to(site_url('login'))->with('error', 'Silakan login.');
        }

        if ($this->isDeactivated($user)) {
            $auth->logout();

            return redirect()->to(site_url('login'))->with('error', 'Akun dinonaktifkan.');
        }

        $role   = $this->resolveRole($user);
        $config = config('AdminAccess');

        if (! isset($config->roles[$role])) {
            $auth->logout();

            return redirect()->to(site_url('login'))->with('error', 'Akun tidak memiliki akses.');
        }

        $this->synchronizeSession($user, $role);

        $segments = $request->getUri()->getSegments();
        if (($segments[0] ?? '') !== 'admin') {
            return;
        }

        $default = $config->defaultDashboardSection ?? 'dashboard';
        $section = $this->detectSection($segments, $default);
        $allowed = $config->roles[$role]['allowedSections'] ?? [];

        if (! $this->isAllowed($section, $allowed)) {
            $fallback = $config->fallbackRoute ?? 'admin';

            return redirect()->to(site_url($fallback))->with('error', 'Tidak berwenang.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No-op
    }

    private function synchronizeSession(User $user, string $role): void
    {
        session()->set([
            'user_id'   => $user->id,
            'username'  => $user->username,
            'name'      => $user->name ?? $user->username ?? 'Admin',
            'role'      => $role,
            'logged_in' => true,
        ]);
    }

    private function resolveRole(User $user): string
    {
        $role = strtolower((string) ($user->role ?? ''));

        if ($role === 'admin' || $user->inGroup('admin')) {
            return 'admin';
        }

        return 'editor';
    }

    private function detectSection(array $segments, string $default): string
    {
        $section = $segments[1] ?? '';

        if ($section === '') {
            return $default;
        }

        return strtolower($section);
    }

    /**
     * @param list<string> $allowed
     */
    private function isAllowed(string $section, array $allowed): bool
    {
        $normalized = array_map(static fn ($item) => strtolower((string) $item), $allowed);

        if (in_array('*', $normalized, true)) {
            return true;
        }

        return in_array(strtolower($section), $normalized, true);
    }

    private function isDeactivated(User $user): bool
    {
        $activeStates = [
            $user->active ?? null,
            $user->is_active ?? null,
        ];

        foreach ($activeStates as $state) {
            if ($state !== null && (int) $state === 0) {
                return true;
            }
        }

        $status = strtolower((string) ($user->status ?? ''));

        return $status !== '' && $status !== 'active';
    }
}
