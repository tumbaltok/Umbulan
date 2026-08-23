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

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Ambil Data Hari Libur Nasional via Nager.Date API
     */
    public function getNationalHolidays(int|string $year)
    {
        return Cache::remember("national_holidays_nager_{$year}", 86400, function () use ($year) {
            try {
                // Endpoint resmi Nager.Date API untuk Indonesia (ID)
                $response = Http::timeout(5)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/ID");

                if ($response->successful()) {
                    $holidays = [];
                    foreach ($response->json() as $holiday) {
                        // Tentukan nama libur (prioritaskan nama lokal/localName)
                        $holidayName = $holiday['localName'] ?? $holiday['name'];
                        $formattedDate = Carbon::parse($holiday['date'])->format('Y-m-d');

                        $holidays[$formattedDate] = $holidayName;
                    }

                    if (! empty($holidays)) {
                        return $holidays;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Gagal mengambil data libur dari Nager API: '.$e->getMessage());
            }

            // Fallback Data Libur Standar (Jika API Offline)
            return [
                "{$year}-01-01" => 'Tahun Baru Masehi',
                "{$year}-05-01" => 'Hari Buruh Internasional',
                "{$year}-06-01" => 'Hari Lahir Pancasila',
                "{$year}-08-17" => 'Hari Kemerdekaan RI',
                "{$year}-12-25" => 'Hari Raya Natal',
            ];
        });
    }

    /**
     * Generate Matriks Jadwal Bulanan untuk Tampilan GitHub Activity
     */
    public function getMonthlyCalendar(User $user, $month, $year)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $holidays = $this->getNationalHolidays($year);

        // Ambil Data Cuti User yang Disetujui (Mencakup Cuti Lintas Bulan)
        $approvedLeaves = PengajuanCuti::where('user_id', $user->id)
            ->where('status_akhir', 'approved') // <--- Menggunakan status_akhir
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

            // 1. Cek Apakah Ada Cuti pada Tanggal Ini
            $leaveInfo = null;
            foreach ($approvedLeaves as $leave) {
                if ($currentDate->between(Carbon::parse($leave->tanggal_mulai), Carbon::parse($leave->tanggal_selesai))) {
                    $leaveInfo = $leave;
                    break;
                }
            }

            // Determine Status & Box Color (Style GitHub)
            $statusType = 'normal_work';
            $colorClass = 'bg-emerald-500 hover:bg-emerald-600';
            $titleText = 'Jadwal Masuk Kerja';
            $descriptionText = '';

            if ($leaveInfo) {
                // KONDISI CUTI -> KUNING
                $statusType = 'cuti';
                $colorClass = 'bg-amber-400 hover:bg-amber-500';
                $titleText = 'Sedang Cuti';
                $descriptionText = 'Pengajuan Cuti Disetujui: '.($leaveInfo->alasan_cuti ?? 'Izin Cuti');
            } elseif ($user->schedule_type === 'normal') {
                // JADWAL NORMAL
                $isNationalHoliday = isset($holidays[$dateString]);
                if ($daySchedule['is_day_off'] || $isNationalHoliday) {
                    // LIBUR -> MERAH
                    $statusType = 'libur';
                    $colorClass = 'bg-rose-500 hover:bg-rose-600';
                    $titleText = $isNationalHoliday ? 'Libur Nasional: '.$holidays[$dateString] : 'Libur Akhir Pekan';
                    $descriptionText = $isNationalHoliday ? $holidays[$dateString] : 'Hari Libur Kerja Normal';
                } else {
                    $descriptionText = "Masuk Kerja Normal ({$daySchedule['scheduled_in']} - {$daySchedule['scheduled_out']} WIB)";
                }
            } else {
                // JADWAL ROSTER (Abaikan Libur Nasional, Ikuti Rotasi Shift)
                if ($daySchedule['shift_type'] === 'pagi') {
                    $colorClass = 'bg-emerald-500 hover:bg-emerald-600'; // Pagi -> Hijau
                    $titleText = 'Roster Shift Pagi';
                    $descriptionText = "Jam Kerja: {$daySchedule['scheduled_in']} - {$daySchedule['scheduled_out']} WIB (12 Jam)";
                } elseif ($daySchedule['shift_type'] === 'malam') {
                    $colorClass = 'bg-indigo-600 hover:bg-indigo-700'; // Malam -> Nila/Biru Tua
                    $titleText = 'Roster Shift Malam';
                    $descriptionText = "Jam Kerja: {$daySchedule['scheduled_in']} - {$daySchedule['scheduled_out']} WIB (12 Jam)";
                } else {
                    $colorClass = 'bg-rose-500 hover:bg-rose-600'; // Libur Roster -> Merah
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
