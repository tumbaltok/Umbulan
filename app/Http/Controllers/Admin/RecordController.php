<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Car\PengajuanCar;
use App\Models\Mpr\PengajuanMpr;
use Carbon\Carbon;

class RecordController extends Controller
{
    // ==========================================
    //            MANAGEMENT RECORD CUTI
    // ==========================================

    public function cuti(Request $request)
    {
        $query = PengajuanCuti::with(['user.role', 'user.station', 'jenisCuti', 'subCuti']);

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_mulai', $request->bulan);
        }

        $tahun = $request->get('tahun', date('Y'));
        $query->whereYear('tanggal_mulai', $tahun);

        $daftarCuti = $query->orderBy('tanggal_mulai', 'desc')->get();

        return view('admin.record.cuti', compact('daftarCuti'));
    }

    public function exportCuti(Request $request)
    {
        $query = PengajuanCuti::with(['user.role', 'user.station', 'jenisCuti', 'subCuti']);

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_mulai', $request->bulan);
        }
        $tahun = $request->get('tahun', date('Y'));
        $query->whereYear('tanggal_mulai', $tahun);

        $dataCuti = $query->orderBy('tanggal_mulai', 'desc')->get();

        $namaBulan = $request->filled('bulan') ? Carbon::create()->month((int) $request->bulan)->isoFormat('MMMM') : 'Semua_Bulan';
        $fileName = "Record_Cuti_Karyawan_META_{$namaBulan}_{$tahun}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Nama Karyawan', 'NIP', 'Station', 'Jenis Perizinan', 'Total Hari', 'Tanggal Mulai', 'Tanggal Selesai', 'Status'];

        $callback = function() use($dataCuti, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns, ';');

            foreach ($dataCuti as $cuti) {
                $perihal = $cuti->subCuti ? $cuti->subCuti->nama_sub_cuti : ($cuti->jenisCuti->name_cuti ?? 'Cuti/Izin');
                fputcsv($file, [
                    $cuti->user->name ?? '-',
                    $cuti->user->nip ?? '-',
                    $cuti->user->station->name ?? 'Pusat',
                    $perihal,
                    ($cuti->total_hari ?? $cuti->durasi_hari) . ' Hari',
                    $cuti->tanggal_mulai,
                    $cuti->tanggal_selesai,
                    strtoupper($cuti->status_akhir ?? 'PENDING')
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    //            MANAGEMENT RECORD CAR
    // ==========================================

    public function car(Request $request)
    {
        $query = PengajuanCar::with(['user.role', 'user.station', 'details']);

        if ($request->filled('bulan')) {
            $query->whereMonth('created_at', $request->bulan);
        }

        $tahun = $request->get('tahun', date('Y'));
        $query->whereYear('created_at', $tahun);

        $daftarCar = $query->orderBy('created_at', 'desc')->get();

        return view('admin.record.car', compact('daftarCar'));
    }

    public function exportCar(Request $request)
    {
        $query = PengajuanCar::with(['user.role', 'user.station', 'details']);

        if ($request->filled('bulan')) {
            $query->whereMonth('created_at', $request->bulan);
        }
        $tahun = $request->get('tahun', date('Y'));
        $query->whereYear('created_at', $tahun);

        $dataCar = $query->orderBy('created_at', 'desc')->get();

        $namaBulan = $request->filled('bulan') ? Carbon::create()->month((int) $request->bulan)->isoFormat('MMMM') : 'Semua_Bulan';
        $fileName = "Record_CAR_Karyawan_META_{$namaBulan}_{$tahun}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Nama Karyawan', 'NIP', 'Station', 'Nominal Dana', 'Keperluan / Deskripsi', 'Tanggal Pengajuan', 'Status'];

        $callback = function() use($dataCar, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns, ';');

            foreach ($dataCar as $car) {
                $totalNominal = $car->details ? $car->details->sum('total_harga') : 0;
                $keperluanBarang = $car->details ? $car->details->pluck('nama_barang')->implode(', ') : '-';

                fputcsv($file, [
                    $car->user->name ?? '-',
                    $car->user->nip ?? '-',
                    $car->user->station->name ?? 'Pusat',
                    'Rp ' . number_format($totalNominal, 0, ',', '.'),
                    $keperluanBarang ?: '-',
                    $car->created_at ? $car->created_at->format('Y-m-d') : '-',
                    strtoupper($car->status_akhir ?? 'PENDING')
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    //            MANAGEMENT RECORD MPR
    // ==========================================

    public function mpr(Request $request)
    {
        $query = PengajuanMpr::with(['user.role', 'user.station', 'items']);

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_pengajuan', $request->bulan);
        }

        $tahun = $request->get('tahun', date('Y'));
        $query->whereYear('tanggal_pengajuan', $tahun);

        $daftarMpr = $query->orderBy('tanggal_pengajuan', 'desc')->get();

        return view('admin.record.mpr', compact('daftarMpr'));
    }

    public function exportMpr(Request $request)
    {
        $query = PengajuanMpr::with(['user.role', 'user.station', 'items']);

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_pengajuan', $request->bulan);
        }
        $tahun = $request->get('tahun', date('Y'));
        $query->whereYear('tanggal_pengajuan', $tahun);

        $dataMpr = $query->orderBy('tanggal_pengajuan', 'desc')->get();

        $namaBulan = $request->filled('bulan') ? Carbon::create()->month((int) $request->bulan)->isoFormat('MMMM') : 'Semua_Bulan';
        $fileName = "Record_MPR_Karyawan_META_{$namaBulan}_{$tahun}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['No. MPR', 'Nama Karyawan', 'NIP', 'Station', 'Urgensi Keperluan', 'Rincian Material', 'Estimasi Total', 'Tanggal Pengajuan', 'Status'];

        $callback = function() use($dataMpr, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns, ';');

            foreach ($dataMpr as $mpr) {
                $totalNominal = $mpr->items ? $mpr->items->sum(function($item) {
                    return $item->jumlah * $item->estimasi_harga;
                }) : 0;

                $rincianBarang = $mpr->items ? $mpr->items->map(function($item) {
                    return $item->nama_barang . ' (' . $item->jumlah . ' ' . $item->satuan . ')';
                })->implode(', ') : '-';

                fputcsv($file, [
                    $mpr->nomor_mpr,
                    $mpr->user->name ?? '-',
                    $mpr->user->nip ?? '-',
                    $mpr->user->station->name ?? 'Pusat',
                    $mpr->keperluan_urgensi ?? '-',
                    $rincianBarang ?: '-',
                    'Rp ' . number_format($totalNominal, 0, ',', '.'),
                    $mpr->tanggal_pengajuan ?? '-',
                    strtoupper($mpr->status_akhir ?? 'PENDING')
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}