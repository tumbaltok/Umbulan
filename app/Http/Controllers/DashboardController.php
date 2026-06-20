<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Cuti dianggap benar-benar diambil jika sudah disetujui sampai tahap akhir (status_manager = approved)
        $totalCutiDiambil = DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->where('status_manager', 'approved')
            ->sum('total_hari');

        // 2. Hitung jumlah pengajuan yang masih dalam proses peninjauan (status_manager = pending)
        $totalPending = DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->where('status_manager', 'pending')
            ->count();

        // 3. Ambil kuota tahunan dari saldo_cuti milik user (default 12 jika kosong)
        $kuotaTahunan = $user->saldo_cuti ?? 12;
        $sisaKuota = $kuotaTahunan - $totalCutiDiambil;

        // 4. Mengambil 5 riwayat transaksi pengajuan cuti terakhir
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
