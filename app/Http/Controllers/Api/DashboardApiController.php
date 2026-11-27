<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car\PengajuanCar;
use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\Mpr\PengajuanMpr;
use App\Models\User\Station;
use App\Services\ApprovalRoutingService;
use App\Services\CalendarScheduleService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService,
        protected CalendarScheduleService $calendarService,
        protected ApprovalRoutingService $approvalRouting
    ) {}

    /**
     * Data ringkasan dashboard APK Native (jadwal hari ini, saldo cuti, riwayat pengajuan, & kesiapan akun).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->load(['station', 'roles']);
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $tahunSekarang = (int) date('Y');

        $stations = Station::orderBy('name')->get([
            'id', 'name', 'type', 'kode_stasiun',
        ]);

        $todaySchedule = $this->scheduleService->getTodaySchedule($user);

        $cutiTahunan = JenisCuti::whereNull('parent_id')
            ->where(function ($q) {
                $q->where('kode_cuti', 'CT')
                  ->orWhereRaw('LOWER(name_cuti) LIKE ?', ['%tahunan%'])
                  ->orWhere('name_cuti', 'Cuti');
            })
            ->first();

        $saldoTahunanRow = $cutiTahunan
            ? SaldoCuti::where('user_id', $user->id)
                ->where('jenis_cuti_id', $cutiTahunan->id)
                ->where('tahun', $tahunSekarang)
                ->first()
            : null;

        $kuotaTahunan = (int) ($saldoTahunanRow->kuota_awal ?? $cutiTahunan?->kuota_default ?? 12);
        $sisaSaldoTahunan = (int) ($saldoTahunanRow->sisa_saldo ?? 0);
        $cutiTerpakai = max(0, $kuotaTahunan - $sisaSaldoTahunan);
        $pendingHari = $cutiTahunan
            ? (int) PengajuanCuti::where('user_id', $user->id)
                ->where('jenis_cuti_id', $cutiTahunan->id)
                ->where('status_akhir', 'pending')
                ->sum('total_hari')
            : 0;

        $sisaCutiHaid = 0;
        if ($user->isPerempuan()) {
            $haidSub = \App\Models\Cuti\SubCuti::whereRaw('LOWER(name_cuti) LIKE ?', ['%haid%'])->first();
            $haidQuery = PengajuanCuti::where('user_id', $user->id)
                ->whereIn('status_akhir', ['pending', 'approved'])
                ->whereMonth('tanggal_mulai', $now->month)
                ->whereYear('tanggal_mulai', $tahunSekarang);
            if ($haidSub) {
                $haidQuery->where('sub_cuti_id', $haidSub->id);
            }
            $sisaCutiHaid = max(0, 2 - (int) $haidQuery->sum('total_hari'));
        }

        $recentCuti = PengajuanCuti::with(['jenisCuti:id,name_cuti', 'subCuti:id,name_cuti'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $recentCar = PengajuanCar::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $recentMpr = PengajuanMpr::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $pendingCutiCount = $this->approvalRouting
            ->applyPendingApprovalScope(PengajuanCuti::query(), $user, 'cuti')
            ->count();
        $pendingCarCount = $this->approvalRouting
            ->applyPendingApprovalScope(PengajuanCar::query(), $user, 'car')
            ->count();
        $pendingMprCount = $this->approvalRouting
            ->applyPendingApprovalScope(PengajuanMpr::query(), $user, 'mpr')
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'stations' => $stations,
                'today' => $today,
                'today_schedule' => $todaySchedule,
                'leave_balance' => [
                    'tahun' => $tahunSekarang,
                    'kuota_tahunan' => $kuotaTahunan,
                    'sisa_cuti_tahunan' => $sisaSaldoTahunan,
                    'cuti_terpakai' => $cutiTerpakai,
                    'pending_hari' => $pendingHari,
                    'quota_haid_per_bulan' => $user->isPerempuan() ? 2 : 0,
                    'sisa_cuti_haid' => $sisaCutiHaid,
                ],
                'recent_cuti' => $recentCuti,
                'recent_car' => $recentCar,
                'recent_mpr' => $recentMpr,
                'pending_approvals_count' => [
                    'cuti' => $pendingCutiCount,
                    'car' => $pendingCarCount,
                    'mpr' => $pendingMprCount,
                    'total' => $pendingCutiCount + $pendingCarCount + $pendingMprCount,
                ],
                'unread_notifications_count' => $user->unreadNotifications()->count(),
                'is_account_complete' => $user->isAccountComplete(),
                'completion_status' => $user->getAccountCompletionStatus(),
            ],
        ]);
    }

    /**
     * Kalender jadwal kerja bulanan dan hari libur nasional.
     */
    public function calendar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|between:2020,2100',
        ]);

        $now = Carbon::now('Asia/Jakarta');
        $month = (int) ($validated['month'] ?? $now->month);
        $year = (int) ($validated['year'] ?? $now->year);

        return response()->json([
            'status' => 'success',
            'data' => [
                'month' => $month,
                'year' => $year,
                'days' => $this->calendarService->getMonthlyCalendar($request->user(), $month, $year),
                'holidays' => $this->calendarService->getNationalHolidays($year),
            ],
        ]);
    }
}
