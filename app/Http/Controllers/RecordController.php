<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\PengajuanCar; // Pastikan Mengimport Model CAR Anda di sini
use Carbon\Carbon;

class RecordController extends Controller
{
    // ==========================================
    //            MANAGEMENT RECORD CUTI
    // ==========================================

    /**
     * Menampilkan data seluruh riwayat cuti karyawan dengan Filter
     */
    public function cuti(Request $request)
    {
        $query = PengajuanCuti::with(['user.role', 'user.station', 'jenisCuti', 'subCuti']);

        // 1. Filter Bulan
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_mulai', $request->bulan);
        }

        // 2. Filter Tahun (Default ke tahun sekarang)
        $tahun = $request->get('tahun', date('Y'));
        $query->whereYear('tanggal_mulai', $tahun);

        $daftarCuti = $query->orderBy('tanggal_mulai', 'desc')->get();

        return view('admin.record.cuti', compact('daftarCuti'));
    }

    /**
     * Export data Cuti ke Excel / CSV
     */
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

    /**
     * Menampilkan data seluruh riwayat CAR karyawan dengan Filter
     */
    public function car(Request $request)
    {
        // Sesuaikan relasi Eager Loading dengan field table CAR Anda (contoh: user)
        $query = PengajuanCar::with(['user.role', 'user.station']);

        // 1. Filter Bulan (berdasarkan created_at atau tanggal pengajuan CAR Anda)
        if ($request->filled('bulan')) {
            $query->whereMonth('created_at', $request->bulan);
        }

        // 2. Filter Tahun (Default ke tahun sekarang)
        $tahun = $request->get('tahun', date('Y'));
        $query->whereYear('created_at', $tahun);

        $daftarCar = $query->orderBy('created_at', 'desc')->get();

        return view('admin.record.car', compact('daftarCar'));
    }

    /**
     * Export data CAR ke Excel / CSV
     */
    public function exportCar(Request $request)
    {
        $query = PengajuanCar::with(['user.role', 'user.station']);

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

        // Kolom disesuaikan dengan kebutuhan berkas CAR (Nominal, Keperluan, dll)
        $columns = ['Nama Karyawan', 'NIP', 'Station', 'Nominal Dana', 'Keperluan / Deskripsi', 'Tanggal Pengajuan', 'Status'];

        $callback = function() use($dataCar, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns, ';');

            foreach ($dataCar as $car) {
                fputcsv($file, [
                    $car->user->name ?? '-',
                    $car->user->nip ?? '-',
                    $car->user->station->name ?? 'Pusat',
                    'Rp ' . number_format($car->nominal ?? 0, 0, ',', '.'), // Format mata uang rupiah
                    $car->keperluan ?? $car->deskripsi ?? '-',
                    $car->created_at ? $car->created_at->format('Y-m-d') : '-',
                    strtoupper($car->status_akhir ?? $car->status ?? 'PENDING')
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
