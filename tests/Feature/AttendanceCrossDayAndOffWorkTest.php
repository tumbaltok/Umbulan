<?php

namespace Tests\Feature;

use App\Models\Absen\Kehadiran;
use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceCrossDayAndOffWorkTest extends TestCase
{
    use DatabaseMigrations;

    protected Station $station;
    protected Role $role;
    protected User $user;
    protected array $descriptor;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->descriptor = array_map(fn ($i) => round((sin($i) + 1) / 2, 6), range(0, 127));

        $this->station = Station::create([
            'kode_stasiun' => 'ST-TEST-01',
            'name' => 'Stasiun Umbulan Test',
            'type' => 'stasiun',
            'latitude' => -7.7556,
            'longitude' => 112.9125,
            'radius_meters' => 150,
        ]);

        $this->role = Role::create([
            'role_name' => 'OPERATOR_TEST',
            'description' => 'Operator Testing',
        ]);

        $this->user = User::create([
            'nip' => 'OP-TEST-01',
            'name' => 'Karyawan Test Absensi',
            'email' => 'op.absen@umbulan.site',
            'password' => bcrypt('Password123!'),
            'role_id' => $this->role->id,
            'level' => 3,
            'gender' => 'Laki-laki',
            'gender_id' => 1,
            'station_id' => $this->station->id,
            'schedule_type' => 'normal',
            'normal_work_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            'normal_check_in' => '07:00:00',
            'normal_check_out' => '16:00:00',
            'email_verified_at' => now(),
            'face_descriptor' => $this->descriptor,
            'signature' => 'signatures/test.png',
        ]);
        $this->user->roles()->syncWithoutDetaching([$this->role->id => ['is_primary' => true]]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function baseBiometricPayload(array $extra = []): array
    {
        return array_merge([
            'latitude' => -7.7556,
            'longitude' => 112.9125,
            'accuracy' => 10,
            'is_face_verified' => true,
            'is_liveness_verified' => true,
            'face_descriptor' => json_encode($this->descriptor),
        ], $extra);
    }

    public function test_check_in_on_off_day_requires_reason_and_records_present_status(): void
    {
        // Sabtu adalah jadwal OFF untuk reguler Senin-Jumat
        Carbon::setTestNow(Carbon::parse('2026-10-10 09:00:00', 'Asia/Jakarta'));

        $this->actingAs($this->user);

        // 1. Tanpa alasan di hari OFF -> 422
        $failRes = $this->postJson('/api/attendance/check-in', $this->baseBiometricPayload());
        $failRes->assertStatus(422);

        // 2. Dengan alasan keperluan hari OFF -> 200 & status Hadir
        $okRes = $this->postJson('/api/attendance/check-in', $this->baseBiometricPayload([
            'reason' => 'Perbaikan valve darurat di rumah meter',
        ]));

        $okRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'Hadir')
            ->assertJsonPath('data.shift_type', 'Masuk Hari OFF')
            ->assertJsonPath('data.is_off_day_work', true)
            ->assertJsonPath('data.is_late', false)
            ->assertJsonPath('data.reason_in', 'Perbaikan valve darurat di rumah meter');
    }

    public function test_unclosed_previous_session_blocks_checkin_and_forgot_checkout_allows_new_checkin(): void
    {
        // Kamis 08:00 WIB (Rabu kemarin lupa absen pulang)
        Carbon::setTestNow(Carbon::parse('2026-10-08 08:00:00', 'Asia/Jakarta'));

        $yesterday = Carbon::parse('2026-10-07', 'Asia/Jakarta')->toDateString();
        $prev = Kehadiran::create([
            'user_id' => $this->user->id,
            'date' => $yesterday,
            'shift_type' => 'Normal',
            'scheduled_in' => '07:00:00',
            'scheduled_out' => '16:00:00',
            'check_in' => '06:55:00',
            'status' => 'Hadir',
        ]);

        $this->actingAs($this->user);

        // 1. Dashboard melaporkan wajib checkout sesi kemarin & perlu pilihan resolusi
        $this->getJson('/api/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('data.requires_cross_day_checkout', true)
            ->assertJsonPath('data.needs_cross_day_resolution', true)
            ->assertJsonPath('data.is_rest_after_overtime', false)
            ->assertJsonPath('data.open_previous_attendance.id', $prev->id);

        // 2. Absen masuk hari ini ditolak sebelum absen pulang sesi kemarin
        $this->postJson('/api/attendance/check-in', $this->baseBiometricPayload())
            ->assertStatus(422)
            ->assertJsonPath('requires_cross_day_checkout', true);

        // 3. Absen pulang lintas hari tanpa cross_day_resolution ditolak dengan flag popup
        $this->postJson('/api/attendance/check-out', $this->baseBiometricPayload())
            ->assertStatus(422)
            ->assertJsonPath('requires_cross_day_resolution', true)
            ->assertJsonPath('target_date', $yesterday);

        // 4. Pilih 'forgot_checkout' -> menutup kemarin & tidak mengunci hari ini
        $outRes = $this->postJson('/api/attendance/check-out', $this->baseBiometricPayload([
            'cross_day_resolution' => 'forgot_checkout',
        ]));

        $outRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('cross_day_resolution', 'forgot_checkout')
            ->assertJsonPath('locked_today_for_rest', false);

        $prev->refresh();
        $this->assertEquals('forgot_checkout', $prev->checkout_type);
        $this->assertEquals('2026-10-08', $prev->checkout_date?->toDateString());
        $this->assertFalse((bool) $prev->is_early_checkout);

        // 5. Setelah itu, absen masuk hari ini diizinkan
        $inToday = $this->postJson('/api/attendance/check-in', $this->baseBiometricPayload([
            'reason' => 'Masuk terlambat sedikit',
        ]));
        $inToday->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_overnight_overtime_resolution_locks_today_checkin_for_mandatory_rest(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-08 08:00:00', 'Asia/Jakarta'));
        $yesterday = '2026-10-07';

        $prev = Kehadiran::create([
            'user_id' => $this->user->id,
            'date' => $yesterday,
            'shift_type' => 'Normal',
            'scheduled_in' => '07:00:00',
            'scheduled_out' => '16:00:00',
            'check_in' => '06:58:00',
            'status' => 'Hadir',
        ]);

        $this->actingAs($this->user);

        // Pilih 'overnight_overtime' -> menutup kemarin & mengunci hari ini untuk istirahat
        $outRes = $this->postJson('/api/attendance/check-out', $this->baseBiometricPayload([
            'cross_day_resolution' => 'overnight_overtime',
        ]));

        $outRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('cross_day_resolution', 'overnight_overtime')
            ->assertJsonPath('locked_today_for_rest', true);

        $prev->refresh();
        $this->assertEquals('overnight_overtime', $prev->checkout_type);

        // Record hari ini tercipta sebagai Istirahat Pasca Lembur dengan status Libur
        $todayRec = Kehadiran::where('user_id', $this->user->id)
            ->whereDate('date', '2026-10-08')
            ->first();

        $this->assertNotNull($todayRec);
        $this->assertTrue((bool) $todayRec->is_rest_after_overtime);
        $this->assertEquals('Libur', $todayRec->status);
        $this->assertEquals('Istirahat Pasca Lembur', $todayRec->shift_type);

        // Dashboard menandai sedang masa istirahat
        $this->getJson('/api/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('data.requires_cross_day_checkout', false)
            ->assertJsonPath('data.is_rest_after_overtime', true);

        // Mencoba absen masuk hari ini harus ditolak 422
        $this->postJson('/api/attendance/check-in', $this->baseBiometricPayload([
            'reason' => 'Coba masuk',
        ]))->assertStatus(422)
            ->assertJsonPath('is_rest_after_overtime', true);
    }

    public function test_yesterday_night_shift_checkout_before_11am_works_normally_without_popup(): void
    {
        // Pukul 07:15 WIB pasca Shift Malam (19:00 - 07:00) kemarin
        Carbon::setTestNow(Carbon::parse('2026-10-08 07:15:00', 'Asia/Jakarta'));

        $nightSession = Kehadiran::create([
            'user_id' => $this->user->id,
            'date' => '2026-10-07',
            'shift_type' => 'Shift Malam',
            'scheduled_in' => '19:00:00',
            'scheduled_out' => '07:00:00',
            'check_in' => '18:55:00',
            'status' => 'Hadir',
        ]);

        $this->actingAs($this->user);

        // Dashboard butuh checkout lintas hari, namun TIDAK memunculkan popup resolusi
        $this->getJson('/api/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('data.requires_cross_day_checkout', true)
            ->assertJsonPath('data.needs_cross_day_resolution', false);

        // Langsung check-out tanpa cross_day_resolution berhasil normal
        $this->postJson('/api/attendance/check-out', $this->baseBiometricPayload())
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('cross_day_resolution', 'normal');

        $nightSession->refresh();
        $this->assertEquals('normal', $nightSession->checkout_type);
        $this->assertEquals('2026-10-08', $nightSession->checkout_date?->toDateString());
        $this->assertFalse((bool) $nightSession->is_early_checkout);
    }
}
