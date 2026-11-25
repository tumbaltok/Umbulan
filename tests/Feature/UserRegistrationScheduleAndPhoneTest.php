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
     * Test 0: Halaman register tidak menampilkan input nomor telepon/WhatsApp dan menampilkan opsi jadwal kerja.
     */
    public function test_register_page_displays_schedule_fields_without_phone_number(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertDontSee('name="phone_number"', false);
        $response->assertDontSee('Nomor WhatsApp');
        $response->assertSee('schedule_type');
        $response->assertSee('current_shift_choice');
        $response->assertSee('Pilih Jenis Jadwal Kerja');
    }

    /**
     * Test 1: Registrasi mewajibkan pilihan jadwal kerja dan tidak membutuhkan nomor telepon.
     */
    public function test_registration_requires_schedule_type(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Karyawan Baru',
            'email' => 'karyawan.baru@umbulan.co.id',
            'gender_id' => $this->gender->id,
            'station_id' => $this->station->id,
            'roles' => [$this->role->id],
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            // schedule_type omitted
        ]);

        $response->assertSessionHasErrors(['schedule_type']);
    }

    /**
     * Test 2: Validasi keunikan alamat email saat registrasi.
     */
    public function test_registration_validates_email_uniqueness(): void
    {
        // Setup user dengan email yang sudah terdaftar
        User::create([
            'name' => 'User Lama',
            'email' => 'lama@umbulan.co.id',
            'password' => bcrypt('Password123!'),
            'role_id' => $this->role->id,
            'station_id' => $this->station->id,
            'gender_id' => $this->gender->id,
            'schedule_type' => 'normal',
        ]);

        // Coba mendaftar dengan email yang sama (duplikat)
        $responseDuplicate = $this->post(route('register.post'), [
            'name' => 'User Kembar',
            'email' => 'lama@umbulan.co.id',
            'gender_id' => $this->gender->id,
            'station_id' => $this->station->id,
            'roles' => [$this->role->id],
            'schedule_type' => 'normal',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $responseDuplicate->assertSessionHasErrors(['email']);

        // Coba mendaftar dengan format email tidak valid
        $responseInvalid = $this->post(route('register.post'), [
            'name' => 'User Salah',
            'email' => 'bukan-email',
            'gender_id' => $this->gender->id,
            'station_id' => $this->station->id,
            'roles' => [$this->role->id],
            'schedule_type' => 'normal',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $responseInvalid->assertSessionHasErrors(['email']);
    }

    /**
     * Test 3: Pendaftaran sukses dengan Jadwal Normal (Senin - Jumat).
     */
    public function test_successful_registration_with_normal_schedule(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Budi Reguler',
            'email' => 'budi.reguler@umbulan.co.id',
            'gender_id' => $this->gender->id,
            'station_id' => $this->station->id,
            'roles' => [$this->role->id],
            'schedule_type' => 'normal',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));

        $user = User::where('email', 'budi.reguler@umbulan.co.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('normal', $user->schedule_type);
        $this->assertEquals(['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], $user->normal_work_days);
        $this->assertEquals('07:00:00', $user->normal_check_in);
        $this->assertEquals('16:00:00', $user->normal_check_out);
        $this->assertNull($user->roster_start_date);
    }

    /**
     * Test 4: Pendaftaran menolak tipe jadwal reguler_6_hari (sudah dihapus dari sistem).
     */
    public function test_registration_rejects_reguler_6_hari_schedule(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Siti Enam Hari',
            'email' => 'siti.6hari@umbulan.co.id',
            'gender_id' => $this->gender->id,
            'station_id' => $this->station->id,
            'roles' => [$this->role->id],
            'schedule_type' => 'reguler_6_hari',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['schedule_type']);
        $this->assertNull(User::where('email', 'siti.6hari@umbulan.co.id')->first());
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
            'schedule_type' => 'roster',
            'current_shift_choice' => 'pagi',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));

        $user = User::where('email', 'agus.roster@umbulan.co.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('roster', $user->schedule_type);
        $this->assertNull($user->normal_work_days);
        $this->assertNull($user->normal_check_in);
        $this->assertNull($user->normal_check_out);
        $this->assertNotNull($user->roster_start_date);
    }
}
