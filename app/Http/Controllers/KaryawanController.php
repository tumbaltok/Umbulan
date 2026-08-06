<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Services\ScheduleService;

class KaryawanController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index()
    {
        $daftarKaryawan = User::with(['role', 'station', 'cuti_aktif'])
            ->orderBy('name', 'asc')
            ->get();

        $daftarKaryawan->transform(function ($karyawan) {
            $karyawan->status_detail = $this->scheduleService->getWorkingStatusText($karyawan);
            return $karyawan;
        });

        return view('admin.karyawan.index', compact('daftarKaryawan'));
    }

    public function showDetail(int $id): JsonResponse
    {
        try {
            $karyawan = User::with(['role', 'station'])->find($id);

            if (!$karyawan) {
                return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
            }

            $todaySchedule = $this->scheduleService->getTodaySchedule($karyawan);

            $normalWorkDaysFormatted = [];
            if ($karyawan->schedule_type === 'normal' && is_array($karyawan->normal_work_days)) {
                $dayMap = [
                    'Mon' => 'Senin',
                    'Tue' => 'Selasa',
                    'Wed' => 'Rabu',
                    'Thu' => 'Kamis',
                    'Fri' => 'Jumat',
                    'Sat' => 'Sabtu',
                    'Sun' => 'Minggu'
                ];
                foreach ($karyawan->normal_work_days as $day) {
                    if (isset($dayMap[$day])) {
                        $normalWorkDaysFormatted[] = $dayMap[$day];
                    }
                }
            }

            return response()->json([
                'id'                 => $karyawan->id,
                'nip'                => $karyawan->nip ?? '-',
                'name'               => $karyawan->name ?? '-',
                'email'              => $karyawan->email ?? '-',
                'phone_number'       => $karyawan->phone_number ?? null,
                'profile_photo'      => $karyawan->profile_photo ?? null,
                'role_name'          => optional($karyawan->role)->role_name ?? 'Tidak Ada Role',
                'nama_stasiun'       => optional($karyawan->station)->name ?? '-',
                'job_title'          => $karyawan->job_title ?? 'Belum Memilih',
                
                // DATA JADWAL KERJA
                'schedule_type'      => $karyawan->schedule_type ?? 'normal',
                'normal_work_days'   => !empty($normalWorkDaysFormatted) ? implode(', ', $normalWorkDaysFormatted) : 'Senin - Jumat',
                'normal_check_in'    => $karyawan->normal_check_in ? substr($karyawan->normal_check_in, 0, 5) : '08:00',
                'normal_check_out'   => $karyawan->normal_check_out ? substr($karyawan->normal_check_out, 0, 5) : '17:00',
                'today_shift'        => $todaySchedule['shift_name'] ?? '-',
                'today_shift_type'   => $todaySchedule['shift_type'] ?? 'libur',
                'today_scheduled_in' => $todaySchedule['scheduled_in'] ? substr($todaySchedule['scheduled_in'], 0, 5) : null,
                'today_scheduled_out'=> $todaySchedule['scheduled_out'] ? substr($todaySchedule['scheduled_out'], 0, 5) : null,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }
}