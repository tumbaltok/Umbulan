<?php

namespace Database\Seeders;

use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Membuat 1 akun Admin (Level 1 / Approver) dan 1 akun Karyawan (Level 3) untuk kebutuhan testing.
     */
    public function run(): void
    {
        $station = Station::firstOrCreate(
            ['kode_stasiun' => 'UMBULAN'],
            [
                'name' => 'Stasiun Umbulan',
                'type' => 'stasiun',
                'latitude' => -7.7572565,
                'longitude' => 112.9314949,
                'radius_meters' => 1000,
            ]
        );

        $roleAdmin = Role::firstOrCreate(
            ['role_name' => 'GENERAL MANAGER'],
            ['description' => 'Atasan / Administrator Utama']
        );

        $roleKaryawan = Role::firstOrCreate(
            ['role_name' => 'OPERATIONAL'],
            ['description' => 'Staf Operasional']
        );

        // Pastikan OPERATIONAL mengarah ke GENERAL MANAGER untuk alur approval testing
        if (empty($roleKaryawan->parent_role_id)) {
            $roleKaryawan->update(['parent_role_id' => $roleAdmin->id]);
        }

        // 1. Akun Admin / Approver (Level 1)
        $admin = User::updateOrCreate(
            ['email' => 'admin@umbulan.site'],
            [
                'nip' => 'ADM-TEST-01',
                'name' => 'Admin Test',
                'password' => 'Password123!',
                'role_id' => $roleAdmin->id,
                'level' => 1,
                'gender' => 'Laki-laki',
                'gender_id' => 1,
                'station_id' => $station->id,
                'schedule_type' => 'normal',
                'normal_work_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'normal_check_in' => '07:00:00',
                'normal_check_out' => '16:00:00',
            ]
        );
        $admin->forceFill(['email_verified_at' => $admin->email_verified_at ?? now()])->save();
        $admin->roles()->sync([$roleAdmin->id => ['is_primary' => true]]);

        // 2. Akun Karyawan Biasa (Level 3)
        $karyawan = User::updateOrCreate(
            ['email' => 'karyawan@umbulan.site'],
            [
                'nip' => 'KRY-TEST-01',
                'name' => 'Karyawan Test',
                'password' => 'Password123!',
                'role_id' => $roleKaryawan->id,
                'level' => 3,
                'gender' => 'Laki-laki',
                'gender_id' => 1,
                'station_id' => $station->id,
                'schedule_type' => 'normal',
                'normal_work_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'normal_check_in' => '07:00:00',
                'normal_check_out' => '16:00:00',
            ]
        );
        $karyawan->forceFill(['email_verified_at' => $karyawan->email_verified_at ?? now()])->save();
        $karyawan->roles()->sync([$roleKaryawan->id => ['is_primary' => true]]);

        // Pastikan kuota Cuti Tahunan (kode CT) tersedia untuk kedua user test
        $cutiTahunan = JenisCuti::where('kode_cuti', 'CT')->first()
            ?? JenisCuti::whereNull('parent_id')->first();

        if ($cutiTahunan) {
            foreach ([$admin, $karyawan] as $u) {
                SaldoCuti::firstOrCreate(
                    [
                        'user_id' => $u->id,
                        'jenis_cuti_id' => $cutiTahunan->id,
                        'tahun' => (int) date('Y'),
                    ],
                    [
                        'kuota_awal' => 12,
                        'sisa_saldo' => 12,
                    ]
                );
            }
        }
    }
}
