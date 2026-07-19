<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'gender_id' => $request->gender_id,
            'station_id' => $request->station_id,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan masuk menggunakan akun baru Anda.');
    }

    /**
     * Menangani pendaftaran (registrasi) pengguna lewat API (Mobile App).
     */
    public function registerApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nip' => 'nullable|string|max:50|unique:users,nip',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'gender_id' => 'required|exists:genders,id',
            'station_id' => 'required|exists:stations,id',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        /** @var User $user */
        $user = User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'gender_id' => $request->gender_id,
            'station_id' => $request->station_id,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load('role');

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil lewat API!',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'nip' => $user->nip,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->role_name ?? null,
                'assigned_supervisor_id' => $user->supervisor_id ?? null,
                'assigned_manager_id' => $user->manager_id ?? null,
            ]
        ], 201);
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

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Kombinasi Email atau Password salah!',
        ])->withInput($request->only('email'));
    }

    /**
     * Menangani login untuk pengguna lewat API.
     */
    public function loginApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        /** @var User $user */
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kombinasi Email atau Password salah!'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login sukses lewat API!',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ], 200);
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
            'reset_email' => $request->email,
            'reset_otp' => $otp,
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

        $sessionEmail = session('reset_email');
        $sessionOtp = session('reset_otp');
        $sessionExpires = session('reset_otp_expires');

        if (!$sessionOtp || $sessionEmail !== $request->email || now()->greaterThan($sessionExpires)) {
            return response()->json(['status' => 'error', 'message' => 'Sesi habis atau OTP kadaluarsa.'], 400);
        }

        // PERBAIKAN: Ditambahkan trim() menghindari spasi tidak sengaja
        if (trim((string)$sessionOtp) !== trim((string)$request->otp)) {
            return response()->json(['status' => 'error', 'message' => 'Kode OTP salah.'], 400);
        }

        // PERBAIKAN: Kunci status verifikasi ke email spesifik agar lebih aman
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

        // PERBAIKAN: Validasi kecocokan email yang diverifikasi dengan input form
        if (session('otp_verified_for') !== $request->email) {
            return redirect()->back()->withErrors(['email' => 'Aksi tidak valid atau verifikasi OTP gagal.']);
        }

        DB::table('users')->where('email', $request->email)->update([
            'password' => Hash::make($request->password),
            'updated_at' => now()
        ]);

        session()->forget(['reset_email', 'reset_otp', 'reset_otp_expires', 'otp_verified_for']);

        return redirect()->route('login')->with('success', 'Kata sandi Anda berhasil diperbarui! Silakan login.');
    }

    // 1. Fungsi untuk mengirim OTP via Fonnte
    public function sendOtpPhone(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|numeric|digits_between:10,14',
        ]);

        $phone = $request->phone_number;
        $otp = rand(100000, 999999);

        session([
            'otp_code' => $otp,
            'otp_phone' => $phone,
            'otp_expires_at' => now()->addMinutes(5)
        ]);

        $message = "Kode verifikasi (OTP) Anda adalah: *{$otp}*.\nJangan bagikan kode ini kepada siapapun. Kode berlaku selama 5 menit.";

        // PERBAIKAN: Menggunakan config() alih-alih env() langsung
        $fonnteToken = config('services.fonnte.token') ?? env('FONNTE_TOKEN');

        $response = Http::withHeaders([
            'Authorization' => $fonnteToken,
        ])->post('https://api.fonnte.com/send', [
            'target' => $phone,
            'message' => $message,
            'all' => 'true'
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

        // PERBAIKAN: Ditambahkan trim() menghindari celah spasi kosong saat copy-paste
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

    /**
     * Menangani fungsi logout untuk API (Sanctum).
     */
    public function logoutApi(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user) {
            /** @var \Laravel\Sanctum\PersonalAccessToken $token */
            $token = $user->currentAccessToken();

            if ($token) {
                $token->delete();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil logout API, token telah dihapus.'
        ], 200);
    }
}
