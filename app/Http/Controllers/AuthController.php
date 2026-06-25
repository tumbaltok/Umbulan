<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Menangani pendaftaran (registrasi) pengguna lewat WEB.
     * Mengarahkan kembali ke halaman login setelah berhasil mendaftar.
     */
    public function registerWeb(Request $request)
    {
        // 1. Validasi Inputan sesuai skema database Anda
        $request->validate([
            'nip' => 'nullable|string|max:50|unique:users,nip',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'gender_id' => 'required|exists:genders,id',
            'station_id' => 'required|exists:stations,id',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Simpan Data ke Database
        $user = User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'gender_id' => $request->gender_id,
            'station_id' => $request->station_id,
            'password' => Hash::make($request->password),
        ]);

        // 3. Alihkan ke halaman login dengan pesan sukses
        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan masuk menggunakan akun baru Anda.');
    }

    /**
     * Menangani pendaftaran (registrasi) pengguna lewat API (Mobile App).
     * Mengembalikan response JSON beserta token Sanctum.
     */
    public function registerApi(Request $request)
    {
        // 1. Validasi Inputan API
        $request->validate([
            'nip' => 'nullable|string|max:50|unique:users,nip',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'gender_id' => 'required|exists:genders,id',
            'station_id' => 'required|exists:stations,id',
            'password' => 'required|string|min:8', // Konfirmasi diurus client-side (Mobile/Frontend)
        ]);

        // 2. Simpan Data ke Database (Dengan Atasan Otomatis)
        $user = User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'gender_id' => $request->gender_id,
            'station_id' => $request->station_id,
            'password' => Hash::make($request->password),
        ]);

        // 3. Buat token akses Sanctum untuk login otomatis setelah mendaftar
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load('role');

        // 4. Kirim respons sukses berupa JSON beserta info atasan yang didapat
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
                'role' => $user->role->role_name,
                'assigned_supervisor_id' => $user->supervisor_id, // Bagus untuk info di aplikasi mobile/frontend
                'assigned_manager_id' => $user->manager_id,
            ]
        ], 201);
    }

    /**
     * Menangani login untuk pengguna lewat WEB (Session & Cookie).
     * Fungsi inilah yang akan membuat halaman berpindah/redirect.
     */
    public function loginWeb(Request $request)
    {
        // 1. Validasi Inputan
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Coba Autentikasi Menggunakan Session Laravel (Standar Web)
        if (Auth::attempt($credentials)) {
            // Regenerasi session untuk keamanan dari Session Fixation Attack
            $request->session()->regenerate();

            // Redirect (pindahkan halaman) ke dashboard / pengajuan cuti
            return redirect()->intended('/dashboard');
        }

        // 3. Jika gagal, kembalikan ke halaman login dengan pesan error
        return back()->withErrors([
            'email' => 'Kombinasi Email atau Password salah!',
        ])->withInput($request->only('email'));
    }

    /**
     * Menangani login untuk pengguna lewat API (Sanctum Token).
     * Fungsi ini mengembalikan JSON dan digunakan untuk Mobile App / Postman.
     */
    public function loginApi(Request $request)
    {
        // Validasi Inputan
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Autentikasi Menggunakan Hash
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kombinasi Email atau Password salah!'
            ], 401);
        }

        // Buat Token Sanctum sebagai pengganti Session
        $token = $user->createToken('auth_token')->plainTextToken;

        // Kirim respons sukses berupa JSON beserta Tokennya
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
    public function sendOtpWeb(Request $request)
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
    public function verifyOtpWeb(Request $request)
    {
        $request->validate(['email' => 'required|email', 'otp' => 'required']);

        $sessionEmail = session('reset_email');
        $sessionOtp = session('reset_otp');
        $sessionExpires = session('reset_otp_expires');

        if (!$sessionOtp || $sessionEmail !== $request->email || now()->greaterThan($sessionExpires)) {
            return response()->json(['status' => 'error', 'message' => 'Sesi habis atau OTP kadaluarsa.'], 400);
        }

        if ($sessionOtp != $request->otp) {
            return response()->json(['status' => 'error', 'message' => 'Kode OTP salah.'], 400);
        }

        // Beri tanda di session bahwa OTP sudah sukses lolos verifikasi
        session(['otp_verified' => true]);

        return response()->json(['status' => 'success', 'message' => 'OTP Benar! Silakan masukkan kata sandi baru.']);
    }

    // 3. SIMPAN PASSWORD BARU PILIHAN USER (POST FORM)
    public function forgotWeb(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed', // Harus sama dengan password_confirmation
        ]);

        // Proteksi keamanan: pastikan session OTP sudah terverifikasi sebelumnya
        if (!session('otp_verified') || session('reset_email') !== $request->email) {
            return redirect()->back()->withErrors(['error' => 'Aksi tidak valid. Proses verifikasi salah.']);
        }

        // Update password baru pilihan user ke database
        DB::table('users')->where('email', $request->email)->update([
            'password' => Hash::make($request->password),
            'updated_at' => now()
        ]);

        // Bersihkan semua session pemulihan
        session()->forget(['reset_email', 'reset_otp', 'reset_otp_expires', 'otp_verified']);

        return redirect()->route('login')->with('success', 'Kata sandi Anda berhasil diperbarui! Silakan login.');
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
        // Menghapus token yang sedang digunakan saat ini oleh client API
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil logout API, token telah dihapus.'
        ], 200);
    }
}
