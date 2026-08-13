<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Jobdesk;
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
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Menampilkan form pendaftaran (registrasi) pengguna WEB.
     */
    public function showRegisterForm()
    {
        $daftarStasiun = Station::whereIn('type', ['kantor', 'stasiun'])
            ->orderBy('type', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $daftarRole = Role::where('role_name', 'NOT LIKE', '%admin%')
            ->orderBy('role_name', 'asc')
            ->get();

        $daftarJobdesk = Jobdesk::orderBy('job_title', 'asc')->get();

        return view('auth.register', compact('daftarStasiun', 'daftarRole', 'daftarJobdesk'));
    }

    /**
     * Menangani pendaftaran (registrasi) pengguna lewat WEB.
     */
    public function registerWeb(Request $request)
    {
        $request->validate([
            'nip' => 'nullable|string|max:50|unique:users,nip',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'gender_id' => 'required|exists:genders,id',
            'station_id' => 'required|exists:stations,id',
            'sektor' => 'required|in:manajemen,operasional',
            'jobdesk' => 'required|string|max:100',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $sektorInput = strtolower($request->sektor);
        $roleSelected = Role::find($request->role_id);
        $roleName = strtolower($roleSelected->role_name ?? '');

        $supervisorId = null;
        $managerId = null;

        // -------------------------------------------------------------
        // LOGIKA PENENTUAN ATASAN LANGSUNG BERJENJANG (LINIER)
        // -------------------------------------------------------------
        if (str_contains($roleName, 'staff')) {
            $supervisor = User::whereHas('role', function ($q) {
                $q->where('role_name', 'LIKE', '%Supervisor%');
            })
                ->where('sektor', $sektorInput)
                ->where('station_id', $request->station_id)
                ->where('job_title', $request->jobdesk)
                ->first();

            if (! $supervisor) {
                $supervisor = User::whereHas('role', function ($q) {
                    $q->where('role_name', 'LIKE', '%Supervisor%');
                })
                    ->where('sektor', $sektorInput)
                    ->where('station_id', $request->station_id)
                    ->first();
            }

            $supervisorId = $supervisor ? $supervisor->id : null;

            $manager = User::whereHas('role', function ($q) {
                $q->where('role_name', 'LIKE', '%Manager%');
            })
                ->where('sektor', $sektorInput)
                ->first();

            $managerId = $manager ? $manager->id : null;

        } elseif (str_contains($roleName, 'supervisor')) {
            $manager = User::whereHas('role', function ($q) {
                $q->where('role_name', 'LIKE', '%Manager%');
            })
                ->where('sektor', $sektorInput)
                ->first();

            $supervisorId = null;
            $managerId = $manager ? $manager->id : null;
        }

        // 1. Buat User Baru
        $user = User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'gender_id' => $request->gender_id,
            'station_id' => $request->station_id,
            'sektor' => $sektorInput,
            'job_title' => $request->jobdesk,
            'supervisor_id' => $supervisorId,
            'manager_id' => $managerId,
            'password' => Hash::make($request->password),
        ]);

        // Audit Log Registrasi
        Log::info("User baru berhasil terdaftar: ID {$user->id}, Email: {$user->email}, IP: {$request->ip()}");

        // Kirim Notifikasi Verifikasi Email Bawaan Laravel
        $user->sendEmailVerificationNotification();

        // Arahkan ke Halaman Login dengan Pesan Petunjuk Verifikasi Email
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

        $remember = $request->boolean('remember');

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
            return response()->json([
                'status' => 'error',
                'message' => 'Aksi tidak valid atau batas waktu verifikasi OTP telah habis.',
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
            Log::info("Kata sandi berhasil di-reset untuk email: {$request->email} dari IP: {$request->ip()}");
        }

        session()->forget(['reset_email', 'reset_otp_hash', 'reset_otp_expires', 'otp_verified_for', 'otp_verified_expires']);

        return response()->json([
            'status' => 'success',
            'message' => 'Kata sandi berhasil diperbarui. Silakan login.',
        ]);
    }

    /**
     * 1. Fungsi untuk mengirim OTP via WhatsApp (Fonnte) dengan Checking Ownership & Rate Limiting
     */
    public function sendOtpPhone(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|numeric|digits_between:10,14',
        ]);

        $phone = $request->phone_number;

        // Rate limiting kirim WA (Maksimal 3x percobaan per 5 menit)
        $throttleKey = 'send-otp-phone:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak permintaan OTP WhatsApp. Silakan tunggu beberapa menit.',
            ], 429);
        }
        RateLimiter::hit($throttleKey, 300);

        // Ownership Check: Pastikan nomor telepon belum dipakai oleh akun user lain
        $isPhoneTaken = User::where('phone_number', $phone)
            ->where('id', '!=', Auth::id())
            ->exists();

        if ($isPhoneTaken) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor telepon tersebut sudah terdaftar pada akun lain.',
            ], 422);
        }

        // Kriptografi Kuat
        $otp = random_int(100000, 999999);

        session([
            'otp_phone_hash' => Hash::make($otp),
            'otp_phone' => $phone,
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        $message = "Kode verifikasi (OTP) Anda adalah: *{$otp}*.\nJangan bagikan kode ini kepada siapapun. Kode berlaku selama 5 menit.";
        $fonnteToken = config('services.fonnte.token');

        try {
            $response = Http::withHeaders([
                'Authorization' => $fonnteToken,
            ])->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => $message,
                'all' => 'true',
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['status']) && $result['status'] == true) {
                    Log::info("OTP WA berhasil dikirim ke nomor {$phone} oleh User ID: ".Auth::id());
                    return response()->json(['success' => true, 'message' => 'Kode OTP berhasil dikirim ke WhatsApp Anda!']);
                }

                return response()->json(['success' => false, 'message' => $result['reason'] ?? 'Gagal mengirim pesan dari gateway.'], 422);
            }
        } catch (\Exception $e) {
            Log::error("Gagal terhubung ke Fonnte API: ".$e->getMessage());
        }

        return response()->json(['success' => false, 'message' => 'Gagal terhubung ke server WhatsApp. Coba lagi nanti.'], 500);
    }

    /**
     * 2. Fungsi untuk mencocokkan OTP via HP
     */
    public function verifyOtpPhone(Request $request)
    {
        $request->validate([
            'otp_input' => 'required|numeric|digits:6',
        ]);

        $sessionOtpHash = session('otp_phone_hash');
        $sessionExpires = session('otp_expires_at');

        if (! $sessionOtpHash || now()->isAfter(Carbon::parse($sessionExpires))) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah kedaluwarsa atau tidak valid. Silakan kirim ulang.',
            ], 422);
        }

        if (Hash::check($request->otp_input, $sessionOtpHash)) {
            if (Auth::check()) {
                User::where('id', Auth::id())->update([
                    'phone_number' => session('otp_phone'),
                    'phone_verified_at' => now(),
                ]);
                Log::info("Nomor WhatsApp berhasil diverifikasi untuk User ID: ".Auth::id());
            }

            session()->forget(['otp_phone_hash', 'otp_phone', 'otp_expires_at']);

            return response()->json([
                'success' => true,
                'message' => 'Nomor telepon berhasil diverifikasi!',
            ]);
        }

        Log::warning("Gagal verifikasi OTP HP dari IP: {$request->ip()}");

        return response()->json([
            'success' => false,
            'message' => 'Kode OTP yang Anda masukkan salah.',
        ], 422);
    }

    /**
     * Menangani fungsi logout untuk WEB.
     */
    public function logoutWeb(Request $request)
    {
        if (Auth::check()) {
            Log::info("User ID ".Auth::id()." melakukan logout.");
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}