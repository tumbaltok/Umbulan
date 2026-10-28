<?php

namespace Tests\Feature;

use App\Models\User\Gender;
use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserRegistrationScheduleAndPhoneTest extends TestCase
{
    use DatabaseMigrations;

    protected Station $station;
    protected Gender $gender;
    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->station = Station::firstOrCreate(
            ['name' => 'Stasiun Mata Air'],
            ['kode_stasiun' => 'SMA', 'type' => 'stasiun']
        );

        $this->gender = Gender::firstOrCreate(
            ['name' => 'Laki-laki']
        );

        $this->role = Role::firstOrCreate(
            ['role_name' => 'STAF TEKNIK'],
            ['parent_role_id' => null]
        );
    }

    /**
     * Test 0: Halaman register menampilkan input nomor WhatsApp dan opsi jadwal kerja.
     */
    public function test_register_page_displays_phone_number_and_schedule_fields(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('phone_number');
        $response->assertSee('schedule_type');
        $response->assertSee('current_shift_choice');
        $response->assertSee('Nomor WhatsApp (Aktif)');
        $response->assertSee('Pilih Jenis Jadwal Kerja');
    }

    /**
     * Test 1: Registrasi mewajibkan nomor WhatsApp dan pilihan jadwal kerja.
     */
    public function test_registration_requires_phone_number_and_schedule_type(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Karyawan Baru',
            'email' => 'karyawan.baru@umbulan.co.id',
            'gender_id' => $this->gender->id,
            'station_id' => $this->station->id,
            'roles' => [$this->role->id],
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            // phone_number and schedule_type omitted
        ]);

        $response->assertSessionHasErrors(['phone_number', 'schedule_type']);
    }

    /**
     * Test 2: Validasi format dan keunikan nomor telepon WhatsApp.
     */
    public function test_registration_validates_phone_number_format_and_uniqueness(): void
    {
        // Setup user dengan nomor WA yang sudah terdaftar
        User::create([
            'name' => 'User Lama',
            'email' => 'lama@umbulan.co.id',
            'password' => bcrypt('Password123!'),
            'role_id' => $this->role->id,
            'station_id' => $this->station->id,
            'gender_id' => $this->gender->id,
            'phone_number' => '081234567890',
            'schedule_type' => 'reguler_5_hari',
        ]);

        // Coba mendaftar dengan nomor telepon yang sama (duplikat)
        $responseDuplicate = $this->post(route('register.post'), [
            'name' => 'User Kembar',
            'email' => 'kembar@umbulan.co.id',
            'gender_id' => $this->gender->id,
            'station_id' => $this->station->id,
            'roles' => [$this->role->id],
            'phone_number' => '081234567890',
            'schedule_type' => 'reguler_5_hari',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $responseDuplicate->assertSessionHasErrors(['phone_number']);

        // Coba mendaftar dengan nomor telepon bukan angka / terlalu pendek (<10 digit)
        $responseInvalid = $this->post(route('register.post'), [
            'name' => 'User Salah',
            'email' => 'salah@umbulan.co.id',
            'gender_id' => $this->gender->id,
            'station_id' => $this->station->id,
            'roles' => [$this->role->id],
            'phone_number' => '0812abc',
            'schedule_type' => 'reguler_5_hari',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $responseInvalid->assertSessionHasErrors(['phone_number']);
    }

    /**
     * Test 3: Pendaftaran sukses dengan Jadwal Reguler 5 Hari.
     */
    public function test_successful_registration_with_reguler_5_hari_schedule(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Budi Reguler',
            'email' => 'budi.reguler@umbulan.co.id',
            'gender_id' => $this->gender->id,
            'station_id' => $this->station->id,
            'roles' => [$this->role->id],
            'phone_number' => '081298765432',
            'schedule_type' => 'reguler_5_hari',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));

        $user = User::where('email', 'budi.reguler@umbulan.co.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('081298765432', $user->phone_number);
        $this->assertEquals('reguler_5_hari', $user->schedule_type);
        $this->assertEquals(['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], $user->normal_work_days);
        $this->assertEquals('07:00:00', $user->normal_check_in);
        $this->assertEquals('16:00:00', $user->normal_check_out);
        $this->assertNull($user->roster_start_date);
    }

    /**
     * Test 4: Pendaftaran sukses dengan Jadwal Reguler 6 Hari.
     */
    public function test_successful_registration_with_reguler_6_hari_schedule(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Siti Enam Hari',
            'email' => 'siti.6hari@umbulan.co.id',
            'gender_id' => $this->gender->id,
            'station_id' => $this->station->id,
            'roles' => [$this->role->id],
            'phone_number' => '085712345678',
            'schedule_type' => 'reguler_6_hari',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));

        $user = User::where('email', 'siti.6hari@umbulan.co.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('085712345678', $user->phone_number);
        $this->assertEquals('reguler_6_hari', $user->schedule_type);
        $this->assertEquals(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'], $user->normal_work_days);
        $this->assertEquals('07:00:00', $user->normal_check_in);
        $this->assertEquals('16:00:00', $user->normal_check_out);
        $this->assertNull($user->roster_start_date);
    }

    /**
     * Test 5: Pendaftaran sukses dengan Jadwal Roster dan Shift Pilihan.
     */
    public function test_successful_registration_with_roster_schedule(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Agus Roster',
            'email' => 'agus.roster@umbulan.co.id',
            'gender_id' => $this->gender->id,
            'station_id' => $this->station->id,
            'roles' => [$this->role->id],
            'phone_number' => '087812345678',
            'schedule_type' => 'roster',
            'current_shift_choice' => 'pagi',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));

        $user = User::where('email', 'agus.roster@umbulan.co.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('087812345678', $user->phone_number);
        $this->assertEquals('roster', $user->schedule_type);
        $this->assertNull($user->normal_work_days);
        $this->assertNull($user->normal_check_in);
        $this->assertNull($user->normal_check_out);
        $this->assertNotNull($user->roster_start_date);
    }
}
