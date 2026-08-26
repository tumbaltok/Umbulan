<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Menerima satu atau multi-parameter role, contoh:
     * ->middleware('role:ADMIN')
     * ->middleware('role:ADMIN,PROCUREMENT,OPERATIONAL')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Jika tidak ada role spesifik yang ditentukan, izinkan lewat
        if (empty($roles)) {
            return $next($request);
        }

        // Pecah parameter jika dikirim dalam bentuk koma (misal: "ADMIN,PROCUREMENT")
        $allowedRoles = [];
        foreach ($roles as $roleParam) {
            $parts = explode(',', $roleParam);
            foreach ($parts as $part) {
                $trimmed = trim($part);
                if (!empty($trimmed)) {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        // Cek apakah user memiliki salah satu role yang diizinkan
        if ($user->hasRole($allowedRoles)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Akses ditolak: Anda tidak memiliki wewenang role yang sesuai untuk mengakses fitur ini.',
            ], 403);
        }

        return redirect()->route('dashboard')->with('error', 'Akses ditolak: Anda tidak memiliki wewenang role yang sesuai untuk mengakses halaman ini.');
    }
}
