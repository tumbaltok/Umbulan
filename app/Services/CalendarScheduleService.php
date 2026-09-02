<?php

namespace App\Services;

use App\Models\Cuti\PengajuanCuti;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalendarScheduleService
{
    protected ScheduleService $scheduleService;
    protected HolidayService $holidayService;

    public function __construct(ScheduleService $scheduleService, HolidayService $holidayService)
    {
        $this->scheduleService = $scheduleService;
        $this->holidayService = $holidayService;
    }

    /**
     * Ambil Data Hari Libur Nasional via HolidayService (SKB 3 Menteri)
     */
    public function getNationalHolidays(int|string $year): array
    {
        return $this->holidayService->getNationalHolidays($year);
    }

    /**
     * Generate Matriks Jadwal Bulanan untuk Tampilan GitHub Activity
     */
    public function getMonthlyCalendar(User $user, $month, $year)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $holidays = $this->getNationalHolidays($year);

        // Ambil data cuti user yang disetujui (mencakup cuti lintas bulan)
        $approvedLeaves = PengajuanCuti::where('user_id', $user->id)
            ->where('status_akhir', 'approved') // Hanya ambil cuti yang telah disetujui
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_mulai', [$startDate, $endDate])
                    ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
                    ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->where('tanggal_mulai', '<=', $startDate)
                            ->where('tanggal_selesai', '>=', $endDate);
                    });
            })
            ->get();

        $calendarDays = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $daySchedule = $this->scheduleService->getTodaySchedule($user, $dateString);

            // Cek apakah ada cuti aktif pada tanggal evaluasi
            $leaveInfo = null;
            foreach ($approvedLeaves as $leave) {
                if ($currentDate->between(Carbon::parse($leave->tanggal_mulai), Carbon::parse($leave->tanggal_selesai))) {
                    $leaveInfo = $leave;
                    break;
                }
            }

            // Tentukan status dan warna indikator kalender
            $statusType = 'normal_work';
            $colorClass = 'bg-emerald-500 hover:bg-emerald-600';
            $titleText = 'Jadwal Masuk Kerja';
            $descriptionText = '';

            if ($leaveInfo) {
                // Status cuti disetujui (warna kuning)
                $statusType = 'cuti';
                $colorClass = 'bg-amber-400 hover:bg-amber-500';
                $titleText = 'Sedang Cuti';
                $descriptionText = 'Pengajuan Cuti Disetujui: '.($leaveInfo->alasan_cuti ?? 'Izin Cuti');
            } elseif ($user->schedule_type === 'normal') {
                // Status jadwal kerja normal
                $isNationalHoliday = isset($holidays[$dateString]);
                if ($daySchedule['is_day_off'] || $isNationalHoliday) {
                    // Libur akhir pekan atau tanggal merah nasional (warna merah)
                    $statusType = 'libur';
                    $colorClass = 'bg-rose-500 hover:bg-rose-600';
                    $titleText = $isNationalHoliday ? 'Libur Nasional: '.$holidays[$dateString] : 'Libur Akhir Pekan';
                    $descriptionText = $isNationalHoliday ? $holidays[$dateString] : 'Hari Libur Kerja Normal';
                } else {
                    $descriptionText = "Masuk Kerja Normal ({$daySchedule['scheduled_in']} - {$daySchedule['scheduled_out']} WIB)";
                }
            } else {
                // Status jadwal roster (rotasi shift 12 jam)
                if ($daySchedule['shift_type'] === 'pagi') {
                    $colorClass = 'bg-emerald-500 hover:bg-emerald-600'; // Shift pagi (warna hijau)
                    $titleText = 'Roster Shift Pagi';
                    $descriptionText = "Jam Kerja: {$daySchedule['scheduled_in']} - {$daySchedule['scheduled_out']} WIB (12 Jam)";
                } elseif ($daySchedule['shift_type'] === 'malam') {
                    $colorClass = 'bg-indigo-600 hover:bg-indigo-700'; // Shift malam (warna indigo)
                    $titleText = 'Roster Shift Malam';
                    $descriptionText = "Jam Kerja: {$daySchedule['scheduled_in']} - {$daySchedule['scheduled_out']} WIB (12 Jam)";
                } else {
                    $colorClass = 'bg-rose-500 hover:bg-rose-600'; // Libur roster (warna merah)
                    $statusType = 'libur';
                    $titleText = 'Roster Minggu Libur';
                    $descriptionText = 'Hari Libur Roster Shift';
                }
            }

            $calendarDays[] = [
                'date' => $dateString,
                'day_number' => $currentDate->format('d'),
                'day_name' => $currentDate->isoFormat('dddd'),
                'full_date' => $currentDate->isoFormat('D MMMM YYYY'),
                'color_class' => $colorClass,
                'title' => $titleText,
                'description' => $descriptionText,
                'status_type' => $statusType,
            ];

            $currentDate->addDay();
        }

        return $calendarDays;
    }
}
