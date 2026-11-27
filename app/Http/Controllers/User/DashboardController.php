<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Car\PengajuanCar;
use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\Cuti\SubCuti;
use App\Models\Mpr\PengajuanMpr;
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

    // Menampilkan halaman utama dashboard pengguna dengan rekapitulasi jadwal kerja dan pengajuan Cuti/CAR/MPR
    public function index(Request $request)
    {
        $user = Auth::user();

        // Waktu server (WIB)
        $now = Carbon::now('Asia/Jakarta');
        $tahunSekarang = $now->year;
        $today = $now->format('Y-m-d');

        // Parameter navigasi kalender
        $selectedMonth = (int) $request->get('month', $now->month);
        $selectedYear = (int) $request->get('year', $now->year);

        // Kalender bulanan dan jadwal kerja hari ini
        $calendarDays = $this->calendarService->getMonthlyCalendar($user, $selectedMonth, $selectedYear);
        $todaySchedule = $this->scheduleService->getTodaySchedule($user, $today);

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
        $pendingCar = PengajuanCar::where('user_id', $user->id)
            ->where('status_akhir', 'pending')
            ->count();

        // Hitung pending MPR
        $pendingMpr = PengajuanMpr::where('user_id', $user->id)
            ->where('status_akhir', 'pending')
            ->count();

        $totalPending = $pendingCuti + $pendingCar + $pendingMpr;

        // 7. Hitung Sisa Kuota Real-Time
        $sisaKuota = $saldoTahunan->sisa_saldo ?? max(0, $kuotaTahunan - $totalCutiDiambil);

        // 8. Sisa Saldo Cuti Haid (Khusus Karyawan Perempuan)
        $isPerempuan = $user->isPerempuan();
        $saldoCutiHaid = 0;

        if ($isPerempuan) {
            $subHaid = SubCuti::where('name_cuti', 'LIKE', '%Haid%')->first();
            $subHaidId = $subHaid ? $subHaid->id : 6;

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
            ->leftJoin('jenis_cutis as sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->where('pengajuan_cutis.user_id', $user->id)
            ->select('pengajuan_cutis.*', 'jenis_cutis.name_cuti', 'sub_cutis.name_cuti as nama_sub_cuti')
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
            'calendarDays',
            'selectedMonth',
            'selectedYear',
            'currentCarbonDate'
        ));
    }
}
