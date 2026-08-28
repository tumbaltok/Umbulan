<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absen\Kehadiran;
use App\Models\Cuti\PengajuanCuti;
use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensiAdminController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index(Request $request)
    {
        $todayStr = Carbon::today('Asia/Jakarta')->format('Y-m-d');

        // 1. Identifikasi Filter Periode & Rentang Tanggal
        $periode = $request->input('periode', 'today');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Backward-compatibility jika request hanya mengirimkan 'tanggal'
        if ($request->filled('tanggal') && !$request->filled('start_date')) {
            $startDate = $request->input('tanggal');
            $endDate = $request->input('tanggal');
            $periode = ($startDate === $todayStr) ? 'today' : 'custom';
        }

        if ($periode === 'today' || (empty($startDate) && empty($endDate))) {
            $startDate = $todayStr;
            $endDate = $todayStr;
            $periode = 'today';
        } elseif ($periode === 'week') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfWeek()->format('Y-m-d');
            $endDate = $now->copy()->endOfWeek()->format('Y-m-d');
        } elseif ($periode === 'month') {
            $now = Carbon::today('Asia/Jakarta');
            $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        } else {
            // custom
            $startDate = $startDate ?: $todayStr;
            $endDate = $endDate ?: $startDate;
            $periode = 'custom';
        }

        // Pastikan urutan tanggal valid
        if ($startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        $userId = $request->input('user_id');
        $roleId = $request->input('role_id');
        $stationId = $request->input('station_id');
        $status = $request->input('status', 'all');

        // 2. Query Data Kehadiran dengan Eager Loading
        $query = Kehadiran::with([
            'user' => function ($u) {
                $u->with(['roles', 'station', 'gender']);
            },
        ])->whereBetween('date', [$startDate, $endDate]);

        // Filter Karyawan
        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

        // Filter Stasiun Penugasan (22 titik stasiun & Rumah Meter)
        if (!empty($stationId)) {
            $query->whereHas('user', function ($q) use ($stationId) {
                $q->where('station_id', $stationId);
            });
        }

        // Filter Role / Divisi
        if (!empty($roleId)) {
            $query->whereHas('user', function ($q) use ($roleId) {
                $q->where('role_id', $roleId)
                  ->orWhereHas('roles', fn ($r) => $r->where('roles.id', $roleId));
            });
        }

        // Filter Status Kehadiran
        if ($status === 'on_time') {
            $query->where('is_late', false)->whereNotNull('check_in');
        } elseif ($status === 'late') {
            $query->where('is_late', true);
        } elseif ($status === 'outside_radius') {
            $query->where(function ($q) {
                $q->where('is_in_radius_check_in', false)
                  ->orWhere('is_in_radius_check_out', false);
            });
        } elseif ($status === 'early_out') {
            $query->where('is_early_checkout', true);
        } elseif ($status === 'cuti') {
            $query->where(function ($q) {
                $q->where('shift_type', 'Cuti')
                  ->orWhere('status', 'Izin');
            });
        }

        // 3. Kalkulasi Ringkasan Metrik Statistik (Summary Cards)
        $metricsQuery = clone $query;
        $allMatchingRecords = $metricsQuery->get();

        $totalPresensi = $allMatchingRecords->whereNotNull('check_in')->count();
        $totalOnTime = $allMatchingRecords->where('is_late', false)->whereNotNull('check_in')->count();
        $totalLate = $allMatchingRecords->where('is_late', true)->count();
        $totalLuarRadius = $allMatchingRecords->filter(function ($item) {
            return (isset($item->is_in_radius_check_in) && !$item->is_in_radius_check_in) ||
                   (isset($item->is_in_radius_check_out) && !$item->is_in_radius_check_out);
        })->count();

        $onTimeRate = $totalPresensi > 0 ? round(($totalOnTime / $totalPresensi) * 100, 1) : 100.0;

        // 4. Evaluasi Ketidakhadiran (Belum Absen / Cuti) jika filter harian tunggal
        $isSingleDay = ($startDate === $endDate);
        $belumAbsen = [];
        $sedangCuti = [];
        $totalTidakHadir = 0;

        if ($isSingleDay) {
            $evalDate = $startDate;
            // Ambil semua user aktif (diterapkan filter station/role jika ada)
            $usersQuery = User::with(['roles', 'station'])->orderBy('name', 'asc');
            if (!empty($userId)) {
                $usersQuery->where('id', $userId);
            }
            if (!empty($stationId)) {
                $usersQuery->where('station_id', $stationId);
            }
            if (!empty($roleId)) {
                $usersQuery->where(function ($q) use ($roleId) {
                    $q->where('role_id', $roleId)
                      ->orWhereHas('roles', fn ($r) => $r->where('roles.id', $roleId));
                });
            }

            $usersList = $usersQuery->get();
            $attendedUserIds = $allMatchingRecords->pluck('user_id')->toArray();

            // Cek pengajuan cuti aktif pada tanggal ini
            $cutiAktifUsers = PengajuanCuti::where('status_akhir', 'approved')
                ->whereDate('tanggal_mulai', '<=', $evalDate)
                ->whereDate('tanggal_selesai', '>=', $evalDate)
                ->pluck('user_id')
                ->toArray();

            foreach ($usersList as $usr) {
                // Lewati jika sudah ada record check-in
                if (in_array($usr->id, $attendedUserIds)) {
                    continue;
                }

                // Cek jadwal kerja user
                $sched = $this->scheduleService->getTodaySchedule($usr, $evalDate);

                if (in_array($usr->id, $cutiAktifUsers)) {
                    $sedangCuti[] = [
                        'user' => $usr,
                        'reason' => 'Sedang Menjalani Cuti Resmi',
                        'schedule' => $sched,
                    ];
                    continue;
                }

                // Jika jadwal libur / off dan tidak ada kewajiban hadir
                if ($sched['is_day_off']) {
                    continue;
                }

                // Terjadwal hadir namun belum melakukan presensi
                $belumAbsen[] = [
                    'user' => $usr,
                    'schedule' => $sched,
                ];
            }

            $totalTidakHadir = count($belumAbsen) + count($sedangCuti);
        } else {
            // Pada rentang multi-hari, hitung dari record yang berstatus Cuti/Izin/Alpha
            $totalTidakHadir = $allMatchingRecords->whereIn('status', ['Izin', 'Alpha'])->count() +
                               $allMatchingRecords->where('shift_type', 'Cuti')->count();
        }

        // 5. Data Absensi Utama (Diurutkan berdasarkan tanggal terbaru dan jam masuk terbaru)
        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in', 'desc')
            ->paginate(35)
            ->withQueryString();

        // 6. Data Master untuk Filter Dropdown
        $stations = Station::orderBy('name', 'asc')->get();
        $roles = Role::orderBy('role_name', 'asc')->get();
        $karyawanList = User::orderBy('name', 'asc')->select('id', 'name', 'nip')->get();

        $metrics = [
            'total_presensi'    => $totalPresensi,
            'total_on_time'     => $totalOnTime,
            'total_late'        => $totalLate,
            'total_luar_radius' => $totalLuarRadius,
            'on_time_rate'      => $onTimeRate,
            'total_tidak_hadir' => $totalTidakHadir,
        ];

        $filters = [
            'periode'    => $periode,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'user_id'    => $userId,
            'role_id'    => $roleId,
            'station_id' => $stationId,
            'status'     => $status,
        ];

        // Untuk backward-compatibility blade jika ada yang merujuk variabel lama
        $tanggal = $startDate;
        $sudahAbsen = $attendances;

        return view('admin.record.recordabsensi', compact(
            'attendances',
            'belumAbsen',
            'sedangCuti',
            'isSingleDay',
            'stations',
            'roles',
            'karyawanList',
            'metrics',
            'filters',
            'tanggal',
            'sudahAbsen'
        ));
    }
}
