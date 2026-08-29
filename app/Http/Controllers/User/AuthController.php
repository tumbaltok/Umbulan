<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Menampilkan form pendaftaran (registrasi) pengguna WEB.
     */
    public function showRegisterForm()
    {
        $daftarStasiun = Station::where('type', '!=', 'rumah_meter')
            ->orderBy('type', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $daftarRole = Role::where('role_name', 'NOT LIKE', '%admin%')
            ->orderBy('role_name', 'asc')
            ->get();

        $daftarRumahMeter = Station::where('type', 'rumah_meter')
            ->orderBy('kode_stasiun', 'asc')
            ->get();

        return view('auth.register', compact('daftarStasiun', 'daftarRole', 'daftarRumahMeter'));
    }

    /**
     * Menampilkan form lupa kata sandi (forgot password) pengguna WEB.
     */
    public function showForgotForm()
    {
        return view('auth.forgot');
    }

    /**
     * Menangani pendaftaran (registrasi) pengguna lewat WEB.
     */
    public function registerWeb(Request $request)
    {
        // 1. ATURAN VALIDASI MULTI-ROLE & DATA UTAMA
        $request->validate([
            'nip' => 'nullable|string|max:50|unique:users,nip',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'roles' => 'required_without:role_id|array|min:1',
            'roles.*' => 'exists:roles,id',
            'role_id' => 'nullable|exists:roles,id',
            'gender_id' => 'required|exists:genders,id',
            'station_id' => 'required|exists:stations,id',
            'assigned_stations' => 'nullable|array',
            'assigned_stations.*' => 'exists:stations,id',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        // Ambil daftar ID role terpilih
        $roleIds = [];
        if ($request->has('roles') && is_array($request->roles)) {
            $roleIds = array_map('intval', $request->roles);
        } elseif ($request->filled('role_id')) {
            $roleIds = [(int)$request->role_id];
        }

        $primaryRoleId = !empty($roleIds) ? $roleIds[0] : null;

        // 2. SIMPAN USER BARU
        $user = User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $primaryRoleId,
            'gender_id' => $request->gender_id,
            'station_id' => $request->station_id,
            'password' => Hash::make($request->password),
        ]);

        // 3. SINKRONISASI SELURUH ROLES KE PIVOT ROLE_USER
        if (!empty($roleIds)) {
            $syncData = [];
            foreach ($roleIds as $idx => $rId) {
                $syncData[$rId] = ['is_primary' => ($idx === 0)];
            }
            $user->roles()->sync($syncData);
        }

        // 4. SINKRONISASI RUMAH METER JIKA USER MEMILIKI ROLE PIPELINE
        $isPipelineRole = Role::whereIn('id', $roleIds)
            ->where(function ($q) {
                $q->where('role_name', 'LIKE', '%PIPELINE%')
                  ->orWhere('id', 14);
            })->exists();

        if ($isPipelineRole && $request->has('assigned_stations')) {
            $user->assignedStations()->sync($request->assigned_stations ?? []);
        }

        // Audit Log Registrasi
        Log::info("User baru berhasil terdaftar (Multi-Role): ID {$user->id}, Email: {$user->email}, Roles: " . implode(',', $roleIds));

        // Kirim Notifikasi Verifikasi Email Bawaan Laravel via Registered Event
        event(new Registered($user));

        // Arahkan ke Halaman Login
        return redirect()->route('login')->with('success', 'Pendaftaran berhasil! Silakan periksa email Anda untuk melakukan verifikasi akun sebelum login.');
    }

    /**
     * Menangani login untuk pengguna lewat WEB.
     */
    public function loginWeb(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Proteksi Rate Limiting Login (Maksimal 5x percobaan per menit)
        $throttleKey = 'login-attempt:'.strtolower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Log::warning("Terlalu banyak percobaan login gagal dari IP: {$request->ip()}, Email: {$request->email}");

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->withInput($request->only('email'));
        }

        // Opsi Remember Me aktif secara default untuk sesi panjang
        $remember = $request->boolean('remember', true);

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            Log::info("User ID ".Auth::id()." berhasil login dari IP: {$request->ip()}");

            return redirect()->intended('/dashboard');
        }

        RateLimiter::hit($throttleKey, 60);
        Log::warning("Gagal login untuk email: {$request->email} dari IP: {$request->ip()}");

        return back()->withErrors([
            'email' => 'Kombinasi Email atau Password salah!',
        ])->withInput($request->only('email'));
    }

    /**
     * 1. KIRIM OTP KE EMAIL (AJAX) - Pencegahan Email Enumeration & Hashing OTP
     */
    public function sendOtpMailWeb(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Rate limiting OTP Email (Maksimal 3x percobaan per 5 menit)
        $throttleKey = 'send-otp-email:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terlalu banyak permintaan OTP. Silakan tunggu beberapa menit.',
            ], 429);
        }
        RateLimiter::hit($throttleKey, 300);

        $user = User::where('email', $request->email)->first();

        // Kriptografi Kuat
        $otp = random_int(100000, 999999);

        if ($user) {
            // Hash OTP sebelum disimpan ke session untuk keamanan
            session([
                'reset_email' => $request->email,
                'reset_otp_hash' => Hash::make($otp),
                'reset_otp_expires' => now()->addMinutes(5),
            ]);

            try {
                Mail::raw('Kode OTP Pemulihan Akun Anda: '.$otp, function ($message) use ($request) {
                    $message->to($request->email)->subject('Kode OTP Lupa Password');
                });
                Log::info("OTP Reset Password berhasil dikirim ke email: {$request->email}");
            } catch (\Exception $e) {
                Log::error("Gagal mengirim email OTP ke {$request->email}: ".$e->getMessage());
            }
        }

        // Mengembalikan respon generik seragam untuk mencegah Email Enumeration
        return response()->json([
            'status' => 'success',
            'message' => 'Jika email Anda terdaftar dalam sistem, kami telah mengirimkan kode OTP ke email tersebut.',
        ]);
    }

    /**
     * Alias untuk sendOtpMailWeb agar kompatibel dengan rute forgot.send_otp.
     */
    public function sendOtpWeb(Request $request)
    {
        return $this->sendOtpMailWeb($request);
    }

    /**
     * 2. VERIFIKASI OTP EMAIL
     */
    public function verifyOtpMailWeb(Request $request)
    {
        $request->validate(['email' => 'required|email', 'otp' => 'required|numeric']);

        $sessionOtpHash = session('reset_otp_hash');
        $sessionEmail = session('reset_email');
        $sessionExpires = session('reset_otp_expires');

        if (! $sessionOtpHash || $sessionEmail !== $request->email || now()->greaterThan(Carbon::parse($sessionExpires))) {
            return response()->json(['status' => 'error', 'message' => 'Kode OTP sudah kedaluwarsa atau tidak valid.'], 400);
        }

        if (! Hash::check($request->otp, $sessionOtpHash)) {
            Log::warning("Gagal verifikasi OTP email untuk: {$request->email} dari IP: {$request->ip()}");
            return response()->json(['status' => 'error', 'message' => 'Kode OTP salah.'], 400);
        }

        session([
            'otp_verified_for' => $request->email,
            'otp_verified_expires' => now()->addMinutes(10),
        ]);

        return response()->json(['status' => 'success', 'message' => 'OTP Benar! Silakan masukkan kata sandi baru.']);
    }

    /**
     * 3. SIMPAN PASSWORD BARU
     */
    public function forgotWeb(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $verifiedEmail = session('otp_verified_for');
        $verifiedExpires = session('otp_verified_expires');

        if (! $verifiedEmail || $verifiedEmail !== $request->email || now()->greaterThan(Carbon::parse($verifiedExpires))) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aksi tidak valid atau batas waktu verifikasi OTP telah habis.',
                ], 422);
            }

            return redirect()->route('forgot')->withErrors([
                'email' => 'Aksi tidak valid atau batas waktu verifikasi OTP telah habis.',
            ]);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
            Log::info("Kata sandi berhasil di-reset untuk email: {$request->email} dari IP: {$request->ip()}");
        }

        session()->forget(['reset_email', 'reset_otp_hash', 'reset_otp_expires', 'otp_verified_for', 'otp_verified_expires']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kata sandi berhasil diperbarui. Silakan login.',
            ]);
        }

        return redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui. Silakan login.');
    }

    /**
     * Menangani fungsi logout untuk WEB.
     * Membersihkan autentikasi, remember_token database, session token, serta cookie.
     */
    public function logoutWeb(Request $request)
    {
        $user = Auth::guard('web')->user();

        if ($user) {
            Log::info("User ID {$user->id} ({$user->email}) melakukan logout.");
            // Reset remember token di database agar token lama tidak dapat disalahgunakan
            $user->setRememberToken(null);
            $user->saveQuietly();
        }

        $recallerName = Auth::guard('web')->getRecallerName();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => 'Sun, 02 Jan 1990 00:00:00 GMT',
            ])
            ->withoutCookie($recallerName);
    }
}
