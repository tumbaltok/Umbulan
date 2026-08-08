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

        // Skenario Shift Normal / Shift Pagi (Contoh: 08:00:00 - 17:00:00)
        if ($in < $out) {
            return ($currentTime >= $in && $currentTime < $out);
        }

        // Skenario Shift Malam Lintas Hari (Contoh: 19:00:00 - 07:00:00)
        if ($in > $out) {
            return ($currentTime >= $in || $currentTime < $out);
        }

        return false;
    }

    /**
     * Mendapatkan teks status operasional & alasan detail untuk tabel karyawan
     */
    public function getWorkingStatusText(User $user): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $todaySchedule = $this->getTodaySchedule($user, $now->format('Y-m-d'));
        $isWorkingNow = $this->isUserWorkingNow($user);

        if ($isWorkingNow) {
            return [
                'is_on' => true,
                'status_code' => 'ON',
                'label' => 'ON (Sedang Bekerja)',
                'badge_class' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                'dot_class' => 'bg-emerald-500',
            ];
        }

        // KONDISI OFF (TIDAK BEKERJA SAAT INI)
        if ($user->schedule_type === 'roster') {
            if ($todaySchedule['shift_type'] === 'pagi') {
                $detailText = 'OFF (Shift Pagi)';
            } elseif ($todaySchedule['shift_type'] === 'malam') {
                $detailText = 'OFF (Shift Malam)';
            } else {
                $detailText = 'OFF (Libur Roster)';
            }
        } else {
            // Jadwal Normal
            if ($todaySchedule['is_day_off']) {
                $detailText = 'OFF (Libur Akhir Pekan)';
            } else {
                $detailText = 'OFF (Luar Jam Kerja)';
            }
        }

        return [
            'is_on' => false,
            'status_code' => 'OFF',
            'label' => $detailText,
            'badge_class' => 'text-slate-600 bg-slate-100 border-slate-200',
            'dot_class' => 'bg-slate-400',
        ];
    }

    public function getTodaySchedule(User $user, $date = null)
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();
        
        // 1. JADWAL NORMAL
        if ($user->schedule_type === 'normal' || empty($user->schedule_type)) {
            // Ambil nomor hari ISO: 1 (Senin) sampai 7 (Minggu)
            $dayOfWeek = $targetDate->dayOfWeekIso; 

            // Ambil data hari kerja dari atribut user
            $allowedDays = $user->normal_work_days;

            if (is_string($allowedDays)) {
                $allowedDays = json_decode($allowedDays, true);
            }

            // Fallback default jika data di database kosong: Senin (1) s/d Jumat (5)
            if (empty($allowedDays) || !is_array($allowedDays)) {
                $isWorkDay = ($dayOfWeek >= 1 && $dayOfWeek <= 5);
            } else {
                // Konversi isi array ke huruf kecil untuk pencocokan multi-format
                $allowedDaysLower = array_map('strtolower', $allowedDays);
                $dayNameShort = strtolower($targetDate->format('D')); // mon, tue, thu, dst
                $dayNameFull  = strtolower($targetDate->format('l')); // monday, thursday, dst
                
                // Peta nama hari Indonesia
                $indoDays = [
                    1 => 'senin', 2 => 'selasa', 3 => 'rabu', 
                    4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'
                ];
                $dayNameIndo = $indoDays[$dayOfWeek];

                $isWorkDay = in_array($dayNameShort, $allowedDaysLower) || 
                             in_array($dayNameFull, $allowedDaysLower) || 
                             in_array($dayNameIndo, $allowedDaysLower) ||
                             in_array((string)$dayOfWeek, $allowedDaysLower);
            }

            if ($isWorkDay) {
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

    private function getShiftRosterDetail(string $shiftType)
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

    /**
     * Menghitung jarak presisi antara dua titik GPS dalam satuan METER (Haversine Formula)
     */
    public function calculateDistanceMeter(float $userLat, float $userLng, float $stationLat, float $stationLng): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latFrom = deg2rad($userLat);
        $lonFrom = deg2rad($userLng);
        $latTo = deg2rad($stationLat);
        $lonTo = deg2rad($stationLng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2);
    }
}