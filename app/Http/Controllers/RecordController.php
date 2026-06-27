<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use Carbon\Carbon;

class RecordController extends Controller
{
    /**
     * Menampilkan data seluruh riwayat cuti karyawan dengan Filter
     */
    public function index(Request $request)
    {
        // Query data pengajuan cuti yang sudah disetujui (atau semua status sesuai kebutuhan)
        $query = PengajuanCuti::with(['user.role', 'user.station', 'jenisCuti', 'subCuti']);

        // 1. Filter Bulan (opsional)
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_mulai', $request->bulan);
        }

        // 2. Filter Tahun (Default ke tahun sekarang jika tidak dipilih)
        $tahun = $request->get('tahun', date('Y'));
        $query->whereYear('tanggal_mulai', $tahun);

        // Ambil data terbaru
        $daftarCuti = $query->orderBy('tanggal_mulai', 'desc')->get();

        return view('admin.record.index', compact('daftarCuti'));
    }

    /**
     * Export data ke Excel / CSV secara langsung
     */
    public function export(Request $request)
    {
        $query = PengajuanCuti::with(['user.role', 'user.station', 'jenisCuti', 'subCuti']);

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_mulai', $request->bulan);
        }
        $tahun = $request->get('tahun', date('Y'));
        $query->whereYear('tanggal_mulai', $tahun);

        $dataCuti = $query->orderBy('tanggal_mulai', 'desc')->get();

        // PERBAIKAN DI SINI: Tambahkan casting (int) pada $request->bulan
        $namaBulan = $request->filled('bulan') ? Carbon::create()->month((int) $request->bulan)->isoFormat('MMMM') : 'Semua_Bulan';
        $fileName = "Record_Cuti_Karyawan_META_{$namaBulan}_{$tahun}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Header Kolom di Excel
        $columns = ['Nama Karyawan', 'NIP', 'Station', 'Jenis Perizinan', 'Total Hari', 'Tanggal Mulai', 'Tanggal Selesai', 'Status'];

        $callback = function() use($dataCuti, $columns) {
            $file = fopen('php://output', 'w');
            // Menambahkan BOM UTF-8 agar Excel Windows otomatis membaca tanda pisah dengan rapi
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, $columns, ';');

            foreach ($dataCuti as $cuti) {
                $perihal = $cuti->subCuti ? $cuti->subCuti->nama_sub_cuti : ($cuti->jenisCuti->name_cuti ?? 'Cuti/Izin');
                fputcsv($file, [
                    $cuti->user->name ?? '-',
                    $cuti->user->nip ?? '-',
                    $cuti->user->station->nama_stasiun ?? 'Pusat',
                    $perihal,
                    ($cuti->total_hari ?? $cuti->durasi_hari) . ' Hari', // Disesuaikan dengan view agar aman
                    $cuti->tanggal_mulai,
                    $cuti->tanggal_selesai,
                    strtoupper($cuti->status_akhir ?? 'PENDING')
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
