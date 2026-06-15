<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
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
        $hariIni = Carbon::now()->format('Y-m-d');
        $waktuSekarang = Carbon::now()->format('H:i:s');

        // Cek apakah hari ini user sudah absen masuk sebelumnya?
        $cekAbsen = Absensi::where('user_id', $user->id)
            ->where('tanggal', $hariIni)
            ->first();

        if ($cekAbsen && $cekAbsen->jam_masuk !== null) {
            return response()->json(['message' => 'Anda sudah melakukan absen masuk hari ini!'], 400);
        }

        // Aturan Jam Masuk Kantor (Contoh: Batas masuk jam 08:00:00)
        $keterangan = 'Tepat Waktu';
        if (Carbon::now()->gt(Carbon::parse('08:00:00'))) {
            $keterangan = 'Terlambat';
        }

        // Simpan data absen masuk (menggunakan updateOrCreate jika baris "Cuti/Izin" sudah dibuat sistem sebelumnya)
        $absensi = Absensi::updateOrCreate(
            [
                'user_id' => $user->id,
                'tanggal' => $hariIni
            ],
            [
                'jam_masuk' => $waktuSekarang,
                'status_kehadiran' => 'Hadir',
                'latitude_masuk' => $request->latitude,
                'longitude_masuk' => $request->longitude,
                'keterangan' => $keterangan
            ]
        );

        return response()->json([
            'message' => 'Berhasil melakukan absen masuk. Selamat bekerja!',
            'data' => $absensi
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
        $hariIni = Carbon::now()->format('Y-m-d');
        $waktuSekarang = Carbon::now()->format('H:i:s');

        // Cari data absen hari ini
        $absensi = Absensi::where('user_id', $user->id)
            ->where('tanggal', $hariIni)
            ->first();

        // Cegah absen pulang jika belum absen masuk
        if (!$absensi || $absensi->jam_masuk === null) {
            return response()->json(['message' => 'Gagal! Anda belum melakukan absen masuk hari ini.'], 400);
        }

        // Cegah absen pulang ganda
        if ($absensi->jam_pulang !== null) {
            return response()->json(['message' => 'Anda sudah melakukan absen pulang hari ini!'], 400);
        }

        // Update data pulang
        $absensi->update([
            'jam_pulang' => $waktuSekarang,
            'latitude_pulang' => $request->latitude,
            'longitude_pulang' => $request->longitude,
        ]);

        return response()->json([
            'message' => 'Berhasil melakukan absen pulang. Hati-hati di jalan!',
            'data' => $absensi
        ], 200);
    }

    // 3. KARYAWAN: Cek Status Absen Hari Ini (Untuk Kebutuhan UI Berubah Tombol)
    public function statusAbsenHariIni(Request $request)
    {
        $user = $request->user();
        $hariIni = Carbon::now()->format('Y-m-d');

        $absensi = Absensi::where('user_id', $user->id)
            ->where('tanggal', $hariIni)
            ->first();

        return response()->json([
            'sudah_absen_masuk' => $absensi && $absensi->jam_masuk ? true : false,
            'sudah_absen_pulang' => $absensi && $absensi->jam_pulang ? true : false,
            'data_absensi' => $absensi
        ], 200);
    }

    // 4. KARYAWAN: Melihat Riwayat Absensi Bulanan Sendiri
    public function riwayatAbsensiDiri(Request $request)
    {
        $user = $request->user();
        $bulanini = Carbon::now()->month;

        $riwayat = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulanini)
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json(['data' => $riwayat], 200);
    }
}
