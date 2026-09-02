<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car\PengajuanCar;
use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Mpr\PengajuanMpr;
use App\Models\User\Station;
use App\Models\User\User;
use App\Traits\CutiHelperTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    use CutiHelperTrait;

    // =========================================================================
    //                        REKAPITULASI RECORD CUTI
    // =========================================================================

    // Menampilkan halaman rekapitulasi data pengajuan cuti karyawan
    public function cuti(Request $request)
    {
        $todayStr = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $periode = $request->input('periode', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Dukungan filter periode tanggal fleksibel (hari ini, minggu ini, bulan ini, atau kustom)
        if ($request->filled('bulan')) {
            $tahun = $request->get('tahun', date('Y'));
            $startDate = Carbon::createFromDate($tahun, $request->bulan, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($tahun, $request->bulan, 1)->endOfMonth()->format('Y-m-d');
            $periode = 'month';
        } elseif ($periode === 'today') {
            $startDate = $todayStr;
            $endDate = $todayStr;
        } elseif ($periode === 'week') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfWeek()->format('Y-m-d');
            $endDate = $now->copy()->endOfWeek()->format('Y-m-d');
        } elseif ($periode === 'month') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        } elseif ($periode === 'all') {
            $startDate = null;
            $endDate = null;
        } else {
            // Rentang tanggal kustom
            $startDate = $startDate ?: $todayStr;
            $endDate = $endDate ?: $startDate;
        }

        if ($startDate && $endDate && $startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        $userId = $request->input('user_id');
        $stationId = $request->input('station_id');
        $jenisCutiId = $request->input('jenis_cuti_id');
        $status = $request->input('status', 'all');

        $query = PengajuanCuti::with([
            'user.role',
            'user.station',
            'jenisCuti',
            'subCuti',
            'approverTahap1.role',
            'approverTahap2.role',
        ]);

        if ($startDate && $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                  ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->where('tanggal_mulai', '<=', $startDate)
                          ->where('tanggal_selesai', '>=', $endDate);
                  });
            });
        }

        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }
        if (!empty($stationId)) {
            $query->whereHas('user', fn ($q) => $q->where('station_id', $stationId));
        }
        if (!empty($jenisCutiId)) {
            $query->where('jenis_cuti_id', $jenisCutiId);
        }
        if ($status === 'approved') {
            $query->where('status_akhir', 'approved');
        } elseif ($status === 'pending') {
            $query->where('status_akhir', 'pending');
        } elseif ($status === 'rejected') {
            $query->where('status_akhir', 'rejected');
        }

        // Kalkulasi Metrics
        $metricsQuery = clone $query;
        $allRecords = $metricsQuery->get();

        $metrics = [
            'total'    => $allRecords->count(),
            'approved' => $allRecords->where('status_akhir', 'approved')->count(),
            'pending'  => $allRecords->where('status_akhir', 'pending')->count(),
            'rejected' => $allRecords->where('status_akhir', 'rejected')->count(),
        ];

        $daftarCuti = $query->orderBy('tanggal_mulai', 'desc')->paginate(25)->withQueryString();

        $stations = Station::orderBy('name', 'asc')->get();
        $karyawanList = User::orderBy('name', 'asc')->select('id', 'name', 'nip')->get();
        $jenisCutiList = JenisCuti::orderBy('name_cuti', 'asc')->get();

        $filters = [
            'periode'       => $periode,
            'start_date'    => $startDate ?: '',
            'end_date'      => $endDate ?: '',
            'user_id'       => $userId,
            'station_id'    => $stationId,
            'jenis_cuti_id' => $jenisCutiId,
            'status'        => $status,
            'bulan'         => $request->input('bulan', ''),
            'tahun'         => $request->input('tahun', date('Y')),
        ];

        return view('admin.record.recordcuti', compact(
            'daftarCuti',
            'metrics',
            'filters',
            'stations',
            'karyawanList',
            'jenisCutiList'
        ));
    }

    // Ekspor data rekapitulasi cuti ke format file CSV (dengan UTF-8 BOM)
    public function exportCuti(Request $request)
    {
        $todayStr = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $periode = $request->input('periode', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($request->filled('bulan')) {
            $tahun = $request->get('tahun', date('Y'));
            $startDate = Carbon::createFromDate($tahun, $request->bulan, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($tahun, $request->bulan, 1)->endOfMonth()->format('Y-m-d');
        } elseif ($periode === 'today') {
            $startDate = $todayStr;
            $endDate = $todayStr;
        } elseif ($periode === 'week') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfWeek()->format('Y-m-d');
            $endDate = $now->copy()->endOfWeek()->format('Y-m-d');
        } elseif ($periode === 'month') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        } elseif ($periode === 'all') {
            $startDate = null;
            $endDate = null;
        } else {
            $startDate = $startDate ?: $todayStr;
            $endDate = $endDate ?: $startDate;
        }

        if ($startDate && $endDate && $startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        $userId = $request->input('user_id');
        $stationId = $request->input('station_id');
        $jenisCutiId = $request->input('jenis_cuti_id');
        $status = $request->input('status', 'all');

        $query = PengajuanCuti::with([
            'user.role',
            'user.station',
            'jenisCuti',
            'subCuti',
            'approverTahap1.role',
            'approverTahap2.role',
        ]);

        if ($startDate && $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                  ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->where('tanggal_mulai', '<=', $startDate)
                          ->where('tanggal_selesai', '>=', $endDate);
                  });
            });
        }

        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }
        if (!empty($stationId)) {
            $query->whereHas('user', fn ($q) => $q->where('station_id', $stationId));
        }
        if (!empty($jenisCutiId)) {
            $query->where('jenis_cuti_id', $jenisCutiId);
        }
        if ($status === 'approved') {
            $query->where('status_akhir', 'approved');
        } elseif ($status === 'pending') {
            $query->where('status_akhir', 'pending');
        } elseif ($status === 'rejected') {
            $query->where('status_akhir', 'rejected');
        }

        $records = $query->orderBy('tanggal_mulai', 'desc')->get();

        $dateSuffix = ($startDate && $endDate) ? (($startDate === $endDate) ? $startDate : "{$startDate}_sd_{$endDate}") : date('Ymd');
        $fileName = "Record_Cuti_Karyawan-{$dateSuffix}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $delimiter = $request->input('delimiter', ';');

        $columns = [
            'No. Pengajuan',
            'Tanggal Diajukan',
            'Nama Karyawan',
            'NIP',
            'Jabatan / Divisi',
            'Stasiun Penugasan',
            'Jenis Cuti',
            'Sub-Cuti / Detail',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Durasi Hari Kerja',
            'Potong Saldo Kuota',
            'Verifikasi SPV (L3)',
            'Persetujuan Manager (L1/2)',
            'Status Akhir',
            'Alasan / Keperluan Cuti',
            'Catatan Penolakan',
        ];

        $callback = function () use ($records, $columns, $delimiter) {
            $file = fopen('php://output', 'w');
            // Sisipkan UTF-8 BOM agar kompatibel dengan pembaca Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns, $delimiter);

            foreach ($records as $cuti) {
                $isPotong = $this->alurPotongSaldo($cuti->jenis_cuti_id, $cuti->sub_cuti_id);
                $spvStatus = ($cuti->status_tahap_1 === 'approved') ? 'Disetujui (' . ($cuti->approverTahap1->name ?? 'SPV') . ')' : (($cuti->status_tahap_1 === 'rejected') ? 'Ditolak (' . ($cuti->approverTahap1->name ?? 'SPV') . ')' : 'Pending');
                $mgrStatus = ($cuti->status_tahap_2 === 'approved') ? 'Disetujui (' . ($cuti->approverTahap2->name ?? 'Manager') . ')' : (($cuti->status_tahap_2 === 'rejected') ? 'Ditolak (' . ($cuti->approverTahap2->name ?? 'Manager') . ')' : (($cuti->status_tahap_2 === 'not_required') ? 'Tidak Diperlukan' : 'Pending'));

                $statusAkhirLabel = match ($cuti->status_akhir) {
                    'approved' => 'Disetujui Penuh',
                    'rejected' => 'Ditolak',
                    default => 'Menunggu Persetujuan',
                };

                fputcsv($file, [
                    '#CUTI-' . sprintf('%04d', $cuti->id),
                    $cuti->created_at ? $cuti->created_at->format('Y-m-d H:i') : '-',
                    $cuti->user->name ?? '-',
                    $cuti->user->nip ?? '-',
                    $cuti->user->role->role_name ?? '-',
                    $cuti->user->station->name ?? 'Pusat',
                    $cuti->jenisCuti->name_cuti ?? 'Cuti',
                    $cuti->subCuti->nama_sub_cuti ?? '-',
                    $cuti->tanggal_mulai ? Carbon::parse($cuti->tanggal_mulai)->format('Y-m-d') : '-',
                    $cuti->tanggal_selesai ? Carbon::parse($cuti->tanggal_selesai)->format('Y-m-d') : '-',
                    (int) ($cuti->total_hari ?? 1),
                    $isPotong ? 'Ya' : 'Tidak',
                    $spvStatus,
                    $mgrStatus,
                    $statusAkhirLabel,
                    $cuti->alasan_cuti ?: '-',
                    $cuti->catatan_penolakan ?: '-',
                ], $delimiter);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================================================================
    //                        REKAPITULASI RECORD CAR
    // =========================================================================

    // Menampilkan halaman rekapitulasi data pengajuan CAR (Cash Advance Report)
    public function car(Request $request)
    {
        $todayStr = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $periode = $request->input('periode', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($request->filled('bulan')) {
            $tahun = $request->get('tahun', date('Y'));
            $startDate = Carbon::createFromDate($tahun, $request->bulan, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($tahun, $request->bulan, 1)->endOfMonth()->format('Y-m-d');
            $periode = 'month';
        } elseif ($periode === 'today') {
            $startDate = $todayStr;
            $endDate = $todayStr;
        } elseif ($periode === 'week') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfWeek()->format('Y-m-d');
            $endDate = $now->copy()->endOfWeek()->format('Y-m-d');
        } elseif ($periode === 'month') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        } elseif ($periode === 'all') {
            $startDate = null;
            $endDate = null;
        } else {
            $startDate = $startDate ?: $todayStr;
            $endDate = $endDate ?: $startDate;
        }

        if ($startDate && $endDate && $startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        $userId = $request->input('user_id');
        $stationId = $request->input('station_id');
        $status = $request->input('status', 'all');

        $query = PengajuanCar::with([
            'user.role',
            'user.station',
            'details',
            'approverTahap1.role',
            'approverTahap2.role',
        ]);

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        }

        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }
        if (!empty($stationId)) {
            $query->whereHas('user', fn ($q) => $q->where('station_id', $stationId));
        }
        if ($status === 'approved') {
            $query->where('status_akhir', 'approved');
        } elseif ($status === 'pending') {
            $query->where('status_akhir', 'pending');
        } elseif ($status === 'rejected') {
            $query->where('status_akhir', 'rejected');
        }

        $metricsQuery = clone $query;
        $allRecords = $metricsQuery->get();

        $totalDanaDisetujui = $allRecords->where('status_akhir', 'approved')->sum(function ($item) {
            return $item->details ? $item->details->sum('total_harga') : 0;
        });

        $metrics = [
            'total'               => $allRecords->count(),
            'approved'            => $allRecords->where('status_akhir', 'approved')->count(),
            'pending'             => $allRecords->where('status_akhir', 'pending')->count(),
            'rejected'            => $allRecords->where('status_akhir', 'rejected')->count(),
            'total_dana_disetujui' => $totalDanaDisetujui,
        ];

        $daftarCar = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        $stations = Station::orderBy('name', 'asc')->get();
        $karyawanList = User::orderBy('name', 'asc')->select('id', 'name', 'nip')->get();

        $filters = [
            'periode'    => $periode,
            'start_date' => $startDate ?: '',
            'end_date'   => $endDate ?: '',
            'user_id'    => $userId,
            'station_id' => $stationId,
            'status'     => $status,
            'bulan'      => $request->input('bulan', ''),
            'tahun'      => $request->input('tahun', date('Y')),
        ];

        return view('admin.record.recordcar', compact(
            'daftarCar',
            'metrics',
            'filters',
            'stations',
            'karyawanList'
        ));
    }

    // Ekspor data rekapitulasi CAR ke format file CSV
    public function exportCar(Request $request)
    {
        $todayStr = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $periode = $request->input('periode', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($request->filled('bulan')) {
            $tahun = $request->get('tahun', date('Y'));
            $startDate = Carbon::createFromDate($tahun, $request->bulan, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($tahun, $request->bulan, 1)->endOfMonth()->format('Y-m-d');
        } elseif ($periode === 'today') {
            $startDate = $todayStr;
            $endDate = $todayStr;
        } elseif ($periode === 'week') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfWeek()->format('Y-m-d');
            $endDate = $now->copy()->endOfWeek()->format('Y-m-d');
        } elseif ($periode === 'month') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        } elseif ($periode === 'all') {
            $startDate = null;
            $endDate = null;
        } else {
            $startDate = $startDate ?: $todayStr;
            $endDate = $endDate ?: $startDate;
        }

        if ($startDate && $endDate && $startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        $userId = $request->input('user_id');
        $stationId = $request->input('station_id');
        $status = $request->input('status', 'all');

        $query = PengajuanCar::with([
            'user.role',
            'user.station',
            'details',
            'approverTahap1.role',
            'approverTahap2.role',
        ]);

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        }

        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }
        if (!empty($stationId)) {
            $query->whereHas('user', fn ($q) => $q->where('station_id', $stationId));
        }
        if ($status === 'approved') {
            $query->where('status_akhir', 'approved');
        } elseif ($status === 'pending') {
            $query->where('status_akhir', 'pending');
        } elseif ($status === 'rejected') {
            $query->where('status_akhir', 'rejected');
        }

        $records = $query->orderBy('created_at', 'desc')->get();

        $dateSuffix = ($startDate && $endDate) ? (($startDate === $endDate) ? $startDate : "{$startDate}_sd_{$endDate}") : date('Ymd');
        $fileName = "Record_CAR_Karyawan-{$dateSuffix}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $delimiter = $request->input('delimiter', ';');

        $columns = [
            'Nomor CAR',
            'Tanggal Pengajuan',
            'Nama Pemohon',
            'NIP',
            'Jabatan',
            'Stasiun Penugasan',
            'Rekening Pencairan',
            'Keperluan / Alasan Pembelian',
            'Ringkasan Item Barang',
            'Jumlah Item',
            'Total Nominal Biaya (IDR)',
            'Verifikasi SPV (L3)',
            'Persetujuan Manager (L1/2)',
            'Status Akhir',
            'Catatan Penolakan',
        ];

        $callback = function () use ($records, $columns, $delimiter) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns, $delimiter);

            foreach ($records as $car) {
                $totalNominal = $car->details ? (int) round($car->details->sum('total_harga')) : 0;
                $itemsCount = $car->details ? $car->details->count() : 0;
                $ringkasanBarang = $car->details ? $car->details->map(fn ($d) => $d->nama_barang . ' (' . (float)$d->jumlah . ' ' . $d->satuan . ')')->implode(', ') : '-';

                $spvStatus = ($car->status_tahap_1 === 'approved') ? 'Disetujui (' . ($car->approverTahap1->name ?? 'SPV') . ')' : (($car->status_tahap_1 === 'rejected') ? 'Ditolak (' . ($car->approverTahap1->name ?? 'SPV') . ')' : 'Pending');
                $mgrStatus = ($car->status_tahap_2 === 'approved') ? 'Disetujui (' . ($car->approverTahap2->name ?? 'Manager') . ')' : (($car->status_tahap_2 === 'rejected') ? 'Ditolak (' . ($car->approverTahap2->name ?? 'Manager') . ')' : (($car->status_tahap_2 === 'not_required') ? 'Tidak Diperlukan' : 'Pending'));

                $statusAkhirLabel = match ($car->status_akhir) {
                    'approved' => 'Disetujui Penuh',
                    'rejected' => 'Ditolak',
                    default => 'Menunggu Persetujuan',
                };

                fputcsv($file, [
                    $car->nomor_car ?: ('CAR-' . sprintf('%04d', $car->id)),
                    $car->created_at ? $car->created_at->format('Y-m-d') : '-',
                    $car->user->name ?? '-',
                    $car->user->nip ?? '-',
                    $car->user->role->role_name ?? '-',
                    $car->user->station->name ?? 'Pusat',
                    $car->receiving_account ?: '-',
                    $car->alasan_pembelian ?: ($car->note_explanation ?: '-'),
                    $ringkasanBarang ?: '-',
                    $itemsCount,
                    $totalNominal, // Nilai integer murni
                    $spvStatus,
                    $mgrStatus,
                    $statusAkhirLabel,
                    $car->catatan_penolakan ?: '-',
                ], $delimiter);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================================================================
    //                        REKAPITULASI RECORD MPR
    // =========================================================================

    // Menampilkan halaman rekapitulasi data pengajuan MPR (Material Purchase Request)
    public function mpr(Request $request)
    {
        $todayStr = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $periode = $request->input('periode', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($request->filled('bulan')) {
            $tahun = $request->get('tahun', date('Y'));
            $startDate = Carbon::createFromDate($tahun, $request->bulan, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($tahun, $request->bulan, 1)->endOfMonth()->format('Y-m-d');
            $periode = 'month';
        } elseif ($periode === 'today') {
            $startDate = $todayStr;
            $endDate = $todayStr;
        } elseif ($periode === 'week') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfWeek()->format('Y-m-d');
            $endDate = $now->copy()->endOfWeek()->format('Y-m-d');
        } elseif ($periode === 'month') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        } elseif ($periode === 'all') {
            $startDate = null;
            $endDate = null;
        } else {
            $startDate = $startDate ?: $todayStr;
            $endDate = $endDate ?: $startDate;
        }

        if ($startDate && $endDate && $startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        $userId = $request->input('user_id');
        $stationId = $request->input('station_id');
        $priority = $request->input('priority');
        $status = $request->input('status', 'all');

        $query = PengajuanMpr::with([
            'user.role',
            'user.station',
            'items',
            'approverTahap1.role',
            'approverTahap2.role',
            'supervisor',
            'manager',
        ]);

        if ($startDate && $endDate) {
            $query->whereDate('tanggal_pengajuan', '>=', $startDate)
                  ->whereDate('tanggal_pengajuan', '<=', $endDate);
        }

        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }
        if (!empty($stationId)) {
            $query->whereHas('user', fn ($q) => $q->where('station_id', $stationId));
        }
        if (!empty($priority)) {
            $query->where('priority', $priority);
        }
        if ($status === 'approved') {
            $query->where('status_akhir', 'approved');
        } elseif ($status === 'pending') {
            $query->where('status_akhir', 'pending');
        } elseif ($status === 'rejected') {
            $query->where('status_akhir', 'rejected');
        }

        $metricsQuery = clone $query;
        $allRecords = $metricsQuery->get();

        $totalNilaiPengadaan = $allRecords->where('status_akhir', 'approved')->sum(function ($item) {
            return $item->items ? $item->items->sum(fn ($i) => (float)$i->jumlah * (float)$i->estimasi_harga) : 0;
        });

        $metrics = [
            'total'                 => $allRecords->count(),
            'emergency'             => $allRecords->filter(fn ($m) => strtolower($m->priority ?? '') === 'emergency')->count(),
            'approved'              => $allRecords->where('status_akhir', 'approved')->count(),
            'pending'               => $allRecords->where('status_akhir', 'pending')->count(),
            'rejected'              => $allRecords->where('status_akhir', 'rejected')->count(),
            'total_nilai_pengadaan' => $totalNilaiPengadaan,
        ];

        $daftarMpr = $query->orderBy('tanggal_pengajuan', 'desc')->paginate(25)->withQueryString();

        $stations = Station::orderBy('name', 'asc')->get();
        $karyawanList = User::orderBy('name', 'asc')->select('id', 'name', 'nip')->get();

        $filters = [
            'periode'    => $periode,
            'start_date' => $startDate ?: '',
            'end_date'   => $endDate ?: '',
            'user_id'    => $userId,
            'station_id' => $stationId,
            'priority'   => $priority,
            'status'     => $status,
            'bulan'      => $request->input('bulan', ''),
            'tahun'      => $request->input('tahun', date('Y')),
        ];

        return view('admin.record.recordmpr', compact(
            'daftarMpr',
            'metrics',
            'filters',
            'stations',
            'karyawanList'
        ));
    }

    // Ekspor data rekapitulasi MPR ke format file CSV
    public function exportMpr(Request $request)
    {
        $todayStr = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $periode = $request->input('periode', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($request->filled('bulan')) {
            $tahun = $request->get('tahun', date('Y'));
            $startDate = Carbon::createFromDate($tahun, $request->bulan, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($tahun, $request->bulan, 1)->endOfMonth()->format('Y-m-d');
        } elseif ($periode === 'today') {
            $startDate = $todayStr;
            $endDate = $todayStr;
        } elseif ($periode === 'week') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfWeek()->format('Y-m-d');
            $endDate = $now->copy()->endOfWeek()->format('Y-m-d');
        } elseif ($periode === 'month') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        } elseif ($periode === 'all') {
            $startDate = null;
            $endDate = null;
        } else {
            $startDate = $startDate ?: $todayStr;
            $endDate = $endDate ?: $startDate;
        }

        if ($startDate && $endDate && $startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        $userId = $request->input('user_id');
        $stationId = $request->input('station_id');
        $priority = $request->input('priority');
        $status = $request->input('status', 'all');

        $query = PengajuanMpr::with([
            'user.role',
            'user.station',
            'items',
            'approverTahap1.role',
            'approverTahap2.role',
            'supervisor',
            'manager',
        ]);

        if ($startDate && $endDate) {
            $query->whereDate('tanggal_pengajuan', '>=', $startDate)
                  ->whereDate('tanggal_pengajuan', '<=', $endDate);
        }

        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }
        if (!empty($stationId)) {
            $query->whereHas('user', fn ($q) => $q->where('station_id', $stationId));
        }
        if (!empty($priority)) {
            $query->where('priority', $priority);
        }
        if ($status === 'approved') {
            $query->where('status_akhir', 'approved');
        } elseif ($status === 'pending') {
            $query->where('status_akhir', 'pending');
        } elseif ($status === 'rejected') {
            $query->where('status_akhir', 'rejected');
        }

        $records = $query->orderBy('tanggal_pengajuan', 'desc')->get();

        $dateSuffix = ($startDate && $endDate) ? (($startDate === $endDate) ? $startDate : "{$startDate}_sd_{$endDate}") : date('Ymd');
        $fileName = "Record_MPR_Karyawan-{$dateSuffix}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $delimiter = $request->input('delimiter', ';');

        $columns = [
            'Nomor MPR',
            'Tanggal Pengajuan',
            'Nama Pemohon',
            'NIP',
            'Jabatan',
            'Stasiun Penugasan',
            'Titik Pengiriman (Delivery Point)',
            'Kategori Urgensi',
            'Departemen',
            'Rincian Material',
            'Jumlah Macam Barang',
            'Total Estimasi Nilai (IDR)',
            'Verifikasi SPV (L3)',
            'Persetujuan Manager (L1/2)',
            'Status Akhir',
            'Keperluan & Urgensi',
            'Catatan Penolakan',
        ];

        $callback = function () use ($records, $columns, $delimiter) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns, $delimiter);

            foreach ($records as $mpr) {
                $totalNominal = $mpr->items ? (int) round($mpr->items->sum(fn ($i) => (float)$i->jumlah * (float)$i->estimasi_harga)) : 0;
                $itemsCount = $mpr->items ? $mpr->items->count() : 0;
                $rincianBarang = $mpr->items ? $mpr->items->map(fn ($i) => $i->nama_barang . ' (' . (float)$i->jumlah . ' ' . ($i->satuan ?: 'pcs') . ')')->implode(', ') : '-';

                $spvStatus = ($mpr->status_tahap_1 === 'approved') ? 'Disetujui (' . ($mpr->approverTahap1->name ?? ($mpr->supervisor->name ?? 'SPV')) . ')' : (($mpr->status_tahap_1 === 'rejected') ? 'Ditolak' : 'Pending');
                $mgrStatus = ($mpr->status_tahap_2 === 'approved') ? 'Disetujui (' . ($mpr->approverTahap2->name ?? ($mpr->manager->name ?? 'Manager')) . ')' : (($mpr->status_tahap_2 === 'rejected') ? 'Ditolak' : (($mpr->status_tahap_2 === 'not_required') ? 'Tidak Diperlukan' : 'Pending'));

                $statusAkhirLabel = match ($mpr->status_akhir) {
                    'approved' => 'Disetujui Penuh',
                    'rejected' => 'Ditolak',
                    default => 'Menunggu Persetujuan',
                };

                fputcsv($file, [
                    $mpr->nomor_mpr ?: ('MPR-' . sprintf('%04d', $mpr->id)),
                    $mpr->tanggal_pengajuan ? Carbon::parse($mpr->tanggal_pengajuan)->format('Y-m-d') : '-',
                    $mpr->user->name ?? '-',
                    $mpr->user->nip ?? '-',
                    $mpr->user->role->role_name ?? '-',
                    $mpr->user->station->name ?? 'Pusat',
                    $mpr->delivery_point ?: 'Site Umbulan',
                    strtoupper($mpr->priority ?: 'NORMAL'),
                    $mpr->department ?: 'Operation',
                    $rincianBarang ?: '-',
                    $itemsCount,
                    $totalNominal, // Angka murni integer
                    $spvStatus,
                    $mgrStatus,
                    $statusAkhirLabel,
                    $mpr->keperluan_urgensi ?: '-',
                    $mpr->catatan_penolakan ?: '-',
                ], $delimiter);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
