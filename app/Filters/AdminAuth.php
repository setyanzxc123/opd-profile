<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('user_id')) {
            return redirect()->to(site_url('login'))->with('error', 'Silakan login.');
        }

        $role   = strtolower((string) $session->get('role'));
        $config = config('AdminAccess');

        if ($role === '' || ! isset($config->roles[$role])) {
            $session->destroy();

            return redirect()->to(site_url('login'))->with('error', 'Silakan login.');
        }

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
    }

    /**
     * Determine which admin section is being accessed.
     */
    private function detectSection(array $segments, string $default): string
    {
        $section = $segments[1] ?? '';

        if ($section === '') {
            return $default;
        }

        return strtolower($section);
    }

    /**
     * Check whether the given section is allowed for the role.
     *
     * @param list<string> $allowed
     */
    private function isAllowed(string $section, array $allowed): bool
    {
        $normalized = array_map(
            static fn ($item) => strtolower((string) $item),
            $allowed
        );

        if (in_array('*', $normalized, true)) {
            return true;
        }

        return in_array(strtolower($section), $normalized, true);
    }
}
