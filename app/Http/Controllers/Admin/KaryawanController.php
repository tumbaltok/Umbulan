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
use Illuminate\Support\Facades\Log;

class KaryawanController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    // Menampilkan daftar data karyawan dan struktur organisasi
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $cutiJenis = JenisCuti::where('name_cuti', 'Cuti')->first();
        $jenisCutiId = $cutiJenis ? $cutiJenis->id : null;

        // Ambil data Role lengkap untuk struktur pohon organisasi
        $daftarRole = Role::orderBy('id', 'asc')->get();

        $query = User::with([
            'roles', // Relasi multi-role karyawan
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

        $userRoles = $currentUser->roles;

        $isAdminRole = $currentUser->isLevel1() || $userRoles->contains('id', 1) || $currentUser->role_id === 1;
        $hasTopRole  = $userRoles->contains(fn($r) => empty($r->parent_role_id));

        if (! $isAdminRole && ! $hasTopRole) {
            $userRoleIds = $userRoles->pluck('id')->filter()->toArray();
            $subordinateRoleIds = Role::getAllChildRoleIds($userRoleIds);

            if (! empty($subordinateRoleIds)) {
                $query->whereHas('roles', function ($q) use ($subordinateRoleIds) {
                    $q->whereIn('roles.id', $subordinateRoleIds);
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

        // Ambil data pendukung stasiun kerja (hanya kantor & stasiun operasional, rumah meter dikecualikan)
        $daftarStasiun = Station::where('type', '!=', 'rumah_meter')->orderBy('type', 'asc')->orderBy('name', 'asc')->get();
        $daftarRumahMeter = Station::where('type', 'rumah_meter')->orderBy('kode_stasiun', 'asc')->get();
        $daftarJobdesk = collect();

        return view('admin.daftar.karyawanindex', compact('daftarKaryawan', 'daftarStasiun', 'daftarJobdesk', 'daftarRole', 'daftarRumahMeter'));
    }

    // Mengambil rincian data profil dan status kerja karyawan via JSON
    public function showDetail(int $id): JsonResponse
    {
        try {
            $currentUser = Auth::user();
            if (! $currentUser) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $karyawan = User::with(['roles', 'station', 'assignedStations', 'saldoCuti.jenisCuti'])->find($id);

            if (! $karyawan) {
                return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
            }

            // [SEC-08 FIX] Otorisasi IDOR: Validasi kepemilikan / hierarki role untuk Atasan Level 2
            $userRoles = $currentUser->roles;
            $isAdminRole = $currentUser->isLevel1() || $userRoles->contains('id', 1) || $currentUser->role_id === 1;
            $hasTopRole  = $userRoles->contains(fn($r) => empty($r->parent_role_id));

            // Jika bukan administrator dan bukan melihat profil diri sendiri, pastikan target adalah bawahan hierarkis
            if (! $isAdminRole && ! $hasTopRole && $currentUser->id !== $karyawan->id) {
                $userRoleIds = $userRoles->pluck('id')->filter()->toArray();
                $subordinateRoleIds = Role::getAllChildRoleIds($userRoleIds);
                $targetRoleIds = $karyawan->roles->pluck('id')->toArray();

                $isSubordinate = !empty(array_intersect($targetRoleIds, $subordinateRoleIds));
                if (! $isSubordinate) {
                    return response()->json([
                        'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk mengakses rincian data karyawan ini.',
                    ], 403);
                }
            }

            $todaySchedule = $this->scheduleService->getTodaySchedule($karyawan);
            $primaryRole = $karyawan->roles->where('pivot.is_primary', true)->first() ?? $karyawan->roles->first();
            $roleNames = $karyawan->roles->pluck('role_name')->implode(' / ');

            return response()->json([
                'id' => $karyawan->id,
                'nip' => $karyawan->nip ?? '-',
                'name' => $karyawan->name ?? '-',
                'email' => $karyawan->email ?? '-',
                'sektor' => optional($primaryRole)->role_name ?? 'Operasional',
                'phone_number' => $karyawan->phone_number ?? null,
                'profile_photo' => $karyawan->profile_photo ?? null,
                'role_name' => $roleNames ?: 'Tidak Ada Role',
                'role_ids' => $karyawan->roles->pluck('id')->toArray(),
                'roles' => $karyawan->roles,
                'nama_stasiun' => optional($karyawan->station)->name ?? '-',
                'is_pipeline' => $karyawan->hasRole('AREA (PIPELINE)') || $karyawan->hasRole(14),
                'assigned_stations' => $karyawan->assignedStations->map(function ($st) {
                    return [
                        'id' => $st->id,
                        'name' => $st->name,
                        'kode_stasiun' => $st->kode_stasiun,
                    ];
                }),
                'schedule_type' => $karyawan->schedule_type ?? 'reguler_5_hari',
                'schedule_label' => $karyawan->schedule_label,
                'normal_work_days' => $karyawan->schedule_type === 'reguler_6_hari' ? 'Senin - Sabtu' : ($karyawan->schedule_type === 'roster' ? '-' : 'Senin - Jumat'),
                'normal_check_in' => $karyawan->schedule_type === 'roster' ? '-' : '07:00',
                'normal_check_out' => $karyawan->schedule_type === 'reguler_6_hari' ? '16:00 (Sabtu: 12:00)' : ($karyawan->schedule_type === 'roster' ? '-' : '16:00'),
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
                'is_face_registered' => ! empty($karyawan->face_descriptor),
            ], 200);

        } catch (\Throwable $e) {
            Log::error("Gagal mengambil detail karyawan: " . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memuat data karyawan.'], 500);
        }
    }

    // Reset data biometrik wajah karyawan (khusus Administrator Level 1)
    public function resetBiometric(Request $request, int $id): JsonResponse
    {
        $currentUser = Auth::user();

        // Otoritas khusus Admin / Superadmin (Level 1)
        $isAdmin = $currentUser->isLevel1();

        if (! $isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Administrator (Level 1) yang berwenang mereset biometrik wajah.',
            ], 403);
        }

        $karyawan = User::findOrFail($id);
        $karyawan->update([
            'face_descriptor' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Data biometrik wajah karyawan {$karyawan->name} berhasil di-reset. Karyawan kini dapat melakukan perekaman wajah ulang 1x.",
        ]);
    }

    // Memperbarui sisa saldo cuti karyawan (Khusus Admin Level 1) [M-03 FIX]
    public function updateSaldoCuti(Request $request, int $id)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isLevel1() && !$currentUser->hasRole('ADMIN')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Administrator Level 1 yang berwenang memperbarui sisa saldo cuti.',
            ], 403);
        }

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

    // Memperbarui penugasan peran (role) dan stasiun Rumah Meter karyawan (Khusus Admin Level 1)
    public function updateRoles(Request $request, int $id)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isLevel1() && !$currentUser->hasRole('ADMIN')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Administrator Level 1 yang berwenang mengubah peran/jabatan karyawan.',
            ], 403);
        }

        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'level' => 'nullable|integer|in:1,2,3',
            'assigned_stations' => 'nullable|array',
            'assigned_stations.*' => 'exists:stations,id',
        ]);

        $karyawan = User::findOrFail($id);
        $roleIds = array_map('intval', $request->roles);

        $syncData = [];
        foreach ($roleIds as $idx => $rId) {
            $syncData[$rId] = ['is_primary' => ($idx === 0)];
        }

        $karyawan->roles()->sync($syncData);
        
        $updateData = [];
        if (!empty($roleIds)) {
            $updateData['role_id'] = $roleIds[0];
        }
        if ($request->filled('level')) {
            $updateData['level'] = (int)$request->level;
        }
        if (!empty($updateData)) {
            $karyawan->update($updateData);
        }

        // Sinkronisasi Rumah Meter jika karyawan memegang role AREA (PIPELINE)
        $isPipeline = $karyawan->fresh()->hasRole('AREA (PIPELINE)') || $karyawan->fresh()->hasRole(14);
        if ($isPipeline) {
            if ($request->has('assigned_stations')) {
                $karyawan->assignedStations()->sync($request->assigned_stations ?? []);
            }
        } else {
            $karyawan->assignedStations()->detach();
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Peran dan Level Akses karyawan berhasil disinkronkan!',
                'roles' => $karyawan->fresh()->roles,
                'level' => $karyawan->fresh()->level,
                'assigned_stations' => $karyawan->fresh()->assignedStations,
            ]);
        }

        return redirect()->back()->with('success', 'Peran / Jabatan karyawan berhasil disinkronkan!');
    }

    // Memperbarui lokasi penempatan stasiun kerja karyawan (Khusus Admin Level 1)
    public function updateStation(Request $request, int $id): JsonResponse
    {
        $currentUser = Auth::user();
        if (!$currentUser->isLevel1() && !$currentUser->hasRole('ADMIN')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Administrator Level 1 yang berwenang mengubah lokasi stasiun kerja karyawan.',
            ], 403);
        }

        $request->validate([
            'station_id' => 'required|integer|exists:stations,id',
        ]);

        $station = Station::where('type', '!=', 'rumah_meter')->find($request->station_id);
        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Stasiun yang dipilih tidak valid atau berupa Rumah Meter (hanya Kantor dan Stasiun Operasional yang dapat dipilih).',
            ], 422);
        }

        $karyawan = User::findOrFail($id);
        $karyawan->update([
            'station_id' => $station->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Lokasi stasiun kerja karyawan {$karyawan->name} berhasil diubah ke {$station->name}!",
            'station' => [
                'id' => $station->id,
                'name' => $station->name,
                'kode_stasiun' => $station->kode_stasiun,
                'type' => $station->type,
            ],
        ]);
    }
}
