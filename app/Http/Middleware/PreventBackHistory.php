<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    // Tambahkan header HTTP anti-cache untuk mencegah browser membuka kembali halaman dari bfcache setelah logout
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Header proteksi cache peramban
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
