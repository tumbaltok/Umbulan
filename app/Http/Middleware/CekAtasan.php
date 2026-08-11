<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekAtasan
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = $user->role;

        if (! $role) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum memiliki role jabatan!');
        }

        $level = (int) ($role->level ?? 3);

        // Level 3 = User Biasa (Sama sekali tidak boleh akses halaman admin)
        if ($level === 3) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki hak akses ke halaman administrator!');
        }

        // Level 2 = Read Only (Bisa lihat fitur admin, tapi dilarang ubah data admin)
        if ($level === 2 && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            // Kecualikan rute persetujuan (tetap boleh approve/reject jika ditujukan kepadanya)
            $isApprovalRoute = $request->routeIs('admin.persetujuan.*');

            if (! $isApprovalRoute) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Role Anda hanya memiliki akses Monitoring (Read-Only)!'], 403);
                }

                return redirect()->back()->with('error', 'Aksi ditolak: Role Anda hanya memiliki hak akses Read-Only (Monitoring)!');
            }
        }

        return $next($request);
    }
}
