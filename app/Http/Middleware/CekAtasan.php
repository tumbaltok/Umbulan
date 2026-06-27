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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Pastikan user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Ambil nama role user saat ini
        // Catatan: Pastikan nama kolom di tabel 'users' Anda adalah 'role_name'
        $roleName = Auth::user()->role->role_name;

        // 3. Cek apakah role_name TIDAK termasuk dalam 'Manager' atau 'Supervisor'
        if (!in_array($roleName, ['Manager', 'Supervisor', 'Admin'])) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut!');
        }

        // 4. Jika termasuk Manager atau Supervisor, izinkan akses
        return $next($request);
    }
}
