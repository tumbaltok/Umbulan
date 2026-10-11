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

            // 1. Cek apakah ada cuti aktif pada tanggal evaluasi
            $leaveInfo = null;
            foreach ($approvedLeaves as $leave) {
                if ($currentDate->between(Carbon::parse($leave->tanggal_mulai), Carbon::parse($leave->tanggal_selesai))) {
                    $leaveInfo = $leave;
                    break;
                }
            }

            // 2. Evaluasi apakah tanggal ini merupakan Hari Libur (Weekend, Libur Nasional SKB 3 Menteri, atau Libur Roster)
            $isNationalHoliday = isset($holidays[$dateString]);
            $isHoliday = false;
            $holidayTitle = '';
            $holidayDesc = '';

            if ($user->schedule_type !== 'roster') {
                if ($daySchedule['is_day_off'] || $isNationalHoliday) {
                    $isHoliday = true;
                    $holidayTitle = $isNationalHoliday ? 'Libur Nasional: '.$holidays[$dateString] : ($daySchedule['shift_name'] ?? 'Hari Libur (Akhir Pekan)');
                    $holidayDesc = $isNationalHoliday ? $holidays[$dateString] : ($daySchedule['shift_name'] ?? 'Hari Libur (Akhir Pekan)');
                }
            } else {
                if ($daySchedule['shift_type'] === 'libur') {
                    $isHoliday = true;
                    $holidayTitle = 'Roster Minggu Libur';
                    $holidayDesc = 'Hari Libur Roster Shift';
                }
            }

            // 3. Tentukan status, warna, dan deskripsi indikator kalender sesuai hierarki prioritas
            if ($isHoliday) {
                // PRIORITAS 1: Hari Libur SELALU berwarna MERAH (walaupun ada cuti, karena hari libur tidak memotong saldo cuti)
                $statusType = 'libur';
                $colorClass = 'bg-rose-500 hover:bg-rose-600';
                $titleText = $holidayTitle;
                $descriptionText = $holidayDesc . ($leaveInfo ? ' (Bertepatan masa cuti - tidak memotong kuota)' : '');
            } elseif ($leaveInfo) {
                // PRIORITAS 2: Cuti disetujui pada HARI KERJA AKTIF (warna KUNING, memotong saldo kuota cuti)
                $statusType = 'cuti';
                $colorClass = 'bg-amber-400 hover:bg-amber-500';
                $titleText = 'Sedang Cuti';
                $descriptionText = 'Pengajuan Cuti Disetujui: '.($leaveInfo->alasan_cuti ?? 'Izin Cuti');
            } elseif ($user->schedule_type !== 'roster') {
                // PRIORITAS 3: Hari Kerja Reguler Masuk Biasa (warna HIJAU)
                $statusType = 'normal_work';
                $colorClass = 'bg-emerald-500 hover:bg-emerald-600';
                $titleText = 'Jadwal Masuk Kerja';
                $descriptionText = "Masuk Kerja ({$daySchedule['scheduled_in']} - {$daySchedule['scheduled_out']} WIB)";
            } else {
                // PRIORITAS 3: Hari Kerja Roster Masuk Biasa (Shift Pagi = HIJAU, Shift Malam = INDIGO)
                if ($daySchedule['shift_type'] === 'pagi') {
                    $statusType = 'pagi';
                    $colorClass = 'bg-emerald-500 hover:bg-emerald-600';
                    $titleText = 'Roster Shift Pagi';
                    $descriptionText = "Jam Kerja: {$daySchedule['scheduled_in']} - {$daySchedule['scheduled_out']} WIB (12 Jam)";
                } elseif ($daySchedule['shift_type'] === 'malam') {
                    $statusType = 'malam';
                    $colorClass = 'bg-indigo-600 hover:bg-indigo-700';
                    $titleText = 'Roster Shift Malam';
                    $descriptionText = "Jam Kerja: {$daySchedule['scheduled_in']} - {$daySchedule['scheduled_out']} WIB (12 Jam)";
                } else {
                    $statusType = 'libur';
                    $colorClass = 'bg-rose-500 hover:bg-rose-600';
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
