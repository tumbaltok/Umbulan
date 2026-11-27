<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User\Station;
use Illuminate\Http\Request;

class StationController extends Controller
{
    // Menampilkan daftar stasiun dan Rumah Meter
    public function index()
    {
        $daftarStasiunUtama = Station::where('type', '!=', 'rumah_meter')
            ->withCount('users')
            ->with(['users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.nip', 'users.station_id');
            }])
            ->orderBy('type', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $daftarRumahMeter = Station::where('type', 'rumah_meter')
            ->withCount('assignedUsers')
            ->with(['assignedUsers' => function ($q) {
                $q->select('users.id', 'users.name', 'users.nip');
            }])
            ->orderBy('kode_stasiun', 'asc')
            ->get();

        $daftarStasiun = Station::withCount(['users', 'assignedUsers'])
            ->with(['users:users.id,users.name,users.nip,users.station_id', 'assignedUsers:users.id,users.name,users.nip'])
            ->orderBy('type', 'asc')
            ->orderBy('kode_stasiun', 'asc')
            ->get();

        return view('admin.daftar.stationindex', compact('daftarStasiunUtama', 'daftarRumahMeter', 'daftarStasiun'));
    }

    // Menyimpan data stasiun/lokasi baru (mendukung single atau multi-row repeater)
    public function store(Request $request)
    {
        // Pengecekan jika input dikirim dari repeater JavaScript Blade ($request->has('stations'))
        if ($request->has('stations') && is_array($request->stations)) {
            $request->validate([
                'stations' => 'required|array|min:1',
                'stations.*.kode_stasiun' => 'required|string|unique:stations,kode_stasiun',
                'stations.*.name' => 'required|string|max:255',
                'stations.*.type' => 'required|in:kantor,stasiun,rumah_meter',
            ]);

            foreach ($request->stations as $stasiunData) {
                Station::create([
                    'kode_stasiun' => strtoupper($stasiunData['kode_stasiun']),
                    'name' => $stasiunData['name'],
                    'type' => strtolower($stasiunData['type']),
                ]);
            }

            return redirect()->back()->with('success', 'Semua lokasi/stasiun kerja berhasil ditambahkan!');
        }

        // Fallback jika input dikirim secara single item
        $request->validate([
            'kode_stasiun' => 'required|string|unique:stations,kode_stasiun',
            'name' => 'required|string|max:255',
            'type' => 'required|in:kantor,stasiun,rumah_meter',
        ]);

        Station::create([
            'kode_stasiun' => strtoupper($request->kode_stasiun),
            'name' => $request->name,
            'type' => strtolower($request->type),
        ]);

        return redirect()->back()->with('success', 'Lokasi/Stasiun kerja baru berhasil ditambahkan!');
    }

    // Memperbarui data stasiun kerja
    public function update(Request $request, int $id)
    {
        $station = Station::findOrFail($id);

        // Dukungan payload edit dari array atau single form
        $input = $request->has('stations') && isset($request->stations[0])
            ? $request->stations[0]
            : $request->all();

        $request->merge($input);

        $request->validate([
            'kode_stasiun' => 'required|string|unique:stations,kode_stasiun,'.$id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:kantor,stasiun,rumah_meter',
        ]);

        $station->update([
            'kode_stasiun' => strtoupper($request->kode_stasiun),
            'name' => $request->name,
            'type' => strtolower($request->type),
        ]);

        return redirect()->back()->with('success', 'Data lokasi/stasiun kerja berhasil diperbarui!');
    }

    // Menghapus stasiun kerja (hanya jika tidak memiliki karyawan terikat)
    public function destroy(int $id)
    {
        $station = Station::withCount('users')->findOrFail($id);

        if ($station->users_count > 0) {
            return redirect()->back()->with('error', 'Stasiun tidak dapat dihapus karena masih digunakan oleh '.$station->users_count.' karyawan!');
        }

        $station->delete();

        return redirect()->back()->with('success', 'Stasiun kerja berhasil dihapus!');
    }

    // Mengambil daftar karyawan yang bertugas pada stasiun tertentu (format JSON)
    public function getKaryawan(int $id)
    {
        try {
            $stasiun = Station::with(['users.role', 'users.roles', 'assignedUsers.role', 'assignedUsers.roles'])->find($id);

            if (! $stasiun) {
                return response()->json(['status' => 'error', 'message' => 'Stasiun tidak ditemukan.'], 404);
            }

            // Jika tipe stasiun adalah rumah_meter, ambil staf terikat (assignedUsers)
            $karyawanList = $stasiun->type === 'rumah_meter'
                ? $stasiun->assignedUsers->merge($stasiun->users)->unique('id')
                : $stasiun->users;

            $data = $karyawanList->values()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nip' => $user->nip ?? '-',
                    'profile_photo' => $user->profile_photo,
                    'role_name' => $user->role ? $user->role->role_name : ($user->roles->pluck('role_name')->first() ?? 'Staff'),
                ];
            });

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan sistem: '.$e->getMessage()], 500);
        }
    }
}
