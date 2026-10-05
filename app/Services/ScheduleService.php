<?php

namespace App\Services;

use App\Models\User\User;
use Carbon\Carbon;

class ScheduleService
{
    private const TIMEZONE = 'Asia/Jakarta';

    public const ROSTER_CHANGE_HOUR = 7;

    private const SHIFT_CYCLE = [
        0 => 'pagi',
        1 => 'malam',
        2 => 'libur',
    ];

    // Periksa apakah karyawan saat ini berada dalam rentang jam kerja aktif
    public function isUserWorkingNow(User $user): bool
    {
        $now = $this->now();
        $schedule = $this->getTodaySchedule($user, $now);

        // Jika hari libur atau jadwal kosong, kembalikan false
        if (
            $schedule['is_day_off'] ||
            empty($schedule['scheduled_in']) ||
            empty($schedule['scheduled_out'])
        ) {
            return false;
        }

        $currentTime = $now->format('H:i:s');
        $in = $this->normalizeTime($schedule['scheduled_in']);
        $out = $this->normalizeTime($schedule['scheduled_out']);

        // Shift dalam hari yang sama (contoh: 08:00 - 17:00)
        if ($in < $out) {
            return $currentTime >= $in && $currentTime < $out;
        }

        // Shift lintas tengah malam (contoh: 19:00 - 07:00)
        if ($in > $out) {
            return $currentTime >= $in || $currentTime < $out;
        }

        return false;
    }

    // Ambil label status dan konfigurasi badge visual kerja karyawan
    public function getWorkingStatusText(User $user): array
    {
        $now = $this->now();
        $todaySchedule = $this->getTodaySchedule($user, $now);
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

        if ($user->schedule_type === 'roster') {
            if ($todaySchedule['shift_type'] === 'pagi') {
                $detailText = 'OFF (Shift Pagi)';
            } elseif ($todaySchedule['shift_type'] === 'malam') {
                $detailText = 'OFF (Shift Malam)';
            } else {
                $detailText = 'OFF (Libur Roster)';
            }
        } elseif ($user->schedule_type === 'reguler_6_hari') {
            if ($todaySchedule['is_day_off']) {
                $detailText = 'OFF (Libur Hari Minggu)';
            } else {
                $detailText = 'OFF (Luar Jam Kerja)';
            }
        } else {
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

    // Ambil detail jadwal kerja karyawan untuk tanggal tertentu (Reguler 5 Hari, Reguler 6 Hari, atau Roster)
    public function getTodaySchedule(User $user, $date = null): array
    {
        if ($date === null) {
            $evalDate = $this->now();
        } elseif ($date instanceof Carbon) {
            $evalDate = $date->copy()->setTimezone(self::TIMEZONE);
        } else {
            $evalDate = Carbon::createFromFormat(
                'Y-m-d',
                substr((string) $date, 0, 10),
                self::TIMEZONE
            )->setTime(self::ROSTER_CHANGE_HOUR, 0, 0);
        }

        $scheduleType = $user->schedule_type ?? 'reguler_5_hari';
        if ($scheduleType === 'normal') {
            $scheduleType = 'reguler_5_hari';
        }

        // Jadwal tipe roster (rotasi mingguan dimulai hari Selasa)
        if ($scheduleType === 'roster') {
            if (empty($user->roster_start_date)) {
                return $this->getShiftRosterDetail('pagi');
            }

            return $this->calculateRosterByDateTime($user, $evalDate);
        }

        // Jadwal tipe Reguler 6 Hari (Senin - Sabtu, Sabtu 07:00 - 12:00)
        if ($scheduleType === 'reguler_6_hari') {
            return $this->calculateReguler6HariSchedule($user, $evalDate);
        }

        // Default: Jadwal tipe Reguler 5 Hari (Senin - Jumat, 07:00 - 16:00)
        return $this->calculateReguler5HariSchedule($user, $evalDate);
    }

    // Hitung jarak geolokasi dalam satuan meter menggunakan formula Haversine
    public function calculateDistanceMeter(
        float $userLat,
        float $userLng,
        float $stationLat,
        float $stationLng
    ): float {
        $earthRadius = 6371000; // Radius rata-rata bumi dalam meter
        $latFrom = deg2rad($userLat);
        $lonFrom = deg2rad($userLng);
        $latTo = deg2rad($stationLat);
        $lonTo = deg2rad($stationLng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(
            sqrt(
                pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) *
                pow(sin($lonDelta / 2), 2)
            )
        );

        return round($angle * $earthRadius, 2);
    }

    // Ambil waktu saat ini dengan zona waktu Asia/Jakarta
    private function now(): Carbon
    {
        return Carbon::now(self::TIMEZONE);
    }

    // Tentukan acuan Selasa pergantian shift roster terdekat
    private function getCurrentRosterTuesday(Carbon $dateTime): Carbon
    {
        $dateTime = $dateTime->copy()->setTimezone(self::TIMEZONE);

        $tuesday = $dateTime
            ->copy()
            ->startOfWeek(Carbon::TUESDAY)
            ->setTime(self::ROSTER_CHANGE_HOUR, 0, 0);

        if (
            $dateTime->dayOfWeekIso === Carbon::TUESDAY &&
            $dateTime->lt($tuesday)
        ) {
            $tuesday->subWeek();
        }

        if ($tuesday->gt($dateTime)) {
            $tuesday->subWeek();
        }

        return $tuesday;
    }

    // Hitung pergeseran siklus shift roster (Pagi -> Malam -> Libur)
    private function calculateRosterByDateTime(User $user, Carbon $targetDate): array
    {
        $startDate = Carbon::parse($user->roster_start_date, self::TIMEZONE)
            ->startOfWeek(Carbon::TUESDAY)
            ->setTime(self::ROSTER_CHANGE_HOUR, 0, 0);

        $currentTuesday = $this->getCurrentRosterTuesday($targetDate);

        $startDay = $startDate->copy()->startOfDay();
        $currentDay = $currentTuesday->copy()->startOfDay();
        $daysDiff = (int) $startDay->diffInDays($currentDay, false);
        $weeksDiff = (int) round($daysDiff / 7);
        $shiftCycle = (($weeksDiff % 3) + 3) % 3;
        $shiftType = self::SHIFT_CYCLE[$shiftCycle];

        return $this->getShiftRosterDetail($shiftType);
    }

    private function calculateRosterByDate(User $user, Carbon $targetDate): array
    {
        return $this->calculateRosterByDateTime($user, $targetDate);
    }

    // Hitung jadwal kerja Reguler 5 Hari: Senin - Jumat (07:00 - 16:00), Sabtu & Minggu OFF
    private function calculateReguler5HariSchedule(User $user, Carbon $evalDate): array
    {
        $dayOfWeek = $evalDate->dayOfWeekIso; // 1 = Senin s/d 7 = Minggu

        if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
            return [
                'shift_type' => 'reguler_5_hari',
                'shift_name' => 'Reguler 5 Hari (07:00 - 16:00)',
                'scheduled_in' => '07:00:00',
                'scheduled_out' => '16:00:00',
                'is_day_off' => false,
            ];
        }

        return [
            'shift_type' => 'libur',
            'shift_name' => 'Hari Libur (Akhir Pekan)',
            'scheduled_in' => null,
            'scheduled_out' => null,
            'is_day_off' => true,
        ];
    }

    // Hitung jadwal kerja Reguler 6 Hari: Senin - Jumat (07:00 - 16:00), Sabtu (07:00 - 12:00), Minggu OFF
    private function calculateReguler6HariSchedule(User $user, Carbon $evalDate): array
    {
        $dayOfWeek = $evalDate->dayOfWeekIso; // 1 = Senin s/d 7 = Minggu

        // Senin s/d Jumat: 07:00 - 16:00 WIB
        if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
            return [
                'shift_type' => 'reguler_6_hari',
                'shift_name' => 'Reguler 6 Hari (07:00 - 16:00)',
                'scheduled_in' => '07:00:00',
                'scheduled_out' => '16:00:00',
                'is_day_off' => false,
            ];
        }

        // Khusus Sabtu: 07:00 - 12:00 WIB (Setengah Hari)
        if ($dayOfWeek === 6) {
            return [
                'shift_type' => 'reguler_6_hari_sabtu',
                'shift_name' => 'Reguler Sabtu Setengah Hari (07:00 - 12:00)',
                'scheduled_in' => '07:00:00',
                'scheduled_out' => '12:00:00',
                'is_day_off' => false,
            ];
        }

        // Hari Minggu: OFF (Libur)
        return [
            'shift_type' => 'libur',
            'shift_name' => 'Hari Libur (Minggu)',
            'scheduled_in' => null,
            'scheduled_out' => null,
            'is_day_off' => true,
        ];
    }

    // Backward compatibility alias untuk calculateNormalSchedule
    private function calculateNormalSchedule(User $user, Carbon $evalDate): array
    {
        return $this->calculateReguler5HariSchedule($user, $evalDate);
    }

    // Ambil konfigurasi jam kerja berdasarkan jenis shift roster (pagi, malam, atau libur)
    private function getShiftRosterDetail(string $shiftType): array
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

    // Normalisasi format string waktu menjadi format H:i:s
    private function normalizeTime(string $time): string
    {
        return strlen($time) === 5 ? $time.':00' : $time;
    }
}
