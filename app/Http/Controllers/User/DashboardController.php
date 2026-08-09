<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User\User;
use App\Models\Car\PengajuanCar;
use App\Models\Absen\Kehadiran;
use App\Models\Cuti\SaldoCuti;
use App\Services\ScheduleService;
use App\Services\CalendarScheduleService;
use Carbon\Carbon;

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
        $tahunSekarang = now()->year;
        $today = Carbon::today()->format('Y-m-d');

        // 1. Parameter Navigasi Bulan & Tahun Kalender Activity
        $selectedMonth = (int) $request->get('month', Carbon::now()->month);
        $selectedYear = (int) $request->get('year', Carbon::now()->year);

        // 2. Ambil Matriks Kalender Bulanan & Deteksi Jadwal Hari Ini
        $calendarDays = $this->calendarService->getMonthlyCalendar($user, $selectedMonth, $selectedYear);
        $todaySchedule = $this->scheduleService->getTodaySchedule($user, $today);

        // 3. Ambil Transaksi Absensi Hari Ini
        $todayAttendance = Kehadiran::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // 4. Data Saldo Cuti (Proteksi agar tidak error untuk Pria & Wanita)
        $saldoTahunan = SaldoCuti::where('user_id', $user->id)
            ->where('jenis_cuti_id', User::CUTI_TAHUNAN_ID)
            ->where('tahun', $tahunSekarang)
            ->first();

        // Jika data saldo belum terbuat, gunakan nilai default tanpa bikin crash
        $kuotaTahunan = $saldoTahunan->kuota_awal ?? 12;

        // 5. Total Cuti Diambil & Pending
        $totalCutiDiambil = (int) DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->where('jenis_cuti_id', User::CUTI_TAHUNAN_ID)
            ->where('status_akhir', 'approved')
            ->whereYear('tanggal_mulai', $tahunSekarang)
            ->sum('total_hari');

        // Hitung pending cuti
        $pendingCuti = DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->where('status_akhir', 'pending')
            ->count();

        // Hitung pending CAR (Sesuaikan nama tabel jika berbeda, misal: 'pengajuan_cars' atau 'cars')
        $pendingCar = DB::table('pengajuan_cars')
            ->where('user_id', $user->id)
            ->where('status_akhir', 'pending')
            ->count();

        // Gabungkan total pending cuti dan CAR
        $totalPending = $pendingCuti + $pendingCar;  

        // 6. Hitung Sisa Kuota Real-Time
        $sisaKuota = $saldoTahunan->sisa_saldo ?? max(0, $kuotaTahunan - $totalCutiDiambil);

        // 7. Riwayat CAR & Cuti
        $riwayatCar = PengajuanCar::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $riwayatCuti = DB::table('pengajuan_cutis')
            ->join('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->where('pengajuan_cutis.user_id', $user->id)
            ->select('pengajuan_cutis.*', 'jenis_cutis.name_cuti', 'sub_cutis.nama_sub_cuti')
            ->orderBy('pengajuan_cutis.created_at', 'desc')
            ->take(5)
            ->get();

        $currentCarbonDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1);

        return view('dashboard.index', compact(
            'user',
            'kuotaTahunan',
            'totalCutiDiambil',
            'totalPending',
            'sisaKuota',
            'riwayatCuti',
            'riwayatCar',
            'todaySchedule',
            'todayAttendance',
            'calendarDays',
            'selectedMonth',
            'selectedYear',
            'currentCarbonDate'
        ));
    }
}