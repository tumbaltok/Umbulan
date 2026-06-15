<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaldoCuti;
use App\Models\User;
use App\Models\JenisCuti;
use Carbon\Carbon;

class SaldoCutiController extends Controller
{
    // KARYAWAN: Melihat sisa kuota cuti diri sendiri pada tahun berjalan
    public function index(Request $request)
    {
        $user = $request->user();
        $tahunSekarang = Carbon::now()->year;

        // Ambil semua saldo cuti milik user beserta nama jenis cutinya
        $saldo = SaldoCuti::with('jenisCuti')
            ->where('user_id', $user->id)
            ->where('tahun', $tahunSekarang)
            ->get();

        return response()->json([
            'message' => 'Data saldo cuti tahun ' . $tahunSekarang . ' berhasil diambil.',
            'data' => $saldo
        ], 200);
    }

    // ADMIN/HRD: Generate saldo cuti tahunan otomatis untuk seluruh karyawan
    // Biasanya dijalankan setiap awal tahun baru
    public function generateSaldoMassal(Request $request)
    {
        // Validasi opsional: Pastikan hanya admin/HRD yang bisa mengakses (jika ada role admin)
        $operator = $request->user()->load('role');
        $roleName = strtolower($operator->role->role_name ?? '');

        if ($roleName !== 'manager' && $roleName !== 'admin') {
            return response()->json(['message' => 'Akses ditolak! Hanya manager atau admin yang bisa generate saldo.'], 403);
        }

        $tahunTarget = Carbon::now()->year;
        $karyawanLolos = 0;

        // Ambil semua user yang tipenya karyawan biasa (bukan admin)
        $users = User::all();

        // Ambil semua jenis cuti yang butuh saldo (misal: Cuti Tahunan, Cuti Besar)
        // Sesuaikan query-nya jika cuti sakit tidak punya data default kuota
        $jenisCutis = JenisCuti::all();

        foreach ($users as $user) {
            foreach ($jenisCutis as $jc) {

                // Lewati jika jenis cutinya adalah Cuti Sakit / Melahirkan (karena unlimited/bebas saldo)
                $namaCuti = strtolower($jc->name_cuti?? '');
                if (str_contains($namaCuti, 'sakit') || str_contains($namaCuti, 'melahirkan')) {
                    continue;
                }

                // Cek apakah saldo untuk user, jenis cuti, dan tahun ini sudah ada?
                $cekSaldo = SaldoCuti::where('user_id', $user->id)
                    ->where('jenis_cuti_id', $jc->id)
                    ->where('tahun', $tahunTarget)
                    ->exists();

                // Jika belum ada saldo di tahun tersebut, buatkan barunya
                if (!$cekSaldo) {
                    SaldoCuti::create([
                        'user_id' => $user->id,
                        'jenis_cuti_id' => $jc->id,
                        'tahun' => $tahunTarget,
                        'sisa_saldo' => $jc->kuota_default ?? 12, // Default 12 hari jika kolom kuota_default tidak ada
                    ]);
                    $karyawanLolos++;
                }
            }
        }

        return response()->json([
            'message' => 'Proses generate saldo cuti massal berhasil dilakukan.',
            'total_data_baru_terbuat' => $karyawanLolos
        ], 200);
    }
}
