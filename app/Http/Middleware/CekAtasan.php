<?php

namespace App\Http\Middleware;

use App\Models\User\User;
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

        /** @var User $user */
        $user = Auth::user();

        $roles = $user->roles;

        // Jika tidak punya role di pivot maupun kolom role_id, tolak akses
        if ($roles->isEmpty() && ! $user->role) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda tidak memiliki role yang valid!');
        }

        // BYPASS FULL AKSES (Role ID 1 / Admin)
        if ($user->hasRole('ADMIN') || $roles->contains('id', 1) || $user->role_id === 1) {
            return $next($request);
        }

        // Ambil level tertinggi (angka terkecil) dari seluruh role pengguna
        $minLevel = $roles->min('level') ?? ($user->role?->level ?? 3);

        // Level > 2 (Level 3, 4, 5, dst) Ditolak dari Area Administrator
        if ($minLevel > 2) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki hak akses ke halaman administrator!');
        }

        // Level 2 = Monitoring / Read-Only (Kecuali untuk Rute Persetujuan)
        if ($minLevel === 2 && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
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
