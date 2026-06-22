<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tahunSekarang = now()->year;

        // 1. Ambil data saldo cuti tahunan milik user
        $saldoTahunan = $user->saldo_cuti;

        $kuotaTahunan = $saldoTahunan ? $saldoTahunan->kuota_awal : 12;

        // 2. Hanya hitung Cuti Tahunan yang benar-benar SUDAH DIAMBEL
        $totalCutiDiambil = DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->where('jenis_cuti_id', User::CUTI_TAHUNAN_ID)
            ->where('status_manager', 'approved')
            ->whereYear('tanggal_mulai', $tahunSekarang)
            ->sum('total_hari');

        // 3. Hitung jumlah pengajuan yang statusnya MASIH PENDING
        $totalPending = DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->where('status_manager', 'pending')
            ->where('status_supervisor', '!=', 'rejected')
            ->count();

        // 4. Hitung sisa kuota cuti
        $sisaKuota = $kuotaTahunan - $totalCutiDiambil;

        // 5. Mengambil 5 riwayat transaksi pengajuan cuti terakhir
        $riwayatCuti = DB::table('pengajuan_cutis')
            ->join('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->where('pengajuan_cutis.user_id', $user->id)
            ->select('pengajuan_cutis.*', 'jenis_cutis.name_cuti')
            ->orderBy('pengajuan_cutis.created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard/index', compact(
            'user',
            'kuotaTahunan',
            'totalCutiDiambil',
            'totalPending',
            'sisaKuota',
            'riwayatCuti'
        ));
    }
}
