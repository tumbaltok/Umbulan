<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePortalSubdomain
{
    /**
     * Memisahkan halaman publik (umbulan.site) dengan portal internal karyawan (karyawan.umbulan.site).
     * Tetap aman dijalankan di localhost, IP LAN, atau environment testing.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mode = strtolower((string) config('app.portal_subdomain_mode', 'auto'));
        if ($mode === 'off') {
            return $next($request);
        }

        $mainDomain = strtolower(trim((string) config('app.main_domain', 'umbulan.site')));
        $portalDomain = strtolower(trim((string) config('app.portal_domain', 'karyawan.' . $mainDomain)));
        $host = strtolower($request->getHost());

        // Abaikan rute API dan health-check agar tetap bisa diakses lintas host
        if ($request->is('api/*') || $request->is('up')) {
            return $next($request);
        }

        $isPortalHost = ($host === $portalDomain) || str_starts_with($host, 'karyawan.');
        $isMainDomain = ($host === $mainDomain) || ($host === 'www.' . $mainDomain);

        if ($isPortalHost) {
            return $next($request);
        }

        if ($isMainDomain || ($mode === 'strict' && !in_array($host, ['localhost', '127.0.0.1'], true))) {
            if ($this->isPortalPath($request)) {
                $targetHost = $isMainDomain
                    ? $portalDomain
                    : (str_starts_with($host, 'www.')
                        ? 'karyawan.' . substr($host, 4)
                        : 'karyawan.' . $host);

                $scheme = $request->getScheme();
                $port = $request->getPort();
                $portSuffix = (($scheme === 'http' && $port !== 80) || ($scheme === 'https' && $port !== 443))
                    ? ':' . $port
                    : '';

                $targetUrl = "{$scheme}://{$targetHost}{$portSuffix}" . $request->getRequestUri();

                return redirect()->away($targetUrl, $request->isMethod('GET') ? 302 : 307);
            }
        }

        return $next($request);
    }

    private function isPortalPath(Request $request): bool
    {
        return $request->is(
            'login',
            'register',
            'forgot',
            'forgot/*',
            'forgot-password',
            'forgot-password/*',
            'auth/*',
            'email/*',
            'logout',
            'dashboard',
            'dashboard/*',
            'profile',
            'profile/*',
            'attendance/*',
            'user/*',
            'cuti/*',
            'car/*',
            'mpr/*',
            'admin/*'
        );
    }
}
