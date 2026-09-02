<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsComplete
{
    // Memastikan akun telah memenuhi 5 kriteria kelayakan sebelum mengakses fitur transaksi
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Validasi 5 kriteria kelayakan akun (Email, Telepon, Biometrik Wajah, TTD, Jadwal)
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
