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
        $roleConfig = $config->roles[$role] ?? null;

        if ($roleConfig === null) {
            $auth->logout();

            return redirect()->to(site_url('login'))->with('error', 'Akun tidak memiliki akses.');
        }

        $allowed = $this->normalizeSections($roleConfig['allowedSections'] ?? []);
        $this->synchronizeSession($user, $role, $allowed, $roleConfig['label'] ?? null);

        $segments = $request->getUri()->getSegments();
        if (($segments[0] ?? '') !== 'admin') {
            return;
        }

        $default = $config->defaultDashboardSection ?? 'dashboard';
        $section = $this->detectSection($segments, $default);

        if (! $this->isAllowed($section, $allowed)) {
            $fallback = $config->fallbackRoute ?? 'admin';

            return redirect()->to(site_url($fallback))->with('error', 'Tidak berwenang.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No-op
    }

    private function synchronizeSession(User $user, string $role, array $allowedSections, ?string $roleLabel = null): void
    {
        $displayName = $this->resolveDisplayName($user);
        $initial     = $this->resolveInitial($displayName);
        $label       = $roleLabel !== null && $roleLabel !== '' ? $roleLabel : ucfirst(strtolower($role));

        session()->set([
            'user_id'   => $user->id,
            'username'  => $user->username,
            'name'      => $displayName,
            'role'      => $role,
            'logged_in' => true,
            'admin_display_name'   => $displayName,
            'admin_initial'        => $initial,
            'admin_role_label'     => $label,
            'admin_allowed_sections' => $allowedSections,
            'admin_has_full_access'  => in_array('*', $allowedSections, true),
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

    private function isAllowed(string $section, array $allowed): bool
    {
        if (in_array('*', $allowed, true)) {
            return true;
        }

        return in_array(strtolower($section), $allowed, true);
    }

    /**
     * @param array<int, string> $sections
     *
     * @return list<string>
     */
    private function normalizeSections(array $sections): array
    {
        $normalized = array_map(static fn ($item) => strtolower(trim((string) $item)), $sections);
        $normalized = array_filter($normalized, static fn ($item) => $item !== '');

        return array_values(array_unique($normalized));
    }

    private function resolveDisplayName(User $user): string
    {
        $candidates = [
            trim((string) ($user->name ?? '')),
            trim((string) ($user->username ?? '')),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return 'Admin';
    }

    private function resolveInitial(string $displayName): string
    {
        $initial = strtoupper(substr($displayName, 0, 1));

        if (function_exists('mb_substr')) {
            $candidate = mb_substr($displayName, 0, 1, 'UTF-8');
            if ($candidate !== false && $candidate !== '') {
                $initial = mb_strtoupper($candidate, 'UTF-8');
            }
        }

        return $initial !== '' ? $initial : 'A';
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
