<?php

namespace Tests\Feature;

use App\Models\Holiday\Holiday;
use App\Models\User\Gender;
use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HolidayAutoUpdateTest extends TestCase
{
    use DatabaseMigrations;

    protected User $adminUser;
    protected HolidayService $holidayService;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $station = Station::firstOrCreate(
            ['name' => 'Stasiun Mata Air'],
            ['kode_stasiun' => 'SMA', 'type' => 'stasiun']
        );

        $gender = Gender::firstOrCreate(['name' => 'Laki-laki']);

        $roleAdmin = Role::firstOrCreate(
            ['role_name' => 'ADMINISTRATOR'],
            ['parent_role_id' => null]
        );

        $this->adminUser = User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@umbulan.co.id',
            'phone_number' => '081234567890',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => bcrypt('Password123!'),
            'role_id' => $roleAdmin->id,
            'level' => 1,
            'station_id' => $station->id,
            'gender_id' => $gender->id,
            'schedule_type' => 'normal',
        ]);
        $this->adminUser->roles()->syncWithoutDetaching([$roleAdmin->id => ['is_primary' => true]]);

        $this->holidayService = app(HolidayService::class);
    }

    /**
     * Test 1: HolidayService membaca data dari database jika data tahun tersebut sudah tersedia.
     */
    public function test_holiday_service_retrieves_from_database_when_available(): void
    {
        Holiday::create([
            'holiday_date' => '2026-08-17',
            'holiday_name' => 'Hari Kemerdekaan RI ke-81',
            'holiday_year' => 2026,
            'is_cuti_bersama' => false,
            'source' => 'system_baseline',
        ]);

        $holidays = $this->holidayService->getNationalHolidays(2026);

        $this->assertArrayHasKey('2026-08-17', $holidays);
        $this->assertEquals('Hari Kemerdekaan RI ke-81', $holidays['2026-08-17']);
        $this->assertTrue($this->holidayService->isNationalHoliday('2026-08-17'));
        $this->assertEquals('Hari Kemerdekaan RI ke-81', $this->holidayService->getHolidayName('2026-08-17'));
    }

    /**
     * Test 2: HolidayService auto-sync dan mengisi database saat tahun baru belum ada datanya.
     */
    public function test_holiday_service_auto_syncs_and_populates_database_when_year_missing(): void
    {
        // Pastikan tabel holidays kosong untuk tahun 2026
        $this->assertEquals(0, Holiday::forYear(2026)->count());

        // Panggil getNationalHolidays
        $holidays = $this->holidayService->getNationalHolidays(2026);

        // Harus berhasil mengambil data (minimal baseline resmi atau API) dan menyimpannya di DB
        $this->assertNotEmpty($holidays);
        $this->assertGreaterThan(0, Holiday::forYear(2026)->count());
        $this->assertArrayHasKey('2026-01-01', $holidays);
    }

    /**
     * Test 3: Perintah Artisan holiday:sync berjalan sukses.
     */
    public function test_artisan_holiday_sync_command(): void
    {
        $this->artisan('holiday:sync', ['year' => 2026])
            ->assertExitCode(0)
            ->run();

        $this->assertGreaterThan(0, Holiday::forYear(2026)->count());
    }

    /**
     * Test 4: Halaman admin hari libur dapat diakses oleh Administrator.
     */
    public function test_admin_can_view_holidays_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.holidays.index'));
        $response->assertStatus(200);
        $response->assertSee('Hari Libur Nasional & Cuti Bersama');
        $response->assertSee('Sinkronisasi SKB 3 Menteri');
    }

    /**
     * Test 5: Admin dapat menambahkan hari libur / cuti bersama manual (instruksi dadakan pemerintah).
     */
    public function test_admin_can_add_custom_holiday_manually(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.holidays.store'), [
            'holiday_date' => '2026-11-25',
            'holiday_name' => 'Cuti Bersama Khusus Daerah Pasuruan',
            'is_cuti_bersama' => '1',
            'description' => 'Instruksi Khusus Bupati',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('holidays', [
            'holiday_date' => '2026-11-25',
            'holiday_name' => 'Cuti Bersama Khusus Daerah Pasuruan',
            'is_cuti_bersama' => 1,
            'source' => 'manual_admin',
        ]);

        // Pastikan cache diperbarui dan HolidayService mengenali hari libur manual ini
        $this->assertTrue($this->holidayService->isNationalHoliday('2026-11-25'));
    }

    /**
     * Test 6: Admin dapat memicu tombol sinkronisasi otomatis dari dashboard.
     */
    public function test_admin_can_trigger_sync_button(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.holidays.sync'), [
            'year' => 2026,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertGreaterThan(0, Holiday::forYear(2026)->count());
    }

    /**
     * Test 7: Admin dapat menghapus hari libur.
     */
    public function test_admin_can_delete_holiday(): void
    {
        $holiday = Holiday::create([
            'holiday_date' => '2026-12-31',
            'holiday_name' => 'Libur Tambahan Akhir Tahun',
            'holiday_year' => 2026,
            'is_cuti_bersama' => true,
            'source' => 'manual_admin',
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.holidays.destroy', $holiday->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
    }
}
