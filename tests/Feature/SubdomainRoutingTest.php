<?php

namespace Tests\Feature;

use App\Models\User\Station;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SubdomainRoutingTest extends TestCase
{
    use DatabaseMigrations;

    public function test_main_domain_serves_landing_and_redirects_portal_paths_to_karyawan_subdomain(): void
    {
        $this->get('https://umbulan.site/')
            ->assertStatus(200)
            ->assertSee('https://karyawan.umbulan.site/login');

        $this->get('https://umbulan.site/login')
            ->assertStatus(302)
            ->assertRedirect('https://karyawan.umbulan.site/login');

        $this->get('https://umbulan.site/register')
            ->assertStatus(302)
            ->assertRedirect('https://karyawan.umbulan.site/register');

        $this->get('https://www.umbulan.site/dashboard')
            ->assertStatus(302)
            ->assertRedirect('https://karyawan.umbulan.site/dashboard');
    }

    public function test_karyawan_subdomain_serves_auth_and_redirects_root_appropriately(): void
    {
        // Tamu mengakses root karyawan.umbulan.site -> diarahkan ke login
        $this->get('https://karyawan.umbulan.site/')
            ->assertStatus(302)
            ->assertRedirect(route('login'));

        $this->get('https://karyawan.umbulan.site/login')
            ->assertStatus(200);

        $this->get('https://karyawan.umbulan.site/register')
            ->assertStatus(200);

        $station = Station::firstOrCreate(
            ['kode_stasiun' => 'SUB-01'],
            [
                'name' => 'Stasiun Subdomain',
                'type' => 'stasiun',
            ]
        );

        $user = User::create([
            'name' => 'Staff Subdomain',
            'email' => 'staff@umbulan.site',
            'password' => bcrypt('Password123!'),
            'station_id' => $station->id,
            'gender' => 'Laki-laki',
            'gender_id' => 1,
            'schedule_type' => 'normal',
            'email_verified_at' => now(),
        ]);

        // Karyawan yang sudah login mengakses root karyawan.umbulan.site -> masuk dashboard
        $this->actingAs($user)
            ->get('https://karyawan.umbulan.site/')
            ->assertStatus(302)
            ->assertRedirect(route('dashboard'));
    }

    public function test_api_routes_are_not_redirected_by_subdomain_middleware(): void
    {
        $this->getJson('https://umbulan.site/api/master-data')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->getJson('https://karyawan.umbulan.site/api/master-data')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }
}
