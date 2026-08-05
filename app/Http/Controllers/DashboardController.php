<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PengajuanCar;
use App\Models\Kehadiran;
use App\Models\SaldoCuti;
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
        $selectedMonth = $request->get('month', Carbon::now()->month);
        $selectedYear = $request->get('year', Carbon::now()->year);

        // 2. Ambil Matriks Kalender Bulanan & Deteksi Jadwal Hari Ini
        $calendarDays = $this->calendarService->getMonthlyCalendar($user, $selectedMonth, $selectedYear);
        $todaySchedule = $this->scheduleService->getTodaySchedule($user, $today);

        // 3. Ambil Transaksi Absensi Hari Ini
        $todayAttendance = Kehadiran::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // 4. Data Saldo Cuti & CAR Existing
        // Pastikan nama kolom konsisten 'sisa_saldo'
        $saldoTahunan = SaldoCuti::firstOrCreate(
            [
                'user_id'       => $user->id,
                'jenis_cuti_id' => User::CUTI_TAHUNAN_ID,
                'tahun'         => $tahunSekarang,
            ],
            [
                'kuota_awal' => 12,
                'sisa_saldo' => 12,
            ]
        );

        $kuotaTahunan = $saldoTahunan->kuota_awal;

        $riwayatCar = PengajuanCar::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Total hari cuti tahunan yang telah disetujui (Approved)
        $totalCutiDiambil = DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->where('jenis_cuti_id', User::CUTI_TAHUNAN_ID)
            ->where('status_akhir', 'approved')
            ->whereYear('tanggal_mulai', $tahunSekarang)
            ->sum('total_hari');

        // Total pengajuan yang masih dalam antrean (Pending)
        $totalPending = DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->where('status_akhir', 'pending')
            ->count();

        // 1. Total hari cuti tahunan yang telah disetujui (Approved)
        $totalCutiDiambil = (int) DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->where('jenis_cuti_id', User::CUTI_TAHUNAN_ID)
            ->where('status_akhir', 'approved')
            ->whereYear('tanggal_mulai', $tahunSekarang)
            ->sum('total_hari');

        // 2. Hitung Sisa Kuota Real-Time (Kuota Awal - Total Cuti Diambil)
        $sisaKuota = max(0, $kuotaTahunan - $totalCutiDiambil);

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