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

        // Cek 5 syarat kelengkapan akun:
        // 1. Verifikasi Email (email_verified_at)
        // 2. Nomor WhatsApp (phone_number & phone_verified_at)
        // 3. Biometrik Wajah (face_descriptor)
        // 4. Tanda Tangan Digital (signature)
        // 5. Jadwal Kerja (schedule_type)
        if (!$user->isAccountComplete()) {
            $pesanDitolak = 'Akses Ditolak: Anda wajib melengkapi verifikasi email, nomor WhatsApp, biometrik wajah, tanda tangan digital (TTD), dan jadwal kerja sebelum dapat membuat pengajuan.';

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
