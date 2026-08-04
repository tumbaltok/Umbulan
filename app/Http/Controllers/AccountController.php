<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User; 

class AccountController extends Controller
{
    // 1. Menampilkan Halaman Pengaturan Akun
    public function index()
    {
        $user = User::find(Auth::id());
        return view('profile.index', compact('user'));
    }

    // 2. Memproses Pembaruan Data Akun & Jadwal Kerja
    public function update(Request $request)
    {
        $user = User::find(Auth::id());

        if (!$user) {
            return redirect()->back()->withErrors('Pengguna tidak ditemukan.');
        }

        // LOGIKA 1: Jika request datang dari tombol "Hapus Foto"
        if ($request->has('delete_photo') && $request->delete_photo == '1') {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $user->update(['profile_photo' => null]);
            return redirect()->back()->with('success', 'Foto profil berhasil dihapus.');
        }

        // LOGIKA 2: Update Data Umum, Password, Foto, & Jadwal Kerja
        $request->validate([
            'nip'               => 'nullable|string|max:50',
            'name'              => 'required|string|max:255',
            'email'             => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'job_title'         => 'nullable|string|in:Operator,Maintenance,Pipeline,HSE,Dokumentasi',
            'phone_number'      => 'nullable|string|max:20',
            'profile_photo'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_password'  => 'nullable|required_with:new_password',
            'new_password'      => 'nullable|min:8|confirmed',
            // Validasi Jadwal Kerja
            'schedule_type'     => 'required|in:normal,roster',
            'normal_work_days'  => 'nullable|array',
            'normal_check_in'   => 'nullable|string',
            'normal_check_out'  => 'nullable|string',
            'roster_start_date' => 'nullable|date',
        ]);

        $updateData = [];

        if ($request->has('nip')) $updateData['nip'] = $request->nip;
        if ($request->has('name')) $updateData['name'] = $request->name;
        if ($request->has('email')) $updateData['email'] = $request->email;
        if ($request->has('job_title')) $updateData['job_title'] = $request->job_title;
        if ($request->has('phone_number')) $updateData['phone_number'] = $request->phone_number;

        // Simpan Konfigurasi Jadwal Kerja
        $updateData['schedule_type'] = $request->schedule_type;
        if ($request->schedule_type === 'normal') {
            $updateData['normal_work_days'] = $request->normal_work_days ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
            $updateData['normal_check_in'] = $request->normal_check_in ?? '08:00';
            $updateData['normal_check_out'] = $request->normal_check_out ?? '17:00';
            $updateData['roster_start_date'] = null;
        } else {
            $updateData['roster_start_date'] = $request->roster_start_date;
            $updateData['normal_work_days'] = null;
        }

        // Periksa jika user ingin mengubah password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama yang Anda masukkan salah.'])->withInput();
            }
            $updateData['password'] = Hash::make($request->new_password);
        }

        // Periksa jika user mengunggah foto profil baru
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $updateData['profile_photo'] = $path;
        }

        $user->update($updateData);

        return redirect()->route('account.index')->with('success', 'Informasi akun dan pengaturan jadwal Anda berhasil diperbarui!');
    }
}