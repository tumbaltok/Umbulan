<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Station;

class StationController extends Controller
{
    public function index()
    {
        $daftarStasiun = Station::withCount(['users as total_karyawan'])
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.stations.index', compact('daftarStasiun'));
    }

    /**
     * FUNGSI UTILITAS: Membaca (Parse) Latitude & Longitude dari URL Google Maps
     */
    private function parseGoogleMapsUrl(string $url): ?array
    {
        // Pattern 1: Format standar browser (@lat,lng)
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }

        // Pattern 2: Format query parameter (?q=lat,lng)
        if (preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }

        // Pattern 3: Format ll parameter (&ll=lat,lng)
        if (preg_match('/[?&]ll=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }

        return null;
    }

    /**
     * CREATE: Tambah Stasiun Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_stasiun'  => 'required|string|unique:stations,kode_stasiun',
            'name'          => 'required|string|max:255',
            'maps_url'      => 'nullable|url',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'radius_meters' => 'required|numeric|min:10',
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;

        // Jika user memasukkan URL Google Maps, prioritaskan ekstraksi dari URL
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
            'kode_stasiun'  => strtoupper($request->kode_stasiun),
            'name'          => $request->name,
            'latitude'      => $lat,
            'longitude'     => $lng,
            'radius_meters' => $request->radius_meters,
        ]);

        return redirect()->back()->with('success', 'Stasiun kerja baru berhasil ditambahkan!');
    }

    /**
     * UPDATE: Edit Stasiun Kerja
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_stasiun'  => 'required|string|unique:stations,kode_stasiun,' . $id,
            'name'          => 'required|string|max:255',
            'maps_url'      => 'nullable|url',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'radius_meters' => 'required|numeric|min:10',
        ]);

        $station = Station::findOrFail($id);

        $lat = $request->latitude;
        $lng = $request->longitude;

        if ($request->filled('maps_url')) {
            $parsed = $this->parseGoogleMapsUrl($request->maps_url);
            if ($parsed) {
                $lat = $parsed['latitude'];
                $lng = $parsed['longitude'];
            }
        }

        $station->update([
            'kode_stasiun'  => strtoupper($request->kode_stasiun),
            'name'          => $request->name,
            'latitude'      => $lat ?? $station->latitude,
            'longitude'     => $lng ?? $station->longitude,
            'radius_meters' => $request->radius_meters,
        ]);

        return redirect()->back()->with('success', 'Data stasiun kerja berhasil diperbarui!');
    }

    /**
     * DELETE: Hapus Stasiun Kerja
     */
    public function destroy($id)
    {
        $station = Station::withCount('users')->findOrFail($id);

        if ($station->users_count > 0) {
            return redirect()->back()->with('error', 'Stasiun tidak dapat dihapus karena masih digunakan oleh ' . $station->users_count . ' karyawan!');
        }

        $station->delete();
        return redirect()->back()->with('success', 'Stasiun kerja berhasil dihapus!');
    }

    public function getKaryawan(int $id)
    {
        try {
            $stasiun = Station::with('users.role')->find($id);

            if (!$stasiun) {
                return response()->json(['status' => 'error', 'message' => 'Stasiun tidak ditemukan.'], 404);
            }

            $data = $stasiun->users->map(function($user) {
                return [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'nip'           => $user->nip ?? '-',
                    'profile_photo' => $user->profile_photo,
                    'role_name'     => $user->role ? $user->role->role_name : 'Staff',
                ];
            });

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}