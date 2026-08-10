<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User\User;
use App\Models\Absen\Kehadiran;
use App\Services\ScheduleService;
use Carbon\Carbon;

class AbsensiAdminController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index(Request $request)
    {
        // 1. Ambil tanggal filter (default hari ini)
        $tanggal = $request->input('tanggal', Carbon::today('Asia/Jakarta')->format('Y-m-d'));

        // 2. Ambil seluruh karyawan aktif beserta relasinya
        $semuaKaryawan = User::with(['role', 'station'])->orderBy('name', 'asc')->get();

        // 3. Ambil data absensi pada tanggal tersebut
        $dataAbsensi = Kehadiran::whereDate('date', $tanggal)->get()->keyBy('user_id');

        $sudahAbsen = [];
        $belumAbsen = [];
        $karyawanWajibHadir = [];

        foreach ($semuaKaryawan as $user) {
            // Cek jadwal kerja user pada tanggal filter menggunakan ScheduleService
            $schedule = $this->scheduleService->getTodaySchedule($user, $tanggal);

            // JIKA HARI INI JADWAL USER LIBUR / OFF -> LEWATI (Jangan dimasukkan ke daftar belum absen)
            if ($schedule['is_day_off']) {
                continue;
            }

            // Masukkan ke daftar karyawan yang wajib hadir hari ini
            $karyawanWajibHadir[] = $user;

            // Kelompokkan Sudah Absen vs Belum Absen
            if ($dataAbsensi->has($user->id)) {
                $sudahAbsen[] = [
                    'user'  => $user,
                    'absen' => $dataAbsensi->get($user->id),
                ];
            } else {
                $belumAbsen[] = $user;
            }
        }

        $karyawan = $karyawanWajibHadir;

        return view('admin.record.absensi', compact('tanggal', 'karyawan', 'sudahAbsen', 'belumAbsen'));
    }
}