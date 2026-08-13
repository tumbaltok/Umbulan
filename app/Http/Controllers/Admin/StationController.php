<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User\Station;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function index()
    {
        $daftarStasiun = Station::withCount(['users as total_karyawan'])
            ->orderBy('type', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.daftar.stationindex', compact('daftarStasiun'));
    }

    private function parseGoogleMapsUrl(string $url): ?array
    {
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }
        if (preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }
        if (preg_match('/[?&]ll=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }
        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }
        return null;
    }

    // 1. TAMBAH LOKASI / STASIUN (Dukungan Multi-Row Repeater & Single Row)
    public function store(Request $request)
    {
        // Pengecekan jika input dikirim dari repeater JavaScript Blade ($request->has('stations'))
        if ($request->has('stations') && is_array($request->stations)) {
            $request->validate([
                'stations' => 'required|array|min:1',
                'stations.*.kode_stasiun' => 'required|string|unique:stations,kode_stasiun',
                'stations.*.name' => 'required|string|max:255',
                'stations.*.type' => 'required|in:kantor,stasiun,rumah_meter',
                'stations.*.latitude' => 'required|numeric',
                'stations.*.longitude' => 'required|numeric',
                'stations.*.radius_meters' => 'required|numeric|min:10',
            ]);

            foreach ($request->stations as $stasiunData) {
                Station::create([
                    'kode_stasiun' => strtoupper($stasiunData['kode_stasiun']),
                    'name' => $stasiunData['name'],
                    'type' => strtolower($stasiunData['type']),
                    'latitude' => $stasiunData['latitude'],
                    'longitude' => $stasiunData['longitude'],
                    'radius_meters' => $stasiunData['radius_meters'],
                ]);
            }

            return redirect()->back()->with('success', 'Semua lokasi/stasiun kerja berhasil ditambahkan!');
        }

        // Fallback jika input dikirim secara single item
        $request->validate([
            'kode_stasiun' => 'required|string|unique:stations,kode_stasiun',
            'name' => 'required|string|max:255',
            'type' => 'required|in:kantor,stasiun,rumah_meter',
            'maps_url' => 'nullable|url',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius_meters' => 'required|numeric|min:10',
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;

        if ($request->filled('maps_url')) {
            $parsed = $this->parseGoogleMapsUrl($request->maps_url);
            if ($parsed) {
                $lat = $parsed['latitude'];
                $lng = $parsed['longitude'];
            }
        }

        if (is_null($lat) || is_null($lng)) {
            return redirect()->back()->withErrors(['maps_url' => 'Gagal membaca koordinat dari URL Google Maps. Harap masukkan koordinat manual atau periksa URL Anda.'])->withInput();
        }

        Station::create([
            'kode_stasiun' => strtoupper($request->kode_stasiun),
            'name' => $request->name,
            'type' => strtolower($request->type),
            'latitude' => $lat,
            'longitude' => $lng,
            'radius_meters' => $request->radius_meters,
        ]);

        return redirect()->back()->with('success', 'Lokasi/Stasiun kerja baru berhasil ditambahkan!');
    }

    // 2. UPDATE LOKASI / STASIUN
    public function update(Request $request, int $id)
    {
        $station = Station::findOrFail($id);

        // Jika update dikirim via array 'stations[0]' dari form edit
        $input = $request->has('stations') && isset($request->stations[0])
            ? $request->stations[0]
            : $request->all();

        $request->merge($input);

        $request->validate([
            'kode_stasiun' => 'required|string|unique:stations,kode_stasiun,'.$id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:kantor,stasiun,rumah_meter',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|numeric|min:10',
        ]);

        $station->update([
            'kode_stasiun' => strtoupper($request->kode_stasiun),
            'name' => $request->name,
            'type' => strtolower($request->type),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meters' => $request->radius_meters,
        ]);

        return redirect()->back()->with('success', 'Data lokasi/stasiun kerja berhasil diperbarui!');
    }

    // 3. HAPUS STASIUN
    public function destroy(int $id)
    {
        $station = Station::withCount('users')->findOrFail($id);

        if ($station->users_count > 0) {
            return redirect()->back()->with('error', 'Stasiun tidak dapat dihapus karena masih digunakan oleh '.$station->users_count.' karyawan!');
        }

        $station->delete();

        return redirect()->back()->with('success', 'Stasiun kerja berhasil dihapus!');
    }

    // 4. AMBIL LIST KARYAWAN PER STASIUN (JSON)
    public function getKaryawan(int $id)
    {
        try {
            $stasiun = Station::with('users.role')->find($id);

            if (! $stasiun) {
                return response()->json(['status' => 'error', 'message' => 'Stasiun tidak ditemukan.'], 404);
            }

            $data = $stasiun->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nip' => $user->nip ?? '-',
                    'profile_photo' => $user->profile_photo,
                    'role_name' => $user->role ? $user->role->role_name : 'Staff',
                ];
            });

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan sistem: '.$e->getMessage()], 500);
        }
    }
}
