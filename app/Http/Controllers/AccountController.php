<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User; // <-- 1. YANG KURANG: Pastikan model dipanggil

class AccountController extends Controller
{
    // 1. Menampilkan Halaman Pengaturan Akun
    public function index()
    {
        $user = User::find(Auth::id()); // Menggunakan Model secara eksplisit
        return view('profile.index', compact('user'));
    }

    // 2. Memproses Pembaruan Data Akun
    public function update(Request $request)
    {
        $user = User::find(Auth::id()); // Menggunakan Model secara eksplisit

        if (!$user) {
            return redirect()->back()->withErrors('Pengguna tidak ditemukan.');
        }

        // LOGIKA 1: Jika request datang dari tombol "Hapus Foto"
        // Memastikan parameter delete_photo terbaca dengan tepat
        if ($request->has('delete_photo') && $request->delete_photo == '1') {

            // Jika ada file foto lama di storage, hapus filenya
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Set kolom foto di database menjadi null
            $user->update([
                'profile_photo' => null
            ]);

            // Langsung hentikan proses dan kembali ke halaman profil
            return redirect()->back()->with('success', 'Foto profil berhasil dihapus.');
        }

        // LOGIKA 2: Update Data Umum, Password, & Unggah Foto Baru
        $request->validate([
            'nip'              => 'nullable|string|max:50',
            'name'             => 'required|string|max:255',
            'email'            => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'job_title'        => 'nullable|string|in:Operator,Maintenance,HSE,Dokumentasi',
            'phone_number'     => 'nullable|string|max:20',
            'profile_photo'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password'     => 'nullable|min:8|confirmed',
        ]);

        // Tampung data dasar yang pasti diupdate
        $updateData = [];

        if ($request->has('nip')) $updateData['nip'] = $request->nip;
        if ($request->has('name')) $updateData['name'] = $request->name;
        if ($request->has('email')) $updateData['email'] = $request->email;
        if ($request->has('job_title')) $updateData['job_title'] = $request->job_title;
        if ($request->has('phone_number')) $updateData['phone_number'] = $request->phone_number;


        // Periksa jika user ingin mengubah password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama yang Anda masukkan salah.'])->withInput();
            }
            $updateData['password'] = Hash::make($request->new_password);
        }

        // Periksa jika user mengunggah foto profil baru
        if ($request->hasFile('profile_photo')) {
            // Hapus foto lama dari storage jika sebelumnya sudah ada
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Simpan foto baru
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $updateData['profile_photo'] = $path;
        }

        // Eksekusi Update ke Database
        $user->update($updateData);

        return redirect()->route('account.index')->with('success', 'Informasi akun Anda berhasil diperbarui!');
    }
}
