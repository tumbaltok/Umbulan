<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppSettingController extends Controller
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    // Menampilkan halaman antarmuka manajemen koneksi WhatsApp Gateway
    public function index()
    {
        $statusData = $this->whatsAppService->getStatus();

        return view('admin.whatsapp.index', compact('statusData'));
    }

    // Endpoint polling status koneksi gateway secara real-time
    public function status(): JsonResponse
    {
        $statusData = $this->whatsAppService->getStatus();

        return response()->json($statusData);
    }

    // Endpoint untuk mendapatkan QR code pemindaian WhatsApp
    public function qr(): JsonResponse
    {
        $qrData = $this->whatsAppService->getQr();

        return response()->json($qrData);
    }

    // Mengirim pesan uji coba koneksi WhatsApp Gateway (Khusus Admin Level 1 & Rate Limited) [H-04 FIX]
    public function sendTest(Request $request): JsonResponse
    {
        $user = Auth::user();

        // [H-04 FIX] Batasi pengiriman pesan test hanya untuk Administrator Level 1
        $isAdmin = $user->isLevel1() || $user->hasRole('ADMIN') || (int)$user->role_id === 1;
        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya akun Administrator Level 1 yang berwenang mengirim pesan uji coba WhatsApp.',
            ], 403);
        }

        // [H-04 FIX] Rate limiting: maksimal 5 kali pengujian per jam
        $throttleKey = 'wa-send-test:' . $user->id;
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Batas pengiriman pesan uji coba tercapai. Silakan coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        $request->validate([
            'phone_number' => 'required|string|min:10|max:16',
            'message'      => 'required|string|max:1000',
        ]);

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 3600);

        $status = $this->whatsAppService->getStatus();
        if (($status['status'] ?? '') !== 'connected') {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp Gateway belum terhubung. Silakan scan QR code terlebih dahulu.',
            ], 400);
        }

        $result = $this->whatsAppService->sendMessage(
            $request->phone_number,
            $request->message
        );

        if ($result['success'] ?? false) {
            return response()->json([
                'success' => true,
                'message' => 'Pesan uji coba berhasil dikirim ke nomor WhatsApp ' . ($result['target'] ?? $request->phone_number) . '!',
                'data'    => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Gagal mengirim pesan uji coba.',
        ], 500);
    }

    // Memutuskan koneksi sesi WhatsApp Gateway (khusus Administrator Level 1) [L-01 FIX]
    public function disconnect(): JsonResponse
    {
        $user = Auth::user();

        // [L-01 FIX] Gunakan method kanonikal isLevel1() dan cek konsisten
        $isLevel1 = $user->isLevel1() || $user->hasRole('ADMIN') || (int)$user->role_id === 1;
        if (!$isLevel1) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya akun Administrator Level 1 yang dapat memutuskan koneksi WhatsApp Gateway.',
            ], 403);
        }

        $result = $this->whatsAppService->disconnect();

        return response()->json($result);
    }
}
