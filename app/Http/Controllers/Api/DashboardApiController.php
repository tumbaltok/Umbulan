<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absen\Kehadiran;
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
use Illuminate\Support\Facades\Cache;

class DashboardApiController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService,
        protected CalendarScheduleService $calendarService,
        protected ApprovalRoutingService $approvalRouting
    ) {}

    /**
     * Data ringkasan dashboard APK Native (jadwal hari ini, absensi, saldo cuti, riwayat, & kesiapan akun).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->load(['station', 'roles']);
        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $tahunSekarang = (int) date('Y');

        $stations = Cache::remember('daftar_stasiun_geofence', now()->addHours(12), function () {
            return Station::orderBy('name')->get([
                'id', 'name', 'type', 'kode_stasiun', 'latitude', 'longitude', 'radius_meters',
            ]);
        });

        $todaySchedule = $this->scheduleService->getTodaySchedule($user);
        $todayAttendance = Kehadiran::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $cutiTahunan = JenisCuti::whereNull('parent_id')
            ->whereRaw('LOWER(name_cuti) LIKE ?', ['%tahunan%'])
            ->first();

        $saldoTahunan = $cutiTahunan
            ? SaldoCuti::where('user_id', $user->id)
                ->where('jenis_cuti_id', $cutiTahunan->id)
                ->where('tahun', $tahunSekarang)
                ->value('sisa_saldo')
            : null;

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
                'today_attendance' => $todayAttendance,
                'leave_balance' => [
                    'tahun' => $tahunSekarang,
                    'sisa_cuti_tahunan' => (int) ($saldoTahunan ?? 0),
                    'quota_haid_per_bulan' => $user->isPerempuan() ? 2 : 0,
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

    /**
     * Riwayat kehadiran pengguna dengan filter bulan & tahun.
     */
    public function attendanceHistory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|between:2020,2100',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Kehadiran::where('user_id', $request->user()->id)
            ->orderByDesc('date');

        if (!empty($validated['month'])) {
            $query->whereMonth('date', (int) $validated['month']);
        }
        if (!empty($validated['year'])) {
            $query->whereYear('date', (int) $validated['year']);
        }

        $items = $query->paginate((int) ($validated['per_page'] ?? 20));

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $items->items(),
                'pagination' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'per_page' => $items->perPage(),
                    'total' => $items->total(),
                ],
            ],
        ]);
    }
}
