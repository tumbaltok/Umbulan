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
    // Menampilkan halaman pendaftaran (registrasi) pengguna baru
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

    // Menampilkan formulir pemulihan kata sandi
    public function showForgotForm()
    {
        return view('auth.forgot');
    }

    // Memproses registrasi akun karyawan baru
    public function registerWeb(Request $request)
    {
        // 1. ATURAN VALIDASI MULTI-ROLE & DATA UTAMA
        $request->validate([
            'nip' => 'nullable|string|max:50|unique:users,nip',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone_number' => 'required|numeric|digits_between:10,15|unique:users,phone_number',
            'roles' => 'required_without:role_id|array|min:1',
            'roles.*' => 'exists:roles,id',
            'role_id' => 'nullable|exists:roles,id',
            'gender_id' => 'required|exists:genders,id',
            'station_id' => 'required|exists:stations,id',
            'assigned_stations' => 'nullable|array',
            'assigned_stations.*' => 'exists:stations,id',
            'schedule_type' => 'required|in:reguler_5_hari,reguler_6_hari,roster',
            'current_shift_choice' => 'required_if:schedule_type,roster|nullable|in:pagi,malam,libur',
            'roster_start_date' => 'nullable|date_format:Y-m-d',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'phone_number.required' => 'Nomor WhatsApp wajib diisi.',
            'phone_number.numeric' => 'Nomor WhatsApp hanya boleh berupa angka.',
            'phone_number.digits_between' => 'Nomor WhatsApp harus terdiri dari 10 hingga 15 digit angka.',
            'phone_number.unique' => 'Nomor WhatsApp ini telah terdaftar pada akun karyawan lain.',
            'schedule_type.required' => 'Silakan tentukan jadwal kerja Anda.',
            'schedule_type.in' => 'Tipe jadwal kerja yang dipilih tidak valid.',
            'current_shift_choice.required_if' => 'Silakan tentukan shift awal untuk jadwal roster.',
            'current_shift_choice.in' => 'Pilihan shift awal roster tidak valid.',
        ]);

        // Ambil daftar ID role terpilih
        $roleIds = [];
        if ($request->has('roles') && is_array($request->roles)) {
            $roleIds = array_map('intval', $request->roles);
        } elseif ($request->filled('role_id')) {
            $roleIds = [(int)$request->role_id];
        }

        $primaryRoleId = !empty($roleIds) ? $roleIds[0] : null;

        // Tentukan konfigurasi jadwal kerja berdasarkan tipe yang dipilih
        $scheduleType = $request->schedule_type;
        $normalWorkDays = null;
        $normalCheckIn = null;
        $normalCheckOut = null;
        $rosterStartDate = null;

        if ($scheduleType === 'reguler_5_hari') {
            $normalWorkDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
            $normalCheckIn = '07:00:00';
            $normalCheckOut = '16:00:00';
        } elseif ($scheduleType === 'reguler_6_hari') {
            $normalWorkDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $normalCheckIn = '07:00:00';
            $normalCheckOut = '16:00:00';
        } elseif ($scheduleType === 'roster') {
            $selectedShift = $request->input('current_shift_choice');
            if ($selectedShift) {
                $now = Carbon::now('Asia/Jakarta');
                $currentTuesday = $now->copy()->startOfWeek(Carbon::TUESDAY)->setTime(7, 0, 0);

                if ($now->dayOfWeekIso === Carbon::TUESDAY && $now->lt($currentTuesday)) {
                    $currentTuesday->subWeek();
                }

                if ($currentTuesday->gt($now)) {
                    $currentTuesday->subWeek();
                }

                switch ($selectedShift) {
                    case 'pagi':
                        $rosterStartDate = $currentTuesday->copy()->format('Y-m-d');
                        break;
                    case 'malam':
                        $rosterStartDate = $currentTuesday->copy()->subWeek()->format('Y-m-d');
                        break;
                    case 'libur':
                        $rosterStartDate = $currentTuesday->copy()->subWeeks(2)->format('Y-m-d');
                        break;
                }
            } elseif ($request->filled('roster_start_date')) {
                $rosterStartDate = Carbon::parse($request->roster_start_date, 'Asia/Jakarta')->format('Y-m-d');
            } else {
                $rosterStartDate = Carbon::now('Asia/Jakarta')->startOfWeek(Carbon::TUESDAY)->format('Y-m-d');
            }
        }

        // 2. SIMPAN USER BARU (Semua pendaftar web tanpa terkecuali murni Level 3 / Staf Biasa demi keamanan)
        $user = User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role_id' => $primaryRoleId,
            'level' => 3,
            'gender_id' => $request->gender_id,
            'station_id' => $request->station_id,
            'schedule_type' => $scheduleType,
            'normal_work_days' => $normalWorkDays,
            'normal_check_in' => $normalCheckIn,
            'normal_check_out' => $normalCheckOut,
            'roster_start_date' => $rosterStartDate,
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

    // Menangani autentikasi login pengguna via Web
    public function loginWeb(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Proteksi batas percobaan login (maksimal 5x per menit)
        $throttleKey = 'login-attempt:'.strtolower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Log::warning("Terlalu banyak percobaan login gagal dari IP: {$request->ip()}, Email: {$request->email}");

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->withInput($request->only('email'));
        }

        // Opsi Remember Me aktif default
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

    // Mengirimkan kode OTP pemulihan kata sandi ke email pengguna (AJAX)
    public function sendOtpMailWeb(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Rate limiting kirim OTP email
        $throttleKey = 'send-otp-email:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terlalu banyak permintaan OTP. Silakan tunggu beberapa menit.',
            ], 429);
        }
        RateLimiter::hit($throttleKey, 300);

        $user = User::where('email', $request->email)->first();

        // Generate OTP 6 digit
        $otp = random_int(100000, 999999);

        if ($user) {
            // Simpan hash OTP di sesi
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

        // Respon seragam untuk mencegah enumerasi email
        return response()->json([
            'status' => 'success',
            'message' => 'Jika email Anda terdaftar dalam sistem, kami telah mengirimkan kode OTP ke email tersebut.',
        ]);
    }

    // Alias fungsi pengiriman OTP email
    public function sendOtpWeb(Request $request)
    {
        return $this->sendOtpMailWeb($request);
    }

    // Memverifikasi kode OTP email untuk alur reset kata sandi
    public function verifyOtpMailWeb(Request $request)
    {
        $request->validate(['email' => 'required|email', 'otp' => 'required|numeric']);

        // [L-02 FIX] Rate limiting percobaan verifikasi OTP email (maksimal 5 kali percobaan per menit)
        $throttleKey = 'verify-otp-email:' . strtolower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'status'  => 'error',
                'message' => "Terlalu banyak percobaan verifikasi OTP yang salah. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        $sessionOtpHash = session('reset_otp_hash');
        $sessionEmail = session('reset_email');
        $sessionExpires = session('reset_otp_expires');

        if (! $sessionOtpHash || $sessionEmail !== $request->email || now()->greaterThan(Carbon::parse($sessionExpires))) {
            return response()->json(['status' => 'error', 'message' => 'Kode OTP sudah kedaluwarsa atau tidak valid.'], 400);
        }

        if (! Hash::check($request->otp, $sessionOtpHash)) {
            RateLimiter::hit($throttleKey, 60);
            Log::warning("Gagal verifikasi OTP email untuk: {$request->email} dari IP: {$request->ip()}");
            return response()->json(['status' => 'error', 'message' => 'Kode OTP salah.'], 400);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        session([
            'otp_verified_for' => $request->email,
            'otp_verified_expires' => now()->addMinutes(10),
        ]);

        return response()->json(['status' => 'success', 'message' => 'OTP Benar! Silakan masukkan kata sandi baru.']);
    }

    // Menyimpan kata sandi baru setelah OTP email terverifikasi
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

    // Menangani proses keluar (logout) dan pembersihan token sesi
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
