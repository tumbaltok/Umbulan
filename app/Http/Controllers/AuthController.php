<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
            'nip' => 'required|string|max:50|unique:users,nip',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'gender_id' => 'required|exists:genders,id',
            'station_id' => 'required|exists:stations,id',
            'tipe_id' => 'required|exists:tipes,id',
            'password' => 'required|string|min:8|confirmed', // Harus ada input password_confirmation di form
        ]);

        // 2. Simpan Data ke Database
        $user = User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'gender_id' => $request->gender_id,
            'station_id' => $request->station_id,
            'tipe_id' => $request->tipe_id,
            'password' => Hash::make($request->password), // Enkripsi password
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
            'nip' => 'required|string|max:50|unique:users,nip',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'gender_id' => 'required|exists:genders,id',
            'station_id' => 'required|exists:stations,id',
            'tipe_id' => 'required|exists:tipes,id',
            'password' => 'required|string|min:8', // Pada API umumnya konfirmasi ditangani client-side
        ]);

        // 2. Simpan Data ke Database
        $user = User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'gender_id' => $request->gender_id,
            'station_id' => $request->station_id,
            'tipe_id' => $request->tipe_id,
            'password' => Hash::make($request->password),
        ]);

        // 3. Buat token akses Sanctum untuk login otomatis setelah mendaftar
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4. Kirim respons sukses berupa JSON
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
