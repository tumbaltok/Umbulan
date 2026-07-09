<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekAtasan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Pastikan user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Ambil nama role user saat ini (Akan menjadi huruf kecil semua)
        /** @var string $roleName */
        $roleName = strtolower(Auth::user()->role?->role_name ?? '');

        // 3. Cek menggunakan array huruf kecil semua agar cocok dengan strtolower
        if (!in_array($roleName, ['manager', 'supervisor', 'admin'])) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut!');
        }

        // 4. Jika termasuk Manager, Supervisor, atau Admin, izinkan akses
        return $next($request);
    }
}
