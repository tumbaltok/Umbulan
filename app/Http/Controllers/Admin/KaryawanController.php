<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\User\Jobdesk;
use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KaryawanController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $cutiJenis = JenisCuti::where('name_cuti', 'Cuti')->first();
        $jenisCutiId = $cutiJenis ? $cutiJenis->id : null;

        // Ambil data Role lengkap untuk dirender pada struktur pohon organisasi
        $daftarRole = Role::orderBy('level', 'asc')->get();

        $query = User::with([
            'role',
            'station',
            'supervisor',
            'manager',
            'saldoCuti' => function ($q) use ($jenisCutiId) {
                if ($jenisCutiId) {
                    $q->where('jenis_cuti_id', $jenisCutiId);
                }
            },
            'pengajuanCuti' => function ($q) use ($today) {
                $q->where('status_akhir', 'approved')
                    ->whereDate('tanggal_mulai', '<=', $today)
                    ->whereDate('tanggal_selesai', '>=', $today);
            },
        ]);

        // PERBAIKAN: Jika user bukan Top Level (BOD), batasi daftar karyawan berdasarkan cabang kodenya (tree_code)
        if (!empty($currentUser->role->parent_role_id)) {
            $userTreeCode = $currentUser->role->tree_code;
            if ($userTreeCode) {
                $query->whereHas('role', function ($q) use ($userTreeCode) {
                    $q->where('tree_code', 'LIKE', $userTreeCode . '%');
                });
            }
        }

        $daftarKaryawan = $query->orderBy('name', 'asc')->get();

        $daftarKaryawan->transform(function ($karyawan) {
            $sisaCuti = $karyawan->saldoCuti->first() ?? null;
            $karyawan->sisaCutiUtama = $sisaCuti ? $sisaCuti->sisa_saldo : 12;
            $karyawan->cuti_aktif = $karyawan->pengajuanCuti;

            if ($karyawan->cuti_aktif->isEmpty()) {
                $todaySchedule = $this->scheduleService->getTodaySchedule($karyawan);
                $shiftType = $todaySchedule['shift_type'] ?? 'libur';

                if ($shiftType === 'pagi') {
                    $karyawan->status_detail = [
                        'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'dot_class' => 'bg-emerald-500',
                        'is_on' => true,
                        'label' => 'Shift Pagi',
                    ];
                } elseif ($shiftType === 'malam') {
                    $karyawan->status_detail = [
                        'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'dot_class' => 'bg-indigo-500',
                        'is_on' => true,
                        'label' => 'Shift Malam',
                    ];
                } else {
                    $karyawan->status_detail = [
                        'badge_class' => 'bg-slate-50 text-slate-600 border-slate-200',
                        'dot_class' => 'bg-slate-400',
                        'is_on' => false,
                        'label' => 'Standby / Libur',
                    ];
                }
            }

            return $karyawan;
        });

        // Ambil data pendukung filter pohon organisasi
        $daftarStasiun = Station::orderBy('name', 'asc')->get();
        $daftarJobdesk = Jobdesk::orderBy('job_title', 'asc')->get();

        return view('admin.daftar.karyawanindex', compact('daftarKaryawan', 'daftarStasiun', 'daftarJobdesk', 'daftarRole'));
    }

    public function showDetail(int $id): JsonResponse
    {
        try {
            $karyawan = User::with(['role', 'station', 'saldoCuti.jenisCuti'])->find($id);

            if (! $karyawan) {
                return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
            }

            $todaySchedule = $this->scheduleService->getTodaySchedule($karyawan);

            return response()->json([
                'id' => $karyawan->id,
                'nip' => $karyawan->nip ?? '-',
                'name' => $karyawan->name ?? '-',
                'email' => $karyawan->email ?? '-',
                'sektor' => optional($karyawan->role)->role_name ?? 'Operasional',
                'phone_number' => $karyawan->phone_number ?? null,
                'profile_photo' => $karyawan->profile_photo ?? null,
                'role_name' => optional($karyawan->role)->role_name ?? 'Tidak Ada Role',
                'nama_stasiun' => optional($karyawan->station)->name ?? '-',
                'job_title' => $karyawan->job_title ?? 'Belum Memilih',
                'schedule_type' => $karyawan->schedule_type ?? 'normal',
                'normal_work_days' => is_array($karyawan->normal_work_days) ? implode(', ', $karyawan->normal_work_days) : ($karyawan->normal_work_days ?? 'Senin - Jumat'),
                'normal_check_in' => $karyawan->normal_check_in ? Carbon::parse($karyawan->normal_check_in)->format('H:i') : '08:00',
                'normal_check_out' => $karyawan->normal_check_out ? Carbon::parse($karyawan->normal_check_out)->format('H:i') : '17:00',
                'today_shift' => $todaySchedule['shift_name'] ?? '-',
                'today_shift_type' => $todaySchedule['shift_type'] ?? 'libur',
                'today_scheduled_in' => ! empty($todaySchedule['scheduled_in']) ? Carbon::parse($todaySchedule['scheduled_in'])->format('H:i') : null,
                'today_scheduled_out' => ! empty($todaySchedule['scheduled_out']) ? Carbon::parse($todaySchedule['scheduled_out'])->format('H:i') : null,
                'saldo_cuti' => $karyawan->saldoCuti ? $karyawan->saldoCuti->map(function ($saldo) {
                    return [
                        'id' => $saldo->id,
                        'nama_cuti' => optional($saldo->jenisCuti)->name_cuti ?? 'Cuti',
                        'sisa_saldo' => $saldo->sisa_saldo,
                    ];
                }) : [],
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan server: '.$e->getMessage()], 500);
        }
    }

    public function updateSaldoCuti(Request $request, int $id)
    {
        $request->validate([
            'sisa_saldo' => 'required|integer|min:0',
        ]);

        if ($id > 0) {
            $saldo = SaldoCuti::findOrFail($id);
            $saldo->update(['sisa_saldo' => $request->sisa_saldo]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sisa saldo cuti berhasil diperbarui!',
        ]);
    }
}
