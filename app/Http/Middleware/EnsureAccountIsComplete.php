<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Cek 4 syarat kelengkapan akun:
        // 1. Phone number & phone_verified_at
        // 2. Email verified
        // 3. Jadwal kerja (schedule_type)
        // 4. Biometrik wajah (face_descriptor)
        if (!$user->isAccountComplete()) {
            $pesanDitolak = 'Akses Ditolak: Anda wajib melengkapi verifikasi nomor WhatsApp, verifikasi email, pengaturan jadwal kerja, dan biometrik wajah sebelum dapat membuat pengajuan.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $pesanDitolak,
                    'completion_status' => $user->getAccountCompletionStatus(),
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', $pesanDitolak);
        }

        return $next($request);
    }
}
