<?php

namespace Tests\Feature;

use App\Models\User\User;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Tests\TestCase;

class WorkScheduleTest extends TestCase
{
    protected ScheduleService $scheduleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scheduleService = app(ScheduleService::class);
    }

    public function test_user_schedule_helpers_and_label()
    {
        // 1. Jadwal Normal (Senin - Jumat)
        $userNormal = new User(['schedule_type' => 'normal']);
        $this->assertTrue($userNormal->isNormal());
        $this->assertTrue($userNormal->isReguler5Hari()); // Backward compatibility
        $this->assertFalse($userNormal->isRoster());
        $this->assertEquals('Normal (Senin – Jumat)', $userNormal->schedule_label);

        // 2. Backward compatibility untuk 'reguler_5_hari'
        $userLegacy = new User(['schedule_type' => 'reguler_5_hari']);
        $this->assertTrue($userLegacy->isNormal());
        $this->assertTrue($userLegacy->isReguler5Hari());
        $this->assertFalse($userLegacy->isRoster());
        $this->assertEquals('Normal (Senin – Jumat)', $userLegacy->schedule_label);

        // 3. Jadwal Roster
        $userRoster = new User(['schedule_type' => 'roster']);
        $this->assertFalse($userRoster->isNormal());
        $this->assertFalse($userRoster->isReguler5Hari());
        $this->assertTrue($userRoster->isRoster());
        $this->assertEquals('Roster / Shift', $userRoster->schedule_label);
    }

    public function test_normal_schedule_calculation()
    {
        $user = new User([
            'id' => 101,
            'name' => 'Budi Santoso',
            'schedule_type' => 'normal',
        ]);

        // Uji hari Rabu (hari kerja: 07:00 - 16:00)
        $wednesday = Carbon::parse('2026-10-07 10:00:00'); // Rabu
        $scheduleWed = $this->scheduleService->getTodaySchedule($user, $wednesday);

        $this->assertFalse($scheduleWed['is_day_off']);
        $this->assertEquals('07:00:00', $scheduleWed['scheduled_in']);
        $this->assertEquals('16:00:00', $scheduleWed['scheduled_out']);
        $this->assertStringContainsString('Normal', $scheduleWed['shift_name']);

        // Uji hari Sabtu (libur akhir pekan)
        $saturday = Carbon::parse('2026-10-10 10:00:00'); // Sabtu
        $scheduleSat = $this->scheduleService->getTodaySchedule($user, $saturday);

        $this->assertTrue($scheduleSat['is_day_off']);
        $this->assertStringContainsString('Libur', $scheduleSat['shift_name']);

        // Uji hari Minggu (libur akhir pekan)
        $sunday = Carbon::parse('2026-10-11 10:00:00'); // Minggu
        $scheduleSun = $this->scheduleService->getTodaySchedule($user, $sunday);

        $this->assertTrue($scheduleSun['is_day_off']);
        $this->assertStringContainsString('Libur', $scheduleSun['shift_name']);
    }

    public function test_legacy_reguler_5_hari_schedule_calculation()
    {
        $user = new User([
            'id' => 102,
            'name' => 'Siti Aminah',
            'schedule_type' => 'reguler_5_hari',
        ]);

        // Uji hari Kamis (hari kerja: 07:00 - 16:00)
        $thursday = Carbon::parse('2026-10-08 09:00:00'); // Kamis
        $scheduleThu = $this->scheduleService->getTodaySchedule($user, $thursday);

        $this->assertFalse($scheduleThu['is_day_off']);
        $this->assertEquals('07:00:00', $scheduleThu['scheduled_in']);
        $this->assertEquals('16:00:00', $scheduleThu['scheduled_out']);

        // Uji hari Sabtu (libur)
        $saturday = Carbon::parse('2026-10-10 09:00:00'); // Sabtu
        $scheduleSat = $this->scheduleService->getTodaySchedule($user, $saturday);
        $this->assertTrue($scheduleSat['is_day_off']);
    }

    public function test_lateness_calculation_for_regular_schedule()
    {
        $user = new User([
            'id' => 103,
            'name' => 'Ahmad Dani',
            'schedule_type' => 'normal',
        ]);

        // Jam masuk baku adalah 07:00:00
        $wednesday = Carbon::parse('2026-10-07 07:00:00');
        $schedule = $this->scheduleService->getTodaySchedule($user, $wednesday);

        $scheduledIn = Carbon::parse('2026-10-07 ' . $schedule['scheduled_in'], 'Asia/Jakarta');

        $checkInOnTime = Carbon::parse('2026-10-07 07:00:00', 'Asia/Jakarta');
        $isLateOnTime = $checkInOnTime->gt($scheduledIn);
        $this->assertFalse($isLateOnTime);

        $checkInLate = Carbon::parse('2026-10-07 07:05:00', 'Asia/Jakarta');
        $isLate = $checkInLate->gt($scheduledIn);
        $this->assertTrue($isLate);
    }
}
