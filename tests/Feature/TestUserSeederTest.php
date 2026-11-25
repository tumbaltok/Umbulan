<?php

namespace Tests\Feature;

use App\Models\User\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TestUserSeederTest extends TestCase
{
    use DatabaseMigrations;

    public function test_database_seeder_creates_one_admin_and_one_karyawan_for_testing(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Pastikan idempoten jika dijalankan dua kali
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@umbulan.site')->first();
        $karyawan = User::where('email', 'karyawan@umbulan.site')->first();

        $this->assertNotNull($admin);
        $this->assertEquals(1, (int) $admin->level);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('Password123!', $admin->password));

        $this->assertNotNull($karyawan);
        $this->assertEquals(3, (int) $karyawan->level);
        $this->assertNotNull($karyawan->email_verified_at);
        $this->assertTrue(Hash::check('Password123!', $karyawan->password));

        $this->assertEquals(2, User::whereIn('email', ['admin@umbulan.site', 'karyawan@umbulan.site'])->count());
    }
}
