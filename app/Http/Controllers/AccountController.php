<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    // 1. Menampilkan Halaman Pengaturan Akun
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    // 2. Memproses Pembaruan Data Akun
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi input form
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed', // 'confirmed' mewajibkan field new_password_confirmation
        ]);

        // Cek jika user ingin mengubah password
        if ($request->filled('new_password')) {
            // Validasi apakah password lama sudah benar
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama yang Anda masukkan salah.'])->withInput();
            }

            // Update nama, email, dan password baru
            DB::table('users')->where('id', $user->id)->update([
                'nip' => $request->nip,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->new_password),
                'updated_at' => now(),
            ]);
        } else {
            // Update nama dan email saja (tanpa ganti password)
            DB::table('users')->where('id', $user->id)->update([
                'nip' => $request->nip,
                'name' => $request->name,
                'email' => $request->email,
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('account.index')->with('success', 'Informasi akun Anda berhasil diperbarui!');
    }
}
