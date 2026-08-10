<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User\User;
use App\Models\User\Station;
use App\Models\User\Role;
use App\Models\User\Jobdesk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    /**
     * Menampilkan form pendaftaran (registrasi) pengguna WEB.
     */
    public function showRegisterForm()
    {
        // Hanya tampilkan lokasi bertipe 'kantor' atau 'stasiun' (Rumah Meter disembunyikan)
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
            'nip'         => 'nullable|string|max:50|unique:users,nip',
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users,email',
            'role_id'     => 'required|exists:roles,id',
            'gender_id'   => 'required|exists:genders,id',
            'station_id'  => 'required|exists:stations,id',
            'sektor'      => 'required|in:manajemen,operasional',
            'jobdesk'     => 'required|string|max:100',
            'password'    => 'required|string|min:8|confirmed',
        ]);

        $sektorInput = strtolower($request->sektor);
        $roleSelected = Role::find($request->role_id);
        $roleName = strtolower($roleSelected->role_name ?? '');

        $supervisorId = null;
        $managerId    = null;

        // -------------------------------------------------------------
        // LOGIKA PENENTUAN ATASAN LANGSUNG BERJENJANG (LINIER)
        // -------------------------------------------------------------
        if (str_contains($roleName, 'staff')) {
            // 1. CARI SUPERVISOR LINIER (Sektor + Tempat Kerja + Job Title/Jobdesk SAMA)
            $supervisor = User::whereHas('role', function($q) {
                    $q->where('role_name', 'LIKE', '%Supervisor%');
                })
                ->where('sektor', $sektorInput)
                ->where('station_id', $request->station_id)
                ->where(function($q) use ($request) {
                    $q->where('job_title', $request->jobdesk)
                      ->orWhere('jobdesk', $request->jobdesk);
                })
                ->first();

            // Fallback: Jika tidak ada Supervisor dengan jobdesk yang sama, cari Supervisor di Sektor & Tempat Kerja yang sama
            if (!$supervisor) {
                $supervisor = User::whereHas('role', function($q) {
                        $q->where('role_name', 'LIKE', '%Supervisor%');
                    })
                    ->where('sektor', $sektorInput)
                    ->where('station_id', $request->station_id)
                    ->first();
            }

            $supervisorId = $supervisor ? $supervisor->id : null;

            // Cari Manager Linier berdasarkan Sektor
            $manager = User::whereHas('role', function($q) {
                    $q->where('role_name', 'LIKE', '%Manager%');
                })
                ->where('sektor', $sektorInput)
                ->first();

            $managerId = $manager ? $manager->id : null;

        } elseif (str_contains($roleName, 'supervisor')) {
            // 2. UNTUK SUPERVISOR: Penentuan Manager BEBAS (Tidak butuh linier jobdesk, cukup Sektor)
            $manager = User::whereHas('role', function($q) {
                    $q->where('role_name', 'LIKE', '%Manager%');
                })
                ->where('sektor', $sektorInput)
                ->first();

            $supervisorId = null;
            $managerId    = $manager ? $manager->id : null;
        }

        // 1. Buat User Baru (Disimpan ke kolom 'job_title' agar sesuai struktur DB)
        $user = User::create([
            'nip'           => $request->nip,
            'name'          => $request->name,
            'email'         => $request->email,
            'role_id'       => $request->role_id,
            'gender_id'     => $request->gender_id,
            'station_id'    => $request->station_id,
            'sektor'        => $sektorInput,
            'job_title'     => $request->jobdesk, // PERBAIKAN: Disimpan ke kolom job_title
            'supervisor_id' => $supervisorId,
            'manager_id'    => $managerId,
            'password'      => Hash::make($request->password),
        ]);

        // 2. OTOMATIS LOGIN-KAN USER
        Auth::login($user);
        $request->session()->regenerate();

        // 3. DIRECT LANGSUNG KE DASHBOARD
        return redirect()->intended('/dashboard')->with('success', 'Pendaftaran berhasil! Selamat datang di Portal Cuti.');
    }

    /**
     * Menangani login untuk pengguna lewat WEB.
     */
    public function loginWeb(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Tangkap status centang checkbox 'remember' (menghasilkan boolean true/false)
        $remember = $request->boolean('remember');

        // Masukkan $remember sebagai parameter kedua pada Auth::attempt
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Kombinasi Email atau Password salah!',
        ])->withInput($request->only('email'));
    }

    // 1. KIRIM OTP KE EMAIL (AJAX)
    public function sendOtpMailWeb(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $userExists = DB::table('users')->where('email', $request->email)->exists();

        if (!$userExists) {
            return response()->json(['status' => 'error', 'message' => 'Email tidak terdaftar.'], 404);
        }

        $otp = rand(100000, 999999);
        session([
            'reset_email'       => $request->email,
            'reset_otp'         => $otp,
            'reset_otp_expires' => now()->addMinutes(5)
        ]);

        try {
            Mail::raw("Kode OTP Pemulihan Akun Anda: " . $otp, function ($message) use ($request) {
                $message->to($request->email)->subject("Kode OTP Lupa Password");
            });
            return response()->json(['status' => 'success', 'message' => 'Kode OTP berhasil dikirim ke email!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengirim email OTP.'], 500);
        }
    }

    // 2. VERIFIKASI OTP SAJA 
    public function verifyOtpMailWeb(Request $request)
    {
        $request->validate(['email' => 'required|email', 'otp' => 'required']);

        $sessionOtp = session('reset_otp');
        $sessionEmail = session('reset_email');
        $sessionExpires = session('reset_otp_expires');

        if (!$sessionOtp || $sessionEmail !== $request->email || now()->greaterThan(Carbon::parse($sessionExpires))) {
            return response()->json(['status' => 'error', 'message' => 'Kode OTP sudah kedaluwarsa atau tidak valid.'], 400);
        }

        if ($sessionOtp != $request->otp) {
            return response()->json(['status' => 'error', 'message' => 'Kode OTP salah.'], 400);
        }

        session(['otp_verified_for' => $request->email]);

        return response()->json(['status' => 'success', 'message' => 'OTP Benar! Silakan masukkan kata sandi baru.']);
    }

    // 3. SIMPAN PASSWORD BARU PILIHAN USER
    public function forgotWeb(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if (session('otp_verified_for') !== $request->email) {
            return redirect()->back()->withErrors(['email' => 'Aksi tidak valid atau verifikasi OTP gagal.']);
        }

        DB::table('users')->where('email', $request->email)->update([
            'password' => Hash::make($request->password),
        ]);

        session()->forget(['reset_email', 'reset_otp', 'reset_otp_expires', 'otp_verified_for']);

        return response()->json([
            'status' => 'success',
            'message' => 'Kata sandi berhasil diperbarui. Silakan login.',
        ]);
    }

    // 1. Fungsi untuk mengirim OTP via Fonnte (WhatsApp)
    public function sendOtpPhone(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|numeric|digits_between:10,14',
        ]);

        $phone = $request->phone_number;
        $otp   = rand(100000, 999999);

        session([
            'otp_code'       => $otp,
            'otp_phone'      => $phone,
            'otp_expires_at' => now()->addMinutes(5)
        ]);

        $message = "Kode verifikasi (OTP) Anda adalah: *{$otp}*.\nJangan bagikan kode ini kepada siapapun. Kode berlaku selama 5 menit.";

        $fonnteToken = config('services.fonnte.token') ?? env('FONNTE_TOKEN');

        $response = Http::withHeaders([
            'Authorization' => $fonnteToken,
        ])->post('https://api.fonnte.com/send', [
            'target'  => $phone,
            'message' => $message,
            'all'     => 'true'
        ]);

        if ($response->successful()) {
            $result = $response->json();
            if (isset($result['status']) && $result['status'] == true) {
                return response()->json(['success' => true, 'message' => 'Kode OTP berhasil dikirim ke WhatsApp Anda!']);
            }
            return response()->json(['success' => false, 'message' => $result['reason'] ?? 'Gagal mengirim pesan dari gateway.'], 422);
        }

        return response()->json(['success' => false, 'message' => 'Gagal terhubung ke server WhatsApp. Coba lagi nanti.'], 500);
    }

    // 2. Fungsi untuk mencocokkan OTP yang diinput user via HP
    public function verifyOtpPhone(Request $request)
    {
        $request->validate([
            'otp_input' => 'required|numeric|digits:6',
        ]);

        if (!session()->has('otp_code') || now()->isAfter(session('otp_expires_at'))) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah kedaluwarsa atau tidak valid. Silakan kirim ulang.'
            ], 422);
        }

        if (trim((string)$request->otp_input) === trim((string)session('otp_code'))) {

            if (Auth::check()) {
                User::where('id', Auth::id())->update([
                    'phone_number'      => session('otp_phone'),
                    'phone_verified_at' => now(),
                ]);
            }

            session()->forget(['otp_code', 'otp_phone', 'otp_expires_at']);

            return response()->json([
                'success' => true,
                'message' => 'Nomor telepon berhasil diverifikasi!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kode OTP yang Anda masukkan salah.'
        ], 422);
    }

    /**
     * Menangani fungsi logout untuk WEB.
     */
    public function logoutWeb(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}