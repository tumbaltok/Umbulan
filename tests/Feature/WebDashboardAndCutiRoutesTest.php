<?php

namespace Tests\Feature;

use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class WebDashboardAndCutiRoutesTest extends TestCase
{
    use DatabaseMigrations;

    public function test_dashboard_and_submission_history_pages_load_for_male_and_female_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@umbulan.site')->firstOrFail();
        $karyawan = User::where('email', 'karyawan@umbulan.site')->firstOrFail();

        $station = Station::firstOrFail();
        $role = Role::firstOrFail();

        $femaleUser = User::create([
            'nip' => 'FEM-001',
            'name' => 'Karyawan Perempuan',
            'email' => 'perempuan@umbulan.site',
            'password' => bcrypt('Password123!'),
            'role_id' => $role->id,
            'level' => 3,
            'gender' => 'Perempuan',
            'gender_id' => 2,
            'station_id' => $station->id,
            'schedule_type' => 'normal',
            'normal_work_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            'normal_check_in' => '07:00:00',
            'normal_check_out' => '16:00:00',
            'email_verified_at' => now(),
        ]);

        // Dashboard, riwayat pengajuan, & profil untuk akun karyawan laki-laki
        $this->actingAs($karyawan)
            ->get('https://karyawan.umbulan.site/dashboard')
            ->assertStatus(200)
            ->assertSee('Kalender Jadwal Kerja')
            ->assertDontSee('Biometrik Wajah');

        $this->actingAs($karyawan)
            ->get('https://karyawan.umbulan.site/cuti/riwayat')
            ->assertStatus(200);

        $this->actingAs($karyawan)
            ->get('https://karyawan.umbulan.site/car/riwayat')
            ->assertStatus(200);

        $this->actingAs($karyawan)
            ->get('https://karyawan.umbulan.site/mpr/riwayat')
            ->assertStatus(200);

        $this->actingAs($karyawan)
            ->get('https://karyawan.umbulan.site/profile')
            ->assertStatus(200)
            ->assertDontSee('Biometrik Wajah');

        // Dashboard & riwayat cuti untuk akun perempuan (query Saldo Cuti Haid via SubCuti)
        $this->actingAs($femaleUser)
            ->get('https://karyawan.umbulan.site/dashboard')
            ->assertStatus(200)
            ->assertSee('Saldo Cuti Haid');

        $this->actingAs($femaleUser)
            ->get('https://karyawan.umbulan.site/cuti/riwayat')
            ->assertStatus(200);

        // Halaman admin stasiun, karyawan, role, holidays, persetujuan, & record
        $this->actingAs($admin)
            ->get('https://karyawan.umbulan.site/admin/stations')
            ->assertStatus(200)
            ->assertDontSee('Radius GPS');

        $this->actingAs($admin)
            ->get('https://karyawan.umbulan.site/admin/karyawan')
            ->assertStatus(200)
            ->assertDontSee('Reset Biometrik');

        foreach ([
            '/admin/role',
            '/admin/holidays',
            '/admin/persetujuan/cuti',
            '/admin/persetujuan/car',
            '/admin/persetujuan/mpr',
            '/admin/record/cuti',
            '/admin/record/car',
            '/admin/record/mpr',
        ] as $adminPath) {
            $this->actingAs($admin)
                ->get("https://karyawan.umbulan.site{$adminPath}")
                ->assertStatus(200);
        }

        // Form pengajuan Cuti, CAR, & MPR saat akun lengkap (3 syarat terpenuhi)
        $karyawan->forceFill(['signature' => 'signatures/dummy.png'])->save();
        foreach (['/cuti/ajukan', '/car/ajukan', '/mpr/ajukan'] as $createPath) {
            $response = $this->actingAs($karyawan)
                ->get("https://karyawan.umbulan.site{$createPath}")
                ->assertStatus(200);

            if ($createPath === '/cuti/ajukan') {
                $response->assertViewHas('jenisCuti', function ($items) {
                    return $items->every(fn ($item) => $item->parent_id === null);
                });
            }
        }
    }
}
