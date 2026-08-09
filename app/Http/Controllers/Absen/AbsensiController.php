<?php

namespace App\Http\Controllers\Absen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absen\Kehadiran as Absensi;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    // 1. KARYAWAN: Melakukan Absen Masuk (Clock In)
    public function absenMasuk(Request $request)
    {
        $request->validate([
            'latitude' => 'required|string',
            'longitude' => 'required|string',
        ]);

        $user = $request->user();
        
        // PERBAIKAN: Gunakan timezone Asia/Jakarta
        $now = Carbon::now('Asia/Jakarta');
        $hariIni = $now->format('Y-m-d');
        $waktuSekarang = $now->format('H:i:s');

        // PERBAIKAN: Cari berdasarkan created_at atau tanggal
        $cekAbsen = Absensi::where('user_id', $user->id)
            ->where(function($q) use ($hariIni) {
                $q->whereDate('created_at', $hariIni)
                  ->orWhere('tanggal', $hariIni);
            })
            ->first();

        // Cek apakah sudah jam masuk (dukung kolom check_in dan jam_masuk)
        if ($cekAbsen && ($cekAbsen->check_in !== null || $cekAbsen->jam_masuk !== null)) {
            return response()->json(['message' => 'Anda sudah melakukan absen masuk hari ini!'], 400);
        }

        // Aturan Jam Masuk Kantor (Batas masuk jam 08:00 WIB)
        $batasMasuk = Carbon::today('Asia/Jakarta')->setTime(8, 0, 0);
        $keterangan = $now->gt($batasMasuk) ? 'Terlambat' : 'Tepat Waktu';

        // Simpan data absen masuk (Menyelaraskan nama kolom DB)
        $absensi = Absensi::updateOrCreate(
            [
                'user_id' => $user->id,
                'tanggal' => $hariIni
            ],
            [
                'jam_masuk'        => $waktuSekarang,
                'check_in'         => $waktuSekarang,
                'status_kehadiran' => 'Hadir',
                'latitude_masuk'   => $request->latitude,
                'longitude_masuk'  => $request->longitude,
                'keterangan'       => $keterangan
            ]
        );

        return response()->json([
            'message' => 'Berhasil melakukan absen masuk. Selamat bekerja!',
            'data'    => $absensi
        ], 200);
    }

    // 2. KARYAWAN: Melakukan Absen Pulang (Clock Out)
    public function absenPulang(Request $request)
    {
        $request->validate([
            'latitude' => 'required|string',
            'longitude' => 'required|string',
        ]);

        $user = $request->user();
        $now = Carbon::now('Asia/Jakarta');
        $hariIni = $now->format('Y-m-d');
        $waktuSekarang = $now->format('H:i:s');

        // Cari data absen hari ini
        $absensi = Absensi::where('user_id', $user->id)
            ->where(function($q) use ($hariIni) {
                $q->whereDate('created_at', $hariIni)
                  ->orWhere('tanggal', $hariIni);
            })
            ->first();

        // Cegah absen pulang jika belum absen masuk
        if (!$absensi || ($absensi->jam_masuk === null && $absensi->check_in === null)) {
            return response()->json(['message' => 'Gagal! Anda belum melakukan absen masuk hari ini.'], 400);
        }

        // Cegah absen pulang ganda
        if ($absensi->jam_pulang !== null || $absensi->check_out !== null) {
            return response()->json(['message' => 'Anda sudah melakukan absen pulang hari ini!'], 400);
        }

        // Update data pulang (Mendukung kedua versi nama kolom)
        $absensi->update([
            'jam_pulang'       => $waktuSekarang,
            'check_out'        => $waktuSekarang,
            'latitude_pulang'  => $request->latitude,
            'longitude_pulang' => $request->longitude,
        ]);

        return response()->json([
            'message' => 'Berhasil melakukan absen pulang. Hati-hati di jalan!',
            'data'    => $absensi
        ], 200);
    }

    // 3. KARYAWAN: Cek Status Absen Hari Ini
    public function statusAbsenHariIni(Request $request)
    {
        $user = $request->user();
        $hariIni = Carbon::now('Asia/Jakarta')->format('Y-m-d');

        $absensi = Absensi::where('user_id', $user->id)
            ->where(function($q) use ($hariIni) {
                $q->whereDate('created_at', $hariIni)
                  ->orWhere('tanggal', $hariIni);
            })
            ->first();

        $sudahMasuk  = $absensi && ($absensi->jam_masuk || $absensi->check_in);
        $sudahPulang = $absensi && ($absensi->jam_pulang || $absensi->check_out);

        return response()->json([
            'sudah_absen_masuk'  => (bool)$sudahMasuk,
            'sudah_absen_pulang' => (bool)$sudahPulang,
            'data_absensi'       => $absensi
        ], 200);
    }

    // 4. KARYAWAN: Melihat Riwayat Absensi Bulanan Sendiri
    public function riwayatAbsensiDiri(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now('Asia/Jakarta');

        $riwayat = Absensi::where('user_id', $user->id)
            ->where(function($q) use ($now) {
                $q->whereMonth('created_at', $now->month)
                  ->orWhereMonth('tanggal', $now->month);
            })
            ->where(function($q) use ($now) {
                $q->whereYear('created_at', $now->year)
                  ->orWhereYear('tanggal', $now->year);
            })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $riwayat], 200);
    }
}