<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PhoneVerificationController extends Controller
{
    /**
     * Menampilkan halaman verifikasi nomor WhatsApp OTP (Tier 2).
     */
    public function notice(Request $request)
    {
        $user = $request->user();

        // Jika email belum diverifikasi, alihkan kembali ke Tier 1
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // Jika nomor telepon sudah terverifikasi, lanjutkan ke Dashboard
        if ($user->hasVerifiedPhone()) {
            $intended = session()->get('url.intended');
            if ($intended && (str_contains($intended, '/auth/verify-email') || str_contains($intended, '/auth/verify-phone'))) {
                session()->forget('url.intended');
            }

            return redirect()->intended('/dashboard');
        }

        return view('auth.verify-phone', compact('user'));
    }

    /**
     * Mengirimkan kode OTP 6 digit ke nomor WhatsApp pengguna via Baileys microservice.
     */
    public function sendOtp(Request $request, WhatsAppService $whatsAppService)
    {
        $user = $request->user();

        if (empty($user->phone_number)) {
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp belum terdaftar pada akun Anda. Silakan tentukan nomor WhatsApp terlebih dahulu.',
            ]);
        }

        // Generate OTP 6 digit angka
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Simpan OTP ter-hash dan waktu kedaluwarsa 5 menit ke database
        $user->forceFill([
            'phone_otp' => Hash::make($otp),
            'phone_otp_expires_at' => now()->addMinutes(5),
        ])->save();

        // Format pesan resmi sesuai instruksi ERP Umbulan
        $message = "*[ERP META ADHYA TIRTA UMBULAN]*\n"
            . "Halo {$user->name},\n\n"
            . "Kode Verifikasi (OTP) WhatsApp Anda adalah: *{$otp}*\n\n"
            . "Kode ini berlaku selama 5 menit. Jangan bagikan kode ini kepada siapa pun demi keamanan akun Anda.";

        $result = $whatsAppService->sendMessage($user->phone_number, $message);

        if ($result['success'] ?? false) {
            Log::info("[PhoneVerificationController] OTP berhasil dikirim ke {$user->phone_number} untuk User ID: {$user->id}");

            return back()->with('status', 'otp-sent')->with('message', 'Kode OTP 6 digit berhasil dikirimkan ke nomor WhatsApp Anda.');
        }

        Log::error("[PhoneVerificationController] Gagal mengirim OTP ke {$user->phone_number}: " . ($result['message'] ?? 'Unknown error'));

        return back()->withErrors([
            'phone' => $result['message'] ?? 'Gagal mengirimkan kode OTP via WhatsApp Gateway. Pastikan gateway aktif.',
        ]);
    }

    /**
     * Memvalidasi kode OTP yang dimasukkan oleh pengguna.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Silakan masukkan 6 digit kode OTP.',
            'otp.size' => 'Kode OTP harus terdiri dari tepat 6 digit angka.',
        ]);

        $user = $request->user();

        // Cek apakah OTP sudah kedaluwarsa atau belum pernah digenerate
        if (!$user->phone_otp || !$user->phone_otp_expires_at || now()->isAfter($user->phone_otp_expires_at)) {
            return back()->withErrors([
                'otp' => 'Kode OTP telah kedaluwarsa atau belum dikirimkan. Silakan klik tombol kirim ulang OTP.',
            ]);
        }

        // Verifikasi kesesuaian OTP (mendukung hash maupun plain check)
        if (Hash::check($request->otp, $user->phone_otp) || $request->otp === $user->phone_otp) {
            $user->forceFill([
                'phone_verified_at' => now(),
                'phone_otp' => null,
                'phone_otp_expires_at' => null,
            ])->save();

            Log::info("[PhoneVerificationController] Nomor WhatsApp berhasil diverifikasi untuk User ID: {$user->id}");

            $intended = session()->get('url.intended');
            if ($intended && (str_contains($intended, '/auth/verify-email') || str_contains($intended, '/auth/verify-phone'))) {
                session()->forget('url.intended');
            }

            return redirect()->intended('/dashboard')->with('success', 'Nomor WhatsApp Anda berhasil diverifikasi!');
        }

        Log::warning("[PhoneVerificationController] Percobaan OTP gagal untuk User ID: {$user->id}");

        return back()->withErrors([
            'otp' => 'Kode OTP yang Anda masukkan salah. Silakan periksa kembali pesan WhatsApp Anda.',
        ]);
    }

    /**
     * Memperbarui nomor telepon pengguna jika salah dan langsung mengirimkan OTP baru.
     */
    public function updateNumber(Request $request, WhatsAppService $whatsAppService)
    {
        $user = $request->user();

        $request->validate([
            'phone_number' => 'required|numeric|digits_between:10,15|unique:users,phone_number,' . $user->id,
        ], [
            'phone_number.required' => 'Nomor WhatsApp wajib diisi.',
            'phone_number.numeric' => 'Nomor WhatsApp hanya boleh berupa angka.',
            'phone_number.digits_between' => 'Nomor WhatsApp harus terdiri dari 10 hingga 15 digit angka.',
            'phone_number.unique' => 'Nomor WhatsApp ini telah terdaftar pada akun karyawan lain.',
        ]);

        $newPhone = $request->phone_number;

        // Generate OTP 6 digit
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'phone_number' => $newPhone,
            'phone_verified_at' => null,
            'phone_otp' => Hash::make($otp),
            'phone_otp_expires_at' => now()->addMinutes(5),
        ])->save();

        // Format pesan resmi
        $message = "*[ERP META ADHYA TIRTA UMBULAN]*\n"
            . "Halo {$user->name},\n\n"
            . "Kode Verifikasi (OTP) WhatsApp Anda adalah: *{$otp}*\n\n"
            . "Kode ini berlaku selama 5 menit. Jangan bagikan kode ini kepada siapa pun demi keamanan akun Anda.";

        $result = $whatsAppService->sendMessage($newPhone, $message);

        if ($result['success'] ?? false) {
            Log::info("[PhoneVerificationController] Nomor WA diubah ke {$newPhone} dan OTP terkirim untuk User ID: {$user->id}");

            return back()->with('status', 'otp-sent')->with('message', 'Nomor WhatsApp berhasil diperbarui dan kode OTP telah dikirimkan.');
        }

        return back()->with('status', 'otp-sent')->withErrors([
            'phone' => 'Nomor diperbarui, tetapi WhatsApp Gateway gagal mengirim pesan: ' . ($result['message'] ?? 'Pastikan gateway online.'),
        ]);
    }
}
