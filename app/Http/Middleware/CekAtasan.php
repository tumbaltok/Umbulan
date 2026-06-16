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
        // 1. Pastikan pengguna sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Ambil data role user (Asumsi: role_id ? = Karyawan biasa)
        // Jika role_id user adalah 4 (karyawan biasa), mereka dilarang masuk!
        if (Auth::user()->role_id == 4) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut!');
        }

        return $next($request);
    }
}
