<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     *
     * Menambahkan HTTP headers anti-cache untuk mencegah browser
     * menyimpan snapshot halaman terproteksi di cache lokal/bfcache.
     * Sehingga saat user menekan tombol 'Back' setelah logout,
     * browser dipaksa merevalidasi sesi ke server.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
