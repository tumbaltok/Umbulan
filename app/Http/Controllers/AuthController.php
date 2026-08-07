<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Station;
use App\Models\Role;
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

        $daftarRole = Role::orderBy('role_name', 'asc')->get();

        return view('auth.register', compact('daftarStasiun', 'daftarRole'));
    }

    /**
     * Menangani pendaftaran (registrasi) pengguna lewat WEB.
     */
    public function registerWeb(Request $request)
    {
        $request->validate([
            'nip'        => 'nullable|string|max:50|unique:users,nip',
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users,email',
            'role_id'    => 'required|exists:roles,id',
            'gender_id'  => 'required|exists:genders,id',
            'station_id' => 'required|exists:stations,id',
            'sektor'     => 'required|in:manajemen,operasional',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        // 1. Buat User Baru
        $user = User::create([
            'nip'        => $request->nip,
            'name'       => $request->name,
            'email'      => $request->email,
            'role_id'    => $request->role_id,
            'gender_id'  => $request->gender_id,
            'station_id' => $request->station_id,
            'sektor'     => strtolower($request->sektor),
            'password'   => Hash::make($request->password),
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

        if (Auth::attempt($credentials)) {
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

    // 2. VERIFIKASI OTP SAJA (AJAX)
    public function verifyOtpMailWeb(Request $request)
    {
        $request->validate(['email' => 'required|email', 'otp' => 'required']);

        $sessionEmail   = session('reset_email');
        $sessionOtp     = session('reset_otp');
        $sessionExpires = session('reset_otp_expires');

        if (!$sessionOtp || $sessionEmail !== $request->email || now()->greaterThan(Carbon::parse($sessionExpires))) {
            return response()->json(['status' => 'error', 'message' => 'Kode OTP sudah kedaluwarsa atau tidak valid.'], 400);
        }

        if (trim((string)$sessionOtp) !== trim((string)$request->otp)) {
            return response()->json(['status' => 'error', 'message' => 'Kode OTP salah.'], 400);
        }

        session(['otp_verified_for' => $request->email]);

        return response()->json(['status' => 'success', 'message' => 'OTP Benar! Silakan masukkan kata sandi baru.']);
    }

    // 3. SIMPAN PASSWORD BARU PILIHAN USER
    public function forgotWeb(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if (session('otp_verified_for') !== $request->email) {
            return redirect()->back()->withErrors(['email' => 'Aksi tidak valid atau verifikasi OTP gagal.']);
        }

        DB::table('users')->where('email', $request->email)->update([
            'password'   => Hash::make($request->password),
            'updated_at' => now()
        ]);

        session()->forget(['reset_email', 'reset_otp', 'reset_otp_expires', 'otp_verified_for']);

        return response()->json([
            'status'       => 'success',
            'message'      => 'Kata sandi Anda berhasil diperbarui! Silakan login.',
            'redirect_url' => route('login')
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