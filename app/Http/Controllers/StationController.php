<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Station;

class StationController extends Controller
{
    public function index()
    {
        // Menggunakan Eloquent withCount('users') jauh lebih clean dibanding DB::table
        // Ini otomatis membuat attribute virtual bernama 'users_count' yang kita alias-kan ke 'total_karyawan'
        $daftarStasiun = Station::withCount(['users as total_karyawan'])
            ->orderBy('name', 'desc')
            ->get();

        return view('admin.stations.index', compact('daftarStasiun'));
    }

    public function getKaryawan(int $id)
    {
        try {
            // Tarik data stasiun beserta relasi users dan sub-relasi role-nya sekaligus
            $stasiun = Station::with('users.role')->find($id);

            if (!$stasiun) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Stasiun tidak ditemukan.'
                ], 404);
            }

            $karyawanList = $stasiun->users;

            $data = $karyawanList->map(function($user) {

                // Default jika user ternyata tidak punya role di database
                $roleName = 'Staff';

                // Cek jika relasi role ada, lalu ambil kolom 'role_name' dari tabel roles
                if ($user->role) {
                    $roleName = $user->role->role_name;
                }

                return [
                    'name'          => $user->name,
                    'nip'           => $user->nip ?? '-',
                    'profile_photo' => $user->profile_photo,
                    'role_name'     => $roleName, // Dikirimkan ke JavaScript dengan key 'role_name'
                ];
            });

            return response()->json($data, 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
