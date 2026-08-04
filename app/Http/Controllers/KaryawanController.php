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
            $karyawan->status_is_on_now = $this->scheduleService->isUserWorkingNow($karyawan);
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

            return response()->json([
                'id'            => $karyawan->id,
                'nip'           => $karyawan->nip ?? '-',
                'name'          => $karyawan->name ?? '-',
                'email'         => $karyawan->email ?? '-',
                'phone_number'  => $karyawan->phone_number ?? null,
                'profile_photo' => $karyawan->profile_photo ?? null,
                'role_name'     => optional($karyawan->role)->role_name ?? 'Tidak Ada Role',
                'nama_stasiun'  => optional($karyawan->station)->name ?? '-',
                'job_title'     => $karyawan->job_title ?? 'Belum Memilih',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }
}