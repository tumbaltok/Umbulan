<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class ScheduleService
{
    /**
     * Cek apakah user sedang aktif bekerja secara REAL-TIME berdasarkan jam saat ini.
     */
    public function isUserWorkingNow(User $user): bool
    {
        // Paksa timezone ke Asia/Jakarta agar tidak bentrok dengan jam server default (UTC)
        $now = Carbon::now('Asia/Jakarta');
        $dateStr = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        // Ambil jadwal hari ini berdasarkan logika shift
        $schedule = $this->getTodaySchedule($user, $dateStr);

        // Jika hari ini libur atau cuti, status dipastikan FALSE (OFF)
        if ($schedule['is_day_off'] || empty($schedule['scheduled_in']) || empty($schedule['scheduled_out'])) {
            return false;
        }

        // Ambil string jam dan pastikan format 8 karakter (HH:mm:ss)
        $in  = strlen($schedule['scheduled_in']) === 5 ? $schedule['scheduled_in'] . ':00' : $schedule['scheduled_in'];
        $out = strlen($schedule['scheduled_out']) === 5 ? $schedule['scheduled_out'] . ':00' : $schedule['scheduled_out'];

        // Skenario Shift Normal / Shift Pagi (Contoh: 02:00:00 - 17:00:00)
        if ($in < $out) {
            return ($currentTime >= $in && $currentTime < $out);
        }

        // Skenario Shift Malam Lintas Hari (Contoh: 19:00:00 - 07:00:00)
        if ($in > $out) {
            return ($currentTime >= $in || $currentTime < $out);
        }

        return false;
    }

    public function getTodaySchedule(User $user, $date = null)
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();
        
        // 1. JADWAL NORMAL
        if ($user->schedule_type === 'normal') {
            $dayName = $targetDate->format('D');
            $allowedDays = $user->normal_work_days ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

            if (in_array($dayName, $allowedDays)) {
                return [
                    'shift_type' => 'normal',
                    'shift_name' => 'Kerja Normal',
                    'scheduled_in' => $user->normal_check_in ?? '08:00:00',
                    'scheduled_out' => $user->normal_check_out ?? '17:00:00',
                    'is_day_off' => false,
                ];
            }

            return [
                'shift_type' => 'libur',
                'shift_name' => 'Hari Libur (Normal)',
                'scheduled_in' => null,
                'scheduled_out' => null,
                'is_day_off' => true,
            ];
        }

        // 2. JADWAL ROSTER
        if ($user->schedule_type === 'roster') {
            if (!$user->roster_start_date) {
                return $this->getShiftRosterDetail('pagi');
            }

            $startDate = Carbon::parse($user->roster_start_date)->startOfWeek(Carbon::TUESDAY);
            $currentTuesday = $targetDate->copy()->startOfWeek(Carbon::TUESDAY);

            $weeksDiff = (int) $startDate->diffInWeeks($currentTuesday, false);

            $shiftCycle = ($weeksDiff % 3 + 3) % 3;

            switch ($shiftCycle) {
                case 0:
                    return $this->getShiftRosterDetail('pagi');
                case 1:
                    return $this->getShiftRosterDetail('malam');
                case 2:
                    return $this->getShiftRosterDetail('libur');
            }
        }

        return [
            'shift_type' => 'libur',
            'shift_name' => 'Libur',
            'scheduled_in' => null,
            'scheduled_out' => null,
            'is_day_off' => true,
        ];
    }

    private function getShiftRosterDetail($shiftType)
    {
        if ($shiftType === 'pagi') {
            return [
                'shift_type' => 'pagi',
                'shift_name' => 'Roster Shift Pagi (07:00 - 19:00)',
                'scheduled_in' => '07:00:00',
                'scheduled_out' => '19:00:00',
                'is_day_off' => false,
            ];
        }

        if ($shiftType === 'malam') {
            return [
                'shift_type' => 'malam',
                'shift_name' => 'Roster Shift Malam (19:00 - 07:00)',
                'scheduled_in' => '19:00:00',
                'scheduled_out' => '07:00:00',
                'is_day_off' => false,
            ];
        }

        return [
            'shift_type' => 'libur',
            'shift_name' => 'Roster Minggu Libur',
            'scheduled_in' => null,
            'scheduled_out' => null,
            'is_day_off' => true,
        ];
    }
}