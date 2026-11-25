<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    // Menampilkan formulir awal identifikasi pemulihan kata sandi
    public function showForgotForm(Request $request): View
    {
        return view('auth.forgot-password');
    }

    // Mencari akun pengguna berdasarkan email atau NIP
    public function identify(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'identity' => 'required|string|max:255',
        ], [
            'identity.required' => 'Silakan masukkan Email atau NIP terdaftar Anda.',
        ]);

        $identity = trim($request->identity);
        $user = $this->findUserByIdentity($identity);

        if (!$user) {
            // [SEC-02 FIX] Anti-account enumeration: response seragam saat akun tidak ditemukan
            session()->forget('reset_candidate_id');
            $isEmail = filter_var($identity, FILTER_VALIDATE_EMAIL);

            $channels = [
                [
                    'id' => 'email',
                    'name' => 'Email Kepegawaian',
                    'description' => 'Kirim kode verifikasi ke kotak masuk email Anda.',
                    'target_masked' => $isEmail ? $this->maskEmail($identity) : 'u***@***.***',
                    'icon' => 'fa-solid fa-envelope',
                    'available' => true,
                ],
            ];

            $responseData = [
                'status' => 'success',
                'message' => 'Identifikasi akun selesai. Kode verifikasi OTP akan dikirimkan melalui Email Kepegawaian.',
                'user' => [
                    'name' => 'Akun Pengguna',
                    'nip' => 'Terlindungi',
                    'initials' => 'AP',
                ],
                'channels' => $channels,
            ];

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($responseData);
            }

            return back()->with('identification', $responseData);
        }

        // Saluran pengiriman OTP resmi (Email Kepegawaian)
        $channels = [];
        if (!empty($user->email)) {
            $channels[] = [
                'id' => 'email',
                'name' => 'Email Kepegawaian',
                'description' => 'Kirim kode verifikasi ke kotak masuk email Anda.',
                'target_masked' => $this->maskEmail($user->email),
                'icon' => 'fa-solid fa-envelope',
                'available' => true,
            ];
        }

        // [SEC-02 FIX] Simpan identifier pengguna hanya di session server-side
        session(['reset_candidate_id' => $user->id]);

        // Hapus data sensitif (id, name, nip) dari payload response JSON
        $responseData = [
            'status' => 'success',
            'message' => 'Identifikasi akun selesai. Kode verifikasi OTP akan dikirimkan melalui Email Kepegawaian.',
            'user' => [
                'name' => 'Akun Pengguna',
                'nip' => 'Terlindungi',
                'initials' => 'AP',
            ],
            'channels' => $channels,
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($responseData);
        }

        return back()->with('identification', $responseData);
    }

    // Menghasilkan dan mengirimkan kode OTP 6 digit melalui Email
    public function sendOtp(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'user_id' => 'nullable',
            'identity' => 'nullable|string|max:255',
            'channel' => 'nullable|string|in:email',
        ]);

        $user = null;
        if (session()->has('reset_candidate_id')) {
            $user = User::find(session('reset_candidate_id'));
        } elseif ($request->filled('identity')) {
            $user = $this->findUserByIdentity(trim($request->identity));
        } elseif ($request->filled('user_id')) {
            $user = User::find($request->user_id);
        }

        if (!$user) {
            // [SEC-02 FIX] Anti-account enumeration: simulasikan sukses pengiriman OTP jika akun tidak terdaftar
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Jika akun terdaftar pada sistem kami, kode verifikasi OTP telah dikirimkan ke saluran yang dipilih.',
                    'redirect_url' => route('forgot.verify_otp_view'),
                    'channel' => $request->channel,
                    'cooldown_seconds' => 60,
                ]);
            }
            return redirect()->route('forgot.verify_otp_view')->with('success', 'Jika akun terdaftar pada sistem kami, kode verifikasi OTP telah dikirimkan ke saluran yang dipilih.');
        }

        $channel = 'email';

        // Proteksi batas permintaan (Rate Limiting) kirim OTP
        $ipThrottleKey = 'send-reset-otp-ip:' . $request->ip();
        $userThrottleKey = 'send-reset-otp-user:' . $user->id;

        if (RateLimiter::tooManyAttempts($ipThrottleKey, 5) || RateLimiter::tooManyAttempts($userThrottleKey, 3)) {
            $seconds = max(RateLimiter::availableIn($ipThrottleKey), RateLimiter::availableIn($userThrottleKey));
            $msg = "Terlalu banyak permintaan OTP. Mohon tunggu {$seconds} detik sebelum mencoba kembali.";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg], 429);
            }
            return back()->withErrors(['otp' => $msg]);
        }

        // Cooldown pengiriman ulang OTP selama 60 detik
        $resendAllowedAt = session('reset_otp_resend_allowed_at');
        if ($resendAllowedAt && now()->timestamp < $resendAllowedAt) {
            $waitSec = $resendAllowedAt - now()->timestamp;
            $msg = "Harap tunggu {$waitSec} detik sebelum meminta kode OTP kembali.";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg], 429);
            }
            return back()->withErrors(['otp' => $msg]);
        }

        // Generate kode OTP 6-digit numerik
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiryMinutes = 5;

        $targetMasked = $this->maskEmail($user->email);
        $sendSuccess = false;
        $sendErrorMessage = '';

        try {
            Mail::to($user->email)->send(new PasswordResetOtpMail($user, $otp, $expiryMinutes));
            $sendSuccess = true;
            Log::info("[ForgotPassword] Email OTP berhasil dikirim ke {$user->email} untuk User ID {$user->id}");
        } catch (\Exception $e) {
            Log::error("[ForgotPassword] Gagal mengirim email OTP ke {$user->email}: " . $e->getMessage());
            // Fallback pengiriman pesan teks jika rendering template email bermasalah
            try {
                Mail::raw("Kode OTP Pemulihan Kata Sandi Akun Anda: {$otp}. Berlaku selama {$expiryMinutes} menit.", function ($msg) use ($user) {
                    $msg->to($user->email)->subject('Kode OTP Pemulihan Kata Sandi - PT META Adhya Tirta Umbulan');
                });
                $sendSuccess = true;
            } catch (\Exception $ex2) {
                $sendSuccess = false;
                $sendErrorMessage = 'Layanan pengiriman email sedang tidak dapat dijangkau. Silakan hubungi IT Helpdesk.';
            }
        }

        if (!$sendSuccess) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $sendErrorMessage ?: 'Gagal mengirimkan kode OTP. Silakan coba metode lain.',
                ], 500);
            }
            return back()->withErrors(['channel' => $sendErrorMessage]);
        }

        // Catat hitung rate limiter
        RateLimiter::hit($ipThrottleKey, 300);
        RateLimiter::hit($userThrottleKey, 300);

        // Simpan hash OTP dan metadata ke sesi dan cache
        $hashedOtp = Hash::make($otp);
        $expiresAtTimestamp = now()->addMinutes($expiryMinutes)->timestamp;
        $nextResendAllowed = now()->addSeconds(60)->timestamp;

        session([
            'reset_user_id' => $user->id,
            'reset_channel' => $channel,
            'reset_target_masked' => $targetMasked,
            'reset_otp_hash' => $hashedOtp,
            'reset_otp_expires_at' => $expiresAtTimestamp,
            'reset_otp_resend_allowed_at' => $nextResendAllowed,
            'reset_attempts' => 0,
        ]);

        Cache::put("pwd_reset_otp_{$user->id}", [
            'hash' => $hashedOtp,
            'expires_at' => $expiresAtTimestamp,
            'attempts' => 0,
            'channel' => $channel,
        ], now()->addMinutes(10));

        $successMsg = "Kode OTP 6 digit berhasil dikirimkan ke {$targetMasked} via " . ucfirst($channel) . ".";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => $successMsg,
                'redirect_url' => route('forgot.verify_otp_view'),
                'channel' => $channel,
                'target_masked' => $targetMasked,
                'cooldown_seconds' => 60,
            ]);
        }

        return redirect()->route('forgot.verify_otp_view')->with('success', $successMsg);
    }

    // Menampilkan halaman antarmuka verifikasi kode OTP 6 digit
    public function showVerifyOtpForm(Request $request): View|RedirectResponse
    {
        $userId = session('reset_user_id');
        $expiresAt = session('reset_otp_expires_at');

        if (!$userId || !$expiresAt || now()->timestamp > $expiresAt) {
            return redirect()->route('forgot')->withErrors([
                'identity' => 'Sesi pemulihan akun Anda telah kedaluwarsa atau belum dimulai. Silakan masukkan kembali identitas akun Anda.',
            ]);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('forgot');
        }

        $channel = session('reset_channel', 'email');
        $targetMasked = session('reset_target_masked', '');
        $resendAllowedAt = session('reset_otp_resend_allowed_at', 0);
        $cooldownSeconds = max(0, $resendAllowedAt - now()->timestamp);

        return view('auth.verify-otp', compact('user', 'channel', 'targetMasked', 'cooldownSeconds'));
    }

    // Memvalidasi kode OTP 6 digit yang dimasukkan pengguna
    public function verifyOtp(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Silakan masukkan 6 digit kode OTP yang Anda terima.',
            'otp.size' => 'Kode OTP harus terdiri dari tepat 6 digit angka.',
        ]);

        $userId = session('reset_user_id');
        $sessionOtpHash = session('reset_otp_hash');
        $expiresAt = session('reset_otp_expires_at');
        $attempts = (int) session('reset_attempts', 0);

        if (!$userId || !$sessionOtpHash || !$expiresAt) {
            $msg = 'Sesi OTP tidak ditemukan atau telah kedaluwarsa. Silakan ulangi langkah pemulihan akun.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg, 'redirect_url' => route('forgot')], 422);
            }
            return redirect()->route('forgot')->withErrors(['identity' => $msg]);
        }

        // Batas maksimal 5x percobaan gagal (proteksi brute force)
        if ($attempts >= 5) {
            session()->forget(['reset_otp_hash', 'reset_otp_expires_at']);
            Cache::forget("pwd_reset_otp_{$userId}");

            $msg = 'Anda telah memasukkan kode OTP yang salah sebanyak 5 kali. Demi keamanan, kode OTP telah dibatalkan. Silakan kirim ulang kode OTP baru.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg], 429);
            }
            return back()->withErrors(['otp' => $msg]);
        }

        // Periksa apakah waktu OTP telah lewat batas 5 menit
        if (now()->timestamp > $expiresAt) {
            $msg = 'Kode OTP telah kedaluwarsa. Silakan klik tombol Kirim Ulang OTP.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg], 422);
            }
            return back()->withErrors(['otp' => $msg]);
        }

        // [H-02 FIX] Validasi kesesuaian OTP HANYA dengan Hash::check(), hapus raw hash fallback
        if (!Hash::check($request->otp, $sessionOtpHash)) {
            $attempts++;
            session(['reset_attempts' => $attempts]);

            $remainingAttempts = max(0, 5 - $attempts);
            $msg = $remainingAttempts > 0 
                ? "Kode OTP yang Anda masukkan salah. Sisa kesempatan: {$remainingAttempts} kali." 
                : "Kode OTP salah. Anda telah mencapai batas maksimal percobaan.";

            Log::warning("[ForgotPassword] Percobaan OTP gagal ke-{$attempts} untuk User ID {$userId} dari IP {$request->ip()}");

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg, 'remaining' => $remainingAttempts], 400);
            }
            return back()->withErrors(['otp' => $msg]);
        }

        // [H-01 FIX] Regenerate session ID untuk mencegah session fixation
        $request->session()->regenerate();

        // Otorisasi token pemulihan setelah OTP valid
        $authToken = Str::random(40);
        session([
            'reset_authorized_user_id' => $userId,
            'reset_auth_token' => $authToken,
            'reset_auth_expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        // Hapus kode OTP agar tidak dapat digunakan ulang
        session()->forget(['reset_otp_hash', 'reset_otp_expires_at', 'reset_attempts']);
        Cache::forget("pwd_reset_otp_{$userId}");

        Log::info("[ForgotPassword] OTP berhasil diverifikasi untuk User ID {$userId}");

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Verifikasi OTP berhasil! Silakan tentukan kata sandi baru Anda.',
                'redirect_url' => route('forgot.reset_password_view'),
            ]);
        }

        return redirect()->route('forgot.reset_password_view')->with('success', 'Kode OTP terverifikasi! Silakan masukkan kata sandi baru Anda.');
    }

    // Mengirimkan kembali kode OTP pemulihan kata sandi
    public function resendOtp(Request $request): JsonResponse|RedirectResponse
    {
        $userId = session('reset_user_id');
        if (!$userId) {
            return redirect()->route('forgot');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('forgot');
        }

        $request->merge(['user_id' => $user->id, 'channel' => 'email']);
        return $this->sendOtp($request);
    }

    // Menampilkan formulir input kata sandi baru
    public function showResetPasswordForm(Request $request): View|RedirectResponse
    {
        $userId = session('reset_authorized_user_id');
        $authToken = session('reset_auth_token');
        $authExpiresAt = session('reset_auth_expires_at');

        if (!$userId || !$authToken || !$authExpiresAt || now()->timestamp > $authExpiresAt) {
            return redirect()->route('forgot')->withErrors([
                'identity' => 'Sesi perubahan kata sandi telah habis. Silakan ulangi langkah pemulihan.',
            ]);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('forgot');
        }

        return view('auth.reset-password', compact('user'));
    }

    // Menyimpan pembaruan kata sandi baru ke database
    public function resetPassword(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi harus terdiri dari minimal 8 karakter.',
        ]);

        $userId = session('reset_authorized_user_id');
        $authToken = session('reset_auth_token');
        $authExpiresAt = session('reset_auth_expires_at');

        if (!$userId || !$authToken || !$authExpiresAt || now()->timestamp > $authExpiresAt) {
            $msg = 'Sesi perubahan kata sandi Anda telah kedaluwarsa. Silakan ulangi proses pemulihan.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg, 'redirect_url' => route('forgot')], 422);
            }
            return redirect()->route('forgot')->withErrors(['identity' => $msg]);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        // Update password hash baru
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        Log::info("[ForgotPassword] Kata sandi berhasil diperbarui untuk User ID {$user->id} ({$user->email}) dari IP {$request->ip()}");

        // Bersihkan seluruh sesi pemulihan kata sandi
        session()->forget([
            'reset_candidate_id',
            'reset_user_id',
            'reset_channel',
            'reset_target_masked',
            'reset_otp_hash',
            'reset_otp_expires_at',
            'reset_otp_resend_allowed_at',
            'reset_attempts',
            'reset_authorized_user_id',
            'reset_auth_token',
            'reset_auth_expires_at',
        ]);

        $successMessage = 'Kata sandi akun Anda berhasil diperbarui! Silakan masuk menggunakan kata sandi baru Anda.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => $successMessage,
                'redirect_url' => route('login'),
            ]);
        }

        return redirect()->route('login')->with('success', $successMessage);
    }

    // ==========================================================
    // HELPER UTILITIES
    // ==========================================================

    // Mencari akun pengguna berdasarkan email atau NIP
    protected function findUserByIdentity(string $identity): ?User
    {
        // Cari langsung jika format email
        if (str_contains($identity, '@')) {
            return User::where('email', strtolower($identity))->first();
        }

        // Pencarian melalui NIP
        return User::where('nip', $identity)->first();
    }

    // Menyamarkan alamat email untuk privasi (contoh: y***@meta.com)
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) < 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        $len = strlen($name);
        if ($len <= 2) {
            $maskedName = substr($name, 0, 1) . '***';
        } else {
            $maskedName = substr($name, 0, 2) . str_repeat('*', max(3, $len - 2));
        }

        return $maskedName . '@' . $domain;
    }

    // Mengambil inisial nama untuk avatar badge
    protected function getInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            if (!empty($w)) {
                $initials .= strtoupper($w[0]);
            }
        }
        return $initials ?: 'U';
    }
}
