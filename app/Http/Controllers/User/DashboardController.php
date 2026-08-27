<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Absen\Kehadiran;
use App\Models\Car\PengajuanCar;
use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\Mpr\PengajuanMpr;
use App\Models\User\Station;
use App\Models\User\User;
use App\Services\CalendarScheduleService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected ScheduleService $scheduleService;

    protected CalendarScheduleService $calendarService;

    public function __construct(ScheduleService $scheduleService, CalendarScheduleService $calendarService)
    {
        $this->scheduleService = $scheduleService;
        $this->calendarService = $calendarService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // Pastikan Timezone Asia/Jakarta
        $now = Carbon::now('Asia/Jakarta');
        $tahunSekarang = $now->year;
        $today = $now->format('Y-m-d');

        // 1. Ambil Seluruh Data Stasiun Resmi (Kantor, Stasiun, & Seluruh 18 Rumah Meter) untuk Geofencing
        $daftarStasiun = Station::select('id', 'name', 'type', 'kode_stasiun', 'latitude', 'longitude', 'radius_meters')->get();

        // 2. Parameter Navigasi Bulan & Tahun Kalender Activity
        $selectedMonth = (int) $request->get('month', $now->month);
        $selectedYear = (int) $request->get('year', $now->year);

        // 3. Ambil Matriks Kalender Bulanan & Deteksi Jadwal Hari Ini
        $calendarDays = $this->calendarService->getMonthlyCalendar($user, $selectedMonth, $selectedYear);
        $todaySchedule = $this->scheduleService->getTodaySchedule($user, $today);

        // 4. Ambil Transaksi Absensi Hari Ini
        $todayAttendance = Kehadiran::where('user_id', $user->id)
            ->where(function ($q) use ($today) {
                $q->whereDate('created_at', $today)
                    ->orWhere('date', $today);
            })
            ->first();

        // Cek Peringatan Terlambat & Waktunya Pulang
        $isLateNotCheckedIn = false;
        $canCheckOutNow = false;

        if (! empty($todaySchedule) && empty($todaySchedule['is_day_off'])) {
            $scheduledInStr = $todaySchedule['scheduled_in'] ?? null;
            $scheduledOutStr = $todaySchedule['scheduled_out'] ?? null;

            if ($scheduledInStr && $scheduledInStr !== '--:--') {
                try {
                    $scheduledInTime = Carbon::parse($today.' '.$scheduledInStr, 'Asia/Jakarta');
                    if ((! $todayAttendance || empty($todayAttendance->check_in)) && $now->gt($scheduledInTime)) {
                        $isLateNotCheckedIn = true;
                    }
                } catch (\Exception $e) {
                }
            }

            if ($scheduledOutStr && $scheduledOutStr !== '--:--' && $todayAttendance && ! empty($todayAttendance->check_in) && empty($todayAttendance->check_out)) {
                try {
                    $scheduledInTime = Carbon::parse($today.' '.$scheduledInStr, 'Asia/Jakarta');
                    $scheduledOutTime = Carbon::parse($today.' '.$scheduledOutStr, 'Asia/Jakarta');

                    if ($scheduledOutTime->lt($scheduledInTime)) {
                        $scheduledOutNextDay = $scheduledOutTime->copy()->addDay();
                        if ($now->gte($scheduledOutNextDay) || ($now->lt($scheduledInTime) && $now->gte($scheduledOutTime))) {
                            $canCheckOutNow = true;
                        }
                    } else {
                        if ($now->gte($scheduledOutTime)) {
                            $canCheckOutNow = true;
                        }
                    }
                } catch (\Exception $e) {
                }
            }
        }

        // 5. Penentuan ID Cuti Tahunan secara Aman
        $cutiTahunanId = defined('App\Models\User\User::CUTI_TAHUNAN_ID')
            ? User::CUTI_TAHUNAN_ID
            : optional(JenisCuti::where('name_cuti', 'LIKE', '%Tahunan%')->orWhere('name_cuti', 'Cuti')->first())->id;

        // Data Saldo Cuti
        $saldoTahunan = SaldoCuti::where('user_id', $user->id)
            ->when($cutiTahunanId, function ($q) use ($cutiTahunanId) {
                $q->where('jenis_cuti_id', $cutiTahunanId);
            })
            ->where('tahun', $tahunSekarang)
            ->first();

        $kuotaTahunan = $saldoTahunan->kuota_awal ?? 12;

        // 6. Total Cuti Diambil & Pending
        $totalCutiDiambil = (int) DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->when($cutiTahunanId, function ($q) use ($cutiTahunanId) {
                $q->where('jenis_cuti_id', $cutiTahunanId);
            })
            ->where('status_akhir', 'approved')
            ->whereYear('tanggal_mulai', $tahunSekarang)
            ->sum('total_hari');

        // Hitung pending cuti
        $pendingCuti = DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->where('status_akhir', 'pending')
            ->count();

        // Hitung pending CAR
        $pendingCar = DB::table('pengajuan_cars')
            ->where('user_id', $user->id)
            ->where('status_akhir', 'pending')
            ->count();

        // Hitung pending MPR
        $pendingMpr = DB::table('pengajuan_mprs')
            ->where('user_id', $user->id)
            ->where('status_akhir', 'pending')
            ->count();

        $totalPending = $pendingCuti + $pendingCar + $pendingMpr;

        // 7. Hitung Sisa Kuota Real-Time
        $sisaKuota = $saldoTahunan->sisa_saldo ?? max(0, $kuotaTahunan - $totalCutiDiambil);

        // 8. Sisa Saldo Cuti Haid (Khusus Karyawan Perempuan)
        $isPerempuan = $user->isPerempuan();
        $saldoCutiHaid = 0;

        if ($isPerempuan) {
            $subHaid = DB::table('sub_cutis')
                ->where('nama_sub_cuti', 'LIKE', '%Haid%')
                ->first();
            $subHaidId = $subHaid ? $subHaid->id : 2;

            $haidDiambilBulanIni = (int) DB::table('pengajuan_cutis')
                ->where('user_id', $user->id)
                ->where('sub_cuti_id', $subHaidId)
                ->where('status_akhir', 'approved')
                ->whereMonth('tanggal_mulai', $now->month)
                ->whereYear('tanggal_mulai', $now->year)
                ->sum('total_hari');

            $saldoCutiHaid = max(0, 2 - $haidDiambilBulanIni);
        }

        // 9. Riwayat Cuti, CAR, & MPR
        $riwayatCuti = DB::table('pengajuan_cutis')
            ->join('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->where('pengajuan_cutis.user_id', $user->id)
            ->select('pengajuan_cutis.*', 'jenis_cutis.name_cuti', 'sub_cutis.nama_sub_cuti')
            ->orderBy('pengajuan_cutis.created_at', 'desc')
            ->take(5)
            ->get();

        $riwayatCar = PengajuanCar::with('details')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $riwayatMpr = PengajuanMpr::with('items')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $currentCarbonDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1, 'Asia/Jakarta');

        return view('dashboard.dashboardindex', compact(
            'user',
            'daftarStasiun',
            'kuotaTahunan',
            'totalCutiDiambil',
            'totalPending',
            'sisaKuota',
            'isPerempuan',
            'saldoCutiHaid',
            'riwayatCuti',
            'riwayatCar',
            'riwayatMpr',
            'todaySchedule',
            'todayAttendance',
            'calendarDays',
            'selectedMonth',
            'selectedYear',
            'currentCarbonDate',
            'isLateNotCheckedIn',
            'canCheckOutNow'
        ));
    }
}
