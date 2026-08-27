<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::id());

        // 1. Ambil seluruh Penempatan Kerja / Stasiun (Kecuali Rumah Meter)
        $daftarStasiun = Station::where('type', '!=', 'rumah_meter')
            ->orderBy('name', 'asc')
            ->get();

        // 2. Ambil Seluruh Peran / Jabatan KECUALI Admin
        $daftarRole = Role::where('role_name', 'NOT LIKE', '%admin%')
            ->orderBy('level', 'asc')
            ->orderBy('role_name', 'asc')
            ->get();

        return view('pengaturan.index', compact(
            'user',
            'daftarStasiun',
            'daftarRole'
        ));
    }

    public function update(Request $request)
    {
        $user = User::find(Auth::id());

        if (! $user) {
            return redirect()->back()->withErrors('Pengguna tidak ditemukan.');
        }

        // LOGIKA HAPUS FOTO PROFIL
        if ($request->has('delete_photo') && $request->delete_photo == '1') {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $user->update(['profile_photo' => null]);

            return redirect()->back()->with('success', 'Foto profil berhasil dihapus.');
        }

        // LOGIKA HAPUS TTD
        if ($request->has('delete_signature') && $request->delete_signature == '1') {
            if ($user->signature && Storage::disk('public')->exists($user->signature)) {
                Storage::disk('public')->delete($user->signature);
            }
            $user->update(['signature' => null]);

            return redirect()->back()->with('success', 'Tanda tangan digital berhasil dihapus.');
        }

        // VALIDASI LENGKAP
        $request->validate([
            'nip' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'gender_id' => 'nullable|integer',
            'station_id' => 'nullable|integer|exists:stations,id',
            'role_id' => 'nullable|integer|exists:roles,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'phone_number' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
            'schedule_type' => 'nullable|in:normal,roster',
            'normal_work_days' => 'nullable|array',
            'normal_check_in' => 'nullable|string',
            'normal_check_out' => 'nullable|string',
            'roster_start_date' => 'nullable|date',
        ]);

        $updateData = [];

        if ($request->has('nip')) {
            $updateData['nip'] = $request->nip;
        }
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        // CEK PERUBAHAN EMAIL
        if ($request->has('email')) {
            if ($request->email !== $user->email) {
                $updateData['email'] = $request->email;
                $updateData['email_verified_at'] = null;
            }
        }

        // CEK PERUBAHAN NO TELEPON
        if ($request->has('phone_number')) {
            if ($request->phone_number !== $user->phone_number) {
                $updateData['phone_number'] = $request->phone_number;
                $updateData['phone_verified_at'] = null;
            }
        }

        if ($request->has('gender_id')) {
            $updateData['gender_id'] = $request->gender_id;
        }

        if ($request->has('station_id')) {
            $updateData['station_id'] = $request->station_id;
        }

        // SIMPAN JADWAL KERJA
        if ($request->has('schedule_type') && ! empty($request->schedule_type)) {
            $updateData['schedule_type'] = $request->schedule_type;

            if ($request->schedule_type === 'normal') {
                $updateData['normal_work_days'] = $request->normal_work_days ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
                $updateData['normal_check_in'] = $request->normal_check_in ?? '08:00';
                $updateData['normal_check_out'] = $request->normal_check_out ?? '17:00';
                $updateData['roster_start_date'] = null;
            } elseif ($request->schedule_type === 'roster') {
                if ($request->filled('roster_start_date')) {
                    $updateData['roster_start_date'] = $request->roster_start_date;
                } else {
                    $updateData['roster_start_date'] = Carbon::now('Asia/Jakarta')->startOfWeek(Carbon::TUESDAY)->format('Y-m-d');
                }
                $updateData['normal_work_days'] = null;
            }
        }

        // SIMPAN PASSWORD BARU
        if ($request->filled('new_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama yang Anda masukkan salah.'])->withInput();
            }
            $updateData['password'] = Hash::make($request->new_password);
        }

        // UPLOAD FOTO PROFIL
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $updateData['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        // UPLOAD & PROSES TTD OTOMATIS TRANSPARAN
        if ($request->hasFile('signature')) {
            $file = $request->file('signature');

            if ($user->signature && Storage::disk('public')->exists($user->signature)) {
                Storage::disk('public')->delete($user->signature);
            }

            $transparentImageData = $this->makeSignatureBackgroundTransparent($file->getPathname());

            if ($transparentImageData) {
                $filename = 'signatures/ttd_' . $user->id . '_' . time() . '.png';
                Storage::disk('public')->put($filename, $transparentImageData);
                $updateData['signature'] = $filename;
            } else {
                $updateData['signature'] = $file->store('signatures', 'public');
            }
        }

        // PREVENT ROLE ADMIN OVERWRITE
        if (!$user->hasRole('ADMIN') && $user->role_id != 1) {
            if ($request->has('roles') && is_array($request->roles)) {
                $validRoleIds = Role::whereIn('id', $request->roles)
                    ->where('id', '!=', 1)
                    ->where('role_name', 'NOT LIKE', '%admin%')
                    ->pluck('id')
                    ->toArray();
                if (!empty($validRoleIds)) {
                    $syncData = [];
                    foreach ($validRoleIds as $idx => $rId) {
                        $syncData[$rId] = ['is_primary' => ($idx === 0)];
                    }
                    $user->roles()->sync($syncData);
                    $updateData['role_id'] = $validRoleIds[0];
                }
            } elseif ($request->has('role_id') && ! empty($request->role_id)) {
                $selectedRole = Role::find($request->role_id);
                if ($selectedRole && strtolower($selectedRole->role_name) !== 'admin') {
                    $updateData['role_id'] = $request->role_id;
                    $user->roles()->sync([$request->role_id => ['is_primary' => true]]);
                }
            }
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Informasi akun dan pengaturan profil berhasil diperbarui!');
    }

    private function makeSignatureBackgroundTransparent(string $filePath)
    {
        $info = @getimagesize($filePath);
        if (! $info) {
            return null;
        }

        switch ($info['mime']) {
            case 'image/jpeg':
            case 'image/jpg':
                $src = @imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $src = @imagecreatefrompng($filePath);
                break;
            default:
                return null;
        }

        if (! $src) {
            return null;
        }

        $width = imagesx($src);
        $height = imagesy($src);

        $dst = imagecreatetruecolor($width, $height);
        imagecopy($dst, $src, 0, 0, 0, 0, $width, $height);
        imagedestroy($src);

        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($dst, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                if ($r > 180 && $g > 180 && $b > 180) {
                    imagesetpixel($dst, $x, $y, $transparent);
                }
            }
        }

        ob_start();
        imagepng($dst);
        $imageData = ob_get_clean();

        imagedestroy($dst);

        return $imageData;
    }
}
